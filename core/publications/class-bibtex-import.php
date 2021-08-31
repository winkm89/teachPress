<?php
/**
 * This file contains all functions which are used for a bibtex import
 * @package teachpress\core\bibtex
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

/**
 * This class contains functions which are used for a bibtex import
 * @package teachpress\core\bibtex
 * @since 6.0.0
 */
class TP_Bibtex_Import {
   /**
    * Imports a BibTeX string
    * @global class $PARSEENTRIES
    * @param string $input      The input string with bibtex entries
    * @param array $settings    With index names: keyword_separator, author_format, overwrite
    * @param string $test       Set it to true for test mode. This mode disables the inserting of publications into database
    * @return $array            An array with the inserted publication entries
    * @since 3.0.0
    */
    public static function init ($input, $settings, $test = false) {
        
        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);
        
        // create import info
        $import_id = ( $test === false ) ? tp_publication_imports::add_import() : 0;
        
        // Init bibtexParse
        $input = TP_Bibtex::convert_bibtex_to_utf8($input);
        $parse = NEW BIBTEXPARSE();
        $parse->expandMacro = TRUE;
        $array = array('RMP' => 'Rev., Mod. Phys.');
        $parse->loadStringMacro($array);
        $parse->loadBibtexString($input);
        $parse->extractEntries();
        
        list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
        $max = count( $entries );
        // print_r($undefinedStrings);
        // print_r($entries);
        for ( $i = 0; $i < $max; $i++ ) {
            $entries[$i]['name'] = array_key_exists('name', $entries[$i]) === true ? $entries[$i]['name'] : '';
            $entries[$i]['date'] = array_key_exists('date', $entries[$i]) === true ? $entries[$i]['date'] : '';
            $entries[$i]['location'] = array_key_exists('location', $entries[$i]) === true ? $entries[$i]['location'] : '';
            $entries[$i]['keywords'] = array_key_exists('keywords', $entries[$i]) === true ? $entries[$i]['keywords'] : '';
            $entries[$i]['tags'] = array_key_exists('tags', $entries[$i]) === true ? $entries[$i]['tags'] : '';
            $entries[$i]['isbn'] = array_key_exists('isbn', $entries[$i]) === true ? $entries[$i]['isbn'] : '';
            $entries[$i]['issn'] = array_key_exists('issn', $entries[$i]) === true ? $entries[$i]['issn'] : '';
            $entries[$i]['tppubtype'] = array_key_exists('tppubtype', $entries[$i]) === true ? $entries[$i]['tppubtype'] : '';
            $entries[$i]['pubstate'] = array_key_exists('pubstate', $entries[$i]) === true ? $entries[$i]['pubstate'] : '';
            $entries[$i]['journal'] = array_key_exists('journal', $entries[$i]) === true ? $entries[$i]['journal'] : '';
            $entries[$i]['import_id'] = $import_id;
            
            // for the date of publishing
            $entries[$i]['date'] = self::set_date_of_publishing($entries[$i]);
            
            // for tags
            $tags = self::set_tags($entries[$i], $settings);
            
            // replace journal macros
            if ( $entries[$i]['journal'] != '' ) {
                $entries[$i]['journal'] = self::replace_journal_macros($entries[$i]['journal']);
            }
            
            // correct name | title bug of old teachPress versions
            if ($entries[$i]['name'] != '') {
                $entries[$i]['title'] = $entries[$i]['name'];
            }
            
            // consider old location fields
            if ( $entries[$i]['location'] != '' ) {
                $entries[$i]['address'] = $entries[$i]['location'];
            }
            
            // for author / editor
            if ( array_key_exists('author', $entries[$i]) ) { 
                $entries[$i]['author'] = tp_bibtex_import_author::init($entries[$i], 'author', $settings['author_format']);
            }
            if ( array_key_exists('editor', $entries[$i]) ) { 
                $entries[$i]['editor'] = tp_bibtex_import_author::init($entries[$i], 'editor', $settings['author_format']);
            }
            
            // for isbn/issn detection
            if ( $entries[$i]['issn'] != '' ) {
                $entries[$i]['is_isbn'] = 0;
                $entries[$i]['isbn'] = $entries[$i]['issn'];
            }
            else {
                $entries[$i]['is_isbn'] = 1;
            }
            
            // rename to teachPress keys
            $entries[$i]['type'] = $entries[$i]['bibtexEntryType'];
            $entries[$i]['bibtex'] = $entries[$i]['bibtexCitation'];
            
            // handle export data from teachPress/biblatex
            if ( $entries[$i]['tppubtype'] != '' ) {
                $entries[$i]['type'] = $entries[$i]['tppubtype'];
            }
            if ( $entries[$i]['pubstate'] != '' ) {
                $entries[$i]['status'] = $entries[$i]['pubstate'];
            }
            
            // replace bibtex chars
            foreach ($entries[$i] as $key => $value) {
                if ( $key == 'author' || $key == 'editor' ) {
                    // replace only a list of special chars and not all {} blocks
                    $entries[$i][$key] = TP_Bibtex::clean_author_names($value);
                    continue;
                }
                $entries[$i][$key] = str_replace(array('{','}'), array('',''), $value);
            }
            
            // Try to fix problems with line breaks
            if ( $tags != '' ) {
                $tags = str_replace (array("\r\n", "\n", "\r"), ' ', $tags);
            }
            
            // Add the string to database
            if ( $test === false ) {
                $entries[$i]['entry_id'] = self::import_publication_to_database($entries[$i], $tags, $settings);
            }
            // Print the array for testing
            else {
                var_dump($entries[$i]);
            }
        }
        return $entries;

    }
    
    /**
     * This function is used for the import and adds publications to the database or owerwrites existing publications
     * @param array $entry
     * @param array $tags
     * @param array $settings
     * @return int Returns the ID of the new or changed publication
     * @since 5.0.0
     * @access private
     */
    protected static function import_publication_to_database ($entry, $tags, $settings) {
        $check = true;
        if ( $settings['overwrite'] === true ) {
            $entry['entry_id'] = TP_Publications::change_publication_by_key($entry['bibtex'], $entry, $tags, $settings['ignore_tags']);
            $check = ( $entry['entry_id'] === false ) ? false : true;
        }
        if ( $settings['overwrite'] === false || $check === false ) {
            $tags = ( $settings['ignore_tags'] === true ) ? '' : $tags;
            $entry['entry_id'] = TP_Publications::add_publication($entry, $tags, '');
        }
        return $entry['entry_id'];
    }
    
    /**
     * This function parses a month name into his numeric expression
     * @param string $input
     * @return string
     * @since 6.0.0
     * @access public
     */
    public static function parse_month ($input) {
        $output = '';
        if ( strlen($input) > 2 ) {
            $date = date_parse($input);
            $output = ( $date['month'] < 10 ) ? '0' . $date['month'] : $date['month'];
        }
        return $output;
    }
    
    /**
     * This function is used for the import and sets the date of publishing for a publications.
     * @param array $entry
     * @return string
     * @since 5.0.0
     * @acces private
     */
    protected static function set_date_of_publishing ($entry) {
        $entry['month'] = array_key_exists('month', $entry) === true ? self::parse_month($entry['month']) : '';
        $entry['day'] = array_key_exists('day', $entry) === true ? $entry['day'] : '';
        // if complete date is given
        if ( $entry['date'] !== '' ) {
            $entry['date'] = $entry['date'];
        }
        // if month + year is given
        elseif ( $entry['month'] != '' && $entry['day'] === '' && $entry['year'] != '' ) {
            $entry['date'] = $entry['year'] . '-' . $entry['month'] . '-01';
        }
        // if day + month + year is given
        elseif ($entry['month'] != '' && $entry['day'] != '' && $entry['year'] != '') {
            $entry['date'] = $entry['year'] . '-' . $entry['month'] . '-' . $entry['day'];
        }
        // if year is given
        else {
            $entry['date'] = $entry['year'] . '-01-01';
        }
        return $entry['date'];
    }
    
    /**
     * This function is used for the import and sets the tags.
     * @param array $entry
     * @param array $settings
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function set_tags ($entry, $settings) {
        if ( $entry['keywords'] != '' ) {
            $tags = str_replace($settings['keyword_separator'],",",$entry['keywords']);
        }
        elseif ( $entry['tags'] != '' ) {
            $tags = str_replace($settings['keyword_separator'],",",$entry['tags']);
        }
        else {
            $tags = '';
        }
        return $tags;
    }
    
    /**
     * Replaces macro codes for journals with the full name of the journal
     * @param string $entry
     * @return string
     * @since 6.0.0
     */
    private static function replace_journal_macros ($entry) {
        $macro_list = TP_Bibtex_Macros::journals();
        if ( array_key_exists($entry, $macro_list) ) {
            return $macro_list[$entry];
        }
        return $entry;
    }
}

/**
 * This class contains functions which are used for a author name manipulation in bibtex imports
 * @package teachpress\core\bibtex
 * @since 6.0.0
 */
class tp_bibtex_import_author {
    
    /**
     * Init function for importing/parsing an author/editor
     * @param array $entry      The entry array
     * @param string $key       author or editor 
     * @param string $mode      default, dynamic or lastfirst
     * @return string
     */
    public static function init ($entry, $key, $mode) {
        if ( $mode === 'lastfirst' ) {
            return self::lastname_first($entry, $key);
        }
        else if ( $mode === 'dynamic'  ) {
            return self::dynamic_mode($entry, $key);
        }
        else {
            return $entry[$key];
        }
    }
    
    /**
     * This function can detect the name format automatically
     * @param array $entry
     * @param string $key
     * @return string
     * @since 6.0.0
     * @access private
     */
    private static function dynamic_mode ($entry, $key) {
        $creator = new BIBTEXCREATORPARSE();
        $creatorArray = $creator->parse( $entry[$key] );
        // print_r($creatorArray);
        $string = '';
        foreach ($creatorArray as $singlecreator) {
            $single = $singlecreator[0] . $singlecreator[1] . ' ' . $singlecreator[3] . ' ' .  $singlecreator[2];
            $single = str_replace(array('   ','  '),array(' ', ' '), $single);
            $string = ( $string == '' ) ? trim($single) : $string . ' and ' . trim($single);
        }
        return $string;
    }
    
    /**
     * This function is used for the format lastname1, firstname1 and lastname2, firstname2
     * @param array $entry
     * @param string $key
     * @return string
     * @since 6.0.0
     * @access private
     */
    private static function lastname_first($entry, $key) {
        $end = '';
        $new = explode(' and ', $entry[$key] );
        foreach ( $new as $new ) {
            $parts = explode(',', $new); 
            $num = count($parts); 
            $one = ''; 
            for ($j = 1; $j < $num; $j++) {
                $parts[$j] = trim($parts[$j]);
                $one .= ' '. $parts[$j];
            }
            $one .= ' ' . trim($parts[0]);
            $end = ( $end != '' ) ? $end . ' and ' . $one : $one;
        }
        return $end;
    }
}

