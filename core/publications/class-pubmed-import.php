<?php
/**
 * This file contains functions which are used for a PubMed import
 * @package teachpress\core\PubMed
 */

/**
 * This class contains functions which are used for a PubMed import
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


        // Make a comma-separated list of numeric PMIDs from the
        // provided $input.  With retmode=xml, efetch will return the
        // whole schmear.
        $query = http_build_query([
            'db'      => 'pubmed',
            'id'      => preg_replace('/[^0-9]+/', ',', $input),
            'retmode' => 'xml',
            'tool'    => 'teachPress',
            'email'   => 'johan@hattne.se']);
        $reply = file_get_contents(
            'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?'
            . $query);
        if ( $reply === false ) {
            get_tp_message(
                __("Error: Failed to get PMID $input from PubMed",
                   'teachpress'));
            return null;
        }
        
        $object = simplexml_load_string($reply);
        if ( $object === false ) {
            get_tp_message(
                __("Error: Failed to parse PubMed XML", 'teachpress'));
            return null;
        }

        // Debug: dump the raw XML reply.
        //echo "<pre>" . htmlentities($reply) . "</pre>";

        // Debug: round-trip via JSON into PHP array and dump.
        //echo "<pre>";
        //print_r(json_decode(json_encode($object), true));
        //echo "</pre>";

        $entries = array();
        foreach ( $object->PubmedArticle as $pubmed_article ) {
            // Although PubMed does have references to book chapters
            // or book sections (i.e. 'inbook'), they appear to be
            // indistinguishable from regular articles.
            $entry = array();
            $entry['type'] = 'article';


            // As in TexMed (https://www.bioinformatics.org/texmed),
            // use 'pmidN' for the BibTeX identifier, where N is the
            // eight-digit PMID.  Could also use $article->ELocationID
            // for the DOI.  IdType='pmc' is also of potential
            // interest here; could be used with db=pmc.
            foreach ( $pubmed_article
                      ->PubmedData->ArticleIdList->ArticleId as $id ) {
                switch ( $id['IdType'] ) {
                case 'doi':
                    $entry['doi'] = (string)$id;
                    break;
                case 'pubmed':
                    $entry['bibtex'] = 'pmid' . (string)$id;
                    break;
                }
            }

            $article = $pubmed_article->MedlineCitation->Article;
            $entry['title'] = (string)$article->ArticleTitle;

            $entry['journal'] = (string)$article->Journal->ISOAbbreviation;
            $entry['volume'] = (string)$article->Journal->JournalIssue->Volume;
            $entry['number'] = (string)$article->Journal->JournalIssue->Issue;
            $entry['abstract'] = $article->Abstract->AbstractText;


            // Also have $author->Initials (which is just the ForeName
            // concatenated in my case: "P C" => "PC") and
            // $author->AffiliationInfo.
            $separator = "";
            foreach ( $article->AuthorList->Author as $author ) {
                if ( $author['ValidYN'] != 'Y' )
                    continue;
                $entry['author'] .= $separator
                                 . (string)$author->ForeName . " "
                                 . (string)$author->LastName;
                $separator = " and ";
            }

            if ( $article->Journal->ISSN ) {
                $entry['is_isbn'] = 0;
                $entry['isbn'] = (string)$article->Journal->ISSN;
            }


            // For the date of publishing, PubMed appears to only
            // provide month and year.  For articles published by
            // other means, $article->ArticleDate may provide finer
            // granularity (e.g. electronic publication ahead of print
            // where ArticleDate['DateType'] == 'Electronic'.
            $entry['month'] = (string)$article
                            ->Journal->JournalIssue->PubDate->Month;
            $entry['year'] = (string)$article
                           ->Journal->JournalIssue->PubDate->Year;
            $entry['date'] = self::set_date_of_publishing($entry);



            // Use LaTeX syntax for en-dash ('--') in the page range
            // and convert e.g. "349-60" to "349--360".
            $pages = explode('-', (string)$article->Pagination->MedlinePgn);
            if ( count($pages) === 2 ) {
                $missing_digits = strlen($pages[0]) - strlen($pages[1]);
                if ( $missing_digits > 0 ) {
                    $pages[1] = substr($pages[0], 0, $missing_digits)
                              . $pages[1];
                }
            }
            $entry['pages'] = implode('--', $pages);


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
