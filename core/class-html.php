<?php
/**
 * This file contains all functions of the tp_html class
 * @package teachpress\core\html
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.10
 */

/**
 * This class contains some HTML formatting functions
 * @package teachpress\core\html
 * @since 5.0.10
 */
class tp_html {
    
    /**
     * Gets the second line of the publications with editor, year, volume, address, edition, etc.
     * @param array $row            The data array of a single publication
     * @param array $settings       The settings array
     * @return string
     * @since 6.0.0
    */
    public static function get_publication_meta_row($row, $settings) {
        $use_span = $settings['use_span']; 
        // For ISBN or ISSN number
        $isbn = '';
        if ( $row['isbn'] != '' ) {
            // test if ISBN or ISSN
            $after = ( $use_span === true ) ? '</span>' : ''; 
            if ($row['is_isbn'] == '0') {
                $before = ( $use_span === true ) ? '<span class="tp_pub_additional_issn">' : '';
                $isbn = ', ' . $before . 'ISSN: ' . $row['isbn'] . $after; 
            }
            else {
                $before = ( $use_span === true ) ? '<span class="tp_pub_additional_isbn">' : '';
                $isbn = ', ' . $before . 'ISBN: ' . $row['isbn'] . $after;
            }
        }
        
        // for urldate
        $urldate = '';
        if ( isset( $row['urldate'] ) && $row['urldate'] !== '0000-00-00'  ) {
            $row['urldate'] = ( array_key_exists('date_format', $settings) === true ) ? date( $settings['date_format'], strtotime($row['urldate']) ) : $row['urldate'];
            $urldate = tp_html::prepare_line('urldate', $row['urldate'],', ' . __('visited','teachpress') . ': ', '', $use_span); 
        }
        
        // for forthcoming publications
        if ( $row['status'] === 'forthcoming' ) {
            $year = __('Forthcoming','teachpress');
        }
        else {
             $year = isset( $row['year'] ) ? tp_html::prepare_line('year', $row['year'],'','',$use_span) : '';
        }
        
        // isset() doesn't work for $editor
        $editor = ( $row['editor'] != '' ) ? tp_bibtex::parse_author($row['editor'], $settings['editor_name']) . ' (' . __('Ed.','teachpress') . '): ' : '';
        $pages = isset( $row['pages'] ) ? tp_html::prepare_line('pages', tp_bibtex::prepare_page_number($row['pages']) , __('pp.','teachpress') . ' ',', ', $use_span) : '';
        $booktitle = isset( $row['booktitle'] ) ? tp_html::prepare_line('booktitle', $row['booktitle'],'',', ',$use_span) : '';
        $issuetitle = isset( $row['issuetitle'] ) ? tp_html::prepare_line('issuetitle', $row['issuetitle'],'',', ',$use_span) : '';
        $journal = isset( $row['journal'] ) ? tp_html::prepare_line('journal', $row['journal'],'',', ',$use_span) : '';
        $volume = isset( $row['volume'] ) ? tp_html::prepare_line('volume', $row['volume'],'',' ',$use_span) : '';
        $number = isset( $row['number'] ) ? tp_html::prepare_line('number', $row['number'],'(','), ',$use_span) : '';
        $publisher = isset( $row['publisher'] ) ? tp_html::prepare_line('publisher', $row['publisher'],'',', ',$use_span) : '';
        $address = isset( $row['address'] ) ? tp_html::prepare_line('address', $row['address'],'',', ',$use_span) : '';
        $edition = isset( $row['edition'] ) ? tp_html::prepare_line('edition', $row['edition'],'',', ',$use_span) : '';
        $chapter = isset( $row['chapter'] ) ? tp_html::prepare_line('chapter', $row['chapter'],' ' . __('Chapter','teachpress') . ' ',', ',$use_span) : '';
        $institution = isset( $row['institution'] ) ? tp_html::prepare_line('institution', $row['institution'],'',' ',$use_span) : '';
        $organization = isset( $row['organization'] ) ? tp_html::prepare_line('organization', $row['organization'],'',' ',$use_span) : '';
        $school = isset( $row['school'] ) ? tp_html::prepare_line('school', $row['school'],'',', ',$use_span) : '';
        $series = isset( $row['series'] ) ? tp_html::prepare_line('series', $row['series'],'',' ',$use_span) : '';
        $howpublished = isset( $row['howpublished'] ) ? tp_html::prepare_line('howpublished', $row['howpublished'],'',', ',$use_span) : '';
        $techtype = isset( $row['techtype'] ) ? tp_html::prepare_line('techtype', $row['techtype'],'',', ',$use_span) : '';
        $note = isset( $row['techtype'] ) ? tp_html::prepare_line('note', $row['note'],', (',')',$use_span) : '';
        
        // special cases for volume/number
        if ( $number == '' && $volume != '' ) {
            $number = ', ';
        }
        
        // special cases for article/incollection/inbook/inproceedings
        $in = '';
        if ( $settings['style'] === 'simple' || $settings['style'] === 'numbered' ) {
            if ( $row['type'] === 'article' || $row['type'] === 'inbook' || $row['type'] === 'incollection' || $row['type'] === 'inproceedings') {
                $in = __('In','teachpress') . ': ';
            }
        }

        // end format after type
        if ($row['type'] === 'article') {
            $end = $in . $journal . $volume . $number . $pages . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'book') {
            $end = $edition . $publisher . $address . $year . $isbn . $note .'.';
        }
        elseif ($row['type'] === 'booklet') {
            $end = $howpublished . $address . $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'collection') {
            $end = $edition . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'conference') {
            $end = $booktitle . $volume . $number . $series . $organization . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inbook') {
            $end = $in . $editor . $booktitle . $volume . $number . $chapter . $pages . $publisher . $address . $edition. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'incollection') {
            $end = $in . $editor . $booktitle . $volume . $number . $pages . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inproceedings') {
            $end = $in . $editor . $booktitle . $pages . $organization . $publisher . $address. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'manual') {
            $end = $editor . $organization . $address. $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] == 'mastersthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'misc') {
            $end = $howpublished . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'online') {
            $end = $editor . $organization . $year . $urldate . $note . '.';
        }
        elseif ($row['type'] === 'periodical') {
            $end = $issuetitle . $series . $volume . $number . $year . $urldate . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'phdthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'presentation') {
            $date = ( array_key_exists('date_format', $settings) === true ) ? ', ' . tp_html::prepare_line('date', date( $settings['date_format'], strtotime($row['date']) ) ,'','',$use_span) : '';
            $end = ( $howpublished === '' && $row['address'] === '' ) ? substr($date,2) . $note . '.' : $howpublished . tp_html::prepare_line('address', $row['address'],'','',$use_span) . $date . $note . '.';
        }
        elseif ($row['type'] === 'proceedings') {
            $end = $howpublished . $organization. $publisher. $address . $volume . $number . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'techreport') {
            $end = $institution . $address . $techtype . $number. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'unpublished') {
            $end = $year . $isbn . $note . '.';
        }
        else {
            $end = $year . $note . '.';
        }
        $end = stripslashes($end);
        return $end;
    }
    
    /**
     * Prepares a single HTML line with the input from one publication field
     * @param string $element
     * @param string $content
     * @param string $before
     * @param string $after
     * @param string $use_span 
     * @return string
     * @since 6.0.0
     */
    public static function prepare_line($element, $content, $before = '', $after = '', $use_span = false) {
        if ( $content === '' ) {
            return '';
        }
        if ( $use_span === true ) {
            return '<span class="tp_pub_additional_' . $element . '">' . $before . $content . $after . '</span>';
        }
        return $before . $content . $after;
    }
    
    /**
     * Prepares a title string for normal html output. Works like htmlspecialchars_decode, but with a white list
     * @param string $input     The input string
     * @param string $mode      decode or replace
     * @return string
     * @since 5.0.10
     * @access public
     */
    public static function prepare_title ($input, $mode = 'decode') {
        $search = array('&lt;sub&gt;', '&lt;/sub&gt;',
                        '&lt;sup&gt;', '&lt;/sup&gt;',
                        '&lt;small&gt;', '&lt;/small&gt;',
                        '&lt;i&gt;', '&lt;/i&gt;',
                        '&lt;b&gt;', '&lt;/b&gt;',
                        '&lt;s&gt;', '&lt;/s&gt;',
                        '&lt;del&gt;', '&lt;/del&gt;',
                        '&lt;em&gt;', '&lt;/em&gt;',
                        '&lt;u&gt;', '&lt;/u&gt;');
        
        if ( $mode === 'decode' ) {
            $replace = array('<sub>', '</sub>', 
                             '<sup>', '</sup>',
                             '<small>', '</small>',
                             '<i>', '</i>',
                             '<b>', '</b>', 
                             '<s>', '</s>',
                             '<del>', '</del>',
                             '<em>', '</em>', 
                             '<u>', '</u>' );
        }
        else {
            $replace = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        }
        
        $output = str_replace($search, $replace, $input);
        return stripslashes($output);
    }
    
    /**
     * Prepares a text for normal html output. Works like htmlspecialchars_decode, but with a white list
     * @param string $input
     * @return string
     * @since 5.0.10
     * @access public
     */
    public static function prepare_text ($input) {
        $search = array('&lt;sub&gt;', '&lt;/sub&gt;',
                        '&lt;sup&gt;', '&lt;/sup&gt;',
                        '&lt;i&gt;', '&lt;/i&gt;',
                        '&lt;b&gt;', '&lt;/b&gt;',
                        '&lt;s&gt;', '&lt;/s&gt;',
                        '&lt;em&gt;', '&lt;/em&gt;',
                        '&lt;u&gt;', '&lt;/u&gt;',
                        '&lt;ul&gt;', '&lt;/ul&gt;', 
                        '&lt;li&gt;', '&lt;/li&gt;', 
                        '&lt;ol&gt;', '&lt;/ol&gt;' );
        $replace = array('<sub>', '</sub>', 
                         '<sup>', '</sup>',
                         '<i>', '</i>',
                         '<b>', '</b>', 
                         '<s>', '</s>',
                         '<em>', '</em>', 
                         '<u>', '</u>', 
                         '<ul>', '</ul>', 
                         '<li>', '</li>', 
                         '<ol>', '</ol>' );
        $output = str_replace($search, $replace, $input);
        return nl2br(stripslashes($output));
    }
    
    /**
     * Converts some HTML special chars with the UTF-8 versions
     * @param string $input
     * @return string
     * @since 6.0.0
    */
    public static function convert_special_chars ($input) {
        $array_1 = array('&Uuml;','&uuml;',
                         '&Ouml;','&ouml;','&ograve;','&oacute;','&Ograve;','&Oacute;',
                         '&Auml;','&auml;','&aacute;','&agrave;','&Agrave;','&Aacute;',
                         '&eacute;','&egrave;','&Egrave;','&Eacute;',
                         '&sect;','&copy;','&reg;','&pound;','&yen;',
                         '&szlig;','&micro;','&amp;',
                         '&nbsp;','&ndash;','&rdquo;','&ldquo;','&raquo;','&laquo;','&shy;','&quot;');
        $array_2 = array('Ü','ü',
                         'Ö','ö','ò','ó','Ò','Ó',
                         'Ä','ä','á','à','À','Á',
                         'é','è','È','É',
                         '§','©','®','£','¥',
                         'ß','µ','&',
                         ' ','-','”','“','»','«','­','"');
        $input = str_replace($array_1, $array_2, $input);
        return $input;
    }
}
