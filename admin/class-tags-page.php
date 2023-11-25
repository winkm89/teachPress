<?php
/**
 * This class contains all functions for the tags page in the admin menu
 * @since 5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */
class TP_Tags_Page {
    
    public static function init () {

        echo '<div class="wrap" style="max-width:900px;">';
        echo '<h2>' . __('Tags') . '</h2>';
        echo '<form id="form1" name="form1" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo wp_nonce_field( 'verify_teachpress_tags_edit', 'tp_nonce', false, false );
        echo '<input name="page" type="hidden" value="teachpress/tags.php" />';

        TP_Tags_Page::get_page();

        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Handle page actions
     * @param string $action
     * @param array $checkbox
     * @param string $page
     * @param sting $search
     * @param int $curr_page
     * @since 8.1
     */
    private static function actions ($action, $checkbox, $page, $search, $curr_page) {
        
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
            TP_Tags_Page::check_nonce_field();
            TP_Tags::delete_tags($checkbox);
            get_tp_message( __('Removing successful','teachpress') );
        }
        if ( isset( $_GET['tp_edit_tag_submit'] )) {
            TP_Tags_Page::check_nonce_field();
            $name = htmlspecialchars($_GET['tp_edit_tag_name']);
            $tag_id = intval($_GET['tp_edit_tag_id']);
            TP_Tags::edit_tag($tag_id, $name);
            get_tp_message( __('Tag saved','teachpress') );
        }
    }


    /**
     * Prints the page
     * @param string $search
     * @param int $entry_limit
     * @param int $number_messages
     * @param array $checkbox
     * @param string $action
     * @param int $page
     * @param int $curr_page
     * @since 6.0.0
     */
    public static function get_page () {
        /**
         * Form data
         */
        // Get screen options
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);
        
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
        $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
        $only_zero = ( $filter === 'only_zero' ) ? true : false;
        $page = 'teachpress/tags.php';
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
        
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
        
        // Actions
        self::actions($action, $checkbox, $page, $search, $curr_page);
        
        // Page Menu
        $test = TP_Tags::get_tags_occurence( array( 
                    'count'         => true, 
                    'search'        => $search, 
                    'only_zero'     => $only_zero
        ));
        
        // Search box
        TP_HTML::line('<div id="tp_searchbox">');
        if ( $search != "" ) { 
            TP_HTML::line('<a href="admin.php?page=teachpress/tags.php" class="tp_search_cancel" title="' . __('Cancel the search','teachpress') . '">X</a>');
        }
        TP_HTML::line('<input type="search" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>');
        TP_HTML::line('<input type="submit" name="button" id="button" value="' . __('Search','teachpress') . '" class="button-secondary"/>');
        TP_HTML::line('</div>');
        
        // Table actions
        TP_HTML::line('<div class="tablenav" style="padding-bottom:5px;">');
        TP_HTML::line('<div class="alignleft actions">');
        
        TP_HTML::line('<select name="action">');
        TP_HTML::line('<option value="">- ' . __('Bulk actions','teachpress') . '-</option>');
        TP_HTML::line('<option value="delete">'  . __('Delete','teachpress') . '</option>');
        TP_HTML::line('</select>');
        
        TP_HTML::line('<select name="filter">');
        TP_HTML::line('<option>- ' . __('Select filter','teachpress') . ' -</option>');
        $selected = ( $only_zero === true ) ? 'selected="selected"' : '';
        TP_HTML::line('<option value="only_zero"' . $selected . '>' . __('Occurrence = 0','teachpress') . '</option>');
        TP_HTML::line('</select>');
        
        TP_HTML::line('<input name="OK" value="OK" type="submit" class="button-secondary"/>');
        TP_HTML::div_close('alignleft actions');
        
        // Page nav
        $args = array('number_entries'  => $test,
                  'entries_per_page'    => $number_messages,
                  'current_page'        => $curr_page,
                  'entry_limit'         => $entry_limit,
                  'page_link'           => "admin.php?page=$page&amp;",
                  'link_attributes'     => "search=$search");
        echo tp_page_menu($args);
        
        TP_HTML::div_close('tablenav');
        
        // Table
        TP_HTML::line('<table border="0" cellspacing="0" cellpadding="0" class="widefat">');
        TP_HTML::line('<thead>');
        TP_HTML::line('<tr>');
        $onclick = "teachpress_checkboxes('checkbox[]','tp_check_all');";
        TP_HTML::line('<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="' . $onclick . '" /></td>');
        TP_HTML::line('<th>' . __('Name','teachpress') . '</th>');
        TP_HTML::line('<th>' . __('ID') . '</th>');
        TP_HTML::line('<th>' . __('Number','teachpress') . '</th>');
        TP_HTML::line('</tr>');
        TP_HTML::line('</thead>');
        
        if ( $test === 0 ) {
            TP_HTML::line('<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>');
        }
        else {
            $link = 'admin.php?page=' . $page . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '&amp;action=delete&amp;filter=' . $filter;
            $results = TP_Tags::get_tags_occurence( array(
                    'search'        => $search,
                    'limit'         => $entry_limit . ',' . $number_messages,
                    'order'         => 't.name ASC',
                    'only_zero'     => $only_zero,
            ) );
            TP_Tags_Page::get_table($results, $action, $checkbox, $link);
        } 

        TP_HTML::line('</table>');
        // END Table
  
        TP_HTML::div_open('tablenav bottom');
        TP_HTML::line('<div class="tablenav-pages" style="float:right;">');
        
        if ( $test > $number_messages ) {
            $args = array('number_entries'  => $test,
                      'entries_per_page'    => $number_messages,
                      'current_page'        => $curr_page,
                      'entry_limit'         => $entry_limit,
                      'page_link'           => "admin.php?page=$page&amp;",
                      'link_attributes'     => "search=$search",
                      'mode'                => 'bottom');
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
        TP_HTML::div_close('tablenav-pages');
        TP_HTML::div_close('tablenav bottom');
    }
    
    /**
     * Prints a single table row for the table
     * @param array $results
     * @param string action
     * @param array $checkbox
     * @param string link
     * @since 6.0.0
     */
    private static function get_table ($results, $action, $checkbox, $link) {
        $class_alternate = true;
        
        foreach ($results as $row) {
            // Alternate line style
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            TP_HTML::line('<tr ' . $tr_class . '>');
            $checked = '';
            if ( $action === "delete") { 
                $checked = in_array($row['tag_id'], $checkbox ) ? 'checked="checked"' : '';
            }
            TP_HTML::line('<th class="check-column"><input name="checkbox[]" class="tp_checkbox" ' . $checked . ' type="checkbox" value="' . $row['tag_id'] . '"></th>');
            TP_HTML::line('<td id="tp_tag_row_' . $row['tag_id'] . '">');
            TP_HTML::line( '<a onclick="teachpress_editTags(' . "'" . $row['tag_id'] . "'" . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;"><strong>' . stripslashes($row['name']) . '</strong></a><input type="hidden" id="tp_tag_row_name_' . $row['tag_id'] . '" value="' . stripslashes($row['name']) . '"/>');
            
            // Row actions
            TP_HTML::line( '<div class="tp_row_actions">');
            TP_HTML::line( '<a onclick="teachpress_editTags(' . "'" . $row['tag_id'] . "'" . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;">' . __('Edit', 'teachpress') . '</a> | <a href="admin.php?page=publications.php&amp;tag=' . $row['tag_id'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . __('Publications','teachpress') . '</a> | <a class="tp_row_delete" href="' . $link . '&amp;checkbox%5B%5D=' . $row['tag_id'] . '" title="' . __('Delete','teachpress') . '">' . __('Delete', 'teachpress') . '</a>');
            TP_HTML::line('</div>');
            // END Row actions
            
            TP_HTML::line('</td>');
            TP_HTML::line('<td>' . $row['tag_id'] . '</td>');
            TP_HTML::line('<td>' . $row['count'] . '</td>');
            TP_HTML::line('</tr>');
            
        }
    }
    
    /**
     * Checks the nonce field of the form. If the check fails wp_die() will be executed
     * @since 9.0.5
     */
    private static function check_nonce_field () {
        if ( ! isset( $_GET['tp_nonce'] ) 
            || ! wp_verify_nonce( $_GET['tp_nonce'], 'verify_teachpress_tags_edit' ) 
        ) {
           wp_die('teachPress error: This request could not be verified!');
           exit;
        }
    }
    
    /**
     * Adds the screen options
     * @global string $tp_admin_edit_tags_page
     * @since 8.1
     */
    public static function add_screen_options () {
        global $tp_admin_edit_tags_page;
        $screen = get_current_screen();

        if( !is_object($screen) || $screen->id != $tp_admin_edit_tags_page ) {
            return;
        }

        $args = array(
            'label' => __('Items per page', 'teachpress'),
            'default' => 50,
            'option' => 'tp_tags_per_page'
        );
        add_screen_option( 'per_page', $args );
    }
    
}