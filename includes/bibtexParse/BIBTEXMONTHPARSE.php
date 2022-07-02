<?php
/**
 * This file contains the external class PARSEMONTH of WIKINDX
 * 
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * BibTeX MONTH import class
 *
 * BibTeX month field can come in as:
 * jan
 * "8~" # jan
 * jan#"~8"
 *
 * where # is concatenation and '~' can be any non-numeric character. The number must be extracted for use in the WIKINDX 'day' field.
 *
 * Entries of type jun # "-" # aug are reduced to just the first month.
 */
class BIBTEXMONTHPARSE {
    
    // Constructor
    public function __construct() {
    }
    
    function init($monthField) {
        $startMonth = $this->startDay = $endMonth = $this->endDay = FALSE;
        $date = explode("#", $monthField);
        foreach ($date as $field) {
            $field = ucfirst(mb_strtolower(trim($field)));
            if ( $month = array_search($field, $this->monthToLongName()) ) {
                if (!$startMonth) {
                    $startMonth = $month;
                }
                else {
                    $endMonth = $month;
                }
                continue;
            }
            elseif ( $month = array_search($field, $this->monthToShortName()) ) {
                if (!$startMonth) {
                    $startMonth = $month;
                }
                else {
                    $endMonth = $month;
                }
                continue;
            }
            $this->parseDay($field);
        }
        if ($this->endDay && !$endMonth) {
            $endMonth = $startMonth;
        }
        
        return array($startMonth, $this->startDay, $endMonth, $this->endDay);
    }
        
    /**
     * extract day of month from field
     * @param string $dayField
     */
    private function parseDay($dayField) {
        preg_match("/([0-9]+).*([0-9]+)|([0-9]+)/u", $dayField, $array);
        if (array_key_exists(3, $array)) {
            if (!$this->startDay) {
                $this->startDay = $array[3];
            }
            elseif (!$this->endDay) {
                $this->endDay = $array[3];
            }
        }
        else {
            if (array_key_exists(1, $array)) {
                $this->startDay = $array[1];
            }
            if (array_key_exists(2, $array)) {
                $this->endDay = $array[2];
            }
        }
    }
    
    /**
     * Convert month to long name
     * @return array
     */
    function monthToLongName() {
        return array(
            1	=>	'January',
            2	=>	'February',
            3	=>	'March',
            4	=>	'April',
            5	=>	'May',
            6	=>	'June',
            7	=>	'July',
            8	=>	'August',
            9	=>	'September',
            10	=>	'October',
            11	=>	'November',
            12	=>	'December',
        );
    }
    
    /**
     * Convert month to short name
     * @return array
     */
    function monthToShortName() {
        return array(
            1	=>	'Jan',
            2	=>	'Feb',
            3	=>	'Mar',
            4	=>	'Apr',
            5	=>	'May',
            6	=>	'Jun',
            7	=>	'Jul',
            8	=>	'Aug',
            9	=>	'Sep',
            10	=>	'Oct',
            11	=>	'Nov',
            12	=>	'Dec',
        );
    }
}
