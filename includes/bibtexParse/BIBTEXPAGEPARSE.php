<?php
/**
 * This file contains the external class PARSEPAGE of WIKINDX
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * BibTeX PAGE import class
 */
class BIBTEXPAGEPARSE {
    /** boolean */
    private $completeField = FALSE;
    
    /**
     * calls the page parser and returns an array with first- and lastpage
     *
     * @param string $item
     *
     * @return array
     */
    public function init($item) {
        $item = trim($item);
        if (!$item) {
            return [FALSE, FALSE];
        }
        elseif ($this->completeField)
        { //if true, return the complete item, else return only the first number found.
            return [$item, FALSE];
        }
        if ($this->parsePages($item)) {
            return $this->return; // first and last page present.
        }
        // return whatever we have
        return [$item, FALSE];
    }
    
    /**
     * parsePages tries to split on '--' or '-' (in case no valid split on '--' is possible.
     * if the split results in 2 elements, the split is considered valid.
     *
     * @param string $pages
     *
     * @return mixed BOOLEAN|array(start, end)
     */
    private function parsePages($pages) {
        $start = $end = FALSE;
        $elements = preg_split("/--/u", $pages);
        //first split on the valid bibtex page separator
        if (count($elements) == 1) {
            //no '--' found, try on single '-'
            $elements = preg_split("/-/u", $pages);
        }
        //try on ','
        if (count($elements) == 1) {
            $elements = preg_split("/,/u", $pages);
        }
        if (count($elements) == 2) {
            //found valid pages that are separated by '--' or by '-'
            $start = trim($elements[0]);
            $end = trim($elements[1]);
            // if [1] < [0], this might be e.g. 456-76 or 456,76 inferring 456-476.  Will only work if arabic numerals
            if (is_numeric($start) && is_numeric($end) && ($end < $start)) {
                $end = mb_substr($start, 0, mb_strlen($start) - mb_strlen($end)) . $end;
            }
            $this->return = [$start, $end];

            return TRUE;
        }
        return FALSE;
    }
}