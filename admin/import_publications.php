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
function tp_import_page_help_tab() {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_import_page_help',
        'title'     => __('Import'),
        'content'   => '<p><strong>' . __('Import') . '</strong></p>
                        <p>' . __("Use the file upload or add your BibTeX entries directly in the textfield. Restrictions: teachPress can't convert not numeric month and day attributes.",'teachpress') . '</p>
                        <p>' . __('Supported file types', 'teachpress') . ': .txt, .bib</p>',
    ) );
}

/**
 * The controller for the import page of teachPress
*/ 
function tp_import_page() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    if ( isset($_POST['tp_submit']) || isset($_POST['tp_bookmark']) || isset($_POST['tp_delete']) ) {
        global $current_user;
        get_currentuserinfo();
        $tp_bookmark = isset( $_POST['tp_bookmark'] ) ? $_POST['tp_bookmark'] : '';
        $tp_delete = isset($_POST['tp_delete']) ? $_POST['tp_delete'] : '';
        $checkbox = isset($_POST['checkbox']) ? $_POST['checkbox'] : '';
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
            tp_publications::delete_publications($_POST['checkbox']);
            get_tp_message( __('Removing successful','teachpress') );
        }
        // error messages
        if ( ( $tp_bookmark !== '' || $tp_delete !== '' ) && $checkbox === '' ) {
            get_tp_message( __('Warning: No publication was selected.','teachpress') );
        }
        
        // import from textarea or file
        $file_name = isset($_FILES['file']['tmp_name']) ? htmlentities($_FILES['file']['tmp_name']) : '';
        $bibtex_area = isset($_POST['bibtex_area']) ? $_POST['bibtex_area'] : '';
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
                'keyword_separator' => htmlspecialchars($_POST['keyword_option']),
                'author_format' => htmlspecialchars($_POST['author_format']),
                'overwrite' => isset( $_POST['overwrite']) ? true : false
            );

            // echo $bibtex;
            // add publications to database
            $entries = tp_bibtex::import_bibtex($bibtex, $settings);
        }
        // if there is no import
        else {
            $entries = tp_publications::get_publications( array( 'include' => htmlspecialchars($_POST['tp_entries'] ), 'output_type' => ARRAY_A ) );
        }
        tp_import_show_results($entries);
    }
    else {
        $set_menu_1 = ( $tab === 'import' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_2 = $tab === 'export' ? 'nav-tab nav-tab-active' : 'nav-tab';
        echo '<div class="wrap">';
        echo '<h2>' . __('Publications','teachpress') . '</h2>';
        echo '<h3 class="nav-tab-wrapper"><a href="admin.php?page=teachpress/import.php&amp;tab=import" class="' . $set_menu_1 .'">' . __('Import') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=export" class="' . $set_menu_2 . '">' . __('Export') . '</a></h3>';
        
        // Import
        if ($tab === '' || $tab === 'import') {
            tp_import_tab($tab);
        }    
        // Export
        if ($tab === 'export') {
            tp_export_tab();
        }
        ?>
        </div>
        <?php
        
     }
}

/**
 * Shows the import form
 * @param string $tab
 */
function tp_import_tab () {
    ?>
    <form id="tp_file" name="tp_file" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="teachpress/import.php"/>
    <div style="min-width:780px; width:100%;">
    <div style="width:24%; float:right; padding-left:1%; padding-bottom:1%;">
        <table class="widefat" style="margin-bottom:15px;">
            <thead>
                <tr>
                    <th><?php _e('Import options','teachpress'); ?></th>
                </tr>
                <tr>
                    <td>
                    <?php if ( get_tp_option('import_overwrite') === '1' ) { ?>
                    <p><strong><label for="overwrite"><?php _e('Overwrite Publications','teachpress'); ?></label></strong></p>
                    <?php echo tp_admin::get_checkbox('overwrite', __('Overwrite existing publications with a similar BibTeX key','teachpress'), ''); 
                    } ?>
                    </td>
                </tr>
                <tr style="text-align:center;">
                    <td style="height: 25px;">
                        <input name="tp_submit" type="submit" class="button-primary" value="<?php _e('Import'); ?>"/>
                    </td>
                </tr>
            </thead>    
        </table>
        <table class="widefat" style="margin-bottom:15px;">
            <thead>
                <tr>
                    <th><?php _e('Data options','teachpress'); ?></th>
                </tr>
                <tr>
                    <td>
                        <p><strong><label for="author_format_0"><?php _e('Author/Editor Format','teachpress'); ?></label></strong></p>
                        <label>
                         <input type="radio" name="author_format" value="1" id="author_format_0" checked="checked" />
                            Firstname1 Lastname1 and Firstname2 Lastname2 and ...</label>
                        <br />
                        <label>
                        <input type="radio" name="author_format" value="2" id="author_format_1" />
                            Lastname1, Firstname1 and Lastname2, Firstname2 and ...</label>
                        <br />
                        <p><strong><label for="keyword_option"><?php _e('Keyword Separator','teachpress'); ?></label></strong></p>
              <input type="text" name="keyword_option" id="keyword_option" title="<?php _e('Keyword Separator','teachpress'); ?>" value="," size="3"/>
                    </td>
                </tr>
            </thead>
        </table>
    </div>
    <div style="width:75%; float:left; padding-bottom:10px;">
        <div style="text-align: center;">
            <input name="file" id="upload_file" type="file" title="<?php _e('Choose a BibTeX file for upload','teachpress'); ?>" /> (<?php echo __('max file size','teachpress') . ': ' . ini_get('upload_max_filesize'); ?> )
            <p style="text-align: center; font-weight: bold;"><?php _e('or','teachpress'); ?></p>
        </div>
        <textarea name="bibtex_area" id="bibtex_area" rows="20" style="width:99%;" title="<?php _e('Insert your BibTeX entries here','teachpress'); ?>"></textarea>
    </div>
    </div>
    </form>
    <?php
}

/**
 * Shows the import results
 * @param array $entries
 */
function tp_import_show_results($entries) {
    // WordPress User informations
    global $current_user;
    get_currentuserinfo();
    
    echo '<div class="wrap">';
    echo '<p><a href="admin.php?page=teachpress/import.php" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
    echo '<h2>' . __('Imported Publications','teachpress') . '</h2>';
    echo '<form id="import_publications" name="import_publications" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    echo '<p><input type="submit" class="button-primary" name="tp_bookmark" value="' . __('Add to your own list','teachpress') . '"/> <input type="submit" class="button-secondary" name="tp_delete" value="' . __('Delete','teachpress') . '"</p>';
    echo '<table class="widefat">';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="' . "teachpress_checkboxes('checkbox','tp_check_all');" . '" /></th>';
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
        echo '<td><a href="admin.php?page=teachpress/addpublications.php&amp;pub_id=' . $value . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" target="_blank">' . $entry['title'] . '</a></td>';
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
 * @since 3.0.0
 */
function tp_export_tab() {
    ?>
    <form id="tp_export" name="tp_export" action="<?php echo plugins_url(); ?>/teachpress/export.php?type=pub" method="post">
    <table class="form-table">
         <tr>
              <th style="width: 150px;"><label for="tp_user"><?php _e('Publications by user','teachpress'); ?></label></th>
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