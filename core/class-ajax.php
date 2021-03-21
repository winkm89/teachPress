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
     */
    public static function add_document_headline( $doc_name, $course_id ) {
        $file_id = TP_Documents::add_document($doc_name, '', 0, $course_id);
        echo $file_id;
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
        echo $doc_name;
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
        echo '<input name="tp_artefact_id" type="hidden" value="' . $artefact_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Title','teachpress') . '</td>';
        echo '<td><input name="tp_artefact_title" cols="50" value="' . stripslashes($artefact['title']) . '"/></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_artefact" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tp_delete_artefact" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/></p>';
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

        $artefact['title'] = ( $artefact['title'] == '' ) ? __('Complete Course','teachpress') : $artefact['title'];
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tp_assessment_id" type="hidden" value="' . $assessment_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Name','teachpress') . '</td>';
        echo '<td>' . stripslashes($student['firstname']) . ' ' . stripslashes($student['lastname']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Artefact','teachpress') . '</td>';
        echo '<td>' . stripslashes($artefact['title'])  . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Type','teachpress') . '</td>';
        echo '<td>' . TP_Admin::get_assessment_type_field('tp_type', $assessment['type']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Value/Grade','teachpress') . '</td>';
        echo '<td><input name="tp_value" type="text" size="50" value="' . $assessment['value'] . '" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Comment','teachpress') . '</td>';
        echo '<td><textarea name="tp_comment" rows="4" cols="50">' . stripslashes($assessment['comment']) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Has passed','teachpress') . '</td>';
        echo '<td>' . TP_Admin::get_assessment_passed_field('tp_passed', $assessment['passed']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Date','teachpress') . '</td>';
        echo '<td>' . $assessment['exam_date'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Examiner','teachpress') . '</td>';
        echo '<td>' . stripslashes($examiner->display_name) . '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_assessment" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tp_delete_assessment" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/></p>';
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
            echo '<a target="_blank" title="' . __('Edit publication','teachpress') .'" href="admin.php?page=teachpress/addpublications.php&pub_id=' . $pub['pub_id'] . '">' . TP_HTML::prepare_title($pub['title'], 'decode') . '</a>, ' . stripslashes($pub['type']) . ', ' . $pub['year'];
            if ( $pub['is_author'] == 1 ) {
                echo ' (' . __('as author','teachpress') . ')';
            }
            if ( $pub['is_editor'] == 1 ) {
                echo ' (' . __('as editor','teachpress') . ')';
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
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - cite publication</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<div class="wrap">';
        echo '<h3 class="nav-tab-wrapper"><a class="nav-tab nav-tab-active tp_cite_text" id="tp_cite_text_' . $cite_id . '" pub_id="' . $cite_id . '">' . __('Text','teachpress') . '</a> <a class="nav-tab tp_cite_bibtex" id="tp_cite_bibtex_' . $cite_id . '" pub_id="' . $cite_id . '">' . __('BibTeX','teachpress') . '</a></h3>';
        echo '<form name="form_cite" method="post">';
        echo '<input name="tp_cite_id" type="hidden" value="' . '"/>';
        echo '<textarea name="tp_cite_full" id="tp_cite_full_' . $cite_id . '" class="tp_cite_full" rows="7" style="width:100%; border-top:none;">' . TP_Export::text_row($publication) . '</textarea>';
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
            echo TP_Bibtex::get_single_publication_bibtex($publication, $tags);
        }
        if ( $mode === 'text' ) {
            $publication = TP_Publications::get_publication($cite_id, ARRAY_A);
            echo TP_Export::text_row($publication);
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
        if ( $meta_field_id === 0 ) {
            $data = array(
                'name' => '',
                'title' => '',
                'type' => '',
                'min' => '',
                'max' => '',
                'step' => '',
                'visibility' => '',
                'required'
            );
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
        echo '<input name="field_edit" type="hidden" value="' . $meta_field_id . '">';
        echo '<table class="form-table">';
        
        // field name
        if ( $meta_field_id === 0 ) {
            echo '<tr>';
            echo '<td><label for="field_name">' . __('Field name','teachpress') . '</label></td>';
            echo '<td><input name="field_name" type="text" id="field_name" size="30" title="' . __('Allowed chars','teachpress') . ': A-Z,a-z,0-9,_" value="' . $data['name'] . '"/></td>';
            echo '</tr>';
        }
        else {
            echo '<input name="field_name" id="field_name" type="hidden" value="' . $data['name'] . '">';
        }
        
        // label
        echo '<tr>';
        echo '<td><label for="field_label">' . __('Label','teachpress') . '</label></td>';
        echo '<td><input name="field_label" type="text" id="field_label" size="30" title="' . __('The visible name of the field','teachpress') . '" value="' . $data['title'] . '" /></td>';
        echo '</tr>';
        
        // field type
        $field_types = array('TEXT', 'TEXTAREA', 'INT', 'DATE', 'SELECT', 'CHECKBOX', 'RADIO');
        echo '<tr>';
        echo '<td><label for="field_type">' . __('Field type','teachpress') . '</label></td>';
        echo '<td>';
        echo '<select name="field_type" id="field_type">';
        foreach ( $field_types as $type ) {
            $selected = ( $data['type'] === $type ) ? 'selected="selected"' : '';
            echo '<option value="' . $type . '" ' . $selected . '>' . $type . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        // min
        $min = ( $data['min'] === 'false' ) ? '' : intval($min);
        echo '<tr>';
        echo '<td><label for="number_min">' . __('Min','teachpress') . ' (' . __('Only for INT fields','teachpress') . ')</label></td>';
        echo '<td><input name="number_min" id="number_min" type="number" size="10" value="' . $min . '"/></td>';
        echo '</tr>';
        
        // max
        $max = ( $data['max'] === 'false' ) ? '' : intval($max);
        echo '<tr>';
        echo '<td><label for="number_max">' . __('Max','teachpress') . ' (' . __('Only for INT fields','teachpress') . ')</label></td>';
        echo '<td><input name="number_max" id="number_max" type="number" size="10" value="' . $max . '"/></td>';
        echo '</tr>';
        
        // step
        $step = ( $data['step'] === 'false' ) ? '' : intval($step);
        echo '<tr>';
        echo '<td><label for="number_step">' . __('Step','teachpress') . ' (' . __('Only for INT fields','teachpress') . ')</label></td>';
        echo '<td><input name="number_step" id="number_step" type="text" size="10" value="' . $step . '"/></td>';
        echo '</tr>';
        
        // visibility
        echo '<tr>';
        echo '<td><label for="visibility">' . __('Visibility','teachpress') . '</label></td>';
        echo '<td>';
        echo '<select name="visibility" id="visibility">';
        
        // normal
        $vis_normal = ( $data['visibility'] === 'normal' ) ? 'selected="selected"' : '';
        echo '<option value="normal" ' . $vis_normal . '>' . __('Normal','teachpress') . '</option>';

        // admin
        $vis_admin = ( $data['visibility'] === 'admin' ) ? 'selected="selected"' : '';
        echo '<option value="admin" ' . $vis_admin . '>' . __('Admin','teachpress') . '</option>';

        // hidden
        $vis_hidden = ( $data['visibility'] === 'hidden' ) ? 'selected="selected"' : '';
        echo '<option value="hidden" ' . $vis_hidden . '>' . __('Hidden','teachpress') . '</option>';
        
        echo '</select>';
        echo '</td>';
        echo '</tr>'; 
        
        // required
        $req = ( $data['required'] === 'true' ) ? 'checked="checked"' : '';
        echo '<tr>';
        echo '<td colspan="2"><input type="checkbox" name="is_required" id="is_required" ' . $req . '/> <label for="is_required">' . __('Required field','teachpress') . '</label></td>';
        echo '</tr>';
           
        echo '</table>';
        echo '<p><input type="submit" name="add_field" class="button-primary" value="' . __('Save','teachpress') . '"/></p>';
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
        echo TP_Icons::get_class($filename);
    }

    /**
     * Saves the order of a document list
     * @param array $array      A numeric array which represents the sort order of course documents
     * @since 5.0.0
     * @access public
     */
    public static function set_sort_order( $array ) {
        $i = 0;
        foreach ($array as $value) {
            TP_Documents::set_sort($value, $i);
            $i++;
        }
    }
}
