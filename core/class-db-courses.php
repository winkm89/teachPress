<?php
/**
 * This file contains the database access class for courses
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting of courses
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class tp_courses {
    
    /**
     * Returns the capability ("owner" or "approved") of an user for a course. For courses with no capabilities "owner" is returned.
     * @param string $course_id     The course ID
     * @param string $wp_id         WordPress user ID
     * @return string
     * @since 5.0.0
     */
    public static function get_capability ($course_id, $wp_id){
        global $wpdb;
        $test = $wpdb->get_var("SELECT `use_capabilites` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '" . intval($course_id) . "'");
        if ( intval($test) === 1 ){
            return $wpdb->get_var("SELECT `capability` FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '" . intval($course_id) . "' AND `wp_id` = '" . intval($wp_id) . "'");
        }
        // Return owner if the course has no capabilities
        return 'owner';
    }

    /**
    * Get course capabilites
    * @param int $course_id         The course ID
    * @param string $output_type    OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
    * @return array|object
    * @since 5.0.0
    */
   public static function get_capabilities ($course_id, $output_type = ARRAY_A) {
       global $wpdb;
       return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '" . intval($course_id) . "'",$output_type);
   }
   
   /**
    * Add course capability
    * @param int $course_id         The course ID
    * @param int $wp_id             WordPress user ID
    * @param string $capability     The capability name (owner, approved)
    * @return int|false
    * @since 5.0.0
    */
   public static function add_capability ($course_id, $wp_id, $capability) {
       global $wpdb;
       if ( $course_id === 0 || $wp_id === 0 || $capability === '' ) {
           return false;
       }
       if ( !tp_courses::has_capability($course_id, $wp_id, $capability) ) {
           $wpdb->insert(TEACHPRESS_COURSE_CAPABILITES, array('course_id' => $course_id, 'wp_id' => $wp_id, 'capability' => $capability), array('%d', '%d', '%s'));
       }
       return $wpdb->insert_id;
   }
   
   /**
    * Delete course capability
    * @param int $cap_id    The capability ID
    * @since 5.0.0
    * @todo unused
    */
   public static function delete_capability ($cap_id) {
       global $wpdb;
       $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `cap_id` = '" . intval($cap_id) . "'");
   }
   
   /**
    * Checks if a user has a cap in the selected course
    * @param int $course_id         ID of a course
    * @param int $wp_id             WordPress user ID
    * @param string $capability     "owner" or "approved"
    * @return boolean
    * @since 5.0.0
    */
   public static function has_capability ($course_id, $wp_id, $capability) {
       global $wpdb;
       $where = '';
       if ( $capability !== '' ) {
           $where = "AND `capability` = '" . esc_sql($capability). "'";
       }
       $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '" . intval($course_id) . "' AND `wp_id` = '" . intval($wp_id) . "' $where");
       if ( $test === 1 ) {
           return true;
       }
       return false;
   }
   
   /**
    * Checks if there is an owner of the selected course. If not, the function returns false, if yes, the user_id is returned.
    * @param int $course_id     The course ID
    * @return boolean|int
    * @since 5.0.0
    */
   public static function is_owner ($course_id) {
       global $wpdb;
       $test = $wpdb->get_var("SELECT `wp_id` FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '" . intval($course_id) . "' AND `capability` = 'owner'");
       if ( $test === NULL ){
           return false;
       }
       return intval($test);
       
   }
   
   /**
    * Checks if a post is used as related content for a course. If is true, the course ID will be returned otherwise it's false. 
    * @param int $post_id
    * @return int|boolean   Returns the course_id or false
    * @since 5.0.0
    */
   public static function is_used_as_related_content($post_id) {
       global $wpdb;
       $post_id = intval($post_id);
       if ( $post_id === 0 ) {
           return false;
       }
       return $wpdb->get_var("SELECT `course_id` FROM `" . TEACHPRESS_COURSES . "` WHERE `rel_page` = '$post_id' ");
   }

    /**
     * Returns all data of a single course
     * @param int $course_id            The course ID
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * @return mixed
     * @since 5.0.0
     */
    public static function get_course ($course_id, $output_type = OBJECT) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM `" . TEACHPRESS_COURSES . "` WHERE `course_id` = '" . intval($course_id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns all data of one or more courses
     * 
     * possible values for the array $args:
     *      @type string semester         The semester/term of the courses
     *      @type string visibility       The visibility of the coures (1,2,3) separated by comma
     *      @type string parent           The course_id of the parent
     *      @type string search           A general search string
     *      @type string exclude          The course IDs you want to exclude
     *      @type string order            Default: semester DESC, name
     *      @type string limit            The sql search limit, ie: 0,30
     *      @type string output_type      OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_courses ( $args = array() ) {
        $defaults = array(
            'semester' => '',
            'visibility' => '',
            'parent' => '',
            'search' => '',
            'exclude' => '',
            'order' => 'semester DESC, name',
            'limit' => '',
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        // Define basics
        $sql = "SELECT course_id, name, type, lecturer, date, room, places, start, end, semester, parent, visible, rel_page, comment, image_url, strict_signup, use_capabilites, parent_name
                FROM ( SELECT t.course_id AS course_id, t.name AS name, t.type AS type, t.lecturer AS lecturer, t.date AS date, t.room As room, t.places AS places, t.start AS start, t.end As end, t.semester AS semester, t.parent As parent, t.visible AS visible, t.rel_page AS rel_page, t.comment AS comment, t.image_url AS image_url, t.strict_signup AS strict_signup, t.use_capabilites AS use_capabilites, p.name AS parent_name 
                    FROM " . TEACHPRESS_COURSES . " t 
                    LEFT JOIN " . TEACHPRESS_COURSES . " p ON t.parent = p.course_id ) AS temp";
        $where = '';
        $order = esc_sql($order);
        $limit = esc_sql($limit);
        $output_type = esc_sql($output_type);
        $search = esc_sql(htmlspecialchars(stripslashes($search)));
        $exclude = tp_db_helpers::generate_where_clause($exclude, "p.pub_id", "AND", "!=");
        $semester = tp_db_helpers::generate_where_clause($semester, "semester", "OR", "=");
        $visibility = tp_db_helpers::generate_where_clause($visibility, "visible", "OR", "=");

        // define global search
        if ( $search != '' ) {
            $search = "`name` like '%$search%' OR `parent_name` like '%$search%' OR `lecturer` like '%$search%' OR `date` like '%$search%' OR `room` like '%$search%' OR `course_id` = '$search'";
        }

        if ( $exclude != '' ) {
            $where = ( $where != '' ) ? $where . " AND ( $exclude ) " : " ( $exclude ) ";
        }
        if ( $semester != '') {
            $where = ( $where != '' ) ? $where . " AND ( $semester ) " : " ( $semester ) ";
        }
        if ( $visibility != '') {
            $where = ( $where != '' ) ? $where . " AND ( $visibility ) " : " ( $visibility ) ";
        }
        if ( $search != '') {
            $where = ( $where != '' ) ? $where . " AND ( $search ) " : " ( $search ) ";
        }
        if ( $parent !== '' ) {
            $parent = intval($parent);
            $where = ( $where != '' ) ? $where . " AND ( `parent` = '$parent' ) " : "`parent` = '$parent'" ;
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $limit != '' ) {
            $limit = " LIMIT $limit";
        }

        // define order
        if ( $order != '' ) {
            $order = " ORDER BY $order";
        }
        $result = $wpdb->get_results($sql . $where . $order . $limit, $output_type);
        return $result;
    }
    
    /** 
     * Returns a single value of a course 
     * @param int $course_id    The course ID
     * @param string $col       The name of the column
     * @return string
     * @since 5.0.0
    */  
    public static function get_course_data ($course_id, $col) {
        global $wpdb;
        $result = $wpdb->get_var("SELECT `" . esc_sql($col) . "` FROM `" . TEACHPRESS_COURSES . "` WHERE `course_id` = '" . intval($course_id) . "'");
        return $result;
    }
    
    /**
     * Returns the course name under consideration of a possible parent course
     * @param int $course_id    The course ID
     * @return string
     * @since 5.0.6
     */
    public static function get_course_name ($course_id) {
        global $wpdb;
        $row = $wpdb->get_row("SELECT `name`, `parent` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '" . intval($course_id) . "'");
        if ($row->parent != '0') {
            $parent = tp_courses::get_course_data($row->parent, 'name');
            $row->name = ( $row->name != $parent ) ? $parent . ' ' . $row->name : $row->name;
        }
        return $row->name;
    }
    
    /**
     * Returns course meta data
     * @param int $course_id        The course ID
     * @param string $meta_key      The name of the meta field (optional)
     * @return array
     * @since 5.0.0
     */
    public static function get_course_meta($course_id, $meta_key = ''){
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = '" . intval($course_id) . "' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Add course meta
     * @param int $course_id        The course ID
     * @param string $meta_key      The name of the meta field
     * @param string $meta_value    The value of the meta field
     * @since 5.0.0
     */
    public static function add_course_meta ($course_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHPRESS_COURSE_META, array( 'course_id' => $course_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /**
     * Deletes curse meta
     * @param int $course_id    The course ID
     * @param string $meta_key  The name of the meta field
     * @since 5.0.0
     */
    public static function delete_course_meta ($course_id, $meta_key = '') {
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = '" . intval($course_id) . "' $where");
    }
    
    /**
     * Returns the number of free places in a course
     * @param int $course_id    ID of the course
     * @param int $places       Number of places
     * @return int
     * @since 5.0.0
     */
    public static function get_free_places($course_id, $places) {
        global $wpdb;
        $places = intval($places);
        $used_places = $wpdb->get_var("SELECT COUNT(`course_id`) FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '" . intval($course_id) . "' AND `waitinglist` = 0");
        return ($places - $used_places);
    }
    
    /**
    * Returns an array with the number of used places for each course. The array key is the course_id, the value is the number of used places.
    * @return array
    * @since 5.0.0
    */
   public static function get_used_places() {
       global $wpdb;
       $used_places = array();
       $sql = "SELECT `course_id`, COUNT(`course_id`) AS used_places FROM " . TEACHPRESS_SIGNUP . " WHERE `waitinglist` = '0' GROUP BY `course_id`";
       $r = $wpdb->get_results($sql);
       foreach ($r as $r) {
           $used_places[$r->course_id] = $r->used_places;
       }
       return $used_places;
    }
    
    /** 
     * Add a new course
     * @param array $data       An associative array with data of the course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @return int              ID of the new course
     * @since 5.0.0
    */
   public static function add_course($data, $sub) {
        global $wpdb;
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['type'] = stripslashes($data['type']);
        $data['room'] = stripslashes($data['room']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['comment'] = stripslashes($data['comment']);
        $data['semester'] = stripslashes($data['semester']);
        
        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        $wpdb->insert( TEACHPRESS_COURSES, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'], 'use_capabilites' => $data['use_capabilites'] ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ) );
        $course_id = $wpdb->insert_id;
        // add capability
        global $current_user;
        tp_courses::add_capability($course_id, $current_user->ID, 'owner');
        // create rel_page
        if ($data['rel_page_alter'] !== 0 ) {
            $data['rel_page'] = tp_courses::add_rel_page($course_id, $data);
            // Update rel_page
            $wpdb->update( TEACHPRESS_COURSES, array( 'rel_page' => $data['rel_page'] ), array( 'course_id' => $course_id ), array( '%d', ), array( '%d' ) );
        }
        // test if creation was successful
        if ( $data['rel_page'] === false ) {
            get_tp_message(__('Error while adding new related content.','teachpress'), 'red');
        }
        // create sub courses
        if ( $sub['number'] !== 0 ) {
            tp_courses::add_sub_courses($course_id, $data, $sub);
        }
        return $course_id;
    }
    
    /**
     * Adds a new related content to WordPress
     * @param int $course_id    The ID of the course
     * @param array $data       An associative array of the course data
     * @return int or false
     * @since 5.0.0
     * @access private
     */
    private static function add_rel_page($course_id, $data) {
        $post = get_post($data['rel_page_alter']);
        $content = str_replace('[course_id]', 'id="' . $course_id . '"', $post->post_content );
        $postarr = array ( 
            'post_title'   => $data['name'],
            'post_content' => $content,
            'post_type'    => $post->post_type,
            'post_author'  => $post->post_author,
            'post_status'   => 'publish'
        );
        return wp_insert_post($postarr);
    }
    
    /**
     * Adds sub courses to a course
     * @param int $course_id    The ID of the parent course
     * @param array $data       An associative array with data of the parent course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @since 5.0.0
     * @access private
     */
    private static function add_sub_courses($course_id, $data, $sub) {
        $sub_data = $data;
        $sub_data['parent'] = $course_id;
        $sub_data['places'] = $sub['places'];
        $sub_data['type'] = $sub['type'];
        $sub_data['rel_page'] = 0;
        $sub_data['rel_page_alter'] = 0;
        $options = array('number' => 0);
        for ( $i = 1; $i <= $sub['number']; $i++ ) {
            $sub_data['name'] = $sub['type'] . ' ' . $i;
            tp_courses::add_course($sub_data, $options);
        }
    }
    
    /** 
     * Changes course data. Returns false if errors, or the number of rows affected if successful.
     * @param int $course_id    course ID
     * @param array $data       An associative array of couse data (name, places, type, room, ...)
     * @return int|false
     * @since 5.0.0
    */ 
   public static function change_course($course_id, $data){
        global $wpdb;
        $course_id = intval($course_id);
        global $current_user;
        $old_places = tp_courses::get_course_data ($course_id, 'places');

        // If the number of places is raised up
        if ( $data['places'] > $old_places ) {
            self::handle_changes_of_free_places($course_id, $data['places'], $old_places);
        }
        
        // Handle capabilities for old existing courses (added before teachpress 5.0)
        if ( self::is_owner($course_id) === false ) {
            self::add_capability($course_id, $current_user->ID, 'owner');
        }
        
        // prevent possible double escapes
        $data['name'] = stripslashes($data['name']);
        $data['type'] = stripslashes($data['type']);
        $data['room'] = stripslashes($data['room']);
        $data['lecturer'] = stripslashes($data['lecturer']);
        $data['comment'] = stripslashes($data['comment']);
        $data['semester'] = stripslashes($data['semester']);

        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        return $wpdb->update( TEACHPRESS_COURSES, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'], 'use_capabilites' => $data['use_capabilites'] ), array( 'course_id' => $course_id ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ), array( '%d' ) );
    }
    
    /**
     * Delete courses
     * @param int   $user_ID    The ID of the current user
     * @param array $checkbox   IDs of the courses
     * @since 5.0.0
     */
    public static function delete_courses($user_ID, $checkbox){
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        for( $i = 0; $i < count( $checkbox ); $i++ ) { 
            $checkbox[$i] = intval($checkbox[$i]);
            
            // capability check
            $capability = tp_courses::get_capability($checkbox[$i], $user_ID);
            if ($capability !== 'owner' ) {
                continue;
            }
            
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_ARTEFACTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = $checkbox[$i]" );
            // Check if there are parent courses, which are not selected for erasing, and set there parent to default
            $sql = "SELECT `course_id` FROM " . TEACHPRESS_COURSES . " WHERE `parent` = $checkbox[$i]";
            $test = $wpdb->query($sql);
            if ($test == '0') {
                continue;
            }
            $row = $wpdb->get_results($sql);
            foreach ($row as $row) {
                if ( !in_array($row->course_id, $checkbox) ) {
                    $wpdb->update( TEACHPRESS_COURSES, array( 'parent' => 0 ), array( 'course_id' => $row->course_id ), array('%d' ), array( '%d' ) );
                }
            }
        }
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
    
    /**
     * Returns course signups or waitinglist entries
     * 
     * Possible values for the array $args:
     *      course_id (INT)             The ID of the course
     *      waitinglist (STRING)        The waitinglist flag (0 or 1 or '')
     *      order (STRING)              The SQL order by statement
     *      limit (STRING)              The SQL limit statement
     *      search (STRING)             A search string for a name search (firstname, lastname of students)
     *      count (BOOLEAN)             If this flag is true, only the number of rows will be returned, default is false
     *      meta_visibility (STRING)    The visibility level of considered meta data fields (normal, admin, hidden, all), default is admin
     *      output_type (STRING)        OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_signups ( $args = array() ) {
        $defaults = array(
            'course_id' => '',
            'waitinglist' => '',
            'order' => '',
            'limit' => '',
            'search' => '',
            'count' => false,
            'meta_visibility' => 'admin',
            'output_type' => OBJECT
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        $course_id = intval($course_id);
        $order = esc_sql($order);
        $search = esc_sql(stripslashes($search));
        $output_type = esc_sql($output_type);
        $waitinglist = esc_sql($waitinglist);
        $limit = esc_sql($limit);

        if ($order != '') {
            $order = " ORDER BY $order";
        }
        if ( $limit != '' ) {
            $limit = " LIMIT $limit";
        }

        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        $selects = '';
        $joins = '';
        $where = '';
        $i = 1;
        foreach ($fields as $row) {
            $settings = tp_db_helpers::extract_column_data($row->value);
            if ( $settings['visibility'] !== $meta_visibility || $meta_visibility === 'all' ) {
                continue;
            }
            $table_id = 'm' . $i; 
            $selects .= ', ' . $table_id .'.meta_value AS ' . $row->variable;
            $joins .= ' LEFT JOIN ' . TEACHPRESS_STUD_META . ' ' . $table_id . " ON ( " . $table_id . ".wp_id = s.wp_id AND " . $table_id . ".meta_key = '" . $row->variable . "' ) ";
            $i++;
        }

        if ( $count === true ) {
            $select = "COUNT(st.wp_id)";
        }
        else {
            $select = "st.wp_id, st.firstname, st.lastname, st.userlogin, st.email, s.date, s.con_id, s.waitinglist $selects";
        }
        
        $sql = "SELECT DISTINCT $select "
                . "FROM " . TEACHPRESS_SIGNUP . " s "
                . "INNER JOIN " . TEACHPRESS_STUD . " st ON st.wp_id = s.wp_id $joins"
                . "WHERE s.course_id = '$course_id' ";
        
        if ( $search !== '' ) {
            $where .= " AND ( st.firstname LIKE '%$search%' OR st.lastname LIKE '%$search%' )";
        }

        if ( $waitinglist !== '' ) {
            $where .= " AND s.waitinglist = '$waitinglist'";
        }
        
        // get_tp_message($sql . $where . $order . $limit, 'orange');
        if ( $count === true ) {
            return $wpdb->get_var($sql . $where);
        }
        return $wpdb->get_results($sql . $where . $order . $limit, $output_type);
    }
    
    /** 
     * Subscribe a student manually
     * @param int $wp_id        ID of the student
     * @param int $course_id    ID of the course
     * @return boolean
     * @since 5.0.0
    */	
    public static function add_signup($wp_id, $course_id) {
        global $wpdb;
        if ( $wp_id != 0 && $course_id != 0 ) {
            $time = current_time('mysql',0);
            $wpdb->insert( TEACHPRESS_SIGNUP, array( 'course_id' => $course_id,
                                                     'wp_id' => $wp_id,
                                                     'waitinglist' => 0,
                                                     'date' => $time, ),
                                                     array( '%d', '%d', '%d', '%s') );
            // Find course name
            $name = self::get_course_name($course_id);
            
            // Send notification
            tp_enrollments::send_notification(201, $wp_id, $name);
            
            return true;
        }
        return false;
    }
    
    /**
     * Moves a signup to an other course
     * @param array $checkbox     An array of registration IDs
     * @param int $course         The course ID
     * @since 5.0.0
     */
    public static function move_signup($checkbox, $course) {
        global $wpdb;
        if ( $checkbox == '' ) { 
            return false; 
        }
        $course = intval($course);
        $max = count($checkbox);
        for ( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            if ( $checkbox[$i] != 0 && $course != 0) {
                $wpdb->update( TEACHPRESS_SIGNUP, array ('course_id' => $course), array( 'con_id' => $checkbox[$i] ), array('%d'), array('%d') );
            }
        }
    }
    
    /** 
     * Change the status of one or more course signups
     * @param array $checkbox   IDs of the signups
     * @param string $status    The new status for the signups (course or waitinglist)
     * @since 5.0.0
    */
    public static function change_signup_status($checkbox, $status = 'course') {
        global $wpdb;
        if ( $checkbox == '' ) { return false; }
        $status = ( $status === 'course' ) ? 0 : 1;
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->update( TEACHPRESS_SIGNUP, array ( 'waitinglist' => $status ), array ( 'con_id' => $checkbox[$i] ), array ( '%d'), array ( '%d' ) );
        }
    }
    
    /** 
     * Delete signup and add an entry from the waitinglist to the course (if possible). Please note that this function doesn't use transactions like tp_delete_signup_student().
     * @param array $checkbox   An array with course IDs
     * @param boolean $move_up  A flag for the automatic move up from waitinglist entries
     * @since 5.0.0
    */
    public static function delete_signup($checkbox, $move_up = true) {
        global $wpdb;
        if ( $checkbox == '' ) {
            return false;
        }
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            if ( $move_up !== true ) {
                self::move_up_signup($checkbox[$i]);
            }
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * This function is used to move a signup entry from waitinglist into the course if a signup is deleted.
     * @param int $connect_id   The ID of the signup which will be deleted
     * @since 5.0.0
     * @access public
     */
    public static function move_up_signup($connect_id) {
        global $wpdb;
        
        $connect_id = intval($connect_id);
        
        // Get course ID
        $course_id = $wpdb->get_var("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$connect_id'");
        if ( $course_id === NULL ) {
            return;
        }

        // check if there are users in the waiting list
        $signup = $wpdb->get_row("SELECT `con_id`, `course_id`, `wp_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '" . $course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1");
        if ( $signup === NULL ) {
            return;
        }
        
        // if is true subscribe the first one in the waiting list for the course
        if ($signup->con_id != 0 && $signup->con_id != '') {
            $wpdb->query( "UPDATE " . TEACHPRESS_SIGNUP . " SET `waitinglist` = '0' WHERE `con_id` = '" . $signup->con_id . "'" );
            
            // Find course name
            $name = self::get_course_name($course_id);
            
            // Send notification
            tp_enrollments::send_notification(201, $signup->wp_id, $name);
        }	
        
    }
    
    /**
     * Returns true if the current user is subscribed in the tested course or false if not.
     * @param integer $course_id                The course ID
     * @param boolean $consider_childcourses    Default is false
     * @return boolean
     * @since 5.0.0
     */
    public static function is_student_subscribed ($course_id, $consider_childcourses = false) {
        global $wpdb;
        
        $current_user = wp_get_current_user();
        $course_id = intval($course_id);
        $user_ID = $current_user->ID;
        
        if ( $course_id == 0 ) {
            return false;
        }
        // simple case
        if ( $consider_childcourses == false ) {
            $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
        }
        // consider child courses
        if ( $consider_childcourses == true ) {
            $where = '';
            $courses = $wpdb->get_results("SELECT `course_id` FROM " . TEACHPRESS_COURSES . " WHERE `parent` = '$course_id'");
            foreach ( $courses as $row ) {
                $where = $where == '' ? "`course_id` = '$row->course_id'" : $where . " OR `course_id` = '$row->course_id'";
            }
            if ( $where != '' ) {
                $where = " WHERE `wp_id` = '$user_ID' AND `waitinglist` = '0' AND ( $where OR `course_id` = '$course_id' )";
                $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " $where");
            }
            // Fallback if there are no child courses
            else {
                $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
            }
        }

        if ( $test >= 1 ) {
            return true;
        }
        return false;
    }
    
    /**
     * This function subscribes student from the waitinglist to the course, if the number of places is raised up.
     * This is used in tp_courses::change_course()
     * 
     * @param int $course_id        The course ID
     * @param int $new_places       The new number of places
     * @param int $old_places       The old number of places
     * @since 5.0.0
     * @access private
     */
    private static function handle_changes_of_free_places($course_id, $new_places, $old_places){
        global $wpdb;
        $course_id = intval($course_id);
        $new_free_places = $new_places - $old_places;
        
        $sql = "SELECT s.con_id, s.wp_id, s.waitinglist, s.date
                FROM " . TEACHPRESS_SIGNUP . " s 
                INNER JOIN " . TEACHPRESS_COURSES . " c ON c.course_id=s.course_id
                WHERE c.course_id = '$course_id' AND s.waitinglist = '1' ORDER BY s.date ASC";
        $waitinglist = $wpdb->get_results($sql, ARRAY_A);
        
        if ( count($waitinglist) === 0 ) {
            return;
        }
        
        // Subscribe students from waitinglist if there are new free places in the course
        foreach ( $waitinglist as $waitinglist ) {
            if ( $new_free_places > 0 ) {
                $wpdb->update( TEACHPRESS_SIGNUP, array ( 'waitinglist' => 0 ), array ( 'con_id' => $waitinglist["con_id"] ), array ( '%d' ), array ( '%d' ) );
                // Find course name
                $name = self::get_course_name($course_id);

                // Send notification
                tp_enrollments::send_notification(201, $waitinglist["wp_id"], $name);
            }
            else {
                break;
            }
            $new_free_places--;
        }
    }
    
}

