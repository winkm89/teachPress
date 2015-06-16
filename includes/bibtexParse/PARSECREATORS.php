<?php
/**
 * This file contains the external class PARSECREATORS of bibtexParse
 * @package teachpress
 * @subpackage bibtexParse
 */

/*
Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

A collection of PHP classes to manipulate bibtex files.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net so that your improvements can be added to the release package.

Mark Grimshaw 2004/2005
http://bibliophile.sourceforge.net

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

class PARSECREATORS {
    
    public function __construct() {
    }
    
    /* Create writer arrays from bibtex input.
    'author field can be (delimiters between authors are 'and' or '&'):
    1. <first-tokens> <von-tokens> <last-tokens>
    2. <von-tokens> <last-tokens>, <first-tokens>
    3. <von-tokens> <last-tokens>, <jr-tokens>, <first-tokens>
    */
    function parse($input) {
        $input = trim($input);
        // split on ' and ' 
        $authorArray = preg_split("/\s(and|&)\s/i", $input);
        foreach($authorArray as $value) {
            $appellation = $prefix = $surname = $firstname = $initials = '';
            $this->prefix = array();
            $author = explode(",", preg_replace("/\s{2,}/", ' ', trim($value)));
            $size = count($author);
            // No commas therefore something like Mark Grimshaw, Mark Nicholas Grimshaw, M N Grimshaw, Mark N. Grimshaw
            if ( $size === 1 ) {
                // Is complete surname enclosed in {...}, unless the string starts with a backslash (\) because then it is
                // probably a special latex-sign.. 
                // 2006.02.11 DR: in the last case, any NESTED curly braces should also be taken into account! so second 
                // clause rules out things such as author="a{\"{o}}"
                if ( preg_match("/(.*){([^\\\].*)}/", $value, $matches) && !(preg_match("/(.*){\\\.{.*}.*}/", $value, $matches2)) ) {
                    $author = explode(" ", $matches[1]);
                    $surname = $matches[2];
                }
                else {
                    $author = explode(" ", $value);
                    // last of array is surname (no prefix if entered correctly)
                    $surname = array_pop($author);
                }
            }
            // Something like Grimshaw, Mark or Grimshaw, Mark Nicholas  or Grimshaw, M N or Grimshaw, Mark N.
            else if( $size === 2 ) {
                // first of array is surname (perhaps with prefix)
                list($surname, $prefix) = $this->grabSurname(array_shift($author));
            }
            // If $size is 3, we're looking at something like Bush, Jr. III, George W
            else {
                // middle of array is 'Jr.', 'IV' etc.
                $appellation = join(' ', array_splice($author, 1, 1));
                // first of array is surname (perhaps with prefix)
                list($surname, $prefix) = $this->grabSurname(array_shift($author));
            }
            $remainder = join(" ", $author);
            list($firstname, $initials) = $this->grabFirstnameInitials($remainder);
            if(!empty($this->prefix))
                    $prefix = join(' ', $this->prefix);
            $surname = $surname . ' ' . $appellation;
            $creators[] = array("$firstname", "$initials", "$surname", "$prefix");
        }
        if( isset( $creators ) ) {
            return $creators;
        }
        return FALSE;
    }
    // grab firstname and initials which may be of form "A.B.C." or "A. B. C. " or " A B C " etc.
    function grabFirstnameInitials($remainder) {
        $firstname = $initials = '';
        $array = explode(" ", $remainder);
        foreach($array as $value) {
            $firstChar = substr($value, 0, 1);
            if( (ord($firstChar) >= 97) && (ord($firstChar) <= 122) ) {
                $this->prefix[] = $value;
            }
            else if(preg_match("/[a-zA-Z]{2,}/", trim($value))) {
                $firstnameArray[] = trim($value);
            }
            else {
                $initialsArray[] = str_replace(".", " ", trim($value));
            }
        }
        if( isset($initialsArray) ) {
            foreach($initialsArray as $initial) {
                $initials .= ' ' . trim($initial);
            }
        }
        if( isset($firstnameArray) ) {
            $firstname = join(" ", $firstnameArray);
        }
        return array($firstname, $initials);
    }
    // surname may have title such as 'den', 'von', 'de la' etc. - characterised by first character lowercased.  Any 
    // uppercased part means lowercased parts following are part of the surname (e.g. Van den Bussche)
    function grabSurname($input) {
        $surnameArray = explode(" ", $input);
        $noPrefix = $surname = FALSE;
        foreach($surnameArray as $value) {
            $firstChar = substr($value, 0, 1);
            if (!$noPrefix && (ord($firstChar) >= 97) && (ord($firstChar) <= 122)) {
                $prefix[] = $value;
            }
            else {
                $surname[] = $value;
                $noPrefix = TRUE;
            }
        }
        if ( $surname ) {
            $surname = join(" ", $surname);
        }
        if( isset($prefix) ) {
            $prefix = join(" ", $prefix);
            return array($surname, $prefix);
        }
        return array($surname, FALSE);
    }
}
?>
