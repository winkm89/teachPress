<?php
/**
 * This file contains the database access class for publication authors
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Database access class for publication authors
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_Authors  {
   /**
    * Returns an array/object of authors/editors of publications
    * 
    * Note: If you only need a list of used authors, set group_by to true.
    * In this case you should ignore the columns con_id and pub_id from return
    * 
    * Possible values for the array $args:
    *       author_id (STRING)       Author IDs (separated by comma)
    *       pub_id (STRING)          Publication IDs (separated by comma)
    *       user (STRING)            User IDs (separated by comma)
    *       exclude (STRING)         Authors IDs you want to exclude from result (separated by comma)
    *       order (STRING)           ASC or DESC; default is ASC
    *       limit (STRING)           The sql search limit, example: 0,30
    *       search (STRING)          A normal search string
    *       inclue_editors (BOOLEAN) Boolean flag, set it to true if you want to include editors (default: false)
    *       group by (BOOLEAN)       Boolean flag for the group by clause (default: false)
    *       count (BOOLEAN)          Set it to true, if you only need a number of authors, which will be returned by your selection (default: false)
    *       output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
    * 
    * @param array $args
    * @return array|object
    * @since 5.0.0
    */
    public static function get_authors ( $args = array() ) {
        $defaults = array(
           'author_id' => '',
           'pub_id' => '',
           'user' => '',
           'exclude' => '',
           'order' => 'ASC',
           'limit' => '',
           'search' => '',
           'include_editors' => false,
           'count' => false,
           'group_by' => false, 
           'output_type' => OBJECT
        ); 
        $atts = wp_parse_args( $args, $defaults );

        global $wpdb;

        // Define basics
        $select = "SELECT DISTINCT a.name, r.author_id, r.pub_id, r.con_id, r.is_author, r.is_editor FROM " . TEACHPRESS_REL_PUB_AUTH . " r INNER JOIN " . TEACHPRESS_AUTHORS . " a ON a.author_id = r.author_id";
        $join = '';
        $order = esc_sql($atts['order']);

        // if the user needs only the number of rows
        if ( $atts['count'] === true ) {
            $select = "SELECT COUNT(a.`author_id`) AS `count` FROM " . TEACHPRESS_AUTHORS . " a";
        }

        // Additional tables
        if ( $atts['user'] != '' ) {
            $join .= " INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = r.pub_id ";
        }

        // WHERE clause
        $search = esc_sql(htmlspecialchars(stripslashes($atts['search'])));
        $nwhere = array();
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['author_id'], "r.author_id", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['pub_id'], "r.pub_id", "OR", "=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['exclude'], "r.author_id", "AND", "!=");
        $nwhere[] = TP_DB_Helpers::generate_where_clause($atts['user'], "u.user", "OR", "=");
        $nwhere[] = ( $search != '' ) ? "a.name like '%$search%'" : null;
        $nwhere[] = ( $atts['include_editors'] === false ) ? "r.is_editor = '0'" : null;
        
        $where = TP_DB_Helpers::compose_clause($nwhere);

        // LIMIT clause
        $limit = ( $atts['limit'] != '' ) ? 'LIMIT ' . esc_sql($atts['limit']) : '';

        // GROUP BY clause
        $group_by = ( $atts['group_by'] === true ) ? " GROUP BY a.name" : '';

        // End
        $sql = $select . $join . $where . $group_by . " ORDER BY a.sort_name $order, a.name $order $limit";
        $sql = ( $atts['count'] == false ) ? $wpdb->get_results($sql, $atts['output_type']): $wpdb->get_var($sql);
        // echo get_tp_message($wpdb->last_query);
        return $sql;
    }
    
    /**
     * Adds a new author
     * @param string $name          The name of the author
     * @param string $sort_name     The name used for sorting (mostly the lastname)
     * @return int
     * @since 5.0.0
     */
    public static function add_author ($name, $sort_name) {
        global $wpdb;
        
        // prevent possible double escapes
        $name = stripslashes($name);
        $sort_name = stripslashes($sort_name);
        
        $wpdb->insert(TEACHPRESS_AUTHORS, array('name' => $name, 'sort_name' => $sort_name), array('%s', '%s'));
        return $wpdb->insert_id;
    }
    
    /**
     * Adds a new author - publication relation
     * @param int $pub_id       The publication ID
     * @param int $author_id    The author ID
     * @param int $is_author    1 (true) or 0 (false)
     * @param int $is_editor    1 (true) or 0 (false)
     * @return int
     * @since 5.0.0
     */
    public static function add_author_relation ($pub_id, $author_id, $is_author, $is_editor){
        global $wpdb;
        $wpdb->insert(TEACHPRESS_REL_PUB_AUTH, array('pub_id' => $pub_id, 'author_id' => $author_id, 'is_author' => $is_author, 'is_editor' => $is_editor), array('%d', '%d', '%d', '%d'));
        return $wpdb->insert_id;
    }
    
     /**
     * Returns an array|object with the name, author_id and occurence of all authors
     * @param string $search            normal search string
     * @param string $limit             SQL limit like 0,50
     * @param string $output_type       OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
     * @return array|object
     * @since 5.0.0
     */
    public static function count_authors ( $search = '', $limit = '', $output_type = ARRAY_A ) {
        global $wpdb;
        
        // define global search
        $s = ( $search != '' ) ? "WHERE a.`name` like '%" . esc_sql(stripslashes($search)) . "%'" : '';
        
        // LIMIT clause
        $l = ( $limit != '' ) ? 'LIMIT ' . esc_sql($limit) : '';
        
        return $wpdb->get_results("SELECT DISTINCT a.name, a.author_id, count(r.author_id) AS count FROM " . TEACHPRESS_AUTHORS . " a LEFT JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON a.author_id = r.author_id $s GROUP BY a.name ORDER BY a.name ASC $l", $output_type);
    }
    
    /**
     * Deletes author to publication relations
     * @param int $pub_id       The author ID
     * @since 5.0.0
     */
    public static function delete_author_relations ($pub_id) {
        global $wpdb;
        $wpdb->query("DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = '" . intval($pub_id) . "'");
    }
    
    /**
     * Deletes authors
     * @param array $checkbox
     * @since 5.0.0
     */
    public static function delete_authors($checkbox) {
       global $wpdb;
       for( $i = 0; $i < count( $checkbox ); $i++ ) {
           $checkbox[$i] = intval($checkbox[$i]);
           $wpdb->query( "DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `author_id` = $checkbox[$i]" );
           $wpdb->query( "DELETE FROM " . TEACHPRESS_AUTHORS . " WHERE `author_id` = $checkbox[$i]" );
       }
   }
   
   /**
    * Returns an array or object of related publications of an author/editor
    * @param int $author_id         The author ID
    * @param string $output_type    OBJECT, ARRAY_N or ARRAY_A, default is ARRAY_A
    * @since 5.0.0
    */
   public static function get_related_publications($author_id, $output_type = ARRAY_A){
       global $wpdb;
       return $wpdb->get_results("SELECT DISTINCT p.pub_id, p.title, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.urldate, p.isbn , p.url, p.booktitle, p.issuetitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page, r.is_author, r.is_editor 
            FROM " . TEACHPRESS_PUB .  " p 
            INNER JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON p.pub_id = r.pub_id 
            WHERE r.author_id = '" . intval($author_id) . "' 
            ORDER BY year DESC", $output_type);
       
   }
    
}