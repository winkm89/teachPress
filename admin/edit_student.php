<?php
/**
 * This file contains all functions for displaying the edit_student page in admin menu
 * 
 * @package teachpress\admin\students
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/** 
 * Edit a student
 * @param int $student          The ID of the student/user
 * @param array $fields         An associative array with the settings of the meta data fields. The array keys are variable an value.
 * @param string $search        The search string       (used for back button)
 * @param string $curr_page     The numbero of entries  (used for back button)
 * @param string $url_parameter A string with URL parameter for meta data fields
 * @since 5.0.0
*/ 
function tp_show_student_page($student, $fields, $search, $curr_page, $url_parameter) {
   ?> 
   <div class="wrap">
   <?php
   // Event handler
   if ( isset( $_GET['delete'] )) {
        tp_courses::delete_signup($_GET['checkbox']);
        $message = __('Enrollment deleted','teachpress');
        get_tp_message($message);
   }
   echo '<p><a href="admin.php?page=teachpress/students.php&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';
   ?>
   <form name="edit_student" method="get" action="admin.php">
   <input name="page" type="hidden" value="teachpress/students.php" />
   <input name="action" type="hidden" value="show" />
   <input name="student_id" type="hidden" value="<?php echo $student; ?>" />
   <input name="search" type="hidden" value="<?php echo $search; ?>" />
   <input name="limit" type="hidden" value="<?php echo $curr_page; ?>" />
   <?php
      $row3 = tp_students::get_student($student);
      $row4 = tp_students::get_student_meta($student);
   ?>
    <h2 style="padding-top:0px;"><?php echo stripslashes($row3['firstname']); ?> <?php echo stripslashes($row3['lastname']); ?> <span class="tp_break">|</span> <small><a href="<?php echo 'admin.php?page=teachpress/students.php&amp;student_id=' . $student . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=edit'; ?>" id="daten_aendern"><?php _e('Edit','teachpress'); ?> </a></small></h2>
     <div style="width:55%; padding-bottom:10px;">
     <table border="0" cellpadding="0" cellspacing="5" class="widefat">
        <thead>
        <?php
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
        echo'<td><strong>' . __('E-Mail') . '</strong></td>';
        echo '<td style="vertical-align:middle;"><a href="admin.php?page=teachpress/teachpress.php&amp;student_id=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;limit=' . $curr_page . $url_parameter . '&amp;action=mail&amp;single=' . $row3['email'] . '" title="' . __('Send E-Mail to','teachpress') . ' ' . $row3['firstname'] . ' ' . $row3['lastname'] . '">' . $row3['email'] . '</a></td>';
        echo '</tr>';
        foreach ($fields as $row) {
            $data = tp_db_helpers::extract_column_data($row['value']);
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
        ?>
      </thead>   
     </table>
     </div>
   <h3><?php _e('Signups','teachpress'); ?></h3>
   <table cellpadding="5" class="widefat">
    <thead>
        <tr>
        <th>&nbsp;</th>
        <th><?php _e('Enrollment-Nr.','teachpress'); ?></th>
        <th><?php _e('Registered at','teachpress'); ?></th>
        <th><?php _e('Course','teachpress'); ?></th>
        <th><?php _e('Type'); ?></th>
        <th><?php _e('Date','teachpress'); ?></th>
        </tr>
    </thead>    
    <tbody>
    <?php
        // get signups
        $row = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'reg'));
        if ( count($row) != 0) {
            foreach($row as $row) {
                $parent_name = ( $row->parent_name != "" ) ? $row->parent_name . ' ' : '';
                echo '<tr>';
                echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $row->con_id . '"/></th>';
                echo '<td>' . $row->con_id . '</td>';
                echo '<td>' . $row->timestamp . '</td>';
                echo '<td>' . stripslashes($row->parent_name) . ' ' . stripslashes($row->name) . '</td>';
                echo '<td>' . stripslashes($row->type) . '</td>';
                echo '<td>' . stripslashes($row->date) . '</td>';
                echo '</tr>';
            } 
        }
        else {
            echo '<tr><td colspan="6"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }?>
    </tbody>
   </table>
   <?php
   $row = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'wtl') );
   if ( count($row) != 0 ) {
        echo '<h3>' . __('Waitinglist','teachpress') . '</h3>';
        ?>
        <table cellpadding="5" class="widefat">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php _e('Enrollment-Nr.','teachpress'); ?></th>
                    <th><?php _e('Registered at','teachpress'); ?></th>
                    <th><?php _e('Course','teachpress'); ?></th>
                    <th><?php _e('Type'); ?></th>
                    <th><?php _e('Date','teachpress'); ?></th>
                </tr>
            </thead>    
            <tbody>
            <?php     
            foreach($row as $row) {
                if ( $row->waitinglist == 1 ) {
                    $parent_name = ( $row->parent_name != "" ) ? $row->parent_name . ' ' : '';
                    echo '<tr>';
                    echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $row->con_id . '"/></th>';
                    echo '<td>' . $row->con_id . '</td>';
                    echo '<td>' . $row->timestamp . '</td>';
                    echo '<td>' . stripslashes($parent_name) . stripslashes($row->name) . '</td>';
                    echo '<td>' . stripslashes($row->type) . '</td>';
                    echo '<td>' . stripslashes($row->date) . '</td>';
                    echo '</tr>';
                }
            }
                ?>
            </tbody>
        </table>
   <?php } ?>
   <table border="0" cellspacing="0" cellpadding="7" id="einzel_optionen">
     <tr>
        <td><?php _e('delete enrollment','teachpress'); ?></td>
        <td> <input name="delete" type="submit" value="<?php _e('Delete','teachpress'); ?>" id="teachpress_search_delete" class="button-secondary"/></td>
     </tr>
   </table>
   </form>
   </div>
<?php } 

/**
 * Edit student UI
 * @param int $student          The ID of the student/user
 * @param array $fields         An associative array with the settings of the meta data fields. The array keys are variable an value.
 * @param string $search        The search string       (used for back button)
 * @param string $entry_limit   The numbero of entries  (used for back button)
 * @param string $url_parameter A string with URL parameter for meta data fields
 * @since 5.0.0
 */
function tp_edit_student_page($student, $fields, $search, $entry_limit, $url_parameter) {
    
    
    if ( isset($_POST['tp_change_user'] ) ) {
        // delete old meta data
        tp_students::delete_student_meta($student);
        
        $data = array (
            'firstname' => htmlspecialchars($_POST['firstname']),
            'lastname' => htmlspecialchars($_POST['lastname']),
            'userlogin' => htmlspecialchars($_POST['userlogin']),
            'email' => htmlspecialchars($_POST['email'])
        );
        tp_db_helpers::prepare_meta_data($student, $fields, $_POST, 'students');
        tp_students::change_student($student, $data, false);
        get_tp_message( __('Saved') );
    }
    
    echo '<div class="wrap">';
    echo '<p><a href="admin.php?page=teachpress/students.php&amp;student_id=' . $student . '&amp;search=' . $search . '&amp;limit=' . $entry_limit . $url_parameter . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';
    echo '<h2>' . __('Edit Student','teachpress') . '</h2>';
    echo tp_registration_form($student, 'admin');
    echo '</div>';
}