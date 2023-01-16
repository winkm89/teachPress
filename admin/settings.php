<?php
/**
 * This file contains all functions for displaying the settings page in admin menu
 * 
 * @package teachpress\admin\settings
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all functions for the teachpress settings page
 * @since 5.0.0
 */
class TP_Settings_Page {
    
    /**
     * Generates the settings page
     * @since 5.0.0
     */
    public static function load_page (){
        echo '<div class="wrap">';

        $site = 'options-general.php?page=teachpress/settings.php';
        
        // Tab selector
        $tab_list = ['general', 'publication_data', 'publication_templates', 'db_status' ];
        $tab_input = isset( $_GET['tab'] ) ? htmlspecialchars($_GET['tab']) : 'general';
        $tab = ( in_array($tab_input, $tab_list) ) ? $tab_input : '';

        // update dababase
        if ( isset($_GET['up']) ) {
            TP_Settings_Page::update_database($site);
        }

        // sync database
        if ( isset($_GET['sync']) ) {
            $sync = intval($_GET['sync']);
            if ( $sync === 1 ) {
                tp_db_sync('authors');
                TP_Settings_Page::update_database($site, false);
            }
            if ( $sync === 2 ) {
                tp_db_sync('stud_meta');
            }
        }

        // install database
        if ( isset($_GET['ins']) ) {
            tp_install();
        }
        
        // delete database
        if ( isset( $_GET['drop_tp'] ) || isset( $_GET['drop_tp_ok'] ) ) {
            TP_Settings_Page::delete_database();
        }

        // change general options
        if (isset( $_POST['einstellungen'] )) {
            TP_Settings_Page::change_general_options();
        }

        // delete settings
        if ( isset( $_GET['delete'] ) ) {
            TP_Options::delete_option($_GET['delete']);
            get_tp_message(__('Deleted', 'teachpress'));
        }
        
        // Delete data field
        if ( isset( $_GET['delete_field'] ) || isset( $_GET['delete_field_ok'] ) ) {
            TP_Settings_Page::delete_meta_fields($tab);
        }

        // add meta field options
        if ( isset($_POST['add_field']) ) {
            $table = 'teachpress_pub';
            TP_Settings_Page::add_meta_fields($table);
        }

        // test if database is installed
        TP_Admin::database_test();

        echo '<h2 style="padding-bottom:0px;">' . __('teachPress settings','teachpress') . '</h2>';

        // Site menu
        $set_menu_1 = ( $tab === 'general' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_6 = ( $tab === 'publication_data' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_7 = ( $tab === 'publication_templates' ) ? 'nav-tab nav-tab-active' : 'nav-tab';

        echo '<h3 class="nav-tab-wrapper">'; 
        echo '<a href="' . $site . '&amp;tab=general" class="' . $set_menu_1 . '">' . __('General','teachpress') . '</a>';
        echo '<a href="' . $site . '&amp;tab=publication_data" class="' . $set_menu_6 . '">' . __('Meta','teachpress') . ': ' . __('Publications','teachpress') . '</a>'; 
       
        echo '<a href="' . $site . '&amp;tab=publication_templates" class="' . $set_menu_7 . '">' . __('Templates','teachpress') . '</a>';
        echo '</h3>';

        echo '<form id="form1" name="form1" method="post" action="' . $site . '&amp;tab=' . $tab . '">';
        echo '<input name="page" type="hidden" value="teachpress/settings.php" />';
        echo '<input name="tab" type="hidden" value="<?php echo $tab; ?>" />';

        /* General */
        if ($tab === '' || $tab === 'general') {
            self::get_general_tab($site);
        }
        /* Meta data */
        if ( $tab === 'publication_data' ) {
            self::get_meta_tab($tab);
        }
        
        /* Templates */
        if ( $tab === 'publication_templates' ) {
            self::get_template_tab();
        }
        /* DB Status Tab */
        if ( $tab === 'db_status' ) {
            self::get_db_status_tab();
        }

        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Gets the about dialog for the general tab
     * @since 5.0.0
     * @access private
     */
    private static function get_about_dialog () {
        // img source: https://unsplash.com/photos/uG1jwfpCRhg
        echo '<div id="dialog" title="About">
                <div style="text-align: center;">
                <p><img src="' . plugins_url( 'images/misc/about.jpg', dirname( __FILE__ ) ) . '" style="border-radius: 130px; width: 250px; height: 250px;" title="Photo by Ella Olsson on Unsplash" /></p>
                <p><img src="' . plugins_url( 'images/full.png', dirname( __FILE__ ) ) . '" width="400" /></p>
                <p style="font-size: 20px; font-weight: bold; color: #e6005c;">' . get_tp_option('db-version') . ' "Raspberry Brownie"</p>
                <p><a href="http://mtrv.wordpress.com/teachpress/">Website</a> | <a href="https://github.com/winkm89/teachPress/">teachPress on GitHub</a> | <a href="https://github.com/winkm89/teachPress/wiki">Dokumentation</a> | <a href="https://github.com/winkm89/teachPress/wiki/Changelog">Changelog</a></p>
                <p>&copy;2008-2022 by Michael Winkler | License: GPLv2 or later<br/></p>
                </div>
              </div>';
    }

    /**
     * Returns the select form for rel_page option
     * @param string $type  rel_page_publications or rel_page_courses
     * @access private
     * @since 5.0.0
     */
    private static function get_rel_page_form ($type) {
        $title = ( $type === 'rel_page_publications' ) ? __('For publications','teachpress') : __('For courses','teachpress');
        $value = get_tp_option($type);
        echo '<p><select name="' . $type . '" id="' . $type . '" title="' . $title . '">';
        
        echo '<option value="page" ';
        if ($value == 'page') { echo 'selected="selected"'; }
        echo '>' . __('Pages') . '</option>';
        
        echo '<option value="post" ';
        if ($value == 'post') { echo 'selected="selected"'; }
        echo '>' . __('Posts') . '</option>';

        $post_types = get_post_types( array('public' => true, '_builtin' => false ), 'objects' ); 
        foreach ($post_types as $post_type ) {
            $current = ($post_type->name == $value) ? 'selected="selected"' : '';
            echo '<option value="'. $post_type->name . '" ' . $current . '>'. $post_type->label. '</option>';
        }
        echo '</select> ';
        echo '<label for="' . $type . '"></label></p>';
    }
    
    /**
     * Returns the select for for user role field
     * @param string $type
     * @access private
     * @since 5.0.0
     */
    private static function get_user_role_form ($type){
        $title = ( $type === 'userrole_publications' ) ? __('Backend access for publication module','teachpress') : __('Backend access for course module','teachpress');
        $cap = ( $type === 'userrole_publications' ) ? 'use_teachpress' : 'use_teachpress_courses';
        
        echo '<tr>';
        echo '<th><label for="' . $type . '">' . $title . '</label></th>';
        echo '<td style="vertical-align: top;">';
        echo '<select name="' . $type . '[]" id="' . $type . '" multiple="multiple" style="height:120px; width: 220px;" title="' . $title . '">';
        
        global $wp_roles;
        foreach ($wp_roles->role_names as $roledex => $rolename){
           $role = $wp_roles->get_role($roledex);
           $select = $role->has_cap($cap) ? 'selected="selected"' : '';
           echo '<option value="'.$roledex.'" '.$select.'>'.$rolename.'</option>';
        }
        
        echo '</select>';
        echo '</td>';
        echo '<td style="vertical-align: top;">' . __('Select which userrole your users must have to use the teachPress backend.','teachpress') . '<br />' . __('use &lt;Ctrl&gt; key to select multiple roles','teachpress') . '</td>';        
        echo '</tr>';
    }
    
    /**
     * Shows the tab for general options
     * @param sting $site
     * @access private
     * @since 5.0.0
     */
    private static function get_general_tab($site) {

        echo '<table class="form-table">';
        echo '<thead>';

        // Version
        echo '<tr>';
        echo '<th width="160">' . __('teachPress version','teachpress') . '</th>';
        echo '<td width="250"><a id="tp_open_readme" class="tp_open_readme">' . get_tp_option('db-version') . '</a></td>';
        echo '<td></td>';
        echo '</td>';
        echo '</tr>';

        // Frontend styles
        echo '<tr>';
        echo '<th><label for="stylesheet">' . __('Frontend styles','teachpress') . '</label></th>';
        echo '<td style="vertical-align: top;">';
        echo '<select name="stylesheet" id="stylesheet" title="' . __('Frontend styles','teachpress') . '">';

        $value = get_tp_option('stylesheet');
        if ($value == '1') {
            echo '<option value="1" selected="selected">' . __('teachpress_front.css','teachpress') . '</option>';
            echo '<option value="0">' . __('your theme.css','teachpress') . '</option>';
        }
        else {
            echo '<option value="1">' . __('teachpress_front.css','teachpress') . '</option>';
            echo '<option value="0" selected="selected">' . __('your theme.css','teachpress') . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td>' . __('Select which style sheet you will use. teachpress_front.css is the teachPress default style. If you have created your own style in the default style sheet of your theme, you can activate this here.','teachpress') . '</td>';
        echo '</tr>';

        // User roles
        TP_Settings_Page::get_user_role_form('userrole_publications');
        
        // Related content
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('Related content','teachpress') . '</h3></th>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Type','teachpress') . '</th>';
        echo '<td>';
        TP_Settings_Page::get_rel_page_form('rel_page_publications');
        echo '</td>';
        echo '<td style="vertical-align: top;">' . __('If you create a publication you can define a link to related content. It is kind of a "more information link", which helps you to connect a course/publication with a page. If you want to use custom post types instead of pages, so you can set it here.','teachpress') . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Default category for related content','teachpress') . '</th>';
        echo '<td>';
        wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'rel_content_category', 'orderby' => 'name', 'selected' => get_tp_option('rel_content_category'), 'hierarchical' => true, 'show_option_none' => __('none','teachpress'))); 
        echo '</td>';
        echo '<td>' . __('Used if the related content type for publicaitons is set on','teachpress') . ': ' . __('Posts') . ' </td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Automatic related content','teachpress') . '</th>';
        echo '<td colspan="2">' . TP_Admin::get_checkbox('rel_content_auto', __('Create an automatic related content with every new publication','teachpress'), get_tp_option('rel_content_auto')) . '</td>';
        echo '</tr>';
		
        echo '<tr>';
        echo '<th>' . __('Template for related content','teachpress') . '</th>';
        echo '<td colspan="2"><textarea name="rel_content_template" id="rel_content_template" style="width:90%;" rows="10">' . get_tp_option('rel_content_template') . '</textarea></td>';
        echo '</tr>';
        
         // Import/Export
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('Import / Export','teachpress') . '</h3></th>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th width="160">' . __('BibTeX special chars','teachpress') . '</th>';
        echo '<td colspan="2">' . TP_Admin::get_checkbox('convert_bibtex', __('Try to convert utf-8 chars into BibTeX compatible ASCII strings','teachpress'), get_tp_option('convert_bibtex')) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th width="160">' . __('Update existing publications','teachpress') . '</th>';
        echo '<td colspan="2">' . TP_Admin::get_checkbox('import_overwrite', __('Allow optional updating for publication import','teachpress'), get_tp_option('import_overwrite')) . '</td>';
        echo '</tr>';
        
        
        // RSS
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('RSS','teachpress') . '</h3></th>';
        echo '</tr>';
		
        echo '<tr>';
        echo '<th>' . __('RSS feed addresses','teachpress') . '</th>';
        echo '<td colspan="2"><p><em>' . __('For all publications:','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss" target="_blank">' . __('Show','teachpress') . '</a></p>
            <p><em>' . __('Example for publications of a single user (id = WordPress user-ID):','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss&amp;id=1</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss&amp;id=1" target="_blank">' . __('Show','teachpress') . '</a></p>
            <p><em>' . __('Example for publications of a single tag (tag = tag-id):','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss&amp;tag=1</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss&amp;tag=1" target="_blank">' . __('Show','teachpress') . '</a></p>
                  </td>';  
        echo '</tr>';

        // Misc
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('Misc','teachpress') . '</h3></th>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Database','teachpress') . '</th>';
        echo '<td>';
        echo '<a href="' .$site . '&amp;tab=db_status">' . __('Index status','teachpress') . '</a>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Uninstalling','teachpress') . '</th>';
        echo '<td>';
        echo '<a class="tp_row_delete" href="options-general.php?page=teachpress/settings.php&amp;tab=general&amp;drop_tp=1">' . __('Remove teachPress from database','teachpress') . '</a>';
        echo '</td>';
        echo '</tr>';
        
        echo '</thead>';
        echo '</table>';
        
        echo '<p><input name="einstellungen" type="submit" id="teachpress_settings" value="' . __('Save') . '" class="button-primary" /></p>';
        
        echo '<script type="text/javascript" src="' . plugins_url( 'js/admin_settings.js', dirname( __FILE__ ) ) . '"></script>';
        self::get_about_dialog();
    }
    
    /**
     * Shows the student settings tab
     * @param string $tab   student_data, publication_data or course_data
     * @access private
     * @since 5.0.0
     */
    private static function get_meta_tab($tab) {
        // Select right table name
        if ( $tab === 'student_data' ) {
            $table = 'teachpress_stud';
        }
        else if ( $tab === 'publication_data' ) {
            $table = 'teachpress_pub';
        }
        else {
            $table = 'teachpress_courses';
        }

        $select_fields = array();

        echo '<div style="min-width:780px; width:100%;">';
        echo '<div style="width:48%; float:left; padding-right:2%;">';
        echo '<h3>' . __('Meta data fields','teachpress') . '</h3>';

        echo '<table class="widefat">';
        
        // Table Head
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Field name','teachpress') . '</th>';
        echo '<th>' . __('Properties','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        
        // Table Body
        echo '<tbody>';

        // Default fields
        $class_alternate = true;
        $fields = get_tp_options($table,'`variable` ASC');
        foreach ($fields as $field) {
            $data = TP_DB_Helpers::extract_column_data($field->value);
            if ( $data['type'] === 'SELECT' || $data['type'] === 'CHECKBOX' || $data['type'] === 'RADIO' ) {
                array_push($select_fields, $field->variable);
                // search for select options and add it
                if ( isset( $_POST['add_' . $field->variable] ) && $_POST['new_' . $field->variable] != __('Add element','teachpress') ) {
                    TP_Options::add_option($_POST['new_' . $field->variable], $_POST['new_' . $field->variable], $field->variable);
                }
            }
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>
                <td>' . $field->variable . '
                    <div class="tp_row_actions">
                    <a class="tp_edit_meta_field" title="' . __('Click to edit','teachpress') . '" href="' . admin_url( 'admin-ajax.php' ) . '?action=teachpress&meta_field_id=' . $field->setting_id . '">' . __('Edit','teachpress') . '</a> | <a class="tp_row_delete" title="' . __('Delete','teachpress') . '" href="options-general.php?page=teachpress/settings.php&amp;delete_field=' . $field->setting_id . '&amp;tab=' . $tab . '">' . __('Delete','teachpress') . '</a>
                    </div>
                </td>
                <td>';
            if ( isset( $data['title'] ) ) {
                echo 'Label: <b>' . stripslashes($data['title']) . '</b><br/>'; }
            if ( isset( $data['type'] ) ) {
                echo 'Type: <b>' . stripslashes($data['type']) . '</b><br/>';}
            if ( isset( $data['visibility'] ) ) {
                echo 'Visibility: <b>' . stripslashes($data['visibility']) . '</b><br/>'; }
            if ( isset( $data['min'] ) ) {
                echo 'Min: <b>' . stripslashes($data['min']) . '</b><br/>'; }
            if ( isset( $data['max'] ) ) {
                echo 'Max: <b>' . stripslashes($data['max']) . '</b><br/>'; }
            if ( isset( $data['step'] ) ) {
                echo 'Step: <b>' . stripslashes($data['step']) . '</b><br/>'; }
            if ( isset( $data['required'] ) ) {
                echo 'Required: <b>' . stripslashes($data['required']) . '</b>'; }
            echo '</td>';
            echo '</tr>';
        }
        // Table Footer
        echo '</tbody>';
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="2">';
        echo '<a class="tp_edit_meta_field button-primary" title="' . __('Add new','teachpress') . '" href="' . admin_url( 'admin-ajax.php' ) . '?action=teachpress&meta_field_id=0">' . __('Add new','teachpress') . '</a>';
        echo '</td>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';

        echo '</div>';
        echo '<div style="width:48%; float:left; padding-left:2%;">';

        foreach ( $select_fields as $elem ) {
            $args1 = array ( 
                 'element_title'    => __('Name','teachpress'),
                 'count_title'      => __('Number of students','teachpress'),
                 'delete_title'     => __('Delete elemtent','teachpress'),
                 'add_title'        => __('Add element','teachpress'),
                 'tab'              => $tab
                 );
             TP_Admin::get_course_option_box($elem, $elem, $args1);
        }

        echo '</div>';
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($){
                $(".tp_edit_meta_field").each(function() {
                    var $link = $(this);
                    var $dialog = $('<div></div>')
                        .load($link.attr('href') + ' #content')
                        .dialog({
                                autoOpen: false,
                                title: '<?php _e('Meta Field Settings','teachpress'); ?>',
                                width: 600
                        });

                    $link.click(function() {
                        $dialog.dialog('open');
                        return false;
                    });
                });
            });
        </script>
        <?php
    }
    
    /**
     * Shows the templates tab
     * @access private
     * @since 6.0.0 
     */
    private static function get_template_tab () {
        $tp_upload_dir = wp_upload_dir();
        echo '<h3>' . __('Available templates for publication lists','teachpress') . '</h3>';
        
        // Begin change directory message
        echo '<div class="teachpress_message teachpress_message_orange"><b>' . __('Please note','teachpress') . '</b>: ' . __('Changes in the templates will be overwritten by updates of the plugin.','teachpress') . ' <a onclick="teachpress_showhide(' . "'teachpress_change_directory'" . ')" style="cursor: pointer;">' . __('But you can change the directory for the templates.','teachpress') . '</a></div>';
        echo '<div id="teachpress_change_directory" class="teachpress_message" style="display:none;">';
        echo '<p><b>1. Add the following code to your wp-config.php:</b></p>';
        echo '// For changing the teachPress template directory (moving it to wp-content/uploads/)<br/>';
        echo "define ('TEACHPRESS_TEMPLATE_PATH', '" . $tp_upload_dir['basedir'] . "/teachpress/templates/');<br/>";
        echo "define ('TEACHPRESS_TEMPLATE_URL', '". $tp_upload_dir['baseurl'] . "/teachpress/templates/');<br/>";
        echo '<p><b>2. Move all teachpress template files to wp-content/uploads/teachpress/templates/</b></p>';
        echo '</div>';
        // End change directory message
        
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Name') . '</th>';
        echo '<th>' . __('Key') . '</th>';
        echo '<th>' . __('Description') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo self::list_templates();
        echo '</table>';
    }

    /**
     * Creates the list of publication templates
     * @return string
     * @access private
     * @since 6.0.0
     */
    private static function list_templates () {
        $templates = tp_detect_templates();
        $s = '';
        $class_alternate = true;
        foreach ($templates as $key => $value) {
            // alternate row style
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            // load template
            include_once $templates[$key];
            $template = new $key();
            $settings = TP_HTML_Publication_Template::load_settings($template);
            
            $s .= '<tr ' . $tr_class . '>';
            $s .= '<td>' . esc_html($settings['name']) . '</td>';
            $s .= '<td>' . esc_html($key) . '</td>';
            $s .= '<td>' . esc_html($settings['description']) . '
                       <p>' . __('Version', 'teachpress') . ' ' . esc_html($settings['version']) . ' | ' . __('by', 'teachpress') . ' ' . esc_html($settings['author']) . '</p>
                  </td>';
            $s .= '</tr>';
        }
        
        $s .= '</table>';
        return $s;
    }
    
    /**
     * Shows the db status tab
     * @access private
     * @since 7.0.0 
     */
    private static function get_db_status_tab () {
        self::list_db_table_index(TEACHPRESS_AUTHORS);
        self::list_db_table_index(TEACHPRESS_PUB);
        self::list_db_table_index(TEACHPRESS_PUB_CAPABILITIES);
        self::list_db_table_index(TEACHPRESS_PUB_DOCUMENTS);
        self::list_db_table_index(TEACHPRESS_PUB_IMPORTS);
        self::list_db_table_index(TEACHPRESS_PUB_META);
        self::list_db_table_index(TEACHPRESS_RELATION);
        self::list_db_table_index(TEACHPRESS_REL_PUB_AUTH);
        self::list_db_table_index(TEACHPRESS_SETTINGS);
        self::list_db_table_index(TEACHPRESS_TAGS);
        self::list_db_table_index(TEACHPRESS_USER);
        self::list_db_table_index(TEACHPRESS_MONITORED_SOURCES);
    }
    
    /**
     * Returns the list of table indexes for the given database table
     * @param $db_name
     * @return string
     * @access private
     * @since 7.0.0
     */
    private static function list_db_table_index ($db_name) {
        echo '<h3>' . $db_name . '</h3>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Key_name','teachpress') . '</th>';
        echo '<th>' . __('Type','teachpress') . '</th>';
        echo '<th>' . __('Unique','teachpress') . '</th>';
        echo '<th>' . __('Packed','teachpress') . '</th>';
        echo '<th>' . __('Column','teachpress') . '</th>';
        echo '<th>' . __('Cardinality','teachpress') . '</th>';
        echo '<th>' . __('Collation','teachpress') . '</th>';
        echo '<th>NULL</th>';
        echo '<th>' . __('Seq index','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        
        $result = TP_DB_Helpers::get_db_index($db_name);
        foreach ($result as $row) {
            // For unique field
            $unique = ( $row['Non_unique'] === '0' ) ? __('No') : __('Yes');
            
            // For NULL field
            if ( $row['Null'] === 'YES' ) {
                $n = __('Yes');
            }
            else if ( $row['Null'] === 'NO' ) {
                $n = __('No');
            }
            else {
                $n = $row['Null'];
            }
            
            echo '<tr>';
            echo '<td>' . $row['Key_name'] . '</td>';
            echo '<td>' . $row['Index_type'] . '</td>';
            echo '<td>' . $unique . '</td>';
            echo '<td>' . $row['Packed'] . '</td>';
            echo '<td>' . $row['Column_name'] . '</td>';
            echo '<td>' . $row['Cardinality'] . '</td>';
            echo '<td>' . $row['Collation'] . '</td>';
            echo '<td>' . $n . '</th>';
            echo '<td>' . $row['Seq_in_index'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    /**
     * Adds new term and new types for courses
     * @access private
     * @since 5.0.0
     */
    private static function add_course_options () {
        $new_term = isset( $_POST['new_term'] ) ? htmlspecialchars($_POST['new_term']) : ''; 
        $new_type = isset( $_POST['new_type'] ) ? htmlspecialchars($_POST['new_type']) : '';

        if (isset( $_POST['add_type'] ) && $new_type != __('Add type','teachpress')) {
            TP_Options::add_option($new_type, $new_type, 'course_type');
            get_tp_message(__('Saved'));
        }
        if (isset( $_POST['add_term'] ) && $new_term != __('Add term','teachpress')) {
           TP_Options::add_option($new_term, $new_term, 'semester');
           get_tp_message(__('Saved'));
        }
    }
    
    /**
     * Handles adding of new meta data fields
     * @param string $table         The table name (teachpress_stud, teachpress_courses or teachpress_pub)
     * @access private
     * @since 5.0.0
     */
    private static function add_meta_fields ($table) {
        if ( !isset( $_POST['field_name'] ) ) {
            return;
        }
        
        // Generate field name
        $field_name = self::generate_meta_field_name($_POST['field_name'], $table);
        
        // Field values
        $data['title'] = isset( $_POST['field_label'] ) ? htmlspecialchars($_POST['field_label']) : '';
        $data['type'] = isset( $_POST['field_type'] ) ? htmlspecialchars($_POST['field_type']) : '';
        $data['visibility'] = isset( $_POST['visibility'] ) ? htmlspecialchars($_POST['visibility']) : '';
        $data['min'] = isset( $_POST['number_min'] ) ? intval($_POST['number_min']) : 'false';
        $data['max'] = isset( $_POST['number_max'] ) ? intval($_POST['number_max']) : 'false';
        $data['step'] = isset( $_POST['number_step'] ) ? intval($_POST['number_step']) : 'false';
        $data['required'] = isset( $_POST['is_required'] ) ? 'true' : 'false';
        $data['field_edit'] = isset( $_POST['field_edit'] ) ? intval($_POST['field_edit']) : 0 ;
        
        // Generate an array of forbidden field names
        $forbidden_names = array('system', 'course_type', 'semester', __('Field name','teachpress'));
        $options = get_tp_options($table);
        foreach ( $options as $row) {
            if ( $data['field_edit'] !== intval($row->setting_id) ) {
                array_push( $forbidden_names, $row->variable );
            }
        }
        
        if ( !in_array($field_name, $forbidden_names) && $data['title'] != __('Label', 'teachpress') && preg_match("#^[_A-Za-z0-9]+$#", $field_name) ) {
            
            // Delete old settings if needed
            if ( $data['field_edit'] > 0 ) {
                TP_Options::delete_option($data['field_edit']);
            }
            
            TP_DB_Helpers::register_column($table, $field_name, $data);
            get_tp_message(  __('Field added','teachpress') );
        }
        else {
            get_tp_message(  __('Warning: This field name is not possible.','teachpress'), 'red' );
        }
    }
    
    /**
     * Generates and returns a name for meta data fields
     * @param string $fieldname     The field name
     * @param string $table         The table name (used to define a prefix)
     * @access private
     * @since 5.0.0
     */
    private static function generate_meta_field_name($fieldname, $table) {
        $name = str_replace( array("'", '"', ' '), array("", "", '_'), $fieldname);
        
        if ( $table === 'teachpress_courses' ) {
            $prefix = 'tp_meta_courses_';
        }
        elseif ( $table === 'teachpress_pub' ) {
            $prefix = 'tp_meta_pub_';
        }
        elseif ( $table === 'teachpress_stud' ) {
            $prefix = 'tp_meta_stud_';
        }
        else {
            $prefix = 'tp_meta_';
        }
        
        // Check if the prefix is already part of the field name
        if ( stristr($fieldname, $prefix) === false ) {
            return $prefix . esc_attr($name);
        }
        
        return esc_attr($name);
    }
    
    /**
     * Deletes student data fields
     * @param string $tab   The name of the tab (used for return link)
     * @access private
     * @since 5.0.0
     */
    private static function delete_meta_fields ($tab) {
        if ( isset($_GET['delete_field']) ) {
            $message = '<p>' . __('Do you really want to delete the selected meta field?','teachpress') . '</p>' . '<a class="button-primary" href="options-general.php?page=teachpress/settings.php&amp;delete_field_ok=' . intval($_GET['delete_field']) . '&amp;tab=' . $tab . '">'. __('OK') . '</a> <a class="button-secondary" href="options-general.php?page=teachpress/settings.php&amp;tab=student_data">'. __('Cancel') . '</a>';
            get_tp_message($message,'orange');
        }
        if ( isset($_GET['delete_field_ok']) ) {
            $option = TP_Options::get_option_by_id($_GET['delete_field_ok']);
            $options = get_tp_options($option['variable'], "`setting_id` DESC", ARRAY_A);
            foreach ( $options as $row ) {
                TP_Options::delete_option($row['setting_id']);
            }
            TP_Options::delete_option($_GET['delete_field_ok']);
            get_tp_message( __('Field deleted','teachpress') );
        }
    }

    /**
     * Handles changing of general options
     * @access private
     * @since 5.0.0
     */
    private static function change_general_options () {
        $option_semester = isset( $_POST['semester'] ) ? htmlspecialchars($_POST['semester']) : '';
        $option_rel_page_publications = isset( $_POST['rel_page_publications'] ) ? htmlspecialchars($_POST['rel_page_publications']) : '';
        $option_stylesheet = isset( $_POST['stylesheet'] ) ? intval($_POST['stylesheet']) : '';
        $option_userrole_publications = isset( $_POST['userrole_publications'] ) ? $_POST['userrole_publications'] : '';
        $checkbox_convert_bibtex = isset( $_POST['convert_bibtex'] ) ? 1 : '';
        $checkbox_import_overwrite = isset( $_POST['import_overwrite'] ) ? 1 : '';
        $checkbox_rel_content_auto = isset( $_POST['rel_content_auto'] ) ? 1 : '';
    
        TP_Options::change_option('sem', $option_semester);
        TP_Options::change_option('rel_page_publications', $option_rel_page_publications);
        TP_Options::change_option('stylesheet', $option_stylesheet);

        TP_Options::change_option('convert_bibtex', $checkbox_convert_bibtex, 'checkbox');
        TP_Options::change_option('import_overwrite', $checkbox_import_overwrite, 'checkbox');
        TP_Options::change_option('rel_content_auto', $checkbox_rel_content_auto, 'checkbox');
        TP_Options::change_option('rel_content_template', $_POST['rel_content_template']);
        TP_Options::change_option('rel_content_category', $_POST['rel_content_category']);
        tp_update_userrole($option_userrole_publications, 'use_teachpress');

        get_tp_message( __('Settings are changed. Please note that access changes are visible, until you have reloaded this page a second time.','teachpress') );
    }
    
    /**
     * Handles start of database updates
     * @param string $site                      The current URL
     * @param boolean $with_structure_change    Update database structure (true) or not (false), Default is true
     * @access private
     * @since 5.0.0
     */
    private static function update_database ($site, $with_structure_change = true) {
        if ( $with_structure_change === true ) {
            tp_db_update();
        }
        $check_stud_meta = TP_Update::check_table_stud_meta();
        $check_authors = TP_Update::check_table_authors();
        if ( $check_authors === false && $check_stud_meta === false ) {
            return;
        }
        $sync = ( $check_authors === true ) ? 1 : 2;
        $table = ( $check_authors === true ) ? 'teachpress_authors' : 'teachpress_stud_meta';
        $message = 'TABLE ' . $table . ': ' .  __('teachPress wants to fill up the new database. This can take some time.','teachpress') . ' <a href="' . $site . '&amp;sync=' . $sync . '" class="button-primary">' . __('Continue','teachpress') . '</a>';
        get_tp_message($message, 'orange');
    }
    
    /**
     * Hanldes start of database deletion
     * @access private
     * @since 5.0.0
     */
    private static function delete_database () {
        if ( isset($_GET['drop_tp']) ) {
            $message = '<p>' . __('Do you really want to delete all teachpress database tables?','teachpress') . '</p>' . '<a class="button-primary" href="options-general.php?page=teachpress/settings.php&amp;tab=general&amp;drop_tp_ok=1">'. __('OK') . '</a> <a class="button-secondary" href="options-general.php?page=teachpress/settings.php&amp;tab=general">'. __('Cancel') . '</a>';
            get_tp_message($message,'orange');
        }
        if ( isset($_GET['drop_tp_ok']) ) {
            tp_uninstall();
            get_tp_message( __('Database uninstalled','teachpress') );
        }
    }
}
