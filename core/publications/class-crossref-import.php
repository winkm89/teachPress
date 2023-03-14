<?php
/**
 * This file contains functions which are used for a Crossref import
 * @package teachpress\core\Crossref
 */

/**
 * This class contains functions which are used for Crossref import
 *
 * TP_Crossref_Import uses the Crossref v1 REST API, which appears to
 * only allow one DOI query per request.  If several DOIs are
 * requested, TP_Crossref_Import::init() will loop and sleep according
 * to the rate-limiting HTTPS headers received with the first
 * response.  Does the XML API allow queries for more than one DOI?
 *
 * The bibliographic metadata, including references, served by
 * Crossref are facts and thus not subject to copyright.  The
 * copyright of abstracts is held by the publisher or the author,
 * depending on the journal, but is redistributable as per Crossref
 * membership terms.
 *
 * @see https://www.crossref.org/documentation/retrieve-metadata/rest-api
 * @see https://api.crossref.org
 *
 * @package teachpress\core\Crossref
 */
class TP_Crossref_Import extends TP_Bibtex_Import {
    // Cannot use self::set_date_of_publishing() because it assumes
    // month is invalid if its length is <= 2.  Note that $parts is a
    // nested array as per CSL.
    private static function parse_date_parts( &$entry, $parts ) {
        $parts = $parts[0];
        switch ( count( $parts ) ) {
        case 1:
            $entry['date'] = sprintf(
                '%d-00-00',
                (int) $parts[0] );
            return true;
        case 2:
            $entry['date'] = sprintf(
                '%d-%02d-00',
                (int) $parts[0],
                (int) $parts[1] );
            return true;
        case 3:
            $entry['date'] = sprintf(
                '%d-%02d-%02d',
                (int) $parts[0],
                (int) $parts[1],
                (int) $parts[2] );
            return true;
        }
        return false;
    }


    // Prefer 'electronic' ISBN (or ISSN) over 'print' ditto.
    // Documentation claims type is one of 'eissn', 'pissn', or
    // 'lissn', but that appears not to be the case.
    private static function parse_isbn_type( &$entry, $type ) {
        $have_isbn = false;
        foreach ( $type as $isbn ) {
            switch ( (string) $isbn->type ) {
            case 'electronic':
                $entry['isbn'] = (string) $isbn->value;
                return true;
            case 'print':
                $entry['isbn'] = (string) $isbn->value;
                $have_isbn = true;
                break;
            }
        }
        return $have_isbn;
    }


   /**
    * Imports a Crossref string
    * @global class $PARSEENTRIES
    * @param string $input      String of DOIs, separated by a positive number of space (' ') characters
    * @param array $settings    With index names: overwrite
    * @param string $test       Set it to true for test mode.  This mode disables the inserting of publications into database
    * @return $array            An array with the inserted publication entries
    */
    public static function init( $input, $settings, $test = false ) {
        // Try to set the time limit for the script
        set_time_limit( TEACHPRESS_TIME_LIMIT );


        // create import info
        $import_id = true === $test ? 0 : tp_publication_imports::add_import();


        // It seems a DOI may contain just about any character except
        // for space ('/\s/ ', see
        // https://www.crossref.org/blog/dois-and-matching-regular-expressions).
        $dois = preg_split( '/\s+/', $input );
        if ( false === $dois ) {
            get_tp_message(
                __( "Error: No DOIs in {$input}", 'teachpress' ) );
            return null;
        }

        $entries = array();
        foreach ( $dois as $doi ) {
            // Honor rate limiting for all subsequent requests.
            // Calculate the delay (in seconds) from the headers
            // returned for the first request, or fall back to 50
            // requests per second.  Should also implement back-off if
            // the response time increases.
            if ( isset( $now ) ) {
                if ( ! isset( $delay ) ) {
                    $headers = wp_remote_retrieve_headers( $response );
                    $interval = array();
                    preg_match( '/^([0-9]+)s$/',
                                $headers['X-Rate-Limit-Interval'],
                                $interval );
                    $limit = $headers['X-Rate-Limit-Limit'];
                    $delay = count( $interval ) === 2 && $limit !== ''
                           ? $interval[1] / $limit
                           : 1 / 50;
                }
                time_sleep_until( $now + $delay );
            }
            $now = microtime( true );


            // Always specify the API version in the request,
            // URL-encode the DOI, and use "mailto" for
            // self-identification (a.k.a polite service level, verify
            // with "x-api-pool" header in response).  Could also use
            // "User-Agent" HTTPS header.  The API always returns
            // JSON.
            $response = wp_remote_get(
                'https://api.crossref.org/v1/works/'
                . urlencode( $doi ) . '?'
                . build_query( array( 'mailto' => 'hattne@ucla.edu' ) )
            );

            if ( is_wp_error( $response ) || intdiv(
                wp_remote_retrieve_response_code( $response ),
                100 ) !== 2 ) {
                get_tp_message(
                    __( "Error: Failed to get DOI {$doi} from Crossref",
                        'teachpress' ) );
                return null;
            }

            $object = json_decode( wp_remote_retrieve_body ( $response ) );
            if ( false === $object || $object->status !== 'ok' ) {
                get_tp_message(
                    __( "Error: Failed to parse Crossref JSON",
                        'teachpress' ) );
                return null;
            }


            // Debug: dump the raw XML response.
//            echo "<pre>";
//            echo htmlentities( wp_remote_retrieve_body( $response ) );
//            echo "</pre>";

            // Debug: round-trip via JSON into PHP array and dump.
//            echo "<pre>";
//            print_r( json_decode( json_encode( $object ), true ) );
//            echo "</pre>";


            // Only support version 1 works.
            if ( $object->{'message-type'} !== 'work'
                 || version_compare( $object->{'message-version'}, '1' ) < 0
                 || version_compare( $object->{'message-version'}, '2' ) >= 0) {
                get_tp_message(
                    __( "Error: Unsupported Crossref message",
                        'teachpress' ) );
                return null;
            }
            $work = $object->message;


            $entry = array();
            $entry['bibtex'] = TP_Publications::generate_unique_bibtex_key(
                $work->author[0]->family
                . $work->published->{'date-parts'}[0][0] );
            $entry['doi'] = (string) $work->DOI;

            if ( count( $work->title ) > 0 )
                $entry['title'] = (string) $work->title[0];

            $entry['volume'] = (string) $work->volume;
            $entry['number'] = (string) $work->issue;
            $entry['publisher'] = (string) $work->publisher;

            if ( $work->abstract )
                $entry['abstract'] = (string) $work->abstract;

            foreach ( $work->author as $author ) {
                $entry['author'] .= $author->given . " "
                                 . $author->family;
                if ( $author !== end( $work->author ) ) {
                    $entry['author'] .= " and ";
                }
            }

            foreach ( $work->editor as $editor ) {
                $entry['editor'] .= $editor->given . " "
                                 . $editor->family;
                if ( $editor !== end( $work->editor ) ) {
                    $entry['editor'] .= " and ";
                }
            }


            // Issued is the earliest of $work->published-print' and
            // $work->published-online.
            self::parse_date_parts( $entry, $work->issued->{'date-parts'} );


            // Use LaTeX syntax for en-dash ('--') in the page range.
            $pages = explode( '-', $work->page );
            if ( count( $pages ) === 2 ) {
                $entry['pages'] = implode( '--', $pages );
            }


            // See core/publications/default-publications-types.php
            // for options.
            switch ( (string) $work->type ) {
            case 'book':
                $entry['type'] = 'book';

                if ( self::parse_isbn_type( $entry, $work->{'isbn-type'} ) ) {
                    $entry['is_isbn'] = 1;
                }
                break;

            case 'book-chapter':
                // Prefer ISBN over ISSN for book chapters in case
                // they have both.
                $entry['type'] = 'inbook';

                if ( count( $work->{'container-title'} ) > 0 ) {
                    $entry['booktitle'] = (string)
                                        $work->{'container-title'}[0];
                }

                if ( self::parse_isbn_type( $entry, $work->{'isbn-type'} ) ) {
                    $entry['is_isbn'] = 1;
                } elseif ( self::parse_isbn_type(
                    $entry,
                    $work->{'issn-type'} ) ) {
                    $entry['is_isbn'] = 0;
                }
                break;

            case 'journal-article':
                // Prefer $work->{'short-container-title'} over
                // $work->title for journal articles.
                $entry['type'] = 'article';

                if ( count( $work->{'short-container-title'} ) > 0 ) {
                    $entry['journal']
                        = (string) $work->{'short-container-title'}[0];
                }

                if ( self::parse_isbn_type( $entry, $work->{'issn-type'} ) ) {
                    $entry['is_isbn'] = 0;
                }

                $issue = $work->{'journal-issue'};
                if ( $issue ) {
                    $entry['number'] = (string) $issue->issue;
                    if ( $issue->{'published-print'} ) {
                        self::parse_date_parts(
                            $entry,
                            $issue->{'published-print'}->{'date-parts'} );
                    } elseif ( $issue->{'published-online'} ) {
                        self::parse_date_parts(
                            $entry,
                            $issue->{'published-online'}->{'date-parts'} );
                    }
                }
                break;

            case 'posted-content':
                self::parse_date_parts(
                    $entry, $work->posted->{'date-parts'} );

                switch ( (string) $work->subtype ) {
                case 'preprint':
                    $entry['type'] = 'unpublished';
                    if ( count( $work->institution ) > 0 ) {
                        $entry['howpublished'] = (string) $work
                                               ->institution[0]->name;
                    }
                    if ( $work->resource->primary->URL ) {
                        $entry['url'] = (string) $work
                                      ->resource->primary->URL;
                    }
                    break;
                }
                break;
            }


            // Add the string to database, supply comma-separated
            // subject category names as tags (keywords).
            if ( false === $test ) {
                $entry['import_id'] = $import_id;
                $entry['entry_id'] = self::import_publication_to_database(
                    $entry, implode( ",", $work->subject), $settings);
            }
            array_push( $entries, $entry );
        }

        return $entries;
    }
}
