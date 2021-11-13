<?php
/**
 * This file contains the database access class for publication tags
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Database access class for tags
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Tags {
    
    /**
     * Returns an array of all used tags based on the publication tag relation
     * 
     * Note: If you only need a list of used tags, set group_by to true.
     * In this case you should ignore the columns con_id and pub_id from return
     * 
     * Possible values for array $args:
     *       pub_id (STRING)          Publication IDs (separated by comma)
     *       user (STRING)            User IDs (separated by comma)
     *       exclude (STRING)         Tag IDs you want to exclude from result (separated by comma)
     *       order (STRING)           ASC or DESC; default is ASC
     *       limit (STRING)           The SQL limit, example: 0,30
     *       search (STRING)          A normal search string
     *       group by (BOOLEAN)       Boolean flag for the group by clause. Default is: false
     *       count (BOOLEAN)          Set it to true if you only need an number of tags which will be returned by your selection. Default is: false
     *       output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
     * 
     * @param array $args
     * @return array|object
     * @since 5.0.0
     */
    public static function get_tags( $args = array() ) {
        $defaults = array(
            'pub_id'        => '',
            'user'          => '',
            'exclude'       => '',
            'order'         => 'ASC',
            'limit'         => '',
            'search'        => '',
            'count'         => false,
            'group_by'      => false, 
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;
       
        // Define basics
        $select = "SELECT DISTINCT t.name, r.tag_id, r.pub_id, r.con_id FROM " . TEACHPRESS_RELATION . " r INNER JOIN " . TEACHPRESS_TAGS . " t ON t.tag_id = r.tag_id";
        $join = '';

        // if the user needs only the number of rows
        if ( $atts['count'] === true ) {
            $select = "SELECT COUNT(t.`tag_id`) AS `count` FROM " . TEACHPRESS_TAGS . " t";
        }

        // Additional tables
        if ( $atts['user'] != '' ) {
            $join .= " INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = r.pub_id ";
        }

        // WHERE clause
        $search = esc_sql( htmlspecialchars( stripslashes($atts['search']) ) );
        $nwhere = array();
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['pub_id'], "r.pub_id", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['user'], "u.user", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['exclude'], "r.tag_id", "AND", "!=");
        $nwhere[] = ( $search != '' ) ? "t.name like '%$search%'" : null;
        $where = TP_DB_Helpers::compose_clause($nwhere);

        // LIMIT clause
        $limit = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';

        // GROUP BY clause
        $group_by = ( $atts['group_by'] === true ) ? " GROUP BY t.name" : '';

        // End
        $order = esc_sql($atts['order']);
        $sql = $select . $join . $where . $group_by . " ORDER BY t.name $order $limit";

        // echo get_tp_message($sql, 'orange');
        $sql = ( $atts['count'] == false ) ? $wpdb->get_results($sql, $atts['output_type']): $wpdb->get_var($sql);
        return $sql;
    }
   
    /**
     * Adds a new tag
     * @param string $name          the new tag
     * @return int                  the id of the created tag
     * @since 5.0.0
     */
    public static function add_tag($name) {
        global $wpdb;

        // prevent possible double escapes
        $name = stripslashes($name);

        $wpdb->insert(TEACHPRESS_TAGS, array('name' => $name), array('%s'));
        return $wpdb->insert_id;
    }
    
    /** 
     * Edit a tag. Returns false if errors, or the number of rows affected if successful.
     * @param int $tag_id        The tag ID
     * @param string $name       the tag name
     * @return int|false
     * @since 5.0.0
    */
    public static function edit_tag($tag_id, $name) {
        global $wpdb;

        // prevent possible double escapes
        $name = stripslashes($name);

        return $wpdb->update( TEACHPRESS_TAGS, array( 'name' => $name ), array( 'tag_id' => $tag_id ), array( '%s' ), array( '%d' ) );
    }
   
    /**
     * Adds a relation between a tag and a publication
     * @param int $pub_id    The ID of the publication
     * @param int $tag_id    The ID of the tag
     * @return int
     * @since 5.0.0
     */
    public static function add_tag_relation($pub_id, $tag_id) {
        global $wpdb;
        $wpdb->insert(TEACHPRESS_RELATION, array('pub_id' => $pub_id, 'tag_id' => $tag_id), array('%d', '%d'));
        return $wpdb->insert_id;
    }
   
    /**
     * Changes tag relations for more than one publication
     * @param array $publications       Array of publication IDs
     * @param string $new_tags          New tags separated by comma
     * @param array $delete             Array of tag IDs whose relations with publications (given in the first parameter) should be deleted
     * @since 5.0.0
     */
    public static function change_tag_relations ($publications, $new_tags, $delete) {
        global $wpdb;
        $array = explode(",",$new_tags);
        $max = count( $publications );
        $max_delete = count ( $delete );

        for( $i = 0; $i < $max; $i++ ) {
            $publication = intval($publications[$i]);
            // Delete tags
            for ( $j = 0; $j < $max_delete; $j++ ) {
                $delete[$j] = intval($delete[$j]);
                $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$publication' AND `tag_id` = '$delete[$j]'" );
            }

            // Add tags
            foreach( $array as $element ) {
                $element = esc_sql( htmlspecialchars( trim( stripslashes($element ) ) ) );
                if ($element === '') {
                   continue;
                }
                $check = $wpdb->get_var("SELECT `tag_id` FROM " . TEACHPRESS_TAGS . " WHERE `name` = '$element'");
                // if tag not exist
                if ( $check === NULL ){
                    $check = TP_Tags::add_tag($element);
                }
                // add releation between publication and tag
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$publication' AND `tag_id` = '$check'");
                if ($test === 0) {
                    TP_Tags::add_tag_relation($publications[$i], $check);
                }

            }  
        } 
    }
   
    /** 
     * Deletes tags
     * @param array $checkbox       An array with tag IDs
     * @since 5.0.0
    */
    public static function delete_tags($checkbox) {
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `tag_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_TAGS . " WHERE `tag_id` = $checkbox[$i]" );
        }
    }
   
    /**
     * Deletes relations between tags and publications
     * @param array $delbox
     * @since 5.0.0
     */
    public static function delete_tag_relation($delbox) {
        global $wpdb;
        for ( $i = 0; $i < count($delbox); $i++ ) {
            $delbox[$i] = intval($delbox[$i]);
            $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION .  " WHERE `con_id` = $delbox[$i]" );
        }
    }
    
    /**
     * Returns an array|object with the name, tag_id and occurence of all_tags
     * @param array $args {
     *      @type string order          Default: a.name ASC 
     *      @type string limit          SQL limit like 0,50
     *      @type string search         a full text search through name field
     *      @type boolean only_zero     If true: only tags with 0 occurence will be returned
     *      @type boolean count         Set it to true, if you only need a number of tags, which will be returned by your selection
     *      @type string output_type    OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * }     
     * @return array|object
     * @since 8.1
     */
    public static function get_tags_occurence ( $args ) {
        $defaults = array(
           'order'              => 't.name ASC',
           'limit'              => '',
           'search'             => '',
           'only_zero'          => false,
           'count'              => false,
           'output_type'        => ARRAY_A
        ); 
        $atts = wp_parse_args( $args, $defaults );
        global $wpdb;
        $search = esc_sql( htmlspecialchars( stripslashes( $atts['search'] ) ) );
        
        // LIMIT clause
        $l = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';
        
        // WHERE clause
        $awhere = array();
        $awhere[] = ( $atts['search'] != '' ) ? "t.`name` like '%" . $search . "%'" : '';
        $where = TP_DB_Helpers::compose_clause($awhere);
        
        // HAVING clause
        $having = ( $atts['only_zero'] === true ) ? 'HAVING count = 0' : '';
        
        // ORDER clause
        $order = esc_sql($atts['order']);
        
        $sql = "SELECT DISTINCT DISTINCT t.name, t.tag_id, count(r.tag_id) AS count 
                FROM " . TEACHPRESS_TAGS . " t 
                LEFT JOIN " . TEACHPRESS_RELATION . " r ON t.tag_id = r.tag_id 
                $where 
                GROUP BY t.name
                $having 
                ORDER BY $order $l";
        
        // if the user needs only the number of rows
        if ( $atts['count'] === true ) {
            $sql = "SELECT COUNT(`tag_id`) FROM ( " . $sql . ") AS temp";
        }
        
        $return = ( $atts['count'] == false ) ? $wpdb->get_results($sql, $atts['output_type']): $wpdb->get_var($sql);
        return $return;
        
    }
    
    /**
     * Returns a special array for creating tag clouds
     * 
     * Possible values for array $args:
     *      user (STRING)            User IDs (separated by comma)
     *      exclude (STRING)         Tag IDs you want to exclude from result (separated by comma)
     *      type (STRING)            Publication types (separated by comma)
     *      number_tags (Int)        The number of tags       
     *      output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
     * 
     * 
     * The returned array $result has the following array_keys:
     *      'tags'  => it's an array or object with tags, including following keys: tagPeak, name, tag_id
     *      'info'  => it's an object which includes information about the frequency of tags, including following keys: max, min
     * 
     * @param array $args
     * @return array|object
     * @since 5.0.0
    */
    public static function get_tag_cloud ( $args = array() ) {
        $defaults = array(
            'user'          => '',
            'type'          => '',
            'number_tags'   => '',
            'exclude'       => '',
            'output_type'   => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;

        $join1 = "LEFT JOIN " . TEACHPRESS_TAGS . " t ON r.tag_id = t.tag_id";
        $join2 = "INNER JOIN " . TEACHPRESS_PUB . " p ON p.pub_id = r.pub_id";
        $join3 = "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = p.pub_id";

        if ( $atts['user'] == '' && $atts['type'] == '' ) {
            $join1 = '';
            $join2 = '';
            $join3 = '';

        }
        if ( $atts['user'] == '' && $atts['type'] != '' ) {
            $join3 = '';
        }

        // WHERE clause
        $nwhere = array();
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['type'], "p.type", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['user'], "u.user", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['exclude'], "r.tag_id", "AND", "!=");
        $where = TP_DB_Helpers::compose_clause($nwhere);
        
        // LIMIT clause
        $limit = ( $atts['number_tags'] != '' ) ? 'LIMIT ' . esc_sql($atts['number_tags']) : '';

        $sql = "SELECT anzahlTags FROM ( 
                    SELECT COUNT(*) AS anzahlTags 
                    FROM " . TEACHPRESS_RELATION . " r
                    $join1 $join2 $join3 $where
                    GROUP BY r.tag_id 
                    ORDER BY anzahlTags DESC ) as temp1 
                GROUP BY anzahlTags 
                ORDER BY anzahlTags DESC";
        
        $cloud_info = $wpdb->get_row("SELECT MAX(anzahlTags) AS max, min(anzahlTags) AS min FROM ( $sql ) AS temp", OBJECT);
        
        $cloud_info->min = $cloud_info->min == '' ? 0 : $cloud_info->min; // Fix if there are no tags
        
        $sql = "SELECT tagPeak, name, tag_id FROM ( 
                  SELECT COUNT(r.tag_id) as tagPeak, t.name AS name, t.tag_id as tag_id 
                  FROM " . TEACHPRESS_RELATION . " r 
                  LEFT JOIN " . TEACHPRESS_TAGS . " t ON r.tag_id = t.tag_id 
                  INNER JOIN " . TEACHPRESS_PUB . " p ON p.pub_id = r.pub_id 
                  $join3 $where
                  GROUP BY r.tag_id ORDER BY tagPeak DESC 
                  $limit ) AS temp 
                WHERE tagPeak>=".$cloud_info->min." 
                ORDER BY name";
        
        $result["tags"] = $wpdb->get_results($sql, $atts['output_type']);
        $result["info"] = $cloud_info;
        return $result;
    }
}

