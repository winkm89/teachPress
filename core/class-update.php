<?php
/**
 * This file contains all functions for updating a teachpress database
 *
 * @package teachpress\core\update
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 4.2.0
 */

/**
 * This class contains all functions for updating a teachpress database
 * @package teachpress\core\update
 * @since 4.2.0
 */
class tp_update_db {

    /**
     * Execute this function to start a database update
     * @since 4.2.0
     */
    public static function force_update () {
        global $wpdb;
        $db_version = get_tp_option('db-version');
        $software_version = get_tp_version();
        $update_level = '0';

        // Fallback for very old teachPress systems
        if ( $db_version == '' ) {
            $db_version = $wpdb->get_var("SELECT `value` FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'db-version'");
        }

        // if is the current one
        if ( $db_version === $software_version ) {
            get_tp_message( __('An update is not necessary.','teachpress') );
            return;
        }

        // charset & collate like WordPress
        $charset_collate = ( !empty($wpdb->charset) ) ? "CHARACTER SET $wpdb->charset" : "CHARACTER SET utf8";
        if ( ! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
        else {
            $charset_collate .= " COLLATE utf8_general_ci";
        }

        // set capabilities
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachpress') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress');
        }
        if ( !$role->has_cap('use_teachpress_courses') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress_courses');
        }

        // Disable foreign key checks
        if ( TEACHPRESS_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 0");
        }

        // force updates to reach structure of teachPress 2.0.0
        if ( $db_version[0] === '0' || $db_version[0] === '1' ) {
            tp_update_db::upgrade_table_teachpress_ver($charset_collate);
            tp_update_db::upgrade_table_teachpress_beziehung($charset_collate);
            tp_update_db::upgrade_table_teachpress_kursbelegung($charset_collate);
            tp_update_db::upgrade_table_teachpress_einstellungen($charset_collate);
            tp_update_db::upgrade_table_teachpress_stud_to_20($charset_collate);
            tp_update_db::upgrade_table_teachpress_pub_to_04($charset_collate);
            tp_update_db::upgrade_table_teachpress_pub_to_20($charset_collate);
            $update_level = '2';
        }

        // force updates to reach structure of teachPress 3.0.0
        if ( $db_version[0] === '2' || $update_level === '2' ) {
            tp_update_db::upgrade_to_30();
            $update_level = '3';
        }

        // force updates to reach structure of teachPress 3.1.0
        if ( $db_version[0] === '3' || $update_level === '3' ) {
            tp_update_db::upgrade_to_31($charset_collate);
            $update_level = '4';
        }

        // force updates to reach structure of teachPress 4.2.0
        if ( $db_version[0] === '4' || $update_level === '4' ) {
            tp_update_db::upgrade_to_40($charset_collate);
            tp_update_db::upgrade_to_41();
            tp_update_db::upgrade_to_42($charset_collate);
            $update_level = '5';
        }

        // force updates to reach structure of teachPress 5.0.0
        if ( $db_version[0] === '5' || $update_level === '5' ) {
            tp_update_db::upgrade_to_50($charset_collate);
            $update_level = '6';
        }

        // force updates to reach structure of teachPress 6.0.0
        if ( $db_version[0] === '6' || $update_level === '6' ) {
            tp_update_db::upgrade_to_60();
        }

        // Add teachPress options
        tp_update_db::add_options();

        // Enable foreign key checks
        if ( TEACHPRESS_FOREIGN_KEY_CHECKS === false ) {
            $wpdb->query("SET foreign_key_checks = 1");
        }

        tp_update_db::finalize_update($software_version);
   }

   /**
    * Replace the old table "teachpress_ver" with "teachpress_courses" and copy all data
    * @param string $charset_collate
    * @since 4.2.0
    */
   private static function upgrade_table_teachpress_ver ($charset_collate) {
        global $wpdb;
        $teachpress_ver = $wpdb->prefix . 'teachpress_ver';

        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_ver LIKE 'veranstaltungs_id'") == '1') {
            // create new table teachpress_courses
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_COURSES . "'") != TEACHPRESS_COURSES) {
                $sql = "CREATE TABLE " . TEACHPRESS_COURSES . " ( `course_id` INT UNSIGNED AUTO_INCREMENT, `name` VARCHAR(100), `type` VARCHAR(100), `room` VARCHAR(100), `lecturer` VARCHAR (100), `date` VARCHAR(60), `places` INT(4), `start` DATETIME, `end` DATETIME, `semester` VARCHAR(100), `comment` VARCHAR(500), `rel_page` INT, `parent` INT, `visible` INT(1), `waitinglist` INT(1), `image_url` VARCHAR(400), `strict_signup` INT(1), PRIMARY KEY (course_id)
                ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_ver");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHPRESS_COURSES . " (`course_id`, `name`, `type`, `room`, `lecturer`, `date`, `places`, `start`, `end`, `semester`, `comment`, `rel_page`, `parent`, `visible`, `waitinglist`) VALUES('$row->veranstaltungs_id', '$row->name', '$row->vtyp', '$row->raum', '$row->dozent', '$row->termin', '$row->plaetze', '$row->startein', '$row->endein', '$row->semester', '$row->bemerkungen', '$row->rel_page', '$row->parent', '$row->sichtbar', '$row->warteliste')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_ver");
        }
    }

    /**
     * Replace the old table "teachpress_beziehung" with "teachpress_relation" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_beziehung ($charset_collate) {
        global $wpdb;
        $teachpress_beziehung = $wpdb->prefix . 'teachpress_beziehung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_beziehung LIKE 'belegungs_id'") == '1') {
            // create new table teachpress_relation
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_RELATION . "'") != TEACHPRESS_RELATION) {
                $sql = "CREATE TABLE " . TEACHPRESS_RELATION . " ( `con_id` INT UNSIGNED AUTO_INCREMENT, `pub_id` INT UNSIGNED, `tag_id` INT UNSIGNED, FOREIGN KEY (pub_id) REFERENCES " . TEACHPRESS_PUB . " (pub_id), FOREIGN KEY (tag_id) REFERENCES " . TEACHPRESS_TAGS . " (tag_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_beziehung");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHPRESS_RELATION . " (`con_id`, `pub_id`, `tag_id`) VALUES('$row->belegungs_id', '$row->pub_id', '$row->tag_id')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_beziehung");
        }
    }

    /**
     * Replace the old table "teachpress_kursbelegung" with "teachpress_signup" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_kursbelegung ($charset_collate) {
        global $wpdb;
        $teachpress_kursbelegung = $wpdb->prefix . 'teachpress_kursbelegung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_kursbelegung LIKE 'belegungs_id'") == '1') {
            // create new table teachpress_signup
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_SIGNUP . "'") != TEACHPRESS_SIGNUP) {
                $sql = "CREATE TABLE " . TEACHPRESS_SIGNUP . " (`con_id` INT UNSIGNED AUTO_INCREMENT, `course_id` INT UNSIGNED, `wp_id` INT UNSIGNED, `waitinglist` INT(1) UNSIGNED, `date` DATETIME, FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id), FOREIGN KEY (wp_id) REFERENCES " . TEACHPRESS_STUD . " (wp_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_kursbelegung");
            foreach ($row as $row) {
                $sql = "INSERT INTO " . TEACHPRESS_SIGNUP . " (`con_id`, `course_id`, `wp_id`, `waitinglist`, `date`) VALUES('$row->belegungs_id', '$row->veranstaltungs_id', '$row->wp_id', '$row->warteliste', '$row->datum')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_kursbelegung");
        }
    }

    /**
     * Replace the old table "teachpress_einstellungen" with "teachpress_settings" and copy all data
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_einstellungen ($charset_collate) {
        global $wpdb;
        $teachpress_einstellungen = $wpdb->prefix . 'teachpress_einstellungen';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_einstellungen LIKE 'einstellungs_id'") == '1') {
            // create new table teachpress_settings
            if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_SETTINGS . "'") != TEACHPRESS_SETTINGS) {
                $sql = "CREATE TABLE " . TEACHPRESS_SETTINGS . " ( `setting_id` INT UNSIGNED AUTO_INCREMENT, `variable` VARCHAR (100), `value` VARCHAR (400), `category` VARCHAR (100), PRIMARY KEY (setting_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_einstellungen");
            foreach ($row as $row) {
                if ($row->category == 'studiengang') {
                    $row->category = 'course_of_studies';
                }
                if ($row->category == 'veranstaltungstyp') {
                    $row->category = 'course_type';
                }
                $sql = "INSERT INTO " . TEACHPRESS_SETTINGS . " (`setting_id`, `variable`, `value`, `category`) VALUES('$row->einstellungs_id', '$row->variable', '$row->wert', '$row->category')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_einstellungen");
        }
    }

    /**
     * Upgrade table "teachPress_stud" to teachPress 2.x structure
     * @param string $charset_collate
     */
    private static function upgrade_table_teachpress_stud_to_20 ($charset_collate) {
        global $wpdb;
        // rename column vorname to firstname
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'vorname'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `vorname` `firstname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column nachname to lastname
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'nachname'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `nachname` `lastname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column studiengang to course_of_studies
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'studiengang'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `studiengang` `course_of_studies` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column urzkurz to userlogin
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'urzkurz'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `urzkurz` `userlogin` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column gebdat to birthday
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'gebdat'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `gebdat` `birthday` DATE $charset_collate NULL DEFAULT NULL");
        }
        // rename column fachsemester to semesternumber
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'fachsemester'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . "CHANGE `fachsemester` `semesternumber` INT(2) NULL DEFAULT NULL");
        }
        // rename column matrikel to matriculation_number
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_STUD . " LIKE 'matrikel'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " CHANGE `matrikel` `matriculation_number` INT NULL DEFAULT NULL");
        }
    }

    /**
     * Upgrade table "teachPress_pub" to teachPress 0.40 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_pub_to_04 ($charset_collate) {
        global $wpdb;
        // add column image_url
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'image_url'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `image_url` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // add colum rel_page
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'rel_page'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `rel_page` INT NULL AFTER `image_url`");
        }
        // add column is_isbn
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'is_isbn'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `is_isbn` INT(1) NULL DEFAULT NULL AFTER `rel_page`");
        }
    }

    /**
     * Upgrade table "teachPress_pub" to teachPress 2.x structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_pub_to_20 ($charset_collate) {
        global $wpdb;
        // Rename sort to date
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'sort'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE  `sort`  `date` DATE NULL DEFAULT NULL");
        }
        // Rename typ to type
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . "LIKE 'typ'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `typ`  `type` VARCHAR( 50 ) $charset_collate NULL DEFAULT NULL");
            // remane publication types
            $row = $wpdb->get_results("SELECT pub_id, type  FROM " . TEACHPRESS_PUB . "");
            foreach ($row as $row) {
                if ($row->type === 'Buch') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'book' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Chapter in book') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'inbook' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Conference paper') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'proceedings' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Journal article') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'article' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Vortrag') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'presentation' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Bericht') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'techreport' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Sonstiges') {
                    $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET type = 'misc' WHERE pub_id = '$row->pub_id'");
                }
            }
        }
        // Rename autor to author
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'autor'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `autor` `author` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // Drop column jahr
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'jahr'") == '1' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " DROP `jahr`");
        }
        // insert column bibtex
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'bibtex'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `bibtex` VARCHAR(50) $charset_collate NULL DEFAULT NULL AFTER `type`");
        }
        // insert column editor
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'editor'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `editor` VARCHAR(500) $charset_collate NULL DEFAULT NULL AFTER `author`");
        }
        // insert column booktitle
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'booktitle'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `booktitle` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `date`");
        }
        // insert column journal
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'journal'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `journal` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `booktitle`");
        }
        // insert column volume
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'volume'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `volume` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `journal`");
        }
        // insert column number
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'number'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `number` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `volume`");
        }
        // insert column pages
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'pages'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `pages` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `number`");
        }
        // insert column publisher
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'publisher'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `publisher` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `pages`");
        }
        // insert column address
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'address'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `address` VARCHAR(300) $charset_collate NULL DEFAULT NULL AFTER `publisher`");
        }
        // insert column edition
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'edition'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `edition` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `address`");
        }
        // insert column chapter
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'chapter'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `chapter` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `edition`");
        }
        // insert column institution
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'institution'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `institution` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `chapter`");
        }
        // insert column organization
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'organization'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `organization` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `institution`");
        }
        // insert column school
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'school'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `school` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `organization`");
        }
        // insert column series
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'series'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `series` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `school`");
        }
        // insert column crossref
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'crossref'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `crossref` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `series`");
        }
        // insert column abstract
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'abstract'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `abstract` TEXT $charset_collate NULL DEFAULT NULL AFTER `crossref`");
        }
        // insert column howpublished
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'howpublished'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `howpublished` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `abstract`");
        }
        // insert column key
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'key'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `key` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `howpublished`");
        }
        // insert column techtype
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'techtype'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `techtype` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `key`");
        }
        // insert column note
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'note'") == '0' ) {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `note` TEXT $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // drop column verlag
        if ( $wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'verlag'") == '1') {
            $row = $wpdb->get_results("SELECT pub_id, verlag  FROM " . TEACHPRESS_PUB . "");
            foreach ($row as $row) {
                $wpdb->query("UPDATE " . TEACHPRESS_PUB . " SET `publisher` = '$row->verlag' WHERE `pub_id` = '$row->pub_id'");
            }
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " DROP `verlag`");
        }
    }

    /**
     * Upgrade table "teachpress_courses" to teachPress 3.0 structure
     * @since 4.2.0
     */
    private static function upgrade_to_30 () {
        global $wpdb;

        // teachpress_courses
        // change type in column start
        $wpdb->get_results("SELECT `start` FROM " . TEACHPRESS_COURSES);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHPRESS_COURSES . "` CHANGE  `start`  `start` DATETIME NULL DEFAULT NULL");
        }
        // change type in column end
        $wpdb->get_results("SELECT `end` FROM " . TEACHPRESS_COURSES);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHPRESS_COURSES . "` CHANGE  `end`  `end` DATETIME NULL DEFAULT NULL");
        }
        // insert column strict_signup
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_COURSES . " LIKE 'strict_signup'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_COURSES . " ADD `strict_signup` INT( 1 ) NULL DEFAULT NULL");
        }

        // teachpress_signup
        // Change type in column date
        $wpdb->get_results("SELECT `date` FROM " . TEACHPRESS_SIGNUP);
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHPRESS_SIGNUP . "` CHANGE `date` `date` DATETIME NULL DEFAULT NULL");
        }
    }

    /**
     * Database upgrade to teachPress 3.1.3 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_31 ($charset_collate) {
        global $wpdb;
        // change type in column url
        $wpdb->get_results("SELECT `url` FROM " . TEACHPRESS_PUB);
        if ($wpdb->get_col_info('type', 0) == 'string') {
            $wpdb->query("ALTER TABLE `" . TEACHPRESS_PUB . "` CHANGE `url` `url` TEXT $charset_collate NULL DEFAULT NULL");
        }
        // drop table teachpress_log
        $table_name = $wpdb->prefix . 'teachpress_log';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $wpdb->query("DROP TABLE " . $table_name . "");
        }
        // Drop column fplaces
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_COURSES . " LIKE 'fplaces'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_COURSES . " DROP `fplaces`");
        }
        // Change type in column birthday
        // Fixed a bug with the installer in teachpress versions 2.0.0 to 2.1.0
        $wpdb->get_results("SELECT `birthday` FROM " . TEACHPRESS_STUD);
        if ($wpdb->get_col_info('type', 0) != 'date') {
            $wpdb->query("ALTER TABLE `" . TEACHPRESS_STUD . "` CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL");
        }
        // Change database engine
        $wpdb->query("ALTER TABLE " . TEACHPRESS_STUD . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_SETTINGS . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_TAGS . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_COURSES . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_SIGNUP . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_RELATION . " ENGINE = INNODB");
        $wpdb->query("ALTER TABLE " . TEACHPRESS_USER . " ENGINE = INNODB");
    }

    /**
     * Database upgrade to teachPress 4.0.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_40 ($charset_collate) {
        global $wpdb;
        // rename column name to title
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'name'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `name` `title` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'urldate'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `urldate` DATE NULL DEFAULT NULL AFTER `date`");
        }
    }

    /**
     * Database upgrade to teachPress 4.1.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_41 () {
        global $wpdb;
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'issuetitle'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `issuetitle` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `booktitle`");
        }
    }

    /**
     * Database upgrade to teachPress 4.2.0 structure
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_42 ($charset_collate) {
        global $wpdb;
        // expand char limit for tp_settings::value
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_SETTINGS . " LIKE 'value'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_SETTINGS . " CHANGE `value` `value` TEXT $charset_collate NULL DEFAULT NULL");
        }
    }

    /**
     * Database upgrade to teachPress 5.0.0 structure
     * @param string $charset_collate
     * @since 5.0.0
     */
    private static function upgrade_to_50 ($charset_collate){
        global $wpdb;
        $charset = tp_tables::get_charset();
        // add new tables
        tp_tables::add_table_artefacts($charset);
        tp_tables::add_table_assessments($charset);
        tp_tables::add_table_course_capabilites($charset);
        tp_tables::add_table_course_documents($charset);
        tp_tables::add_table_course_meta($charset);
        tp_tables::add_table_authors($charset);
        tp_tables::add_table_rel_pub_auth($charset);
        tp_tables::add_table_stud_meta($charset);
        tp_tables::add_table_pub_meta($charset);

        // add column use_capabilites to table teachpress_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_COURSES . " LIKE 'use_capabilites'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_COURSES . " ADD `use_capabilites` INT( 1 ) NULL DEFAULT NULL AFTER `strict_signup`");
        }

        // add column doi to table teachpress_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'doi'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `doi` VARCHAR( 100 ) NULL DEFAULT NULL AFTER `image_url`");
        }

        // add column status to table teachpress_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'status'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `status` VARCHAR( 100 ) NULL DEFAULT 'published' AFTER `rel_page`");
        }

        // add column added to table teachpress_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'added'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `added` DATETIME NULL DEFAULT NULL AFTER `status`");
        }

        // add column modified to table teachpress_pub
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'modified'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `modified` DATETIME NULL DEFAULT NULL AFTER `added`");
        }

        // add column size to table teachpress_course_documents
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_COURSE_DOCUMENTS . " LIKE 'size'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_COURSE_DOCUMENTS . " ADD `size` BIGINT NULL DEFAULT NULL AFTER `added`");
        }

        // add column sort_name to table teachpress_authors
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_AUTHORS . " LIKE 'sort_name'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_AUTHORS . " ADD `sort_name` VARCHAR( 500 ) NULL DEFAULT NULL AFTER `name`");
        }

        // expand char limit for tp_settings::value
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_SETTINGS . " LIKE 'value'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_SETTINGS . " CHANGE `value` `value` LONGTEXT $charset_collate NULL DEFAULT NULL");
        }

        // expand char limit for tp_publications::author
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'author'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `author` `author` VARCHAR (3000) $charset_collate NULL DEFAULT NULL");
        }

        // expand char limit for tp_publications::editor
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'editor'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `editor` `editor` VARCHAR (3000) $charset_collate NULL DEFAULT NULL");
        }

        // expand char limit for tp_publications::institution
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'institution'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `institution` `institution` VARCHAR (500) $charset_collate NULL DEFAULT NULL");
        }

        // expand char limit for tp_publications::organization
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'organization'") == '1') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " CHANGE `organization` `organization` VARCHAR (500) $charset_collate NULL DEFAULT NULL");
        }

    }

    /**
     * Database upgrade to teachPress 6.0.0 structure
     * @since 6.0.0
     */
    private static function upgrade_to_60 (){
        global $wpdb;
        $charset = tp_tables::get_charset();

        // add new tables
        tp_tables::add_table_pub_capabilites($charset);
        tp_tables::add_table_pub_documents($charset);
        tp_tables::add_table_pub_imports($charset);

        // add column use_capabilites to table teachpress_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'use_capabilites'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `use_capabilites` INT( 1 ) NULL DEFAULT NULL AFTER `modified`");
        }

        // add column import_id to table teachpress_courses
        if ($wpdb->query("SHOW COLUMNS FROM " . TEACHPRESS_PUB . " LIKE 'import_id'") == '0') {
            $wpdb->query("ALTER TABLE " . TEACHPRESS_PUB . " ADD `import_id` INT NULL DEFAULT NULL AFTER `use_capabilites`");
        }

    }

    /**
     * Checks if the table teachpress_authors needs to be filled. Returns false if not.
     * @return boolean
     * @since 5.0.0
     */
    public static function check_table_authors () {
        global $wpdb;
        $test_pub = $wpdb->get_var("SELECT COUNT(`pub_id`) FROM " . TEACHPRESS_PUB);
        $test_authors = $wpdb->get_var("SELECT COUNT(`author_id`) FROM " . TEACHPRESS_AUTHORS);
        if ( $test_authors == 0 && $test_pub != 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the table teachpress_stud_meta needs to be filled. Returns false if not.
     * @return boolean
     * @since 5.0.0
     */
    public static function check_table_stud_meta () {
        global $wpdb;
        $test_stud = $wpdb->get_var("SELECT COUNT(`wp_id`) FROM " . TEACHPRESS_STUD);
        $test_stud_meta = $wpdb->get_var("SELECT COUNT(`wp_id`) FROM " . TEACHPRESS_STUD_META);
        if ( $test_stud != 0 && $test_stud_meta == 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Prepares and Returns the statement for adding all author - publications relations in one SQL Query
     * Returns a string like: ('pub_id', 'author_id', 'is_author', 'is_editor'), ('pub_id', 'author_id', 'is_author', 'is_editor'),...
     *
     * @param int $pub_id               The ID of the publication
     * @param string $input_string      A author / editor string
     * @param string $delimiter         default is ','
     * @param string $rel_type          authors or editors
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function prepare_relation ($pub_id, $input_string, $delimiter = ',', $rel_type = 'authors') {
        global $wpdb;
        $pub_id = intval($pub_id);
        $array = explode($delimiter, $input_string);
        $return = '';
        foreach($array as $element) {
            $element = trim($element);

            if ( $element === '' ) {
                continue;
            }

            $element = esc_sql( htmlspecialchars($element) );

            // check if element exists
            $check = $wpdb->get_var("SELECT `author_id` FROM " . TEACHPRESS_AUTHORS . " WHERE `name` = '$element'");

            // if element not exists
            if ( $check === NULL ){
                $check = tp_authors::add_author( $element, tp_bibtex::get_lastname($element) );
            }

            // prepare relation
            $is_author = ( $rel_type === 'authors' ) ? 1 : 0;
            $is_editor = ( $rel_type === 'editors' ) ? 1 : 0;
            $check = intval($check);
            $return = ($return === '') ? "($pub_id, $check, $is_author, $is_editor)" : $return . ", ($pub_id, $check, $is_author, $is_editor)";

        }
        return $return;
    }

    /**
     * Use this function to fill up the table teachpress_authors with data from teachpress_pub
     * @param string $limit     A normal SQL limit like 0,500. By default this value is not set.
     * @since 5.0.0
     */
    public static function fill_table_authors ($limit = '') {
        global $wpdb;

        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);

        if ( $limit !== '' ) {
            $limit = ' LIMIT ' . esc_sql($limit);
        }

        $relation = '';
        get_tp_message( __('Step 1: Read data and add authors','teachpress') );
        $pubs = $wpdb->get_results("SELECT pub_id, author, editor FROM " . TEACHPRESS_PUB . $limit, ARRAY_A);
        foreach ( $pubs as $row ) {
            if ( $row['author'] != '' ) {
                $relation .= self::prepare_relation($row['pub_id'], $row['author'], ' and ', 'authors') . ', ';
            }
            if ( $row['editor'] != '' ) {
                $relation .= self::prepare_relation($row['pub_id'], $row['editor'], ' and ', 'editors') . ', ';
            }
        }
        $relation = substr($relation, 0, -2);
        $relation = str_replace(', ,', ',', $relation);
        get_tp_message( __('Step 2: Add relations between authors and publications','teachpress') );
        $wpdb->query("INSERT INTO " . TEACHPRESS_REL_PUB_AUTH . " (`pub_id`, `author_id`, `is_author`, `is_editor`) VALUES $relation");
        get_tp_message( __('Update successful','teachpress') );
    }

    /**
     * Use this function to transfer all data from no longer used columns of teachpress_stud to teachpress_stud_meta
     * @since 5.0.0
     */
    public static function fill_table_stud_meta () {
        global $wpdb;
        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);
        $relation = '';
        get_tp_message( __('Step 1: Read and prepare data','teachpress') );
        $students = $wpdb->get_results("SELECT wp_id, course_of_studies, birthday, semesternumber, matriculation_number FROM " . TEACHPRESS_STUD, ARRAY_A);
        foreach ( $students as $row ) {
            $relation .= "(" . $row['wp_id'] . ", 'course_of_studies', '" . $row['course_of_studies'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'birthday', '" . $row['birthday'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'semester_number', '" . $row['semesternumber'] . "'), ";
            $relation .= "(" . $row['wp_id'] . ", 'matriculation_number', '" . $row['matriculation_number'] . "'), ";
        }

        $relation = substr($relation, 0, -2);
        get_tp_message( __('Step 2: Insert data','teachpress') );
        $wpdb->query("INSERT INTO " . TEACHPRESS_STUD_META . " (`wp_id`, `meta_key`, `meta_value`) VALUES $relation");
        get_tp_message( __('Update successful','teachpress') );
    }

    /**
     * Add possible missing options
     * @since 4.2.0
     */
    private static function add_options () {
        global $wpdb;
        // Stylesheet
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE variable = 'stylesheet' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (variable, value, category) VALUES ('stylesheet', '1', 'system')");
        }
        // Sign out
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE variable = 'sign_out' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . "(variable, value, category) VALUES ('sign_out', '0', 'system')");
        }
        // Login
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE variable = 'login' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (variable, value, category) VALUES ('login', 'std', 'system')");
        }
        // rel_page_courses
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE variable = 'rel_page_courses' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (variable, value, category) VALUES ('rel_page_courses', 'page', 'system')");
        }
        // rel_page_publications
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE variable = 'rel_page_publications' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (variable, value, category) VALUES ('rel_page_publications', 'page', 'system')");
        }

        /**** since version 4.2.0 ****/

        // rel_content_template
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'rel_content_template' AND `category` = 'system'") == '0') {
            $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]";
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_template', '$value', 'system')");
        }
        // rel_content_auto
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'rel_content_auto' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_auto', '0', 'system')");
        }
        // rel_content_category
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'rel_content_category' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_category', '', 'system')");
        }
        // import_overwrite
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'import_overwrite' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('import_overwrite', '0', 'system')");
        }

        /**** since version 5.0.0 ****/
        // fix old entries
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'sem' AND `category` = ''") == '0') {
            $wpdb->query("UPDATE " . TEACHPRESS_SETTINGS . " SET `category` = 'system' WHERE `variable` = 'sem'");
        }

        // convert_bibtex
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'convert_bibtex' AND `category` = 'system'") == '0') {
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('convert_bibtex', '0', 'system')");
        }

        // course_of_studies
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'course_of_studies' AND `category` = 'teachpress_stud'") == '0') {
            $value = 'name = {course_of_studies}, title = {' . __('Course of studies','teachpress') . '}, type = {SELECT}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {admin}';
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('course_of_studies', '$value', 'teachpress_stud')");
        }
        // birthday
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'birthday' AND `category` = 'teachpress_stud'") == '0') {
            $value = 'name = {birthday}, title = {' . __('Birthday','teachpress') . '}, type = {DATE}, required = {false}, min = {false}, max = {false}, step = {false}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('birthday', '$value', 'teachpress_stud')");
        }
        // semester_number
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'semester_number' AND `category` = 'teachpress_stud'") == '0') {
            $value = 'name = {semester_number}, title = {' . __('Semester number','teachpress') . '}, type = {INT}, required = {false}, min = {1}, max = {99}, step = {1}, visibility = {normal}';
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('semester_number', '$value', 'teachpress_stud')");
        }
        // matriculation_number
        if ($wpdb->query("SELECT value FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = 'matriculation_number' AND `category` = 'teachpress_stud'") == '0') {
            $value = 'name = {matriculation_number}, title = {' . __('Matriculation number','teachpress') . '}, type = {INT}, required = {false}, min = {1}, max = {1000000}, step = {1}, visibility = {admin}';
            $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('matriculation_number', '$value', 'teachpress_stud')");
        }
        /**** since version 5.0.3 ****/
        // Fix an installer bug (wrong template for related content)
        if ( get_tp_option('rel_content_template') == 'page' ) {
            tp_options::change_option('rel_content_template', '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]");
        }
        /**** since version 6.x.x ****/
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('doi_resolver_url_template_default', 'https://dx.doi.org/%doi%', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('doi_resolver_url_template_custom', '', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('doi_resolver_href_label_custom', '', 'system')");
    }

    /**
     * Update version information in the database
     * @param string $version
     * @since 4.2.0
     */
    private static function finalize_update ($version) {
        global $wpdb;
        $version = htmlspecialchars( esc_sql( $version ) );
        $wpdb->query("UPDATE " . TEACHPRESS_SETTINGS . " SET `value` = '$version', `category` = 'system' WHERE `variable` = 'db-version'");
        get_tp_message( __('Update successful','teachpress') );
    }
}
