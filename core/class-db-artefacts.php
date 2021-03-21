<?php
/**
 * This file contains the database access class for course artefacts
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */


/**
 * Database access class for course artefacts
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Artefacts {
    
    /**
     * Returns an artefact by ID
     * @param int $artefact_id      The artefact ID
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_artefact ($artefact_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_ARTEFACTS . " WHERE `artefact_id` = '" . intval($artefact_id ) . "'", $output_type);
    }
    
    /**
     * Returns artefacts
     * @param int $course_id        The course ID
     * @param int $parent_id        The ID of the parent artefact
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_artefacts ($course_id, $parent_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_ARTEFACTS . " WHERE `course_id` = '" . intval($course_id) . "' AND `parent_id` = '" . intval($parent_id) . "'", $output_type);
    }
    
    /**
     * Returns the artefact IDs of a selected course
     * @param int $course_id        The course ID
     * @param int $parent_id        0 for the main artefacts
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_artefact_ids ($course_id, $parent_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_results("SELECT artefact_id FROM " . TEACHPRESS_ARTEFACTS . " WHERE `course_id` = '" . intval($course_id) . "' AND `parent_id` = '" . intval($parent_id) . "'", $output_type);
    }
    
    /**
     * Adds a new artefact
     * @param array $data     An associative array of artefact data (parent_id, course_id, title, scale, passed, max_value)
     * @return int
     * @since 5.0.0
     */
    public static function add_artefact ($data) {
        global $wpdb;
        
        // prevent double escapes
        $data['title'] = stripslashes($data['title']);
        
        $wpdb->insert(TEACHPRESS_ARTEFACTS, array('parent_id' => $data['parent_id'], 'course_id' => $data['course_id'], 'title' => $data['title'], 'scale' => $data['scale'], 'passed' => $data['passed'], 'max_value' => $data['max_value']), array('%d', '%d', '%s', '%s', '%d', '%s'));
        return $wpdb->insert_id;
    }
    
    /**
     * Deletes an artefact
     * @param int $artefact_id      The artefact ID
     * @since 5.0.0
     */
    public static function delete_artefact ($artefact_id) {
        global $wpdb;
        $wpdb->query("DELETE FROM " . TEACHPRESS_ARTEFACTS . " WHERE `artefact_id` = '" . intval($artefact_id) . "'");
    }
    
    /**
     * Changes an artefact name
     * @param int $artefact_id      The artefact ID
     * @param string $title         The new title for the artefact
     * @return mixed int|false
     * @since 5.0.0
     */
    public static function change_artefact_title ($artefact_id, $title) {
        global $wpdb;
        
        // prevent double escapes
        $title = stripslashes($title);
        
        return $wpdb->update( TEACHPRESS_ARTEFACTS, array( 'title' => $title), array( 'artefact_id' => $artefact_id ), array( '%s' ), array( '%d' ) );
    }
    
    /**
     * Checks if an artefact has assessments. If yes, the function returns true. If not, the function returns false.
     * @param int $artefact_id      The artefact ID
     * @return boolean
     * @since 5.0.0
     */
    public static function has_assessments($artefact_id) {
        global $wpdb;
        $test = $wpdb->query("SELECT assessment_id FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `artefact_id` = '" . intval($artefact_id) . "'");
        if ( $test === 0 ) {
            return false;
        }
        return true;
    }
}
