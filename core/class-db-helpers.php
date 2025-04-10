<?php
/**
 * This file contains the database helper class
 * @package teachpress
 * @subpackage core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 8.0.0
 */

/**
 * Contains database helper functions
 * @package teachpress
 * @subpackage database
 * @since 5.0.0
 */
class TP_DB_Helpers {
    
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
        if ($input === '' || $input === 0 || $input === null) {
            return;
        }
        
        $array = explode(",", $input);
        foreach ( $array as $element ) {
            $element = esc_sql( trim($element) );
            if ( $element === '' ) {
                continue;
            }
            if ( $pattern != '' ) { $element = $pattern . $element . $pattern; }
            $end = ( $end == '' ) ? "$column $operator '$element'" : $end . " $connector $column $operator '$element'";
        }
        
        return $end;
    }
    
    /**
     * Generate a between clause
     * 
     * The $input should be "start,end". For an open end, you can use 0. 
     * Examples: 
     * "2017,2022"  --> between 2017 AND 2022
     * "0,2017"     --> all <= 2017
     * "2017,0"     --> all >= 2017
     * 
     * @param string $input     Start and end value separated by comma
     * @param string $column    database column
     * @return string
     * @since 9.0.0
     */
    public static function generate_between_clause ($input, $column) {
        
        // Return if there is nothing to do
        if ($input === '') {
            return;
        }
        
        $array = explode(",", $input);
        
        // we need an array length of two
        if ( count($array) != 2 ) {
            return;
        }
        
        $start = esc_sql( trim($array[0]));
        $end = esc_sql( trim($array[1]));
        $element = esc_sql( trim($column) );
        
        if ( $start == '0' ) {
            return "$element <= '$end'";
        }
        
        if ( $end == '0' ) {
            return "$element >= '$start'";
        }
        
        return "$element BETWEEN '$start' AND '$end'"; 
        
    }
    
    /**
     * Sets clause parts from the $parts[] array together to one clause string
     * 
     * Example: 
     * $where = array();
     * $where[] = tp_db_helpers::where_clause_part('1,2,3', "u.id", "OR", "=");
     * $where[] = tp_db_helpers::where_clause_part('Max', "u.user", "OR", "=");
     * echo tp_db_helpers::compose_clause($where);
     * 
     * @param array $parts          An array of where parts. Each element should be generated by the where_clause_part() method
     * @param string $connector     Can be used to change the logical connector between the clause parts. Default: AND
     * @para, string $clause_part   The SQL code word for the part Default: WHERE. Optional: GROUP BY
     * @return string
     * @since 8.0.0
     */
    public static function compose_clause( $parts = array(), $connector = 'AND', $clause_part = 'WHERE' ) {
        // If there is nothing to do
        if ( empty( $parts ) ) {
            return '';
        }

        // Set all parts together to one WHERE clause
        $where = '';
        foreach ( $parts as $row ) {
            if ( empty( $row ) ) {
                continue;
            }
            $where = ( $where != '' ) ? $where . " $connector ( $row )" : " ( $row ) ";
        }
        if ( $where === '' ) {
            return;
        }
        return " $clause_part $where";
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
            
            $column_info = TP_DB_Helpers::extract_column_data($row['value']);
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
                TP_Students::add_student_meta( $id, $row['variable'], htmlspecialchars($value) );
            }
            else if ( $table === 'courses' ) {
                TP_Courses::add_course_meta($id, $row['variable'], $value);
            }
            else {
                TP_Publications::add_pub_meta($id, $row['variable'], $value);
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
        TP_Options::add_option($column, $value, $table);
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
    
    /**
     * Validates qualifiers for order or limit clauses. Returns the default if the input contains not allowed chars  
     * @param string $input
     * @param string $default
     * @return string
     * @since 9.0.8
     */
    public static function validate_qualifier ($input, $default = '') {
        if ( preg_match("#^[a-zA-Z0-9 \.,`_\]]+$#", $input) ) {
            return $input;
        } else {
            return $default;
        }
    }
    
}