<?php
/**
 * This file contains all functions which are used for a bibtex import
 * @package teachpress\core\bibtex
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.1.0
 */

/**
 * This class contains functions which are used for a bibtex import
 * @package teachpress\core\bibtex
 * @since 5.1.0
 */
class tp_bibtex_import {
    
}

/**
 * This class contains functions which are used for a author name manipulation in bibtex imports
 * @package teachpress\core\bibtex
 * @since 5.1.0
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
     * @global class $PARSECREATORS
     * @param array $entry
     * @param string $key
     * @return string
     * @since 5.1.0
     * @access private
     */
    private static function dynamic_mode ($entry, $key) {
        global $PARSECREATORS;
        $creator = new PARSECREATORS();
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
     * @since 5.1.0
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

