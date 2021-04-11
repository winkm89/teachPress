<?php
/**
 * This file contains the database access class for students
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting students
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Students {
    
    /**
     * Returns data of a student
     * @param string $id            ID of the student/user
     * @param string $output_type   OBJECT, ARRAY_A or ARRAY_N; Default is ARRAY_A
     * @return object
     * @since 5.0.0
     */
    public static function get_student ($id, $output_type = ARRAY_A) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '" . intval($id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns the data of all students
     * 
     * Possible values for the array $args:
     *       search (STRING)                A normal search string
     *       meta_search (ARRAY)            An associative array of search strings for meta data
     *       order (STRING)                 Default is s.lastname ASC, s.firstname ASC
     *       limit (STRING)                 The sql search limit, example: 0,30
     *       output type (STRING)           OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
     *       count (BOOLEAN)                Set it to true if you only need an number of authors which will be returned by your selection (default: false)
     * 
     * @param array $args
     * @return object or array
     * @since 5.0.0
     */
    public static function get_students ( $args = array() ) {
        $defaults = array(
            'search' => '',
            'meta_search' => '',
            'order' => 's.lastname ASC, s.firstname ASC',
            'limit' => '',
            'output_type' => OBJECT,
            'count' => false
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        
        $where = '';
        $order = esc_sql($order);
        $limit = esc_sql($limit);
        $output_type = esc_sql($output_type);
        
        // define all which is needed for meta data integration
        $joins = '';
        $selects = '';
        $i = 1;
        if ( !empty($meta_search) ) {
            foreach ($meta_search as $key => $value) {
                if ( $meta_search[$key] === '' ) {
                    continue;
                }
                $key = esc_sql($key);
                $value = esc_sql($meta_search[$key]);
                $table_id = 'm' . $i; 
                $selects .= ', ' . $table_id .'.meta_value AS ' . $key;
                $joins .= ' LEFT JOIN ' . TEACHPRESS_STUD_META . ' ' . $table_id . " ON ( " . $table_id . ".wp_id = s.wp_id AND " . $table_id . ".meta_key = '" . $key . "' ) ";
                $where = ( $where != '' ) ? $where . " AND ( " . $table_id . ".meta_value = '$value' )" : " ( " . $table_id . ".meta_value = '$value' )" ;
                $i++;
            }
        }
        
        // define SELECT
        $select = "SELECT s.wp_id, s.firstname, s.lastname, s.userlogin, s.email $selects FROM " . TEACHPRESS_STUD . " s $joins";
        
        // if the user needs only the number of rows
        if ( $count === true ) {
            $select = "SELECT COUNT(s.wp_id) AS `count` FROM " . TEACHPRESS_STUD  . " s $joins";
        }

        // define global search
        $search = esc_sql( htmlspecialchars( stripslashes($search) ) );
        if ( $search != '' ) {
            $search = "s.wp_id like '%$search%' OR s.userlogin LIKE '%$search%' OR s.firstname LIKE '%$search%' OR s.lastname LIKE '%$search%' OR s.email LIKE '%$search%'";
        }

        // define where clause
        if ( $search != '') {
            $where = ( $where != '' ) ? $where . " AND ( $search ) " :  " ( $search ) ";
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }

        // End
        $sql = $select . $where . " ORDER BY $order $limit";
        // get_tp_message($sql);
        $sql = ( $count === false ) ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
        return $sql;
    }
    
    /**
     * Returns user meta data
     * @param int $wp_id            The user ID
     * @param string $meta_key      The name of the meta field (optional)
     * @return array
     * @since 5.0.0
     */
    public static function get_student_meta($wp_id, $meta_key = ''){
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '" . intval($wp_id) . "' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Returns an array|object with ID and user_login of WordPress users, which are not registered in teachPress 
     * @param string $output_type   ARRAY_A, ARRAY_N or OBJECT, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function get_unregistered_students($output_type = ARRAY_A) {
        global $wpdb;
        $sql = "SELECT u.ID, u.user_login FROM " . $wpdb->users . " u "
                . "LEFT JOIN " . TEACHPRESS_STUD . " s ON u.ID = s.wp_id "
                . "WHERE s.wp_id IS NULL";
        return $wpdb->get_results($sql, $output_type);
    }

    /** 
     * Add student
     * @param int $wp_id    WordPress user ID
     * @param array $data   An assocative array with the user data
     * @return boolean
     * @since 5.0.0
    */
   public static function add_student($wp_id, $data) {
        global $wpdb;
        $wp_id = intval($wp_id);
        
        // prevent possible double escapes
        $data['firstname'] = stripslashes($data['firstname']);
        $data['lastname'] = stripslashes($data['lastname']);

        $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$wp_id'");
        if ($test == '0') {
            $wpdb->insert( TEACHPRESS_STUD, array( 'wp_id' => $wp_id, 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'userlogin' => $data['userlogin'], 'email' => $data['email'] ), array( '%d', '%s', '%s', '%s', '%s' ) );
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Add student meta
     * @param int $wp_id            The user ID
     * @param string $meta_key      The name of the meta field
     * @param string $meta_value    The value of the meta field
     * @since 5.0.0
     */
    public static function add_student_meta ($wp_id, $meta_key, $meta_value) {
        global $wpdb;
        
        // prevent possible double escapes
        $meta_value = stripslashes($meta_value);
        
        $wpdb->insert( TEACHPRESS_STUD_META, array( 'wp_id' => $wp_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /** 
     * Edit userdata
     * @param int $wp_id                The user ID
     * @param array $data             An associative array of user data (firstname, lastname, userlogin,...)
     * @param boolean $show_message     Default is true
     * @return string
     * @since 5.0.0
    */
   public static function change_student($wp_id, $data, $show_message = true) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['firstname'] = stripslashes($data['firstname']);
        $data['lastname'] = stripslashes($data['lastname']);
        
        $wpdb->update( TEACHPRESS_STUD, array( 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'userlogin' => $data['userlogin'], 'email' => $data['email'] ), array( 'wp_id' => $wp_id ), array( '%s', '%s', '%s', '%s' ), array( '%d' ) );
        if ($show_message === true) {
            return '<div class="teachpress_message_success">' . __('Changes in your profile successful.','teachpress') . '</div>';
        }
    }
    
    /** 
     * Delete student
     * @param array $checkbox       ID of the enrollment
     * @since 5.0.0
    */ 
   public static function delete_student($checkbox){
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            // search courses where the user was registered
            $row1 = $wpdb->get_results("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `wp_id` = '$checkbox[$i]'");
            foreach ($row1 as $row1) {
                // check if there are users in the waiting list
                $sql = "SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '" . $row1->course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
                $con_id = $wpdb->get_var($sql);
                // if is true subscribe the first one in the waiting list for the course
                if ($con_id != 0 && $con_id != '') {
                    $wpdb->query( "UPDATE " . TEACHPRESS_SIGNUP . " SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
                }
            }
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `wp_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Deletes student meta
     * @param int $wp_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function delete_student_meta ($wp_id, $meta_key = '') {
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '" . intval($wp_id) . "' $where");
    }
    
    /**
     * Returns an array or object of all signups of a student
     * 
     * possible values for $args:
     *      wp_id (INT)             The user ID
     *      mode (STRING)           all, reg or wtl. Default is: all
     *      course_id (INT)         The course ID. Set it and the function searches only in sub courses
     *      order (STRING)          Default is: con_id DESC
     *      output_type (STRING)    OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * 
     * @param array $args
     * @return array|object 
     * @since 5.0.0
     */
    public static function get_signups ( $args = array()) {
        $defaults = array(
            'wp_id' => 0,
            'mode' => 'all',
            'course_id' => 0,
            'order' => 'con_id DESC',
            'output_type' => OBJECT
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        $wp_id = intval($wp_id);
        $course_id = intval($course_id);
        $mode = htmlspecialchars($mode);
        $output_type = htmlspecialchars($output_type);
        $order = esc_sql($order);

        // search only in sub courses
        $where = '';
        if ( $course_id !== 0 ) {
            $where = "WHERE c.parent = '$course_id' ";
        }

        $sql = "SELECT con_id, wp_id, course_id, waitinglist, name, type, room, date, semester, parent_name, timestamp FROM (SELECT s.con_id as con_id, s.wp_id as wp_id, s.course_id as course_id, s.waitinglist as waitinglist, c.name as name, c.type as type, c.room as room, c.date as date, c.semester as semester, c2.name as parent_name, s.date as timestamp FROM " . TEACHPRESS_SIGNUP . " s INNER JOIN " . TEACHPRESS_COURSES . " c ON s.course_id = c.course_id LEFT JOIN " . TEACHPRESS_COURSES . " c2 ON c.parent = c2.course_id $where) AS temp WHERE `wp_id` = '$wp_id'";
        if ( $mode === 'reg' ) {
            $sql .= " AND `waitinglist` = '0'";
        }
        if ( $mode === 'wtl' ) {
            $sql .= " AND `waitinglist` = '1'";
        }
        $sql .= " ORDER BY " . $order;
        // get_tp_message($sql);
        $result = $wpdb->get_results($sql, $output_type);
        return $result;
    }
    
    /**
     * Checks if a student exists. If not, the function returns false. If yes, the user_id will be returned.
     * @param int $wp_id        The WordPress user ID
     * @return boolean|int
     * @since 5.0.0
     */
    public static function is_student($wp_id) {
        global $wpdb;
        $test = $wpdb->get_var("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '" . intval($wp_id) . "'");
        if ( $test === NULL ) {
            return false;
        }
        else {
            return $test;
        }
    }
    
    /**
    * Checks if the student has assessments. If yes, the function returns true. If not, the function returns false.
    * @param int $wp_id         The user ID
    * @param int $course_id     The course ID
    * @return boolean
    * @since 5.0.0
    */
    public static function has_assessment ($wp_id, $course_id) {
        global $wpdb;
        $artefacts = TP_Artefacts::get_artefact_ids($course_id, 0);

        // Define where clause
        $where = '';
        if ( count($artefacts) !== 0 ) {
            foreach ( $artefacts as $row ) {
                $where .= " OR `artefact_id` = '" . $row['artefact_id'] . "'";
            }
        }

        $test = $wpdb->query("SELECT assessment_id FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `wp_id` = '" . intval($wp_id) . "' AND ( `course_id` = '" . intval($course_id) . "' $where)");
        if ( $test === 0 ) {
            return false;
        }
        return true;
    }
    
}