<?php
/**
 * This file contains the database access class for course documents
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains all functions for the access to course documents
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Documents {
    
    /**
     * Returns the data of a document
     * @param int $doc_id               The document ID
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     */
    public static function get_document($doc_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `doc_id` = '" . intval($doc_id) . "'",$output_type);
    }
    
    /**
     * Returns the data of documents of a course
     * @param int $course_id        The course ID
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_documents($course_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `course_id` = '" . intval($course_id) . "' ORDER BY `sort` ASC, `added` ASC",$output_type);
    }
    
    /**
     * Adds a connection between a course and a document file
     * @param string $name      The document name
     * @param string $path      The document path
     * @param string $size      The document size in bytes
     * @param int $course_id    The course ID
     * @return int The id of the added document entry
     * @since 5.0.0
     */
    public static function add_document($name, $path, $size, $course_id) {
        global $wpdb;
        $time = current_time('mysql',0);
        
        // prevent possible double escapes
        $name = stripslashes($name);
        
        // ask for max sort
        $max = $wpdb->get_var( "SELECT MAX(sort) FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `course_id` = '" . intval($course_id) . "'" );
        $sort = intval($max) + 1;
        
        $wpdb->insert( TEACHPRESS_COURSE_DOCUMENTS, array( 'name' => $name, 
                                                           'path' => $path, 
                                                           'added' => $time,
                                                           'size' => $size,
                                                           'sort' => $sort, 
                                                           'course_id' => intval($course_id) ), 
                                                           array( '%s', '%s', '%s', '%d', '%d', '%d') );
        return $wpdb->insert_id;
    }
    
    /**
     * Sets the value of the name field for a document entry. Returns false if errors, or the number of rows affected if successful.
     * @param int $doc_id           The document ID
     * @param string $doc_name      The document name
     * @return int|false
     * @since 5.0.0
     */
    public static function change_document_name($doc_id, $doc_name) {
        global $wpdb;
        
        // prevent possible double escapes
        $doc_name = stripslashes($doc_name);
        
        return $wpdb->update( TEACHPRESS_COURSE_DOCUMENTS, array( 'name' => $doc_name ), array( 'doc_id' => $doc_id ), array( '%s', ), array( '%d' ) );
    }


    /**
     * Sets the value of the sort field for a document entry. Returns false if errors, or the number of rows affected if successful.
     * @param int $doc_id       The document ID
     * @param int $sort         The sort value
     * @return int|false
     * @since 5.0.0
     */
    public static function set_sort($doc_id, $sort) {
        global $wpdb;
        return $wpdb->update( TEACHPRESS_COURSE_DOCUMENTS, array( 'sort' => $sort ), array( 'doc_id' => $doc_id ), array( '%d', ), array( '%d' ) );
    }

    /**
     * Deletes a document entry in the database
     * @param int $doc_id       The document ID
     * @since 5.0.0
     */
    public static function delete_document($doc_id) {
        global $wpdb;
        $doc_id = intval($doc_id);
        $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `doc_id` = '$doc_id'");
    }
}

