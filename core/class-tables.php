<?php
/**
 * This file contains all functions for creating a database for teachpress
 * 
 * @package teachpress\core\installation
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions for creating a database for teachpress
 * @package teachpress\core\installation
 * @since 5.0.0
 */
class TP_Tables {
    
    /**
     * Install teachPress database tables
     * @since 5.0.0
     */
    public static function create() {
        global $wpdb;
        self::add_capabilities();
        
        $charset_collate = self::get_charset();
        
        // Disable foreign key checks
        if ( TEACHPRESS_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 0");
        }
        
        // Settings
        self::add_table_settings($charset_collate);
        
        // Publications
        self::add_table_pub($charset_collate);
        self::add_table_pub_meta($charset_collate);
        self::add_table_pub_capabilities($charset_collate);
        self::add_table_pub_documents($charset_collate);
        self::add_table_pub_imports($charset_collate);
        self::add_table_tags($charset_collate);
        self::add_table_relation($charset_collate);
        self::add_table_user($charset_collate);
        self::add_table_authors($charset_collate);
        self::add_table_rel_pub_auth($charset_collate);
        self::add_table_monitored_sources($charset_collate);
        
        // Enable foreign key checks
        if ( TEACHPRESS_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 1");
        }
    }
    
      /**
     * Remove teachPress database tables
     * @since 5.0.0
     */
    public static function remove() {
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        $wpdb->query("DROP TABLE  
                                `" . TEACHPRESS_AUTHORS . "`, 
                                `" . TEACHPRESS_PUB . "`, 
                                `" . TEACHPRESS_PUB_CAPABILITIES . "`, 
                                `" . TEACHPRESS_PUB_DOCUMENTS . "`, 
                                `" . TEACHPRESS_PUB_META . "`, 
                                `" . TEACHPRESS_PUB_IMPORTS . "`,
                                `" . TEACHPRESS_RELATION ."`,
                                `" . TEACHPRESS_REL_PUB_AUTH . "`, 
                                `" . TEACHPRESS_MONITORED_SOURCES . "`,                                 
                                `" . TEACHPRESS_SETTINGS ."`, 
                                `" . TEACHPRESS_TAGS . "`, 
                                `" . TEACHPRESS_USER . "`");
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
    
    /**
     * Returns an associative array with table status informations (Name, Engine, Version, Rows,...)
     * @param string $table
     * @return array
     * @since 5.0.0
     */
    public static function check_table_status($table){
        global $wpdb;
        return $wpdb->get_row("SHOW TABLE STATUS FROM " . DB_NAME . " WHERE `Name` = '$table'", ARRAY_A);
    }
    
    /**
     * Tests if the engine for the selected table is InnoDB. If not, the function changes the engine.
     * @param string $table
     * @since 5.0.0
     * @access private
     */
    private static function change_engine($table){
        global $wpdb;
        $db_info = self::check_table_status($table);
        if ( $db_info['Engine'] != 'InnoDB' ) {
            $wpdb->query("ALTER TABLE " . $table . " ENGINE = INNODB");
        }
    }
    
    /**
     * Create table teachpress_settings
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_settings($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_SETTINGS . "'") == TEACHPRESS_SETTINGS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_SETTINGS . " (
                    `setting_id` INT UNSIGNED AUTO_INCREMENT,
                    `variable` VARCHAR (100),
                    `value` LONGTEXT,
                    `category` VARCHAR (100),
                    PRIMARY KEY (setting_id)
                    ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_SETTINGS);
        
        // Add default values
        self::add_default_settings();
    }
    
    /**
     * Add default system settings
     * @since 5.0.0
     */
    public static function add_default_settings(){
        global $wpdb;
        $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]";
        $version = get_tp_version();
        
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('db-version', '$version', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('stylesheet', '1', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_page_publications', 'page', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_auto', '0', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_template', '$value', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_category', '', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('import_overwrite', '1', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('convert_bibtex', '0', 'system')");
       
    }
    
    /**
     * Create table teachpress_pub
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_pub($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB . "'") == TEACHPRESS_PUB ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB . " (
                    `pub_id` INT UNSIGNED AUTO_INCREMENT,
                    `title` VARCHAR(500),
                    `type` VARCHAR (50),
                    `bibtex` VARCHAR (100),
                    `author` VARCHAR (3000),
                    `editor` VARCHAR (3000),
                    `isbn` VARCHAR (50),
                    `url` TEXT,
                    `date` DATE,
                    `urldate` DATE,
                    `booktitle` VARCHAR (1000),
                    `issuetitle` VARCHAR (200),
                    `journal` VARCHAR(200),
                    `issue` VARCHAR(40),
                    `volume` VARCHAR(40),
                    `number` VARCHAR(40),
                    `pages` VARCHAR(40),
                    `publisher` VARCHAR (500),
                    `address` VARCHAR (300),
                    `edition` VARCHAR (100),
                    `chapter` VARCHAR (40),
                    `institution` VARCHAR (500),
                    `organization` VARCHAR (500),
                    `school` VARCHAR (200),
                    `series` VARCHAR (200),
                    `crossref` VARCHAR (100),
                    `abstract` TEXT,
                    `howpublished` VARCHAR (200),
                    `key` VARCHAR (100),
                    `techtype` VARCHAR (200),
                    `comment` TEXT,
                    `note` TEXT,
                    `image_url` VARCHAR (400),
                    `image_target` VARCHAR (100),
                    `image_ext` VARCHAR (400),
                    `doi` VARCHAR (100),
                    `is_isbn` INT(1),
                    `rel_page` INT,
                    `status` VARCHAR (100) DEFAULT 'published',
                    `added` DATETIME,
                    `modified` DATETIME,
                    `use_capabilities` INT(1),
                    `import_id` INT,
                    PRIMARY KEY (pub_id),
                    KEY `ind_type` (`type`),
                    KEY `ind_date` (`date`),
                    KEY `ind_import_id` (`import_id`),
                    KEY `ind_key` (`key`),
                    KEY `ind_bibtex_key` (`bibtex`),
                    KEY `ind_status` (`status`)
                ) ROW_FORMAT=DYNAMIC $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_PUB);
    }
    
    /**
     * Create table teachpress_pub_meta
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_pub_meta($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB_META . "'") == TEACHPRESS_PUB_META ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB_META . " (
                    `meta_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `meta_key` VARCHAR(255),
                    `meta_value` TEXT,
                    PRIMARY KEY (meta_id),
                    KEY `ind_pub_id` (`pub_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_PUB_META);
    }
    
        /**
     * Create table pub_capabilities
     * @param string $charset_collate
     * @since 6.0.0
     */
    public static function add_table_pub_capabilities($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB_CAPABILITIES . "'") == TEACHPRESS_PUB_CAPABILITIES ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB_CAPABILITIES . " (
                    `cap_id` INT UNSIGNED AUTO_INCREMENT,
                    `wp_id` INT UNSIGNED,
                    `pub_id` INT UNSIGNED,
                    `capability` VARCHAR(100),
                    PRIMARY KEY (`cap_id`),
                    KEY `ind_pub_id` (`pub_id`),
                    KEY `ind_wp_id` (`wp_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_PUB_CAPABILITIES);
    }
    
    /**
     * Create table pub_documents
     * @param string $charset_collate
     * @since 6.0.0
     */
    public static function add_table_pub_documents($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB_DOCUMENTS . "'") == TEACHPRESS_PUB_DOCUMENTS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB_DOCUMENTS . " (
                    `doc_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(500),
                    `path` VARCHAR(500),
                    `added` DATETIME,
                    `size` BIGINT,
                    `sort` INT,
                    `pub_id` INT UNSIGNED,
                    PRIMARY KEY (doc_id),
                    KEY `ind_pub_id` (`pub_id`)
                ) $charset_collate;");
         
        // test engine
        self::change_engine(TEACHPRESS_PUB_DOCUMENTS);
    }
    
    /**
     * Create table pub_imports
     * @param string $charset_collate
     * @since 6.1.0
     */
    public static function add_table_pub_imports($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB_IMPORTS . "'") == TEACHPRESS_PUB_IMPORTS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB_IMPORTS . " (
                    `id` INT UNSIGNED AUTO_INCREMENT,
                    `wp_id` INT UNSIGNED,
                    `date` DATETIME,
                    PRIMARY KEY (id)
                ) $charset_collate;");
         
        // test engine
        self::change_engine(TEACHPRESS_PUB_DOCUMENTS);
    }
    
    /**
     * Create table teachpress_tags
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_tags($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_TAGS . "'") == TEACHPRESS_TAGS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_TAGS . " (
                    `tag_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(300),
                    PRIMARY KEY (tag_id),
                    KEY `ind_tag_name` (`name`)
                ) ROW_FORMAT=DYNAMIC $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_TAGS);
    }
    
    /**
     * Create table teachpress_relation
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_relation($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_RELATION . "'") == TEACHPRESS_RELATION ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_RELATION . " (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `tag_id` INT UNSIGNED,
                    PRIMARY KEY (con_id),
                    KEY `ind_pub_id` (`pub_id`),
                    KEY `ind_tag_id` (`tag_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_RELATION);
    }
    
    /**
     * Create table teachpress_user
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_user($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_USER . "'") == TEACHPRESS_USER ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_USER . " (
                    `bookmark_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `user` INT UNSIGNED,
                    PRIMARY KEY (bookmark_id),
                    KEY `ind_pub_id` (`pub_id`),
                    KEY `ind_user` (`user`)
                    ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_USER);
    }
    
    /**
     * Create table teachpress_authors
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_authors($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_AUTHORS . "'") == TEACHPRESS_AUTHORS ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_AUTHORS . " (
                    `author_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(500),
                    `sort_name` VARCHAR(500),
                    PRIMARY KEY (author_id),
                    KEY `ind_sort_name` (`sort_name`)
                ) ROW_FORMAT=DYNAMIC $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_AUTHORS);
    }
    
    /**
     * Create table teachpress_rel_pub_auth
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_rel_pub_auth($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_REL_PUB_AUTH . "'") == TEACHPRESS_REL_PUB_AUTH ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_REL_PUB_AUTH . " (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `author_id` INT UNSIGNED,
                    `is_author` INT(1),
                    `is_editor` INT(1),
                    PRIMARY KEY (con_id),
                    KEY `ind_pub_id` (`pub_id`),
                    KEY `ind_author_id` (`author_id`)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_REL_PUB_AUTH);
    }

    /**
     * Create table teachpress_monitored_sources
     * @param string $charset_collate
     * @since 9.0.0
     */
    public static function add_table_monitored_sources($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_MONITORED_SOURCES . "'") == TEACHPRESS_MONITORED_SOURCES ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_MONITORED_SOURCES . " (
                    `src_id` INT UNSIGNED AUTO_INCREMENT,
                    `md5` VARCHAR(32) DEFAULT '',
                    `name` VARCHAR(4096),
                    `last_res` VARCHAR(1024) DEFAULT '',
                    PRIMARY KEY (src_id)
                ) $charset_collate;");
        
        // test engine
        self::change_engine(TEACHPRESS_MONITORED_SOURCES);
    }
    
    /**
     * Add capabilities
     * @since 5.0.0
     */
    private static function add_capabilities() {
        // 
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachpress') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress');
        }
    }
    
    /**
     * charset & collate like WordPress
     * @since 5.0.0
     */
    public static function get_charset() {
        global $wpdb; 
        $charset_collate = '';
        if ( ! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }	
        if ( ! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
        $charset_collate .= " ENGINE = INNODB";
        return $charset_collate;
    }
    
}
