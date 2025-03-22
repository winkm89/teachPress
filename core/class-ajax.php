<?php
/**
 * This file contains all functions which are used in ajax calls
 * @package teachpress\core\ajax
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions which are used in ajax calls
 * @package teachpress\core\ajax
 * @since 5.0.0
 */
class TP_Ajax {
    /**
     * Adds a document headline
     * @param string $doc_name      The name of the document
     * @param int $course_id        The course ID
     * @since 5.0.0
     * @access public
     * @return int Returns the ID of the new document
     */
    public static function add_document_headline( $doc_name, $course_id ) {
        $file_id = TP_Documents::add_document($doc_name, '', 0, $course_id);
        echo intval($file_id);
    }
    
    /**
     * Changes the name of a document
     * @param int $doc_id          The document ID
     * @param string $doc_name     The name of the document
     * @since 5.0.0
     * @access public
     */
    public static function change_document_name( $doc_id, $doc_name ) {
        TP_Documents::change_document_name($doc_id, $doc_name);
        echo esc_html($doc_name);
    }
    
    /**
     * Deletes a document
     * @param int $doc_id           The document ID
     * @return boolean
     * @since 5.0.0
     * @access public
     */
    public static function delete_document( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = TP_Documents::get_document($doc_id);
        if ( $data['path'] !== '' ) {
            $uploads = wp_upload_dir();
            $test = @ unlink( $uploads['basedir'] . $data['path'] );
            //echo $uploads['basedir'] . $data['path'];
            if ( $test === false ) {
                echo 'false';
                return false;
            }
        }
        TP_Documents::delete_document($doc_id);
        echo 'true';
        return true;
    }
    
    /**
     * Gets the artefact info screen. The info screen is used in the assessment menu of teachPress.
     * @param int $artefact_id      The artefact ID
     * @since 5.0.0
     * @access public
     */
    public static function get_artefact_screen($artefact_id) {
        $artefact = TP_Artefacts::get_artefact($artefact_id);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tp_artefact_id" type="hidden" value="' . intval($artefact_id) . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . esc_html__('Title','teachpress') . '</td>';
        TP_HTML::line('<td><input name="tp_artefact_title" cols="50" value="' . stripslashes($artefact['title']) . '"/></td>');
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_artefact" type="submit" class="button-primary" value="' . esc_html__('Save') . '"/> <input name="tp_delete_artefact" type="submit" class="button-secondary" value="' . esc_html__('Delete','teachpress') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the info screen for a single assessment.
     * @param int $assessment_id       The assessment ID
     * @since 5.0.0
     * @access public
     */
    public static function get_assessment_screen($assessment_id) {
        global $current_user;
        $assessment = TP_Assessments::get_assessment($assessment_id);
        $artefact = TP_Artefacts::get_artefact($assessment['artefact_id']);
        $course_id = ( $assessment['course_id'] !== '' ) ? $assessment['course_id'] : $artefact['course_id'];
        $capability = TP_Courses::get_capability($course_id, $current_user->ID);
        $student = TP_Students::get_student($assessment['wp_id']);
        $examiner = get_userdata($assessment['examiner_id']);

        // Check capability
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            return;
        }

        $artefact['title'] = ( $artefact['title'] == '' ) ? esc_html__('Complete Course','teachpress') : $artefact['title'];
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tp_assessment_id" type="hidden" value="' . intval($assessment_id) . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . esc_html__('Name','teachpress') . '</td>';
        TP_HTML::line( '<td>' . stripslashes($student['firstname']) . ' ' . stripslashes($student['lastname']) . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Artefact','teachpress') . '</td>';
        TP_HTML::line( '<td>' . stripslashes($artefact['title'])  . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Type','teachpress') . '</td>';
        TP_HTML::line( '<td>' . TP_Admin::get_assessment_type_field('tp_type', $assessment['type']) . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Value/Grade','teachpress') . '</td>';
        TP_HTML::line( '<td><input name="tp_value" type="text" size="50" value="' . $assessment['value'] . '" /></td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Comment','teachpress') . '</td>';
        TP_HTML::line( '<td><textarea name="tp_comment" rows="4" cols="50">' . stripslashes($assessment['comment']) . '</textarea></td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Has passed','teachpress') . '</td>';
        TP_HTML::line( '<td>' . TP_Admin::get_assessment_passed_field('tp_passed', $assessment['passed']) . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Date','teachpress') . '</td>';
        TP_HTML::line( '<td>' . $assessment['exam_date'] . '</td>' );
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . esc_html__('Examiner','teachpress') . '</td>';
        TP_HTML::line( '<td>' . stripslashes($examiner->display_name) . '</td>' );
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_assessment" type="submit" class="button-primary" value="' . esc_html__('Save') . '"/> <input name="tp_delete_assessment" type="submit" class="button-secondary" value="' . esc_html__('Delete','teachpress') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets a list of publications of a single author. This function is used for teachpress/admin/show_authors.php
     * @param int $author_id        The authur ID
     * @since 5.0.0
     * @access public
     */
    public static function get_author_publications( $author_id ) {
        $author_id = intval($author_id);
        $pubs = TP_Authors::get_related_publications($author_id, ARRAY_A);
        echo '<ol>';
        foreach ( $pubs as $pub) {
            echo '<li style="padding-left:10px;">';
            TP_HTML::line( '<a target="_blank" title="' . esc_html__('Edit publication','teachpress') .'" href="admin.php?page=teachpress/addpublications.php&pub_id=' . intval($pub['pub_id']) . '">' . TP_HTML::prepare_title($pub['title'], 'decode') . '</a>, ' . stripslashes($pub['type']) . ', ' . intval($pub['year']) );
            if ( $pub['is_author'] == 1 ) {
                echo ' (' . esc_html__('as author','teachpress') . ')';
            }
            if ( $pub['is_editor'] == 1 ) {
                echo ' (' . esc_html__('as editor','teachpress') . ')';
            }
            echo '</li>';
        }
        echo '</ol>';
    }
    
    /**
     * Gets a unique bibtex key from a given string
     * @param string $string
     * @since 6.1.1
     * @access public
     */
    public static function get_generated_bibtex_key ($string) {
        echo TP_Publications::generate_unique_bibtex_key($string);
    }

    /**
     * Gets the cite screen for a single publication.
     * @param int $cite_id       The publication ID
     * @since 6.0.0
     * @access public
     */
    public static function get_cite_screen ($cite_id) {
        $publication = TP_Publications::get_publication($cite_id, ARRAY_A);
        $cite_id = intval($cite_id);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - cite publication</title>';
        echo '</head>';
        echo '<body>';
        echo '<div class="content">';
        echo '<div class="wrap">';
        TP_HTML::line( '<h3 class="nav-tab-wrapper"><a class="nav-tab nav-tab-active tp_cite_text" id="tp_cite_text_' . $cite_id . '" pub_id="' . $cite_id . '">' . esc_html__('Text','teachpress') . '</a> <a class="nav-tab tp_cite_bibtex" id="tp_cite_bibtex_' . $cite_id . '" pub_id="' . $cite_id . '">' . esc_html__('BibTeX','teachpress') . '</a></h3>' );
        echo '<form name="form_cite" method="post">';
        echo '<input name="tp_cite_id" type="hidden" value="' . '"/>';
        TP_HTML::line( '<textarea name="tp_cite_full" id="tp_cite_full_' . $cite_id . '" class="tp_cite_full" rows="7" style="width:100%; border-top:none;" title="' . esc_html__('Publication entry','teachpress') . '">' . TP_Export::text_row($publication) . '</textarea>' );
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the cite text for a publication
     * @param int $cite_id      the publication ID
     * @param string $mode      text or bibtex
     * @access public
     * @since 6.0.0
     */
    public static function get_cite_text ($cite_id, $mode) {
        if ( $mode === 'bibtex' ) {
            $publication = TP_Publications::get_publication($cite_id, ARRAY_A);
            $tags = TP_Tags::get_tags(array('pub_id' => $cite_id, 'output_type' => ARRAY_A));
            TP_HTML::line( TP_Bibtex::get_single_publication_bibtex($publication, $tags) );
        }
        if ( $mode === 'text' ) {
            $publication = TP_Publications::get_publication($cite_id, ARRAY_A);
            TP_HTML::line( TP_Export::text_row($publication) );
        }
    }


    /**
     * Gets the name of a document
     * @param int $doc_id       The ID of the document
     * @since 5.0.0
     * @access public
     */
    public static function get_document_name( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = TP_Documents::get_document($doc_id);
        echo stripslashes($data['name']);
    }
    
    /**
     * Gets the meta field screen for the settings panel
     * @param int $meta_field_id        The meta field ID
     * @since 6.0.0
     * @access public
     */
    public static function get_meta_field_screen ( $meta_field_id ) {
        $meta_field_id = intval( $meta_field_id );
        if ( $meta_field_id === 0 ) {
            $data = [
                'name'          => '',
                'title'         => '',
                'type'          => '',
                'min'           => '',
                'max'           => '',
                'step'          => '',
                'visibility'    => '',
                'required'      => ''
            ];
        }
        else {
            $field = TP_Options::get_option_by_id($meta_field_id);
            $data = TP_DB_Helpers::extract_column_data($field['value']);
        }
        
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Meta Field Screen</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        wp_nonce_field( 'verify_teachpress_settings', 'tp_nonce', true, true );
        echo '<input name="field_edit" type="hidden" value="' . intval($meta_field_id) . '">';
        echo '<table class="form-table">';
        
        // field name
        if ( $meta_field_id === 0 ) {
            echo '<tr>';
            echo '<td><label for="field_name">' . esc_html__('Field name','teachpress') . '</label></td>';
            TP_HTML::line( '<td><input name="field_name" type="text" id="field_name" size="30" title="' . esc_html__('Allowed chars','teachpress') . ': A-Z,a-z,0-9,_" value="' . $data['name'] . '"/></td>' );
            echo '</tr>';
        }
        else {
            TP_HTML::line( '<input name="field_name" id="field_name" type="hidden" value="' . $data['name'] . '">' );
        }
        
        // label
        echo '<tr>';
        echo '<td><label for="field_label">' . esc_html__('Label','teachpress') . '</label></td>';
        TP_HTML::line( '<td><input name="field_label" type="text" id="field_label" size="30" title="' . esc_html__('The visible name of the field','teachpress') . '" value="' . $data['title'] . '" /></td>' );
        echo '</tr>';
        
        // field type
        $field_types = array('TEXT', 'TEXTAREA', 'INT', 'DATE', 'SELECT', 'CHECKBOX', 'RADIO');
        echo '<tr>';
        echo '<td><label for="field_type">' . esc_html__('Field type','teachpress') . '</label></td>';
        echo '<td>';
        echo '<select name="field_type" id="field_type">';
        foreach ( $field_types as $type ) {
            $selected = ( $data['type'] === $type ) ? 'selected="selected"' : '';
            TP_HTML::line( '<option value="' . $type . '" ' . $selected . '>' . $type . '</option>' );
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        // min
        $min = ( $data['min'] === 'false' ) ? '' : intval($min);
        echo '<tr>';
        echo '<td><label for="number_min">' . esc_html__('Min','teachpress') . ' (' . esc_html__('Only for INT fields','teachpress') . ')</label></td>';
        TP_HTML::line( '<td><input name="number_min" id="number_min" type="number" size="10" value="' . $min . '"/></td>' );
        echo '</tr>';
        
        // max
        $max = ( $data['max'] === 'false' ) ? '' : intval($max);
        echo '<tr>';
        echo '<td><label for="number_max">' . esc_html__('Max','teachpress') . ' (' . esc_html__('Only for INT fields','teachpress') . ')</label></td>';
        TP_HTML::line( '<td><input name="number_max" id="number_max" type="number" size="10" value="' . $max . '"/></td>' );
        echo '</tr>';
        
        // step
        $step = ( $data['step'] === 'false' ) ? '' : intval($step);
        echo '<tr>';
        echo '<td><label for="number_step">' . esc_html__('Step','teachpress') . ' (' . esc_html__('Only for INT fields','teachpress') . ')</label></td>';
        TP_HTML::line( '<td><input name="number_step" id="number_step" type="text" size="10" value="' . $step . '"/></td>' );
        echo '</tr>';
        
        // visibility
        echo '<tr>';
        echo '<td><label for="visibility">' . esc_html__('Visibility','teachpress') . '</label></td>';
        echo '<td>';
        echo '<select name="visibility" id="visibility">';
        
        // normal
        $vis_normal = ( $data['visibility'] === 'normal' ) ? 'selected="selected"' : '';
        TP_HTML::line( '<option value="normal" ' . $vis_normal . '>' . esc_html__('Normal','teachpress') . '</option>' );

        // admin
        $vis_admin = ( $data['visibility'] === 'admin' ) ? 'selected="selected"' : '';
        TP_HTML::line( '<option value="admin" ' . $vis_admin . '>' . esc_html__('Admin','teachpress') . '</option>' );

        // hidden
        $vis_hidden = ( $data['visibility'] === 'hidden' ) ? 'selected="selected"' : '';
        TP_HTML::line( '<option value="hidden" ' . $vis_hidden . '>' . esc_html__('Hidden','teachpress') . '</option>' );
        
        echo '</select>';
        echo '</td>';
        echo '</tr>'; 
        
        // required
        $req = ( $data['required'] === 'true' ) ? 'checked="checked"' : '';
        echo '<tr>';
        TP_HTML::line( '<td colspan="2"><input type="checkbox" name="is_required" id="is_required" ' . $req . '/> <label for="is_required">' . esc_html__('Required field','teachpress') . '</label></td>' );
        echo '</tr>';
           
        echo '</table>';
        echo '<p><input type="submit" name="add_field" class="button-primary" value="' . esc_html__('Save','teachpress') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the url of a mimetype image
     * @param string $filename      The filename or the url
     * @since 5.0.0
     * @access public
     */
    public static function get_mimetype_image( $filename ) {
        echo esc_html( TP_Icons::get_class($filename) );
    }

    /**
     * Saves the order of a document list
     * @param array $array      A numeric array which represents the sort order of course documents
     * @since 5.0.0
     * @access public
     */
    public static function set_sort_order( $array ) {
        $i = 0;
        foreach ( $array as $value ) {
            TP_Documents::set_sort( intval($value), $i );
            $i++;
        }
    }
}
