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
    
    /**
     * Getting author's publications (for show_authors.php)
     * Works if $_GET['author_id'] is given
     */
    $author_id = ( isset( $_GET['author_id'] ) ) ? intval( $_GET['author_id'] ) : 0;
    if ( $author_id !== 0 ) {
        tp_ajax::get_author_publications($author_id);
    }
    
    /**
     * Getting assessment screent (for show_single_course.php)
     * Works if $_GET['assessment_id'] is given
     */
    $assessment_id = ( isset( $_GET['assessment_id'] ) ) ? intval( $_GET['assessment_id'] ) : 0;
    if ( $assessment_id !== 0 ) {
        tp_ajax::get_assessment_screen($assessment_id);
    }
    
    /**
     * Getting artefact screent (for show_single_course.php)
     * Works if $_GET['artefact_id'] is given
     */
    $artefact_id = ( isset( $_GET['artefact_id'] ) ) ? intval( $_GET['artefact_id'] ) : 0;
    if ( $artefact_id !== 0 ) {
        tp_ajax::get_artefact_screen($artefact_id);
    }
    
    /**
     * Removing documents
     * Works if $_GET['del_document'] is given
     */
    $del_document = ( isset( $_GET['del_document'] ) ) ? intval( $_GET['del_document'] ) : 0;
    if ( $del_document !== 0 ) {
        tp_ajax::delete_document($del_document);
    }
    
    /**
     * Adding document headlines
     * Works if $_GET['add_document'] and $_GET['course_id'] are given
     */
    $add_document = ( isset( $_GET['add_document'] ) ) ? htmlspecialchars( $_GET['add_document'] ) : '';
    $course_id = ( isset( $_GET['course_id'] ) ) ? intval($_GET['course_id']) : 0;
    if ( $add_document !== '' && $course_id !== 0 ) {
        tp_ajax::add_document_headline($add_document, $course_id);
    }
    
    /**
     * Getting a document name
     * Works if $_GET['get_document_name'] is given
     */
    $get_document_name = ( isset( $_GET['get_document_name'] ) ) ? intval( $_GET['get_document_name'] ) : 0;
    if ( $get_document_name !== 0 ) {
        tp_ajax::get_document_name($get_document_name);
    }
    
    /**
     * Changing a document name
     * Works if $_POST['change_document'] and $_POST['new_document_name'] are given
     */
    $change_document = ( isset( $_POST['change_document'] ) ) ? intval( $_POST['change_document'] ) : 0;
    $new_document_name = ( isset( $_POST['new_document_name'] ) ) ? htmlspecialchars( $_POST['new_document_name'] ) : '';
    if ( $change_document !== 0 && $new_document_name !== '' ) {
        tp_ajax::change_document_name($change_document, $new_document_name);
    }

    /**
     * Saving sort order of documents
     * Works if $_POST['tp_file'] is given
     */
    if ( isset( $_POST['tp_file'] ) ) {
        tp_ajax::set_sort_order($_POST['tp_file']);
    }
    
    /**
     * Getting image url for mimetype
     * Works if $_GET['mimetype_input'] is given
     */
    if ( isset( $_GET['mimetype_input'] ) ) {
        tp_ajax::get_mimetype_image($_GET['mimetype_input']);
    }
}
