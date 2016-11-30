<?php
/*
Plugin Name: teachPress
Plugin URI: http://mtrv.wordpress.com/teachpress/
Description: With teachPress you can easy manage courses, enrollments and publications.
Version: 6.0beta
Author: Michael Winkler
Author URI: http://mtrv.wordpress.com/
Min WP Version: 3.9
Max WP Version: 4.6.1
Text Domain: teachpress
Domain Path: /languages
GitHub Plugin URI: https://github.com/winkm89/teachPress
GitHub Branch: master
*/

/*
   LICENCE

    Copyright 2008-2016 Michael Winkler

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*************/
/* Constants */
/*************/

/*
 * If you want, you can owerwrite this parameters in your wp-config.php.
 */

global $wpdb;

if ( !defined('TEACHPRESS_ARTEFACTS') ) {
    /**
     * This constant defines the table name for teachpress_artefacts.
     * @since 5.0.0
    */
    define('TEACHPRESS_ARTEFACTS', $wpdb->prefix . 'teachpress_artefacts');}

if ( !defined('TEACHPRESS_ASSESSMENTS') ) {
    /**
     * This constant defines the table name for teachpress_assessments.
     * @since 5.0.0
    */
    define('TEACHPRESS_ASSESSMENTS', $wpdb->prefix . 'teachpress_assessments');}

if ( !defined('TEACHPRESS_STUD') ) {
    /**
     * This constant defines the table name for teachpress_stud.
     * @since 5.0.0
    */
    define('TEACHPRESS_STUD', $wpdb->prefix . 'teachpress_stud');}

if ( !defined('TEACHPRESS_STUD_META') ) {
    /**
     * This constant defines the table name for teachpress_stud_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_STUD_META', $wpdb->prefix . 'teachpress_stud_meta');}

if ( !defined('TEACHPRESS_COURSES') ) {
    /**
     * This constant defines the table name for teachpress_courses.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSES', $wpdb->prefix . 'teachpress_courses');}

if ( !defined('TEACHPRESS_COURSE_META') ) {
    /**
     * This constant defines the table name for teachpress_course_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_META', $wpdb->prefix . 'teachpress_course_meta');}

if ( !defined('TEACHPRESS_COURSE_CAPABILITES') ) {
    /**
     * This constant defines the table name for teachpress_course_cababilites.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_CAPABILITES', $wpdb->prefix . 'teachpress_course_capabilites');}

if ( !defined('TEACHPRESS_COURSE_DOCUMENTS') ) {
    /**
     * This constant defines the table name for teachpress_course_documents.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_DOCUMENTS', $wpdb->prefix . 'teachpress_course_documents');}

if ( !defined('TEACHPRESS_SIGNUP') ) {
    /**
     * This constant defines the table name for teachpress_signups.
     * @since 5.0.0
    */
    define('TEACHPRESS_SIGNUP', $wpdb->prefix . 'teachpress_signup');}

if ( !defined('TEACHPRESS_SETTINGS') ) {
    /**
     * This constant defines the table name for teachpress_settings.
     * @since 5.0.0
    */
    define('TEACHPRESS_SETTINGS', $wpdb->prefix . 'teachpress_settings');}

if ( !defined('TEACHPRESS_PUB') ) {
    /**
     * This constant defines the table name for teachpress_pub.
     * @since 5.0.0
    */
    define('TEACHPRESS_PUB', $wpdb->prefix . 'teachpress_pub');}

if ( !defined('TEACHPRESS_PUB_META') ) {
    /**
     * This constant defines the table name for teachpress_pub_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_PUB_META', $wpdb->prefix . 'teachpress_pub_meta');}

if ( !defined('TEACHPRESS_PUB_CAPABILITES') ) {
    /**
     * This constant defines the table name for teachpress_course_cababilites.
     * @since 6.0.0
    */
    define('TEACHPRESS_PUB_CAPABILITES', $wpdb->prefix . 'teachpress_pub_capabilites');}

if ( !defined('TEACHPRESS_PUB_DOCUMENTS') ) {
    /**
     * This constant defines the table name for teachpress_course_documents.
     * @since 6.0.0
    */
    define('TEACHPRESS_PUB_DOCUMENTS', $wpdb->prefix . 'teachpress_pub_documents');}

if ( !defined('TEACHPRESS_TAGS') ) {
    /**
     * This constant defines the table name for teachpress_tags.
     * @since 5.0.0
    */
    define('TEACHPRESS_TAGS', $wpdb->prefix . 'teachpress_tags');}

if ( !defined('TEACHPRESS_RELATION') ) {
    /**
     * This constant defines the table name for teachpress_relation. This is the relationship tags to publications.
     * @since 5.0.0
    */
    define('TEACHPRESS_RELATION', $wpdb->prefix . 'teachpress_relation');}

if ( !defined('TEACHPRESS_USER') ) {
    /**
     * This constant defines the table name for teachpress_user. This is the relationship publications to users.
     * @since 5.0.0
    */
    define('TEACHPRESS_USER', $wpdb->prefix . 'teachpress_user');}

if ( !defined('TEACHPRESS_AUTHORS') ) {
    /**
     * This constant defines the table name for teachpress_authors.
     * @since 5.0.0
    */
    define('TEACHPRESS_AUTHORS', $wpdb->prefix . 'teachpress_authors');}

if ( !defined('TEACHPRESS_REL_PUB_AUTH') ) {
    /**
     * This constant defines the table name for teachpress_rel_pub_auth. This is the relationship publications to authors.
     * @since 5.0.0
    */
    define('TEACHPRESS_REL_PUB_AUTH', $wpdb->prefix . 'teachpress_rel_pub_auth');}

if ( !defined('TEACHPRESS_TIME_LIMIT') ) {
    /**
     * This value is used for PHP's set_time_limit(). The plugin sets this value before an import or export of publications
     * @since 5.0.0
    */
    define('TEACHPRESS_TIME_LIMIT', 240);}

if ( !defined('TEACHPRESS_FILE_LINK_CSS_CLASS') ) {
    /**
     * This value defines the CSS classes for file links which are inserted via the tinyMCE plugin
     * @since 5.0.0
    */
    define('TEACHPRESS_FILE_LINK_CSS_CLASS', 'linksecure tp_file_link');}

if ( !defined('TEACHPRESS_COURSE_MODULE') ) {
    /**
     * This value defines if the course module of teachPress is active
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_MODULE', true);}

if ( !defined('TEACHPRESS_PUBLICATION_MODULE') ) {
    /**
     * This value defines if the publication module of teachPress is active
     * @since 5.0.0
    */
    define('TEACHPRESS_PUBLICATION_MODULE', true);}

if ( !defined('TEACHPRESS_ERROR_REPORTING') ) {
    /**
     * This value defines if the error reporting is active or not
     * @since 5.0.13
    */
    define('TEACHPRESS_ERROR_REPORTING', false);}

if ( !defined('TEACHPRESS_FOREIGN_KEY_CHECKS') ) {
    /**
     * This value defines if foreign key checks are enabled or disabled, while adding database tables
     * @since 5.0.16
    */
    define('TEACHPRESS_FOREIGN_KEY_CHECKS', true);}

if ( !defined('TEACHPRESS_TEMPLATE_PATH') ) {
    /**
     * This value defines the template path
     * @since 6.0.0
    */
    define('TEACHPRESS_TEMPLATE_PATH', plugin_dir_path(__FILE__) . 'templates/');}

if ( !defined('TEACHPRESS_OSBIB_TEMPLATE_PATH') ) {
    /**
     * This value defines the template path
     * @since 6.0.0
    */
    define('TEACHPRESS_OSBIB_TEMPLATE_PATH', plugin_dir_path(__FILE__) . 'includes/osbib_new/styles/');}

if ( !defined('TEACHPRESS_TEMPLATE_URL') ) {
    /**
     * This value defines the template url
     * @since 6.0.0
    */
    define('TEACHPRESS_TEMPLATE_URL', plugins_url() . '/teachpress/templates/');}

if ( !defined('TEACHPRESS_ALTMETRIC_SUPPORT') ) {
    /**
     * This value defines the template url
     * @since 6.0.0
    */
    define('TEACHPRESS_ALTMETRIC_SUPPORT', false);}


/*********/
/* Menus */
/*********/

/**
 * Add menu for courses and students
 * @since 0.1.0
 * @todo Remove support for WordPress < 3.9
 */
function tp_add_menu() {
    global $wp_version;
    global $tp_admin_show_courses_page;
    global $tp_admin_add_course_page;

    $logo = (version_compare($wp_version, '3.8', '>=')) ? plugins_url() . '/teachpress/images/logo_small.png' : plugins_url() . '/teachpress/images/logo_small_black.png';

    $tp_admin_show_courses_page = add_menu_page(__('Course','teachpress'), __('Course','teachpress'),'use_teachpress_courses', __FILE__, 'tp_show_courses_page', $logo);
    $tp_admin_add_course_page = add_submenu_page('teachpress/teachpress.php',__('Add new','teachpress'), __('Add new', 'teachpress'),'use_teachpress_courses','teachpress/add_course.php','tp_add_course_page');
    add_submenu_page('teachpress/teachpress.php',__('Students','teachpress'), __('Students','teachpress'),'use_teachpress_courses', 'teachpress/students.php', 'tp_students_page');
    add_action("load-$tp_admin_add_course_page", 'tp_add_course_page_help');
    add_action("load-$tp_admin_show_courses_page", 'tp_show_course_page_help');
    add_action("load-$tp_admin_show_courses_page", 'tp_show_course_page_screen_options');
}

/**
 * Add menu for publications
 * @since 0.9.0
 * @todo Remove support for WordPress <3.9
 */
function tp_add_menu2() {
    global $wp_version;
    global $tp_admin_all_pub_page;
    global $tp_admin_your_pub_page;
    global $tp_admin_add_pub_page;
    global $tp_admin_import_page;
    global $tp_admin_show_authors_page;
    global $tp_admin_edit_tags_page;

    $logo = ( version_compare($wp_version, '3.8', '>=') ) ? plugins_url() . '/teachpress/images/logo_small.png' : plugins_url() . '/teachpress/images/logo_small_black.png';

    $tp_admin_all_pub_page = add_menu_page (__('Publications','teachpress'), __('Publications','teachpress'), 'use_teachpress', 'publications.php', 'tp_show_publications_page', $logo);
    $tp_admin_your_pub_page = add_submenu_page('publications.php',__('Your publications','teachpress'), __('Your publications','teachpress'),'use_teachpress','teachpress/publications.php','tp_show_publications_page');
    $tp_admin_add_pub_page = add_submenu_page('publications.php',__('Add new', 'teachpress'), __('Add new','teachpress'),'use_teachpress','teachpress/addpublications.php','tp_add_publication_page');
    $tp_admin_import_page = add_submenu_page('publications.php',__('Import/Export'), __('Import/Export'), 'use_teachpress', 'teachpress/import.php','tp_show_import_publication_page');
    $tp_admin_show_authors_page = add_submenu_page('publications.php',__('Authors', 'teachpress'),__('Authors', 'teachpress'),'use_teachpress','teachpress/authors.php','tp_show_authors_page');
    $tp_admin_edit_tags_page = add_submenu_page('publications.php',__('Tags'),__('Tags'),'use_teachpress','teachpress/tags.php','tp_tags_page');

    add_action("load-$tp_admin_all_pub_page", 'tp_show_publications_page_help');
    add_action("load-$tp_admin_all_pub_page", 'tp_show_publications_page_screen_options');
    add_action("load-$tp_admin_your_pub_page", 'tp_show_publications_page_help');
    add_action("load-$tp_admin_your_pub_page", 'tp_show_publications_page_screen_options');
    add_action("load-$tp_admin_add_pub_page", 'tp_add_publication_page_help');
    add_action("load-$tp_admin_import_page", 'tp_import_publication_page_help');
    add_action("load-$tp_admin_show_authors_page", 'tp_show_authors_page_screen_options');
    add_action("load-$tp_admin_edit_tags_page", 'tp_edit_tags_page_screen_options');
}

/**
 * Add option screen
 * @since 4.2.0
 */
function tp_add_menu_settings() {
    add_options_page(__('teachPress Settings','teachpress'),'teachPress','administrator','teachpress/settings.php', 'tp_show_admin_settings');
}

/************/
/* Includes */
/************/

// Admin menus
if ( is_admin() ) {
    include_once("admin/add-course.php");
    include_once("admin/add-publication.php");
    include_once("admin/add-students.php");
    include_once("admin/create-lists.php");
    include_once("admin/edit-student.php");
    include_once("admin/edit-tags.php");
    include_once("admin/import-publications.php");
    include_once("admin/mail.php");
    include_once("admin/settings.php");
    include_once("admin/show-authors.php");
    include_once("admin/show-courses.php");
    include_once("admin/show-publications.php");
    include_once("admin/show-single-course.php");
    include_once("admin/show-students.php");
}

// Core functions
include_once("core/admin.php");
include_once("core/class-ajax.php");
include_once("core/class-bibtex.php");
include_once("core/class-bibtex-import.php");
include_once("core/class-bibtex-macros.php");
include_once("core/class-cite-object.php");
include_once("core/class-document-manager.php");
include_once("core/class-export.php");
include_once("core/class-html.php");
include_once("core/class-mail.php");
include_once("core/database.php");
include_once("core/deprecated.php");
include_once("core/enrollments.php");
include_once("core/feeds.php");
include_once("core/general.php");
include_once("core/shortcodes.php");
include_once("core/templates.php");
include_once("core/widgets.php");

// BibTeX Parse
if ( !class_exists( 'PARSEENTRIES' ) ) {
    include_once("includes/bibtexParse/PARSEENTRIES.php");
    include_once("includes/bibtexParse/PARSECREATORS.php");
}

/*****************/
/* Mainfunctions */
/*****************/

/**
 * Load OSBiB core
 * @since 6.0.0
 */
function tp_load_osbib() {
    include_once 'includes/osbib_new/core.php';
}

/**
 * Returns the current teachPress version
 * @return string
*/
function get_tp_version() {
    return '6.0beta';
}

/**
 * Returns the WordPress version
 * @global string $wp_version
 * @return string
 * @since 5.0.13
 */
function tp_get_wp_version () {
    global $wp_version;
    return $wp_version;
}

/**
 * Function for the integrated registration mode
 * @since 1.0.0
 */
function tp_advanced_registration() {
    $user = wp_get_current_user();
    global $wpdb;
    global $current_user;
    $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$current_user->ID'");
    if ($test == '0' && $user->ID != '0') {
        if ($user->user_firstname == '') {
            $user->user_firstname = $user->display_name;
        }
        $data = array (
            'firstname' => $user->user_firstname,
            'lastname' => $user->user_lastname,
            'userlogin' => $user->user_login,
            'email' => $user->user_email
        );
        tp_students::add_student($user->ID, $data );
    }
}

/**********************/
/* RSS feed functions */
/**********************/

/**
 * Adds publication feeds
 * @since 6.0.0
 */
function tp_feed_init(){
    add_feed('tp_pub_rss', 'tp_pub_rss_feed_func');
    add_feed('tp_pub_bibtex', 'tp_pub_bibtex_feed_func');
    add_feed('tp_export', 'tp_export_feed_func');
}

/*************************/
/* Installer and Updater */
/*************************/

/**
 * Database update manager
 * @since 4.2.0
 */
function tp_db_update() {
   require_once("core/class-tables.php");
   require_once('core/class-update.php');
   tp_update_db::force_update();
}

/**
 * Database synchronisation manager
 * @param string $table     authors or stud_meta
 * @since 5.0.0
 */
function tp_db_sync($table) {
    require_once("core/class-tables.php");
    require_once('core/class-update.php');
    if ( $table === 'authors' ) {
        tp_update_db::fill_table_authors();
    }
    if ( $table === 'stud_meta' ) {
        tp_update_db::fill_table_stud_meta();
    }
}

/**
 * teachPress plugin activation
 * @param boolean $network_wide
 * @since 4.0.0
 */
function tp_activation ( $network_wide ) {
    global $wpdb;
    // it's a network activation
    if ( $network_wide ) {
        $old_blog = $wpdb->blogid;
        // Get all blog ids
        $blogids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM $wpdb->blogs"));
        foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            tp_install();
        }
        switch_to_blog($old_blog);
        return;
    }
    // it's a normal activation
    else {
        tp_install();
    }
}

/**
 * Activates the error reporting
 * @since 5.0.13
 */
function tp_activation_error_reporting () {
    file_put_contents(__DIR__.'/teachpress_activation_errors.html', ob_get_contents());
}

/**
 * Installation manager
 */
function tp_install() {
    require_once 'core/class-tables.php';
    tp_tables::create();
}

/**
 * Uninstallation manager
 */
function tp_uninstall() {
    require_once 'core/class-tables.php';
    tp_tables::remove();
}

/****************************/
/* tinyMCE plugin functions */
/****************************/

/**
 * Hooks functions for tinymce plugin into the correct filters
 * @since 5.0.0
 */
function tp_add_tinymce_button() {
    // the user need at least the edit_post capability (by default authors, editors, administrators)
    if ( !current_user_can( 'edit_posts' ) ) {
        return;
    }

    // the user need at least one of the teachpress capabilites
    if ( !current_user_can( 'use_teachpress' ) || !current_user_can( 'use_teachpress_courses' ) ) {
        return;
    }

    add_filter('mce_buttons', 'tp_register_tinymce_buttons');
    add_filter('mce_external_plugins', 'tp_register_tinymce_js');
}

/**
 * Adds a tinyMCE button for teachPress
 * @param array $buttons
 * @return array
 * @since 5.0.0
 */
function tp_register_tinymce_buttons ($buttons) {
    array_push($buttons, 'teachpress_tinymce');
    return $buttons;
}

/**
 * Adds a teachPress plugin to tinyMCE
 * @param array $plugins
 * @return array
 * @since 5.0.0
 */
function tp_register_tinymce_js ($plugins) {
    $plugins['teachpress_tinymce'] = plugins_url() . '/teachpress/js/tinymce-plugin.js';
    return $plugins;
}

/*********************/
/* Loading functions */
/*********************/

/**
 * Admin interface script loader
 */
function tp_backend_scripts() {
    // Define $page
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    wp_enqueue_style('teachpress-print-css', plugins_url() . '/teachpress/styles/print.css', false, false, 'print');
    // Load scripts only, if it's a teachpress page
    if ( strpos($page, 'teachpress') !== false || strpos($page, 'publications') !== false ) {
        wp_enqueue_script('teachpress-standard', plugins_url() . '/teachpress/js/backend.js');
        wp_enqueue_style('teachpress.css', plugins_url() . '/teachpress/styles/teachpress.css');
        wp_enqueue_script('media-upload');
        add_thickbox();
        // Load jQuery + ui plugins + plupload
        wp_enqueue_script(array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-resizable', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-dialog', 'plupload'));
        wp_enqueue_style('teachpress-jquery-ui.css', plugins_url() . '/teachpress/styles/jquery.ui.css');
        wp_enqueue_style('teachpress-jquery-ui-dialog.css', includes_url() . '/css/jquery-ui-dialog.min.css');
        $current_lang = ( version_compare( tp_get_wp_version() , '4.0', '>=') ) ? get_option('WPLANG') : WPLANG;
        $array_lang = array('de_DE','it_IT','es_ES', 'sk_SK');
        if ( in_array( $current_lang , $array_lang) ) {
            wp_enqueue_script('teachpress-datepicker-de', plugins_url() . '/teachpress/js/datepicker/jquery.ui.datepicker-' . $current_lang . '.js');
        }
    }
}

/**
 * Frontend script loader
 */
function tp_frontend_scripts() {
    $version = get_tp_version();
    echo chr(13) . chr(10) . '<!-- teachPress -->' . chr(13) . chr(10);
    echo '<script type="text/javascript" src="' . plugins_url() . '/teachpress/js/frontend.js?ver=' . $version . '"></script>' . chr(13) . chr(10);
    $value = get_tp_option('stylesheet');
    if ($value == '1') {
        echo '<link type="text/css" href="' . plugins_url() . '/teachpress/styles/teachpress_front.css?ver=' . $version . '" rel="stylesheet" />' . chr(13) . chr(10);
    }

    if ( TEACHPRESS_ALTMETRIC_SUPPORT === true ) {
        echo '<script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>' . chr(13) . chr(10);
    }

    echo '<!-- END teachPress -->' . chr(13) . chr(10);
}

/**
 * Load language files
 * @since 0.30
 */
function tp_language_support() {
    load_plugin_textdomain('teachpress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}

/**
 * Adds a link to the WordPress plugin menu
 * @param array $links
 * @param string $file
 * @return array
 */
function tp_plugin_link($links, $file){
    if ($file == plugin_basename(__FILE__)) {
        return array_merge($links, array( sprintf('<a href="options-general.php?page=teachpress/settings.php">%s</a>', __('Settings') ) ));
    }
    return $links;
}

// Register WordPress-Hooks
register_activation_hook( __FILE__, 'tp_activation');
add_action('init', 'tp_language_support');
add_action('init', 'tp_feed_init');
add_action('wp_ajax_teachpress', 'tp_ajax_callback');
add_action('wp_ajax_teachpressdocman', 'tp_ajax_doc_manager_callback');
add_action('admin_menu', 'tp_add_menu_settings');
add_action('wp_head', 'tp_frontend_scripts');
add_action('admin_init','tp_backend_scripts');
add_filter('plugin_action_links','tp_plugin_link', 10, 2);
add_action('wp_ajax_tp_document_upload', 'tp_handle_document_uploads' );

// Register tinyMCE Plugin
if ( version_compare( tp_get_wp_version() , '3.9', '>=') ) {
    add_action('admin_head', 'tp_add_tinymce_button');
    add_action('admin_head', 'tp_write_data_for_tinymce' );
 }

// Activation Error Reporting
if ( TEACHPRESS_ERROR_REPORTING === true ) {
    register_activation_hook( __FILE__, 'tp_activation_error_reporting' );
}

// Register course module
if ( TEACHPRESS_COURSE_MODULE === true ) {
    add_action('admin_menu', 'tp_add_menu');
    add_shortcode('tpdate', 'tp_date_shortcode');  // Deprecated
    add_shortcode('tpcourseinfo', 'tp_courseinfo_shortcode');
    add_shortcode('tpcoursedocs', 'tp_coursedocs_shortcode');
    add_shortcode('tpcourselist', 'tp_courselist_shortcode');
    add_shortcode('tpenrollments', 'tp_enrollments_shortcode');
    add_shortcode('tppost','tp_post_shortcode');
}

// register publication module
if ( TEACHPRESS_PUBLICATION_MODULE === true ) {
    add_action('admin_menu', 'tp_add_menu2');
    add_action('widgets_init', create_function('', 'return register_widget("tp_books_widget");'));
    add_shortcode('tpcloud', 'tp_cloud_shortcode');
    add_shortcode('tplist', 'tp_list_shortcode');
    add_shortcode('tpsingle', 'tp_single_shortcode');
    add_shortcode('tpbibtex', 'tp_bibtex_shortcode');
    add_shortcode('tpabstract', 'tp_abstract_shortcode');
    add_shortcode('tplinks', 'tp_links_shortcode');
    add_shortcode('tpsearch', 'tp_search_shortcode');
    add_shortcode('tpcite', 'tp_cite_shortcode');
    add_shortcode('tpref','tp_ref_shortcode');
}
