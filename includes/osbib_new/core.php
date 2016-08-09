<?php
/********************************
OSBiB Version 4:
An open source collection of PHP classes to create and manage bibliographic formatting for OS bibliography software using the OSBiB standard.

This is an updated version of the original OSBiB collection which was written by Mark Grimshaw and released through http://bibliophile.sourceforge.net under the GPL licence.

********************************/

// Includes
require_once 'stylemaps.php';
require_once 'parsexml.php';
require_once 'bibtexparse.php';
require_once 'utf8.php';

/** 
 * Format a bibliographic resource for output.
 * 
 * @author	Mark Grimshaw
 * @version	4.0
*/
class osbib_bibformat {
    public function __construct($bibtex = FALSE) {
        $this->creators = array('creator1', 'creator2', 'creator3', 'creator4', 'creator5');
        $this->bibtex = $bibtex;
        $this->preview = false; // Hack
        $this->preview = true;  // Hack
        $this->dir = dirname(__FILE__) . "/";   // Unused
        $this->bibtexParsePath  = $this->dir . "format/bibtexParse"; // Unused
        $this->output = 'html'; // default
        $this->previousCreator = '';
        $this->citationFootnote = FALSE;
        $this->footnotePages = FALSE;
        $this->footnoteType = FALSE;
        $this->cleanEntry = FALSE;
        // Switch editor and author positions in the style definition for a book in which there are only editors
        $this->editorSwitch = $this->dateMonthNoDay = FALSE;
        // Highlight preg pattern and CSS class for HTML display
        $this->patterns = FALSE;
        $this->patternHighlight = FALSE;
        if( $this->bibtex ) {
            $this->styleMap = new osbib_stylemapbibtex();
        }
        else {
            $this->styleMap = new osbib_stylemap();
        }
        $this->utf8 = new osbib_utf8();
    }
    
    /**
     * Read the chosen bibliographic style and create arrays based on resource type.
     * @param string $style     The name of the style: APA, HARVARD, CHICAGO, MLA, TURABIAN, IEEE
     * @param string $path      The path for the styles folder, default is "styles/"
     * @return array
     * @author	Mark Grimshaw
     * @version 2
     * @since 1.0
     */
    public function loadStyle($style, $path = 'styles/') {
        // Find and open file
        $uc = $path . strtolower($style) . ".xml";
        $lc = $path . strtoupper($style) . ".xml";
        $styleFile = file_exists($uc) ? $uc : $lc;
        if( !$fh = fopen($styleFile, "r") ) {
            return array(FALSE, FALSE, FALSE, FALSE, FALSE);
        }
        
        // Parse XML
        $parseXML = new osbib_parsexml($this);
        list($info, $citation, $footnote, $common, $types) = $parseXML->extractEntries($fh);
        
        // Close file
        fclose($fh);
        
        // Return
        return array($info, $citation, $footnote, $common, $types);
    }
    
    /**
     * Transform the raw data from the XML file into usable arrays
     *
     * @author	Mark Grimshaw
     * @version	1
     *
     * @param	$common		Array of global formatting data
     * @param	$types		Array of style definitions for each resource type
     * @param	$footnote	Array of style definitions for footnote creators
    */
    public function getStyle($common, $types, $footnote) {
        $this->commonToArray($common);
        $this->footnoteToArray($footnote);
        $this->typesToArray($types);
        /**
        * Load localisations etc.
        */
        $this->loadArrays();
    }
    
    /**
    * Format creator name lists (authors, editors, etc.)
    * 
    * @author	Mark Grimshaw
    * @version	1
    * 
    * @param	$creators	Multi-associative array of creator names e.g. this array might be of 
    * the primary authors:
    * <pre>
    *	array([0] => array(['surname'] => 'Grimshaw', ['firstname'] => Mark, ['initials'] => 'N', ['prefix'] => ),
    *	   [1] => array(['surname'] => 'Witt', ['firstname'] => Jan, ['initials'] => , ['prefix'] => 'de'))
    * </pre>
    * @param	$nameType	'creator1', 'creator2' etc.
    * @param	$shortFootnote.  If TRUE, this is being used for just the primary creator names in a footnote style citation using Ibid, Idem, op cit. etc.
    * @return	Optional if $nameType == 'citation': formatted string of all creator names in the input array.
    */
    function formatNames($creators, $nameType, $shortFootnote = FALSE) {
        $style = $this->citationFootnote ? $this->footnoteStyle : $this->style;
        $first = TRUE;
        /**
        * Citation creators
        */
        if( $nameType == 'citation' ) {
            $limit = 'creatorListLimit';
            $moreThan = 'creatorListMore';
            $abbreviation = 'creatorListAbbreviation';
            $initialsStyle = 'creatorInitials';
            $firstNameInitial = 'creatorFirstName';
            $delimitTwo = 'twoCreatorsSep';
            $delimitFirstBetween = 'creatorSepFirstBetween';
            $delimitNextBetween = 'creatorSepNextBetween';
            $delimitLast = 'creatorSepNextLast';
            $uppercase = 'creatorUppercase';
            $italics = 'creatorListAbbreviationItalic';
            $nameStyle = ( $first === true ) ? 'creatorStyle' : 'creatorOtherStyle';
	}
        /**
        * Primary creator
        */
        else if( $nameType == 'creator1' ) {
            $limit = 'primaryCreatorListLimit';
            $moreThan = 'primaryCreatorListMore';
            $abbreviation = 'primaryCreatorListAbbreviation';
            $initialsStyle = 'primaryCreatorInitials';
            $firstNameInitial = 'primaryCreatorFirstName';
            $delimitTwo = 'primaryTwoCreatorsSep';
            $delimitFirstBetween = 'primaryCreatorSepFirstBetween';
            $delimitNextBetween = 'primaryCreatorSepNextBetween';
            $delimitLast = 'primaryCreatorSepNextLast';
            $uppercase = 'primaryCreatorUppercase';
            $italics = 'primaryCreatorListAbbreviationItalic';
            $nameStyle = ( $first === true ) ? 'primaryCreatorFirstStyle' : 'primaryCreatorOtherStyle';
        }
        else
        {
            $limit = 'otherCreatorListLimit';
            $moreThan = 'otherCreatorListMore';
            $abbreviation = 'otherCreatorListAbbreviation';
            $initialsStyle = 'otherCreatorInitials';
            $firstNameInitial = 'otherCreatorFirstName';
            $delimitTwo = 'otherTwoCreatorsSep';
            $delimitFirstBetween = 'otherCreatorSepFirstBetween';
            $delimitNextBetween = 'otherCreatorSepNextBetween';
            $delimitLast = 'otherCreatorSepNextLast';
            $uppercase = 'otherCreatorUppercase';
            $italics = 'otherCreatorListAbbreviationItalic';
            $nameStyle = ( $first === true ) ? 'otherCreatorFirstStyle' : 'otherCreatorOtherStyle';
        }
        $type = $this->type;
        /**
        * Set default plural behaviour for creator lists
        */
        // For use with generic footnote templates which uses generic 'creator' field
        if($this->citationFootnote && ($nameType == 'creator1') && 
            ($this->styleMap->{$type}[$nameType] != 'creator')) {
                
            $pluralKey = "creator_plural";
        }
        else {
            $pluralKey = $nameType . "_plural";
        }
        $this->$pluralKey = FALSE;
        $firstInList = TRUE;
        $rewriteCreatorBeforeDone = $rewriteCreatorFinal = FALSE;
        foreach($creators as $creator) {
            if(array_key_exists('id', $creator)) {
                    $creatorIds[] = $creator['id'];
            }
            $firstName = trim($this->checkInitials($creator, $style[$initialsStyle], $style[$firstNameInitial]));
            $prefix = $creator['prefix'] ? trim(stripslashes($creator['prefix'])) . ' ' : '';
            if( $style[$nameStyle] == 0 ) { // Joe Bloggs
                $nameString = $firstName . ' ' . 
                    $prefix . 
                    stripslashes($creator['surname']);
            }
            else if($style[$nameStyle] == 1) { // Bloggs, Joe
                $prefixDelimit = $firstName ? ', ' : '';
                $nameString = 
                    stripslashes($creator['prefix']) . ' ' . 
                    stripslashes($creator['surname']) . $prefixDelimit . 
                    $firstName;
            }
            else if($style[$nameStyle] == 2) { // Bloggs Joe
                $nameString = 
                    stripslashes($creator['prefix']) . ' ' . 
                    stripslashes($creator['surname']) . ' ' . 
                    $firstName;
            }
            else { // Last name only
                $nameString = 
                    stripslashes($creator['prefix']) . ' ' . 
                    stripslashes($creator['surname']);
            }
            if(isset($style[$uppercase])) {
                $nameString = $this->utf8->utf8_strtoupper($nameString);
            }
            $nameString = trim($nameString);
            if($firstInList) {
                $rewriteCreatorField = $nameType . "_firstString";
                $rewriteCreatorFieldBefore = $nameType . "_firstString_before";
            }
            else {
                $rewriteCreatorField = $nameType . "_remainderString";
                $rewriteCreatorFieldBefore = $nameType . "_remainderString_before";
                $rewriteCreatorFieldEach = $nameType . "_remainderString_each";
            }

            if(array_key_exists($rewriteCreatorField, $this->$type)) {
                if($firstInList) {
                    if(array_key_exists($rewriteCreatorFieldBefore, $this->$type)) {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                    }
                    else {
                        $nameString .= $this->{$type}[$rewriteCreatorField];
                    }
                    $firstInList = FALSE;
                }
                else if(array_key_exists($rewriteCreatorFieldEach, $this->$type)) {
                    if(array_key_exists($rewriteCreatorFieldBefore, $this->$type)) {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                    }
                    else {
                        $nameString .= $this->{$type}[$rewriteCreatorField];
                    }
                //print "$nameString<P>";
                }
                else {
                    if(!$rewriteCreatorBeforeDone && array_key_exists($rewriteCreatorFieldBefore, $this->$type)) {
                        $nameString = $this->{$type}[$rewriteCreatorField] . $nameString;
                        $rewriteCreatorBeforeDone = TRUE;
                    }
                    else if(!$rewriteCreatorBeforeDone && 
                        !array_key_exists($rewriteCreatorFieldEach, $this->$type)) {
                        $rewriteCreatorFinal = $this->{$type}[$rewriteCreatorField];
                    }
                }
            }
            //print "$nameString<P>";
            $cArray[] = $nameString;
            $first = FALSE;
        }
        /**
        * Keep only some elements in array if we've exceeded $moreThan
        */
        $etAl = FALSE;
        if($style[$limit] && (sizeof($cArray) > $style[$moreThan])) {
            array_splice($cArray, $style[$limit]);
            if(isset($style[$italics])) {
                $etAl = "[i]" . $style[$abbreviation] . "[/i]";
            }
            else {
                $etAl = $style[$abbreviation];
            }
        }
        /**
        * add delimiters
        */
        if(sizeof($cArray) > 1) {
            if(sizeof($cArray) == 2) {
                $cArray[0] .= $style[$delimitTwo];
            }
            else {
                for($index = 0; $index < (sizeof($cArray) - 2); $index++) {
                    if(!$index) {
                        $cArray[$index] .= $style[$delimitFirstBetween];
                    }
                    else {
                        $cArray[$index] .= $style[$delimitNextBetween];
                    }
                }
                $cArray[sizeof($cArray) - 2] .= $style[$delimitLast];
            }
        }
        /**
        * If sizeof of $cArray > 1 or $etAl != FALSE, set this $nameType_plural to TRUE
        */
        if((sizeof($cArray) > 1) || $etAl) {
            //	$pluralKey = $nameType . "_plural";
            $this->$pluralKey = TRUE;
        }
        /**
        * Finally flatten array
        */
        if($etAl) {
            $pString = implode('', $cArray) . $etAl;
        }
        else {
            $pString = implode('', $cArray);
        }
        if($rewriteCreatorFinal) {
            $pString .= $rewriteCreatorFinal;
        }
        /**
        * Check for repeating primary creator list in subsequent bibliographic item.
        */
        if($nameType == 'creator1') {
            $tempString = $pString;
            if(($style['primaryCreatorRepeat'] == 2) && ($this->previousCreator == $pString)) {
                $pString = $style['primaryCreatorRepeatString'];
            }
            else if(($style['primaryCreatorRepeat'] == 1) && ($this->previousCreator == $pString)) {
                $pString = ''; // don't print creator list
            }
            $this->previousCreator = $tempString;
        }
        if($shortFootnote) {
            return array($pString, $creatorIds);
        }
        
        // For use with generic footnote templates, we must also place 'creator1' string (if not called 'creator') into the 'creator' slot
        if(($nameType == 'creator1') && ($this->styleMap->{$type}[$nameType] != 'creator')) {
            $this->item['creator'] = $pString;
        }
        $this->item[$this->styleMap->{$type}[$nameType]] = $pString;
    }
        
    /**
    * Format pages.
    * $this->style['pageFormat']:
    * 0 == 132-9
    * 1 == 132-39
    * 2 == 132-139
    * 
    * @author	Mark Grimshaw
    * @version	1
    *
    * @param	$start		Page start.
    * @param	$end		Page end.
    * @param	$citation	If called from CITEFORMAT, this is the array of citation stylings.
    * @return	string of pages.
    */
    function formatPages($start, $end = FALSE, $citation = FALSE) {
        $type = $this->type;
        $style = $citation ? $citation : $this->style;
        /**
        * Set default plural behaviour for pages
        */
        $this->pages_plural = FALSE;
        /**
        * If no page end, return just $start;
        */
        if(!$end) {
            $this->item[$this->styleMap->{$type}['pages']] = $start;
            return;
        }
        /**
        * Pages may be in roman numeral format etc.  Return unchanged
        */
        if(!is_numeric($start)) {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
            return;
        }
        /**
        * We have multiple pages...
        */
        $this->pages_plural = TRUE;
        /**
        * They've done something wrong so give them back exactly what they entered
        */
        if(($end <= $start) || (strlen($end) < strlen($start))) {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
            return;
        }
        else if($style['pageFormat'] == 2) {
            $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
            return;
        }
	else {
            /**
            * We assume page numbers are not into the 10,000 range - if so, return the complete pages
            */
            if(strlen($start) <= 4) {
                $startArray = preg_split('//', $start);
                array_shift($startArray); // always an empty element at start?
                array_pop($startArray); // always an empty array element at end?
                if($style['pageFormat'] == 0) {
                    array_pop($startArray);
                    $endPage = substr($end, -1);
                    $index = -2;
                }
                else {
                    array_pop($startArray);
                    array_pop($startArray);
                    $endPage = substr($end, -2);
                    $index = -3;
                }
                while(!empty($startArray)) {
                    $startPop = array_pop($startArray);
                    $endSub = substr($end, $index--, 1);
                    if($endSub == $startPop) {
                        $this->item[$this->styleMap->{$type}['pages']] = $start . '-' . $endPage;
                        return;
                    }
                    if($endSub > $startPop) {
                        $endPage = $endSub . $endPage;
                    }
                }
            }
            else {
                $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
                return;
            }
        }
        /**
        * We should never reach here - in case we do, give back complete range so that something at least is printed
        */
        $this->item[$this->styleMap->{$type}['pages']] = $start . 'WIKINDX_NDASH' . $end;
    }
        
    /**
    * Format a title.  Anything enclosed in $delimitLeft...$delimitRight is to be left unchanged
    *
    * @author	Mark Grimshaw
    * @version	1
    *
    * @param	$pString	Raw title string.
    * @param	$delimitLeft
    * @param	$delimitRight
    * @return	Formatted title string.
    */
    function formatTitle($pString, $delimitLeft = FALSE, $delimitRight = FALSE) {
        if(!$delimitLeft) {
            $delimitLeft = '{';
        }
        if(!$delimitRight) {
            $delimitRight = '}';
        }
        $delimitLeft = preg_quote($delimitLeft);
        $delimitRight = preg_quote($delimitRight);
        $match = "/" . $delimitLeft . "/";
        $type = $this->type;
        if(!array_key_exists('title', $this->styleMap->$type)) {
            $this->item[$this->styleMap->{$type}['title']] = '';
        }
        /**
        * '0' == 'Osbib Bibliographic Formatting'
        * '1' == 'Osbib bibliographic formatting'
        */
        if( $this->style['titleCapitalization'] ) {
            // Something here (preg_split probably) interferes with UTF-8 encoding (data is stored in 
            // the database as UTF-8 as long as web browser charset == UTF-8).  
            // So first decode then encode back to UTF-8 at end.
            // There is a 'u' UTF-8 parameter for preg_xxx but it doesn't work.
            $pString = $this->utf8->decodeUtf8($pString);
            $newString = '';
            while( preg_match($match, $pString) ) {
                $array = preg_split("/(.*)$delimitLeft(.*)$delimitRight(.*)/U", 
                $pString, 2, PREG_SPLIT_DELIM_CAPTURE);
                /**
                * in case user has input {..} incorrectly
                */
                if(sizeof($array) == 1) {
                    break;
                }
                $newString .= $this->utf8->utf8_strtolower($this->utf8->encodeUtf8($array[1])) . $array[2];
                $pString = $array[4];
            }
            $newString .= $this->utf8->utf8_strtolower($this->utf8->encodeUtf8($pString));
        }
        $pString = isset($newString) ? $newString : $pString;
        $title = $this->utf8->utf8_ucfirst(trim($pString));
        $this->item[$this->styleMap->{$type}['title']] = $title;
    }
        
    /**
    * Add an item to $this->item array
    *
    * @author	Mark Grimshaw
    * @version	1
    *
    * @param	$item		The item to be added.
    * @param	$fieldName	The database fieldName of the item to be added
    */
    function addItem($item, $fieldName) {
        $type = $this->type;
        if( $item === FALSE ) {
            return;
        }
        $item = stripslashes($item);
        /**
        * This item may already exist (e.g. edition field for WIKINDX)
        */
        if( isset($this->item) && array_key_exists($this->styleMap->{$type}[$fieldName], $this->item )) {
            return FALSE;
        }
        $this->item[$this->styleMap->{$type}[$fieldName]] = $item;
    }
    /**
    * Add all remaining items to $this->item array
    *
    * @author	Mark Grimshaw
    * @version	1
    *
    * @param	$row		The items to be added.
    */
    function addAllOtherItems($row) {
        $type = $this->type;
        foreach($row as $field => $value) {
            if( array_key_exists($field, $this->styleMap->$type) && 
                !array_key_exists($this->styleMap->{$type}[$field], $this->item) ) {

                $item = stripslashes($row[$field]);
                $this->addItem($item, $field);
            }
        }
    }
        
    /**
    * Handle initials.
    * @see formatNames()
    * 
    * @author	Mark Grimshaw
    * @version	1
    * 
    * @param	$creator	Associative array of creator name e.g.
    * <pre>
    *	array(['surname'] => 'Grimshaw', ['firstname'] => Mark, ['initials'] => 'M N G', ['prefix'] => ))
    * </pre>
    * Initials must be space-delimited.
    *
    * @param	$initialsStyle
    * @param	$firstNameInitial
    * @return	Formatted string of initials.
    */
    function checkInitials(&$creator, $initialsStyle, $firstNameInitial) {
        /**
        * Format firstname
        */
        if( $creator['firstname'] && !$firstNameInitial ) { // Full name
            $firstName = stripslashes($creator['firstname']);
        }
        else if($creator['firstname']) { // Initial only of first name.  'firstname' field may actually have several 'firstnames'
            $fn = explode(" ", stripslashes($creator['firstname']));
            $firstTime = TRUE;
            foreach($fn as $name) {
                if( $firstTime ) {
                    $firstNameInitialMake = $this->utf8->utf8_strtoupper($this->utf8->utf8_substr(trim($name), 0, 1));
                    $firstTime = FALSE;
                }
                else {
                    $initials[] = $this->utf8->utf8_strtoupper($this->utf8->utf8_substr(trim($name), 0, 1)); 
                }
            }
            if(isset($initials)) {
                if($creator['initials']) {
                    $creator['initials'] = join(" " , $initials) . ' ' . $creator['initials']; 

                }
                else {
                    $creator['initials'] = join(" " , $initials);
                }
            }
        }
        /**
        * Initials are stored as space-delimited characters.
        * If no initials, return just the firstname or its initial in the correct format.
        */
        if(!$creator['initials']) {
                if( isset($firstName) ) {	// full first name only
                    return $firstName;
                }
                if( isset($firstNameInitialMake) && $initialsStyle > 1 ) { // First name initial with no '.'
                    return $firstNameInitialMake;
                }
                if( isset($firstNameInitialMake) ) { // First name initial with  '.'
                    return $firstNameInitialMake . '.';
                }
                return ''; // nothing here
        }
        $initialsArray = explode(' ', $creator['initials']);
        /**
        * If firstname is initial only, prepend to array
        */
        if(isset($firstNameInitialMake)) {
            array_unshift($initialsArray, $firstNameInitialMake);
        }
        if($initialsStyle == 0) { // 'T. U. '
            $initials = implode('. ', $initialsArray) . '.';
        }
        else if($initialsStyle == 1) { // 'T.U.'
            $initials = implode('.', $initialsArray) . '.';
        }
        else if($initialsStyle == 2) { // 'T U '
            $initials = implode(' ', $initialsArray);
        }
        else { // 'TU '
            $initials = implode('', $initialsArray);
        }
        /**
        * If we have a full first name, prepend it to $initials.
        */
        if(isset($firstName)) {
            return ($firstName . ' ' . $initials);
        }
        return $initials;
    }    
    
    /**
     * Reformat the array representation of common styling into a more useable format.
     * 'common' styling refers to formatting that is common to all resource types such as creator formatting, title 
     * capitalization etc.
     *
     * @author	Mark Grimshaw
     * @version	1
     *
     * @param	$common		nodal array representation of XML data
     * @return	flattened array representation for easier use.
    */
    public function commonToArray($common) {
        foreach($common as $array) {
            if( array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array) ) {
                $this->style[$array['_NAME']] = $array['_DATA'];
            }
        }
    }
    
    /**
     * Reformat the array representation of footnote resource styling into a more useable format.
     *
     * @author	Mark Grimshaw
     * @version	1
     *
     * @param	$footnote		nodal array representation of XML data
     * @return	flattened array representation for easier use.
    */
    function footnoteToArray($footnote) {
        foreach($footnote as $array) {
            if(array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array)) {
                if($array['_NAME'] != 'resource') {
                    $this->footnoteStyle[$array['_NAME']] = $array['_DATA'];
                }
		else if(array_key_exists('_ELEMENTS', $array) && !empty($array['_ELEMENTS'])) {
                    $footnoteType = "footnote_" . $array['_ATTRIBUTES']['name'];
                    foreach($array['_ELEMENTS'] as $fArray) {
			if($fArray['_NAME'] == 'ultimate') {
                            $this->{$footnoteType}['ultimate'] = $fArray['_DATA'];
                            continue;
                        }
                        if( $fArray['_NAME'] == 'preliminaryText' ) {
                            $this->{$footnoteType}['preliminaryText'] = $fArray['_DATA'];
                            continue;
                        }
                        foreach($fArray['_ELEMENTS'] as $elements) {
                            if($fArray['_NAME'] == 'independent') {
                                $split = explode("_", $elements['_NAME']);
                                $this->{$footnoteType}[$fArray['_NAME']][$split[1]] = $elements['_DATA'];
                            }
                            else {
                                    $this->{$footnoteType}[$fArray['_NAME']][$elements['_NAME']] = $elements['_DATA'];
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Reformat the array representation of resource types into arrays based on the type.
     *
     * @param	$types		nodal array representation of XML data
    */
    function typesToArray($types) {
        foreach($types as $resourceArray) {
            /**
            * The resource type which will be our array name
            */
            $type = $resourceArray['_ATTRIBUTES']['name'];
            $this->rewriteCreatorsToArray($type, $resourceArray);
            $styleDefinition = $resourceArray['_ELEMENTS'];
            foreach($styleDefinition as $array) {
                if( array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array) && array_key_exists('_ELEMENTS', $array) ) {
                    if($array['_NAME'] == 'fallbackstyle') {
                        $this->fallback[$type] = $array['_DATA'];
                        break;
                    }
                    if($array['_NAME'] == 'ultimate') {
                        $this->{$type}['ultimate'] = $array['_DATA'];
                        continue;
                    }
                    if($array['_NAME'] == 'preliminaryText') {
                        $this->{$type}['preliminaryText'] = $array['_DATA'];
                        continue;
                    }
                    foreach($array['_ELEMENTS'] as $elements) {
                        $data = $elements['_DATA'];
                        if($array['_NAME'] == 'independent') {
                            $split = explode("_", $elements['_NAME']);
                            $this->{$type}[$array['_NAME']][$split[1]] = $data;
                        }
                        else {
                            $this->{$type}[$array['_NAME']][$elements['_NAME']] = $data;
                        }
                    }
                }
            }
            /**
            * Backup each $this->$type array.  If we need to switch editors, it's faster to restore each 
            * $this->$type array from this backup than to reload the style file and parse it.
            */
            if( isset($this->$type) ) {
                $this->backup[$type] = $this->$type;
            }
        }
    }
    
    /**
     * Add resource-specific rewrite creator fields to $this->$type array
     *
     * @author	Mark Grimshaw
     * @version	1
    */
    function rewriteCreatorsToArray($type, $array){
        foreach($this->creators as $creatorField) {
            $name = $creatorField . "_firstString";
            if(array_key_exists($name, $array['_ATTRIBUTES'])) {
                $this->{$type}[$name] = $array['_ATTRIBUTES'][$name];
            }
            $name = $creatorField . "_firstString_before";
            if(array_key_exists($name, $array['_ATTRIBUTES'])) {
                $this->{$type}[$name] = $array['_ATTRIBUTES'][$name];
            }
            $name = $creatorField . "_remainderString";
            if(array_key_exists($name, $array['_ATTRIBUTES'])) {
                $this->{$type}[$name] = $array['_ATTRIBUTES'][$name];
            }
            $name = $creatorField . "_remainderString_before";
            if(array_key_exists($name, $array['_ATTRIBUTES'])) {
                $this->{$type}[$name] = $array['_ATTRIBUTES'][$name];
            }
            $name = $creatorField . "_remainderString_each";
            if(array_key_exists($name, $array['_ATTRIBUTES'])) {
                $this->{$type}[$name] = $array['_ATTRIBUTES'][$name];
            }
        }
    }
    /**
    * Localisations etc.
    * @author	Mark Grimshaw
    * @version	1
    */
    public function loadArrays() {
        // Defaults
        $this->longMonth = array(
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
        $this->shortMonth = array(
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
        $this->titleSubtitleSeparator = ": ";
    }
    
    /**
     * Perform pre-processing on the raw SQL array
     *
     * @author	Mark Grimshaw
     * @version	1
     *
     * @param	$type	The resource type
     * @param	$row	Associate array of raw SQL data
     * @return	$row	Processed row of raw SQL data
    */
    function preProcess($type, $row) {
        /**
        * Ensure that $this->item is empty for each resource!!!!!!!!!!
        */
        $this->item = array();
        // Map this system's resource type to OSBib's resource type
        $this->type = array_search($type, $this->styleMap->types);
        if( $this->bibtex && array_key_exists('author', $row) ) {
            $row['creator1'] = $row['author'];
            unset($row['author']);
        }
        if( $this->bibtex && array_key_exists('editor', $row) ) {
            $row['creator2'] = $row['editor'];
            unset($row['editor']);
        }
        /**
        * Set any author/editor re-ordering for book and book_article type.
        */
        if( !$this->preview && (($type == 'book') || ($type == 'book_article')) && 
            $row['creator2'] && !$row['creator1'] && $this->style['editorSwitch'] &&
            array_key_exists('author', $this->$type) )
        {
            $row['creator1'] = $row['creator2'];
            $row['creator2'] = FALSE;
            $editorArray = osbib_parsestyle::parseStringToArray($type, $this->style['editorSwitchIfYes'], $this->styleMap);
            if(!empty($editorArray) && array_key_exists('editor', $editorArray)) {
                $this->{$type}['author'] = $editorArray['editor'];
                unset($this->{$type}['editor']);
                $this->editorSwitch = TRUE;
            }
        }
        // print_r($type);
        if( $this->style['dateMonthNoDay'] && array_key_exists('date', $this->styleMap->$type) && 
                array_key_exists('dateMonthNoDayString', $this->style) && $this->style['dateMonthNoDayString'] )
        {
            $this->dateArray = osbib_parsestyle::parseStringToArray($type, $this->style['dateMonthNoDayString'], $this->styleMap, TRUE);
            $this->dateMonthNoDay = TRUE;
        }
        /**
        * If $row comes in BibTeX format, process and add items to $this->item
        */
	if( $this->bibtex ) {
            if( !$this->type ) {
                list($type, $row) = $this->preProcessBibtex($row, $type);
            } else {
                list($type, $row) = $this->preProcessBibtex($row, $this->type);
            }
        }
        /**
        * Ensure that for theses types, the first letter of type and label are capitalized 
        * (e.g. 'Master's Thesis').
        */
        if( $type == 'thesis' ) {
            if( ($key = array_search('type', $this->styleMap->$type)) !== FALSE ) {
                if(isset($row[$key])) {
                    $row[$key] = ucfirst($row[$key]);
                }
            }
            if(($key = array_search('label', $this->styleMap->$type)) !== FALSE) {
                if(isset($row[$key])) {
                    $row[$key] = ucfirst($row[$key]);
                }
            }
        }
        /**
        * Set to catch-all generic style.  For all keys except named database fields, creator1 and year1, 
        * we only print if the value in $this->styleMap matches the value in 
        * $this->styleMap->generic for each key.
        */
        
        // using footnote template
        if( $this->citationFootnote ) { 
		
            $footnoteType = 'footnote_' . $type;
            // footnote template for this resource exists
            if( isset($this->$footnoteType) ) {
                $this->footnoteType = $footnoteType;
                $this->footnoteTypeArray[$type] = $footnoteType;
            }
            else {
                $footnoteType = 'footnote_' . $this->fallback[$type];
                // fallback footnote template exists
                if( isset($this->$footnoteType) ) {
                    $this->footnoteType = $footnoteType;
                    $this->footnoteTypeArray[$type] = $footnoteType;
                }
                // use fallback bibliography template
                else if( !isset($this->$type) ) {
                    $fallback = $this->fallback[$type];
                    $this->footnoteTypeArray[$type] = $fallback;
                    $type = $fallback;
                }
                // else, we're using the bibliography template for this resource type
                else {
                    $this->footnoteTypeArray[$type] = $type;
                }
            }
        }
        else {
            if( !isset($this->$type) ) {
                $fallback = $this->fallback[$type];
                $type = $fallback;
            }
        }
        $this->type = $type;
        /**
        * Add BibTeX entry to $this->item
        */
        if( $this->bibtex ) {
            foreach($row as $field => $value) {
                if( array_key_exists($field, $this->styleMap->$type) && !array_key_exists($this->styleMap->{$type}[$field], $this->item) ) {
                    $this->addItem($row[$field], $field);
                }
            }
        }
        return $row;
    }
    
    /**
    * Preprocess BibTeX-type entries
    * @author Mark Grimshaw
    * @version 1
    *
    * @param assoc. array of elements for one bibtex entry
    * @param string resource type
    * @return string resource type
    * @return array resource assoc. array of elements for one bibtex entry
    */
    function preProcessBibtex(&$row, $type) {
        //05/05/2005 G.GARDEY: change bibtexParse name.
        /**
        * This set of includes is for the OSBib public release and should be uncommented for that and
        * the WIKINDX-specific includes below commented out!
        */
        $parseCreator = new osbib_parsecreators();
        $parseDate = new osbib_parsemonth();
        $parsePages = new osbib_parsepage();

        // Added by Christophe Ambroise: convert the bibtex entry to utf8 (for storage or printing)
        if($this->cleanEntry) {
            $row = $this->convertEntry($row);
        }
        /**
        * Bibtex-specific types not defined in STYLEMAPBIBTEX
        */
        if(!$this->type) {
            if($type == 'mastersthesis') {
                $type = 'thesis';
                $row['type'] = "Master's Dissertation";
            }
            if($type == 'phdthesis') {
                $type = 'thesis';
                $row['type'] = "PhD Thesis";
            }
            else if($type == 'booklet') {
                $type = 'miscellaneous';
            }
            else if($type == 'conference') {
                $type = 'proceedings_article';
            }
            else if($type == 'incollection') {
                $type = 'book_article';
            }
            else if($type == 'manual') {
                $type = 'report';
            }
        }
        /**
        * 'article' could be journal, newspaper or magazine article
        */
        else if($type == 'journal_article') {
            if(array_key_exists('month', $row) && array_key_exists('date', $this->styleMap->$type)) {
                list($startMonth, $startDay, $endMonth, $endDay) = $parseDate->init($row['month']);
                if($startDay) {
                    $type = 'newspaper_article';
                }
                else if($startMonth) {
                    $type = 'magazine_article';
                }
                $this->formatDate($startDay, $startMonth, $endDay, $endMonth);
            }
            else {
                $type = 'journal_article';
            }
        }
        /**
        * Is this a web article?
        */
        else if(($type == 'miscellaneous') && array_key_exists('howpublished', $row)) {
            if(preg_match("#^\\\url{(.*://.*)}#", $row['howpublished'], $match)) {
                $row['URL'] = $match[1];
                $type = 'web_article';
            }
        }
        $this->type = $type;
        if(array_key_exists('creator1', $row) && $row['creator1'] && 
                array_key_exists('creator1', $this->styleMap->$type))
        {
            $creators = $parseCreator->parse($row['creator1']);
            foreach($creators as $cArray) {
                $temp[] = array(
                        'surname'	=>	trim($cArray[2]),
                        'firstname'	=>	trim($cArray[0]),
                        'initials'	=>	trim($cArray[1]),
                        'prefix'	=>	trim($cArray[3]),
                        );
            }
            $this->formatNames($temp, 'creator1');
            unset($temp);
        }
        if(array_key_exists('creator2', $row) && $row['creator2'] && 
                array_key_exists('creator2', $this->styleMap->$type))
{
            $creators = $parseCreator->parse($row['creator2']);
            foreach($creators as $cArray) {
                $temp[] = array(
                            'surname'	=>	trim($cArray[2]),
                            'firstname'	=>	trim($cArray[0]),
                            'initials'	=>	trim($cArray[1]),
                            'prefix'	=>	trim($cArray[3]),
                        );
            }
            $this->formatNames($temp, 'creator2');
        }
        if(array_key_exists('pages', $row) && array_key_exists('pages', $this->styleMap->$type)) {
            list($start, $end) = $parsePages->init($row['pages']);
            $this->formatPages(trim($start), trim($end));
        }
        $this->formatTitle($row['title'], "{", "}");
        return array($type, $row);
    }
    
    /**
     * Map the $item array against the style array ($this->$type) for this resource type and 
     * produce a string ready to be formatted for bold, italics etc.
     * 
     * @author	Mark Grimshaw
     * @version	1
     *
     * @param	$template	If called from CITEFORMAT, this is the array of template elements.
     * @return	string ready for printing to the output medium.
    */
    function map( $template = FALSE ) {
        /**
        * Output medium:
        * 'html', 'rtf', or 'plain'
        */
	$this->export = new osbib_exportfilter($this, $this->output);
        // Don't think $template is used anymore
	if( $template ){
            $this->citation = $template;
            $this->type = 'citation';
        }
        $type = $pluralType = $this->type;
        if( $this->footnoteType ) {
            $type = $this->footnoteType;
            $this->footnoteType = FALSE;
        }
        $ultimate = $preliminary = '';
        $index = 0;
        $previousFieldExists = $nextFieldExists = TRUE;
        if( array_key_exists('independent', $this->$type) ) {
            $independent = $this->{$type}['independent'];
        }
        /**
        * For dependency on next field, we must grab array keys of $this->$type, shift the first element then, in the loop, 
        * check each element exists in $item.  If it doesn't, $nextFieldExists is set to FALSE
        */
        $checkPost = array_keys($this->$type);
        array_shift($checkPost);
        $lastFieldKey = FALSE;
        // Add or replace pages field if this process is called from CTIEFORMAT for footnotes where $this->footnotePages are the formatted citation pages.
        if($this->footnotePages) {
            $this->item['pages'] = $this->footnotePages;
        }
        foreach($this->$type as $key => $value) {
            if( $key == 'ultimate' ) {
                $ultimate = $value;
                continue;
            }
            if( $key == 'preliminaryText' ) {
                $preliminary = $value;
                continue;
            }
            if( !array_key_exists($key, $this->item) || !$this->item[$key] ) {
                $keyNotExists[] = $index;
                $index++;
                array_shift($checkPost);
                $previousFieldExists = FALSE;
                continue;
            }
            $checkPostShift = array_shift($checkPost);
            if( !array_key_exists($checkPostShift, $this->item) || !$this->item[$checkPostShift] ) {
                $nextFieldExists = FALSE;
            }
            $pre = array_key_exists('pre', $value) ? $value['pre'] : '';
            $post = array_key_exists('post', $value) ? $value['post'] : '';
            /**
            * Deal with __DEPENDENT_ON_PREVIOUS_FIELD__ for characters dependent on previous field's existence and 
            * __DEPENDENT_ON_NEXT_FIELD__ for characters dependent on the next field's existence
            */
            if( $previousFieldExists && array_key_exists('dependentPre', $value) ) {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/", $value['dependentPre'], $pre);
            }
            else if( array_key_exists('dependentPreAlternative', $value) ) {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/", $value['dependentPreAlternative'], $pre);
            }
            else {
                $pre = preg_replace("/__DEPENDENT_ON_PREVIOUS_FIELD__/", '', $pre); 
            }
            if( $nextFieldExists && array_key_exists('dependentPost', $value) ) {
                $post = str_replace("__DEPENDENT_ON_NEXT_FIELD__", $value['dependentPost'], $post);
            }
            else if( array_key_exists('dependentPostAlternative', $value) ) {
                $post = preg_replace("/__DEPENDENT_ON_NEXT_FIELD__/", 
                $value['dependentPostAlternative'], $post);
            }
            else {
                $post = preg_replace("/__DEPENDENT_ON_NEXT_FIELD__/", '', $post);
            }
            /**
            * Deal with __SINGULAR_PLURAL__ for creator lists and pages
            */			
            if( $styleKey = array_search($key, $this->styleMap->$pluralType) ) {
		$pluralKey = $styleKey . "_plural";
            }
            // For use with generic footnote templates which uses generic 'creator' field
            else {
                $pluralKey = "creator_plural";
            }
            // plural alternative for this key
            if( isset($this->$pluralKey) && $this->$pluralKey )  {
                $pre = array_key_exists('plural', $value) ? 
                        preg_replace("/__SINGULAR_PLURAL__/", $value['plural'], $pre) : $pre;
                $post = array_key_exists('plural', $value) ? 
                        preg_replace("/__SINGULAR_PLURAL__/", $value['plural'], $post) : $post;
            }
            // singular alternative for this key
            else if( isset($this->$pluralKey) ) {
                $pre = array_key_exists('singular', $value) ? 
                        preg_replace("/__SINGULAR_PLURAL__/", $value['singular'], $pre) : $pre;
                $post = array_key_exists('singular', $value) ? 
                        preg_replace("/__SINGULAR_PLURAL__/", $value['singular'], $post) : $post;
            }
            // Deal with en dash characters in pages
            if( $key == 'pages' ) {
                $this->item[$key] = $this->export->format($this->item[$key]);
            }
            /**
            * Strip backticks used in template
            */
            $pre = str_replace("`", '', $pre);
            $post = str_replace("`", '', $post);
            /**
            * Make sure we don't have multiple punctuation characters after a field
            */			
            $lastPre = substr($post, -1);
            $firstItem = substr($this->item[$key], 0, 1);
            if( $firstItem === $lastPre ) {
                $this->item[$key] = substr($this->item[$key], 1);
            }
            // Match last character of this field with $post
            if( $post && preg_match("/[.,;:?!]$/", $this->item[$key]) &&
                    preg_match("/^(\[.*?[\]]+)*([.,;:?!])|^([.,;:?!])/", $post, $capture, PREG_OFFSET_CAPTURE) ) {
                // There is punctuation in post either immediately following BBCode formatting or at the start of the string.
                // The offset for the punctuation character in $post is given at $capture[2][1]
                $post = substr_replace($post, '', $capture[2][1], 1);
            }
            // Match $itemArray[$lastFieldKey] with $pre
            if(($lastFieldKey !== FALSE) && $pre && preg_match("/^[.,;:?!]/", $pre) && 
                    preg_match("/([.,;:?!])(\[.*?[\]]+)*$|([.,;:?!])$/", 
                $itemArray[$lastFieldKey], $capture, PREG_OFFSET_CAPTURE)) {
                // There is punctuation in post either immediately following BBCode formatting or at the start of the string.
                $pre = substr_replace($pre, '', 0, 1);
            }
            if( $this->item[$key] ) {
                $itemArray[$index] = $pre . $this->item[$key] . $post;
                $lastFieldKey = $index;
            }
            $previousFieldExists = $nextFieldExists = TRUE;
            $index++;
        }
        /**
        * Check for independent characters.  These (should) come in pairs.
        */		
        if( isset($independent) ) {
            $independentKeys = array_keys($independent);
            while($independent) {
                $preAlternative = $postAlternative = FALSE;
                $startFound = $endFound = FALSE;
                $pre = array_shift($independent);
                $post = array_shift($independent);
		if( preg_match("/%(.*)%(.*)%|%(.*)%/U", $pre, $dependent) ) {
                    if( sizeof($dependent) == 4 ) {
                        $pre = $dependent[3];
                    }
                    else {
                        $pre = $dependent[1];
                        $preAlternative = $dependent[2];
                    }
                }
                if( preg_match("/%(.*)%(.*)%|%(.*)%/U", $post, $dependent) ) {
                    if(sizeof($dependent) == 4) {
                        $post = $dependent[3];
                    }
                    else {
                        $post = $dependent[1];
                        $postAlternative = $dependent[2];
                    }
                }
                /**
                * Strip backticks used in template
                */
                $preAlternative = str_replace("`", '', $preAlternative);
                $postAlternative = str_replace("`", '', $postAlternative);
                $firstKey = array_shift($independentKeys);
                $secondKey = array_shift($independentKeys);
                for( $index = $firstKey; $index <= $secondKey; $index++ ) {
                    if( array_key_exists($index, $itemArray) ) {
                        $startFound = $index;
                        break;
                    }
                }
                for( $index = $secondKey; $index >= $firstKey; $index-- ) {
                    if( array_key_exists($index, $itemArray) ) {
                        $endFound = $index;
                        break;
                    }
                }
                // intervening fields found
                if( ($startFound !== FALSE) && ($endFound !== FALSE) ) {
                    $itemArray[$startFound] = $pre . $itemArray[$startFound];
                    $itemArray[$endFound] = $itemArray[$endFound] . $post;
                }
                // intervening fields not found - do we have an alternative?
                else {
                    if( array_key_exists($firstKey - 1, $itemArray) && $preAlternative ) {
                        $itemArray[$firstKey - 1] .= $preAlternative;
                    }
                    if( array_key_exists($secondKey + 1, $itemArray) && $postAlternative ) {
                        $itemArray[$secondKey + 1] = $postAlternative . $itemArray[$secondKey + 1];
                    }
                }
            }
        }
        // Empty titles should not occur but, in case, this catches errors.
        if( !isset($itemArray) ) {
            $itemArray = array();
        }
        $pString = join('', $itemArray);
        /**
        * if last character is punctuation (which it may be with missing fields etc.), and $ultimate is also 
        * punctuation, set $ultimate to empty string.
        */		
        if( isset($ultimate) && $ultimate ) {
            $pString = trim($pString);
            /**
            * Don't do ';' in case last element is URL with &gt; ...!
            */
            if(preg_match("/^[.,:?!]/", $ultimate) && 
                preg_match("/([.,:?!])(\[.*?[\]]+)*$|([.,:?!])$/", $pString)) {
                $ultimate = '';
            }
        }
        // If $this->editorSwitch or $this->dateMonthNoDay, we have altered $this->$bibformat->$type so need to reload styles
        if( !$this->preview && ($this->editorSwitch || $this->dateMonthNoDay) ) {
            $this->restoreTypes();
            $this->editorSwitch = $this->dateMonthNoDay = FALSE;
        }
        return $this->export->format($preliminary . trim($pString) . $ultimate);
    }
    
}

/**
* Format a bibliographic resource for output.
* 
* @author	Andrea Rossato
* @version	1
*/
class osbib_exportfilter {
    function __construct(&$ref, $output) {
        $this->bibformat =& $ref;
        $this->format = $output;
        // newLine ( is used in CITEFORMAT::endnoteProcess)
        // RTF
        if($this->format == 'rtf') {
            $this->newLine = "\\par\\qj ";
        }
        // HTML
        else if($this->format == 'html') { 
            $this->newLine = "<br />";
        }
        // plain text
        else {
            $this->newLine = "\n";
        }
    }
    
    /**
    * Format for HTML or RTF/plain?
    *
    * @author	Mark Grimshaw
    * @version	1
    *
    * @param	$data	Input string
    */
    function format($data) {
	if($this->format == 'html') {
            /**
            * Scan for search patterns and highlight accordingly
            */
            /**
            * Temporarily replace any URL - works for just one URL in the output string.
            */
            if( preg_match("/(<a.*>.*<\/a>)/i", $data, $match) ) {
                $url = preg_quote($match[1], '/');
                $data = preg_replace("/$url/", "OSBIB__URL__OSBIB", $data);
            }
            else {
                $url = FALSE;
            }
            $data = str_replace("\"", "&quot;", $data);
            $data = str_replace("<", "&lt;", $data);
            $data = str_replace(">", "&gt;", $data);    
            $data = preg_replace("/&(?![a-zA-Z0-9#]+?;)/", "&amp;", $data);
            $data = $this->bibformat->patterns ? 
                    preg_replace($this->bibformat->patterns, 
                    "<span class=\"" . $this->bibformat->patternHighlight . "\">$1</span>", $data) : $data;
            $data = preg_replace("/\[b\](.*?)\[\/b\]/is", "<strong>$1</strong>", $data);
            $data = preg_replace("/\[i\](.*?)\[\/i\]/is", "<em>$1</em>", $data);
            $data = preg_replace("/\[sup\](.*?)\[\/sup\]/is", "<sup>$1</sup>", $data);
            $data = preg_replace("/\[sub\](.*?)\[\/sub\]/is", "<sub>$1</sub>", $data);
            $data = preg_replace("/\[u\](.*?)\[\/u\]/is", "<span style=\"text-decoration: underline;\">$1</span>", $data);
            // Recover any URL
            if( $url ) {
                $data = str_replace("OSBIB__URL__OSBIB", $match[1], $data);
            }
            $data = str_replace("WIKINDX_NDASH", "&ndash;", $data);
        }
        else if( $this->format == 'rtf' ) {
            $data = preg_replace("/&#(.*?);/", "\\u$1", $data);
            $data = preg_replace("/\[b\](.*?)\[\/b\]/is", "{{\\b $1}}", $data);
            $data = preg_replace("/\[i\](.*?)\[\/i\]/is", "{{\\i $1}}", $data);
            $data = preg_replace("/\[u\](.*?)\[\/u\]/is", "{{\\ul $1}}", $data);
            $data = preg_replace("/\[sup\](.*?)\[\/sup\]/is", "{{\\super $1}}", $data);
            $data = preg_replace("/\[sub\](.*?)\[\/sub\]/is", "{{\\sub $1}}", $data);
            $data = str_replace("WIKINDX_NDASH", "\\u8212\\'14 ", $data);
        }
        /**
        * OpenOffice-1.x.
        */
        else if( $this->format == 'sxw' ) {
            $data = $this->bibformat->utf8->decodeUtf8($data);
            $data = str_replace("\"", "&quot;", $data);
            $data = str_replace("<", "&lt;", $data);
            $data = str_replace(">", "&gt;", $data);    
            $data = preg_replace("/&(?![a-zA-Z0-9#]+?;)/", "&amp;", $data);
            $data = preg_replace("/\[b\](.*?)\[\/b\]/is", "<text:span text:style-name=\"textbf\">$1</text:span>", $data);
            $data = preg_replace("/\[i\](.*?)\[\/i\]/is", "<text:span text:style-name=\"emph\">$1</text:span>", $data);
            $data = preg_replace("/\[sup\](.*?)\[\/sup\]/is", "<text:span text:style-name=\"superscript\">$1</text:span>", $data);
            $data = preg_replace("/\[sub\](.*?)\[\/sub\]/is", "<text:span text:style-name=\"subscript\">$1</text:span>", $data);
            $data = preg_replace("/\[u\](.*?)\[\/u\]/is", 
                    "<text:span text:style-name=\"underline\">$1</text:span>", $data);
            $data = "<text:p text:style-name=\"Text body\">".$data."</text:p>\n";
            $data = str_replace("WIKINDX_NDASH", "-", $data);
        }
        /**
        * 'noScan' means do nothing (leave BBCodes intact)
        */
        else if($this->format == 'noScan') {
            $data = str_replace("WIKINDX_NDASH", "-", $data);
            return $data;
        }
        /**
        * StripBBCode for 'plain'.
        */
        else {
            $data = preg_replace("/\[.*\]|\[\/.*\]/U", "", $data);
            $data = str_replace("WIKINDX_NDASH", "-", $data);
        }
        return $data;
    }
}

/**
 * Class parsestyle
 */
class osbib_parsestyle {
    
    public function __construct() {
    }
       
    // parse input into array
    public function parseStringToArray($type, $subject, $map = FALSE, $date = FALSE) {
        if(!$subject) {
            return array();
        }
        if($map) {
            $this->map = $map;
        }
        $search = join('|', $this->map->$type);
        if($date) {
            $search .= '|' . 'date';
        }
        $subjectArray = split("\|", $subject);
        $sizeSubject = sizeof($subjectArray);
        // Loop each field string
        $index = 0;
        $subjectIndex = 0;
        foreach($subjectArray as $subject) {
            ++$subjectIndex;
            $dependentPre = $dependentPost = $dependentPreAlternative = 
                    $dependentPostAlternative = $singular = $plural = FALSE;

            // First grab fieldNames from the input string.
            preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $subject, $array);
            if(empty($array)) {
                if(!$index) {
                    $possiblePreliminaryText = $subject;
                    continue;
                }
                if(isset($independent) && ($subjectIndex == $sizeSubject) && 
                    array_key_exists('independent_' . $index, $independent)) {
                    $ultimate = $subject;
                }
                else {
                    if(isset($independent) && (sizeof($independent) % 2)) {
                        $independent['independent_' . ($index - 1)] = $subject;
                    }
                    else {
                        $independent['independent_' . $index] = $subject;
                    }
                }
                continue;
            }

            // At this stage, [2] is the fieldName, [1] is what comes before and [3] is what comes after.
            $pre = $array[1];
            $fieldName = $array[2];
            if($date && ($fieldName == 'date')) {
                $fieldName = $this->map->{$type}['date'];
            }
            $post = $array[3];

            // Anything in $pre enclosed in '%' characters is only to be printed if the resource has something in the 
            // previous field -- replace with unique string for later preg_replace().
            if(preg_match("/%(.*)%(.*)%|%(.*)%/U", $pre, $dependent)) {
                // if sizeof == 4, we have simply %*% with the significant character in [3].
                // if sizeof == 3, we have %*%*% with dependent in [1] and alternative in [2].
                $pre = str_replace($dependent[0], "__DEPENDENT_ON_PREVIOUS_FIELD__", $pre);
                if(sizeof($dependent) == 4) {
                    $dependentPre = $dependent[3];
                    $dependentPreAlternative = '';
                }
                else {
                    $dependentPre = $dependent[1];
                    $dependentPreAlternative = $dependent[2];
                }
            }

            // Anything in $post enclosed in '%' characters is only to be printed if the resource has something in the 
            // next field -- replace with unique string for later preg_replace().
            if(preg_match("/%(.*)%(.*)%|%(.*)%/U", $post, $dependent)) {
                $post = str_replace($dependent[0], "__DEPENDENT_ON_NEXT_FIELD__", $post);
                if(sizeof($dependent) == 4)  {
                    $dependentPost = $dependent[3];
                    $dependentPostAlternative = '';
                }
                else {
                    $dependentPost = $dependent[1];
                    $dependentPostAlternative = $dependent[2];
                }
            }

            // find singular/plural alternatives in $pre and $post and replace with unique string for later preg_replace().
            if(preg_match("/\^(.*)\^(.*)\^/U", $pre, $matchCarat)) {
                $pre = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $pre);
                $singular = $matchCarat[1];
                $plural = $matchCarat[2];
            }
            else if(preg_match("/\^(.*)\^(.*)\^/U", $post, $matchCarat)) {
                $post = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $post);
                $singular = $matchCarat[1];
                $plural = $matchCarat[2];
            }

            // Now dump into $final[$fieldName] stripping any backticks
            if($dependentPre) {
                $final[$fieldName]['dependentPre'] = $dependentPre;
            }
            else {
                $final[$fieldName]['dependentPre'] = '';
            }
            if($dependentPost) {
                $final[$fieldName]['dependentPost'] = $dependentPost;
            }
            if($dependentPreAlternative) {
                $final[$fieldName]['dependentPreAlternative'] = $dependentPreAlternative;
            }
            else {
                $final[$fieldName]['dependentPreAlternative'] = '';
            }
            if($dependentPostAlternative) {
                $final[$fieldName]['dependentPostAlternative'] = $dependentPostAlternative;
            }
            else {
                $final[$fieldName]['dependentPostAlternative'] = '';
            }
            if($singular) {
                $final[$fieldName]['singular'] = $singular;
            }
            else {
                $final[$fieldName]['singular'] = '';
            }
            if($plural) {
                $final[$fieldName]['plural'] = $plural;
            }
            else {
                $final[$fieldName]['plural'] = '';
            }
            $final[$fieldName]['pre'] = str_replace('`', '', $pre);
            $final[$fieldName]['post'] = str_replace('`', '', $post);
            $index++;
//			$final[$fieldName]['pre'] = $pre;
//			$final[$fieldName]['post'] = $post;
        }

        if(isset($possiblePreliminaryText)) {
            if(isset($independent)) {
                $independent = array('independent_0' => $possiblePreliminaryText) + $independent;
            }
            else {
                $final['preliminaryText'] = $possiblePreliminaryText;
            }
        }
        if(!isset($final)) { // presumably no field names... so assume $subject is standalone text and return
            $final['preliminaryText'] = $subject;
            return $final;
        }
        if(isset($independent)) {
            $size = sizeof($independent);
            // If $size == 3 and exists 'independent_0', this is preliminaryText
            // If $size == 3 and exists 'independent_' . $index, this is ultimate
            // If $size % 2 == 0 and exists 'independent_0' and 'independent_' . $index, these are preliminaryText and ultimate
            if(($size == 3) && array_key_exists('independent_0', $independent)) {
                $final['preliminaryText'] = array_shift($independent);
            }
            else if(($size == 3) && array_key_exists('independent_' . $index, $independent)) {
                $final['ultimate'] = array_pop($independent);
            }
            else if(!($size % 2) && array_key_exists('independent_0', $independent) 
                && array_key_exists('independent_' . $index, $independent)) 
                {

                $final['preliminaryText'] = array_shift($independent);
                $final['ultimate'] = array_pop($independent);
            }
            $size = sizeof($independent);
            // last element of odd number is actually ultimate punctuation or first element is preliminary if exists 'independent_0'
            if($size % 2)  {
                if(array_key_exists('independent_0', $independent)) {
                    $final['preliminaryText'] = array_shift($independent);
                }
                else {
                    $final['ultimate'] = array_pop($independent);
                }
            }
            if($size == 1) {
                if(array_key_exists('independent_0', $independent)) {
                    $final['preliminaryText'] = array_shift($independent);
                }
                if(array_key_exists('independent_' . $index, $independent)) {
                    $final['ultimate'] = array_shift($independent);
                }
            }
            if(isset($ultimate) && !array_key_exists('ultimate', $final)) {
                $final['ultimate'] = $ultimate;
            }
            if(isset($preliminaryText) && !array_key_exists('preliminaryText', $final)) {
                $final['preliminaryText'] = $preliminaryText;
            }
            if(!empty($independent)) {
                $final['independent'] = $independent;
            }
        }
        return $final;
    }
    
}