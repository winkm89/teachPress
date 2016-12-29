<?php
/**
 * This file contains all functions for displaying the settings page in admin menu
 * 
 * @package teachpress\admin\settings
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * teachPress settings menu: controller
 * @since 5.0.0
 */
function tp_show_admin_settings() {
    tp_settings_page::load_page();   
}

/**
 * This class contains all functions for the teachpress settings page
 * @since 5.0.0
 */
class tp_settings_page {
    
    /**
     * Generates the settings page
     * @since 5.0.0
     */
    public static function load_page (){
        echo '<div class="wrap">';

        $site = 'options-general.php?page=teachpress/settings.php';
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        // update dababase
        if ( isset($_GET['up']) ) {
            tp_settings_page::update_database($site);
        }

        // sync database
        if ( isset($_GET['sync']) ) {
            $sync = intval($_GET['sync']);
            if ( $sync === 1 ) {
                tp_db_sync('authors');
                tp_settings_page::update_database($site, false);
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
            tp_settings_page::delete_database();
        }

        // change general options
        if (isset( $_POST['einstellungen'] )) {
            tp_settings_page::change_general_options();
        }

        // change publication options
        if ( isset($_POST['save_pub']) ) {
            tp_settings_page::change_publication_options();
        }

        // delete settings
        if ( isset( $_GET['delete'] ) ) {
            tp_options::delete_option($_GET['delete']);
        }
        
        // Delete data field
        if ( isset( $_GET['delete_field'] ) || isset( $_GET['delete_field_ok'] ) ) {
            tp_settings_page::delete_meta_fields($tab);
        }

        // add course options
        if ( isset( $_POST['add_term'] ) || isset( $_POST['add_type'] ) ) {
            tp_settings_page::add_course_options();
        }

        // add meta field options
        if ( isset($_POST['add_field']) ) {
            if ( $tab === 'course_data' ) {
                $table = 'teachpress_courses';
            }
            elseif ( $tab === 'publication_data' ) {
                $table = 'teachpress_pub';
            }
            else {
                $table = 'teachpress_stud';
            }
            tp_settings_page::add_meta_fields($table);
        }

        // test if database is installed
        tp_admin::database_test();

        echo '<h2 style="padding-bottom:0px;">' . __('teachPress settings','teachpress') . '</h2>';

        // Site menu
        $set_menu_1 = ( $tab === 'general' || $tab === '' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_2 = ( $tab === 'courses' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_3 = ( $tab === 'course_data' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_4 = ( $tab === 'student_data' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_5 = ( $tab === 'publications' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_6 = ( $tab === 'publication_data' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $set_menu_7 = ( $tab === 'publication_templates' ) ? 'nav-tab nav-tab-active' : 'nav-tab';

        echo '<h3 class="nav-tab-wrapper">'; 
        echo '<a href="' . $site . '&amp;tab=general" class="' . $set_menu_1 . '">' . __('General','teachpress') . '</a>';
        if ( TEACHPRESS_COURSE_MODULE === true ) {
            echo '<a href="' . $site . '&amp;tab=courses" class="' . $set_menu_2 . '">' . __('Courses','teachpress') . '</a>';
            echo '<a href="' . $site . '&amp;tab=course_data" class="' . $set_menu_3 . '">' . __('Meta','teachpress') . ': ' . __('Courses','teachpress') . '</a>';
            echo '<a href="' . $site . '&amp;tab=student_data" class="' . $set_menu_4 . '">' . __('Meta','teachpress') . ': ' . __('Students','teachpress') . '</a>';
        }
        if ( TEACHPRESS_PUBLICATION_MODULE === true ) {
            echo '<a href="' . $site . '&amp;tab=publication_data" class="' . $set_menu_6 . '">' . __('Meta','teachpress') . ': ' . __('Publications','teachpress') . '</a>'; 
            echo '<a href="' . $site . '&amp;tab=publications" class="' . $set_menu_5 . '">' . __('Publications','teachpress') . '</a>';
            echo '<a href="' . $site . '&amp;tab=publication_templates" class="' . $set_menu_7 . '">' . __('Templates','teachpress') . '</a>';
        }
        echo '</h3>';

        echo '<form id="form1" name="form1" method="post" action="' . $site . '&amp;tab=' . $tab . '">';
        echo '<input name="page" type="hidden" value="teachpress/settings.php" />';
        echo '<input name="tab" type="hidden" value="<?php echo $tab; ?>" />';

        /* General */
        if ($tab === '' || $tab === 'general') {
            self::get_general_tab();
        }
        /* Courses */
        if ( $tab === 'courses' ) { 
            self::get_course_tab();
        }
        /* Meta data */
        if ( $tab === 'course_data' || $tab === 'student_data' || $tab === 'publication_data' ) {
            self::get_meta_tab($tab);
        }
        /* Publications */
        if ( $tab === 'publications' ) {
            self::get_publication_tab();
        }
        /* Templates */
        if ( $tab === 'publication_templates' ) {
            self::get_template_tab();
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
        echo '<div id="dialog" title="About">
                <div style="text-align: center;">
                <p><img src="' . plugins_url() . '/teachpress/images/misc/about.jpg" style="border-radius: 130px; width: 250px; height: 250px;" title="Image by Ashwin Vaswani" /></p>
                <p><img src="' . plugins_url() . '/teachpress/images/full.png" width="400" /></p>
                <p style="font-size: 20px; font-weight: bold; color: #ffcc00;">' . get_tp_option('db-version') . ' "Banana Cream Pie"</p>
                <p><a href="http://mtrv.wordpress.com/teachpress/">Website</a> | <a href="https://github.com/winkm89/teachPress/">teachPress on GitHub</a> | <a href="https://github.com/winkm89/teachPress/wiki">Dokumentation</a> | <a href="https://github.com/winkm89/teachPress/wiki/Changelog">Changelog</a></p>
                <p>&copy;2008-2016 by Michael Winkler | License: GPLv2 or later<br/></p>
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
        $title = ( $type === 'rel_page_publications' ) ? __('for publications','teachpress') : __('for courses','teachpress');
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
        echo '<label for="' . $type . '">' . $title. '</label></p>';
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
     * Shows the course settings tab
     * @access private
     * @since 5.0.0
     */
    private static function get_course_tab() {

        echo '<div style="min-width:780px; width:100%;">';
        echo '<div style="width:48%; float:left; padding-right:2%;">';

        $args2 = array ( 
            'element_title' => __('Term','teachpress'),
            'count_title' => __('Number of courses','teachpress'),
            'delete_title' => __('Delete term','teachpress'),
            'add_title' => __('Add term','teachpress'),
            'tab' => 'courses'
            );
            tp_admin::get_course_option_box(__('Term','teachpress'), 'term', $args2);

        echo '</div>';
        echo '<div style="width:48%; float:left; padding-left:2%;">';

        $args3 = array ( 
            'element_title' => __('Type'),
            'count_title' => __('Number of courses','teachpress'),
            'delete_title' => __('Delete type','teachpress'),
            'add_title' => __('Add type','teachpress'),
            'tab' => 'courses'
            );
        tp_admin::get_course_option_box(__('Types of courses','teachpress'), 'type', $args3);

        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Shows the tab for general options
     * @access private
     * @since 5.0.0
     */
    private static function get_general_tab() {

        echo '<table class="form-table">';
        echo '<thead>';

        echo '<tr>';
        echo '<th width="160">' . __('teachPress version','teachpress') . '</th>';
        echo '<td width="250"><a id="tp_open_readme" class="tp_open_readme">' . get_tp_option('db-version') . '</a></td>';
        echo '<td></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th>' . __('Components','teachpress') . '</th>';
        echo '<td style="vertical-align: top;">';
        $course_system = ( TEACHPRESS_COURSE_MODULE === false ) ? '<span style="color:#FF0000;">' . __('inactive','teachpress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
        echo 'Course module: ' . $course_system;
        echo '<br/>';
        $pub_system = ( TEACHPRESS_PUBLICATION_MODULE === false ) ? '<span style="color:#FF0000;">' . __('inactive','teachpress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
        echo 'Publication module: ' . $pub_system;
        echo '</td>';
        echo '<td>';
        echo '<a href="https://github.com/winkm89/teachPress/wiki/FAQ#how-can-i-deactivate-parts-of-the-plugin-like-the-course-module" target="_blank">' . __('You can deactivate parts of the plugin.','teachpress') . '</a>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th>' . __('Related content','teachpress') . '</th>';
        echo '<td>';
        tp_settings_page::get_rel_page_form('rel_page_courses');
        tp_settings_page::get_rel_page_form('rel_page_publications');
        echo '</td>';
        echo '<td style="vertical-align: top;">' . __('If you create a course or a publication you can define a link to related content. It is kind of a "more information link", which helps you to connect a course/publication with a page. If you want to use custom post types instead of pages, so you can set it here.','teachpress') . '</td>';
        echo '</tr>';

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

        tp_settings_page::get_user_role_form('userrole_publications');
        tp_settings_page::get_user_role_form('userrole_courses');
        
        echo '<tr>';
        echo '<th colspan="3"><h3>' . __('Enrollment system','teachpress') . '</h3></th>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="semester">' . __('Current term','teachpress') . '</label></th>';
        echo '<td><select name="semester" id="semester" title="' . __('Current term','teachpress') . '">'; 
        $value = get_tp_option('sem');
        $sem = get_tp_options('semester');
        
        // Test if the current semester is in the semester list
        $sem_test = ( get_tp_option($value, 'semester') === NULL ) ? false : true;
        if ( $sem_test === false ) {
            echo '<option selected="selected">- ' . __('Select','teachpress') . ' -</option>';
        }
        
        foreach ($sem as $sem) { 
            $current = ($sem->value == $value) ? 'selected="selected"' : '';
            echo '<option value="' . $sem->value . '" ' . $current . '>' . stripslashes($sem->value) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td>' . __('Here you can change the current term. This value is used for the default settings for all menus.','teachpress') . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th width="160"><label for="login_mode">' . __('Mode','teachpress') . '</label></th>';
        echo '<td width="210" style="vertical-align: top;">';
        echo '<select name="login" id="login_mode" title="' . __('Mode','teachpress') . '">';

        $value = get_tp_option('login');
        if ($value == 'int') {
            echo '<option value="std">' . __('Standard','teachpress') . '</option>';
            echo '<option value="int" selected="selected">' . __('Integrated','teachpress') . '</option>';
        }
        else {
            echo '<option value="std" selected="selected">' . __('Standard','teachpress') . '</option>';
            echo '<option value="int">' . __('Integrated','teachpress') . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td>' . __('Standard - teachPress has a separate registration. This is usefull if you have an auto login for WordPress or most of your users are registered in your blog, for example in a network.','teachpress') . '<br />' . __('Integrated - teachPress deactivates the own registration and uses all available data from WordPress. This is usefull, if most of your users has not an acount in your blog.','teachpress') . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="sign_out">' . __('Prevent sign out','teachpress') . '</label></th>';
        echo '<td><select name="sign_out" id="sign_out" title="' . __('Prevent sign out','teachpress') . '">';

        $value = get_tp_option('sign_out');
        if ($value == '1') {
            echo '<option value="1" selected="selected">' . __('yes','teachpress') . '</option>';
            echo '<option value="0">' . __('no','teachpress') . '</option>';
        }
        else {
            echo '<option value="1">' . __('yes','teachpress') . '</option>';
            echo '<option value="0" selected="selected">' . __('no','teachpress') . '</option>';
        } 
        echo '</select>';
        echo '</td>';
        echo '<td>' . __('Prevent sign out for your users','teachpress') . '</td>';
        echo '</tr>';

        echo '</thead>';
        echo '</table>';

        echo '<h3>' . __('Uninstalling','teachpress') . '</h3> ';
        echo '<a href="options-general.php?page=teachpress/settings.php&amp;tab=general&amp;drop_tp=1">' . __('Remove teachPress from database','teachpress') . '</a>';
        echo '<p><input name="einstellungen" type="submit" id="teachpress_settings" value="' . __('Save') . '" class="button-primary" /></p>';
        
        echo '<script type="text/javascript" src="' . plugins_url() . '/teachpress/js/admin_settings.js"></script>';
        self::get_about_dialog();
    }
    
    /**
     * Shows the publication settings tab
     * @access private
     * @since 5.0.0
     */
    private static function get_publication_tab() {
        
        echo '<table class="form-table">';
        echo '<thead>';
        
        echo '<tr>';
        echo '<th colspan="2"><h3>' . __('Import / Export','teachpress') . '</h3></th>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th width="160">' . __('BibTeX special chars','teachpress') . '</th>';
        echo '<td>' . tp_admin::get_checkbox('convert_bibtex', __('Try to convert utf-8 chars into BibTeX compatible ASCII strings','teachpress'), get_tp_option('convert_bibtex')) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th width="160">' . __('Overwrite publications','teachpress') . '</th>';
        echo '<td>' . tp_admin::get_checkbox('import_overwrite', __('Allow optional overwriting for publication import','teachpress'), get_tp_option('import_overwrite')) . ' <b>(EXPERIMENTAL)</b></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th colspan="2"><h3>' . __('Related content','teachpress') . '</h3></th>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th>' . __('Automatic related content','teachpress') . '</th>';
        echo '<td>' . tp_admin::get_checkbox('rel_content_auto', __('Create an automatic related content with every new publication','teachpress'), get_tp_option('rel_content_auto')) . '</td>';
        echo '</tr>';
		
        echo '<tr>';
        echo '<th>' . __('Template for related content','teachpress') . '</th>';
        echo '<td><textarea name="rel_content_template" id="rel_content_template" style="width:90%;" rows="10">' . get_tp_option('rel_content_template') . '</textarea></td>';
        echo '</tr>';
		
        echo '<tr>';
        echo '<th>' . __('Default category for related content','teachpress') . '</th>';
        echo '<td>';
        wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'rel_content_category', 'orderby' => 'name', 'selected' => get_tp_option('rel_content_category'), 'hierarchical' => true, 'show_option_none' => __('none','teachpress'))); 
        echo '<em>' . __('Used if the related content type for publicaitons is set on "Posts"','teachpress') . '</em>
             </td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th colspan="2"><h3>' . __('RSS','teachpress') . '</h3></th>';
        echo '</tr>';
		
        echo '<tr>';
        echo '<th>' . __('RSS feed addresses','teachpress') . '</th>';
        echo '<td><p><em>' . __('For all publications:','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss" target="_blank">' . __('Show','teachpress') . '</a></p>
            <p><em>' . __('Example for publications of a single user (id = WordPress user-ID):','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss&amp;id=1</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss&amp;id=1" target="_blank">' . __('Show','teachpress') . '</a></p>
            <p><em>' . __('Example for publications of a single tag (tag = tag-id):','teachpress') . '</em><br />
            <strong>' . home_url() . '?feed=tp_pub_rss&amp;tag=1</strong> &raquo; <a href="' . home_url() . '?feed=tp_pub_rss&amp;tag=1" target="_blank">' . __('Show','teachpress') . '</a></p>
                  </td>';  
        echo '</tr>';
		
        echo '</thead>';
        echo '</table>';

        echo '<input type="submit" class="button-primary" name="save_pub" value="' . __('Save') . '"/>';
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
            $data = tp_db_helpers::extract_column_data($field->value);
            if ( $data['type'] === 'SELECT' || $data['type'] === 'CHECKBOX' || $data['type'] === 'RADIO' ) {
                array_push($select_fields, $field->variable);
                // search for select options and add it
                if ( isset( $_POST['add_' . $field->variable] ) && $_POST['new_' . $field->variable] != __('Add element','teachpress') ) {
                    tp_options::add_option($_POST['new_' . $field->variable], $_POST['new_' . $field->variable], $field->variable);
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
                 'element_title' => __('Name','teachpress'),
                 'count_title' => __('Number of students','teachpress'),
                 'delete_title' => __('Delete elemtent','teachpress'),
                 'add_title' => __('Add element','teachpress'),
                 'tab' => $tab
                 );
             tp_admin::get_course_option_box($elem, $elem, $args1);
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
        echo '<h3>' . __('Available templates for publication lists','teachpress') . '</h3>';
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
            
            // default values
            $settings = array('name' => '', 'description' => '', 'author' => '', 'version' => '0.0');
            
            // overwrite defaults
            if ( method_exists($template, 'get_settings') ) {
                $settings = shortcode_atts( $settings, $template->get_settings() );
            }
            
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
     * Adds new term and new types for courses
     * @access private
     * @since 5.0.0
     */
    private static function add_course_options () {
        $new_term = isset( $_POST['new_term'] ) ? htmlspecialchars($_POST['new_term']) : ''; 
        $new_type = isset( $_POST['new_type'] ) ? htmlspecialchars($_POST['new_type']) : '';

        if (isset( $_POST['add_type'] ) && $new_type != __('Add type','teachpress')) {
            tp_options::add_option($new_type, $new_type, 'course_type');
            get_tp_message(__('Saved'));
        }
        if (isset( $_POST['add_term'] ) && $new_term != __('Add term','teachpress')) {
           tp_options::add_option($new_term, $new_term, 'semester');
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
                tp_options::delete_option($data['field_edit']);
            }
            
            tp_db_helpers::register_column($table, $field_name, $data);
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
            $option = tp_options::get_option_by_id($_GET['delete_field_ok']);
            $options = get_tp_options($option['variable'], "`setting_id` DESC", ARRAY_A);
            foreach ( $options as $row ) {
                tp_options::delete_option($row['setting_id']);
            }
            tp_options::delete_option($_GET['delete_field_ok']);
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
        $option_rel_page_courses = isset( $_POST['rel_page_courses'] ) ? htmlspecialchars($_POST['rel_page_courses']) : '';
        $option_rel_page_publications = isset( $_POST['rel_page_publications'] ) ? htmlspecialchars($_POST['rel_page_publications']) : '';
        $option_stylesheet = isset( $_POST['stylesheet'] ) ? intval($_POST['stylesheet']) : '';
        $option_sign_out = isset( $_POST['sign_out'] ) ? intval($_POST['sign_out']) : '';
        $option_login = isset( $_POST['login'] ) ? htmlspecialchars($_POST['login']) : '';
        $option_userrole_publications = isset( $_POST['userrole_publications'] ) ? $_POST['userrole_publications'] : '';
        $option_userrole_courses = isset( $_POST['userrole_courses'] ) ? $_POST['userrole_courses'] : '';
    
        tp_options::change_option('sem', $option_semester);
        tp_options::change_option('rel_page_courses', $option_rel_page_courses);
        tp_options::change_option('rel_page_publications', $option_rel_page_publications);
        tp_options::change_option('stylesheet', $option_stylesheet);
        tp_options::change_option('sign_out', $option_sign_out);
        tp_options::change_option('login', $option_login);
        tp_update_userrole($option_userrole_courses, 'use_teachpress_courses');
        tp_update_userrole($option_userrole_publications, 'use_teachpress');

        get_tp_message( __('Settings are changed. Please note that access changes are visible, until you have reloaded this page a second time.','teachpress') );
    }

    /**
     * Handles changing of options for publications
     * @access private
     * @since 5.0.0
     */
    private static function change_publication_options () {
        $checkbox_convert_bibtex = isset( $_POST['convert_bibtex'] ) ? 1 : '';
        $checkbox_import_overwrite = isset( $_POST['import_overwrite'] ) ? 1 : '';
        $checkbox_rel_content_auto = isset( $_POST['rel_content_auto'] ) ? 1 : '';
        tp_options::change_option('convert_bibtex', $checkbox_convert_bibtex, 'checkbox');
        tp_options::change_option('import_overwrite', $checkbox_import_overwrite, 'checkbox');
        tp_options::change_option('rel_content_auto', $checkbox_rel_content_auto, 'checkbox');
        tp_options::change_option('rel_content_template', $_POST['rel_content_template']);
        tp_options::change_option('rel_content_category', $_POST['rel_content_category']);
        get_tp_message(__('Saved'));
        
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
        $check_stud_meta = tp_update_db::check_table_stud_meta();
        $check_authors = tp_update_db::check_table_authors();
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