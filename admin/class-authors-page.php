<?php
/**
 * This class contains all functions for the authors page in the admin menu
 * @since 8.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */
class TP_Authors_Page {
    
    /**
     * Prints the page
     * @since 8.1
     */
    public static function init() {
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
        TP_Authors_Page::get_page($action);
    }


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
            TP_HTML::line( '<div class="teachpress_message teachpress_message_orange">
                <p class="teachpress_message_headline">' . esc_html__('Do you want to delete the selected items?','teachpress') . '</p>
                <p><input name="delete_ok" type="submit" class="button-secondary" value="' . esc_html__('Delete','teachpress') . '"/>
                <a href="admin.php?page=' . $page . '&search=' . $search . '&amp;limit=' . $curr_page . '"> ' . esc_html__('Cancel','teachpress') . '</a></p>
                </div>' );
        }
        // delete tags - part 2
        if ( isset( $_GET['delete_ok'] ) ) {
            TP_Authors_Page::check_nonce_field();
            TP_Authors::delete_authors($checkbox);
            get_tp_message( esc_html__('Removing successful','teachpress') );
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
        $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
        $only_zero = ( $filter === 'only_zero' ) ? true : false;
        $page = 'teachpress/authors.php';
        
        /** 
         * Screen options 
         */
        // items per page
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        
        // sorting
        $option = get_user_meta($user, 'tp_authors_sorting', true);
        $order = 'a.sort_name ASC, a.name ASC';
        
        if ( $option == 'firstname' ) {
            $order = 'a.name ASC, a.sort_name ASC';
        }
        if ( $option == 'number_desc' ) {
            $order = 'count DESC, a.name ASC, a.sort_name ASC';
        }
        if ( $option == 'number_asc' ) {
            $order = 'count ASC, a.name ASC, a.sort_name ASC';
        }

        // Handle limits
        $number_messages = $per_page;
        if (isset($_GET['limit'])) {
            $curr_page = intval($_GET['limit']) ;
            if ( $curr_page <= 0 ) {
                $curr_page = 1;
            }
            $entry_limit = ( $curr_page - 1 ) * $number_messages;
        }
        else {
            $entry_limit = 0;
            $curr_page = 1;
        }
        
        echo '<div class="wrap" style="max-width:900px;">';
        echo '<h2>' . esc_html__('Authors','teachpress') . '</h2>';
        echo '<form id="form1" name="form1" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<input name="page" type="hidden" value="' . esc_html($page) . '" />';
        wp_nonce_field( 'verify_teachpress_author_edit', 'tp_nonce', false, true );

        // actions
        self::actions($action, $checkbox, $page, $search, $curr_page);

        // Searchbox
        echo '<div id="tp_searchbox">';
        if ( $search != "" ) {
            echo '<a href="admin.php?page=teachpress/authors.php" class="tp_search_cancel" title="' . esc_html__('Cancel the search','teachpress') . '">X</a>';
        }
        echo '<input type="search" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>';
        echo '<input type="submit" name="button" id="button" value="' . esc_html__('Search','teachpress') . '" class="button-secondary"/>';
        echo '</div>';
        // END Searchbox
        
        // Tablenav actions
        echo '<div class="tablenav" style="padding-bottom:5px;">';
        echo '<div class="alignleft actions">';
        echo '<select name="action1">';
        echo '<option value="">- ' . esc_html__('Bulk actions','teachpress') . ' -</option>';
        echo '<option value="delete">' . esc_html__('Delete','teachpress') . '</option>';
        echo '</select>';
        echo '<select name="filter">';
        echo '<option>- ' . esc_html__('Select filter','teachpress') . ' -</option>';
        $selected = ( $only_zero === true ) ? 'selected="selected"' : '';
        TP_HTML::line( '<option value="only_zero"' . $selected . '>' . esc_html__('Occurrence = 0','teachpress') . '</option>' );
        echo '</select>';
        echo '<input name="OK" value="OK" type="submit" class="button-secondary"/>';
        echo '</div>';
        $test = TP_Authors::get_authors_occurence( [ 
                    'count'         => true, 
                    'search'        => $search, 
                    'only_zero'     => $only_zero
        ]);
        $args = [
                    'number_entries'    => $test,
                    'entries_per_page'  => $number_messages,
                    'current_page'      => $curr_page,
                    'entry_limit'       => $entry_limit,
                    'page_link'         => "admin.php?page=$page&amp;",
                    'link_attributes'   => "search=$search"];
        tp_page_menu($args, true);
        echo '</div>';
        // END Tablenav actions

        // Main table
        echo '<table class="widefat">';
        echo '<thead id="tp_authors_table_header">';
        echo '<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" /></td>';
        echo '<th>' . esc_html__('Name','teachpress') . '</th>';
        echo '<th>' . esc_html__('ID','teachpress') . '</th>';
        echo '<th>' . esc_html__('Number publications','teachpress') . '</th>';
        echo '</thead>';
        echo '<tbody id="tp_authors_table_content">';
        if ( intval($test) === 0 ) {
            echo '<tr><td colspan="4"><strong>' . esc_html__('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
            }
        else {
            
            $link = 'admin.php?page=' . $page . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '&amp;action=delete&amp;filter=' . $filter;
            $results = TP_Authors::get_authors_occurence(array(
                    'search'        => $search,
                    'limit'         => $entry_limit . ',' . $number_messages,
                    'order'         => $order,
                    'only_zero'     => $only_zero,
            ));
            self::get_table($results, $action, $checkbox, $link);
        }
        echo '</tbody>';
        echo '</table>';
        // END Main Table

        // Tablenav actions
        echo '<div class="tablenav bottom">';
        echo '<div class="tablenav-pages" style="float:right;">'; 
        if ( $test > $number_messages ) {
            $args = array(
                        'number_entries'      => $test,
                        'entries_per_page'    => $number_messages,
                        'current_page'        => $curr_page,
                        'entry_limit'         => $entry_limit,
                        'page_link'           => "admin.php?page=$page&amp;",
                        'link_attributes'     => "search=$search",
                        'mode'                => 'bottom');
            tp_page_menu($args, true);
        } 
        else {
           if ( $test === 1 ) {
              echo intval($test) . ' ' . esc_html__('entry','teachpress');
           }
           else {
              echo intval($test) . ' ' . esc_html__('entries','teachpress');
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
     * @param array $results
     * @param string action
     * @param array $checkbox
     * @param string link
     * @since 6.0.0
     * @access private 
     */
    private static function get_table ($results, $action, $checkbox, $link) {
        $class_alternate = true;
        
        foreach ( $results as $row ) {
            // Alternate line style
            if ( $class_alternate === true ) {
                $tr_class = 'alternate';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            $checked = '';
            if ( $action === "delete") { 
                $checked = in_array($row['author_id'], $checkbox ) ? 'checked="checked"' : '';
            }
            
            TP_HTML::line('<tr class="' . $tr_class . '" id="resultbox_' . $row['author_id'] . '">');
            TP_HTML::line('<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' value="' . $row['author_id'] . '"></th>');
            TP_HTML::line('<td><a class="tp_show_pub_info" author_id="' . $row['author_id'] . '" title="' . esc_html__('Show publications','teachpress') . '" style_class="' . $tr_class . '" style="cursor:pointer;"><b>' . TP_Bibtex::parse_author_simple($row['name']) . '</b></a>');
            
            // Row actions
            TP_HTML::line('<div class="tp_row_actions">');
            TP_HTML::line('<a class="tp_row_delete" href="' . $link . '&amp;checkbox%5B%5D=' . $row['author_id'] . '" title="' . esc_html__('Delete','teachpress') . '">' . esc_html__('Delete', 'teachpress') . '</a>');
            TP_HTML::line('</div>');
            // END Row actions
            
            TP_HTML::line('</td>');
            TP_HTML::line('<td>' . $row['author_id'] . '</td>');
            TP_HTML::line('<td>' . $row['count'] . '</td>');
            TP_HTML::line('<tr>');
        }
    }
    
    /**
     * Adds the screen options (items per page, authors_sorting)
     * @global string $tp_admin_show_authors_page
     * @since 8.1
     */
    public static function add_screen_options() {
        global $tp_admin_show_authors_page;
        $screen = get_current_screen();

        if( !is_object($screen) || $screen->id != $tp_admin_show_authors_page ) {
            return;
        }

        $args = [
            'label'     => esc_html__('Items per page', 'teachpress'),
            'default'   => 50,
            'option'    => 'tp_authors_per_page'
        ];
        add_screen_option( 'per_page', $args );

        $args = [
            'default'   => 'lastname',
            'option'    => 'tp_authors_sorting'
        ];
        add_screen_option( 'tp_authors_sorting', $args );
    }
    
    /**
     * Prints the custom screen options fo this page
     * @since 8.1
     */
    public static function print_screen_options() {
        $user = get_current_user_id();
        $option = get_user_meta($user, 'tp_authors_sorting', true);
        $value = ( $option ) ? $option : 'lastname';
        
        // Available options
        $options[] = [
            'key'   => 'firstname',
            'label' => esc_html__('First name', 'teachpress')
        ];
        $options[] = [
            'key'   => 'lastname',
            'label' => esc_html__('Last name', 'teachpress')
        ];
        $options[] = [
            'key'   => 'number_desc',
            'label' => esc_html__('Number of publications (Descending)', 'teachpress')
        ];
        $options[] = [
            'key'   => 'number_asc',
            'label' => esc_html__('Number of publications (Ascending)', 'teachpress')
        ];
        
        $r = '<label for="tp_authors_sorting"><b>' . esc_html__('Sorting', 'teachpress') . '</b></label><br/>';
        $r .= '<select name="tp_authors_sorting" id="tp_authors_sorting">';
        foreach ( $options as $row ) {
            $selected = ( $row['key'] == $value ) ? 'selected="selected"' : '';
            $r .= '<option value="' . $row['key'] . '" ' . $selected . '>' . $row['label'] . '</option>';
        }
        $r .= '</select>';
        return $r;
        
    }
    
    /**
     * Saves the custom screen options
     * @since 8.1
     */
    public static function save_screen_options () {
        $sorting = htmlspecialchars($_POST['tp_authors_sorting']);
        $values = ['firstname', 'lastname', 'number_desc', 'number_asc'];
        if ( in_array($sorting, $values) ) {
            
            update_user_meta(get_current_user_id(), 'tp_authors_sorting', $sorting);
        }
    }

    /**
     * Checks the nonce field of the form. If the check fails wp_die() will be executed
     * @since 9.0.5
     */
    private static function check_nonce_field () {
        if ( ! isset( $_GET['tp_nonce'] ) 
            || ! wp_verify_nonce( $_GET['tp_nonce'], 'verify_teachpress_author_edit' ) 
        ) {
           wp_die('teachPress error: This request could not be verified!');
           exit;
        }
    }

    /**
     * Prints js scripts for the page
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
                        ret = ret + '<td id="pub_details_' + author_id + '" colspan="4" style="padding-left:40px;"><b><?php echo esc_html__('Publications','teachpress') ;?></b><br />' + text + '</td>';
                        current.html(ret);
                        $('<a class="button-secondary" style="cursor:pointer;" onclick="javascript:teachpress_del_node(' + "'#pub_info_" + author_id + "'" + ');">Close</a>').appendTo('#pub_details_' + author_id);

                    });
                });

            });
        </script>
        <?php
    }
}
