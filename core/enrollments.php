<?php
/**
 * This file contains frontend functions for the enrollment forms of teachpress
 * 
 * @package teachpress\core\enrollments
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all special functions for the shortcode [tpenrollments]
 * @package teachpress\core\enrollments
 * @since 5.0.0
 */
class tp_enrollments {
    
    /**
     * Add signups for a student
     * @param int $user_id
     * @param array $checkbox
     * @return string
     * @since 5.0.0
     */
    public static function add_signups ($user_id, $checkbox) {
        global $wpdb;
        $return = '';
        $max = count( $checkbox );
        for ($n = 0; $n < $max; $n++) {
            $row = $wpdb->get_row("SELECT `name`, `parent` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '$checkbox[$n]'");
            if ($row->parent != '0') {
                $parent = tp_courses::get_course_data($row->parent, 'name');
                $row->name = ( $row->name != $parent ) ? $parent . ' ' . $row->name : $row->name;
            }
            $code = self::add_signup($checkbox[$n], $user_id);
            self::send_notification($code, $user_id, $row->name);
            $message = self::get_signup_message($code);
            if ($code === 201) { $class = 'teachpress_message_success'; }
            elseif ($code === 202) { $class = 'teachpress_message_info'; }
            else { $class = 'teachpress_message_error'; }
            $return .= '<div class="' . $class . '">&quot;' . stripslashes($row->name) . '&quot;: ' . $message . '</div>';
        }
        return $return;
        
    }
    
    /** 
     * Add signup (= subscribe student in a course)
     * @param int $checkbox     course_id
     * @param int $wp_id        user_id
     * @return int      This function returns a status code. This means:
     *                  code 0    --> ERROR: course_id was 0
     *                  code 101  --> user is already registered
     *                  code 102  --> user is already registered in waitinglist
     *                  code 103  --> user is already registered for an other course of the course group
     *                  code 104  --> no free places availablea
     *                  code 201  --> registration was successful
     *                  code 202  --> registration was successful for waitinglist
     * @since 5.0.0
     * @access private
    */
    private static function add_signup($checkbox, $wp_id){
       global $wpdb;
       $checkbox = intval($checkbox);
       $wp_id = intval($wp_id);
       if ( $checkbox == 0 ) {
            return 0;
       }
       // Start transaction
       $wpdb->query("START TRANSACTION");
       $wpdb->query("SET AUTOCOMMIT=0");
       // Check if the user is already registered
       $check = $wpdb->get_var("SELECT `waitinglist` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$checkbox' and `wp_id` = '$wp_id'");
       if ( $check != NULL && $check == '0' ) {
            $wpdb->query("ROLLBACK");
            return 101;
       } 
       if ( $check != NULL && $check == '1' ) {
            $wpdb->query("ROLLBACK");
            return 102;
       }
       // Check if there is a strict signup
       $row1 = $wpdb->get_row("SELECT `places`, `waitinglist`, `parent` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '$checkbox'");
       if ( $row1->parent != 0 ) {
            $check = tp_courses::get_course_data($row1->parent, 'strict_signup');
            if ( $check != 0 ) {
                 $check2 = $wpdb->query("SELECT c.course_id FROM " . TEACHPRESS_COURSES . " c INNER JOIN " . TEACHPRESS_SIGNUP . " s ON s.course_id = c.course_id WHERE c.parent = '$row1->parent' AND s.wp_id = '$wp_id' AND s.waitinglist = '0'");
                 if ( $check2 != NULL ) {
                     $wpdb->query("ROLLBACK");
                     return 103;
                 }
            }
       }
       // Check if there are free places available
       $used_places = $wpdb->query("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$checkbox' AND `waitinglist` = 0");
       if ($used_places < $row1->places ) {
            // Subscribe
            $wpdb->query("INSERT INTO " . TEACHPRESS_SIGNUP . " (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$checkbox', '$wp_id', '0', NOW() )");
            $wpdb->query("COMMIT");
            return 201;
       }
       else {
            // if there is a waiting list available
            if ($row1->waitinglist == '1') {
                  $wpdb->query( "INSERT INTO " . TEACHPRESS_SIGNUP . " (course_id, wp_id, waitinglist, date) VALUES ('$checkbox', '$wp_id', '1', NOW() )" );
                  $wpdb->query("COMMIT");
                  return 202;
            }
            else {
                $wpdb->query("ROLLBACK");
                return 104;
            }
       }
    }
    
    /**
     * Add student
     * @param int $user_id
     * @param string $user_login
     * @param string $user_email
     * @return string
     * @since 5.0.0
     */
    public static function add_student ($user_id, $user_login, $user_email, $fields, $post) {
        $data = array(
         'firstname' => isset($post['firstname']) ? htmlspecialchars($post['firstname']) : '',
         'lastname' => isset($post['lastname']) ? htmlspecialchars($post['lastname']) : '',
         'userlogin' => $user_login,
         'email' => $user_email
        );
        $ret = tp_students::add_student($user_id, $data);
        if ( $data['firstname'] === '' && $data['lastname'] === '' ) {
            return '<div class="teachpress_message_error"><strong>' . __('Error: Please enter your first- and lastname','teachpress') . '</strong></div>';
        }
        if ( $ret === false ) {
            return '<div class="teachpress_message_error"><strong>' . __('Error: User already exist','teachpress') . '</strong></div>';
        }
        tp_db_helpers::prepare_meta_data( $user_id, $fields, $post, 'students' );
        return '<div class="teachpress_message_success"><strong>' . __('Registration successful','teachpress') . '</strong></div>';
    }

    /**
     * Get registration message
     * @param int $code
     * @return boolean 
     * @since 5.0.0
     */
    public static function get_signup_message($code) {
        switch ($code) {
        case 0:
            return __('Warning: Wrong course_id','teachpress');
        case 101:
            return __('You are already registered for this course.','teachpress');
        case 102:
            return __('Registration is not possible, because you are already registered in the waitinglist.','teachpress');
        case 103:
            return __('Registration is not possible, because you are already registered for an other course of this course group.','teachpress');
        case 104:
            return __('No free places available.','teachpress');
        case 201:
            return __('Registration was successful.','teachpress');
        case 202:
            return __('For this course there are no more free places available. You are automatically signed up in a waiting list.','teachpress');
        default:
            return false;
        }
    }
    
    /** 
     * Unsubscribe a student from a course
     * @param array $checkbox   An array with the registration IDs
     * @return string
     * @since 5.0.0
    */
    public static function delete_signup($checkbox) {
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            // Select course ID
            $sql = "SELECT `course_id`, `waitinglist` FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'";
            $signup = $wpdb->get_row($sql);
            // Start transaction
            $wpdb->query("START TRANSACTION");
            $wpdb->query("SET AUTOCOMMIT=0");
            // check if there are users in the waiting list
            if ( $signup->waitinglist == 0 ) {
                tp_courses::move_up_signup($checkbox[$i]);
            }
            $wpdb->query("DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'");
            // End transaction
            $wpdb->query("COMMIT");
        }	
        return '<div class="teachpress_message_success">' . __('You are signed out successful','teachpress') . '</div>';
    }
    
    /**
     * Extracts checkbox data for meta data fields and returns an array with the saved values. 
     * 
     * A string for checkbox data has the following structure:
     * {value1},{value2},{value3}
     * 
     * @param string $input
     * @return array
     * @since 5.0.0
     */
    public static function extract_checkbox_data($input) {
        $values = array();
        $array_values = explode(',', $input);
        foreach( $array_values as $element ) {
            $element = str_replace(array('{','}'), array('',''), $element);
            array_push($values, $element);
        }
        return $values;
    }
    
    /**
     * Returns a checkbox field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $checked       current value for the field
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_checkbox_field($field_name, $label, $checked, $readonly = false, $required = false){
        global $wpdb;
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . $field_name . "' ORDER BY value ASC");
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        // extrakt checkbox_values
        $array_checked = self::extract_checkbox_data($checked);
        $return = '<tr>';
        $return .= '<td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>';
        $return .= '<td>';
        $i = 1;
        $max = count($options);
        foreach ($options as $opt) {
            $checked = ( in_array($opt->value, $array_checked) ) ? 'checked="checked"' : '';
            $required = ( $max === 1 ) ? $required : '';  // The required optopns is only available for single checkboxes
            $return .= '<input name="' . $field_name . '[]" type="checkbox" id="' . $field_name . '_' . $i . '" value="' . stripslashes($opt->value) . '" ' . $checked . ' ' . $readonly . ' ' . $required . '/> <label for="' . $field_name . '_' . $i . '">' . stripslashes($opt->value) . '</label><br/>';
            $i++;
        }
        $return .= '</td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns date select fields for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_date_field ($field_name, $label, $value) {
        if ( $value != '' ) {
            $b = tp_datesplit($value);
        }
        $day = ( $value != '' ) ? $b[0][2] : '01';
        $month = ( $value != '' ) ? $b[0][1] : '01';
        $year = ( $value != '' ) ? $b[0][0] : '19xx';
        $months = array ( __('Jan','teachpress'), __('Feb','teachpress'), __('Mar','teachpress'), __('Apr','teachpress'), __('May','teachpress'), __('Jun','teachpress'), __('Jul','teachpress'), __('Aug','teachpress'), __('Sep','teachpress'), __('Oct','teachpress'), __('Nov','teachpress'), __('Dec','teachpress') );
        $return = '';
        $return .= '<tr>';
        $return .= '<td><b>' . stripslashes($label) . '</b></td>';
        $return .= '<td>';
        $return .= '<input name="' . $field_name . '_day" id="' . $field_name . '_day" type="text" title="Day" size="2" value="' . $day . '"/>';
        $return .= '<select name="' . $field_name . '_month" id="' . $field_name . '_month" title="' . __('Month','teachpress') . '">';
        for ( $i = 1; $i <= 12; $i++ ) {
            $m = ( $i < 10 ) ? '0' . $i : $i;
            $selected = ($month == $m) ? 'selected="selected"' : '';
            $return .= '<option value="' . $m . '" ' . $selected . '>' . $months[$i-1] . '</option>';
        }
        $return .= '</select>';
        $return .= '<input name="' . $field_name . '_year" id="' . $field_name . '_year" type="text" title="' . __('Year','teachpress') . '" size="4" value="' . $year . '"/>';
        $return .= '</td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns a hidden field for user form
     * @param string $field_name    name/id of the field
     * @param int $value            value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_hidden_field($field_name, $value) {
        return '<tr style="visible:hidden;">
                    <td></td>
                    <td><input name="' . $field_name . '" type="hidden" id="' . $field_name . '" value="' . stripslashes($value) . '"/></td>
                 </tr>';
    }
    
    /**
     * Returns a number field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param int $value            value for the field
     * @param int $min              default is 0
     * @param int $max              default is 999
     * @param int step              default is 1
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_int_field ($field_name, $label, $value, $min = 0, $max = 999, $step = 1, $readonly = false, $required = false) {
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        return '<tr>
                    <td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>
                    <td><input name="' . $field_name . '" type="number" id="' . $field_name . '" value="' . $value . '" size="50" ' . $readonly . ' ' . $required . ' min="' . $min . '" max="' . $max . '" step="' . $step . '"/></td>
                 </tr>';
    }
    
    /**
     * Returns a checkbox field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         current value for the field
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_radio_field ($field_name, $label, $value, $readonly = false, $required = false) {
        global $wpdb;
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . $field_name . "' ORDER BY value ASC");
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        $return = '<tr>';
        $return .= '<td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>';
        $return .= '<td>';
        $i = 1;
        foreach ($options as $opt) {
            $checked = ( $value == $opt->value ) ? 'checked="checked"' : '';
            $return .= '<input name="' . $field_name . '" type="radio" id="' . $field_name . '_' . $i . '" value="' . stripslashes($opt->value) . '" ' . $checked . ' ' . $readonly . ' ' . $required . '/> <label for="' . $field_name . '_' . $i . '">' . stripslashes($opt->value) . '</label><br/>';
            $i++;
        }
        $return .= '</td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns a select box for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_select_field ($field_name, $label, $value) {
        global $wpdb;
        $return = '';
        $return .= '<tr>';
        $return .= '<td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>';
        $return .= '<td><select name="' . $field_name . '" id="' . $field_name . '">';
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . $field_name . "' ORDER BY value ASC");
        if ( $value == '' ) {
            $return .= '<option value="">- ' . __('Please select','teachpress') . ' -</option>';
        }
        foreach ($options as $opt) {
            $selected = ( $value == $opt->value ) ? 'selected="selected"' : '';
            $return .= '<option value="' . stripslashes($opt->value) . '" ' . $selected . '>' . stripslashes($opt->value) . '</option>';
        }
        $return .= '</select></td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns a text field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_text_field($field_name, $label, $value, $readonly = false, $required = false) {
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        return '<tr>
                    <td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>
                    <td><input name="' . $field_name . '" type="text" id="' . $field_name . '" value="' . stripslashes($value) . '" size="50" ' . $readonly . ' ' . $required . '/></td>
                 </tr>';
    }
    
    /**
     * Returns a texteara field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_textarea_field ($field_name, $label, $value, $required = false) {
        $required = ( $required === true ) ? 'required="required"' : '';
        return '<tr>
                    <td><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></td>
                    <td><textarea name="' . $field_name . '" id="' . $field_name . '" style="width:100%; height:80px;" ' . $required . '>' . stripslashes($value) . '</textarea></td>
                 </tr>';
    }
    
    /**
     * Returns the main menu for enrollments
     * @param string $tab
     * @param object $user
     * @return string
     * @since 5.0.0
     */
    public static function get_menu ($tab, $user) {
        $url = self::get_url();
        // Create Tabs
        $tab1 = ( $tab === '' || $tab === 'current' ) ? '<span class="teachpress_active_tab">' . __('Current enrollments','teachpress') . '</span>' : '<a href="' . $url . 'current">' . __('Current enrollments','teachpress') . '</a>';
        
        $tab2 = ( $tab === 'old' ) ? '<span class="teachpress_active_tab">' . __('Your enrollments','teachpress') . '</span>' : '<a href="' . $url . 'old">' . __('Your enrollments','teachpress') . '</a>';
        
        $tab3 = ( $tab === 'results' ) ? '<span class="teachpress_active_tab">' . __('Your results','teachpress') . '</span>' : '<a href="' . $url . 'results">' . __('Your results','teachpress') . '</a>';
        
        $tab4 = ( $tab === 'data' ) ? '<span class="teachpress_active_tab">' . __('Your data','teachpress') . '</span>' : '<a href="' . $url . 'data">' . __('Your data','teachpress') . '</a>';
        
        $rtn = '<div class="tp_user_menu">
                   <h4>' . __('Hello','teachpress') . ', ' . stripslashes($user['firstname']) . ' ' . stripslashes($user['lastname']) . '</h4>'
                . '<p>' . $tab1 . ' | ' . $tab2 . ' | ' . $tab3 . ' | ' . $tab4 . '</p></div>'; 
        return $rtn;
    }
    
    /**
     * Returns the url for the page, where the shortcode is used.
     * @global type $pagenow
     * @return string
     * @since 5.0.0
     */
    public static function get_url () {
        global $pagenow;
        
        $page = (is_page()) ? 'page_id' : 'p';
        $url = str_replace("index.php", "", $pagenow);
        
        // Define permalinks
        if ( get_option('permalink_structure') ) {
           $url .= '?tab=';
        }
        else {
           $url .= '?' . $page . '=' . get_the_id() . '&amp;tab=';
        }
        return $url;
    }
    
    /**
     * Creates the table for signups/waitinglist entries for old tab
     * @param int $user_id              The user ID
     * @param object $row               An object of course_data
     * @param boolean $is_sign_out      Defines if there is a signout option for users (true) or not (false)
     * @return string
     * @since 5.0.0
     */
    private static function create_signups_table ($user_id, $row, $is_sign_out) {
        $rtn = '<table class="teachpress_enr_old">';
        $rtn .= '<tr>';
        if ( $is_sign_out === true ) {
            $rtn .= '<th width="15">&nbsp;</th>';
        }
        $rtn .= '<th>' . __('Name','teachpress') . '</th>
                <th>' . __('Type') . '</th>
                <th>' . __('Date','teachpress') . '</th>
                <th>' . __('Room','teachpress') . '</th>
                <th>' . __('Term','teachpress') . '</th>
               </tr>';
        // If there is nothing
        if ( count($row) === 0 ) {
            return '<tr><td colspan="6">' . __('No enrollments','teachpress') . '</td></tr>'
                    . '</table>';
        }
        // Select all courses where user is registered
        foreach($row as $row1) {
            if ($row1->parent_name != '') {
                $row1->parent_name .= ' -';
            }
            $rtn .= '<tr>';
            if ( $is_sign_out === true) {
                $checkbox = ( tp_students::has_assessment($user_id, $row1->course_id) === false ) ? '<input name="checkbox2[]" type="checkbox" value="' . $row1->con_id . '" title="' . $row1->name . '" id="ver_' . $row1->con_id . '"/>' : '';
                $rtn .='<td>' . $checkbox . '</td>';
            }		
            $rtn .= '<td><label for="ver_' . $row1->con_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . stripslashes($row1->parent_name) . ' ' .  stripslashes($row1->name) . '</label></td>
                    <td>' . stripslashes($row1->type) . '</td>
                    <td>' . stripslashes($row1->date) . '</td>
                    <td>' . stripslashes($row1->room) . '</td> 
                    <td>' . stripslashes($row1->semester) . '</td>
                    </tr>';
        }
        $rtn .= '</table>';
        return $rtn;
    }
    
    /**
     * Returns the tab for former signups/waitinglist places
     * @param int $user_id          The user id
     * @param boolean $is_sign_out  Defines if there is a signout option for users (true) or not (false)
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function get_old_tab ($user_id, $is_sign_out) { 
        $rtn = '<p><strong>' . __('Signed up for','teachpress') . '</strong></p>';
        
        // signups
        $row1 = tp_students::get_signups( array('wp_id' => $user_id, 'mode' => 'reg') );
        $rtn .= self::create_signups_table($user_id, $row1, $is_sign_out);
        
        // waitinglist entries
        $row2 = tp_students::get_signups( array('wp_id' => $user_id, 'mode' => 'wtl') );
        if ( count($row2) !== 0 ) {
            $rtn .= '<p><strong>' . __('Waiting list','teachpress') . '</strong></p>';
            $rtn .= self::create_signups_table($user_id, $row2, $is_sign_out);
        }
        if ($is_sign_out === true) {
            $rtn .= '<p><input name="austragen" type="submit" value="' . __('unsubscribe','teachpress') . '" id="austragen" /></p>';
        }
        return $rtn;
    }
    
    /**
     * Returns the results tab
     * @param int $user_id
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function get_results_tab($user_id) {
        $url = self::get_url();
        $course_id = ( isset($_GET['course_id']) ) ? intval($_GET['course_id'] ) : 0;
        if ( $course_id !== 0 ) {
            return self::get_results_details($course_id, $user_id, $url);
        }
        return self::get_results_overview($user_id, $url);
    }
    
    /**
     * Returns the result details page for the results tab
     * @param int $course_id
     * @param int $user_id
     * @param string $url
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function get_results_details($course_id, $user_id, $url){
        $course_data = tp_courses::get_course($course_id, ARRAY_A);
        $assessment = tp_assessments::get_assessments($user_id, '', $course_id);
        $rtn = '';
        $parent_name = '';
        $rtn .= '<a href="' . $url . 'results" class="teachpress_enr_button">&larr; ' . __('Back','teachpress') . '</a>';
        
        // Handle course name
        if ( $course_data["parent"] != 0 ) {
            $parent = tp_courses::get_course($course_data["parent"], ARRAY_A);
            $parent_name = $parent['name'] . ' -';
        }
        $rtn .= '<h3>' . stripslashes($parent_name) . ' ' . stripslashes($course_data['name']) . '</h3>';
        
        // Main Course Result
        $rtn .= '<table class="">';
        if ( count($assessment) !== 0 ) {
            $passed = ( $assessment[0]['passed'] == 1 ) ? __('passed','teachpress') : __('not passed','teachpress');
            $rtn .= '<tr class="tp_course_result">'
                    . '<td class="title">' . __('Course result','teachpress') . '</td>'
                    . '<td>' . stripslashes($assessment[0]['comment']) . '</td>'
                    . '<td>' . stripslashes($assessment[0]['value']) . '</td>'
                    . '<td>' . $passed . '</td>'
                    . '</tr>';
        }
        // Artefacts
        $artefacts = tp_artefacts::get_artefacts($course_id, 0);
        foreach ($artefacts as $row) {
            $assessments = tp_assessments::get_assessments($user_id, $row['artefact_id'], $course_id);
            $x = 1;
            foreach ($assessments as $inner_row) {
                $title = ( $x === 1 ) ? stripslashes($row['title']) : '';
                $passed = ( $inner_row['passed'] == 1 ) ? __('passed','teachpress') : __('not passed','teachpress');
                $rtn .= '<tr class="tp_assessment_result">'
                        . '<td class="title">' . $title . '</td>'
                        . '<td>' . stripslashes($inner_row['comment']) . '</td>'
                        . '<td>' . stripslashes($inner_row['value']) . '</td>'
                        . '<td>' . $passed . '</td>'
                        . '</tr>';
                $x++;
            }
        }
        // Print message if there are no results
        if ( count($artefacts) === 0 && count($assessment) === 0 ) {
            $rtn .= '<tr>'
                    . '<td colspan="4"><b>' . __('No results available','teachpress') . '</b></td>'
                    . '</tr>';
        }
        $rtn .= '</table>';
        return $rtn;
    }
    
    /**
     * Returns the overview table for the results tab
     * @param int $user_id
     * @param string $url
     * @since 5.0.0
     * @access private
     */
    private static function get_results_overview($user_id, $url) {
        $rtn = '';
        $rtn .= '<table class="teachpress_enr_results">'
                . '<tr>'
                . '<th>' . __('Name','teachpress') . '</th>'
                . '<th>' . __('Type') . '</th>'
                . '<th>' . __('Term','teachpress') . '</th>'
                . '<th>' . __('Result','teachpress') . '</th>'
                . '<th></th>'
                . '</tr>';
        $courses = tp_students::get_signups( array('wp_id' => $user_id, 'mode' => 'reg') );
        if ( count($courses) === 0 ) {
            $rtn .= '<tr><td>' . __('No results available','teachpress') . '</td></tr>';
        }
        foreach ($courses as $row) {
            // read assessment
            $result = '';
            $assessment = tp_assessments::get_assessments($user_id, '', $row->course_id);
            if ( count($assessment) !== 0 ) {
                $result = $assessment[0]['value'];
            }
            // Handle course name
            if ($row->parent_name != '') {
                $row->parent_name .= ' -';
            }
            $rtn .= '<tr>';
            $rtn .= '<td>' . stripslashes($row->parent_name) . ' ' .  stripslashes($row->name) . '</td>';
            $rtn .= '<td>' . stripslashes($row->type) . '</td>';
            $rtn .= '<td>' . stripslashes($row->semester) . '</td>';
            $rtn .= '<td>' . stripslashes($result) . '</td>';
            $rtn .= '<td><a href="' . $url . 'results&amp;course_id=' . $row->course_id . '">' . __('Show details','teachpress') . '</a></td>';
            $rtn .= '</tr>';
        }
        $rtn .= '</table>';
        return $rtn;
    }
    
    /**
     * Returns the interface for logged in users
     * @param int $user_id          The user ID
     * @param string $user_login    The user login name
     * @param string $user_email    The email adress of the user
     * @param boolean $user_exists  TRUE or FALSE
     * @param string $tab           The selected tab (old, results, data)
     * @return string
     * @since 5.0.0
     */
    public static function get_interface_for_users($user_id, $user_login, $user_email, $user_exists, $tab){
        $is_sign_out = ( get_tp_option('sign_out') == '0' ) ? true : false;
        $rtn = '';
        
        // if user is not registered: Registration
        if ( $user_exists === false ) {
            $user = array('userlogin' => $user_login, 'email'=> $user_email);
            return tp_registration_form($user);
        }

        // Select all user information
        $row = tp_students::get_student($user_id);
        // Menu
        $rtn .= self::get_menu($tab, $row);

        // Old Enrollments / Sign out
        if ( $tab === 'old' ) {
            $rtn .= self::get_old_tab($user_id, $is_sign_out); 
        }
        // Results / Assessments
        if ( $tab === 'results'  ) {
            $rtn .= self::get_results_tab($user_id);
        }
        // Edit userdata
        if ( $tab === 'data' ) {
            $rtn .= tp_registration_form($user_id, 'edit'); 
        }
        return $rtn;
     
    }


    /**
     * Returns the enrollment tab
     * @param string $sem
     * @param array $settings (date_format, order_parent, order_child)
     * @param boolean $user_exists
     * @return string
     * @since 5.0.0
     */
    public static function get_enrollments_tab($sem, $settings, $user_exists) {
        global $wpdb;
        $rtn = '';
        // Select all courses where enrollments in the current term are available
        $row = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSES . " WHERE `semester` = '$sem' AND `parent` = '0' AND (`visible` = '1' OR `visible` = '2') ORDER BY " . $settings['order_parent']);
        foreach( $row as $row ) {
            $rtn .= self::load_course_entry($row, $settings, $user_exists);	
        }	
        if ( $user_exists === true ) {
            $rtn .= '<input name="einschreiben" type="submit" value="' . __('Sign up','teachpress') . '" />';
        }
        return $rtn;
    }
    
    /**
     * Returns a table with a course and his sub courses for enrollments tab
     * @param object $row
     * @param array $settings (date_format, order_parent, order_child)
     * @param boolean $user_exists
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function load_course_entry ($row, $settings, $user_exists) {
        global $wpdb;
        
        $course_name = ( $row->rel_page != 0 ) ? '<a href="' . get_permalink($row->rel_page) . '">' . stripslashes($row->name) . '</a>' : stripslashes($row->name);

        // load all childs
        $childs = $wpdb->get_results("Select * FROM " . TEACHPRESS_COURSES . " WHERE `parent` = '$row->course_id' AND (`visible` = '1' OR `visible` = '2') AND (`start` != '0000-00-00 00:00:00') ORDER BY " . $settings['order_child']);
        
        // leave the function if there is nothing to show
        if ( $row->start == '0000-00-00 00:00:00' && count($childs) === 0 ) {
            return;
        }
        
        // build course string
        $rtn = '<div class="teachpress_course_group">';
        $rtn .= '<div class="teachpress_course_name">' . $course_name . '</div>';
        $rtn .= '<table class="teachpress_enr" width="100%" border="0">';
        $rtn .= self::create_course_entry($row, $settings['date_format'], $user_exists);
        foreach ( $childs as $child ) {
            $rtn .= self::create_course_entry($child, $settings['date_format'], $user_exists, $row->name);
        }
        $rtn .= '</table>';
        $rtn .= '</div>';
        return $rtn;
    }

    /**
     * Returns a single course entry for the function load_course_entry()
     * @param object $row
     * @param string $date_format
     * @param boolean $user_exists
     * @param string $parent_name
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function create_course_entry ($row, $date_format, $user_exists, $parent_name = '') {
        
        // define some course variables
        $date1 = $row->start;
        $date2 = $row->end;
        $free_places = tp_courses::get_free_places($row->course_id, $row->places);
        if ( $free_places < 0 ) {
            $free_places = 0;
        }
        
        // Overwrite child name
        if ($parent_name == $row->name) {
            $row->name = $row->type;
        }
        
        // checkbox
        $checkbox = '&nbsp;';
        $checkbox_label = stripslashes($row->type);
        if ( $user_exists === true ) {
            if ($date1 != '0000-00-00 00:00:00' && current_time('mysql') >= $date1 && current_time('mysql') <= $date2) {
               $checkbox = '<input type="checkbox" name="checkbox[]" value="' . $row->course_id . '" title="' . stripslashes($row->name) . ' ' . __('Select','teachpress') . '" id="checkbox_' . $row->course_id . '"/>';
               $checkbox_label = '<label for="checkbox_' . $row->course_id . '" style="line-height:normal;">' . stripslashes($row->type) . '</label>';
            } 
        }
        
        // display configs
        $display_free_places = ( $date1 != '0000-00-00 00:00:00' ) ? $free_places . ' ' . __('of','teachpress') . ' ' .  $row->places : '&nbsp;';
        $waitinglist_info = ( $row->waitinglist == 1 && $free_places == 0 ) ? __('Possible to subscribe in the waiting list','teachpress') : '&nbsp;';
        $registration_period = ($date1 != '0000-00-00 00:00:00') ? __('Registration period','teachpress') . ': ' . date( $date_format, strtotime($row->start) ) . ' ' . __('to','teachpress') . ' ' . date( $date_format, strtotime($row->end) ) : '&nbsp;';
        $additional_info = ( $parent_name != '' ) ? stripslashes(nl2br($row->comment)) . ' ' : '';
        
        // Row 1
        $rtn = '<tr>';
        $rtn .= '<td rowspan="3" width="25" style="border-bottom:1px solid silver; border-collapse: collapse;">' . $checkbox . '</td>';
        $rtn .= '<td colspan="2">&nbsp;</td>';
        $rtn .= '<td align="center"><strong>' . __('Date(s)','teachpress') . '</strong></td>';
        $rtn .= '<td align="center">';
        if ($date1 != '0000-00-00 00:00:00') {
            $rtn .= '<strong>' . __('free places','teachpress') . '</strong>';
        }
        $rtn .= '</td>';
        $rtn .= '</tr>';
        
        // Row 2
        $rtn .= '<tr>';
        $rtn .= '<td width="20%" style="font-weight:bold;">' . $checkbox_label . '</td>';
        $rtn .= '<td width="20%">' . stripslashes($row->lecturer) . '</td>';
        $rtn .= '<td align="center">' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</td>';
        $rtn .= '<td align="center">' . $display_free_places . '</td>';
        $rtn .= '</tr>';
        
        // Row 3
        $rtn .= '<tr>';
        $rtn .= '<td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">' . $additional_info . $waitinglist_info . '</td>';
        $rtn .= '<td style="border-bottom:1px solid silver; border-collapse: collapse;" align="center" class="einschreibefrist">' . $registration_period . '</td>';
        $rtn .= '</tr>';
        
        return $rtn;
    }
    
    /**
     * Send email notification
     * @param int $code     Needs code 201 (successful course registration) or 202 (successful waitinglist registrarion)
     * @param int $wp_id    The user ID
     * @param string $name  The name of the course
     * @since 5.0.0
     */
    public static function send_notification($code, $wp_id, $name) {
        global $wpdb;
        if ( $code == 201 || $code == 202 ) {
            // Send user an E-Mail and return a message
            $to = $wpdb->get_var("SELECT `email` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$wp_id'");
            if ( $code == 201 ) {
                $subject = '[' . get_bloginfo('name') . '] ' . __('Registration','teachpress');
                $message = __('Your Registration for the following course was successful:','teachpress') . chr(13) . chr(10);
            }
            else {
                $subject = '[' . get_bloginfo('name') . '] ' . __('Waitinglist','teachpress');
                $message = __('You are signed up in the waitinglist for the following course:','teachpress') . chr(13) . chr(10);
            }
            $message = $message . stripslashes($name);
            $headers = 'From: ' . get_bloginfo('name') . ' ' . utf8_decode(chr(60)) .  get_bloginfo('admin_email') . utf8_decode(chr(62)) . "\r\n";
            if ( defined('TP_MAIL_SYSTEM') ) {
                require_once('php/mail.inc');
                $from = get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                tuc_mail($to, $from, $subject, $message, '');
            }
            else {
                wp_mail($to, $subject, $message, $headers);
            }
        }
    }
    
}

/**
 * The form for user registrations
 * @param int|array $user_input    If $mode is'register' or 'admin', it's only the ID. If $mode is 'edit', then it's an array.
 * @param string $mode             register, edit or admin
 * @return string
 * @since 4.0.0
 * @version 2 (since 5.0.0)
 */
function tp_registration_form ($user_input, $mode = 'register') {
    $user = ( $mode !== 'register' ) ? tp_students::get_student($user_input) : '';
    $user_meta = ( $mode !== 'register' ) ? tp_students::get_student_meta($user_input) : array( array('meta_key' => '', 'meta_value' => '') );
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);
    $rtn = '';
    $rtn .= '<form id="tp_registration_form" method="post">';
    $rtn .= '<div id="teachpress_registration">';
    if ( $mode === 'register' ) {
        $rtn .= '<p style="text-align:left; color:#FF0000;">' . __('Please fill in the following registration form and sign up in the system. You can edit your data later.','teachpress') . '</p>';
    }
    
    $rtn .= '<table border="0" cellpadding="0" cellspacing="5" style="text-align:left; padding:5px;">';
    
    // Show default fields
    if ( $mode === 'admin' ) {
        $rtn .= tp_enrollments::get_form_text_field('wp_id', __('WordPress User-ID','teachpress'), $user['wp_id'], true);
    }
    
    $firstname = ( $mode === 'register' ) ? '' : stripslashes($user['firstname']);
    $rtn .= tp_enrollments::get_form_text_field('firstname', __('First name','teachpress'), $firstname, false, true);
 
    $lastname = ( $mode === 'register' ) ? '' : stripslashes($user['lastname']);
    $rtn .= tp_enrollments::get_form_text_field('lastname', __('Last name','teachpress'), $lastname, false, true);
    
    $userlogin = ( is_array( $user_input ) ) ? $user_input['userlogin'] : $user['userlogin'];
    $rtn .= tp_enrollments::get_form_text_field('userlogin', __('User account','teachpress'), $userlogin, true);
    
    $readonly = !isset($user['email']) ? false : true;
    $email = isset($user['email']) ? stripslashes($user['email']) : '';
    $rtn .= tp_enrollments::get_form_text_field('email', __('E-Mail'), $email, $readonly);
    
    // Show custom fields
    foreach ($fields as $row) {
        $data = tp_db_helpers::extract_column_data($row['value']);
        $required = ( $data['required'] === 'true' ) ? true : false; 
        $value = '';
        foreach ( $user_meta as $row_meta ) {
            if ( $row['variable'] === $row_meta['meta_key'] ) {
                $value = $row_meta['meta_value'];
                break;
            }
        }
        if ( $data['visibility'] === 'hidden' ) {
            $rtn .= tp_enrollments::get_form_hidden_field($row['variable'], $value);
        }
        else if ( $data['type'] === 'SELECT' ) {
            $rtn .= tp_enrollments::get_form_select_field($row['variable'], $data['title'], $value);
        }
        elseif ( $data['type'] === 'TEXTAREA' ) {
            $rtn .= tp_enrollments::get_form_textarea_field($row['variable'], $data['title'], $value, $required);
        }
        elseif ( $data['type'] === 'DATE' ) {
            $rtn .= tp_enrollments::get_form_date_field($row['variable'], $data['title'], $value);
        }
        elseif ( $data['type'] === 'INT' ) {
            $data['min'] = ( $data['min'] !== 'false' ) ? intval($data['min']) : 0;
            $data['max'] = ( $data['max'] !== 'false' ) ? intval($data['max']) : 999;
            $data['step'] = ( $data['step'] !== 'false' ) ? intval($data['step']) : 1;
            $rtn .= tp_enrollments::get_form_int_field($row['variable'], $data['title'], $value, $data['min'], $data['max'], $data['step'], false, $required);
        }
        elseif ( $data['type'] === 'CHECKBOX' ) {
            $rtn .= tp_enrollments::get_form_checkbox_field($row['variable'], $data['title'], $value, false, $required);
        }
        elseif ( $data['type'] === 'RADIO' ) {
            $rtn .= tp_enrollments::get_form_radio_field($row['variable'], $data['title'], $value, false, $required);
        }
        else {
            $rtn .= tp_enrollments::get_form_text_field($row['variable'], $data['title'], $value, false, $required);
        }
    }
    $rtn .= '</table>';
    
    $name = ( $mode === 'register' ) ? 'tp_add_user' : 'tp_change_user';
    $rtn .= '<input name="' . $name . '" type="submit" class="button-primary" id="' . $name . '" value="' . __('Send','teachpress') . '" />
             </div>
         </form>';
    return $rtn;
}

/** 
 * Show the enrollment system
 * @param array $atts
 *      @type string term           The term you want to show
 *      @type string date_format    Default: d.m.Y
 *      @type string order_parent   Default: type DESC, name
 *      @type string order_child    Default: name
 * @return string
*/
function tp_enrollments_shortcode($atts) {
    // Shortcode options
    extract(shortcode_atts(array(
       'term' => '',
       'date_format' => 'd.m.Y H:i',
       'order_parent' => 'type DESC, name',
       'order_child' => 'name'
    ), $atts));
    $term = htmlspecialchars($term);
    $settings = array (
        'date_format' => htmlspecialchars($date_format),
        'order_parent' => esc_sql($order_parent),
        'order_child' => esc_sql($order_child)
    );
    
    // Advanced Login
    $tp_login = get_tp_option('login');
    if ( $tp_login == 'int' ) {
         tp_advanced_registration();
    }
    
    // WordPress
    global $user_ID;
    global $user_email;
    global $user_login;
    get_currentuserinfo();

    // teachPress
    $sem = ( $term != '' ) ? $term : get_tp_option('sem');
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);

    // Form   
    $checkbox = ( isset($_POST['checkbox']) ) ? $_POST['checkbox'] : '';
    $checkbox2 = ( isset($_POST['checkbox2']) ) ? $_POST['checkbox2'] : '';
    $tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : '';
   
    $rtn = '<div id="enrollments">
            <h2 class="tp_enrollments">' . __('Enrollments for the','teachpress') . ' ' . $sem . '</h2>
            <form name="anzeige" method="post" id="anzeige" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    /*
     * actions
    */ 
    // change user
    if ( isset( $_POST['tp_change_user'] ) ) {
        $data2 = array( 
          'firstname' => isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '',
          'lastname' => isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '',
          'userlogin' => htmlspecialchars($_POST['userlogin']),
          'email' => htmlspecialchars($_POST['email'])
        );
        tp_students::delete_student_meta($user_ID);
        $rtn .= tp_students::change_student($user_ID, $data2, true);
        tp_db_helpers::prepare_meta_data( $user_ID, $fields, $_POST, 'students' );
    }
    // delete signup
    if ( isset( $_POST['austragen'] ) && $checkbox2 != '' ) {
        $rtn .= tp_enrollments::delete_signup($checkbox2);
    }
    // add signups
    if ( isset( $_POST['einschreiben'] ) && $checkbox != '' ) {
        $rtn .= tp_enrollments::add_signups($user_ID, $checkbox);
    }
    // add new user
    if ( isset( $_POST['tp_add_user'] ) ) {
        $rtn .= tp_enrollments::add_student( $user_ID, $user_login, $user_email, $fields, filter_input_array(INPUT_POST, $_POST) );
    } 

    /*
     * User tabs
    */
    $user_exists = false;
    if ( is_user_logged_in() ) {
       $user_exists = ( tp_students::is_student($user_ID) === false ) ? false : true;
       $rtn .= tp_enrollments::get_interface_for_users($user_ID, $user_login, $user_email, $user_exists, $tab);
    }
    
   /*
    * Enrollments
   */
   if ($tab === '' || $tab === 'current') {
       $rtn .= tp_enrollments::get_enrollments_tab($sem, $settings, $user_exists);
   }
   $rtn .= '</form>';
   $rtn .= '</div>';
   
   return $rtn;
}
