<?php 
/**
 * This file contains all functions for displaying the show_single_course page in admin menu
 * 
 * @package teachpress
 * @subpackage admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/** 
 * Single course overview
 * 
 * $_GET parameters:
 * @param int $course_id    course ID
 * @param string $sem       semester, from show_courses.php
 * @param string $search    search string, from show_courses.php
*/
function tp_show_single_course_page() {
    
    global $current_user;
    get_currentuserinfo();

    // form
    $checkbox = ( isset( $_POST['checkbox'] ) ) ?  $_POST['checkbox'] : '';
    $waiting = isset( $_POST['waiting'] ) ?  $_POST['waiting'] : '';
    $reg_action = isset( $_POST['reg_action'] ) ?  $_POST['reg_action'] : '';
    $course_id = intval($_GET['course_id']);
    
    $link_parameter['sem'] = htmlspecialchars($_GET['sem']);
    $link_parameter['redirect'] = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $link_parameter['sort'] = isset ( $_GET['sort'] ) ? $_GET['sort'] : 'asc';
    $link_parameter['search'] = htmlspecialchars($_GET['search']);
    $link_parameter['order'] = isset ( $_GET['order'] ) ? $_GET['order'] : 'name';
    $action = isset( $_GET['action'] ) ?  $_GET['action'] : 'show';
    
    // Get screen options
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $link_parameter['per_page'] = get_user_meta($current_user->ID, $screen_option, true);
    if ( empty ( $link_parameter['per_page'] ) || $link_parameter['per_page'] < 1 ) {
        $link_parameter['per_page'] = $screen->get_option( 'per_page', 'default' );
    }
    
    // Handle limits
    if ( isset($_GET['limit']) ) {
        $link_parameter['curr_page'] = intval($_GET['limit']);
        if ( $link_parameter['curr_page'] <= 0 ) {
            $link_parameter['curr_page'] = 1;
        }
        $link_parameter['entry_limit'] = ( $link_parameter['curr_page'] - 1 ) * $link_parameter['per_page'];
    }
    else {
        $link_parameter['entry_limit'] = 0;
        $link_parameter['curr_page'] = 1;
    }
    
    // course data
    $course_data = tp_courses::get_course($course_id, ARRAY_A);
    $parent = tp_courses::get_course($course_data["parent"], ARRAY_A);
    $capability = tp_courses::get_capability($course_id, $current_user->ID);

    echo '<div class="wrap">';
    tp_single_course_actions::do_actions($course_id, $_POST, $current_user, $waiting, $checkbox, $reg_action, $capability);
    
    echo '<form id="einzel" name="einzel" action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    echo '<input name="page" type="hidden" value="teachpress/teachpress.php">';
    echo '<input name="action" type="hidden" value="' . $action . '" />';
    echo '<input name="course_id" type="hidden" value="' . $course_id . '" />';
    echo '<input name="sem" type="hidden" value="' . $link_parameter['sem'] . '" />';
    echo '<input name="search" type="hidden" value="' . $link_parameter['search'] . '" />';
    echo '<input name="redirect" type="hidden" value="' . $link_parameter['redirect'] . '" />';
    echo '<input name="sort" type="hidden" value="' . $link_parameter['sort'] . '" />';
    echo '<input name="order" type="hidden" value="' . $link_parameter['order'] . '" />';
    
    echo tp_single_course_page::get_back_button($link_parameter);
    echo tp_single_course_page::get_course_headline($course_id, $course_data, $parent, $link_parameter, true);
    echo tp_single_course_page::get_menu($course_id, $link_parameter, $action, $capability);
    
    echo '<div style="width:100%; float:left; margin-top: 12px;">';
    
    // Show tab content
    if ( $action === 'assessments' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_assessments_tab($course_id, $link_parameter);
    }
    else if ( $action === 'add_assessments' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_add_assessments_tab($course_id, $link_parameter);
    }
    else if ( $action === 'enrollments' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_enrollments_tab($course_id, $course_data, $link_parameter, $reg_action, $checkbox, $waiting);
    }
    else if ( $action === 'capabilites' && $capability === 'owner' ) {
        tp_single_course_page::get_capability_tab($course_data);
    }
    else if ( $action === 'documents' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_documents_tab($course_id);
    }
    else {
        tp_single_course_page::get_info_tab($course_id, $course_data);
    }
    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
}

/**
 * This class contains all functions for single course actions like add an artefact, add a capability...
 * @package teachpress
 * @subpackage courses
 * @since 5.0.0
 */
class tp_single_course_actions {
    
    /**
     * Adds an artefact
     * @param int $course_id
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function add_artefact($course_id, $post) {
        $data = array('parent_id' => intval($post['artefact_parent']), 
                      'course_id' => $course_id, 
                      'title' => htmlspecialchars($post['artefact_name']), 
                      'scale' => '', 
                      'passed' => '', 
                      'max_value' => '');
        tp_artefacts::add_artefact($data);
        get_tp_message( __('Artefact added','teachpress') );
    }
    
    /**
     * Adds an assessment
     * @param int $course_id        The course ID
     * @param array $post           The $_POST array
     * @since 5.0.0
     * @access private
     */
    private static function add_assessment($course_id, $post) {
        $assessment_target = intval($post['assessment_target']);
        $assessment_passed = ( isset($post['assessment_passed']) ) ? 1 : 0;
        $artefact_id = ( $assessment_target !== 0 ) ? intval($post['assessment_target']) : NULL;
        $course = ( $assessment_target === 0 ) ? $course_id : NULL;
        $data = array('artefact_id' => $artefact_id, 
                      'course_id' => $course, 
                      'wp_id' => intval($post['assessment_participant']), 
                      'value' => htmlspecialchars($_POST['assessment_value']), 
                      'max_value' => '',
                      'type' => htmlspecialchars($_POST['assessment_value_type']),
                      'examiner_id' => get_current_user_id(),
                      'exam_date' => date('Y-m-d H:i:s'), 
                      'comment' => htmlspecialchars($_POST['assessment_comment']), 
                      'passed' =>  $assessment_passed );
        tp_assessments::add_assessment($data);
        get_tp_message( __('Assessment added','teachpress') );
    }
    
    /**
     * Adds a capability
     * @param int $course_id        The course ID
     * @param int $user_id          The user ID
     * @param array $post           The $_POST array
     * @since 5.0.0
     * @access private
     */
    private static function add_capability($course_id, $user_id, $post) {
        $cap_user = $post['cap_user'];
        if ( tp_courses::has_capability($course_id, $user_id, 'owner') ) {
            $ret = tp_courses::add_capability($course_id, $cap_user, 'approved');
            if ( $ret !== false ) {
                get_tp_message( __('Capability added','teachpress') );
            }
        }
        else {
            get_tp_message( __('Access denied','teachpress'), 'red' );
        }
    }
    
    /**
     * Adds multiple assessments
     * @param int $course_id        The course ID
     * @param array $post           The $_POST array
     * @since 5.0.0
     * @access private
     */
    private static function add_multiple_assessments($course_id, $post) {
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                    'course_id' => $course_id,
                                                    'order' => 'st.lastname ASC',
                                                    'waitinglist' => 0) );
        $assessment_target = intval($post['assessment_target']);
        $artefact_id = ( $assessment_target !== 0 ) ? intval($post['assessment_target']) : NULL;
        $course = ( $assessment_target === 0 ) ? $course_id : NULL;
        $exam_date = date('Y-m-d H:i:s');
        $examiner_id = get_current_user_id();
        foreach ( $students as $row ) {
            $result = isset ( $post['result_' . $row['wp_id']] ) ? htmlspecialchars($post['result_' . $row['wp_id']]) : '';
            $result_type = isset ( $post['result_type_' . $row['wp_id']] ) ? htmlspecialchars($post['result_type_' . $row['wp_id']]) : '';
            $result_comment = isset ( $post['result_comment_' . $row['wp_id']] ) ? htmlspecialchars($post['result_comment_' . $row['wp_id']]) : '';
            $result_check = isset ( $post['result_check_' . $row['wp_id']] ) ? 1 : 0;
            if ( $result === '' ) {
                continue;
            }
            $data = array('artefact_id' => $artefact_id, 
                      'course_id' => $course, 
                      'wp_id' => $row['wp_id'], 
                      'value' => $result, 
                      'max_value' => '',
                      'type' => $result_type,
                      'examiner_id' => $examiner_id, 
                      'exam_date' => $exam_date, 
                      'comment' => $result_comment, 
                      'passed' =>  $result_check );
            tp_assessments::add_assessment($data);
        }
        get_tp_message( __('Assessments added','teachpress') );
    }
    
    /**
     * Deletes an artefact
     * @param int $artefact_id
     * @since 5.0.0
     * @access private
     */
    private static function delete_artefact($artefact_id) {
        if ( tp_artefacts::has_assessments($artefact_id) === true ) {
            get_tp_message( __('Removing not possible. Delete the assessments first.','teachpress'), 'red' );
            return;
        }
        tp_artefacts::delete_artefact($artefact_id);
        get_tp_message( __('Removing successful','teachpress') );
    }
    
    /**
     * Deletes an assessment
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function delete_assessment($post) {
        $assessment_id = isset ( $post['tp_assessment_id'] ) ? intval($post['tp_assessment_id']) : 0;
        tp_assessments::delete_assessment($assessment_id);
        get_tp_message( __('Removing successful','teachpress') );
    }
    
    /**
     * Deletes signups
     * @param array $post
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     * @access private
     */
    private static function delete_signup($post, $checkbox, $waiting) {
        $move_up = isset( $post['move_up'] ) ? true : false;
        tp_courses::delete_signup($checkbox, $move_up);
        tp_courses::delete_signup($waiting, $move_up);
        get_tp_message( __('Removing successful','teachpress') );	
    }
    
    /**
     * Changes an artefact
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function change_artefact($post) {
        $artefact_id = isset( $post['tp_artefact_id'] ) ? intval($post['tp_artefact_id']) : 0;
        $artefact_title = isset( $post['tp_artefact_title'] ) ? htmlspecialchars($post['tp_artefact_title']) : '';
        if ( $artefact_id === 0 ) {
            return;
        }
        tp_artefacts::change_artefact_title($artefact_id, $artefact_title);
        get_tp_message( __('Saved') );
    }
    
    /**
     * Changes an assessment
     * @param array $post
     * @since 5.0.0
     * @access private
     */
    private static function change_assessment($post) {
        $assessment_id = isset ( $post['tp_assessment_id'] ) ? intval($post['tp_assessment_id']) : 0;
        $data = array('value' => isset ( $post['tp_value'] ) ? htmlspecialchars($post['tp_value']) : '', 
                      'type' => isset ( $post['tp_type'] ) ? htmlspecialchars($post['tp_type']) : '',
                      'examiner_id' => get_current_user_id(),
                      'exam_date' => date('Y-m-d H:i:s'), 
                      'comment' => isset ( $post['tp_comment'] ) ? htmlspecialchars($post['tp_comment']) : '', 
                      'passed' =>  isset ( $post['tp_passed'] ) ? htmlspecialchars($post['tp_passed']) : '' );
        if ( $assessment_id === 0 ) {
            return;
        }
        tp_assessments::change_assessment($assessment_id, $data);
        get_tp_message( __('Saved') );
    }
    
    /**
     * Moves a signup
     * @param array $post
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     * @access private
     */
    private static function move_signup($post, $checkbox, $waiting) {
        tp_courses::move_signup($checkbox, intval($post['tp_rel_course']) );
        tp_courses::move_signup($waiting, intval($post['tp_rel_course']) );
        get_tp_message( __('Participant moved','teachpress') );
    }

    /**
     * Handles all database actions for the single course page
     * @param int $course_id
     * @param array $post
     * @param array $current_user
     * @param array $waiting
     * @param array $checkbox
     * @param string $reg_action
     * @param string $capability
     */
    public static function do_actions($course_id, $post, $current_user, $waiting, $checkbox, $reg_action, $capability) {
        // change signup
        if ( $reg_action == 'signup' && ( $capability === 'owner' || $capability === 'approved' ) ) {
            tp_courses::change_signup_status($waiting, 'course');
            get_tp_message( __('Participant added','teachpress') );
        }
        if ( $reg_action == 'signout' && ( $capability === 'owner' || $capability === 'approved' ) ) {
            tp_courses::change_signup_status($checkbox, 'waitinglist');
            get_tp_message( __('Participant moved','teachpress') );
        }
        // add signup
        if ( isset( $post['add_signup'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            tp_courses::add_signup($post['tp_add_reg_student'], $course_id);
            get_tp_message( __('Participant added','teachpress') );
        }
        // move signup
        if ( isset( $post['move_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::move_signup($post, $checkbox, $waiting);
        }
        // Delete functions
        if ( isset( $post['delete_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_signup($post, $checkbox, $waiting);
        }
        // Add artefact
        if ( isset( $post['add_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::add_artefact($course_id, $post);
        }
        // Edit artefact
        if ( isset( $post['tp_save_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::change_artefact($post);
        }
        // Delete artefact
        if ( isset( $_GET['delete_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_artefact($_GET['delete_artefact']);
            
        }
        // Add assessment
        if ( isset( $post['add_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::add_assessment($course_id, $post);
        }
        // Edit assessment
        if ( isset( $post['tp_save_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::change_assessment($post);
        }
        // Delete assessment
        if ( isset( $post['tp_delete_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::delete_assessment($post);
        }
        // Ass multiple assessments
        if ( isset( $post['add_multiple_assessments'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
            self::add_multiple_assessments($course_id, $post);
        }
        // Add capability
        if ( isset( $post['cap_submit'] ) ) {
            self::add_capability($course_id, $current_user->ID, $post);
        }
    }
}

/**
 * This class contains function for generating the single_course admin pages
 * @since 5.0.0
 */
class tp_single_course_page {
    
    /**
     * Shows the add_artefact_form for show_single_course page
     * @since 5.0.0
     */
    public static function get_artefact_form() {
        echo '<div id="tp_add_artefact_form" class="teachpress_message" style="display:none;">';
        echo '<p class="teachpress_message_headline">' . __('Add artefact','teachpress') . '</p>';

        echo '<p><label for="artefact_name">' . __('Title','teachpress') . '</label></p>';
        echo '<input name="artefact_name" id="artefact_name" type="text" style="width:50%;"/>';
        
        echo '<input name="artefact_parent" type="hidden" value="0"/>';

        echo '<p><input name="add_artefact" type="submit" class="button-primary" value="' . __('Add','teachpress') . '"/> <a onclick="teachpress_showhide(' . "'tp_add_artefact_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachpress') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Shows the add_assessment form for show_single_course page
     * @param int $course_id
     * @since 5.0.0
     */
    public static function get_assessment_form($course_id) {
        echo '<div id="tp_add_assessment_form" class="teachpress_message" style="display:none;">';
        echo '<p class="teachpress_message_headline">' . __('Add assessment','teachpress') . '</p>';

        echo '<p><label for="assessment_participant">' . __('Participant','teachpress') . '</label></p>';
        echo '<select name="assessment_participant" id="assessment_participant">';
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                   'course_id' => $course_id,
                                                   'order' => 'st.lastname ASC',
                                                   'waitinglist' => 0) );
        foreach ( $students as $stud ) {
            echo '<option value="' . $stud['wp_id'] . '">' . stripslashes($stud['lastname']) . ', ' . stripslashes($stud['firstname']) . '</option>';
        }
        echo '</select>';

        echo '<p><label for="assessment_value">' . __('Value/Grade','teachpress') . '</label></p>';
        echo '<input name="assessment_value" id="assessment_value" type="text" style="width:100px;"/>';
        echo tp_admin::get_assessment_type_field('assessment_value_type', '');
        echo '<input type="checkbox" name="assessment_passed" id="assessment_passed" value="1"/> <label for="assessment_passed">' . __('Participant has passed','teachpress') . '</label>';

        echo '<p><label for="assessment_target">' . __('Assessment for','teachpress') . '</label></p>';
        echo '<select name="assessment_target" id="assessment_target">';
            echo '<option value="0">' . __('Complete Course','teachpress') . '</option>';
            $artefacts = tp_artefacts::get_artefacts($course_id, 0);
            foreach ( $artefacts as $row ) {
                echo '<option value="' . $row['artefact_id'] . '">' . stripslashes($row['title']) . '</option>';
            }
        echo '</select>';

        echo '<p><label for="assessment_comment">' . __('Comment','teachpress') . '</label></p>';
        echo '<textarea name="assessment_comment" id="assessment_comment" style="width:50%; height:50px;"></textarea>';

        echo '<p><input name="add_assessment" type="submit" class="button-primary" value="' . __('Add','teachpress') . '"/> <a onclick="teachpress_showhide(' . "'tp_add_assessment_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachpress') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Return the back_to button
     * @param array $link_parameter
     * @return string
     * @since 5.0.0
     */
    public static function get_back_button ($link_parameter){
        $save = isset( $_POST['save'] ) ?  $_POST['save'] : '';
        if ( $save == __('Save') ) {
            return;
        }
        if ( $link_parameter['redirect'] != 0 ) {
            return '<p><a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $link_parameter['redirect'] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
        }
        else {
             return '<p><a href="admin.php?page=teachpress/teachpress.php&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
        }
        
    }

    /**
     * Returns the page headline
     * @param int $course_id
     * @param array $course_data
     * @param array $parent_data
     * @param array $link_parameter
     * @param string $edit_link
     * @return string
     * @since 5.0.0
     */
    public static function get_course_headline($course_id, $course_data, $parent_data, $link_parameter, $edit_link = true) {
        $link = '';
        $parent_name = '';
        
        if ($course_data["parent"] != 0) {
            if ($parent_data["course_id"] == $course_data["parent"]) {
                $parent_name = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $parent_data["course_id"] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show&amp;redirect=' . $course_id . '" title="' . stripslashes($parent_data["name"]) . '" style="color:#464646">' . stripslashes($parent_data["name"]) . '</a> &rarr; ';
            }
        }
        
        if ( $edit_link === true ) {
            $link = '<small><a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=edit" class="teachpress_link" style="cursor:pointer;">' . __('Edit','teachpress') . '</a></small>';
        }

        return '<h2 style="padding-top:5px;">' . $parent_name . stripslashes($course_data["name"]) . ' ' . $course_data["semester"] . ' <span class="tp_break">|</span> ' . $link . '</h2>';
    }
    
    /**
     * Returns the page menu
     * @param int $course_id
     * @param array $link_parameter
     * @param string $action
     * @param strin $capability
     * @return string
     * @since 5.0.0
     */
    public static function get_menu($course_id, $link_parameter, $action, $capability){
        $enrollments_tab = '';
        $assessment_tab = '';
        $capability_tab = '';
        $documents_tab = '';
        
        $set_info_tab = ( $action === 'show' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $info_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="' . $set_info_tab . '">' . __('Info','teachpress') . '</a> ';
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_documents_tab = ( $action === 'documents' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $documents_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=documents" class="' . $set_documents_tab . '">' . __('Documents','teachpress') . '</a> ';
        }
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_enrollments_tab = ( $action === 'enrollments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $enrollments_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=enrollments" class="' . $set_enrollments_tab . '">' . __('Enrollments','teachpress') . '</a> ';
        }
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_assessment_tab = ( $action === 'assessments' || $action === 'add_assessments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $assessment_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" class="' . $set_assessment_tab . '">' . __('Assessments','teachpress') . '</a> ';
        }
        
        if ( $capability === 'owner' ) {
            $set_capability_tab = ( $action === 'capabilites' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $capability_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=capabilites" class="' . $set_capability_tab . '">' . __('Capabilites','teachpress') . '</a> ';
        }
        
        return '<h3 class="nav-tab-wrapper">' . $info_tab . $documents_tab . $enrollments_tab. $assessment_tab . $capability_tab . '</h3>';
    }
    
    /**
     * Gets the add_students_form for the enrollments tab
     * @since 5.0.0
     * @access private
     */
    private static function get_add_students_form() {
        echo '<div class="teachpress_message" id="tp_add_signup_form" style="display: none;">';
        echo '<p class="teachpress_message_headline">' . __('Add students manually','teachpress') . '</p>';
        echo '<select name="tp_add_reg_student" id="tp_add_reg_student">';
        echo '<option value="0">- ' . __('Select student','teachpress') . ' -</option>';
        $row1 = tp_students::get_students();
        $zahl = 0;
        $notice = array();
        foreach($row1 as $row1) {
            if ($zahl != 0 && $notice[0] != $row1->lastname[0]) {
                echo '<option>----------</option>';
            }
            echo '<option value="' . $row1->wp_id . '">' . stripslashes($row1->lastname) . ', ' . stripslashes($row1->firstname) . ' (' . $row1->userlogin . ')</option>';
            $notice = $row1->lastname;
            $zahl++;
        }
        echo '</select>';
        echo '<p>
               <input type="submit" name="add_signup" class="button-primary" value="' . __('Add', 'teachpress') . '" />
               <a onclick="teachpress_showhide(' . "'" . 'tp_add_signup_form' . "'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachpress') . '</a>
             </p>';
        echo '</div>';   
    }
    
    /**
     * Gets the move_to_a_course_form for the enrollments tab
     * @param int $course_id            The ID of the course
     * @param array $cours_data         An associative array of the course_data
     * @param array $link_parameter     An associative array of link parameters
     * @since 5.0.0
     * @access private
     */
    private static function get_move_to_a_course_form($course_id, $cours_data, $link_parameter) {
        $p = $cours_data['parent'] != 0 ? $cours_data['parent'] : $cours_data['course_id'];
        $related_courses = tp_courses::get_courses( array('parent' => $p ) );
        if ( count($related_courses) === 0 ) {
            get_tp_message(__('Error: There are no related courses.','teachpress'));
            return;
        }
        echo '<div class="teachpress_message" id="tp_move_to_course">';
        echo '<p class="teachpress_message_headline">' . __('Move to a related course','teachpress') . '</p>';
        echo '<p>' . __('If you move a signup to an other course the signup status will be not changed. So a waitinglist will be a waitinglist entry.','teachpress') . '</p>';
        echo '<select name="tp_rel_course" id="tp_rel_course">';
        foreach ( $related_courses as $rel ) {
            $selected = $rel->course_id == $cours_data['course_id'] ? ' selected="selected"' : '';
            echo '<option value="' . $rel->course_id . '"' . $selected . '>' . $rel->course_id . ' - ' . $rel->name . '</option>';
        }
        echo ' </select>';
        echo '<p><input name="move_ok" type="submit" class="button-primary" value="' . __('Move','teachpress') . '"/>
                    <a href="admin.php?page=teachpress/teachpress.php&course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;order=' . $link_parameter['order'] . '&amp;sort=' . $link_parameter['sort'] . '&amp;action=enrollments" class="button-secondary">' . __('Cancel','teachpress') . '</a></p>';    
        echo '</div>';
    }

    /**
     * Shows the add_assessment tab
     * @param int $course_id            The course ID
     * @param array $link_parameter     The url link_parameter array. Used keys: sem, search
     * @since 5.0.0
     */
    public static function get_add_assessments_tab($course_id, $link_parameter) {
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                   'course_id' => $course_id,
                                                   'order' => 'st.lastname ASC',
                                                   'waitinglist' => 0) );
        echo '<div class="tp_actions">';
        echo '<span style="font-size: 1.1em; font-weight:bold;">' . __('Add assessments for','teachpress') . '</span> ';
        echo '<select name="assessment_target" id="assessment_target">';
        echo '<option value="0">' . __('Complete Course','teachpress') . '</option>';
        $artefacts = tp_artefacts::get_artefacts($course_id, 0);
        foreach ( $artefacts as $row ) {
            echo '<option value="' . $row['artefact_id'] . '">' . stripslashes($row['title']) . '</option>';
        }
        echo '</select> ';
        echo '<input name="add_multiple_assessments" type="submit" class="button-primary" value="' . __('Save') . '"/> ';
        echo '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" class="button-secondary">'. __('Cancel') . '</a>';
        echo '</div>';
        echo '<table id="tp_add_assessments" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th></th>';
        echo '<th>' . __('Last name','teachpress') . '</th>';
        echo '<th>' . __('First name','teachpress') . '</th>';
        echo '<th>' . __('Result','teachpress') . '</th>';
        echo '<th>' . __('Type') . '</th>';
        echo '<th>' . __('Comment','teachpress') . '</th>';
        echo '<th>' . __('Has passed','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $pos = 1;
        $class_alternate = true;
        foreach ( $students as $stud ) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>';
            echo '<td></td>';
            echo '<td>' . stripslashes($stud['lastname']) . '</td>';
            echo '<td>' . stripslashes($stud['firstname']) . '</td>';
            echo '<td><input name="result_' . $stud['wp_id'] . '" type="text" size="10" tabindex="' . $pos . '" /></td>';
            $pos++;
            echo '<td>';
            echo tp_admin::get_assessment_type_field('result_type_' . $stud['wp_id'], '', $pos);
            echo '</td>';
            $pos++;
            echo '<td><textarea name="result_comment_' . $stud['wp_id'] . '" rows="3" cols="40" tabindex="' . $pos . '"></textarea></td>';
            $pos++;
            echo '<td><input name="result_check_' . $stud['wp_id'] . '" type="checkbox" tabindex="' . $pos . '"/></td>';
            echo '</tr>';
            $pos++;
        }
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Gets a row for the main table of get_assessments_tab() called gradebook
     * @param int $user_id          The user ID
     * @param string $artefact_id   A string of artefact IDs separated by comma
     * @param int $course_id        The course ID
     * @since 5.0.0
     * @access private
     */
    private static function get_assessment_row($user_id, $artefact_id, $course_id){
        $assessments = tp_assessments::get_assessments($user_id, $artefact_id, $course_id);
        echo '<td>';
        foreach ( $assessments as $single_assessment ) {
            $class = '';
            $passed_icon = '';
            
            // if user has a positive assessment (has passed)
            if ( $single_assessment['passed'] == 1 ) {
                $class .= 'tp_assessment_passed';
                $passed_icon = '&#10003;';
            }
            else {
                $passed_icon = '&#10007';
            }
            
            // if there is a commt for the assessment
            if ( $single_assessment['comment'] != '' ) {
                $class .= ' tp_assessment_comment';
            }
            
            echo '<a href="' . plugins_url() . '/teachpress/ajax.php?assessment_id=' . $single_assessment['assessment_id'] . '" title="' . __('Edit Assessment','teachpress') . '" class="tp_assessment ' . $class . '" id="tp_assessment_' . $single_assessment['assessment_id'] . '">' . $single_assessment['value'] . '</a>' . $passed_icon;
        }
        echo '</td>';
    }

    /**
     * Shows the course assessment tab
     * @param int $course_id
     * @param array $link_parameter
     * @since 5.0.0
     */
    public static function get_assessments_tab($course_id, $link_parameter) {
        $local_search = isset ( $_POST['local_search'] ) ? esc_html($_POST['local_search']) : '';
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                   'course_id' => $course_id,
                                                   'search' => $local_search,
                                                   'limit' => $link_parameter['entry_limit'] . ',' . $link_parameter['per_page'],
                                                   'order' => 'st.lastname ASC',
                                                   'waitinglist' => 0) );
        $count_students = tp_courses::get_signups( array('count' => true, 
                                                   'course_id' => $course_id,
                                                   'search' => $local_search,
                                                   'waitinglist' => 0) );
        // Searchbox
        echo '<div id="searchbox" style="float:right; padding-bottom:5px;">';
        if ( $local_search != "" ) { 
            echo '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="' . __('Cancel the search','teachpress') . '">X</a>';
        }
        echo '<input type="search" name="local_search" id="pub_search_field" style="margin-right:5px;" value="' . stripslashes($local_search) . '"/>';
        echo '<input type="submit" name="pub_search_button" id="pub_search_button" value="' . __('Search','teachpress') . '" class="button-secondary"/>';
        echo '</div>';
        // Menu
        echo '<div class="tp_actions">';
        echo '<span style="margin-right:15px;"><a onclick="teachpress_showhide(' . "'tp_add_artefact_form'" . ');" id="teachpress_add_artefact" class="button-secondary" style="cursor:pointer;">' . __('Add artefact','teachpress') . '</a></span> ';
        echo '<span style="margin-right:15px;"><a onclick="teachpress_showhide(' . "'tp_add_assessment_form'" . ');" style="cursor:pointer;" id="teachpress_add_assessment" class="button-secondary">' . __('Add single assessment','teachpress') . '</a></span> ';
        echo '<span style="margin-right:15px;"><a href="admin.php?page=teachpress/teachpress.php&course_id=' . $course_id . '&sem=' . $link_parameter['sem'] . '&search=' . $link_parameter['search'] . '&action=add_assessments" style="cursor:pointer;" id="teachpress_add_assessment" class="button-secondary">' . __('Add a set of assessments','teachpress') . '</a></span> ';
        echo '</div>';
        // Delete artefact
        if ( isset( $_POST['tp_delete_artefact'] ) ) {
            $tp_artefact_id = ( isset($_POST['tp_artefact_id']) ) ? intval($_POST['tp_artefact_id']) : 0;
            echo '<div class="teachpress_message" teachpress_message_orange">';
            echo '<p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>';
            echo '<p><a href="admin.php?page=teachpress/teachpress.php&course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments&amp;delete_artefact=' . $tp_artefact_id . '" class="button-primary">' . __('Delete','teachpress') . '</a> ';
            echo '<a href="admin.php?page=teachpress/teachpress.php&course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" class="button-secondary">' . __('Cancel','teachpress') . '</a></p>';
            echo '</div>';
        }
        tp_single_course_page::get_artefact_form();
        tp_single_course_page::get_assessment_form($course_id);
        $args = array('number_entries' => $count_students,
                      'entries_per_page' => $link_parameter['per_page'],
                      'current_page' => $link_parameter['curr_page'],
                      'entry_limit' => $link_parameter['entry_limit'],
                      'page_link' => 'admin.php?page=teachpress/teachpress.php&amp;',
                      'link_attributes' => 'course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;order=' . $link_parameter['order'] . '&amp;sort=' . $link_parameter['sort'] . '&amp;action=assessments',
                      'mode' => 'top',
                      'class' => 'tablenav-pages',
                      'before' => '<div class="tablenav" style="float:right;">',
                      'after' => '</div>');
        echo tp_page_menu($args);
        echo '<h3>' . __('Assessments','teachpress') . '</h3>';
        // Gradebook
        echo '<table id="tp_assessment_overview" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"></th>';
        echo '<th>' . __('Last name','teachpress') . '</th>';
        echo '<th>' . __('First name','teachpress') . '</th>';
        $artefacts = tp_artefacts::get_artefacts($course_id, 0);
        foreach ( $artefacts as $row ) {
            echo '<th><a href="' . plugins_url() . '/teachpress/ajax.php?artefact_id=' . $row['artefact_id'] . '" class="tp_edit_artefact" title="' . __('Edit Artefact','teachpress') . '">' . stripslashes($row['title']) . '</a></th>';
        }
        echo '<th>' . __('Course','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $pos = 1;
        $class_alternate = true;
        
        // if there is no search result found
        if ( $local_search !== '' && $count_students == 0 ) {
            echo '<tr>';
            echo '<td></td>';
            echo '<td colspan="' . ( count($artefacts) + 3 ) . '"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td>';
            echo '</tr>';
        }
        
        foreach ( $students as $stud ) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>';
            echo '<td></td>';
            echo '<td>' . stripslashes($stud['lastname']) . '</td>';
            echo '<td>' . stripslashes($stud['firstname']) . '</td>';
            // Get assessments for each artefact
            foreach ( $artefacts as $row ) {
                self::get_assessment_row($stud['wp_id'], $row['artefact_id'], 0);
            }
            // Get final course assessment
            self::get_assessment_row($stud['wp_id'], '', $course_id);
            echo '</tr>';
            $pos++;
        }
        echo '</tbody>';
        echo '</table>';
        $args['mode'] = 'bottom';
        echo tp_page_menu($args);
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($){
                $(".tp_assessment").each(function() {
                    var $link = $(this);
                    var $dialog = $('<div></div>')
                        .load($link.attr('href') + ' #content')
                        .dialog({
                                autoOpen: false,
                                title: '<?php _e('Edit Assessment','teachpress'); ?>',
                                width: 600
                        });

                    $link.click(function() {
                        $dialog.dialog('open');
                        return false;
                    });
                });
                $(".tp_edit_artefact").each(function() {
                    var $link = $(this);
                    var $dialog = $('<div></div>')
                        .load($link.attr('href') + ' #content')
                        .dialog({
                                autoOpen: false,
                                title: '<?php _e('Edit Artefact','teachpress'); ?>',
                                width: 600
                        });

                    $link.click(function() {
                        $dialog.dialog('open');
                        return false;
                    });
                });
            });
        </script>
        <?php
    }
    
    /**
     * Shows the capabilites tab for show_single_course page
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_capability_tab ($course_data) {
        if ( $course_data['use_capabilites'] != 1 ) {
            get_tp_message( __("You can't set user capabilites here, because you are using global capabilites for this course.",'teachpress'), 'orange' );
            return;
        }
        echo '<div class="tp_actions"><a id="teachpress_add_capability" class="button-secondary" onclick="javascript:teachpress_showhide(' . "'add_capability'" .');">' . __('Add new','teachpress') . '</a></div>';
        echo '<div id="add_capability" class="teachpress_message" style="display:none;">';
        echo '<form name="add_cap" method=""post>';
        echo '<p class="teachpress_message_headline">' . __('Add capability for user','teachpress') . '</p>';
        echo '<label for="cap_user">' . __('Username', 'teachpress') . '</label> ';
        echo '<select id="cap_user" name="cap_user">';
        echo '<option>- ' . __('Select user','teachpress') . ' -</option>';
        $capabilites = tp_courses::get_capabilities($course_data['course_id']);
        $users = get_users();
        $array_caps = array();
        foreach ($capabilites as $row) {
            array_push($array_caps, $row['wp_id']);
        }
        foreach ($users as $user) {
            if (!in_array($user->ID, $array_caps) && user_can( $user->ID, 'use_teachpress_courses' )  ) {
                echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
            }
        }
        echo '</select>';
        echo '<p><input name="cap_submit" type="submit" class="button-primary" value="' . __('Add','teachpress') . '" /> <a class="button-secondary" onclick="javascript:teachpress_showhide(' . "'add_capability'" .');">' . __('Cancel','teachpress') . '</a></p>';
        echo '</form>';
        echo '</div>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"></th>';
        echo '<th>' . __('Username') . '</th>';
        echo '<th>' . __('Name','teachpress') . '</th>';
        echo '<th>' . __('Capability','teachpress') . '</th>';
        echo '</thead>';
        echo '</tr>';
        echo '<tbody>';
        $class_alternate = true;
        foreach ( $capabilites as $row ) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            $user = get_userdata( $row['wp_id'] );
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column"></th>';
            echo '<td>';
            echo '<span style="float:left; margin-right:10px;">' . get_avatar($row['wp_id'], 35) . '</span> <strong>' . $user->user_login . '</strong>';
            if ( $row['capability'] !== 'owner' ) {
                echo '<div class="tp_row_actions"><a class="tp_row_delete" href="admin.php?page=teachpress/teachpress.php&course_id=6&sem=Example%20term&search=&action=capabilites" style="color:#a00;" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a></div>';
            }
            echo '</td>';
            echo '<td>' . $user->display_name . '</td>';
            echo '<td>' . $row['capability'] . '</td>';
            echo '<tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Shows the info tab for show_single_course page
     * @param int $course_id    The ID of the course
     * @param array $cours_data An associative array with course data
     * @since 5.0.0
     */
    public static function get_info_tab ($course_id, $cours_data) {
        $fields = get_tp_options('teachpress_courses','`setting_id` ASC', ARRAY_A);
        $course_meta = tp_courses::get_course_meta($course_id);
        ?>
        <div style="width:24%; float:right; padding-left:1%; padding-bottom:1%;">
         <div class="postbox">
             <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('Enrollments','teachpress'); ?></span></h3>
             <div class="inside">
                  <table cellpadding="8">
                    <?php 
                    if ($cours_data["start"] != '0000-00-00 00:00:00' && $cours_data["end"] != '0000-00-00 00:00:00') {
                        echo '<tr>';
                        echo '<td colspan="2"><strong>' . __('Start','teachpress') . '</strong></td>';
                        echo '<td colspan="2">' . substr($cours_data["start"], 0, strlen( $cours_data["start"] ) - 3 ) . '</td>';
                        echo '</tr> ';
                        
                        echo '<tr>';
                        echo ' <td colspan="2"><strong>' . __('End','teachpress') . '</strong></td>';
                        echo '<td colspan="2">' . substr($cours_data["end"], 0, strlen( $cours_data["end"] ) - 3 ) . '</td>';
                        echo '</tr>';
                        
                        $free_places = tp_courses::get_free_places($cours_data["course_id"], $cours_data["places"]);
                        $style = ( $free_places < 0 ) ? ' style="color:#ff6600; font-weight:bold;"' : '';
                        echo '<tr>';
                        echo '<td><strong>' . __('Places','teachpress') . '</strong></th>';
                        echo '<td>' . $cours_data["places"] . '</td>';
                        echo '<td><strong>' . __('free places','teachpress') . '</strong></td>';
                        echo '<td ' . $style . '>' . $free_places . '</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr>';
                        echo '<td colspan="4">' . __('none','teachpress') . '</td>';
                        echo '</tr>';
                    } ?>  
                  </table>
             </div>
         </div>
       </div>
       <div style="width:75%; float:left; padding-bottom:10px;">
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('General','teachpress'); ?></span></h3>
               <div class="inside">
                    <table cellpadding="8">
                      <tr>
                        <td width="230"><strong><?php _e('ID'); ?></strong></td>
                        <td><?php echo $cours_data["course_id"]; ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Type'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["type"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Visibility','teachpress'); ?></strong></td>
                        <td>
                         <?php 
                            if ( $cours_data["visible"] == 1 ) {
                                 _e('normal','teachpress');
                            }
                            elseif ( $cours_data["visible"] == 2 ) {
                                 _e('extend','teachpress');
                            }
                            else {
                                 _e('invisible','teachpress');
                            } 
                         ?></td> 
                      </tr>
                      <tr>
                        <td><strong><?php _e('Date','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["date"]); ?></td>
                      </tr>
                      <tr>
                          <td><strong><?php _e('Room','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["room"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Lecturer','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["lecturer"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Comment','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["comment"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Related content','teachpress'); ?></strong></td>
                        <td><?php 
                            if ( $cours_data["rel_page"] != 0) {
                                echo '<a href="' . get_permalink( $cours_data["rel_page"] ) . '" target="_blank" class="teachpress_link">' . get_permalink( $cours_data["rel_page"] ) . '</a>';
                            }
                            else { 
                                _e('none','teachpress');
                            } ?></td>
                      </tr>
                </table>
               </div>
           </div>
           <?php if ( count($course_meta) > 0 ) { ?>
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('Custom meta data','teachpress'); ?></span></h3>
               <div class="inside">
                   <table cellpadding="8">
                    <?php
                    foreach ($fields as $row) {
                        $col_data = tp_db_helpers::extract_column_data($row['value']);
                        $value = '';
                        foreach ( $course_meta as $row_meta ) {
                            if ( $row['variable'] === $row_meta['meta_key'] ) {
                                $value = $row_meta['meta_value'];
                                break;
                            }
                        }
                        echo '<tr>
                               <td width="230"><strong>' . stripslashes($col_data['title']) . '</strong></td>
                               <td> ' . stripslashes($value) . '</td>
                             </tr>';
                     }
                    ?>
                   </table>
               </div>
           </div>
           <?php
           }
           ?>
       </div>
    <?php
    }
    
    /**
     * Gets a row for the enrollments table of get_enrollments_tab()
     * @param int $course_id            The course ID
     * @param array $enrollments        An associative array with data of enrollments
     * @param array $link_parameter     An associative array with link parameter (sem, search, ...)
     * @param array $checkbox           The checkbox array
     * @param array $visible_fields     An array of visible fields
     * @param string $reg_action        The reg_action string (delete, move,...)
     * @param string $checkbox_name     The name of the checkbox in the first column
     * @since 5.0.0
     * @access private
     */
    private static function get_enrollments_rows($course_id, $enrollments, $link_parameter, $checkbox, $visible_fields, $reg_action, $checkbox_name){
        $class_alternate = true;
        foreach ($enrollments as $enrollments) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>';
            $checked = '';
            if ( ( $reg_action === "delete" || $reg_action === 'move' ) && is_array($checkbox) ) {
                $max = count( $checkbox );
                for( $k = 0; $k < $max; $k++ ) {
                    if ( $enrollments["con_id"] == $checkbox[$k] ) { 
                        $checked = 'checked="checked" ';
                        break;
                    }
                }
            }
            echo '<th class="check-column"><input name="' . $checkbox_name . '[]" type="checkbox" value="' . $enrollments["con_id"] . '" ' . $checked . '/></th>';
            echo '<td>' . stripslashes($enrollments["lastname"]) . '</td>';
            echo '<td>' . stripslashes($enrollments["firstname"]) . '</td>';
            echo '<td>' . stripslashes($enrollments["userlogin"]) . '</td>';
            echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=mail&amp;single=' . stripslashes($enrollments["email"]) . '" title="' . __('send E-Mail','teachpress') . '">' . stripslashes($enrollments["email"]) . '</a></td>';
            $max = count($visible_fields);
            for ($i = 0; $i < $max; $i++) {
                echo '<td>' . $enrollments[$visible_fields[$i]] . '</td>';
            }
            echo '<td>' . $enrollments["date"] . '</td>';
            echo '</tr>';
        } 
    }
    
    /**
     * Shows the enrollments tab for show_single_course page
     * @param int $course_id            The ID of the course
     * @param array_a $course_data      An associative array with course data
     * @param array_a $link_parameter   This includes the following: order, sort, entry_limit
     * @param string $reg_action        move or delete
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     */
    public static function get_enrollments_tab($course_id, $course_data, $link_parameter, $reg_action, $checkbox, $waiting) {
        // field options
        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        $visible_fields = array();
        foreach ($fields as $row) {
            $data = tp_db_helpers::extract_column_data($row->value);
            if ( $data['visibility'] === 'admin') {
                array_push($visible_fields, $row->variable);
            }
        }

        // sort and order of signups
        $order_s = ( $link_parameter['order'] === 'name' ) ? 'st.lastname' : 's.date';
        $sort_s = ( $link_parameter['sort'] === 'asc' ) ? ' ASC' : ' DESC';

        // enrollments / signups
        $enrollments = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                      'course_id' => $course_id, 
                                                      'order' => $order_s . $sort_s, 
                                                      'limit' => $link_parameter['entry_limit'] . ',' . $link_parameter['per_page'],
                                                      'waitinglist' => 0) );
        $count_enrollments = count( tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                      'course_id' => $course_id, 
                                                      'order' => $order_s . $sort_s,
                                                      'waitinglist' => 0) ) );

        // waitinglist
        $waitinglist = tp_courses::get_signups( array('output_type' => ARRAY_A, 
                                                      'course_id' => $course_id, 
                                                      'order' => $order_s . $sort_s, 
                                                      'waitinglist' => 1) );
        $count_waitinglist = count($waitinglist);

        ?>
       <!-- Menu -->
       <div class="tp_actions">
            <span style="margin-right:15px;">
            <select name="reg_action">
                <option value="0">- <?php _e('Bulk actions','teachpress'); ?> -</option>
                <option value="signout"><?php _e('Move to waitinglist','teachpress'); ?></option>
                <option value="signup"><?php _e('Move to course','teachpress'); ?></option>
                <option value="move"><?php _e('Move to a related course','teachpress'); ?></option>
                <option value="delete"><?php _e('Delete','teachpress'); ?></option>
            </select>
            <input name="tp_submit" type="submit" class="button-secondary" value="<?php _e('OK', 'teachpress'); ?>"/>
            </span>
           <span style="margin-right:15px;">
            <a id="teachpress_add_signup" style="cursor:pointer;" class="button-secondary" onclick="teachpress_showhide('tp_add_signup_form');" title="<?php _e('Add signup','teachpress'); ?>"><?php _e('Add signup','teachpress'); ?></a>
            <a id="teachpress_create_list" href="admin.php?page=teachpress/teachpress.php&amp;course_id=<?php echo $course_id; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;redirect=<?php echo $link_parameter['redirect']; ?>&amp;action=list" class="button-secondary" title="<?php _e('Attendance list','teachpress'); ?>"><?php _e('Attendance list','teachpress'); ?></a>
           </span>
           <span style="margin-right:15px;">
            <a id="teachpress_create_csv" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_id=<?php echo $course_id; ?>&amp;type=csv" title="<?php _e('CSV export','teachpress'); ?>">CSV</a>
            <a id="teachpress_create_xls" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_id=<?php echo $course_id; ?>&amp;type=xls" title="<?php _e('XLS export','teachpress'); ?>">XLS</a>
           </span>
           <a id="teachpress_send_mail" class="button-secondary" href="admin.php?page=teachpress/teachpress.php&amp;course_id=<?php echo $course_id; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;redirect=<?php echo $link_parameter['redirect']; ?>&amp;action=mail&amp;type=course" title="<?php _e('Send E-Mail','teachpress'); ?>"><?php _e('Send E-Mail','teachpress'); ?></a>
       </div>
       <?php
       // Add students
       tp_single_course_page::get_add_students_form();
       // Move to a course
       if ( $reg_action === 'move' ) {
            tp_single_course_page::get_move_to_a_course_form($course_id, $course_data , $link_parameter);
       }
       // Delete entries
       if ( $reg_action == 'delete' ) { 
           echo '<div class="teachpress_message" teachpress_message_orange">';
           echo '<p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>';
           echo '<p><input type="checkbox" name="move_up" id="move_up" checked="checked" /> <label for="move_up">' . __('Move up entries from the waitinglist as replacement for deleted signups.','teachpress') . '</label></p>';
           echo '<p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/> ';
           echo '<a href="admin.php?page=teachpress/teachpress.php&course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;order=' . $link_parameter['order'] . '&amp;sort=' .$link_parameter['sort'] . '&amp;action=enrollments" class="button-secondary">' . __('Cancel','teachpress') . '</a></p>';
           echo '</div>';
        }
        
        $args = array('number_entries' => $count_enrollments,
                      'entries_per_page' => $link_parameter['per_page'],
                      'current_page' => $link_parameter['curr_page'],
                      'entry_limit' => $link_parameter['entry_limit'],
                      'page_link' => 'admin.php?page=teachpress/teachpress.php&amp;',
                      'link_attributes' => 'course_id=' . $course_id . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;order=' . $link_parameter['order'] . '&amp;sort=' . $link_parameter['sort'] . '&amp;action=enrollments',
                      'mode' => 'top',
                      'class' => 'tablenav-pages',
                      'before' => '<div class="tablenav" style="float:right;">',
                      'after' => '</div>');
        echo tp_page_menu($args);
       ?>
        <!-- END Menu --> 
        <h3><?php _e('Signups','teachpress'); ?></h3>
        <table class="widefat">
        <thead>
         <tr>
           <th class="check-column">
            <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" />
           </th>
           <?php
           // Order option parameter
           if ( $link_parameter['order'] == 'name' ) {
               $display_date = 'none';
               $display_name = 'inline';
               $sort_date = ( $link_parameter['sort'] === 'asc' ) ? 'asc' : 'desc';
               $sort_name = ( $link_parameter['sort'] === 'desc' ) ? 'asc' : 'desc';
               $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
               $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           }
           else {
               $display_date = 'inline';
               $display_name = 'none';
               $sort_date = ( $link_parameter['sort'] === 'asc' ) ? 'desc' : 'asc';
               $sort_name = ( $link_parameter['sort'] === 'desc' ) ? 'asc' : 'desc';
               $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
               $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           }
           ?>
           <th><a href="admin.php?page=teachpress/teachpress.php&course_id=<?php echo $course_id; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=name&amp;sort=<?php echo $sort_name; ?>&amp;action=enrollments"><?php _e('Last name','teachpress'); ?></a> <span style="display: <?php echo $display_name; ?>"><?php echo $sort_sign_name; ?></span></th>
           <th><?php _e('First name','teachpress'); ?></th>
           <th><?php _e('User account','teachpress'); ?></th>
           <th><?php _e('E-Mail'); ?></th>
           <?php
           foreach ($fields as $row) {
                $data = tp_db_helpers::extract_column_data($row->value);
                if ( $data['visibility'] === 'admin' ) {
                    echo '<th>' . $data['title'] . '</th>';
                }
            }
           ?>
           <th><a href="admin.php?page=teachpress/teachpress.php&course_id=<?php echo $course_id; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=date&amp;sort=<?php echo $sort_date; ?>&amp;action=enrollments"><?php _e('Registered at','teachpress'); ?></a> <span style="display: <?php echo $display_date; ?>"><?php echo $sort_sign_date; ?></span></th>
         </tr>
        </thead>  
        <tbody>
        <?php
        if ($count_enrollments === 0) {
            echo '<tr><td colspan="8"><strong>' . __('No entries','teachpress') . '</strong></td></tr>';
        }
        else {
            // all registered students for the course
            self::get_enrollments_rows($course_id, $enrollments, $link_parameter, $checkbox, $visible_fields, $reg_action, 'checkbox');
        }?>
        </tbody>
        </table>
        <?php
        // waitinglist
        if ($count_waitinglist != 0) { ?>
           <h3><?php _e('Waitinglist','teachpress'); ?></h3>
           <table class="widefat">
            <thead>
             <tr>
               <th class="check-column">
                <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('waiting[]','tp_check_all');" />
               </th>
               <th><?php _e('Last name','teachpress'); ?></th>
               <th><?php _e('First name','teachpress'); ?></th>
               <th><?php _e('User account','teachpress'); ?></th>
               <th><?php _e('E-Mail'); ?></th>
               <?php
                foreach ($fields as $row) {
                     $data = tp_db_helpers::extract_column_data($row->value);
                     if ( $data['visibility'] === 'admin' ) {
                         echo '<th>' . $data['title'] . '</th>';
                     }
                 }
                ?>
               <th><?php _e('Registered at','teachpress'); ?></th>
             </tr>
            </thead>  
            <tbody> 
            <?php
            self::get_enrollments_rows($course_id, $waitinglist, $link_parameter, $waiting, $visible_fields, $reg_action, 'waiting');
            ?>
            </tbody>
            </table>
        <?php  }
    }
    
    /**
     * Shows the documents tab for show_single_course page
     * @param int $course_id
     * @since 5.0.0
     */
    public static function get_documents_tab ($course_id) {
        tp_document_manager::init($course_id);
    }
    
}