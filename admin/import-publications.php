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
    $screen->add_help_tab( [
        'id'        => 'tp_import_publication_page_help',
        'title'     => esc_html__('Import'),
        'content'   => '<p><strong>' . esc_html__('Import') . '</strong></p>
                        <p>' . esc_html__("Use the file upload or add your BibTeX entries directly in the textfield. Restrictions: teachPress can't convert not numeric month and day attributes.",'teachpress') . '</p>
                        <p>' . esc_html__('Supported file types', 'teachpress') . ': .txt, .bib</p>',
    ] );
}

/**
 * The controller for the import page of teachPress
 * @since 6.0.0
 */
function tp_show_import_publication_page() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $import_id = isset( $_GET['import_id'] ) ? intval($_GET['import_id']) : 0;
    $delete_import = isset ( $_GET['teachpress_delete_import'] ) ? $_GET['teachpress_delete_import'] : '';
    $checkbox = isset ( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
    $set_menu_1 = ( $tab === 'import' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
    $set_menu_2 = ( $tab === 'export' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
    $set_menu_3 = ( $tab === 'exist' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
    echo '<div class="wrap">';
    echo '<h2>' . esc_html__('Publications','teachpress') . '</h2>';
    TP_HTML::line( '<h3 class="nav-tab-wrapper"><a href="admin.php?page=teachpress/import.php&amp;tab=import" class="' . $set_menu_1 .'">' . esc_html__('Import') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=export" class="' . $set_menu_2 . '">' . esc_html__('Export') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=exist" class="' . $set_menu_3 . '">' . esc_html__('List of imports','teachpress') . '</a></h3>' );
    // For actions
    if ( isset($_POST['tp_submit']) || isset($_POST['tp_bookmark']) || isset($_POST['tp_delete']) ) {
        TP_Import_Publication_Page::import_actions($_POST);
    }
    else {

        // Import
        if ( $tab === '' || $tab === 'import' ) {
            TP_Import_Publication_Page::import_tab($tab);
        }

        // Export
        if ( $tab === 'export' ) {
            TP_Import_Publication_Page::export_tab();
        }

        // List of Imports
        if ( $tab === 'exist' && $import_id === 0 ) {
            TP_Import_Publication_Page::exist_tab($delete_import, $checkbox);
        }

        // Show the list of publications, which were imported with the selected import
        if ( $tab === 'exist' && $import_id !== 0 ) {
            $entries = TP_Publications::get_publications( array( 'import_id' => $import_id, 'output_type' => ARRAY_A ) );
            TP_Import_Publication_Page::show_results($entries, 'history');
        }

        echo '</div>';

    }
}

/**
 * This class contains function for generating the import_publication_page
 * @since 6.0.0
 */
class TP_Import_Publication_Page {

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
                TP_Bookmarks::add_bookmark( intval($checkbox[$i]), $current_user->ID );
            }
            get_tp_message( esc_html__('Publications added to your list.','teachpress') );
        }

        // delete publication
        if ( $tp_delete !== '' && $checkbox !== '' ) {
            TP_Publications::delete_publications($post['checkbox']);
            get_tp_message( esc_html__('Removing successful','teachpress') );
        }

        // error messages
        if ( ( $tp_bookmark !== '' || $tp_delete !== '' ) && $checkbox === '' ) {
            get_tp_message( esc_html__('Warning: No publication was selected.','teachpress') );
        }

        // import from textarea or file
        $file_name = isset($_FILES['file']['tmp_name']) ? htmlentities($_FILES['file']['tmp_name']) : '';
        $bibtex_area = isset($post['bibtex_area']) ? $post['bibtex_area'] : '';

        // Check file name
        if ( $file_name !== '' ) {
            $file_type = substr(htmlentities($_FILES['file']['name']),-4,4);
            if ( substr($file_type,-4,4) !== '.txt' && substr($file_type,-4,4) !== '.bib' ) {
                get_tp_message(esc_html__('No supported file type','teachpress'));
                exit();
            }
        }

        // if there is something to import
        if ( $file_name !== '' || $bibtex_area !== '' ) {
            // Check nonce field
            TP_Import_Publication_Page::check_nonce_field();
            
            if ( $file_name !== '' ) {
                $bibtex =  file_get_contents ( $file_name );
                // Check if string is utf8 or not
                if ( TP_Bibtex::is_utf8($bibtex) === false ) {
                    $bibtex = utf8_encode($bibtex);
                }
            }
            else {
                $bibtex = stripslashes($bibtex_area);
            }

            $settings = array(
                'keyword_separator' => htmlspecialchars($post['keyword_option']),
                'author_format'     => htmlspecialchars($post['author_format']),
                'overwrite'         => isset( $post['overwrite'] ) ? true : false,
                'ignore_tags'       => isset( $post['ignore_tags'] ) ? true : false,
            );

            // echo $bibtex;
            // add publications to database
            $entries = TP_Bibtex_Import::init($bibtex, $settings);
        }

        // import from PubMed
        elseif ( array_key_exists('tp_pmid', $post) && $post['tp_pmid'] !== '' ) {
            // Check nonce field
            TP_Import_Publication_Page::check_nonce_field();
            
            $settings = array(
                'overwrite'   => isset( $post['overwrite'] ),
                'ignore_tags' => isset( $post['ignore_tags'] ),
            );
            $entries = TP_PubMed_Import::init( $post['tp_pmid'], $settings );
        }

        // import from Crossref
        elseif ( array_key_exists('tp_crossref', $post) && $post['tp_crossref'] !== '' ) {
            // Check nonce field
            TP_Import_Publication_Page::check_nonce_field();
            
            $settings = array(
                'overwrite'   => isset( $post['overwrite'] ),
                'ignore_tags' => isset( $post['ignore_tags'] ),
            );
            $entries = TP_Crossref_Import::init(
                $post['tp_crossref'], $settings );
        }

        // if there is no import
        else {
            $tp_entries = ( isset($post['tp_entries']) ) ? htmlspecialchars($post['tp_entries'] ) : '0';
            $entries = TP_Publications::get_publications( array( 'include' => $tp_entries, 'output_type' => ARRAY_A ) );
        }
        TP_Import_Publication_Page::show_results($entries);
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
        
        <?php wp_nonce_field( 'verify_teachpress_import', 'tp_nonce', true, true ); ?>
        <input type="hidden" name="page" value="teachpress/import.php"/>
        <div class="tp_postbody">
            <div class="tp_postcontent">
                
                <div class="postbox">
                    <div class="inside">
                        
                        <h3><label><?php esc_html_e('Select an import type','teachpress'); ?></label></h3>
                        <select name="tp_import_type" id="tp_import_file" onchange="teachpress_importFields()" title="<?php esc_html_e('Select an import type','teachpress'); ?>">
                            <option value="bibtex">BibTeX</option>
                            <option value="doi">crossref.org (DOI)</option>
                            <option value="pmid">NCBI PubMed</option>
                        </select>
                        <hr style="margin-top: 20px; margin-bottom: 20px;"/>
                    
                        <!-- BibTex -->
                        <div id="div_import_bibtex"style="display:block;">

                            <div class="teachpress_message teachpress_message_blue">
                                <?php esc_html_e('Choose a BibTeX file for upload or insert your BibTeX entries into the textarea field','teachpress'); ?>
                            </div>
                            <input name="file" id="upload_file" type="file" title="<?php esc_html_e('Choose a BibTeX file for upload','teachpress'); ?>" /> (<?php echo esc_html__('max file size','teachpress') . ': ' . ini_get('upload_max_filesize'); ?> )
                            <p style="font-weight: bold;"><?php esc_html_e('or','teachpress'); ?></p>
                            <textarea name="bibtex_area" id="bibtex_area" rows="20" style="width:100%;" title="<?php esc_html_e('Insert your BibTeX entries here','teachpress'); ?>"></textarea>

                        </div>
            
                        <!-- PMID -->
                        <div id="div_import_pmid" style="display:none;">
                            <div class="teachpress_message teachpress_message_blue">
                                <?php esc_html_e('Insert a space-separated list of PubMed identifiers','teachpress'); ?> <?php esc_html_e('Please note','teachpress'); ?>: <a href="https://www.ncbi.nlm.nih.gov/home/about/policies" target="_blank">NCBI Website and Data Usage Policies and Disclaimers</a>
                            </div>
                            <label for="tp_pmid">PMID</label>
                            <input name="tp_pmid" id="tp_pmid" style="width:450px;" title="<?php esc_html_e('Insert a space-separated list of PubMed identifiers','teachpress'); ?>" type="text">
                        </div>
            
                        <!-- DOI -->
                        <div id="div_import_doi" style="display:none;">
                            <div class="teachpress_message teachpress_message_blue">
                                <?php esc_html_e('Insert a space-separated list of DOIs','teachpress'); ?>
                            </div>
                            <label for="tp_crossref">DOI</label>
                            <input name="tp_crossref" id="tp_crossref" style="width:450px;" title="<?php esc_html_e('Insert a space-separated list of DOIs','teachpress'); ?>" type="text">
                        </div>
                
                    </div>
                </div> 
                     
            </div>
            <div class="tp_postcontent_right">
                <div class="postbox">
                    <h3 class="tp_postbox"><?php esc_html_e('Import options','teachpress'); ?></h3>
                    <div class="inside">
                        <?php
                        // Overwrite option
                        if ( get_tp_option('import_overwrite') === '1' ) {
                            TP_Admin::get_checkbox(
                                'overwrite',
                                esc_html__('Update existing publications','teachpress'),
                                '',
                                esc_html__('If the bibtex key is similar with a publication in the database, teachPress updates this publication with the import information.','teachpress'),
                                false,
                                true );
                            echo '<br/>';
                        }

                        // Ignore tags option
                        TP_Admin::get_checkbox(
                                'ignore_tags',
                                esc_html__('Ignore Tags','teachpress'),
                                '',
                                esc_html__('Ignore tags or keywords in the import data.','teachpress'),
                                false,
                                true ); ?>
                    </div>
                    <div id="major-publishing-actions" style="text-align: center;">
                        <input name="tp_submit" type="submit" class="button-primary" value="<?php esc_html_e('Import'); ?>"/>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="tp_postbox"><?php esc_html_e('Data options','teachpress'); ?></h3>
                    <div class="inside">
                        <p><strong><label for="author_format_0"><?php esc_html_e('Author/Editor Format','teachpress'); ?></label></strong></p>
                        <label>
                            <input type="radio" name="author_format" value="dynamic" id="author_format_1" checked="checked" />
                            <?php esc_html_e('Dynamic detection','teachpress');?>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="default" id="author_format_0" />
                            Firstname1 Lastname1 and Firstname2 Lastname2 and ...
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="lastfirst" id="author_format_2" />
                            Lastname1, Firstname1 and Lastname2, Firstname2 and ...
                        </label>
                        <br />
                        <p><strong><label for="keyword_option"><?php esc_html_e('Keyword Separator','teachpress'); ?></label></strong></p>
                        <input type="text" name="keyword_option" id="keyword_option" title="<?php esc_html_e('Keyword Separator','teachpress'); ?>" value="," size="3"/>
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
     * @param string $mode
     * @since 6.0.0
     */
    public static function show_results($entries, $mode = 'history') {
        
        if ( !is_array($entries) ) {
            $entries = [];
        }

        // WordPress User information
        $current_user = wp_get_current_user();

        // Debug info
        if ( TEACHPRESS_DEBUG === true ) {
            global $wpdb;
            get_tp_message('Queries: ' . $wpdb->num_queries . ' | Time: ' . timer_stop() . 's');
        }

        echo '<div class="wrap">';
        if ( $mode !== 'history' ) {
            echo '<p><a href="admin.php?page=teachpress/import.php" class="button-secondary">&larr; ' . esc_html__('Back','teachpress') . '</a></p>';
        }
        echo '<h3>' . esc_html__('Imported Publications','teachpress') . '</h3>';
        echo '<form id="import_publications" name="import_publications" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<p><input type="submit" class="button-primary" name="tp_bookmark" value="' . esc_html__('Add to your own list','teachpress') . '"/> <input type="submit" class="button-secondary" name="tp_delete" value="' . esc_html__('Delete','teachpress') . '"</p>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="' . "teachpress_checkboxes('checkbox','tp_check_all');" . '" /></td>';
        echo '<th>' . esc_html__('Title','teachpress') . '</th>';
        echo '<th>' . esc_html__('ID') . '</th>';
        echo '<th>' . esc_html__('Type') . '</th>';
        echo '<th>' . esc_html__('Author(s)','teachpress') . '</th>';
        echo '<th>' . esc_html__('Year','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $array_id = '';
        if ( count($entries) === 0 ) {
            echo '<td colspan="6"><strong>' . esc_html__('Sorry, no entries matched your criteria.','teachpress') . '</strong></td>';
        }
        foreach ( $entries as $entry ) {
            $value = ( isset($_POST['tp_submit']) && isset ($_POST['bibtex_area']) ) ? intval($entry['entry_id']) : intval($entry['pub_id']);
            $author = ( array_key_exists('author', $entry) === true ) ? $entry['author'] : '';
            echo '<tr>';
            if ( TP_Bookmarks::bookmark_exists($value, $current_user->ID) === true ) {
                echo '<th></th>';
            }
            else {
                TP_HTML::line( '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" value="' . $value . '"/></th>' );
            }
            TP_HTML::line( '<td><a href="admin.php?page=teachpress/addpublications.php&amp;pub_id=' . $value . '" class="teachpress_link" title="' . esc_html__('Click to edit','teachpress') . '" target="_blank"><strong>' . esc_html($entry['title']) . '</strong></a></td>' );
            TP_HTML::line( '<td>' . $value . '</td>' );
            TP_HTML::line( '<td>' . tp_translate_pub_type( $entry['type'] ) . '</td>' );
            TP_HTML::line( '<td>' . esc_html($author) . '</td>' );
            echo '<td>';
            if ( array_key_exists('year', $entry) ) {
                echo esc_html($entry['year']);
            }
            elseif ( array_key_exists('date', $entry) ) {
                echo esc_html($entry['date']);
            }
            echo '</td>';
            echo '</tr>';
            $array_id .= $value . ',';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<input type="hidden" name="tp_entries" value="' . esc_html( substr($array_id, 0, -1) ) . '"/>';
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
                    <label for="tp_user"><?php esc_html_e('Publications by user','teachpress'); ?></label>
                </th>
                <td>
                    <select name="tp_user" id="tp_user">
                        <option value="all"><?php esc_html_e('All','teachpress'); ?></option>
                        <?php
                        $row = TP_Publications::get_pub_users();
                        foreach($row as $row) {
                            $user_info = get_userdata($row->user);
                            if ( $user_info != false ) {
                                echo '<option value="' . intval($user_info->ID) . '">' . esc_html($user_info->display_name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                  </td>
            </tr>
            <tr>
                <th style="width: 150px;"><label for="tp_format"><?php esc_html_e('Format'); ?></label></th>
                <td>
                    <select name="tp_format" id="tp_format">
                        <option value="bib">BibTeX (.bib)</option>
                        <option value="txt">BibTeX (.txt)</option>
                        <option value="rss">RSS (2.0)</option>
                        <option value="rtf">RTF</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th style="width: 150px;"><?php esc_html_e('BibTeX Additions'); ?></th>
                <td>
                    <input name="tp_private_comment" id="tp_private_comment" type="checkbox"/> <label for="tp_private_comment"><?php esc_html_e('Include private comments'); ?></label><br/>
                    <?php
                    $checked = ( get_tp_option('convert_bibtex') == '1' ) ? 'checked="checked"' : '';
                    ?>
                    <input name="tp_convert_bibtex" id="tp_convert_bibtex" type="checkbox" <?php echo $checked; ?> /> <label for="tp_convert_bibtex"><?php esc_html_e('Try to convert utf-8 chars into BibTeX compatible ASCII strings'); ?></label>
                </td>
            </tr>
        </table>
        <p><input name="tp_submit_2" type="submit" class="button-primary" value="<?php esc_html_e('Export'); ?>"/></p>
        </form>
        <?php
    }

    /**
     * Displays the exist tab of the import page
     * @param string   $delete_import
     * @param array    $checkbox
     * @since 6.1.0
     * @access public
     */
    public static function exist_tab ($delete_import, $checkbox) {

        echo '<h3>' . esc_html__('List of imports','teachpress') . '</h3>';
        echo '<form name="search" method="get" action="admin.php">';
        echo '<input name="page" type="hidden" value="teachpress/import.php" />';
        echo '<input name="tab" type="hidden" value="exist" />';
        
        wp_nonce_field( 'verify_teachpress_import', 'tp_nonce', false, true );

        // Delete imports part 2
        if ( isset( $_GET['delete_import_ok']) ) {
            // Check nonce field
            TP_Import_Publication_Page::check_nonce_field_get();
        
            tp_publication_imports::delete_import($checkbox);
            $message = esc_html__('Removing successful','teachpress');
            get_tp_message($message);
        }

        // Delete imports part 1
        if ( $delete_import !== "" ) {
            echo '<div class="teachpress_message">
            <p class="teachpress_message_headline">' . esc_html__('Do you want to delete the selected items?','teachpress') . '</p>
            <p><input name="delete_import_ok" type="submit" class="button-primary" value="' . esc_html__('Delete','teachpress') . '"/>
            <a href="admin.php?page=teachpress/import.php&amp;tab=exist" class="button-secondary"> ' . esc_html__('Cancel','teachpress') . '</a></p>
            </div>';
        }

        // Default buttons
        else {
            echo '<div class="tablenav" style="padding-bottom:5px;">';
            echo '<input type="submit" name="teachpress_delete_import" value="' . esc_html__('Delete','teachpress') . '" id="doaction" class="button-secondary"/>';
            echo '</div>';
        }

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

        // List of imports
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<td class="check-column">
            <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes(' . "'checkbox[]','tp_check_all'" . ');" />';
        echo '</td>';
        echo '<th>' . esc_html__('Date') . '</th>';
        echo '<th>' . esc_html__('User','teachpress') . '</th>';
        echo '<th>' . esc_html__('Number publications','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';

        //Print rows
        $class_alternate = true;
        foreach ( $list as $row ) {
            $tr_class = ( $class_alternate === true ) ? 'class="alternate"' : '';
            $class_alternate = ( $class_alternate === true ) ? false : true;
            $user_name = ( isset( $user_list[$row['wp_id']] ) ) ? $user_list[$row['wp_id']] : '';
            $number = ( isset( $number_list[$row['id']] ) ) ? $number_list[$row['id']] : 0;
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column">
                <input type="checkbox" name="checkbox[]" id="checkbox" value="' . intval( $row['id'] ) . '"';
            if ( $delete_import !== "") {
                for( $i = 0; $i < count( $checkbox ); $i++ ) {
                    if ( $row['id'] == $checkbox[$i] ) { echo 'checked="checked"';}
                }
            }
            echo '/></th>';
            echo '<td><a href="admin.php?page=teachpress%2Fimport.php&amp;tab=exist&amp;import_id=' . intval( $row['id'] ) . '">' . esc_html($row['date']) . '</a></td>';
            echo '<td>' . esc_html($user_name) . '</td>';
            echo '<td>' . intval($number) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

    }
    
    /**
     * Checks the nonce field of the form. If the check fails wp_die() will be executed
     */
    public static function check_nonce_field () {
        if ( ! isset( $_POST['tp_nonce'] ) 
            || ! wp_verify_nonce( $_POST['tp_nonce'], 'verify_teachpress_import' ) 
        ) {
           wp_die('teachPress error: This request could not be verified!');
           exit;
        }
    }
    
    /**
     * Checks the nonce field of the form. If the check fails wp_die() will be executed
     */
    public static function check_nonce_field_get() {
        if ( ! isset( $_GET['tp_nonce'] ) 
            || ! wp_verify_nonce( $_GET['tp_nonce'], 'verify_teachpress_import' ) 
        ) {
           wp_die('teachPress error: This request could not be verified!');
           exit;
        }
    }
}
