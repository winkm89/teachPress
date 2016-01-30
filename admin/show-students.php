<?php 
/**
 * This file contains all functions for displaying the show_students page in admin menu
 * 
 * @package teachpress\admin\students
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Main controller for the show students page and all single student pages
 */
function tp_students_page() { 

    $bulk = isset ( $_GET['bulk'] ) ? $_GET['bulk'] : '';
    $search = isset ( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : ''; 
    $action = isset ($_GET['action']) ? $_GET['action'] : '';
    $student = isset ( $_GET['student_id'] ) ? intval($_GET['student_id']) : 0;
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);

    // Page menu
    $page_settings['entries_per_page'] = 50;
    // Handle limits
    if (isset($_GET['limit'])) {
        $page_settings['curr_page'] = intval($_GET['limit']) ;
        if ( $page_settings['curr_page'] <= 0 ) {
            $page_settings['curr_page'] = 1;
        }
        $page_settings['entry_limit'] = ( $page_settings['curr_page'] - 1 ) * $page_settings['entries_per_page'];
    }
    else {
        $page_settings['entry_limit'] = 0;
        $page_settings['curr_page'] = 1;
    }

    // Send mail (received from mail.php)
    if( isset( $_POST['send_mail'] ) ) {
        $from = isset ( $_POST['from'] ) ? htmlspecialchars($_POST['from']) : '';
        $to = isset ( $_POST['recipients'] ) ? htmlspecialchars($_POST['recipients']) : '';
        $recipients_option = isset ( $_POST['recipients_option'] ) ? htmlspecialchars($_POST['recipients_option']) : '';
        $subject = isset ( $_POST['subject'] ) ? htmlspecialchars($_POST['subject']) : '';
        $text = isset ( $_POST['text'] ) ? htmlspecialchars($_POST['text']) : '';
        $attachments = isset ( $_POST['attachments'] ) ? $_POST['attachments'] : '';
        tp_mail::sendMail($from, $to, $subject, $text, $recipients_option, $attachments);
        get_tp_message( __('E-Mail sent','teachpress') );
    }
    
    // field options
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);
    $visible_fields = array();
    $select_fields = array();
    foreach ($fields as $row) {
        $data = tp_db_helpers::extract_column_data($row['value']);
        if ( $data['visibility'] === 'admin') {
            array_push($visible_fields, $row['variable']);
        }
        if ( $data['visibility'] === 'admin' && $data['type'] === 'SELECT' ) {
            array_push($select_fields, $row['variable']);
        }
    }

    // possible data from select fields
    $url_parameter = '';
    $meta_search = array();
    $max = count($select_fields);
    for ($i = 0; $i < $max; $i++) {
        if ( !isset($_GET[$select_fields[$i]]) ) {
            continue;
        }
        $meta_search[$select_fields[$i]] = htmlspecialchars( $_GET[$select_fields[$i]] );
        $url_parameter .= '&amp;' . $select_fields[$i] . '=' . $meta_search[$select_fields[$i]];
    }

    // Event handler
    if ( $action === 'show' ) {
        tp_show_student_page($student, $fields, $search, $page_settings['curr_page'], $url_parameter);
    }
    elseif ( $action === 'edit' ) {
        tp_edit_student_page($student, $fields, $search, $page_settings['curr_page'], $url_parameter);
    }
    elseif ( $action === 'add' ) {
        tp_add_student_page($fields);
    }
    else {
        tp_students_page::get_page($bulk, $search, $meta_search, $fields, $visible_fields, $select_fields, $page_settings, $url_parameter);
    }
}

/**
 * This class contains all function for the show students page
 * @since 5.0.0
 */
class tp_students_page {
    
    /**
     * Gets the filter for meta data
     * @param array $select_fields      A simple array with the names of the select fields 
     * @param array $meta_search        An associative array with the values of used select fields    
     * @since 5.0.0
     */
    public static function get_filter ($select_fields, $meta_search) {
        $selects = false;
        $max = count($select_fields);
        for ($i = 0; $i < $max; $i++) {
            $meta_field = get_tp_option($select_fields[$i], 'teachpress_stud');
            $meta_field_options = tp_db_helpers::extract_column_data($meta_field);
            
            echo '<select name="' . $select_fields[$i] . '" title="' . __('Select','teachpress') . ': ' . $meta_field_options['title'] . '">';
            echo '<option value="">- ' . __('All','teachpress') . ' -</option>';
            $options = get_tp_options($select_fields[$i]);
            $search = ( !empty($meta_search) ) ? $meta_search[$select_fields[$i]] : '';
            
            foreach ( $options as $option ) {
                $selected = ( $search === $option->value ) ? 'selected="selected"' : '';
                echo '<option value="' . $option->value  . '" ' . $selected . '>' . $option->value  . '</option>';
            }
            echo '</select>';
            $selects = true;
        }
            
        if ( $selects === true ) {
            echo ' <input name="anzeigen" type="submit" id="teachpress_search_senden" value="' . __('Show','teachpress') . '" class="button-secondary"/>';
        }
    }
    
    /**
     * Gets the main screen of the show students page
     * @param string $bulk              Info string for bulk actions
     * @param string $search            The search string 
     * @param array $meta_search        An associative array with the values of used select fields 
     * @param array $fields             A simple array with the names of all meta data select fields
     * @param array $visible_fields     A simple array with the names of the visible fields
     * @param array $select_fields      A simple array with the names of the select fields
     * @param array $page_settings      An associative array with page settings (entries_per_page, curr_page, entry_limit)
     * @param string $url_parameter     A string with URL parameter for meta data fields
     * @since 5.0.0
     */
    public static function get_page ($bulk, $search, $meta_search, $fields, $visible_fields, $select_fields, $page_settings, $url_parameter) {
        $page = 'teachpress/students.php';
        $checkbox = isset ( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
        ?>
        <div class="wrap">
        <form name="search" method="get" action="admin.php">
        <input name="page" type="hidden" value="<?php echo $page; ?>" />
        <?php
        // Delete students part 1
        if ( $bulk === "delete" ) {
            echo '<div class="teachpress_message">
            <p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>
            <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
            <a href="admin.php?page=teachpress/students.php&amp;search=' . $search . '&amp;limit=' . $page_settings['entry_limit'] . $url_parameter . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
            </div>';
        }
        
        // Delete students part 2
        if ( isset($_GET['delete_ok']) ) {
            tp_students::delete_student($checkbox);
            $message = __('Removing successful','teachpress');
            get_tp_message($message);
        }
        
        
        
        // Load data
        $number_entries = tp_students::get_students( array('search' => $search, 
                                                           'meta_search' => $meta_search, 
                                                           'output_type' => OBJECT, 
                                                           'count' => true ) );
        $students = tp_students::get_students( array('search' => $search, 
                                                     'meta_search' => $meta_search, 
                                                     'limit' => $page_settings['entry_limit'] . ',' . $page_settings['entries_per_page'], 
                                                     'output_type' => ARRAY_A ) );
        ?>
        <h2><?php _e('Students','teachpress'); ?> <a class="add-new-h2" href="admin.php?page=teachpress/students.php&amp;action=add"><?php _e('Add student','teachpress'); ?></a></h2>
        <div id="searchbox" style="float:right; padding-bottom:5px;">  
            <?php if ($search != "") { ?>
            <a href="admin.php?page=teachpress/students.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a>
            <?php } ?>
            <input name="search" type="search" value="<?php echo stripslashes($search); ?>"/></td>
            <input name="go" type="submit" value="<?php _e('Search','teachpress'); ?>" id="teachpress_search_senden" class="button-secondary"/>
        </div>
        <div class="tablenav" style="padding-bottom:5px;">
            <select name="bulk" id="bulk">
                <option>- <?php _e('Bulk actions','teachpress'); ?> -</option>
                <option value="delete"><?php _e('Delete','teachpress'); ?></option>
            </select>
            <input type="submit" name="teachpress_submit" value="<?php _e('OK','teachpress'); ?>" id="doaction" class="button-secondary"/>
            <?php 
            tp_students_page::get_filter($select_fields, $meta_search);
            // Page Menu
            $args = array('number_entries' => $number_entries,
                      'entries_per_page' => $page_settings['entries_per_page'],
                      'current_page' => $page_settings['curr_page'],
                      'entry_limit' => $page_settings['entry_limit'],
                      'page_link' => "admin.php?page=$page&amp;",
                      'link_attributes' => "search=$search" . $url_parameter);
            echo tp_page_menu($args);
            ?>
        </div>
        <table class="widefat">
        <thead>
        <tr>
            <th class="check-column">
                <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" />
            </th>
            <?php
            echo '<th>' . __('Last name','teachpress') . '</th>';
            echo '<th>' . __('First name','teachpress') . '</th>'; 
            echo '<th>' . __('User account','teachpress') . '</th>'; 
            echo '<th>' . __('E-Mail') . '</th>';
            foreach ($fields as $row) {
                $data = tp_db_helpers::extract_column_data($row['value']);
                if ( $data['visibility'] === 'admin' ) {
                    echo '<th>' . $data['title'] . '</th>';
                }
            }
            ?>
        </tr>
        </thead>
        <tbody> 
        <?php
        tp_students_page::get_table_row ($students, $bulk, $checkbox, $search, $page_settings['curr_page'], $visible_fields, $url_parameter);
        ?> 
        </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages" style="float:right;">
            <?php 
            if ( $number_entries > $page_settings['entries_per_page'] ) {
                $args = array('number_entries' => $number_entries,
                          'entries_per_page' => $page_settings['entries_per_page'],
                          'current_page' => $page_settings['curr_page'],
                          'entry_limit' => $page_settings['entry_limit'],
                          'page_link' => "admin.php?page=$page&amp;",
                          'link_attributes' => "search=$search" . $url_parameter,
                          'mode' => 'bottom');
                echo tp_page_menu($args);
            } 
            else {
                if ($number_entries == 1) {
                    echo $number_entries . ' ' . __('entry','teachpress');
                }
                else {
                    echo $number_entries . ' ' . __('entries','teachpress');
                }
            }?>
            </div>
        </div>
        </form>
        </div>
        <?php
    }
    
    /**
     * Gets all body table rows for the main table of the show students page
     * @param int $students
     * @param string $bulk
     * @param array $checkbox
     * @param string $search
     * @param int $curr_page
     * @param array $visible_fields
     * @param string $url_parameter
     * @since 5.0.0
     */
    public static function get_table_row ($students, $bulk, $checkbox, $search, $curr_page, $visible_fields, $url_parameter) {
        // Show students
        if ( count($students) === 0) { 
            echo '<tr><td colspan="9"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
            return;
        }

        $class_alternate = true;
        foreach( $students as $row3) { 
            $student_meta = tp_students::get_student_meta($row3['wp_id']);
            $tr_class = ( $class_alternate === true ) ? 'class="alternate"' : '';
            $class_alternate = ( $class_alternate === true ) ? false : true;
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column"><input type="checkbox" name="checkbox[]" id="checkbox" value="' . $row3['wp_id'] . '"';
            if ( $bulk === "delete") { 
                for( $i = 0; $i < count( $checkbox ); $i++ ) { 
                    if ( $row3['wp_id'] == $checkbox[$i] ) { echo 'checked="checked"';} 
                } 
            }
            echo '/></th>';
            $link_name = ( $row3['lastname'] !== '' ) ? stripslashes($row3['lastname']) : '[' . __('empty','teachpress') . ']';
            echo '<td><a href="admin.php?page=teachpress/students.php&amp;student_id=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=show" class="teachpress_link" title="' . __('Click to edit','teachpress') . '"><strong>' . $link_name . '</strong></a></td>';
            echo '<td>' . stripslashes($row3['firstname']) . '</td>';
            echo '<td>' . stripslashes($row3['userlogin']) . '</td>';
            echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;student_id=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=mail&amp;single=' . $row3['email'] . '" title="' . __('send E-Mail','teachpress') . '">' . $row3['email'] . '</a></td>';
            $max2 = count($visible_fields);
            for ( $i = 0; $i < $max2; $i++ ) {
                $value = '';
                foreach ($student_meta as $meta) {
                    if ( $meta['meta_key'] === $visible_fields[$i] ) {
                        $value = stripslashes($meta['meta_value']);
                        break;
                    }
                }
                echo '<td>' . $value . '</td>';
            }
            echo '</tr>';
        } 
     
    }
}