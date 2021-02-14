<?php
/**
 * This file contains the database access class for publications
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Contains functions for getting, adding and deleting of publications
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class tp_publications {
    
    /**
     * Returns a single publication
     * @param int $id               The publication ID
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * @return mixed
     * @since 5.0.0
     */
    public static function get_publication($id, $output_type = OBJECT) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT *, DATE_FORMAT(date, '%Y') AS year FROM " . TEACHPRESS_PUB . " WHERE `pub_id` = '" . intval($id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns a single publication selected by BibTeX key
     * @param int $key              The BibTeX key
     * @param string $output_type   OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     * @return mixed
     * @since 5.0.0
     */
    public static function get_publication_by_key($key, $output_type = OBJECT) {
        global $wpdb;
        $key = esc_sql(htmlspecialchars($key));
        $result = $wpdb->get_row("SELECT *, DATE_FORMAT(date, '%Y') AS year FROM " . TEACHPRESS_PUB . " WHERE `bibtex` = '$key'", $output_type);
        return $result;
    }
    
    /**
     * Returns an array or object of publications
     * 
     * Possible values for the array $args:
     *  user (STRING)                   User IDs (separated by comma)
     *  type (STRING)                   Type name (separated by comma)
     *  tag (STRING)                    Tag IDs (separated by comma)
     *  author_id (STRING)              Author IDs (separated by comma)
     *  import_id (STRING)              Import IDs (separated by comma)
     *  year (STRING)                   Years (separated by comma)
     *  author (STRING)                 Author name (separated by comma)
     *  editor (STRING)                 Editor name (separated by comma)
     *  exclude (STRING)                The ids of the publications you want to exclude (separated by comma)
     *  include (STRING)                The ids of the publications you want to include (separated by comma)
     *  include_editor_as_author (BOOL) True or false
     *  exclude_tags (STRING)           Use it to exclude publications via tag IDs (separated by comma)
     *  order (STRING)                  The order of the list
     *  limit (STRING)                  The sql search limit, ie: 0,30
     *  search (STRING)                 The search string
     *  output_type (STRING)            OBJECT, ARRAY_N or ARRAY_A, default is OBJECT
     *
     * @since 5.0.0
     * @param array $args
     * @param boolean $count    set to true of you only need the number of rows
     * @return mixed            array, object or int
    */
    public static function get_publications($args = array(), $count = false) {
        $defaults = array(
            'user' => '',
            'type' => '',
            'tag' => '',
            'author_id' => '', 
            'import_id' => '',
            'year' => '',
            'author' => '',
            'editor' => '',
            'include' => '',
            'include_editor_as_author' => true,
            'exclude' => '',
            'exclude_tags' => '',
            'exclude_types' => '',
            'order' => 'date DESC',
            'limit' => '',
            'search' => '',
            'output_type' => OBJECT
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        $order_all = esc_sql($order);

        global $wpdb;
        
        // define all things for meta data integration
        $joins = '';
        $selects = '';
        $meta_fields = $wpdb->get_results("SELECT variable FROM " . TEACHPRESS_SETTINGS . " WHERE category = 'teachpress_pub'", ARRAY_A);
        if ( !empty($meta_fields) ) {
            $i = 1;
            foreach ($meta_fields as $field) {
                $table_id = 'm' . $i; 
                $selects .= ', ' . $table_id .'.meta_value AS ' . $field['variable'];
                $joins .= ' LEFT JOIN ' . TEACHPRESS_PUB_META . ' ' . $table_id . " ON ( " . $table_id . ".pub_id = p.pub_id AND " . $table_id . ".meta_key = '" . $field['variable'] . "' ) ";
                $i++;
            }
        }

        // define basics
        $select = "SELECT DISTINCT p.pub_id, p.title, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.urldate, p.isbn, p.url, p.booktitle, p.issuetitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.image_target, p.image_ext, p.doi, p.rel_page, p.status, p.added, p.modified, p.import_id $selects FROM " . TEACHPRESS_PUB . " p $joins ";
        $join = '';
        $where = '';
        $order = '';
        $having ='';
        $output_type = esc_sql($output_type);
        $search = esc_sql(stripslashes($search));
        $limit = esc_sql($limit);

        // exclude publications via tag_id
        if ( $exclude_tags != '' ) {
            $extend = '';
            $exclude_tags = tp_db_helpers::generate_where_clause($exclude_tags , "tag_id", "OR", "=");
            $exclude_publications = $wpdb->get_results("SELECT DISTINCT pub_id FROM " . TEACHPRESS_RELATION . " WHERE $exclude_tags ORDER BY pub_id ASC", ARRAY_A);
            foreach ($exclude_publications as $row) {
                $extend .= $row['pub_id'] . ',';
            }
            $exclude = $extend . $exclude;
        }

        // define where, having and limit clause
        $exclude = tp_db_helpers::generate_where_clause($exclude, "p.pub_id", "AND", "!=");
        $exclude_types = tp_db_helpers::generate_where_clause($exclude_types , "p.type", "AND", "!=");
        $include = tp_db_helpers::generate_where_clause($include, "p.pub_id", "OR", "=");
        $type = tp_db_helpers::generate_where_clause($type, "p.type", "OR", "=");
        $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
        $tag = tp_db_helpers::generate_where_clause($tag, "b.tag_id", "OR", "=");
        $author_id = tp_db_helpers::generate_where_clause($author_id, "r.author_id", "OR", "=");
        $import_id = tp_db_helpers::generate_where_clause($import_id, "p.import_id", "OR", "=");
        $year = tp_db_helpers::generate_where_clause($year, "year", "OR", "=");
        $author = tp_db_helpers::generate_where_clause($author, "p.author", "OR", "LIKE", '%');
        $editor = tp_db_helpers::generate_where_clause($editor, "p.editor", "OR", "LIKE", '%');

        // additional joins
        if ( $user != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = p.pub_id ";
        }
        if ( $tag != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_RELATION . " b ON p.pub_id = b.pub_id INNER JOIN " . TEACHPRESS_TAGS . " t ON t.tag_id = b.tag_id ";
        }
        if ( $author_id != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON p.pub_id = r.pub_id ";
        }

        // define order_by clause
        $array = explode(",",$order_all);
        foreach($array as $element) {
            $element = trim($element);
            // order by year
            if ( strpos($element, 'year') !== false ) {
                $order = $order . $element . ', ';
            }
            // normal case
            if ( $element != '' && strpos($element, 'year') === false ) {
                $order = $order . 'p.' . $element . ', ';
            }

        }
        if ( $order != '' ) {
            $order = substr($order, 0, -2);
        }

        // define global search
        if ( $search != '' ) {
            $search = "p.title LIKE '%$search%' OR p.author LIKE '%$search%' OR p.editor LIKE '%$search%' OR p.isbn LIKE '%$search%' OR p.booktitle LIKE '%$search%' OR p.issuetitle LIKE '%$search%' OR p.journal LIKE '%$search%' OR p.date LIKE '%$search%' OR p.abstract LIKE '%$search%' OR p.note LIKE '%$search%'";
        }

        if ( $exclude != '' ) {
            $where = ( $where != '' ) ? $where . " AND ( $exclude ) " : " ( $exclude ) ";
        }
        if ( $exclude_types != '' ) {
            $where = ( $where != '' ) ? $where . " AND ( $exclude_types ) " : " ( $exclude_types) ";
        }
        if ( $include != '' ) {
            $where = ( $where != '' ) ? $where . " AND ( $include ) " : " ( $include ) ";
        }
        if ( $type != '') {
            $where = ( $where != '' ) ? $where . " AND ( $type ) " : " ( $type ) ";
        }
        if ( $user != '') {
            $where = ( $where != '' ) ? $where . " AND ( $user ) " : " ( $user ) ";
        }
        if ( $tag != '' ) {
            $where = ( $where != '' ) ? $where . " AND ( $tag ) " : " ( $tag ) ";
        }
        if ( $author_id != '') {
            $where = ( $where != '' ) ? $where . " AND ( $author_id ) " : " ( $author_id ) ";
        }
        if ( $author_id != '' && $include_editor_as_author === false) {
            $where .= " AND ( r.is_author = 1 ) ";
        }
        if ( $import_id != '') {
            $where = ( $where != '' ) ? $where . " AND ( $import_id ) " : " ( $import_id ) ";
        }
        if ( $author != '') {
            $where = ( $where != '' ) ? $where . " AND ( $author ) " : " ( $author ) ";
        }
        if ( $editor != '') {
            $where = ( $where != '' ) ? $where . " AND ( $editor ) " : " ( $editor ) ";
        }
        if ( $search != '') {
            $where = ( $where != '' ) ? $where . " AND ( $search ) " : " ( $search ) " ;
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $year != '' && $year !== '0' ) {
            $having = " HAVING $year";
        }
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }
        else {
            $limit = '';
        }

        // End
        if ( $count !== true ) {
            $sql = $select . $join . $where . $having . " ORDER BY $order $limit";
        }
        else {
            $sql = "SELECT COUNT( DISTINCT pub_id ) AS `count` FROM ( $select $join $where $having) p ";
        }
        
        // print_r($args);
        // get_tp_message($sql,'red');
        $sql = ( $count != true ) ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
        return $sql;
    }
    
    /**
     * Returns course meta data
     * @param int $pub_id           The publication ID
     * @param string $meta_key      The name of the meta field
     * @return array
     * @since 5.0.0
     */
    public static function get_pub_meta($pub_id, $meta_key = ''){
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_PUB_META . " WHERE `pub_id` = '" . intval($pub_id) . "' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Returns an array or object of users who has a publication list
     * 
     * Possible values for the array $args:
     *       output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_pub_users( $args = array() ) {
        $defaults = array(
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        $output_type = esc_sql($output_type);

        $result = $wpdb->get_results("SELECT DISTINCT user FROM " . TEACHPRESS_USER, $output_type);

        return $result;
    }
    
    /**
     * Returns an array or object of publication types which are used for existing publication entries
     * 
     * Possible values for the array $args:
     *       user (STRING)            User IDs (separated by comma)
     *       include (STRING)         Publication types (separated by comma)
     *       exclude (STRING)         Publication types (separated by comma)
     *       output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is ARRAY_A
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_used_pubtypes( $args = array() ) {
        $defaults = array(
            'user' => '',
            'include' => '',
            'exclude' => '',
            'output_type' => ARRAY_A
        ); 
        $args = wp_parse_args( $args, $defaults );

        global $wpdb;
        $output_type = esc_sql($args['output_type']);
        $include = tp_db_helpers::generate_where_clause($args['include'], "type", "OR", "=");
        $exclude = tp_db_helpers::generate_where_clause($args['exclude'], "type", "OR", "!=");
        $user = tp_db_helpers::generate_where_clause($args['user'], "u.user", "OR", "=");
        
        $having = ( $include != '' || $exclude != '' ) ? " HAVING $include $exclude " : "";
        
        if ( $user == '' ) {
            $result = $wpdb->get_results("SELECT DISTINCT p.type FROM " .TEACHPRESS_PUB . " p $having ORDER BY p.type ASC", $output_type);
        }    
        else {
            $result = $wpdb->get_results("SELECT DISTINCT p.type AS type from " .TEACHPRESS_PUB . " p 
                                          INNER JOIN " .TEACHPRESS_USER . " u ON u.pub_id=p.pub_id 
                                          WHERE $user 
                                          $having
                                          ORDER BY p.type ASC", $output_type);
        }
        return $result;
    }
    
    /**
     * Returns an object or array with the years where publications are written
     * 
     * Possible values for the array $args:
     *       type (STRING)            Publication types (separated by comma)
     *       user (STRING)            User IDs (separated by comma)
     *       order (STRING)           ASC or DESC; default is ASC
     *       output type (STRING)     OBJECT, ARRAY_A, ARRAY_N, default is OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_years( $args = array() ) {
        $defaults = array(
            'type' => '',
            'user' => '',
            'include' => '',
            'order' => 'ASC',
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );

        global $wpdb;

        $join = '';
        $where = '';
        $having= '';
        $order = esc_sql($args['order']);
        $output_type = esc_sql($args['output_type']);
        $type = tp_db_helpers::generate_where_clause($args['type'], "p.type", "OR", "=");
        $user = tp_db_helpers::generate_where_clause($args['user'], "u.user", "OR", "=");
        $year = tp_db_helpers::generate_where_clause($args['include'], "year", "OR", "=");

        if ( $type != '') {
            $where = ( $where != '' ) ? $where . " AND ( $type ) " : " ( $type ) ";
        }
        if ( $user != '') {
            $where = ( $where != '' ) ? $where . " AND ( $user ) " : " ( $user ) ";
            $join = "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = p.pub_id";
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $year != '' ) {
            $having = " HAVING $year ";
        }

        $result = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS year FROM " . TEACHPRESS_PUB . " p $join $where $having ORDER BY year $order", $output_type);
        return $result;
    }
    
    /** 
     * Adds a publication
     * @param array $data       An associative array of publication data (title, type, bibtex, author, editor,...)
     * @param string $tags      An associative array of tags
     * @param array $bookmark   An associative array of bookmark IDs
     * @return int              The ID of the new publication
     * @since 5.0.0
    */
    public static function add_publication($data, $tags, $bookmark) {
        global $wpdb;
        $defaults = self::get_default_fields();
        $post_time = current_time('mysql',0);
        $data = wp_parse_args( $data, $defaults );
        extract( $data, EXTR_SKIP );
        
        // intercept wrong values for dates
        $urldate = ( $urldate == 'JJJJ-MM-TT' ) ? '0000-00-00' : $urldate;
        $date = ( $date == 'JJJJ-MM-TT' ) ? '0000-00-00' : $date;
        
        // generate bibtex key
        $bibtex = tp_publications::generate_unique_bibtex_key($bibtex);
        
        // check last chars of author/editor fields
        if ( substr($author, -5) === ' and ' ) {
            $author = substr($author ,0 , strlen($author) - 5);
        }
        if ( substr($editor, -5) === ' and ' ) {
            $editor = substr($editor ,0 , strlen($editor) - 5);
        }
        
        // replace double spaces from author/editor fields
        $author = str_replace('  ', ' ', $author);
        $editor = str_replace('  ', ' ', $editor);
        
        // prevent possible double escapes
        $title = stripslashes($title);
        $bibtex = stripslashes($bibtex);
        $author = stripslashes($author);
        $editor = stripslashes($editor);
        $booktitle = stripslashes($booktitle);
        $issuetitle = stripslashes($issuetitle);
        $journal = stripslashes($journal);
        $publisher = stripslashes($publisher);
        $address = stripslashes($address);
        $institution = stripslashes($institution);
        $organization = stripslashes($organization);
        $school = stripslashes($school);
        $abstract = stripslashes($abstract);
        $comment = stripslashes($comment);
        $note =  stripslashes($note);
        $status = stripslashes($status);

        $wpdb->insert( TEACHPRESS_PUB, array( 'title' => $title, 'type' => $type, 'bibtex' => $bibtex, 'author' => $author, 'editor' => $editor, 'isbn' => $isbn, 'url' => $url, 'date' => $date, 'urldate' => $urldate, 'booktitle' => $booktitle, 'issuetitle' => $issuetitle, 'journal' => $journal, 'volume' => $volume, 'number' => $number, 'pages' => $pages , 'publisher' => $publisher, 'address' => $address, 'edition' => $edition, 'chapter' => $chapter, 'institution' => $institution, 'organization' => $organization, 'school' => $school, 'series' => $series, 'crossref' => $crossref, 'abstract' => $abstract, 'howpublished' => $howpublished, 'key' => $key, 'techtype' => $techtype, 'comment' => $comment, 'note' => $note, 'image_url' => $image_url, 'image_target' => $image_target, 'image_ext' => $image_ext, 'doi' => $doi, 'is_isbn' => $is_isbn, 'rel_page' => $rel_page, 'status' => $status, 'added' => $post_time, 'modified' => $post_time, 'import_id' => $import_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d' ) );
         $pub_id = $wpdb->insert_id;

        // Bookmarks
        if ( $bookmark != '' ) {
            $max = count( $bookmark );
            for( $i = 0; $i < $max; $i++ ) {
               if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
                   tp_bookmarks::add_bookmark($pub_id, $bookmark[$i]);
               }
            }
        }
        
        // Tags
        tp_publications::add_relation($pub_id, $tags);
        
        // Authors
        tp_publications::add_relation($pub_id, $author, ' and ', 'authors');
        
        // Editors
        tp_publications::add_relation($pub_id, $editor, ' and ', 'editors');
        
        return $pub_id;
    }
    
    /**
     * Add publication meta data
     * @param int $pub_id           The publication Id
     * @param string $meta_key      The name of the meta field
     * @param string $meta_value    The value of the meta field
     * @since 5.0.0
     */
    public static function add_pub_meta ($pub_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHPRESS_PUB_META, array( 'pub_id' => $pub_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /** 
     * Edit a publication
     * @param int $pub_id           ID of the publication
     * @param array $data           An associative array with publication data
     * @param array $bookmark       An array with WP_USER_ids
     * @param array $delbox         An array with tag IDs you want to delete
     * @param string $tags          A string of Tags seperate by comma
     * @since 5.0.0
    */
   public static function change_publication($pub_id, $data, $bookmark, $delbox, $tags) {
        global $wpdb;
        $post_time = current_time('mysql',0);
        $pub_id = intval($pub_id);
        
        // check if bibtex key has no spaces
        if ( strpos($data['bibtex'], ' ') !== false ) {
            $data['bibtex'] = str_replace(' ', '', $data['bibtex']);
        }
        
        // check last chars of author/editor fields
        if ( substr($data['author'], -5) === ' and ' ) {
            $data['author'] = substr($data['author'] ,0 , strlen($data['author']) - 5);
        }
        if ( substr($data['editor'], -5) === ' and ' ) {
            $data['editor'] = substr($data['editor'] ,0 , strlen($data['editor']) - 5);
        }
        
        // replace double spaces from author/editor fields
        $data['author'] = str_replace('  ', ' ', $data['author']);
        $data['editor'] = str_replace('  ', ' ', $data['editor']);
        
        // prevent double escapes
        $data['title'] = stripslashes($data['title']);
        $data['bibtex'] = stripslashes($data['bibtex']);
        $data['author'] = stripslashes($data['author']);
        $data['editor'] = stripslashes($data['editor']);
        $data['booktitle'] = stripslashes($data['booktitle']);
        $data['issuetitle'] = stripslashes($data['issuetitle']);
        $data['journal'] = stripslashes($data['journal']);
        $data['publisher'] = stripslashes($data['publisher']);
        $data['address'] = stripslashes($data['address']);
        $data['institution'] = stripslashes($data['institution']);
        $data['organization'] = stripslashes($data['organization']);
        $data['school'] = stripslashes($data['school']);
        $data['abstract'] = stripslashes($data['abstract']);
        $data['comment'] = stripslashes($data['comment']);
        $data['note'] =  stripslashes($data['note']);
        $data['status'] =  stripslashes($data['status']);
        
        // update row
        $wpdb->update( TEACHPRESS_PUB, array( 'title' => $data['title'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'urldate' => $data['urldate'], 'booktitle' => $data['booktitle'], 'issuetitle' => $data['issuetitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'image_target' => $data['image_target'], 'image_ext' => $data['image_ext'],  'doi' => $data['doi'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'], 'status' => $data['status'], 'modified' => $post_time ), array( 'pub_id' => $pub_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' ), array( '%d' ) );
        
        // get_tp_message($wpdb->last_query);
        
        // Bookmarks
        if ($bookmark != '') {
            $max = count( $bookmark );
            for( $i = 0; $i < $max; $i++ ) {
                if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
                    tp_bookmarks::add_bookmark($pub_id, $bookmark[$i]);
                }
            }
        }
        
        // Handle tag relations
        if ( $delbox != '' ) {
            tp_tags::delete_tag_relation($delbox);
        }
        if ( $tags != '' ) {
            tp_publications::add_relation($pub_id, $tags);
        }
        
        // Handle author/editor relations
        tp_authors::delete_author_relations($pub_id);
        if ( $data['author'] != '' ) {
            tp_publications::add_relation($pub_id, $data['author'], ' and ', 'authors');
        }
        if ( $data['editor'] != '' ) {
            tp_publications::add_relation($pub_id, $data['editor'], ' and ', 'editors');
        }
    }
    
    /**
     * Update a publication by key (import option); Returns FALSE if no publication with the given key was found
     * @param string $key       The BibTeX key
     * @param array $data       An associative array of publication data
     * @param string $tags      An associative array of tags
     * @return boolean|int
     * @since 5.0.0
     */
    public static function change_publication_by_key($key, $input_data, $tags) {
        global $wpdb;
        $post_time = current_time('mysql',0);
        $search_pub = self::get_publication_by_key($key, ARRAY_A);
        if ( $search_pub === NULL ) {
            return false;
        }
        $data = wp_parse_args( $input_data, $search_pub );
        
        // check if bibtex key has no spaces
        if ( strpos($data['bibtex'], ' ') !== false ) {
            $data['bibtex'] = str_replace(' ', '', $data['bibtex']);
        }
        
        // prevent double escapes
        $data['title'] = stripslashes($data['title']);
        $data['author'] = stripslashes($data['author']);
        $data['editor'] = stripslashes($data['editor']);
        $data['booktitle'] = stripslashes($data['booktitle']);
        $data['issuetitle'] = stripslashes($data['issuetitle']);
        $data['journal'] = stripslashes($data['journal']);
        $data['publisher'] = stripslashes($data['publisher']);
        $data['address'] = stripslashes($data['address']);
        $data['institution'] = stripslashes($data['institution']);
        $data['organization'] = stripslashes($data['organization']);
        $data['school'] = stripslashes($data['school']);
        $data['abstract'] = stripslashes($data['abstract']);
        $data['comment'] = stripslashes($data['comment']);
        $data['note'] =  stripslashes($data['note']);
        $data['status'] =  stripslashes($data['status']);
        
        // update row
    $wpdb->update( TEACHPRESS_PUB, array( 'title' => $data['title'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'urldate' => $data['urldate'], 'booktitle' => $data['booktitle'], 'issuetitle' => $data['issuetitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'image_target' => $data['image_target'], 'image_ext' => $data['image_ext'], 'doi' => $data['doi'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'], 'status' => $data['status'], 'modified' => $post_time ), array( 'pub_id' => $search_pub['pub_id'] ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' ), array( '%d' ) );
        
        // Delete existing tags
        $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = " . $search_pub['pub_id'] );
        
        // Add new tags
        if ( $tags != '' ) {
            tp_publications::add_relation($search_pub['pub_id'], $tags);
        }
        
        // Handle author/editor relations
        tp_authors::delete_author_relations($search_pub['pub_id']);
        if ( $data['author'] != '' ) {
            tp_publications::add_relation($search_pub['pub_id'], $data['author'], ' and ', 'authors');
        }
        if ( $data['editor'] != '' ) {
            tp_publications::add_relation($search_pub['pub_id'], $data['editor'], ' and ', 'editors');
        }
        
        return $search_pub['pub_id'];
    }
    
    /** 
     * Delete publications
     * @param array $checkbox       An array with IDs of publication
     * @since 5.0.0
    */
   public static function delete_publications($checkbox){	
        global $wpdb;
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_USER . " WHERE `pub_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_PUB_META . " WHERE `pub_id` = '$checkbox[$i]'");
            $wpdb->query( "DELETE FROM " . TEACHPRESS_PUB . " WHERE `pub_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Deletes course meta
     * @param int $pub_id           The publication ID
     * @param string $meta_key      The name of the meta field
     * @since 5.0.0
     */
    public static function delete_pub_meta ($pub_id, $meta_key = '') {
        global $wpdb;
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '" . esc_sql($meta_key) . "'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_PUB_META . " WHERE `pub_id` = '" . intval($pub_id) . "' $where");
    }
    
    /**
     * Add new relations (for tags,authors,etc)
     * @param int $pub_id               The publication ID
     * @param string $input_string      A sting of tags
     * @param string $delimiter         The separator for the tags, Default is: ','
     * @param string $rel_type          The relation type: tags, authors or editors, default is tags
     * @since 5.0.0
     */
    public static function add_relation ($pub_id, $input_string, $delimiter = ',', $rel_type = 'tags') {
        global $wpdb;
        $pub_id = intval($pub_id);
        
        // Make sure, that there are no slashes or double htmlspecialchar encodes in the input
        $input_string = stripslashes( htmlspecialchars( htmlspecialchars_decode($input_string) ) );
        
        $array = explode($delimiter, $input_string);
        foreach($array as $element) {
            $element = trim($element);
            
            // if there is nothing in the element, go to the next one
            if ( $element === '' ) {
                continue;
            }
            
            // check if element exists
            if ( $rel_type === 'tags' ) {
                $check = $wpdb->get_var( $wpdb->prepare( "SELECT `tag_id` FROM " . TEACHPRESS_TAGS . " WHERE `name` = %s", $element ) );
            }
            else {
                $check = $wpdb->get_var( $wpdb->prepare( "SELECT `author_id` FROM " . TEACHPRESS_AUTHORS . " WHERE `name` = %s", $element ) );
            }
            
            // if element not exists
            if ( $check === NULL ){
                $check = ( $rel_type === 'tags' ) ? tp_tags::add_tag($element) : tp_authors::add_author( $element, tp_bibtex::get_lastname($element) );
            }
            
            // check if relation exists, if not add relation
            if ( $rel_type === 'tags' ) {
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$pub_id' AND `tag_id` = '$check'");
                if ( $test === 0 ) {
                    tp_tags::add_tag_relation($pub_id, $check);
                }
            }
            else {
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = '$pub_id' AND `author_id` = '$check'");
                if ( $test === 0 ) {
                    $is_author = ( $rel_type === 'authors' ) ? 1 : 0;
                    $is_editor = ( $rel_type === 'editors' ) ? 1 : 0;
                    tp_authors::add_author_relation($pub_id, $check, $is_author, $is_editor);
                }
            }
        }
    }
    
    /**
     * Generates a unique bibtex_key from a given bibtex key
     * @param string $bibtex_key
     * @return string
     * @since 6.1.1
     */
    public static function generate_unique_bibtex_key ($bibtex_key) {
        global $wpdb;
        
        if ( $bibtex_key == '' ) {
            return '';
        }
        
        // check if bibtex key has no spaces
        if ( strpos($bibtex_key, ' ') !== false ) {
            $bibtex_key = str_replace(' ', '', $bibtex_key);
        }
        
        // Check if the key is unique
        $check = $wpdb->get_var("SELECT COUNT('pub_id') FROM " . TEACHPRESS_PUB . " WHERE `bibtex` LIKE '%" . esc_sql($bibtex_key) . "%'");
        if ( intval($check) > 0 ) {
            $alphabet = range('a', 'z');
            if ( $check <= 25 ) {
                $bibtex_key .= $alphabet[$check];
            }
            else {
                $bibtex_key .= '_' . $check;
            }
        }
        
        return $bibtex_key;
        
    }
    
    /**
     * Returns the default fields of a publication as array
     * @return array
     * @since 6.2.5
     */
    public static function get_default_fields () {
        return array(
            'title' => '',
            'type' => '',
            'bibtex' => '',
            'author' => '',
            'editor' => '',
            'isbn' => '',
            'url' => '',
            'date' => '',
            'urldate' => '', 
            'booktitle' => '',
            'issuetitle' => '',
            'journal' => '',
            'volume' => '',
            'number' => '',
            'pages' => '',
            'publisher' => '',
            'address' => '',
            'edition' => '',
            'chapter' => '',
            'institution' => '',
            'organization' => '',
            'school' => '',
            'series' => '',
            'crossref' => '',
            'abstract' => '',
            'howpublished' => '',
            'key' => '',
            'techtype' => '',
            'comment' => '',
            'note' => '',
            'image_url' => '',
            'image_target' => '',
            'image_ext' => '',
            'doi' => '',
            'is_isbn' => '',
            'rel_page' => '',
            'status' => 'published',
            'import_id' => 0
        );
    }
}

