<?php
/**
 * This file contains functions which are used for a PubMed import
 * @package teachpress\core\PubMed
 */

/**
 * This class contains functions which are used for a PubMed import
 *
 * NCBI policies restrict the rate of requests to three per second.
 * Since requests are triggered by a human from the WordPress web
 * interface, this class is highly unlikely to approach that limit.
 *
 * @package teachpress\core\PubMed
 */
class TP_PubMed_Import extends TP_Bibtex_Import {
   /**
    * Imports a PubMed string
    * @global class $PARSEENTRIES
    * @param string $input      String of PMIDs, separated by a positive number of non-numeric characters (PMIDs are all-numeric)
    * @param array $settings    With index names: overwrite
    * @param string $test       Set it to true for test mode. This mode disables the inserting of publications into database
    * @return $array            An array with the inserted publication entries
    */
    public static function init ($input, $settings, $test = false) {
        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);


        // create import info
        $import_id = $test === false ? tp_publication_imports::add_import() : 0;


        // Make a comma-separated list of eight-digit PMIDs from the
        // provided $input.  With retmode=xml, efetch will return the
        // whole schmear.  According to
        // https://www.ncbi.nlm.nih.gov/books/NBK25499, if more than
        // about 200 UIDs are provided, the request should be made
        // using the HTTP POST method.
        $pmids = array();
        preg_match_all( '/[0-9]{8}/', $input, $pmids );
        if ( count( $pmids[0] ) < 1 ) {
            get_tp_message(
                __("Error: No PMIDs in $input",
                   'teachpress'));
            return null;

        }

        $body = array( 'db'      => 'pubmed',
                       'id'      => implode( ',', $pmids[0] ),
                       'retmode' => 'xml',
                       'tool'    => 'teachPress',
                       'email'   => 'johan@hattne.se' );

        if ( count( $pmids[0] ) <= 200 ) {
            $response = wp_remote_get(
                'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?' .
                build_query( $body )
            );
        } else {
            $response = wp_remote_post(
                'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi',
                array( 'body' => $body )
            );
        }

        if ( is_wp_error( $response ) || intdiv(
            wp_remote_retrieve_response_code( $response ), 100) !== 2 ) {
            get_tp_message(
                __("Error: Failed to get PMID $input from PubMed",
                   'teachpress'));
            return null;
        }

        $object = simplexml_load_string(
            wp_remote_retrieve_body ( $response ) );
        if ( $object === false ) {
            get_tp_message(
                __("Error: Failed to parse PubMed XML", 'teachpress'));
            return null;
        }

        // Debug: dump the raw XML response.
        //echo "<pre>" . htmlentities( wp_remote_retrieve_body( $response ) ) . "</pre>";

        // Debug: round-trip via JSON into PHP array and dump.
        //echo "<pre>";
        //print_r( json_decode( json_encode( $object ), true ) );
        //echo "</pre>";

        $entries = array();
        foreach ( $object->PubmedArticle as $article ) {
            $entry = array();


            // As in TexMed (https://www.bioinformatics.org/texmed),
            // use 'pmidN' for the BibTeX identifier, where N is the
            // PMID.  Could also use $article->ELocationID for the
            // DOI.  IdType='pmc' is also of potential interest here;
            // could be used with db=pmc.
            foreach ( $article->PubmedData->ArticleIdList->ArticleId as $id ) {
                switch ( $id['IdType'] ) {
                case 'doi':
                    $entry['doi'] = (string)$id;
                    break;
                case 'pubmed':
                    $entry['bibtex'] = 'pmid' . (string)$id;
                    break;
                }
            }

            $citation = $article->MedlineCitation->Article;


            // Zap the trailing period in PubMed article titles.
            $entry['title'] = preg_replace(
                '/\.$/', '', (string)$citation->ArticleTitle );

            $entry['journal'] = (string)$citation->Journal->ISOAbbreviation;
            $entry['volume'] = (string)$citation->Journal->JournalIssue->Volume;
            $entry['number'] = (string)$citation->Journal->JournalIssue->Issue;

            $entry['abstract'] = "";
            foreach ( $citation->Abstract->AbstractText as $text ) {
                // Does $entry['abstract'] support HTML?  If so, the
                // label should probably be emphasized with e.g. bold
                // and each text should be made its own paragraph.
                // There does not appear to be any other markup in the
                // abstract or the title (italic, bold, etc).
                if ( $entry['abstract'] !== "" )
                    $entry['abstract'] .= "\n\n";
                if ( $text['Label']
                     && strcasecmp( $text['Label'], "UNLABELLED") !== 0 ) {
                    $entry['abstract'] .= $text['Label'] . ": " . $text;
                } else {
                    $entry['abstract'] .= $text;
                }
            }


            // Also have $author->Initials (which is just the ForeName
            // concatenated in my case: "P C" => "PC") and
            // $author->AffiliationInfo.
            $separator = "";
            foreach ( $citation->AuthorList->Author as $author ) {
                if ( $author['ValidYN'] != 'Y' )
                    continue;
                $entry['author'] .= $separator
                                 . (string)$author->ForeName . " "
                                 . (string)$author->LastName;
                $separator = " and ";
            }

            if ( $citation->Journal->ISSN ) {
                $entry['is_isbn'] = 0;
                $entry['isbn'] = (string)$citation->Journal->ISSN;
            }


            // For the date of publishing, PubMed appears to only
            // provide month and year.  For articles published by
            // other means, $citation->ArticleDate may provide finer
            // granularity (e.g. electronic publication ahead of print
            // where ArticleDate['DateType'] == 'Electronic'.
            $entry['month'] = (string)$citation
                            ->Journal->JournalIssue->PubDate->Month;
            $entry['year'] = (string)$citation
                           ->Journal->JournalIssue->PubDate->Year;
            $entry['date'] = self::set_date_of_publishing($entry);



            // Use LaTeX syntax for en-dash ('--') in the page range
            // and convert e.g. "349-60" to "349--360".
            $pages = explode('-', (string)$citation->Pagination->MedlinePgn);
            if ( count($pages) === 2 ) {
                $missing_digits = strlen($pages[0]) - strlen($pages[1]);
                if ( $missing_digits > 0 ) {
                    $pages[1] = substr($pages[0], 0, $missing_digits)
                              . $pages[1];
                }
            }
            $entry['pages'] = implode('--', $pages);


            // Although PubMed does have references to book chapters
            // or book sections (i.e. 'inbook'), they appear to be
            // indistinguishable from regular articles.
            foreach ( $citation
                      ->PublicationTypeList->PublicationType as $type ) {
                if ( (string)$type === "Journal Article" ) {
                    $entry['type'] = 'article';
                    break;
                }
            }


            // Add the string to database.  Since there are no
            // keywords in PubMed (except possibly the MeshHeading),
            // there will be no tags.
            if ( $test === false ) {
                $entry['import_id'] = $import_id;
                $entry['entry_id'] = self::import_publication_to_database(
                    $entry, '', $settings);
            }
            array_push($entries, $entry);
        }        

        return $entries;
    }
}
