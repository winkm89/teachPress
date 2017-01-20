<?php
/**
 * This file contains all functions for displaying the import_publications page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Add help tab for import page
 */
function tp_import_publication_page_help() {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_import_publication_page_help',
        'title'     => __('Import'),
        'content'   => '<p><strong>' . __('Import') . '</strong></p>
                        <p>' . __("Use the file upload or add your BibTeX entries directly in the textfield. Restrictions: teachPress can't convert not numeric month and day attributes.",'teachpress') . '</p>
                        <p>' . __('Supported file types', 'teachpress') . ': .txt, .bib</p>',
    ) );
}

/**
 * The controller for the import page of teachPress
 * @since 6.0.0
*/ 
function tp_show_import_publication_page() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $import_id = isset( $_GET['import_id'] ) ? intval($_GET['import_id']) : 0;
    if ( isset($_POST['tp_submit']) || isset($_POST['tp_bookmark']) || isset($_POST['tp_delete']) ) {
        tp_import_publication_page::import_actions($_POST);
    }
    else {
        $set_menu_1 = ( $tab === 'import' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_2 = ( $tab === 'export' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_3 = ( $tab === 'exist' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        echo '<div class="wrap">';
        echo '<h2>' . __('Publications','teachpress') . '</h2>';
        echo '<h3 class="nav-tab-wrapper"><a href="admin.php?page=teachpress/import.php&amp;tab=import" class="' . $set_menu_1 .'">' . __('Import') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=export" class="' . $set_menu_2 . '">' . __('Export') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=exist" class="' . $set_menu_3 . '">' . __('List of Imports') . '</a></h3>';
        
        // Import
        if ( $tab === '' || $tab === 'import' ) {
            tp_import_publication_page::import_tab($tab);
        }
        
        // Export
        if ( $tab === 'export' ) {
            tp_import_publication_page::export_tab();
        }
        
        // List of Imports
        if ( $tab === 'exist' ) {
            tp_import_publication_page::exist_tab($import_id);
        }
        
        echo '</div>';
        
     }
}

/**
 * This class contains function for generating the import_publication_page
 * @since 6.0.0
 */
class tp_import_publication_page {
    
    /**
     * This function executes all import action calls
     * @global object $current_user
     * @param array $post                   The $_POST array
     * @since 6.0.0
     * @access public
     */
    public static function import_actions ($post) {
        $current_user = wp_get_current_user();
        $tp_bookmark = isset( $post['tp_bookmark'] ) ? $post['tp_bookmark'] : '';
        $tp_delete = isset( $post['tp_delete'] ) ? $post['tp_delete'] : '';
        $checkbox = isset( $post['checkbox'] ) ? $post['checkbox'] : '';
        
        // add bookmarks
        if ( $tp_bookmark !== '' && $checkbox !== '' ) {
            $max = count($checkbox);
            for ( $i = 0; $i < $max; $i++ ) {
                tp_bookmarks::add_bookmark( intval($checkbox[$i]), $current_user->ID );
            }
            get_tp_message( __('Publications added to your list.','teachpress') );
        }
        
        // delete publication
        if ( $tp_delete !== '' && $checkbox !== '' ) {
            tp_publications::delete_publications($post['checkbox']);
            get_tp_message( __('Removing successful','teachpress') );
        }
        
        // error messages
        if ( ( $tp_bookmark !== '' || $tp_delete !== '' ) && $checkbox === '' ) {
            get_tp_message( __('Warning: No publication was selected.','teachpress') );
        }
        
        // import from textarea or file
        $file_name = isset($_FILES['file']['tmp_name']) ? htmlentities($_FILES['file']['tmp_name']) : '';
        $bibtex_area = isset($post['bibtex_area']) ? $post['bibtex_area'] : '';
        if ( $file_name !== '' ) {
            $file_type = substr(htmlentities($_FILES['file']['name']),-4,4);
            if ( substr($file_type,-4,4) !== '.txt' && substr($file_type,-4,4) !== '.bib' ) {
                get_tp_message(__('No suported file type','teachpress'));
                exit();
            }
        }
        
        if ( $file_name !== '' || $bibtex_area !== '' ) {
            if ( $file_name !== '' ) {
                $bibtex =  file_get_contents ( $file_name );
                // Check if string is utf8 or not
                if ( tp_bibtex::is_utf8($bibtex) === false ) {
                    $bibtex = utf8_encode($bibtex);
                }
            }
            else {
                $bibtex = $bibtex_area;
            }
            
            $settings = array(
                'keyword_separator' => htmlspecialchars($post['keyword_option']),
                'author_format' => htmlspecialchars($post['author_format']),
                'overwrite' => isset( $post['overwrite']) ? true : false
            );

            // echo $bibtex;
            // add publications to database
            $entries = tp_bibtex_import::init($bibtex, $settings);
        }
        // if there is no import
        else {
            $tp_entries = ( isset($post['tp_entries']) ) ? htmlspecialchars($post['tp_entries'] ) : '0';
            $entries = tp_publications::get_publications( array( 'include' => $tp_entries, 'output_type' => ARRAY_A ) );
        }
        tp_import_publication_page::show_results($entries);
    }
    
    /**
     * Shows the import form
     * @param string $tab
     * @since 6.0.0
     * @access public
    */
    public static function import_tab () {
        ?>
        <form id="tp_file" name="tp_file" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" method="post">
        <input type="hidden" name="page" value="teachpress/import.php"/>
        <div class="tp_postbody">
            <div class="tp_postcontent">
                <div style="text-align: center;">
                    <input name="file" id="upload_file" type="file" title="<?php _e('Choose a BibTeX file for upload','teachpress'); ?>" /> (<?php echo __('max file size','teachpress') . ': ' . ini_get('upload_max_filesize'); ?> )
                    <p style="text-align: center; font-weight: bold;"><?php _e('or','teachpress'); ?></p>
                </div>
                <textarea name="bibtex_area" id="bibtex_area" rows="20" style="width:100%;" title="<?php _e('Insert your BibTeX entries here','teachpress'); ?>"></textarea>
            </div>
            <div class="tp_postcontent_right">
                <div class="postbox">
                    <h3 class="tp_postbox"><?php _e('Import options','teachpress'); ?></h3>
                    <?php if ( get_tp_option('import_overwrite') === '1' ) { ?>
                    <div class="inside">
                        <p><strong><label for="overwrite"><?php _e('Overwrite Publications','teachpress'); ?></label></strong></p>
                        <?php echo tp_admin::get_checkbox('overwrite', __('Overwrite existing publications with a similar BibTeX key','teachpress'), ''); ?>
                    </div>
                    <?php } ?>
                    <div id="major-publishing-actions" style="text-align: center;">
                        <input name="tp_submit" type="submit" class="button-primary" value="<?php _e('Import'); ?>"/>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="tp_postbox"><?php _e('Data options','teachpress'); ?></h3>
                    <div class="inside">
                        <p><strong><label for="author_format_0"><?php _e('Author/Editor Format','teachpress'); ?></label></strong></p>
                        <label>
                            <input type="radio" name="author_format" value="default" id="author_format_0" checked="checked" />
                            Firstname1 Lastname1 and Firstname2 Lastname2 and ...
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="lastfirst" id="author_format_1" />
                            Lastname1, Firstname1 and Lastname2, Firstname2 and ...
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="dynamic" id="author_format_1" />
                            <?php _e('Dynamic detection','teachpress');?>
                        </label>
                        <br />
                        <p><strong><label for="keyword_option"><?php _e('Keyword Separator','teachpress'); ?></label></strong></p>
                        <input type="text" name="keyword_option" id="keyword_option" title="<?php _e('Keyword Separator','teachpress'); ?>" value="," size="3"/>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php
    }
    
    /**
     * Shows the import results
     * @param array $entries
     * @param string mode
     * @since 6.0.0
    */
    public static function show_results($entries, $mode = 'history') {

        // WordPress User informations
        $current_user = wp_get_current_user();

        echo '<div class="wrap">';
        if ( $mode !== 'history' ) {
            echo '<p><a href="admin.php?page=teachpress/import.php" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
        }
        echo '<h2>' . __('Imported Publications','teachpress') . '</h2>';
        echo '<form id="import_publications" name="import_publications" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<p><input type="submit" class="button-primary" name="tp_bookmark" value="' . __('Add to your own list','teachpress') . '"/> <input type="submit" class="button-secondary" name="tp_delete" value="' . __('Delete','teachpress') . '"</p>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="' . "teachpress_checkboxes('checkbox','tp_check_all');" . '" /></td>';
        echo '<th>' . __('Title','teachpress') . '</th>';
        echo '<th>' . __('ID') . '</th>';
        echo '<th>' . __('Type') . '</th>';
        echo '<th>' . __('Author(s)','teachpress') . '</th>';
        echo '<th>' . __('Year','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $array_id = '';
        if ( count($entries) === 0 ) {
            echo '<td colspan="6"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td>';
        }
        foreach ( $entries as $entry ) {
            $value = ( isset($_POST['tp_submit']) && isset ($_POST['bibtex_area']) ) ? intval($entry['entry_id']) : intval($entry['pub_id']);
            $author = ( array_key_exists('author', $entry) === true ) ? $entry['author'] : '';
            echo '<tr>';
            if ( tp_bookmarks::bookmark_exists($value, $current_user->ID) === true ) {
                echo '<th></th>';
            }
            else {
                echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" value="' . $value . '"/></th>';
            }
            echo '<td><a href="admin.php?page=teachpress/addpublications.php&amp;pub_id=' . $value . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" target="_blank"><strong>' . $entry['title'] . '</strong></a></td>';
            echo '<td>' . $value . '</td>';
            echo '<td>' . tp_translate_pub_type( $entry['type'] ) . '</td>';
            echo '<td>' . $author . '</td>';
            echo '<td>' . $entry['year'] . '</td>';
            echo '</tr>';
            $array_id .= $value . ',';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<input type="hidden" name="tp_entries" value="' . substr($array_id, 0, -1) . '"/>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Displays the export tab of the import page
     * @since 6.0.0
     * @access public
     */
    public static function export_tab () {
        ?>
        <form id="tp_export" name="tp_export" action="<?php echo home_url(); ?>" method="get">
            <input name="feed" type="hidden" value="tp_export"/>
            <input name="type" type="hidden" value="pub"/>
        <table class="form-table">
            <tr>
                <th style="width: 150px;">
                    <label for="tp_user"><?php _e('Publications by user','teachpress'); ?></label>
                </th>
                <td>
                    <select name="tp_user" id="tp_user">
                        <option value="all"><?php _e('All','teachpress'); ?></option>
                        <?php
                        $row = tp_publications::get_pub_users();
                        foreach($row as $row) {
                            $user_info = get_userdata($row->user);
                            if ( $user_info != false ) { 
                                echo '<option value="' . $user_info->ID . '">' . $user_info->display_name . '</option>';
                            }
                        }
                        ?>
                    </select>
                  </td>
            </tr>
            <tr>
                <th style="width: 150px;"><label for="tp_format"><?php _e('Format'); ?></label></th>
                <td>
                    <select name="tp_format" id="tp_format">
                        <option value="bib">BibTeX (.bib)</option>
                        <option value="txt">BibTeX (.txt)</option>
                        <option value="rss">RSS (2.0)</option>
                        <option value="rtf">RTF</option>
                    </select>
                </td>
            </tr>
        </table>
        <p><input name="tp_submit_2" type="submit" class="button-primary" value="<?php _e('Export'); ?>"/></p>
        </form>
        <?php
    }
    
     /**
     * Displays the exist tab of the import page
     * @param int $import_id    The ID of the import 
     * @since 6.1.0
     * @access public
     */
    public static function exist_tab ($import_id = 0) {
        
        // Load data
        $wp_id = get_current_user_id();
        $list = tp_publication_imports::get_imports($wp_id);
        $users = get_users();
        $publications_count = tp_publication_imports::count_publications();
        
        // Generate user list
        $user_list = array();
        foreach ( $users as $user ) {
            $user_list[$user->ID] = $user->display_name;
        }
        
        // Generate number list
        $number_list = array();
        foreach ( $publications_count as $row ) {
            $number_list[$row['import_id']] = $row['number'];
        }
        
        // Show a list of available imports
        if ( $import_id === 0 ) {
            echo '<h3>' . __('List of imports','teachpress') . '</h3>';
            echo '<table class="widefat">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Date') . '</th>';
            echo '<th>' . __('User') . '</th>';
            echo '<th>' . __('Number of publications') . '</th>';
            echo '</tr>';
            echo '</thead>';
            //Print rows
            foreach ( $list as $row ) {
                $user_name = ( isset( $user_list[$row['wp_id']] ) ) ? $user_list[$row['wp_id']] : '';
                $number = ( isset( $number_list[$row['id']] ) ) ? $number_list[$row['id']] : 0;
                echo '<tr>';
                echo '<td><a href="admin.php?page=teachpress%2Fimport.php&amp;tab=exist&amp;import_id=' . $row['id'] . '">' . $row['date'] . '</a></td>';
                echo '<td>' . $user_name . '</td>';
                echo '<td>' . $number . '</td>';
                echo '</tr>';
            }
        } 
        
        // Show the list of publications, which were imported with the selected import
        else {
            $entries = tp_publications::get_publications( array( 'import_id' => $import_id, 'output_type' => ARRAY_A ) );
            tp_import_publication_page::show_results($entries, 'history');
        }
        echo '</table>';
    }
}