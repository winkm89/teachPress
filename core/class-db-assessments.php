<?php
/**
 * This file contains the database access class for course assessments
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Database access class for course assessments
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class tp_assessments {
    
    /**
     * Returns an assessment by id
     * @param int $assessment_id        The assessment ID
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_assessment ($assessment_id, $output_type = ARRAY_A) {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `assessment_id` = '" . intval($assessment_id) . "'", $output_type);
    }
    
    /**
     * Returns assessments
     * @param int $wp_id            The user ID
     * @param string $artefact_id   A string of artefact IDs separated by comma
     * @param int $course_id        The course ID
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_assessments ($wp_id, $artefact_id, $course_id, $output_type = ARRAY_A) {
        global $wpdb;
        if ( $artefact_id === '' ) {
            $artefacts = "course_id = '" . intval($course_id) . "'"; 
        }
        else {
            $artefacts = tp_db_helpers::generate_where_clause($artefact_id, "artefact_id", "OR", "=");
        }
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `wp_id` = '" . intval($wp_id) . "' AND $artefacts", $output_type);
    }
    
    /**
     * Adds a new assessment
     * @param array $data     An associative array with new assessment_data (wp_id, type, value, exam_date,...)
     * @return int
     * @since 5.0.0
     */
    public static function add_assessment ($data) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['type'] = stripslashes($data['type']);
        $data['comment'] = stripslashes($data['comment']);
        
        $wpdb->insert(TEACHPRESS_ASSESSMENTS, array('wp_id' => $data['wp_id'], 'value' => $data['value'], 'max_value' => $data['max_value'], 'type' => $data['type'], 'examiner_id' => $data['examiner_id'], 'exam_date' => $data['exam_date'], 'comment' => $data['comment'], 'passed' => $data['passed']), array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d'));
        $insert_id = $wpdb->insert_id;
        
        // For possible NULL values ($wpdb doesn't like that)
        $data['artefact_id'] = ( $data['artefact_id'] === NULL ) ? "NULL" : intval($data['artefact_id']);
        $data['course_id'] = ( $data['course_id'] === NULL ) ? "NULL" : intval($data['course_id']);
        $wpdb->query("SET foreign_key_checks = 0");
        $wpdb->query("UPDATE " . TEACHPRESS_ASSESSMENTS . " SET `artefact_id` = '" . $data['artefact_id'] . "', `course_id` = '" . $data['course_id'] . "' WHERE `assessment_id` = '$insert_id'");
        $wpdb->query("SET foreign_key_checks = 1");
        return $insert_id;
    }
    
    /**
     * Changes an assessment. Returns false if errors, or the number of rows affected if successful.
     * @param int $assessment_id        The assessment ID
     * @param array $data               An associative array with new assessment_data (type, value, exminer_id, exam_date, comment, passed)
     * @return int|false
     * @since 5.0.0
     */
    public static function change_assessment($assessment_id, $data) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['type'] = stripslashes($data['type']);
        $data['comment'] = stripslashes($data['comment']);
        
        $wpdb->query("SET foreign_key_checks = 0");
        $return = $wpdb->update( TEACHPRESS_ASSESSMENTS, array( 'type' => $data['type'], 'value' => $data['value'], 'examiner_id' => $data['examiner_id'], 'exam_date' => $data['exam_date'], 'comment' => $data['comment'], 'passed' => $data['passed']), array( 'assessment_id' => $assessment_id ), array( '%s', '%s', '%d', '%s', '%s', '%d' ), array( '%d' ) );
        $wpdb->query("SET foreign_key_checks = 1");
        return $return;
        
    }
    
   /**
    * Deletes an assessment
    * @param int $assessment_id     The assessment ID
    * @since 5.0.0
    */
   public static function delete_assessment ($assessment_id) {
       global $wpdb;
       $wpdb->query("DELETE FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `assessment_id` = '" . intval($assessment_id) . "'");
   }
    
}