<?php
/**
 * This file contains the database access class for publication bookmarks
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting of bookmarks
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Bookmarks {
    
    /**
     * Returns an arrayor object of bookmarks of an user
     * 
     * @since 5.0.0
     * @param array $args {
     *      @type int user              User IDs (separated by comma)
     *      @type int pub_id            Publication IDs (separated by comma)
     *      @type string output_type    OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * }
     * @return array|object
     */
    public static function get_bookmarks( $args = array() ) {
        $defaults = array(
            'user'          => '',
            'pub_id'        => '',
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;
        
        $select = "SELECT * FROM " . TEACHPRESS_USER;
       
        $awhere = array();
        $awhere[] = TP_DB_Helpers::generate_where_clause($atts['user'], "user", "OR", "=");
        $awhere[] = TP_DB_Helpers::generate_where_clause($atts['pub_id'], "pub_id", "OR", "=");
        $where = TP_DB_Helpers::compose_clause($awhere);
        
        $sql = $select . $where;
        
        return $wpdb->get_results($sql, $atts['output_type']);
    }
    
    /** 
     * Adds a new bookmark for a user
     * @param int $pub_id           The publication ID
     * @param int $user             The user ID
     * @param bool $exist_check     true = Checks if the bookmark already exists before inserting, default: false
     * @return int The id of the created element
     * @since 5.0.0
    */
   public static function add_bookmark($pub_id, $user, $exist_check = false) {
        global $wpdb;
        
        // Check if the bookmark already exists before inserting
        if ( $exist_check === true ) {
            $check = TP_Bookmarks::bookmark_exists($pub_id, $user);
            if ( $check === true ) {
                return;
            }
        }
        
        // Add the bookmark
        $wpdb->insert(
                TEACHPRESS_USER, 
                array(
                    'pub_id' => $pub_id, 
                    'user' => $user), 
                array('%d', '%d') );
        return $wpdb->insert_id;
    }
    
    /** 
     * Delete a bookmark 
     * @param int $bookmark_id
     * @since 5.0.0
    */
    public static function delete_bookmark($bookmark_id) {
        global $wpdb;
        $wpdb->query( "DELETE FROM " . TEACHPRESS_USER . " WHERE `bookmark_id` = '" . intval($bookmark_id) . "'" );
    }
    
    /**
     * Deletes all bookmarks of a publication
     * @param int $pub_id
     * @since 8.1.0
     */
    public static function delete_bookmarks_by_publication($pub_id) {
        global $wpdb;
        $wpdb->query( "DELETE FROM " . TEACHPRESS_USER . " WHERE `pub_id` = '" . intval($pub_id) . "'" );
    }
    
    /**
     * Checks if an user has bookmarked a publication. Returns true the bookmark exists.
     * @param int $pub_id       The publication ID
     * @param int $user_id      The user ID
     * @return boolean
     * @since 5.0.0
     */
    public static function bookmark_exists($pub_id, $user_id) {
        global $wpdb;
        $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_USER . " WHERE `pub_id`='" . intval($pub_id) . "' AND `user` = '" . intval($user_id) . "'");
        if ($test != 0) {
            return true;
        }
        return false;
    }
    
}