<?php
/*
 * If you want, you can owerwrite this parameters in your wp-config.php
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

if ( !defined('TEACHPRESS_PUB_IMPORTS') ) {
    /**
     * This constant defines the table name for teachpress_pub_imports.
     * @since 6.0.0
    */
    define('TEACHPRESS_PUB_IMPORTS', $wpdb->prefix . 'teachpress_pub_imports');}    
    
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
    define('TEACHPRESS_TEMPLATE_PATH', TEACHPRESS_GLOBAL_PATH . 'templates/');}

if ( !defined('TEACHPRESS_TEMPLATE_URL') ) {
    /**
     * This value defines the template url
     * @since 6.0.0
    */
    define('TEACHPRESS_TEMPLATE_URL', plugins_url() . '/teachpress/templates/');}

if ( !defined('TEACHPRESS_ALTMETRIC_SUPPORT') ) {
    /**
     * This value defines if the altmetric support is available (loads external sources)
     * @since 6.0.0
    */
    define('TEACHPRESS_ALTMETRIC_SUPPORT', false);}
    
if ( !defined('TEACHPRESS_DOI_RESOLVER') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 6.1.1
    */
    define('TEACHPRESS_DOI_RESOLVER', 'https://dx.doi.org/');}
    
if ( !defined('TEACHPRESS_LOAD_ACADEMICONS') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 7.0
    */
    define('TEACHPRESS_LOAD_ACADEMICONS', true);}
    
if ( !defined('TEACHPRESS_LOAD_FONT_AWESOME') ) {
    /**
     * This value defines if the URL for the DOI resolve service
     * @since 7.0
    */
    define('TEACHPRESS_LOAD_FONT_AWESOME', true);}
    
if ( !defined('TEACHPRESS_MENU_POSITION') ) {
    /**
     * This value defines the position in the admin menu. 
     * 
     * Options:
     * null         --> position at the end of the default menu
     * int [0..99]  --> individual position
     * For more see:
     * https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure
     * 
     * @since 7.0
    */
    define('TEACHPRESS_MENU_POSITION', null);}
    
if ( !defined('TEACHPRESS_DEBUG') ) { 
    /**
     * This value defines if the debug mode is active or not
     * @since 7.2
     */
    define('TEACHPRESS_DEBUG', false);
}

