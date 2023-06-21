<?php
/**
 * This file contains the ajax callback function
 * 
 * @package teachpress\core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * AJAX callback function
 * @since 6.0.0
 */
function tp_ajax_callback () {
    
    // Check permissions
    if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
        
        /**
         * Getting author's publications (for show_authors.php)
         * Works if $_GET['author_id'] is given
         */
        $author_id = ( isset( $_GET['author_id'] ) ) ? intval( $_GET['author_id'] ) : 0;
        if ( $author_id !== 0 ) {
            TP_Ajax::get_author_publications($author_id);
        }
        
        /**
         * Getting assessment screen (for show_single_course.php)
         * Works if $_GET['assessment_id'] is given
         */
        $assessment_id = ( isset( $_GET['assessment_id'] ) ) ? intval( $_GET['assessment_id'] ) : 0;
        if ( $assessment_id !== 0 ) {
            TP_Ajax::get_assessment_screen($assessment_id);
        }
        
        /**
         * Getting artefact screen (for show_single_course.php)
         * Works if $_GET['artefact_id'] is given
         */
        $artefact_id = ( isset( $_GET['artefact_id'] ) ) ? intval( $_GET['artefact_id'] ) : 0;
        if ( $artefact_id !== 0 ) {
            TP_Ajax::get_artefact_screen($artefact_id);
        }
        
        /**
         * Removing documents
         * Works if $_GET['del_document'] is given
         */
        $del_document = ( isset( $_GET['del_document'] ) ) ? intval( $_GET['del_document'] ) : 0;
        if ( $del_document !== 0 ) {
            TP_Ajax::delete_document($del_document);
        }

        /**
         * Adding document headlines
         * Works if $_GET['add_document'] and $_GET['course_id'] are given
         */
        $add_document = ( isset( $_GET['add_document'] ) ) ? htmlspecialchars( $_GET['add_document'] ) : '';
        $course_id = ( isset( $_GET['course_id'] ) ) ? intval( $_GET['course_id'] ) : 0;
        if ( $add_document !== '' && $course_id !== 0 ) {
            TP_Ajax::add_document_headline($add_document, $course_id);
        }

        /**
         * Getting a document name
         * Works if $_GET['get_document_name'] is given
         */
        $get_document_name = ( isset( $_GET['get_document_name'] ) ) ? intval( $_GET['get_document_name'] ) : 0;
        if ( $get_document_name !== 0 ) {
            TP_Ajax::get_document_name($get_document_name);
        }

        /**
         * Changing a document name
         * Works if $_POST['change_document'] and $_POST['new_document_name'] are given
         */
        $change_document = ( isset( $_POST['change_document'] ) ) ? intval( $_POST['change_document'] ) : 0;
        $new_document_name = ( isset( $_POST['new_document_name'] ) ) ? htmlspecialchars( $_POST['new_document_name'] ) : '';
        if ( $change_document !== 0 && $new_document_name !== '' ) {
            TP_Ajax::change_document_name($change_document, $new_document_name);
        }

        /**
         * Saving sort order of documents
         * Works if $_POST['tp_file'] is given
         */
        if ( isset( $_POST['tp_file'] ) ) {
            $order = is_array( $_POST['tp_file'] ) ? $_POST['tp_file'] : [];
            TP_Ajax::set_sort_order($order);
        }

        /**
         * Getting image url for mimetype
         * Works if $_GET['mimetype_input'] is given
         */
        if ( isset( $_GET['mimetype_input'] ) ) {
            $mimetype = htmlspecialchars( $_GET['mimetype_input'] );
            TP_Ajax::get_mimetype_image($mimetype);
        }

        /**
         * Getting the cite dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['cite_id'] ) ) {
            $cite_id = intval( $_GET['cite_id'] );
            TP_Ajax::get_cite_screen($cite_id);
        }

        /**
         * Getting the cite text for a cite dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['cite_pub'] ) && isset( $_GET['cite_type'] )  ) {
            $cite_pub = intval( $_GET['cite_pub'] );
            $cite_text = ( $_GET['cite_type'] === 'bibtex' ) ? 'bibtex' : 'text';
            TP_Ajax::get_cite_text($cite_pub, $cite_text);
        }
        
        /**
         * Getting the edit meta field dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['meta_field_id'] ) ) {
            $meta_field_id = intval( $_GET['meta_field_id'] );
            TP_Ajax::get_meta_field_screen($meta_field_id);
        } 
        
        /**
         * Getting the unique version of the bibtex string
         * @since 6.1.1
         */
        if ( isset ( $_GET['bibtex_key_check'] ) ) {
            $key = htmlspecialchars( $_GET['bibtex_key_check'] );
            TP_Ajax::get_generated_bibtex_key( $key );
        }

    }

    // this is required to terminate immediately and return a proper response
    wp_die();
}

/**
 * AJAX callback function for the document manager
 * @since 6.0.0
 */
function tp_ajax_doc_manager_callback () {
    TP_Document_Manager::get_window();
    wp_die();
}

