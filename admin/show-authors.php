<?php
/**
 * This file contains all functions for displaying the show_authors page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * Defines the screen options for show_authors_page
 * @global int $tp_admin_show_authors_page
 * @since 5.0.0
 */
function tp_show_authors_page_screen_options(){
    global $tp_admin_show_authors_page;
    $screen = get_current_screen();

    if( !is_object($screen) || $screen->id != $tp_admin_show_authors_page ) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_authors_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * teachpress show_authors_page
 * @since 5.0.0
 */
function tp_show_authors_page () {
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
    TP_Show_Authors_Page::get_page($action);
    
}

/**
 * This class contains all functions for the show authors page
 * @since 6.0.0
 */
class TP_Show_Authors_Page {
    
    /**
     * This function executes all actions for this page
     * @param string $action        The current action
     * @param array $checkbox       The checkbox array
     * @param string $page          The current page 
     * @param string $search        The search string
     * @param int $curr_page        The current page in the page menu
     * @since 6.0.0
     * @access private
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
        if ( isset( $_GET['delete_ok'] ) ) {
            TP_Authors::delete_authors($checkbox);
            get_tp_message( __('Removing successful','teachpress') );
        }
    }
    
    /**
     * This function prints the page
     * @param string $action
     * @since 6.0.0
     */
    public static function get_page ($action) {
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
        $page = 'teachpress/authors.php';
        
        // Get screen options
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }

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
        
        echo '<div class="wrap" style="max-width:800px;">';
        echo '<h2>' . __('Authors','teachpress') . '</h2>';
        echo '<form id="form1" name="form1" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<input name="page" type="hidden" value="' . $page . '" />';

        // actions
        self::actions($action, $checkbox, $page, $search, $curr_page);

        // Searchbox
        echo '<div id="tp_searchbox">';
        if ($search != "") {
            echo '<a href="admin.php?page=teachpress/authors.php" class="tp_search_cancel" title="' . __('Cancel the search','teachpress') . '">X</a>';
        }
        echo '<input type="search" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>';
        echo '<input type="submit" name="button" id="button" value="' . __('Search','teachpress') . '" class="button-secondary"/>';
        echo '</div>';
        // END Searchbox
        
        // Tablenav actions
        echo '<div class="tablenav" style="padding-bottom:5px;">';
        echo '<div class="alignleft actions">';
        echo '<select name="action1">';
        echo '<option value="">- ' . __('Bulk actions','teachpress') . ' -</option>';
        echo '<option value="delete">' . __('Delete','teachpress') . '</option>';
        echo '</select>';
        echo '<input name="OK" value="OK" type="submit" class="button-secondary"/>';
        echo '</div>';
        $test = TP_Authors::get_authors( array( 'count' => true, 'search' => $search, 'include_editors' => true ) );
        $args = array('number_entries' => $test,
                          'entries_per_page' => $number_messages,
                          'current_page' => $curr_page,
                          'entry_limit' => $entry_limit,
                          'page_link' => "admin.php?page=$page&amp;",
                          'link_attributes' => "search=$search");
        echo tp_page_menu($args);
        echo '</div>';
        // END Tablenav actions

        // Main table
        echo '<table class="widefat">';
        echo '<thead id="tp_authors_table_header">';
        echo '<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" /></td>';
        echo '<th>' . __('Name','teachpress') . '</th>';
        echo '<th>' . __('ID','teachpress') . '</th>';
        echo '<th>' . __('Number publications','teachpress') . '</th>';
        echo '</thead>';
        echo '<tbody id="tp_authors_table_content">';
        if ($test === 0) {
                echo '<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
            }
        else {
            self::get_table($action, $checkbox, $page, $search, $curr_page, $entry_limit, $number_messages);
        }
        echo '</tbody>';
        echo '</table>';
        // END Main Table

        // Tablenav actions
        echo '<div class="tablenav bottom">';
        echo '<div class="alignleft actions">';
        echo '<select name="action2">';
        echo '<option value="">- ' . __('Bulk actions','teachpress') . ' -</option>';
        echo '<option value="delete">' . __('Delete','teachpress') . '</option>';
        echo '</select>';
        echo '<input name="OK" value="OK" type="submit" class="button-secondary"/>';
        echo '</div>';
        echo '<div class="tablenav-pages" style="float:right;">'; 
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
        echo '</div>';
        echo '</div>';
        // END Tablenav actions

        echo '</form>';
        self::print_scripts();
        echo '</div>';
    }
    
    /**
     * Prints the authors table
     * @param type $action
     * @param type $checkbox
     * @param type $page
     * @param type $search
     * @param type $curr_page
     * @param type $entry_limit
     * @param type $number_messages
     * @since 6.0.0
     * @access private 
     */
    private static function get_table ($action, $checkbox, $page, $search, $curr_page, $entry_limit, $number_messages) {
        $class_alternate = true;
        $row = TP_Authors::count_authors($search, $entry_limit . ',' . $number_messages);
        foreach ( $row as $row ) {
            $checked = '';
            if ( $class_alternate === true ) {
                $tr_class = 'alternate';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            if ( $action === "delete") { 
                for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                    if ( $row['author_id'] == $checkbox[$k] ) { $checked = 'checked="checked" '; } 
                } 
            }
            echo '<tr class="' . $tr_class . '" id="resultbox_' . $row['author_id'] . '">';
            echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' type="checkbox" value="' . $row['author_id'] . '"></th>';
            echo '<td><a class="tp_show_pub_info" author_id="' . $row['author_id'] . '" title="' . __('Show publications','teachpress') . '" style_class="' . $tr_class . '" style="cursor:pointer;"><b>' . TP_Bibtex::parse_author_simple($row['name']) . '</b></a>';
                echo '<div class="tp_row_actions">';
                echo '<a class="tp_row_delete" href="admin.php?page=' . $page . '&amp;checkbox%5B%5D=' . $row['author_id'] . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '&amp;action=delete' . '" title="' . __('Delete','teachpress') . '">' . __('Delete', 'teachpress') . '</a>';
                echo '</div>';
            echo '</td>';
            echo '<td>' . $row['author_id'] . '</td>';
            echo '<td>' . $row['count'] . '</td>';
            echo '<tr>';
        }
    }
    
    /**
     * Prints the js for the page
     * @since 6.0.0
     */
    private static function print_scripts () {
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($) {
                $(".tp_show_pub_info").click( function(){
                    var author_id = $(this).attr("author_id");
                    var tr_class = $(this).attr("style_class");
                    var tr = '#resultbox_' + author_id;
                    $.get(ajaxurl + "?action=teachpress&author_id=" + author_id, 
                    function(text){
                        var ret;
                        var current = $(tr).next();
                        current.attr('class', tr_class);
                        current.attr('id', 'pub_info_' + author_id);
                        ret = ret + '<td id="pub_details_' + author_id + '" colspan="4" style="padding-left:40px;"><b><?php echo __('Publications','teachpress') ;?></b><br />' + text + '</td>';
                        current.html(ret);
                        $('<a class="button-secondary" style="cursor:pointer;" onclick="javascript:teachpress_del_node(' + "'#pub_info_" + author_id + "'" + ');">Close</a>').appendTo('#pub_details_' + author_id);

                    });
                });

            });
        </script>
        <?php
    }
}