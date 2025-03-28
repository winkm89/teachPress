<?php
/**
 * This file contains all general functions for the export system
 * 
 * @package teachpress\core\export
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * teachPress export class
 *
 * @package teachpress\core\export
 * @since 3.0.0
 */
class TP_Export {

    /**
     * Print html table with registrations
     * @param int $course_id
     * @param array $option
     * @param int $waitinglist 
     * @since 3.0.0
     * @access private
     */
    private static function get_course_registration_table($course_id, $option, $waitinglist = '') {
        $row = TP_Courses::get_signups( [
                    'course_id'     => $course_id, 
                    'waitinglist'   => $waitinglist, 
                    'output_type'   => ARRAY_A, 
                    'order'         => 'st.lastname ASC'] 
                );
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__('Last name','teachpress') . '</th>';
        echo '<th>' . esc_html__('First name','teachpress') . '</th>';
        echo '<th>' . esc_html__('User account','teachpress') . '</th>';
        echo '<th>' . esc_html__('E-Mail') . '</th>';
        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        foreach ( $fields as $field ) {
            $data = TP_DB_Helpers::extract_column_data($field->value);
            if ( $data['visibility'] === 'admin') {
                TP_HTML::line( '<th>' . stripslashes(utf8_decode($data['title'])) . '</th>' );
            }
        }
        echo '<th>' . esc_html__('Registered at','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';  
        echo '<tbody>';
        foreach($row as $row) {
            $row['firstname'] = TP_Export::decode($row['firstname']);
            $row['lastname'] = TP_Export::decode($row['lastname']);
            $row['course_of_studies'] = TP_Export::decode($row['course_of_studies']);
            echo '<tr>';
            TP_HTML::line( '<td>' . stripslashes(utf8_decode($row['lastname'])) . '</td>' );
            TP_HTML::line( '<td>' . stripslashes(utf8_decode($row['firstname'])) . '</td>' );
            TP_HTML::line( '<td>' . stripslashes(utf8_decode($row['userlogin'])) . '</td>' );
            TP_HTML::line( '<td>' . $row['email'] . '</td>' );
            foreach ( $fields as $field ) {
                $data = TP_DB_Helpers::extract_column_data($field->value);
                if ( $data['visibility'] === 'admin') {
                    TP_HTML::line( '<td>' . stripslashes( utf8_decode( TP_Export::decode($row[$field->variable]) ) ) . '</td>' );
                }
            }
            TP_HTML::line( '<td>' . $row['date'] . '</td>' );
            echo '</tr>';

        }
        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Export course data in xls format
     * @param int $course_id 
     * @since 3.0.0
     */
    public static function get_course_xls($course_id) {
        global $current_user;
        $parent = '';
        
        // check capabilities
        $capability = TP_Courses::get_capability($course_id, $current_user->ID);
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            echo esc_html__('Access denied','teachpress');
            return;
        }

        // load course data
        $data = TP_Courses::get_course($course_id, ARRAY_A);
        $course_name = $data['name'];
        if ($data['parent'] != '0') {
            $parent = TP_Courses::get_course($data['parent'], ARRAY_A);
            $course_name = $parent['name'] . ' ' . $data['name'];
        }

        // load settings
        $option['regnum'] = get_tp_option('regnum');
        $option['studies'] = get_tp_option('studies');

        TP_HTML::line( '<h2>' . stripslashes(utf8_decode($course_name)) . ' ' . stripslashes(utf8_decode($data['semester'])) . '</h2>' );
        echo '<table border="1" cellspacing="0" cellpadding="5">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__('Lecturer','teachpress') . '</th>';
        TP_HTML::line( '<td>' . stripslashes(utf8_decode($data['lecturer'])) . '</td>' );
        echo '<th>' . esc_html__('Date','teachpress') . '</th>';
        TP_HTML::line( '<td>' . $data['date'] . '</td>' );
        echo '<th>' . esc_html__('Room','teachpress') . '</th>';
        TP_HTML::line( '<td>' . stripslashes(utf8_decode($data['room'])) . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . esc_html__('Places','teachpress') . '</th>';
        TP_HTML::line( '<td>' . $data['places'] . '</td>' );
        echo '<th>' . esc_html__('free places','teachpress') . '</th>';
        $free_places = TP_Courses::get_free_places($data["course_id"], $data["places"]);
        TP_HTML::line( '<td>' . $free_places . '</td>' );
        echo '<td>&nbsp;</td>';
        echo '<td>&nbsp;</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . esc_html__('Comment','teachpress') . '</th>';
        TP_HTML::line( '<td colspan="5">' . stripslashes(utf8_decode($data['comment'])) . '</td>' );
        echo '</tr>';
        echo '</thead>';
        echo '</table>';

        echo '<h3>' . esc_html__('Registered participants','teachpress') . '</h3>'; 
        self::get_course_registration_table($course_id, $option, 0);
        echo '<h3>' . esc_html__('Waiting list','teachpress') . '</h3>'; 
        self::get_course_registration_table($course_id, $option, 1);

        global $tp_version;
        TP_HTML::line( '<p style="font-size:11px; font-style:italic;">' . esc_html__('Created on','teachpress') . ': ' . date("d.m.Y") . ' | teachPress ' . $tp_version . '</p>' );
    }

    /**
     * Export course data in csv format
     * @param int $course_id
     * @param array $options 
     * @since 3.0.0
     */
    public static function get_course_csv($course_id) {
        global $current_user;
        
        // check capabilities
        $capability = TP_Courses::get_capability($course_id, $current_user->ID);
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            echo esc_html__('Access denied','teachpress');
            return;
        }
        
        // load settings
        $option['regnum'] = get_tp_option('regnum');
        $option['studies'] = get_tp_option('studies');
        $row = TP_Courses::get_signups( array('course_id' => $course_id, 'waitinglist' => 0, 'output_type' => ARRAY_A, 'order' => 'st.lastname ASC') );
        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        
        $extra_headlines = '';
        foreach ( $fields as $field ) {
            $data = TP_DB_Helpers::extract_column_data($field->value);
            if ( $data['visibility'] === 'admin') {
                $extra_headlines .= '"' . stripslashes( utf8_decode( $data['title'] ) ) . '";';
            }
        }

        $headline = '"' . esc_html__('Last name','teachpress') . '";"' . esc_html__('First name','teachpress') . '";"' . esc_html__('User account','teachpress') . '";"' . esc_html__('E-Mail') . '";' . $extra_headlines . '"' . esc_html__('Registered at','teachpress') . '";"' . esc_html__('Record-ID','teachpress') . '";"' . esc_html__('Waiting list','teachpress') . '"';
        $headline = TP_Export::decode($headline);
        TP_HTML::line( $headline );
        foreach($row as $row) {
            $row['firstname'] = TP_Export::decode($row['firstname']);
            $row['lastname'] = TP_Export::decode($row['lastname']);
            
            $values = '';
            foreach ( $fields as $field ) {
                $data = TP_DB_Helpers::extract_column_data($field->value);
                if ( $data['visibility'] === 'admin') {
                    $values .= '"' . stripslashes( utf8_decode( TP_Export::decode( $row[$field->variable] ) ) ) . '";';
                }
            }

            TP_HTML::line( '"' . stripslashes(utf8_decode($row['lastname'])) . '";"' . stripslashes(utf8_decode($row['firstname'])) . '";"' . stripslashes(utf8_decode($row['userlogin'])) . '";"' . $row['email'] . '";' . $values . '"' . $row['date'] . '";"' . $row['con_id'] . '";"' . $row['waitinglist'] . '"');
        }
    }

    /**
     * Export publications
     * @param int $user_id 
     * @param string $format - bibtex or rtf
     * @param boolean $convert_bibtex   Convert utf-8 chars to bibtex special chars, default: false
     * @param boolean $private_comments Add private comments as annote field, default: false
     * @sinsce 4.2.0 
     */
    public static function get_publications($user_id, $format = 'bibtex', $convert_bibtex = false, $private_comments = false) {
        
        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);
        
        $row = TP_Publications::get_publications( array('user' => intval($user_id), 'output_type' => ARRAY_A) );
        
        // Export BibTeX
        if ( $format === 'bibtex' ) {
            foreach ($row as $row) {
                $tags = TP_Tags::get_tags( array('pub_id' => $row['pub_id'], 'output_type' => ARRAY_A ) );
                TP_HTML::line( TP_Bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex, $private_comments) );
            }
        }
        
        // Export RTF
        if ( $format === 'rtf' ) {
            TP_HTML::line( self::rtf($row) );
        }
    }
    
    /**
     * Export a selection of publications
     * @param string $selection         A string of publication IDs which are separated by comma
     * @param string $format            bibtex or rtf
     * @param boolean $convert_bibtex   Convert utf-8 chars to bibtex special chars, default: false
     * @param boolean $private_comment  Add private comments as annote field, default: false
     * @since 5.0.0
     */
    public static function get_selected_publications($selection, $format = 'bibtex', $convert_bibtex = false, $private_comment = false) {
        $row = TP_Publications::get_publications( array( 'include' => $selection, 'output_type' => ARRAY_A) );
        
        if ( $format === 'bibtex' ) {
            foreach ($row as $row) {
                $tags = TP_Tags::get_tags( array('pub_id' => $row['pub_id'], 'output_type' => ARRAY_A) );
                TP_HTML::line( TP_Bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex, $private_comment) );
            }
        }     
        if ( $format === 'rtf' ) {
            TP_HTML::line( self::rtf($row) );
        }
    }


    /**
     * Export a single publication
     * @param string $bibtex_key
     * @since 4.2.0
     */
    public static function get_publication_by_key($bibtex_key) {
        $convert_bibtex = ( get_tp_option('convert_bibtex') == '1' ) ? true : false;
        $row = TP_Publications::get_publication_by_key($bibtex_key, ARRAY_A);
        $tags = TP_Tags::get_tags( array( 'pub_id' => $row['pub_id'], 'output_type' => ARRAY_A ) );
        TP_HTML::line( TP_Bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex) );
    }

    /**
     * Generate rtf document format
     * @param array $row
     * @return string
     * @since 3.0.0
     * @access private
     */
    private static function rtf ($row) {
        $head = '{\rtf1';
        $line = '';
        foreach ($row as $row) {
            $line .= self::rtf_row($row) . '\par'. '\par';
        }
        $foot = '}';
        return $head . $line . $foot;
    }

    /**
     * Returns a single line for rtf file
     * @param array $row        The publication array
     * @return string
     * @since 3.0.0
     * @access public
    */
    public static function rtf_row ($row) {
        $settings = array(
            'author_name'       => 'initials',
            'editor_name'       => 'initials',
            'editor_separator'  => ';',
            'style'             => 'simple',
            'meta_label_in'     => esc_html__('In','teachpress') . ': ',
            'use_span'          => false
        );
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = TP_Bibtex::parse_author($row['editor'], ';', $settings['editor_name'] ) . ' (' . esc_html__('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = TP_Bibtex::parse_author($row['author'], ';', $settings['author_name'] );
        }
        $meta = TP_HTML_Publication_Template::get_publication_meta_row($row, $settings);
        $line = $all_authors . ': ' . TP_HTML::prepare_title($row['title'], 'replace') . '. ' . $meta;
        $line = str_replace('  ', ' ', $line);
        $line = utf8_decode(self::decode($line));
        return $line;
    }
    
    /**
     * Returns a single line for a utf8 encoded text
     * @param array $row        The publication array
     * @return string
     * @since 6.0.0
     * @access public
    */
    public static function text_row ($row) {
        $settings = array(
            'author_name'       => 'initials',
            'editor_name'       => 'initials',
            'editor_separator'  => ';',
            'style'             => 'simple',
            'meta_label_in'     => esc_html__('In','teachpress') . ': ',
            'use_span'          => false
        );
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = TP_Bibtex::parse_author($row['editor'], ';', $settings['editor_name'] ) . ' (' . esc_html__('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = TP_Bibtex::parse_author($row['author'], ';', $settings['author_name'] );
        }
        $meta = TP_HTML_Publication_Template::get_publication_meta_row($row, $settings);
        $line = $all_authors . ': ' . TP_HTML::prepare_title($row['title'], 'replace') . '. ' . $meta;
        $line = str_replace('  ', ' ', $line);
        return trim($line);
    }

    /**
     * Decode chars with wrong charset to UTF-8
     * @param string $char
     * @return string
     * @since 3.0.0
     * @access private 
    */
    private static function decode ($char) {
        $array_1 = array('–', 'Ã¼', 'Ã¶', 'Ã¤', 'Ã¤', 'Ã?', 'Â§', 'Ãœ', 'Ã', 'Ã–','&Uuml;','&uuml;', '&Ouml;', '&ouml;', '&Auml;','&auml;', '&nbsp;', '&szlig;', '&sect;', '&ndash;', '&rdquo;', '&ldquo;', '&eacute;', '&egrave;', '&aacute;', '&agrave;', '&ograve;','&oacute;', '&copy;', '&reg;', '&micro;', '&pound;', '&raquo;', '&laquo;', '&yen;', '&Agrave;', '&Aacute;', '&Egrave;', '&Eacute;', '&Ograve;', '&Oacute;', '&shy;', '&amp;', '&quot;',);
        $array_2 = array('-', 'ü', 'ö', 'ä', 'ä', 'ß', '§', 'Ü', 'Ä', 'Ö', 'Ü', 'ü', 'Ö', 'ö', 'Ä', 'ä', ' ', 'ß', '§', '-', '”', '“', 'é', 'è', 'á', 'à', 'ò', 'ó', '©', '®', 'µ', '£', '»', '«', '¥', 'À', 'Á', 'È', 'É', 'Ò', 'Ó', '­', '&', '"');
        $char = str_replace($array_1, $array_2, $char);
        return $char;
    }
}
