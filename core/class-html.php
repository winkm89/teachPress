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
class TP_HTML {
    
    /**
     * Prints a simple text line with PHP_EOL at the end
     * @param string $text
     * @since 7.2
     */
    public static function line ($text) {
        echo $text . PHP_EOL;
    }
    
    /**
     * Prints a html div init tag
     * @param string $class
     * @param array $args
     * @since 7.2
     */
    public static function div_open ($class = '', $args = array()) {
        $c = ($class !== '') ? ' class="' . $class . '"' : '';
        echo '<div' . $c . '>' . PHP_EOL;
    }
    
    /**
     * Prints a html div close tag
     * @param string $class
     * @since 7.2
     */
    public static function div_close($class = '') {
        // Print the class name as comment in debug mode
        $c = (TEACHPRESS_DEBUG === true && $class !== '') ? '<!-- CLOSE: div: ' . $class . ' -->' : '';
        
        echo '</div>' . $c . PHP_EOL;
    }
    
    /**
     * Gets the second line of the publications with editor, year, volume, address, edition, etc.
     * @param array $row            The data array of a single publication
     * @param array $settings       The settings array
     * @return string
     * @since 6.0.0
    */
    public static function get_publication_meta_row($row, $settings) {
        global $tp_publication_types;
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
            $urldate = TP_HTML::prepare_line('urldate', $row['urldate'],', ' . __('visited','teachpress') . ': ', '', $use_span); 
        }
        
        // for number
        if ( $row['type'] === 'patent' ) {
            $number = isset( $row['number'] ) ? TP_HTML::prepare_line('number', $row['number'],'',', ',$use_span) : '';
        }
        else {
            $number = isset( $row['number'] ) ? TP_HTML::prepare_line('number', $row['number'],'(','), ',$use_span) : '';
        }
        
        // for forthcoming publications
        if ( $row['status'] === 'forthcoming' ) {
            $year = __('Forthcoming','teachpress');
        }
        else {
            $year = isset( $row['year'] ) ? TP_HTML::prepare_line('year', $row['year'],'','',$use_span) : '';
        }
        
        // isset() doesn't work for $editor
        $editor = ( $row['editor'] != '' ) ? TP_Bibtex::parse_author($row['editor'], $settings['editor_separator'], $settings['editor_name']) . ' (' . __('Ed.','teachpress') . '): ' : '';
        $pages = isset( $row['pages'] ) ? TP_HTML::prepare_line('pages', TP_Bibtex::prepare_page_number($row['pages']) , __('pp.','teachpress') . ' ',', ', $use_span) : '';
        $booktitle = isset( $row['booktitle'] ) ? TP_HTML::prepare_line('booktitle', $row['booktitle'],'',', ',$use_span) : '';
        $issuetitle = isset( $row['issuetitle'] ) ? TP_HTML::prepare_line('issuetitle', $row['issuetitle'],'',', ',$use_span) : '';
        $journal = isset( $row['journal'] ) ? TP_HTML::prepare_line('journal', $row['journal'],'',', ',$use_span) : '';
        $volume = isset( $row['volume'] ) ? TP_HTML::prepare_line('volume', $row['volume'],'',' ',$use_span) : '';        
        $publisher = isset( $row['publisher'] ) ? TP_HTML::prepare_line('publisher', $row['publisher'],'',', ',$use_span) : '';
        $address = isset( $row['address'] ) ? TP_HTML::prepare_line('address', $row['address'],'',', ',$use_span) : '';
        $edition = isset( $row['edition'] ) ? TP_HTML::prepare_line('edition', $row['edition'],'',', ',$use_span) : '';
        $chapter = isset( $row['chapter'] ) ? TP_HTML::prepare_line('chapter', $row['chapter'],' ' . __('Chapter','teachpress') . ' ',', ',$use_span) : '';
        $institution = isset( $row['institution'] ) ? TP_HTML::prepare_line('institution', $row['institution'],'',' ',$use_span) : '';
        $organization = isset( $row['organization'] ) ? TP_HTML::prepare_line('organization', $row['organization'],'',' ',$use_span) : '';
        $school = isset( $row['school'] ) ? TP_HTML::prepare_line('school', $row['school'],'',', ',$use_span) : '';
        $series = isset( $row['series'] ) ? TP_HTML::prepare_line('series', $row['series'],'',' ',$use_span) : '';
        $howpublished = isset( $row['howpublished'] ) ? TP_HTML::prepare_line('howpublished', $row['howpublished'],'',', ',$use_span) : '';
        $techtype = isset( $row['techtype'] ) ? TP_HTML::prepare_line('techtype', $row['techtype'],'',', ',$use_span) : '';
        $note = isset( $row['techtype'] ) ? TP_HTML::prepare_line('note', $row['note'],', (',')',$use_span) : '';
        $date = ( array_key_exists('date_format', $settings) === true ) ? TP_HTML::prepare_line('date', date( $settings['date_format'], strtotime($row['date']) ) ,'','',$use_span) : '';
        
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
        
        // end formator
        $type = $tp_publication_types->get_data($row['type']);
        $meta_row_template = '{year}{note}';
        if ( $type !== null ) {
            $meta_row_template = $type['html_meta_row'];
        }
        $replace_pairs = array (
            '{IN}'              => $in,
            '{address}'         => $address,
            '{booktitle}'       => $booktitle,
            '{chapter}'         => $chapter,
            '{date}'            => $date,
            '{editor}'          => $editor,
            '{edition}'         => $edition,
            '{howpublished}'    => $howpublished,
            '{institution}'     => $institution,
            '{isbn}'            => $isbn,
            '{issuetitle}'      => $issuetitle,
            '{journal}'         => $journal,
            '{note}'            => $note,
            '{number}'          => $number,
            '{organization}'    => $organization,
            '{pages}'           => $pages, 
            '{publisher}'       => $publisher,
            '{school}'          => $school,
            '{series}'          => $series,
            '{techtype}'        => $techtype,
            '{urldate}'         => $urldate,
            '{volume}'          => $volume, 
            '{year}'            => $year,
        );
        $end = strtr($meta_row_template, $replace_pairs) . '.';
        
        return stripslashes($end);
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
