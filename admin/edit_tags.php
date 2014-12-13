<?php
/**
 * This file contains all functions for displaying the edit_tags page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Defines the screen options for edit_tags page
 * @global object $tp_admin_edit_tags_page
 */
function tp_edit_tags_page_screen_options () {
    global $tp_admin_edit_tags_page;
    $screen = get_current_screen();
 
    if(!is_object($screen) || $screen->id != $tp_admin_edit_tags_page) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_tags_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * Tag management page
 * @since 5.0.0
 */ 
function tp_tags_page(){
    // Get screen options
    $user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }
    ?> 
    <div class="wrap" style="max-width:700px;">
    <form id="form1" name="form1" method="get" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <input name="page" type="hidden" value="teachpress/tags.php" />
    <?php
    // form data
    $action = '';
    if ( isset( $_GET['action1'] ) && $_GET['action1'] !== '' ) {
        $action = htmlspecialchars($_GET['action1']);
    }
    if ( isset( $_GET['action2'] ) && $_GET['action2'] !== '' ) {
        $action = htmlspecialchars($_GET['action2']);
    }
    if ( isset( $_GET['action'] ) && $_GET['action'] !== '' ) {
        $action = htmlspecialchars($_GET['action']);
    }
    
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
    $page = 'teachpress/tags.php';
    
    // Handle limits
    $number_messages = $per_page;
    if (isset($_GET['limit'])) {
        $curr_page = (int)$_GET['limit'] ;
        if ( $curr_page <= 0 ) {
            $curr_page = 1;
        }
        $entry_limit = ( $curr_page - 1 ) * $number_messages;
    }
    else {
        $entry_limit = 0;
        $curr_page = 1;
    }

    // actions
    // Delete tags - part 1
    if ( $action === 'delete' ) {
        echo '<div class="teachpress_message teachpress_message_orange">
            <p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>
            <p><input name="delete_ok" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/>
            <a href="admin.php?page=' . $page . '&search=' . $search . '&amp;limit=' . $curr_page . '"> ' . __('Cancel','teachpress') . '</a></p>
            </div>';
    }
    // delete tags - part 2
    if ( isset($_GET['delete_ok']) ) {
        tp_tags::delete_tags($checkbox);
        get_tp_message( __('Removing successful','teachpress') );
    }
    if ( isset( $_GET['tp_edit_tag_submit'] )) {
        $name = htmlspecialchars($_GET['tp_edit_tag_name']);
        $tag_id = intval($_GET['tp_edit_tag_id']);
        tp_tags::edit_tag($tag_id, $name);
        get_tp_message( __('Tag saved','teachpress') );
    }
    
    ?>
    <h2><?php _e('Tags'); ?></h2>
    <div id="searchbox" style="float:right; padding-bottom:10px;">
            <?php if ($search != "") { ?><a href="admin.php?page=teachpress/tags.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a><?php } ?>
        <input type="search" name="search" id="pub_search_field" value="<?php echo stripslashes($search); ?>"/>
        <input type="submit" name="button" id="button" value="<?php _e('Search','teachpress'); ?>" class="button-secondary"/>
    </div>
    <div class="tablenav" style="padding-bottom:5px;"> 
        <select name="action1">
            <option value="">- <?php _e('Bulk actions','teachpress'); ?> -</option>
            <option value="delete"><?php _e('Delete','teachpress'); ?></option>
        </select>
        <input name="OK" value="OK" type="submit" class="button-secondary"/>
        <?php
        // Page Menu
        $test = tp_tags::get_tags( array( 'count' => true, 'search' => $search ) );
        $args = array('number_entries' => $test,
                      'entries_per_page' => $number_messages,
                      'current_page' => $curr_page,
                      'entry_limit' => $entry_limit,
                      'page_link' => "admin.php?page=$page&amp;",
                      'link_attributes' => "search=$search");
        echo tp_page_menu($args);
        ?>
    </div>
    <div style="width:700px;">
    <table border="0" cellspacing="0" cellpadding="0" class="widefat">
        <thead>
        <tr>
            <th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" /></th>
            <th><?php _e('Name','teachpress'); ?></th>
            <th><?php _e('ID'); ?></th>
            <th><?php _e('Number','teachpress'); ?></th>
        </tr>
        </thead> 
        <?php
        if ($test === 0) {
            echo '<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }
        else {
            $class_alternate = true;
            $row = tp_tags::count_tags($search, $entry_limit . ',' . $number_messages);
            
            foreach ($row as $row) {
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
                $str = "'";
                if ( $action === "delete") { 
                    for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                        if ( $row['tag_id'] == $checkbox[$k] ) { $checked = 'checked="checked" '; } 
                    } 
                }
                echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' type="checkbox" value="' . $row['tag_id'] . '"></th>';
                echo '<td id="tp_tag_row_' . $row['tag_id'] . '">';
                echo '<a onclick="teachpress_editTags(' . $str . $row['tag_id'] . $str . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;"><strong>' . stripslashes($row['name']) . '</strong></a><input type="hidden" id="tp_tag_row_name_' . $row['tag_id'] . '" value="' . stripslashes($row['name']) . '"/>';
                echo '<div class="tp_row_actions">';
                echo '<a onclick="teachpress_editTags(' . $str . $row['tag_id'] . $str . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;">' . __('Edit', 'teachpress') . '</a> | <a href="admin.php?page=publications.php&amp;tag=' . $row['tag_id'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . __('Publications','teachpress') . '</a> | <a class="tp_row_delete" href="admin.php?page=' . $page . '&amp;limit=' . $curr_page . '&search=' . $search . '&amp;checkbox%5B%5D=' . $row['tag_id'] . '&amp;action=delete' . '" title="' . __('Delete','teachpress') . '">' . __('Delete', 'teachpress') . '</a>';
                echo '</div>';
                echo '</td>';
                echo '<td>' . $row['tag_id'] . '</td>';
                echo '<td>' . $row['count'] . '</td>';
                echo '</tr>';
            }
        } ?>
    </table>
    <div class="tablenav bottom">
        <div class="alignleft actions">
            <select name="action2">
                <option value="">- <?php _e('Bulk actions','teachpress'); ?> -</option>
                <option value="delete"><?php _e('Delete','teachpress'); ?></option>
            </select>
            <input name="OK" value="OK" type="submit" class="button-secondary"/>
        </div>
        <div class="tablenav-pages" style="float:right;">
        <?php 
        if ($test > $number_messages) {
            $args = array('number_entries' => $test,
                      'entries_per_page' => $number_messages,
                      'current_page' => $curr_page,
                      'entry_limit' => $entry_limit,
                      'page_link' => "admin.php?page=$page&amp;",
                      'link_attributes' => "search=$search",
                      'mode' => 'bottom');
            echo tp_page_menu($args);
        } 
        else {
           if ($test === 1) {
              echo $test . ' ' . __('entry','teachpress');
           }
           else {
              echo $test . ' ' . __('entries','teachpress');
           }
        }
        ?>
        </div>
    </div>
    </div>
    </form>
    </div>
<?php }