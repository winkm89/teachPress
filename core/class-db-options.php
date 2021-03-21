<?php
/**
 * This file contains the database access class for options
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains all functions for getting, adding and deleting of plugin options
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Options {
    
    /**
     * Returns an option by ID
     * @param int $id       The option ID
     * @return array
     * @since 5.0.0
     */
    public static function get_option_by_id ($id){
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `setting_id` = '" . intval($id) . "'", ARRAY_A);
    }

    /** 
     * Adds an option
     * @param string $variable  The name of the option
     * @param string $value     The value of the option
     * @param string $category  Category name (system, course_of_studies, course_type, semester,...)
     * @return int              The ID if the added option
     * @since 5.0.0
    */
    public static function add_option($variable, $value, $category) { 
        global $wpdb;
        $wpdb->insert( TEACHPRESS_SETTINGS, 
            array( 
                'variable' => htmlspecialchars(stripslashes($variable)), 
                'value' => htmlspecialchars($value), 
                'category' => htmlspecialchars($category) ), 
            array( '%s', '%s', '%s' ) );
        return $wpdb->insert_id;
    }
    
    /**
     * Updates an option
     * @param string $variable      The name of the option
     * @param string $value         The value of the option
     * @param string $type          normal or checkbox
     * @since 5.0.0
     */
    public static function change_option ($variable, $value, $type = 'normal') {
        global $wpdb;
        $var = esc_sql($variable);
        $val = esc_sql($value);
        if ( $type === 'checkbox' ) {
            $val = ( $val !== '' ) ? 1 : 0;
        }
        $wpdb->query( "UPDATE " . TEACHPRESS_SETTINGS . " SET `value` = '$val' WHERE `variable` = '$var'" );
    }
    
    /** 
     * Deletes an option
     * @param int $delete   The option ID
     * @since 5.0.0
    */
    public static function delete_option($delete) {
        global $wpdb;	
        $wpdb->query( "DELETE FROM " . TEACHPRESS_SETTINGS . " WHERE `setting_id` = '" . intval($delete) . "'" );
    }
    
}

/** 
 * Returns a teachPress option
 * @param string $var           sem, db-version, sign_out, login, regnum, studies, termnumber, birthday
 * @param string $category      system,... default: system
 * @return string
 * @since 1.0.0
*/
function get_tp_option($var, $category = 'system') {
    global $wpdb;
    $result = $wpdb->get_var(
        $wpdb->prepare( "SELECT `value` FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = %s AND `category` = %s",  $var, $category )
    );
    // get_tp_message ($wpdb->last_query);
    return $result;
}

/**
 * Returns all settings of a category
 * @param string $category      category name (system, course_of_studies, course_type, semester)
 * @param string $order         default: setting_id DESC
 * @param string $output_type   default: OBJECT
 * @return object|array
 * @since 4.0.0
 */
function get_tp_options($category, $order = "`setting_id` DESC", $output_type = OBJECT) {
    global $wpdb;
    $order = esc_sql($order);
    $result = $wpdb->get_results( 
        $wpdb->prepare( "SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = %s ORDER BY " . $order,  $category ), $output_type
        
    );
    return $result;
}
