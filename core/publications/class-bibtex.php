<?php
/**
 * This file contains all general functions of teachpress bibtex core
 * 
 * @package teachpress\core\bibtex
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */

/**
 * teachPress BibTeX class
 *
 * @package teachpress\core\bibtex
 * @since 3.0.0
 */
class TP_Bibtex {

    /**
     * Gets a single publication in bibtex format
     * @param array $row
     * @param array $all_tags               optional
     * @param boolean $convert_bibtex       Flag for the utf-8 to TeX char convertion, Default is false
     * @return string
     * @since 3.0.0
    */
    public static function get_single_publication_bibtex ($row, $all_tags = '', $convert_bibtex = false) {
        $string = '';
        $pub_fields = array('type', 'bibtex', 'title', 'author', 'editor', 'url', 'doi', 'isbn', 'date', 'urldate', 'booktitle', 'issuetitle', 'journal', 'volume', 'number', 'issue', 'pages', 'publisher', 'address', 'edition', 'chapter', 'institution', 'organization', 'school', 'series', 'crossref', 'abstract', 'howpublished', 'key', 'techtype', 'note');
        $isbn_label = ( $row['is_isbn'] == 1 ) ? 'isbn' : 'issn';
        
        // initial string
        if ( $row['type'] === 'presentation' ) {
            $string = '@misc{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        else {
            $string = '@' . stripslashes($row['type']) . '{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        
        // loop for all BibTeX fields
        for ( $i = 2; $i < count($pub_fields); $i++ ) {
            // go to the next if there is nothing
            if ( !isset( $row[$pub_fields[$i]] ) || $row[$pub_fields[$i]] == '' || $row[$pub_fields[$i]] == '0000-00-00'  ) {
                continue;
            }
            
            $field_name = $pub_fields[$i];
            $field_value = TP_HTML::convert_special_chars( stripslashes( $row[$pub_fields[$i]] ) );
           
            // prepare the fields
            // ISBN | ISSN
            if ( $field_name === 'isbn' ) {
                $string .= $isbn_label . ' = {' . $field_value . '},' . chr(13) . chr(10);
            }
            // year
            elseif ( $field_name === 'date' ) {
                $string .= 'year  = {' . $row['year'] . '},' . chr(13) . chr(10);
                $string .= TP_Bibtex::prepare_bibtex_line($field_value, $field_name);
            }
            // techtype
            elseif ( $field_name=== 'techtype' ) {
                $string .= 'type = {' . $field_value . '},' . chr(13) . chr(10);
            }
            // patent: use address as location
            elseif ( $field_name=== 'address' && $row['type']  === 'patent' ) {
                $string .= 'location = {' . $field_value . '},' . chr(13) . chr(10);
            }
            // abstract
            elseif ( $field_name === 'abstract' || $field_name === 'title' ) {
                $string .= TP_Bibtex::prepare_text($field_value, $field_name);
            }
            // normal case
            else {
                $string .= TP_Bibtex::prepare_bibtex_line($field_value, $field_name);
            }
            
        }
        
        // Add month
        if ( $row['type'] == 'booklet' ) {
            $date = tp_datesplit( $row['date'] );
            $string .= 'month = {' . $date[0][1] . '},' . chr(13) . chr(10);
        }
        
        // Add keywords
        if ( $all_tags != '' ) {
            $keywords = '';
            foreach ( $all_tags as $all_tags ) {
                $keywords .= $all_tags['name'] . ', ';
            }
            $string .= 'keywords = {' . substr($keywords, 0, -2) . '}';
        }
        else {
            $string .= 'keywords = {}';
        }
        
        // Add teachPress/biblatex extensions
        $string .= ',' . chr(13) . chr(10);
        $string .= 'pubstate = {' . $row['status'] . '},' . chr(13) . chr(10);
        $string .= 'tppubtype = {' . $row['type'] . '}' . chr(13) . chr(10);
        $string .= '}' . chr(13) . chr(10);
        
        // Convert utf-8 chars
        if ( $convert_bibtex === true ) {
            $string = self::convert_utf8_to_bibtex($string);
        }
        return $string;
    }

    /**
     * Heuristics to check if the input is BibTeX. May be useful when handling
     * potentially problematic content, i.e. automatically downloaded from URLs.
     *
     * @param string $input
     * @return false iff $input is very probably not BibTeX format. False positives are
     *         possible.
     * @ince 9.0.0
     * @access public
     */
    public static function looks_like_bibtex ($input) {
        $lines = preg_split("/\r\n|\n|\r/", $input);
        $result = $lines !== false;
        
        if ($result) {
            $lines = array_filter($lines, function ($l) {
                                  $l = trim($l);
                                  return strlen($l) > 0 && substr($l, 0, 1) != "%"; });
            
            if ($result && count($lines) > 0) {
                $first_char = substr(trim(reset($lines)), 0, 1);
                $last_line = trim(end($lines));
                $last_char = substr($last_line, strlen($last_line) - 1, 1);
                
                $result = $first_char == "@" && $last_char == "}";
            }
        }
        
        return $result;
    }
    
    /**
     * Replaces some BibTeX special chars with the UTF-8 versions and secures the input. 
     * Before teachPress 5.0, this function was called replace_bibtex_chars()
     * 
     * @param string $input
     * @return string
     * @since 3.0.0
     * @access public
     */
    public static function convert_bibtex_to_utf8 ($input) {
        
        // return the input if there are no bibtex chars
        if ( strpos( $input,'\\' ) === false && strpos($input,'{') === false ) { return $input; }
        
        // Step 1: Chars which are based on a combination of chars, with escapes
        $array_a = array("\'a","\`a",'\^a',"\'A",'\"a','\"A',"\`A",'\^A',
                         "\c c","\c C",
                         "\'e","\`e",'\^e','\"e',"\'E","\`E",'\^E','\"E',
                         "\'i",'\^i','\"i',"\^I",
                         "\'o","\'O",'\"o','\"O','\^o','\^O',
                         '\"u','\"U','\^u','\^U','\ss',
                         '\L','\l','\AE','\ae','\OE','\oe','\t{oo}','\O','\o',
                         '\textendash','\textemdash','\textquotesingle','\textquoteright','\textquoteleft',
                         '\glqq','\grqq','\flqq','\frqq','\flq','\frq',
                         '\guillemotleft','\guillemotright','\glq','\grq','\dq',chr(92));
        $array_b = array('á','à','â','Á','ä','Ä','À','Â',
                         'ç','Ç',
                         'é','è','ê','ë','É','È','Ê','Ë',
                         'í','î','ï','Î',
                         'ó','Ó', 'ö','Ö','ô','Ô',
                         'ü','Ü','û','Û','ß',
                         'Ł','ł','Æ','æ','Œ','œ','o͡o','Ø','ø',
                         '–','—',"'",'’','‘',
                         '„','“','«','»','‹','›',
                         '«','»','‚','‘','','');
        $input = str_replace( $array_a , $array_b ,$input);
        
        // Step 2: All other special chars 
        $array_1 = array('"{a}','"{A}','`{a}','`{A}',"'{a}","'{A}",'~{a}','~{A}','={a}','={A}','^{a}','^{A}','u{a}','u{A}','k{a}','k{A}','r{a}','r{A}','{aa}','{AA}',
                         '.{b}','.{B}',
                         "'{c}","'{C}",'v{c}','v{C}','c{c}','c{C}','.{c}','.{C}','^{c}','^{C}',
                         'v{d}','v{D}','.{d}','.{D}','d{d}','d{D}','B{d}','B{D}',
                         '"{e}','"{E}',"'{e}","'{E}",'`{e}','`{E}','^{e}','^{E}','u{e}','u{E}','v{e}','v{E}','={e}','={E}','k{e}','k{E}','.{e}','.{E}',
                         '.{f}','.{F}',
                         'u{g}','u{G}','c{g}','c{G}','.{g}','.{G}','^{g}','^{G}',
                         '.{h}','.{H}','d{h}','d{H}','^{h}','^{H}','B{h}','B{H}',
                         '"{i}','"{I}','~{i}','~{I}','`{i}','`{I}',"'{i}","'{I}",'^{i}','^{I}','u{i}','u{I}','={i}','={I}','k{i}','k{I}','.{i}','.{I}',
                         '^{j}','^{J}',
                         'c{k}','c{K}','d{k}','d{K}',
                         "'{l}","'{L}",'v{l}','v{L}','c{l}','c{L}','d{l}','d{L}',
                         '.{m}','.{M}','d{m}','d{M}',
                         "'{n}","'{N}",'~{n}','~{N}','v{n}','v{N}','c{n}','c{N}','.{n}','.{N}',
                         '"{o}','"{O}','`{o}','`{O}',"'{o}","'{O}",'~{o}','~{O}','^{o}','^{O}','u{o}','u{O}','.{o}','.{O}','={o}','={O}','H{o}','H{O}',
                         '.{p}','.{P}',
                         "'{r}","'{R}",'v{r}','v{R}','c{r}','c{R}','.{r}','.{R}','d{r}','d{R}',
                         "'{s}","'{S}",'v{s}','v{S}','c{s}','c{S}','.{s}','.{S}','d{s}','d{S}','^{s}','^{S}',
                         'v{t}','v{T}','c{t}','c{T}','.{t}','.{T}','d{t}','d{T}','B{t}','B{T}',
                         '"{u}','"{U}','`{u}','`{U}',"'{u}","'{U}",'^{u}','^{U}','d{u}','d{U}','~{u}','~{U}','u{u}','u{U}','={u}','={U}','k{u}','k{U}','r{u}','r{U}','H{u}','H{U}',
                         'd{v}','d{V}',
                         '^{w}','^{W}',
                         '"{y}','"{Y}',"'{y}","'{Y}",'^{y}','^{Y}',
                         "'{z}","'{Z}",'v{z}','v{Z}','.{z}','.{Z}');
        $array_2 = array('ä','Ä','à','À','á','Á','ã','Ã','ā','Ā','â','Â','ă','Ă','ą','Ą','å','Å','å','Å',
                         'ḃ','Ḃ',
                         'ć','Ć','č','Č','ç','Ç','ċ','Ċ','ĉ','Ĉ',
                         'ď','Ď','ḋ','Ḋ','ḍ','Ḍ','đ','Đ',
                         'ë','Ë','é','É','è','È','ê','Ê','ĕ','Ĕ','ě','Ě','ē','Ē','ę','Ę','ė','Ė',
                         'ḟ','Ḟ',
                         'ğ','Ğ','ģ','Ģ','ġ','Ġ','ĝ','Ĝ',
                         'ḣ','Ḣ','ḥ','Ḥ','ĥ','Ĥ','ħ','Ħ',
                         'ï','Ï','ĩ','Ĩ','ì','Ì','í','Í','î','Î','ĭ','Ĭ','ī','Ī','į','Į','i','İ',
                         'ĵ','Ĵ',
                         'ķ','Ķ','ḳ','Ḳ',
                         'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ḷ','Ḷ',
                         'ṁ','Ṁ','ṃ','Ṃ',
                         'ń','Ń','ñ','Ñ','ň','Ň','ņ','Ņ','ṅ','Ṅ',
                         'ö','Ö','ò','Ò','ó','Ó','õ','Õ','ô','Ô','ŏ','Ŏ','ȯ','Ȯ','ō','Ō','ő','Ő',
                         'ṗ','Ṗ',
                         'ŕ','Ŕ','ř','Ř','ŗ','Ŗ','ṙ','Ṙ','ṛ','Ṛ',
                         'ś','Ś','š','Š','ş','Ş','ṡ','Ṡ','ṣ','Ṣ','ŝ','Ŝ',
                         'ť','Ť','ţ','Ţ','ṫ','Ṫ','ṭ','Ṭ','ŧ','Ŧ',
                         'ü','Ü','ù','Ù','ú','Ú','û','Û','ụ','Ụ','ũ','Ũ','ŭ','Ŭ','ū','Ū','ų','Ų','ů','Ů','ű','Ű',
                         'ṿ','Ṿ',
                         'ŵ','Ŵ',
                         'ÿ','Ÿ','ý','Ý','ŷ','Ŷ',
                         'ź','Ź','ž','Ž','ż','Ż');
        $return = str_replace($array_1, $array_2, $input);
        return htmlspecialchars($return, ENT_NOQUOTES);
    }
    
    /**
     * Cleans the author names after bibtex to UTF-8 conversion
     * @param string $input
     * @return string
     * @since 6.1.0
     */
    public static function clean_author_names ($input) {
        $array_a = array('{á}','{Á}','{ä}','{Ä}',
                         '{é}','[É}',
                         '{í}',
                         '{ó}','{Ó}','{ö}','{Ö}',
                         '{ü}','{Ü}','{ß}','{š}', '{ø}', '{Ø}', '{å}', '{Å}');
        $array_b = array('á','Á','ä','Ä',
                         'é','É',
                         'í',
                         'ó','Ó','ö','Ö',
                         'ü','Ü','ß','š','ø', 'Ø', 'å', 'Å');
        $ret = str_replace($array_a, $array_b, $input);
        return $ret;
    }
    
    /**
     * Replaces some UTF-8 chars with their BibTeX/LaTeX expression.
     * @param string $input
     * @return string
     * @since 5.0.0
     */
    public static function convert_utf8_to_bibtex ($input) {
        $array_a = array('ä','Ä','à','À','á','Á','â','Â','ã','Ã','ą','Ą','ā','Ā','ă','Ă','å','Å',
                         'ḃ','Ḃ',
                         'ć','Ć','č','Č','ç','Ç','ċ','Ċ','ĉ','Ĉ',
                         'ď','Ď','ḋ','Ḋ','đ','Đ','ḍ','Ḍ',
                         'ë','Ë','é','É','è','È','ê','Ê','ė','Ė','ĕ','Ĕ','ě','Ě','ē','Ē','ę','Ę',
                         'ḟ','Ḟ',
                         'ğ','Ğ','ģ','Ģ','ġ','Ġ','ĝ','Ĝ',
                         'ḣ','Ḣ','ħ','Ħ','ḥ','Ḥ','ĥ','Ĥ',
                         'ï','Ï','ĩ','Ĩ','ì','Ì','í','Í','î','Î','ĭ','Ĭ','ī','Ī','į','Į','İ',
                         'ĵ','Ĵ',
                         'ķ','Ķ','ḳ','Ḳ',
                         'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ḷ','Ḷ',
                         'ṁ','Ṁ','ṃ','Ṃ',
                         'ń','Ń','ñ','Ñ','ň','Ň','ņ','Ņ','ṅ','Ṅ',
                         'ö','Ö','ò','Ò','ó','Ó','ô','Ô','õ','Õ','ŏ','Ŏ','ȯ','Ȯ','ō','Ō','ő','Ő',
                         'ṗ','Ṗ',
                         'ŕ','Ŕ','ř','Ř','ŗ','Ŗ','ṙ','Ṙ','ṛ','Ṛ',
                         'ś','Ś','š','Š','ş','Ş','ṡ','Ṡ','ṣ','Ṣ','ŝ','Ŝ',
                         'ť','Ť','ţ','Ţ','ṫ','Ṫ','ŧ','Ŧ','ṭ','Ṭ',
                         'ü','Ü','ù','Ù','ú','Ú','û','Û','ụ','Ụ','ũ','Ũ','ŭ','Ŭ','ū','Ū','ű','Ű','ů','Ů','ų','Ų',
                         'ṿ','Ṿ',
                         'ŵ','Ŵ',
                         'ÿ','Ÿ','ý','Ý','ŷ','Ŷ',
                         'ź','Ź','ž','Ž','ż','Ż',
                         'ß','&','Ø','ø','Ł','ł','Æ','æ','Œ','œ','o͡o','–','—');
        
        $array_b = array('\"{a}', '\"{A}', '\`{a}', '\`{A}', "\'{a}", "\'{A}", '\^{a}', '\^{A}', '\~{a}', '\~{A}', '\k{a}', '\k{A}', '\={a}', '\={A}', '\u{a}', '\u{A}', 'r{a}', 'r{A}',
                         '\.{b}', '\.{B}',
                         "\'{c}", "\'{C}", '\v{c}', '\v{C}', '\c{c}', '\c{C}', '\.{c}', '\.{C}', '\^{c}', '\^{C}',
                         '\v{d}', '\v{D}', '\.{d}', '\.{D}', '{d}', '{D}', '\d{d}', '\d{D}',
                         '\"{e}', '\"{E}', "\'{e}", "\'{E}", "\`{e}", "\`{E}", '\^{e}', '\^{E}', '\.{e}', '\.{E}', '\u{e}', '\u{E}', 'v{e}', 'v{E}', '={e}', '={E}', '\k{e}', '\k{E}',
                         '\.{f}', '\.{F}',
                         '\u{g}', '\u{G}', '\c{g}', '\c{G}', '\.{g}', '\.{G}', '\^{g}', '\^{G}',
                         '\.{h}', '\.{H}', '{h}', '{H}', '\d{h}', '\d{H}', '\^{h}', '\^{H}',
                         '\"{i}', '\"{I}', '\~{i}', '\~{I}', '\`{i}', '\`{I}', "\'{i}", "\'{I}", '\^{i}', '\^{I}', '\u{i}', '\u{I}', '\={i}', '\={I}', '\k{i}', '\k{I}', '\.{I}',
                         '\^{j}', '\^{J}',
                         '\c{k}', '\c{K}', '\d{k}', '\d{K}',
                         "\'{l}", "\'{L}", '\v{l}', '\v{L}', '\c{l}', '\c{L}', '\d{l}', '\d{L}',
                         '\.{m}', '\.{M}', '\d{m}', '\d{M}',
                         "\'{n}", "\'{N}", '\~{n}', '\~{N}', 'v{n}', 'v{N}', '\c{n}', '\c{N}', '\.{n}', '\.{N}',
                         '\"{o}', '\"{O}', '\`{o}', '\`{O}', "\'{o}", "\'{O}", '\^{o}', '\^{O}', '\~{o}', '\~{O}', '\u{o}', '\u{O}', '\.{o}', '\.{O}', '\={o}' , '\={O}', '\H{o}', '\H{O}',
                         '\.{p}', '\.{P}',
                         "\'{r}", "\'{R}", '\v{r}', '\v{R}', '\c{r}', '\c{R}', '\.{r}', '\.{R}', '\d{r}', '\d{R}',
                         "\'{s}", "\'{S}", '\v{s}', '\v{S}', '\c{s}', '\c{S}', '\.{s}', '\.{S}', '\d{s}', '\d{S}', '\^{s}', '\^{S}',
                         '\v{t}', '\v{T}', '\c{t}', '\c{T}', '\.{t}', '\.{T}', '{t}', '{T}', '\d{t}', '\d{T}',
                         '\"{u}', '\"{U}', '\`{u}', '\`{U}', "\'{u}", "\'{U}", '\^{u}', '\^{U}', '\d{u}', '\d{U}', '\~{u}', '\~{U}', '\u{u}', '\u{U}', '\={u}', '\={U}', '\H{u}', '\H{U}', 'r{u}', 'r{U}', '\k{u}', '\k{U}',
                         '\d{v}', '\d{V}',
                         '\^{w}', '\^{W}',
                         '\"{y}', '\"{Y}', "\'{y}", "\'{Y}", '\^{y}', '\^{Y}',
                         "\'{z}", "\'{Z}", '\v{z}', '\v{Z}', '\.{z}', '\.{Z}',
                         '\ss', '\&','\O', '\o', '\L', '\l', '\AE', '\ae', '\OE', '\oe', '\t{oo}', '\textendash', '\textemdash'
                        );
        $return = str_replace( $array_a , $array_b ,$input);
        return $return;
    }
    
    /**
     * Prepares a (html) input for bibtex and replace expressions for bold, italic, lists, etc. with their latex equivalents
     * @param string $text          The (html) input
     * @param string $fieldname     The bibtex field name
     * @return string
     * @since 4.2.0
     */
    public static function prepare_text($text, $fieldname = 'abstract') {
        if ( $text == '' ) {
            return '';
        }
        
        $text = htmlspecialchars_decode($text);
        // Replace expressions
        $search = array ('/<sub>/i', '/<sup>/i',
                         '/<i>/i', '/<b>/i', '/<em>/i', '/<u>/i', 
                         '/<\/(sub|sup|i|b|em|u)>/i',
                         '/<(s|small|del)>/i',
                         '/<\/(s|small|del)>/i',
                         '/<ul>/i', '/<\/ul>/i',
                         '/<ol>/i', '/<\/ol>/i',
                         '/<li>/i', '/<\/li>/i');
        $replace = array ('_{', '^{',
                          '\textit{', '\textbf{', '\emph{', '\underline{', 
                          '}',
                          '',
                          '',
                          '\begin{itemize}', '\end{itemize}' . "\n",
                          '\begin{enumerate}', '\end{enumerate} . "\n"',
                          '\item ', '');
        $text = preg_replace($search, $replace, $text);
        return TP_Bibtex::prepare_bibtex_line($text, $fieldname, false);
    }

   /**
     * Prepares a page number
     * @access public
     * @param string $input
     * @return string
     * @since 4.0.0
     */
    public static function prepare_page_number ($input) {
        if ( isset($input) ) {
            return str_replace("--", "–", $input);
        }
        return '';
    }
    
    

    /**
     * Prepares a single BibTeX line with the input from onde publication field
     * @param string    $input          The value of the publication field
     * @param string    $fieldname      The name of the publication field
     * @param boolean   $stripslashes   Strip slashes (true) or not (false); default is true; since 4.2.0
     * @return string
     * @since 3.0.0
     */
    public static function prepare_bibtex_line($input, $fieldname, $stripslashes = true) {
        if ($input != '') {
            $input = ( $stripslashes === true ) ? stripslashes($input) : $input;
            return $fieldname . ' = {' . $input . '},' . chr(13) . chr(10);
        }
        return '';
    }
    
    /** 
     * Explodes an url string into array 
     * @param string $url_string 
     * @return array 
     * @since 4.3.5 
    */ 
    public static function explode_url ($url_string) { 
        $all_urls = explode(chr(13) . chr(10), $url_string); 
        $end = array(); 
        foreach ($all_urls as $url) { 
            $parts = explode(', ',$url); 
            $parts[0] = trim( $parts[0] ); 
            if ( !isset($parts[1]) ) { 
                $parts[1] = $parts[0]; 
            } 
            $end[] = $parts; 
        } 
        return $end; 
    } 
    
    /**
     * The function splits a author/editor name and returns the lastname or NULL if there is no name was found
     * @param string $input     A name of an author or editor
     * @return string
     * @since 5.0.0
     */
    public static function get_lastname ($input) {
        $creator = new BIBTEXCREATORPARSE();
        $creatorArray = $creator->parse($input);
        if ( isset( $creatorArray[0][2] ) ) {
            return trim($creatorArray[0][2]);
        }
        return null;
    }

    /**
     * Parses author names
     * @param string $input         The input string
     * @param string $separator     The separator between the authors (for the output)
     * @param string $mode          values: last, initials, old, short
     * @param string $punctuation   Punctuation after an initial (only used for short mode)
     * @return string
     * @since 3.0.0
    */
    public static function parse_author ($input, $separator, $mode = '', $punctuation = '') {
        if ( $mode === 'last' || $mode === 'initials' ) {
            $all_authors = self::parse_author_default($input, $separator, $mode);
        }
        elseif ( $mode === 'short' ) {
            $all_authors = self::parse_author_short($input, $separator, $punctuation);
        }
        elseif ( $mode === 'old' ) {
            $all_authors = self::parse_author_deprecated($input, $separator);
        }
        else {
            $all_authors = self::parse_author_simple($input, $separator);
        }
        return $all_authors;
    }
    
    /**
     * This is the default parsing function for author names
     * 
     * Some examples for the parsing:
     * last:        Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf; van Beethoven, Ludwig
     * initials:    Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F; van Beethoven, Ludwig
     * 
     * @param string $input     The input string
     * @param string $separator The separator between the authors (for the output)
     * @param string $mode      last o initials
     * @return string
     * @since 5.0.0
     * @access public
     * @uses BIBTEXCREATORPARSE()    This class is a part of bibtexParse
     */
    public static function parse_author_default ($input, $separator = ';', $mode = 'initials') {
        $creator = new BIBTEXCREATORPARSE();
        $creator->separateInitials = false;
        $creatorArray = $creator->parse($input);
        $all_authors = '';
        $max = count($creatorArray);
        for ( $i = 0; $i < $max; $i++ ) {
            $one_author = '';
            /* 
             * Set the author name together with the parsing result of bibtexParse
             * 
             * $creatorArray[][0] => firstname
             * $creatorArray[][1] => initials
             * $creatorArray[][2] => surname
             * $creatorArray[][3] => jr
             * $creatorArray[][4] => von
            */
            if ($creatorArray[$i][4] != '') { $one_author .= trim($creatorArray[$i][4]);}
            if ($creatorArray[$i][3] != '') { $one_author = trim($creatorArray[$i][3]);}
            if ($creatorArray[$i][2] != '') { $one_author .= ' ' .trim($creatorArray[$i][2]) . ',';}
            if ($creatorArray[$i][0] != '') { $one_author .= ' ' .trim($creatorArray[$i][0]);}
            if ( $mode == 'initials' && $creatorArray[$i][1] != '' ) { 
                $one_author .= ' ' .trim($creatorArray[$i][1]);
            }
            
            // Add author to the main result
            $all_authors .= stripslashes($one_author);
            if ( $i < count($creatorArray) -1 ) {
                $all_authors .= $separator . ' ';
            }
        }
        return $all_authors;
    }
    
    /**
     * Parse author names to the short style
     * 
     * Example: 
     * Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, A F; van Beethoven, L
     * 
     * @param string $input         The input string
     * @param string $separator     The separator between the authors (for the output)
     * @param string $punctuation   The optional punctuation after an initial 
     * @return string
     * @since 8.0.0
     * @access public
     * @uses BIBTEXCREATORPARSE()   This class is a part of bibtexParse
     */
    public static function parse_author_short($input, $separator = ';', $punctuation = '') {
        $all_authors = '';
        $creator = new BIBTEXCREATORPARSE();
        $creatorArray = $creator->parse($input);
        $max = count($creatorArray);
        for ( $i = 0; $i < $max; $i++ ) {
            $one_author = '';
            /* 
             * Set the author name together with the parsing result of bibtexParse
             * 
             * $creatorArray[][0] => firstname
             * $creatorArray[][1] => initials
             * $creatorArray[][2] => surname
             * $creatorArray[][3] => jr
             * $creatorArray[][4] => von
            */
            if ($creatorArray[$i][4] != '') { $one_author .= trim($creatorArray[$i][4]);}
            if ($creatorArray[$i][2] != '') { $one_author .= ' ' .trim($creatorArray[$i][2]) . '';}
            $initials = $creator->getInitials($creatorArray[$i][0], $punctuation);
            $one_author .= ' ' . trim($initials);
            
            // Add author to the main result
            $all_authors .= stripslashes($one_author);
            if ( $i < count($creatorArray) -1 ) {
                $all_authors .= $separator . ' ';
            }
        }
        return $all_authors;
    }
    
    /**
     * This is the original (deprecated) parsing function for author names
     * 
     * Some examples for the parsing:
     * Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F.; van Beethoven, Ludwig
     * 
     * @param string $input     The input string
     * @param string $separator The separator between the authors (for the output)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function parse_author_deprecated ($input, $separator = ';') {
        $all_authors = '';
        $one_author = '';
        $array = explode(" and ",$input);
        $lenth = count ($array);
        for ( $i = 0; $i < $lenth; $i++ ) {
            $array[$i] = trim($array[$i]);
            $names = explode(" ",$array[$i]);
            $lenth2 = count($names);
            for ( $j = 0; $j < $lenth2 - 1; $j++ ) {
                $one_author .= ' ' . trim( $names[$j] );
            }
            $one_author = trim( $names[$lenth2 - 1] ). ', ' . $one_author;
            $all_authors = $all_authors . $one_author;
            if ( $i < $lenth - 1 ) {
                $all_authors .= $separator . ' ';
            }
            $one_author = '';
        }
        return $all_authors;
    }
    
    /**
     * This is the simple parsing function which just replaces the "and" with ","
     * 
     * Some examples for the parsing:
     * Adolf F. Weinhold and Albert Einstein --> Adolf F. Weinhold, Albert Einstein
     * 
     * @param string $input     The input string
     * @param string $separator The separator between the authors (for the output)
     * @return string
     * @since 5.0.0
     * @acces public
     */
    public static function parse_author_simple ($input, $separator = ',') {
        $all_authors = str_replace( array(' and ', '{', '}'), array($separator . ' ', '', ''), $input );
        return stripslashes($all_authors);
    }

    /**
     * Checks if a string is encoded with UTF-8 or not
     * from http://floern.com/webscripting/is-utf8-auf-utf-8-prüfen
     * 
     * @param string $string
     * @return boolean
     * @since 4.2.0
     */
    public static function is_utf8 ($string) {
        $strlen = strlen($string);
        for( $i = 0; $i < $strlen; $i++ ) {
            $ord = ord($string[$i]);
            if( $ord < 0x80 ) { 
                continue;
            }
            elseif( ($ord&0xE0) === 0xC0 && $ord > 0xC1 ) { 
                $n = 1;
            } 
            elseif( ($ord&0xF0) === 0xE0 ) { 
                $n = 2;
            }
            elseif( ($ord&0xF8) === 0xF0 && $ord < 0xF5 ) {
                $n = 3;
            }
            else {
                return false;
            } 
            for($c = 0; $c < $n; $c++) {
                if( ++$i === $strlen || ( ord($string[$i])&0xC0 ) !== 0x80 ) {
                    return false;
                }
            }
        }
        return true;
    }
}
