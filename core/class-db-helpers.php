<?php
/**
 * This file contains all functions for sql calls
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 4.0.0
 */



/**
 * Contains functions for getting, adding and deleting publication imports
 * @package teachpress
 * @subpackage database
 * @since 6.1.0
 */
class tp_publication_imports {
    
    /**
     * Returns a single row of the import information
     * @param int $id               ID of the table row
     * @param string $output_type     The output type, default is: ARRAY_A
     * @return array|object
     * @since 6.1
     */
    public static function get_import ($id, $output_type = ARRAY_A) {
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM " . TEACHPRESS_PUB_IMPORTS . " WHERE `id` = '" . intval($id) . "'", $output_type);
        return $result;
    }
    
    /**
     * Returns the imports
     * @param int $wp_id            The WordPress user ID, default is: 0
     * @param string $output_type   The output type, default is: ARRAY_A
     * @return array|object
     * @since 6.1
     */
    public static function get_imports ($wp_id = 0, $output_type = ARRAY_A) {
        global $wpdb;
        
        // search only for a single user
        $where = '';
        if ( $wp_id !== 0 ) {
            $where = " WHERE `wp_id` = '" . intval($wp_id) . "'";
        }
        
        $result = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_PUB_IMPORTS . $where . " ORDER BY date DESC", $output_type);
        return $result;
    }
    
    /**
     * Adds the import information
     * return int
     * @since 6.1
     */
    public static function add_import () {
        global $wpdb;
        $time = current_time('mysql',0);
        $id = get_current_user_id();
        $wpdb->insert( TEACHPRESS_PUB_IMPORTS, array( 'wp_id' => $id, 
                                                      'date' => $time ), 
                                               array( '%d', '%s') );
        return $wpdb->insert_id;
    }
    
    /**
     * Deletes the selected import information
     * @param array $checkbox       The IDs of the table rows
     * @since 6.1
     */
    public static function delete_import($checkbox) {
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->query( "DELETE FROM " . TEACHPRESS_PUB_IMPORTS . " WHERE `id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Returns an array with the number of publications for each import
     * @return array
     * @since 6.1
     */
    public static function count_publications () {
        global $wpdb;
        return $wpdb->get_results("SELECT COUNT(`pub_id`) AS number, import_id FROM " . TEACHPRESS_PUB . " WHERE import_ID > 0 GROUP BY import_id ORDER BY import_id ASC", ARRAY_A);
    }
}





/**
 * Contains database helper functions
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class tp_db_helpers {
    
    /**
     * Extract column settings from a string
     * @param string $data      The data string has the following structure: name1 = {value1}, name2 = {value2}, ...
     * @return array
     * @since 5.0.0
     */
    public static function extract_column_data ($data) {
        $return = array();
        $data = explode(',', $data);
        foreach ( $data as $row ) {
            $row = explode(' = ', $row);
            $name = trim($row[0]);
            $value = str_replace(array('{','}'), array('',''), trim($row[1]));
            $return[$name] = $value;
        }
        return $return;
    }
    
    /**
     * Generate a where clause
     * @param string $input         An array with values
     * @param string $column        Name of the table column
     * @param string $connector     The connector: AND, OR
     * @param string $operator      The operator: = !=
     * @param string $pattern       Things like %
     * @return string
     * @since 5.0.0
     */
    public static function generate_where_clause($input, $column, $connector = 'AND', $operator = '=', $pattern = '') {
        $end = '';
        if ($input === '' || $input === 0) {
            return;
        }
        
        $array = explode(",", $input);
        foreach ( $array as $element ) {
            $element = esc_sql( htmlspecialchars( trim($element) ) );
            if ( $element === '' ) {
                continue;
            }
            if ( $pattern != '' ) { $element = $pattern . $element . $pattern; }
            $end = ( $end == '' ) ? "$column $operator '$element'" : $end . " $connector $column $operator '$element'";
        }
        
        return $end;
    }
    
    /**
     * Prepares and adds meta data
     * @param int $id               An user ID, publication ID or course ID
     * @param array $fields         An associative array of field data (keys: variable, value)
     * @param array $post           The $_POST array
     * @param string $table         students, courses or publications
     * @since 5.0.0
     */
    public static function prepare_meta_data ($id, $fields, $post, $table) {
        foreach ($fields as $row) {
            if ( !isset( $post[$row['variable']] ) && !isset( $post[$row['variable'] . '_day'] ) ) {
                continue;
            }
            
            $column_info = tp_db_helpers::extract_column_data($row['value']);
            // For DATE fields
            if ( $column_info['type'] === 'DATE' ) {
                $day = intval( $post[$row['variable'] . '_day'] );
                $day2 = ( $day < 10 ) ? '0' . $day : $day;
                $value = $post[$row['variable'] . '_year'] . '-' . $post[$row['variable'] . '_month'] . '-' . $day2;
            }
            // For CHECKBOX fields
            else if ( $column_info['type'] === 'CHECKBOX' ) {
                $max = count($post[$row['variable']]);
                $val = '';
                for ( $i = 0; $i < $max; $i++ ) {
                    $val = ( $val === '' ) ? '{' . $post[$row['variable']][$i] . '}' : $val . ',{' . $post[$row['variable']][$i] . '}';
                }
                $value = $val;
            }
            // For all other fields
            else {
                $value = $post[$row['variable']];
            }
            
            // Add to database
            if ( $table === 'students' ){
                tp_students::add_student_meta( $id, $row['variable'], htmlspecialchars($value) );
            }
            else if ( $table === 'courses' ) {
                tp_courses::add_course_meta($id, $row['variable'], $value);
            }
            else {
                tp_publications::add_pub_meta($id, $row['variable'], $value);
            }
        }
    }
    
    /**
     * Register a new table column in teachpress
     * @param string $table
     * @param string $column
     * @param array $data
     * @since 5.0.0
     */
    public static function register_column ($table, $column, $data) {
        $value = 'name = {' . $column. '}, title = {' . $data['title'] . '}, type = {' . $data['type'] . '}, required = {' . $data['required'] . '}, min = {' . $data['min'] . '}, max = {' . $data['max'] . '}, step = {' . $data['step'] . '}, visibility = {' . $data['visibility'] . '}';
        tp_options::add_option($column, $value, $table);
    }
    
    /**
     * Returns the indexes of a given table
     * @param string $db_name
     * @return array
     * @since 7.0.0
     */
    public static function get_db_index ($db_name) {
        global $wpdb;
        return $wpdb->get_results("SHOW INDEX FROM " . $db_name, ARRAY_A);
    }
    
}