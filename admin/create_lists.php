<?php
/**
 * This file contains all functions for displaying the create_lists page in admin menu
 * 
 * @package teachpress\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/** 
 * Create attendance lists
 * @param $course_id
 * @param $search
 * @param $sem
*/
function tp_lists_page() {
   
    $course_id = isset( $_GET['course_id'] ) ? intval($_GET['course_id']) : '';
    $redirect = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $sem = isset( $_GET['sem'] ) ? htmlspecialchars($_GET['sem']) : '';
    
    $sort = isset( $_POST['sort'] ) ? htmlspecialchars($_POST['sort']) : '';
    $extra_fields = isset( $_POST['extra_fields'] ) ? $_POST['extra_fields'] : array();
    $number = isset( $_POST['number'] ) ? intval($_POST['number']) : '';
    $create = isset( $_POST['create'] ) ? $_POST['create'] : '';

    echo '<div class="wrap">';
    echo '<form id="einzel" name="einzel" method="post">';

    if ( $create === '' ) {
        echo '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;redirect=' . $redirect . '&amp;action=enrollments" class="button-secondary" title="' . __('back to the course','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a>';
    }
    else {
        echo '<a href="admin.php?page=teachpress/teachpress.php&amp;course_id=' . $course_id . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;redirect=' . $redirect . '&amp;action=list" class="button-secondary" title="' . __('back to the course','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a>';
        
    }

    if ( $create === '' ) { ?>
   <h2><?php _e('Create attendance list','teachpress'); ?></h2>
   <table class="form-table" style="width:600px;">
      <thead>
       <tr>
         <th><label for="sort"><?php _e('Sort after','teachpress'); ?></label></th>
         <th>
            <select name="sort" id="sort">
               <option value="1"><?php _e('Last name','teachpress'); ?></option>
            </select>
         </th>
      </tr>
      <tr>
         <th style="width:160px;"><label for="number"><?php _e('Number of free columns','teachpress'); ?></label></th>
         <th>
            <select name="number" id="number">
               <?php
               for ($i = 0; $i <= 15; $i++) {
                  if ($i === 7) {
                     echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                  }
                  else {
                     echo '<option value="' . $i . '">' . $i . '</option>';
                  }	
               } ?>
            </select>
         </th>
      </tr>
      <tr>
         <th><label for="extra_fields"><?php _e('Additional columns','teachpress'); ?></label></th>
         <th>
             <select name="extra_fields[]" id="extra_fields" multiple="multiple" style="min-height: 200px;">
                <?php
                $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
                foreach ($fields as $row) {
                    $data = tp_db_helpers::extract_column_data($row->value);
                    echo '<option value="' . $row->variable . '">' . $data['title'] . '</option>';
                }
                ?>
             </select>
         </th>
      </tr>
      </thead>
   </table>
   <p><input name="create" type="submit" class="button-primary" value="<?php _e('Create','teachpress'); ?>"/></p>
    <?php
    }
    else {
        tp_create_attendance_list($course_id, $number, $extra_fields);
    }

    echo '</form>';
    echo '</div>';
}

/**
 * Creates an attendance list
 * @param int $course_id            ID of the course
 * @param int $number               number of free columns
 * @param string[] $extra_fields    An array of field_names which are available
 * @since 4.3.0
 */
function tp_create_attendance_list($course_id, $number, $extra_fields) {
    $row = tp_courses::get_course($course_id);
    // define course name
    if ($row->parent != 0) {
       $parent_name = tp_courses::get_course_data($row->parent, 'name');
       // if parent_name == child name
       if ($parent_name == $row->name) {
           $parent_name = "";
       }
    }
    else {
       $parent_name = "";
    }
    
    echo '<h2>' . $parent_name . ' ' . $row->name . ' ' . $row->semester . '</h2>';
    echo '<div style="width:700px; padding-bottom:10px;">';
    echo '<table border="1" cellspacing="0" cellpadding="0" class="tp_print">';
    echo '<tr>';
    echo '<th>' . __('Lecturer','teachpress') . '</th>';
    echo '<td>' . $row->lecturer . '</td>';
    echo '<th>' . __('Date','teachpress') . '</th>';
    echo '<td>' . $row->date . '</td>';
    echo '<th>' . __('Room','teachpress') . '</th>';
    echo '<td>' . $row->room . '</td>';
    echo '</tr>';
    echo '</table>';        
    echo '</div>';
    
    echo '<table border="1" cellpadding="0" cellspacing="0" class="tp_print" width="100%">';
    echo '<thead>';
    echo '<tr style="border-collapse: collapse; border: 1px solid black;">';
    echo '<th width="20" height="100">&nbsp;</th>';
    echo '<th width="250">' . __('Name','teachpress') . '</th>';
    echo '<th width="125">' . __('User account','teachpress') . '</th>';
    $max = count($extra_fields);
    for ($i = 0; $i < $max; $i++) {
        $field_values = get_tp_option($extra_fields[$i], 'teachpress_stud');
        $data = tp_db_helpers::extract_column_data($field_values);
        echo '<th>' . $data['title'] . '</th>';
    }
    for ($i = 1; $i <= $number; $i++ ) {
        echo '<th>&nbsp;</th>';
    }
    
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
          
    $count = 1;
    
    
    $sql = tp_courses::get_signups( array('course_id' => $course_id, 'order' => 'st.lastname', 'waitinglist' => 0, 'output_type' => ARRAY_A ) );
    foreach($sql as $row3) {
        echo '<tr>';
        echo '<td>' . $count . '</td>';
        echo '<td>' . $row3['lastname'] . ', ' . $row3['firstname'] . '</td>';
        echo '<td>' . $row3['userlogin'] . '</td>';
        $max = count($extra_fields);
        for ($i = 0; $i < $max; $i++) {
            echo '<td>' . $row3[$extra_fields[$i]] . '</td>';
        }
        for ( $i= 1; $i <= $number; $i++ ) {
            echo '<td>&nbsp;</td>';
        }
        echo '</tr>';
        $count++;
    }
    echo '</tbody>';
    echo '</table>';
}