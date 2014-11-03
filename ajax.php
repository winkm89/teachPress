<?php
/**
 * This file contains the server side part for the teachpress ajax interface
 * @package teachpress
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

// include wp-load.php
require_once( '../../../wp-load.php' );
if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    
    // for show_authors.php
    $author_id = ( isset( $_GET['author_id'] ) ) ? intval( $_GET['author_id'] ) : 0;
    if ( $author_id !== 0 ) {
        tp_ajax::get_author_publications($author_id);
    }
    
    // for show_single_course.php (assessment screen)
    $assessment_id = ( isset( $_GET['assessment_id'] ) ) ? intval( $_GET['assessment_id'] ) : 0;
    if ( $assessment_id !== 0 ) {
        tp_ajax::get_assessment_screen($assessment_id);
    }
    
    // for show_single_course.php (artefact screen)
    $artefact_id = ( isset( $_GET['artefact_id'] ) ) ? intval( $_GET['artefact_id'] ) : 0;
    if ( $artefact_id !== 0 ) {
        tp_ajax::get_artefact_screen($artefact_id);
    }
    
    // for removing documents
    $del_document = ( isset( $_GET['del_document'] ) ) ? intval( $_GET['del_document'] ) : 0;
    if ( $del_document !== 0 ) {
        tp_ajax::delete_document($del_document);
    }
    
    // for adding document headlines
    $add_document = ( isset( $_GET['add_document'] ) ) ? htmlspecialchars( $_GET['add_document'] ) : '';
    $course_id = ( isset( $_GET['course_id'] ) ) ? intval($_GET['course_id']) : 0;
    if ( $add_document !== '' && $course_id !== 0 ) {
        tp_ajax::add_document_headline($add_document, $course_id);
    }
    
    // for getting a document name
    $get_document_name = ( isset( $_GET['get_document_name'] ) ) ? intval( $_GET['get_document_name'] ) : 0;
    if ( $get_document_name !== 0 ) {
        tp_ajax::get_document_name($get_document_name);
    }
    
    // for changing a document name
    $change_document = ( isset( $_POST['change_document'] ) ) ? intval( $_POST['change_document'] ) : 0;
    $new_document_name = ( isset( $_POST['new_document_name'] ) ) ? htmlspecialchars( $_POST['new_document_name'] ) : '';
    if ( $change_document !== 0 && $new_document_name !== '' ) {
        tp_ajax::change_document_name($change_document, $new_document_name);
    }

    // for saving sort order of documents
    if ( isset( $_POST['tp_file'] ) ) {
        tp_ajax::set_sort_order($_POST['tp_file']);
    }
}
