<?php
/**
 * This file contains all functions for displaying the show/edit student pages in admin menu
 * 
 * @package teachpress\admin\students
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all functions for the show/edit student pages in admin menu
 * @since 6.0.0
 */
class TP_Student_Page {
    
    /**
     * Prints a signup table
     * @param int $student      The WP user ID
     * @param type $mode        wtl or reg
     * @since 6.0.0
     * @access private
     */
    private static function get_signups_table ($student, $mode) {
        
        if ( $mode === 'reg' ) {
            $row = TP_Students::get_signups( array('wp_id' => $student, 'mode' => 'reg'));
        }
        else {
            $row = TP_Students::get_signups( array('wp_id' => $student, 'mode' => 'wtl'));
        }
        
        // return if there is now entry
        if ( $mode === 'wtl' && count($row) === 0 ) {
            return;
        }
        
        // for waitinglists only
        if ( $mode === 'wtl' && count($row) !== 0 ) {
            echo '<h3>' . __('Waitinglist','teachpress') . '</h3>';
        }
        
        echo '<table cellpadding="5" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>&nbsp;</th>';
        echo '<th>' . __('Enrollment-Nr.','teachpress') . '</th>';
        echo '<th>' . __('Registered at','teachpress') . '</th>';
        echo '<th>' . __('Course','teachpress') . '</th>';
        echo '<th>' . __('Type') . '</th>';
        echo '<th>' . __('Date','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';    
        echo '<tbody>';
        
        // get signups
        if ( count($row) != 0 ) {
            self::get_signups_table_rows($row); 
        }
        else {
            echo '<tr><td colspan="6"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Prints the rows for the signup tables
     * @param object $row
     * @since 6.0.0
     */
    private static function get_signups_table_rows ($row) {
        $class_alternate = true;
        foreach($row as $row) {
            
            // alternate table style
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            $parent_name = ( $row->parent_name != "" ) ? $row->parent_name . ' ' : '';
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $row->con_id . '"/></th>';
            echo '<td>' . $row->con_id . '</td>';
            echo '<td>' . $row->timestamp . '</td>';
            echo '<td>' . stripslashes($parent_name) . ' ' . stripslashes($row->name) . '</td>';
            echo '<td>' . stripslashes($row->type) . '</td>';
            echo '<td>' . stripslashes($row->date) . '</td>';
            echo '</tr>';
        }          

    }
    
    /** 
     * Prints the show singe student tab
     * @param int $student          The ID of the student/user
     * @param array $fields         An associative array with the settings of the meta data fields. The array keys are variable an value.
     * @param string $search        The search string       (used for back button)
     * @param string $curr_page     The numbero of entries  (used for back button)
     * @param string $url_parameter A string with URL parameter for meta data fields
     * @since 6.0.0
    */ 
    public static function show_tab ($student, $fields, $search, $curr_page, $url_parameter) {
        $row3 = TP_Students::get_student($student);
        $row4 = TP_Students::get_student_meta($student);
        
        echo '<div class="wrap">';
        
        // Event handler
        if ( isset( $_GET['delete'] )) {
             TP_Courses::delete_signup($_GET['checkbox']);
             $message = __('Enrollment deleted','teachpress');
             get_tp_message($message);
        }
        
        // back button
        echo '<p><a href="admin.php?page=teachpress/students.php&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';

        // start form
        echo '<form name="edit_student" method="get" action="admin.php">';
        echo '<input name="page" type="hidden" value="teachpress/students.php" />';
        echo '<input name="action" type="hidden" value="show" />';
        echo '<input name="student_id" type="hidden" value="' . $student . '" />';
        echo '<input name="search" type="hidden" value="' . $search . '" />';
        echo '<input name="limit" type="hidden" value="' . $curr_page . '" />';
        echo '<h1>' . stripslashes($row3['firstname']) . ' ' . stripslashes($row3['lastname']) . ' <span class="tp_break">|</span> <small><a href="admin.php?page=teachpress/students.php&amp;student_id=' . $student . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=edit' . '" id="daten_aendern">' . __('Edit','teachpress') . '</a></small></h1>';
        echo '<div style="width:55%; padding-bottom:10px;">';
        echo '<table border="0" cellpadding="0" cellspacing="5" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<td width="130"><strong>' . __('WordPress User-ID','teachpress') . '</strong></td>';
        echo '<td style="vertical-align:middle;">' . $row3['wp_id'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<tr>';
        echo '<td width="130"><strong>' . __('User account','teachpress') . '</strong></td>';
        echo '<td style="vertical-align:middle;">' . $row3['userlogin'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>' . __('E-Mail') . '</strong></td>';
        echo '<td style="vertical-align:middle;"><a href="admin.php?page=teachpress/teachpress.php&amp;student_id=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=mail&amp;single=' . $row3['email'] . '" title="' . __('Send E-Mail to','teachpress') . ' ' . $row3['firstname'] . ' ' . $row3['lastname'] . '">' . $row3['email'] . '</a></td>';
        echo '</tr>';
        foreach ($fields as $row) {
            $data = TP_DB_Helpers::extract_column_data($row['value']);
            echo '<tr>';
            echo '<td><strong>' . $data['title'] . '</strong></td>';
            foreach ($row4 as $meta) {
                if ( $meta['meta_key'] === $row['variable'] ) {
                    echo '<td style="vertical-align:middle;">' . $meta['meta_value'] . '</td>';
                    continue;
                }

            }
            echo '</tr>';
        }
        echo '</thead>';   
        echo '</table>';
        echo '</div>';
        
        echo '<h3>' . __('Signups','teachpress') . '</h3>';
        self::get_signups_table($student, 'reg');
        self::get_signups_table($student, 'wtl');
        
        echo '<table border="0" cellspacing="0" cellpadding="7" id="einzel_optionen">';
        echo '<tr>';
        echo '<td>' . __('delete enrollment','teachpress') . '</td>';
        echo '<td> <input name="delete" type="submit" value="' . __('Delete','teachpress') . '" id="teachpress_search_delete" class="button-secondary"/></td>';
        echo '</tr>';
        echo '</table>';
        
        // End form
        echo '</form>';
        echo '</div>';
        
    }
    
    /**
     * Prints the edit student UI
     * @param int $student          The ID of the student/user
     * @param array $fields         An associative array with the settings of the meta data fields. The array keys are variable an value.
     * @param string $search        The search string       (used for back button)
     * @param string $entry_limit   The numbero of entries  (used for back button)
     * @param string $url_parameter A string with URL parameter for meta data fields
     * @since 6.0.0
     * @access public
    */
    public static function edit_tab ($student, $fields, $search, $entry_limit, $url_parameter) {
        if ( isset($_POST['tp_change_user'] ) ) {
            // delete old meta data
            TP_Students::delete_student_meta($student);

            $data = array (
                'firstname' => htmlspecialchars($_POST['firstname']),
                'lastname' => htmlspecialchars($_POST['lastname']),
                'userlogin' => htmlspecialchars($_POST['userlogin']),
                'email' => htmlspecialchars($_POST['email'])
            );
            TP_DB_Helpers::prepare_meta_data($student, $fields, $_POST, 'students');
            TP_Students::change_student($student, $data, false);
            get_tp_message( __('Saved') );
        }

        echo '<div class="wrap">';
        echo '<p><a href="admin.php?page=teachpress/students.php&amp;student_id=' . $student . '&amp;search=' . $search . '&amp;limit=' . $entry_limit . $url_parameter . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';
        echo '<h2>' . __('Edit Student','teachpress') . '</h2>';
        echo tp_registration_form($student, 'admin');
        echo '</div>';
    }
}