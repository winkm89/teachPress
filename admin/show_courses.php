<?php
/**
 * This file contains all functions for displaying the show_courses page in admin menu
 * 
 * @package teachpress\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Add help tab for show courses page
 */
function tp_show_course_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_show_course_help',
        'title'     => __('Display courses','teachpress'),
        'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                        <p>' . __('You can use courses in a page or article with the following shortcodes:','teachpress') . '</p>
                        <p>' . __('For course informations','teachpress') . ': <strong>[tpcourseinfo id="x"]</strong> ' . __('x = Course-ID','teachpress') . '</p>
                        <p>' . __('For course documents','teachpress') . ': <strong>[tpcoursedocs id="x"]</strong> ' . __('x = Course-ID','teachpress') . '</p>
                        <p>' . __('For the course list','teachpress') . ': <strong>[tpcourselist]</strong></p>
                        <p>' . __('For the enrollment system','teachpress') . ': <strong>[tpenrollments]</strong></p>
                        <p><strong>' . __('More information','teachpress') . '</strong></p>
                        <p><a href="http://mtrv.wordpress.com/teachpress/shortcode-reference/" target="_blank" title="teachPress Shortcode Reference (engl.)">teachPress Shortcode Reference (engl.)</a></p>',
    ) );
}

/**
 * Add screen options for show courses page
 * @since 5.0.0
 */
function tp_show_course_page_screen_options() {
    global $tp_admin_show_courses_page;
    $screen = get_current_screen();
 
    if( !is_object($screen) || $screen->id != $tp_admin_show_courses_page ) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_pubs_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * Main controller for the show courses page and all single course pages
 * @since 5.0.0
 */
function tp_show_courses_page() {
    
    tp_admin::database_test('<div class="wrap">', '</div>');
     
    // Send mail (received from mail tab)
    if( isset( $_POST['send_mail'] ) ) {
        $from = isset ( $_POST['from'] ) ? htmlspecialchars($_POST['from']) : '';
        $to = isset ( $_POST['recipients'] ) ? htmlspecialchars($_POST['recipients']) : '';
        $subject = isset ( $_POST['subject'] ) ? htmlspecialchars($_POST['subject']) : '';
        $text = isset ( $_POST['text'] ) ? htmlspecialchars($_POST['text']) : '';
        $options['backup_mail'] = isset ( $_POST['backup_mail'] ) ? htmlspecialchars($_POST['backup_mail']) : '';
        $options['recipients'] = isset ( $_POST['recipients_option'] ) ? htmlspecialchars($_POST['recipients_option']) : '';
        $attachments = isset ( $_POST['attachments'] ) ? $_POST['attachments'] : '';
        $ret = tp_mail::sendMail($from, $to, $subject, $text, $options, $attachments);
        $message = $ret == true ? __('E-Mail sent','teachpress') : __('Error: E-Mail could not sent','teachpress');
        get_tp_message($message);
    }

    // Event Handler
    $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';

    if ( $action === 'edit' ) {
        tp_add_course_page();
    }
    elseif ( $action === 'show' || $action === 'assessments' || $action === 'add_assessments' || $action === 'enrollments' || $action === 'capabilites' || $action === 'documents' ) {
        tp_show_single_course_page();
    }
    elseif ( $action === 'list' ) {
        tp_lists_page();
    }
    elseif ( $action === 'mail' ) {
        tp_show_mail_page();
    }
    else {
        tp_courses_page::get_tab();
    }
}

/**
 * This class contains all function for the show courses page
 * @since 5.0.0
 */
class tp_courses_page {
    
    /**
     * Gets the show courses main page
     * @since 5.0.0
     * @access public
     */
    public static function get_tab() {
        global $current_user;
        $terms = get_tp_options('semester');
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
        $bulk = isset( $_GET['bulk'] ) ? $_GET['bulk'] : '';
        $copysem = isset( $_GET['copysem'] ) ? $_GET['copysem'] : '';
        $sem = ( isset($_GET['sem']) ) ? htmlspecialchars($_GET['sem']) : get_tp_option('sem');
        ?> 

        <div class="wrap">
            <h2><?php _e('Courses','teachpress'); ?> <a href="admin.php?page=teachpress/add_course.php" class="add-new-h2"><?php _e('Create','teachpress'); ?></a></h2>
        <form id="showcourse" name="showcourse" method="get" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <input name="page" type="hidden" value="teachpress/teachpress.php" />
           <?php 	
           // delete a course, part 1
           if ( $bulk === 'delete' ) {
                echo '<div class="teachpress_message">
                <p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>
                <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
                <a href="admin.php?page=teachpress/teachpress.php&sem=' . $sem . '&search=' . $search . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
                </div>';
           }
           // delete a course, part 2
           if ( isset($_GET['delete_ok']) ) {
                tp_courses::delete_courses($current_user->ID, $checkbox);
                $message = __('Removing successful','teachpress');
                get_tp_message($message);
           }
           // copy a course, part 1
           if ( $bulk === "copy" ) { 
                tp_courses_page::get_copy_course_form($terms, $sem, $search);
           }
           // copy a course, part 2
           if ( isset($_GET['copy_ok']) ) {
                tp_copy_course::init($checkbox, $copysem);
                $message = __('Copying successful','teachpress');
                get_tp_message($message);
           }
           ?>
            <div id="searchbox" style="float:right; padding-bottom:10px;"> 
                <?php if ($search != '') { ?>
                <a href="admin.php?page=teachpress/teachpress.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a>
                <?php } ?>
                <input type="search" name="search" id="pub_search_field" value="<?php echo stripslashes($search); ?>"/></td>
                <input type="submit" name="pub_search_button" id="pub_search_button" value="<?php _e('Search','teachpress'); ?>" class="button-secondary"/>
            </div>
            <div id="filterbox" style="padding-bottom:10px;">    
                 <select name="bulk" id="bulk">
                      <option>- <?php _e('Bulk actions','teachpress'); ?> -</option>
                      <option value="copy"><?php _e('copy','teachpress'); ?></option>
                      <option value="delete"><?php _e('Delete','teachpress'); ?></option>
                 </select>
                 <input type="submit" name="teachpress_submit" id="doaction" value="<?php _e('OK','teachpress'); ?>" class="button-secondary"/>
                 <select name="sem" id="sem">
                      <option value=""><?php _e('All terms','teachpress'); ?></option>
                      <?php
                      foreach ($terms as $row) { 
                           $current = ( $row->value == $sem ) ? 'selected="selected"' : '';
                           echo '<option value="' . $row->value . '" ' . $current . '>' . stripslashes($row->value) . '</option>';
                      } ?> 
                 </select>
                <input type="submit" name="start" value="<?php _e('Show','teachpress'); ?>" id="teachpress_submit" class="button-secondary"/>
             </div>
            <table class="widefat">
               <thead>
               <tr>
                   <th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" /></th>
                   <th><?php _e('Name','teachpress'); ?></th>
                   <th><?php _e('ID'); ?></th>
                   <th><?php _e('Type'); ?></th>
                   <th><?php _e('Lecturer','teachpress'); ?></th>
                   <th><?php _e('Date','teachpress'); ?></th>
                   <th colspan="2" align="center" style="text-align:center;"><?php _e('Places','teachpress'); ?></th>
                   <th colspan="2" align="center" style="text-align:center;"><?php _e('Enrollments','teachpress'); ?></th>
                   <th><?php _e('Term','teachpress'); ?></th>
                   <th><?php _e('Visibility','teachpress'); ?></th>
               </tr>
               </thead>
               <tbody>
            <?php
               $order = 'name, course_id';
               if ($search != '') {
                   $order = 'semester DESC, name';	
               }
               tp_courses_page::get_courses($current_user->ID, $search, $sem, $bulk, $checkbox);
  
            ?>
            </tbody>
            </table>
        </form>
        </div>
        <?php 
        
    }
    
    /**
     * Returns the content for the course table
     * @param int $user_ID      The ID of the current user
     * @param string $search    The search string
     * @param string $sem       The semester you want to show
     * @param array $bulk       The bulk checkbox
     * @param array $checkbox   The checkbox
     * @return type
     * @since 5.0.0
     * @access private
     */
    private static function get_courses ($user_ID, $search, $sem, $bulk, $checkbox) {
        $row = tp_courses::get_courses( array('search' => $search, 'semester' => $sem, 'order' => 'name, course_id') );
        // if the query is empty
        if ( count($row) === 0 ) { 
            echo '<tr><td colspan="13"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
            return;
        }
           
        // prepare data
        $used_places = tp_courses::get_used_places();
        $static['bulk'] = $bulk;
        $static['sem'] = $sem;
        $static['search'] = $search;
        $z = 0;
        foreach ($row as $row){
            $date1 = tp_datesplit($row->start);
            $date2 = tp_datesplit($row->end);
            $courses[$z]['course_id'] = $row->course_id;
            $courses[$z]['name'] = stripslashes($row->name);
            $courses[$z]['type'] = stripslashes($row->type);
            $courses[$z]['room'] = stripslashes($row->room);
            $courses[$z]['lecturer'] = stripslashes($row->lecturer);
            $courses[$z]['date'] = stripslashes($row->date);
            $courses[$z]['places'] = $row->places;
            // number of free places
            if ( array_key_exists($row->course_id, $used_places) ) {
                $courses[$z]['fplaces'] = $courses[$z]['places'] - $used_places[$row->course_id];
            }
            else {
                $courses[$z]['fplaces'] = $courses[$z]['places'];
            }
            $courses[$z]['start'] = '' . $date1[0][0] . '-' . $date1[0][1] . '-' . $date1[0][2] . '';
            $courses[$z]['end'] = '' . $date2[0][0] . '-' . $date2[0][1] . '-' . $date2[0][2] . '';
            $courses[$z]['semester'] = stripslashes($row->semester);
            $courses[$z]['parent'] = $row->parent;
            $courses[$z]['visible'] = $row->visible;
            $courses[$z]['use_capabilites'] = $row->use_capabilites;
            $z++;
        }
        // display courses
        $class_alternate = true;
        for ($i = 0; $i < $z; $i++) {
            // normal table design
            if ($search == '') {
                if ($courses[$i]['parent'] != 0) {
                    continue;
                }
                // alternate table rows
                $static['tr_class'] = ( $class_alternate === true ) ? ' class="alternate"' : '';
                $class_alternate = ( $class_alternate === true ) ? false : true;
                echo tp_courses_page::get_single_table_row($courses[$i], $user_ID, $checkbox, $static);
                // Search childs
                for ($j = 0; $j < $z; $j++) {
                    if ($courses[$i]['course_id'] == $courses[$j]['parent']) {
                        echo tp_courses_page::get_single_table_row($courses[$j], $user_ID, $checkbox, $static, $courses[$i]['name'],'child');
                    }
                }
                // END search childs
               	
            }
            // table design for searches
            else {
                $parent_name = ( $courses[$i]['parent'] != 0 ) ? tp_courses::get_course_data($courses[$i]['parent'], 'name') : '';
                echo tp_courses_page::get_single_table_row($courses[$i], $user_ID, $checkbox, $static, $parent_name, 'search');
            }
        }	
             
    }
    
    /** 
     * Returns a single table row for show_courses.php
     * @param array $course                     course data
     * @param array $user_ID                    The ID of the user
     * @param array $checkbox
     * @param array $static
           $static['bulk']                      copy or delete
           $static['sem']                       semester
           $static['search']                    input from search field
     * @param string $parent_course_name        the name of the parent course
     * @param string $type                      parent or child
     * @return string
     * @since 5.0.0
     * @access private
    */ 
    private static function get_single_table_row ($course, $user_ID, $checkbox, $static, $parent_course_name = '', $type = 'parent') {
        $check = '';
        $style = '';
        
        // Check if checkbox must be activated or not
        if ( ( $static['bulk'] == "copy" || $static['bulk'] == "delete") && $checkbox != "" ) {
            for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                if ( $course['course_id'] == $checkbox[$k] ) { $check = 'checked="checked"';} 
            }
        }
        
        // Change the style for an important information
        if ( $course['places'] > 0 && $course['fplaces'] <= 0 ) {
            $style = ' style="color:#ff6600; font-weight:bold;"'; 
        }
        
        // Type specifics
        $class = ( $type == 'parent' || $type == 'search' ) ? ' class="tp_course_parent"' : ' class="tp_course_child"';

        if ( $type == 'child' || $type == 'search' ) {
            if ( $course['name'] != $parent_course_name ) {
                $course['name'] = $parent_course_name . ' - ' . $course['name'];
            }
        }
        
        // row actions
        $delete_link = '';
        $edit_link = '';
        $capability = tp_courses::get_capability($course['course_id'], $user_ID);
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $edit_link = '| <a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=edit&amp;ref=overview" title="' . __('Edit','teachpress') . '">' . __('Edit','teachpress') . '</a>';
        }
        if ( $capability === 'owner' ) {
            $delete_link = '| <a class="tp_row_delete" href="admin.php?page=teachpress/teachpress.php&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;checkbox%5B%5D=' . $course['course_id'] . '&amp;bulk=delete" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a>';
        }
        
        // complete the row
        $a1 = '<tr' . $static['tr_class'] . '>
            <th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $course['course_id'] . '"' . $check . '/></th>
            <td' . $class . '>
                <a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" class="teachpress_link" title="' . __('Click to show','teachpress') . '"><strong>' . $course['name'] . '</strong></a>
                <div class="tp_row_actions">
                    <a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" title="' . __('Show','teachpress') . '">' . __('Show','teachpress') . '</a> ' . $edit_link . $delete_link . '
                </div>
            </td>
            <td>' . $course['course_id'] . '</td>
            <td>' . $course['type'] . '</td>
            <td>' . $course['lecturer'] . '</td>
            <td>' . $course['date'] . '</td>
            <td>' . $course['places'] . '</td>
            <td' . $style . '>' . $course['fplaces'] . '</td>';
        if ( $course['start'] != '0000-00-00' && $course['end'] != '0000-00-00' ) {
            $a2 ='<td>' . $course['start'] . '</td>
                    <td>' . $course['end'] . '</td>';
        } 
        else {
            $a2 = '<td colspan="2" style="text-align:center;">' . __('none','teachpress') . '</td>';
        }
        $a3 = '<td>' . $course['semester'] . '</td>';
        if ( $course['visible'] == 1 ) {
            $a4 = '<td>' . __('normal','teachpress') . '</td>';
        }
        elseif ( $course['visible'] == 2 ) {
            $a4 = '<td>' . __('extend','teachpress') . '</td>';
        }
        else {
            $a4 = '<td>' . __('invisible','teachpress') . '</td>';
        }
        $a5 = '</tr>';
        // Return
        $return = $a1 . $a2 . $a3 . $a4 . $a5;
        return $return;
    }
    
    
    /**
     * Gets the form for the course copy function
     * @param object $terms     an object whith all available terms
     * @param string $sem       the current term/semetser
     * @param string $search    the current search string
     * @since 5.0.0
     * @access public
     */
    public static function get_copy_course_form($terms, $sem, $search) {
        ?>
        <div class="teachpress_message">
            <p class="teachpress_message_headline"><?php _e('Copy courses','teachpress'); ?></p>
            <p class="teachpress_message_text"><?php _e('Select the term, in which you will copy the selected courses.','teachpress'); ?></p>
            <p class="teachpress_message_text">
            <select name="copysem" id="copysem">
                <?php
                foreach ($terms as $term) { 
                    $current = ( $term->value == $sem ) ? 'selected="selected"' : '';
                    echo '<option value="' . $term->value . '" ' . $current . '>' . stripslashes($term->value) . '</option>';
                } ?> 
            </select>
            <input name="copy_ok" type="submit" class="button-primary" value="<?php _e('copy','teachpress'); ?>"/>
            <a href="<?php echo 'admin.php?page=teachpress/teachpress.php&sem=' . $sem . '&search=' . $search . ''; ?>" class="button-secondary"> <?php _e('Cancel','teachpress'); ?></a>
            </p>
        </div>
        <?php
    }
}