<?php
/**
 * This file contains the external class PARSECREATORS of WIKINDX
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/*

28/04/2005 - Mark Grimshaw.
	Efficiency improvements.

11/02/2006 - Daniel Reidsma.
	Changes to preg_matching to account for Latex characters in names such as {\"{o}}
*/
// For a quick command-line test (php -f PARSECREATORS.php) after installation, uncomment these lines:

/***********************
$authors = "Mark \~N. Grimshaw and Bush III, G.W. & M. C. H{\\'a}mmer Jr. and von Frankenstein, Ferdinand Cecil, P.H. & Charles Louis Xavier Joseph de la Vallee P{\\\"{o}}ussin";
$creator = new PARSECREATORS();
$creatorArray = $creator->parse($authors);
print_r($creatorArray);
***********************/

/**
 * Parse BibTeX authors
 */
class BIBTEXCREATORPARSE {
    
    /** boolean If true, separate initials from firstname */
    public $separateInitials = FALSE;
    /** boolean */
    public $removeBraces = TRUE;
    /** boolean */
    public $removeTilde = TRUE;
    /** boolean */
    public $removeEtAl = TRUE;
    /** string */
    private $prefix;

    /**
     * Create writer arrays from bibtex input.
     *
     * 'author field can be (delimiters between authors are 'and', 'AND', or '&'):
     * There are three possible cases:
     * 1: First von Last
     * 2: von Last, First
     * 3: von Last, Jr, First
     *
     * @param string $input
     *
     * @return mixed FALSE|array (firstname, initials, surname, jr, von)
     */
    public function parse($input) {
        if ($this->removeEtAl) {
            $input = str_replace("et al.", '', $input);
        }
        $input = trim($input);
        if ($this->removeBraces) {
            $input = str_replace(['{', '}'], '', $input);
        }
        if ($this->removeTilde) {
            $input = str_replace('~', ' ', $input);
        }
        //remove linebreaks
        $input = preg_replace('/[\r\n\t]/u', ' ', $input);

        if (preg_match('/\s&\s/u', $input)) {
            $authorArray = $this->explodeString(" & ", $input);
            $input = implode(" and ", $authorArray);
        }
        elseif (preg_match('/\sAND\s/u', $input)) {
            $authorArray = $this->explodeString(" AND ", $input);
            $input = implode(" and ", $authorArray);
        }
        // split on ' and '
        $authorArray = $this->explodeString(" and ", $input);
        foreach ($authorArray as $value) {
            $firstname = $initials = $von = $surname = $jr = "";
            $this->prefix = [];

            //get rid of multiple spaces
            $value = preg_replace("/\\s{2,}/u", ' ', trim($value));

            $commaAuthor = $this->explodeString(",", $value);
            $size = count($commaAuthor);
            if ($size == 1) {  
                //First von Last
                // First: longest sequence of white-space separated words starting with an uppercase and that is not the whole string.
                // von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
                // Then Last is everything else.
                // Lastname cannot be empty

                $author = $this->explodeString(" ", $value);
                if (count($author) == 1) {
                    $surname = $author[0];
                }
                else {
                    $tempFirst = [];

                    $case = $this->getStringCase($author[0]);
                    while ((($case == "upper") || ($case == "none")) && (count($author) > 0)) {
                        $tempFirst[] = array_shift($author);
                        if (!empty($author))
                        {
                            $case = $this->getStringCase($author[0]);
                        }
                    }

                    list($von, $surname) = $this->getVonLast($author);

                    if ($surname == "") {
                        $surname = array_pop($tempFirst);
                    }
                    $firstname = implode(" ", $tempFirst);
                }
            }
            elseif ($size == 2) {
                // we deal with von Last, First
                // First: Everything after the comma
                // von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
                // Then Last is everything else.
                // Lastname cannot be empty
                $author = $this->explodeString(" ", $commaAuthor[0]);
                if (count($author) == 1) {
                    $surname = $author[0];
                }
                else {
                    list($von, $surname) = $this->getVonLast($author);
                }
                $firstname = $commaAuthor[1];
            }
            else {
                // we deal with von Last, Jr, First
                // First: Everything after the comma
                // von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
                // Then Last is everything else.
                // Lastname cannot be empty
                $author = $this->explodeString(" ", $commaAuthor[0]);
                if (count($author) == 1) {
                    $surname = $author[0];
                }
                else {
                    list($von, $surname) = $this->getVonLast($author);
                }
                $jr = $commaAuthor[1];
                $firstname = $commaAuthor[2];
            }

            $firstname = trim($firstname);
            $von = trim($von);
            $surname = trim($surname);
            $jr = trim($jr);

            $firstname = $this->formatFirstname($firstname);
            if ($this->separateInitials) {
                list($firstname, $initials) = $this->separateInitialsFunc($firstname);
            }

            $creators[] = [$firstname, $initials, $surname, $jr, $von];
        }
        if (isset($creators)) {
            return $creators;
        }
        else {
            return FALSE;
        }
    }
    /**
     * converts a first name to initials
     *
     * @param string $firstname     The firstname string
     * @param string $punctuation   The punctuation after an initial
     *
     * @return string
     */
    public function getInitials($firstname, $punctuation = '.') {
        $initials = '';
        $name = explode(' ', $firstname);
        foreach ($name as $part) {
            $size = mb_strlen($part);
            if (self::matchSuffix($part, ".") && ($size < 4)) {
                $part = str_replace('.', '', $part);
                $initials .= $part . $punctuation . " ";
            }
            //	elseif (preg_match("/([A-Z])/u", $part, $firstChar))
            elseif (preg_match("/(\\p{Lu})/u", $part, $firstChar)) {
                $initials .= $firstChar[0] . $punctuation . " ";
            }
        }

        return trim($initials);
    }
    /**
     * gets the "von" and "last" part from the author array
     *
     * @param string $author
     *
     * @return array (von, surname)
     */
    private function getVonLast($author) {
        $surname = $von = "";
        $tempVon = [];
        $count = 0;
        $bVon = FALSE;
        foreach ($author as $part) {
            $case = $this->getStringCase($part);
            if ($count == 0) {
                if ($case == "lower") {
                    $bVon = TRUE;
                    if ($case == "none") {
                        $count--;
                    }
                }
            }

            if ($bVon) {
                $tempVon[] = $part;
            }
            else {
                $surname = $surname . " " . $part;
            }

            $count++;
        }

        if (count($tempVon) > 0) {
            //find the first lowercase von starting from the end
            for ($i = (count($tempVon) - 1); $i > 0; $i--) {
                if ($this->getStringCase($tempVon[$i]) == "lower") {
                    break;
                }
                else {
                    $surname = array_pop($tempVon) . " " . $surname;
                }
            }

            if ($surname == "")
            { // von part was all lower chars, the last entry is surname
                $surname = array_pop($tempVon);
            }

            $von = implode(" ", $tempVon);
        }

        return [trim($von), trim($surname)];
    }
    /**
     * Explodes a string but not when the delimiter occurs within a pair of braces
     *
     * @param string $delimiter
     * @param string $val
     *
     * @return array
     */
    private function explodeString($delimiter, $val) {
        $bracelevel = $i = $j = 0;
        $len = mb_strlen($val);
        $dlen = mb_strlen($delimiter);

        $strings = [];
        while ($i < $len) {
            if ($val[$i] == '{') {
                $bracelevel++;
            }
            elseif ($val[$i] == '}') {
                $bracelevel--;
            }
            elseif (!$bracelevel) {
                if (mb_substr($val, $i, $dlen) == $delimiter) {
                    $strings[] = mb_substr($val, $j, $i - $j);
                    $j = $i + $dlen;
                    $i += ($dlen - 1);
                }
            }
            $i++;
        }
        $strings[] = mb_substr($val, $j);

        return $strings;
    }
    /**
     * returns the case of a string
     *
     * Case determination:
     * non-alphabetic chars are caseless
     * the first alphabetic char determines case
     * if a string is caseless, it is grouped to its neighbour string.
     *
     * @param string $string
     *
     * @return string
     */
    private function getStringCase($string) {
        $caseChar = "";
	// $string = preg_replace("/\d/", "", $string);
	$string = preg_replace("/\\p{N}/u", "", $string);
        if (preg_replace("/{/u", "", $string)) {
            $string = preg_replace("/({[^\\\\.]*})/u", "", $string);
        }
	
	// if (preg_match("/\w/", $string, $caseChar))
	if (preg_match("/\\p{L}/u", $string, $caseChar)) {
            if (is_array($caseChar)) {
                $caseChar = $caseChar[0];
            }
	
            // if (preg_match("/[a-z]/", $caseChar))
            if (preg_match("/\\p{Ll}/u", $caseChar)) {
                return "lower";
            }
	
            // else if (preg_match("/[A-Z]/", $caseChar))
            else if (preg_match("/\\p{Lu}/u", $caseChar)) {
                return "upper";
            }

            else {
                return "none";
            }

        }
        else {
            return "none";
        }
    }
    /**
     * separates initials from a firstname
     *
     * @param string $firstname
     *
     * @return array (firstname, initials)
     */
    private function separateInitialsFunc($firstname) {
        $name = $this->explodeString(" ", $firstname);
        $initials = [];
        $remain = [];
        foreach ($name as $part) {
            // if (preg_match("/[a-zA-Z]{2,}/u", trim($part)))
            if (preg_match("/\\.$/u", trim($part))) { 
                // find initials indicated by '.' at the end
                $initials[] = str_replace(".", " ", trim($part));
            }
            // if (preg_match("/\p{L}{2,}/u", trim($part))) // match unicode characters
            else {
                $remain[] = trim($part);
            }
            // else
            // $initials[] = str_replace(".", " ", trim($part));
        }
        if (isset($initials)) {
            $initials_ = '';
            foreach ($initials as $initial) {
                $initials_ .= ' ' . trim($initial);
            }

            $initials = $initials_;
        }
        $firstname = str_replace('.', '', implode(" ", $remain));

        return [$firstname, $initials];
    }
    /**
     * Format firstname
     *
     * @param string $firstname
     *
     * @return string
     */
    private function formatFirstname($firstname) {
        if ($firstname == "") {
            return "";
        }
        $name = $this->explodeString(".", $firstname);
        $formatName = "";
        $count = 1;
        $size = count($name);
        foreach ($name as $part) {
            $part = trim($part);

            if ($part != "") {
                $formatName .= $part;
                if ($count < $size)  {
                    //if the end of part contains an escape character (either just \ or \{, we do not add the extra space
                    if ( self::matchSuffix($part, "\\") || self::matchSuffix($part, "{")) {
                        $formatName .= ".";
                    }
                    else {
                        $formatName .= ". ";
                    }
                }
            }
            $count++;
        }

        return $formatName;
    }
    
    /**
     * Check if a suffix match against a string
     *
     * @param $string A string
     * @param $suffix A suffix
     *
     * @return bool
     */
    private static function matchSuffix($string, $suffix) {
        return (mb_strtolower(mb_substr($string, -mb_strlen($suffix))) == $suffix);
    }
}