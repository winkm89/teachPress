<?php
/**
 * This file contains general core functions
 * 
 * @package teachpress\core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/** 
 * teachPress Page Menu
 *      
 * @param array $atts {
 *      @type int number_entries       Number of all available entries
 *      @type int entries_per_page     Number of entries per page
 *      @type int current_page         current displayed page
 *      @type string entry_limit       SQL entry limit
 *      @type string page_link         the name of the page you will insert the menu
 *      @type string link_atrributes   the url attributes for get parameters
 *      @type string container_suffix  The optional suffix from the shortcode container 
 *      @type string mode              top or bottom, default: top
 * }
 * @return string
 * @since 5.0.0
*/
function tp_page_menu ($atts) {
    $atts = shortcode_atts(array(
       'number_entries'     => 0,
       'entries_per_page'   => 50,
       'current_page'       => 1,
       'entry_limit'        => 0,
       'page_link'          => '',
       'link_attributes'    => '',
       'container_suffix'   => '',
       'mode'               => 'top',
       'class'              => 'tablenav-pages',
       'before'             => '',
       'after'              => ''
    ), $atts);
    
    $number_entries = intval($atts['number_entries']);
    $entries_per_page = intval($atts['entries_per_page']);
    $current_page = intval($atts['current_page']);
    $entry_limit = intval($atts['entry_limit']);
    $limit_name = 'limit' . $atts['container_suffix'];
    
    // If we can show all entries on a page, do nothing
    if ( $number_entries <= $entries_per_page ) {
        return;
    }

    $page_link = $atts['page_link'] . $limit_name;
    $num_pages = floor (($number_entries / $entries_per_page));
    $mod = $number_entries % $entries_per_page;
    if ($mod != 0) {
        $num_pages = $num_pages + 1;
    }
    
    // Defaults
    $page_input = ' <input name="' . $limit_name . '" type="text" size="2" value="' .  $current_page . '" style="text-align:center;" /> ' . __('of','teachpress') . ' ' . $num_pages . ' ';
    $entries = '<span class="displaying-num">' . $number_entries . ' ' . __('entries','teachpress') . '</span> ';
    $back_links = '<a class="page-numbers button disabled">&laquo;</a> <a class="page-numbers button disabled">&lsaquo;</a> ';
    $next_links = '<a class="page-numbers button disabled">&rsaquo;</a> <a class="page-numbers button disabled">&raquo;</a> ';

    // first page / previous page
    if ( $entry_limit != 0 ) {
        $first_page = '<a href="' . $page_link . '=1&amp;' . $atts['link_attributes'] . '" title="' . __('first page','teachpress') . '" class="page-numbers button">&laquo;</a>';
        $prev_page = ' <a href="' . $page_link . '=' . ($current_page - 1) . '&amp;' . $atts['link_attributes'] . '" title="' . __('previous page','teachpress') . '" class="page-numbers button">&lsaquo;</a> ';
        $back_links = $first_page . $prev_page;
    }

    // next page/ last page
    if ( ( $entry_limit + $entries_per_page ) <= ($number_entries)) { 
        $next_page = '<a href="' . $page_link . '=' . ($current_page + 1) . '&amp;' . $atts['link_attributes'] . '" title="' . __('next page','teachpress') . '" class="page-numbers button">&rsaquo;</a>';
        $last_page = ' <a href="' . $page_link . '=' . $num_pages . '&amp;' . $atts['link_attributes'] . '" title="' . __('last page','teachpress') . '" class="page-numbers button">&raquo;</a> ';
        $next_links = $next_page . $last_page;
    }

    // return
    if ($atts['mode'] === 'top') {
        return $atts['before'] . '<div class="' . $atts['class'] . '">' . $entries . $back_links . $page_input . $next_links . '</div>' . $atts['after'];
    }
    
    return $atts['before'] . '<div class="' . $atts['class'] . '">' . $entries . $back_links . $current_page . ' ' . __('of','teachpress') . ' ' . $num_pages . ' ' . $next_links . '</div>' . $atts['after'];

}

/** 
 * Print message
 * @param string $message   The html content of the message
 * @param string $color     green (default), orange, red
 * @version 2
 * @since 5.0.0
*/ 
function get_tp_message($message, $color = 'green') {
    TP_HTML::line('<div class="teachpress_message teachpress_message_' . esc_attr( $color ) . '">');
    TP_HTML::line('<strong>' . $message . '</strong>');
    TP_HTML::line('</div>');
}

/** 
 * Split a timestamp
 * @param datetime $date_string
 * @return array
 * @since 0.20.0
 *
 * $split[0][0] => Year
 * $split[0][1] => Month 
 * $split[0][2] => Day
 * $split[0][3] => Hour 
 * $split[0][4] => Minute 
 * $split[0][5] => Second
*/ 
function tp_datesplit($date_string) {
    $preg = '/[\d]{2,4}/'; 
    $split = array(); 
    preg_match_all($preg, $date_string, $split); 
    return $split; 
}

/**
 * Translate a publication type
 * @param string $pub_slug  The publication type
 * @param string $num       sin (singular) or pl (plural)
 * @return string
 * @since 2.0.0
 */
function tp_translate_pub_type($pub_slug, $num = 'sin') {
    global $tp_publication_types;
    $types = $tp_publication_types->get();
    
    if ( isset( $types[$pub_slug] ) ) {
        if ( $num == 'sin' ) {
            return $types[$pub_slug]['i18n_singular'];
        }
        else {
            return $types[$pub_slug]['i18n_plural'];
        }
    }
    else {
        return $pub_slug;
    }
}

/** 
 * Get publication types
 * @param string $selected  --> 
 * @param string $mode      --> sng (singular titles) or pl (plural titles)
 * 
 * @version 3
 * @since 4.1.0
 * 
 * @return string
*/
function get_tp_publication_type_options ($selected, $mode = 'sng') {
    global $tp_publication_types;
    $types = '';
    $pub_types = $tp_publication_types->get();
    usort($pub_types, 'sort_tp_publication_type_options');
    foreach ( $pub_types as $row ) {
        $title = ($mode === 'sng') ? $row['i18n_singular'] : $row['i18n_plural'];
        $current = ( $row['type_slug'] == $selected && $selected != '' ) ? 'selected="selected"' : '';
        $types = $types . '<option value="' . $row['type_slug'] . '" ' . $current . '>' . $title . '</option>';  
    }
   return $types;
}

/**
 * Sort function helper for get_tp_publication_type_options()
 * Sorts the publication types after the i18n_singular string
 * @param string $a
 * @param string $b
 * @return int
 * @since 8.0.0
 */
function sort_tp_publication_type_options ($a, $b) {
    return strcmp($a['i18n_singular'], $b['i18n_singular']);
}

/**
 * Get award types
 * @param string $selected  --> 
 * @return string
 * @since 9.0.0
 */
function get_tp_award_options ($selected) {
    global $tp_awards;
    $award = '';
    $pub_awards = $tp_awards->get();
    // usort($pub_awards, 'sort_tp_publication_award_options');
    foreach ( $pub_awards as $row ) {
        $title = $row['i18n_singular'];
        $current = ( $row['award_slug'] == $selected && $selected != '' ) ? 'selected="selected"' : '';
        $award = $award . '<option value="' . $row['award_slug'] . '" ' . $current . '>' . $title . '</option>';  
    }
   return $award;
}


/**
 * Returns the default structure for a publication array
 * @return array 
 * @since 9.0.0
 */
function tp_get_default_structure() {
    $ret = array( 
        'pub_id'            => '',
        'bibtex'            => '',
        'type'              => '',
        'award'             => '',
        'title'             => '',
        'author'            => '',
        'editor'            => '',
        'isbn'              => '',
        'url'               => '',
        'date'              => '',
        'urldate'           => '',
        'booktitle'         => '',
        'issuetitle'        => '',
        'journal'           => '',
        'issue'             => '',
        'volume'            => '',
        'number'            => '',
        'pages'             => '',
        'publisher'         => '',
        'address'           => '',
        'edition'           => '',
        'chapter'           => '',
        'institution'       => '',
        'organization'      => '',
        'school'            => '',
        'series'            => '',
        'crossref'          => '',
        'abstract'          => '',
        'howpublished'      => '',
        'key'               => '',
        'techtype'          => '',
        'comment'           => '',
        'note'              => '',
        'image_url'         => '',
        'image_target'      => '',
        'image_ext'         => '',
        'doi'               => '',
        'is_isbn'           => '',
        'rel_page'          => '',
        'status'            => '',
        'added'             => '',
        'modified'          => '',
        'use_capabilities'  => '',
        'import_id'         => 0);
    return $ret;
}

/** 
 * Define who can use teachPress
 * @param array $roles
 * @param string $capability
 * @since 1.0
 * @version 2
 */
function tp_update_userrole($roles, $capability) {
    global $wp_roles;

    if ( empty($roles) || ! is_array($roles) ) { 
        $roles = array(); 
    }
    $who_can = $roles;
    $who_cannot = array_diff( array_keys($wp_roles->role_names), $roles);
    foreach ($who_can as $role) {
        $wp_roles->add_cap($role, $capability);
    }
    foreach ($who_cannot as $role) {
        $wp_roles->remove_cap($role, $capability);
    }
}

/**
 * Returns a message with the current real amount of memory allocated to PHP
 * @uses memory_get_usage() This function is used with the flag $real_usage = true
 * @return string
 * @since 5.0.0
 */
function tp_get_memory_usage () {
    return 'Current real amount of memory: ' . tp_convert_file_size( memory_get_usage(true) ) . '<br/>';
}

/**
 * Converts a file size in bytes into kB, MB or GB
 * @param int $bytes
 * @return string
 * @since 5.0.0
 */
function tp_convert_file_size ($bytes) {
    $bytes = floatval($bytes);
    if ( $bytes >= 1099511627776 ) {
        return number_format($bytes / 1099511627776, 2) . ' TB';
    }
    if ( $bytes >= 1073741824 ) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    if ( $bytes >= 1048576 ) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ( $bytes >= 1024 ) {
        return number_format($bytes / 1024, 2) . ' kB';
    }
    if ( $bytes > 1 ){
        return $bytes . ' bytes';
    }
    if ( $bytes === 1 ){
        return $bytes . ' byte';
    }
    return '0 bytes';
}

/**
 * Converts an input(array or comma separated string) in a secured comma separated string
 * 
 * The method uses intval, floatval or htmlspecialchars for each element depending on the given
 * $type (string, int, float)
 * @param array|string $input
 * @param string $type  The type of the elements: string, int, float
 * @return string
 * @since 8.0.0
 */
function tp_convert_input_to_string($input, $type = 'string') {
    // if we have an array already
    if ( is_array($input) ) {
        $array = $input;
    }
    else {
        // If we have a comma separated string
        if ( strpos ($input, ',') !== false ) {
            $array = explode(',',$input);
        }
        // If we don't know what we have, so we create an array
        else {
            $array[] = $input;
        }
    }

    $max = count( $array );
    $string = '';
    
    for( $i = 0; $i < $max; $i++ ) {
        // Prepare element
        switch ( $type ) :
            case 'int':
                $element = intval($array[$i]);
                break;
            case 'float':
                $element = floatval($array[$i]);
                break;
            default:
                $element = htmlspecialchars($array[$i]);
        endswitch;
        $string = ( $string === '' ) ? $element : $string . ',' . $element;
    }
    return $string;
}

/**
 * Writes data for the teachPress tinyMCE plugin in Javascript objects
 * @since 5.0.0
 */
function tp_write_data_for_tinymce () {
    
    // Only write the data if the page is a page/post editor
    if ( $GLOBALS['current_screen']->base !== 'post' ) {
        return;
    }
    
    // List of courses
    $course_list = array();
    $course_list[] = array( 'text' => '=== SELECT ===' , 'value' => 0 );
    $semester = get_tp_options('semester', '`setting_id` DESC');
    foreach ( $semester as $row ) {
        $courses = TP_Courses::get_courses( array('parent' => 0, 'semester' => $row->value) );
        foreach ($courses as $course) {
            $course_list[] = array( 'text' => $course->name . ' (' . $course->semester . ')' , 'value' => $course->course_id );
        }
        if ( count($courses) > 0 ) {
            $course_list[] = array( 'text' => '====================' , 'value' => 0 );
        }
    }
    
    // List of semester/term
    $semester_list = array();
    $semester_list[] = array( 'text' => __('Default','teachpress') , 'value' => '' );
    foreach ($semester as $sem) { 
        $semester_list[] = array( 'text' => stripslashes($sem->value) , 'value' => stripslashes($sem->value) );
    }
    
    // List of publication users
    $pub_user_list = array();
    $pub_user_list[] = array( 'text' => __('All','teachpress') , 'value' => '' );
    $pub_users = TP_Publications::get_pub_users();
    foreach ($pub_users as $row) { 
        $user_data = get_userdata($row->user);
        if ( $user_data !== false ) {
            $pub_user_list[] = array( 'text' => $user_data->display_name , 'value' => intval($row->user) );
        }
    }
    
    // List of publication tags
    $pub_tag_list = array();
    $pub_tag_list[] = array( 'text' => __('All','teachpress'), 'value' => null );
    $pub_tags = TP_Tags::get_tags(array( 'group_by' => true ));
    foreach($pub_tags as $pub_tag){
	$pub_tag_list[] = array( 'text' => $pub_tag->name, 'value' => intval($pub_tag->tag_id) );
    }
    
    // List of publication types
    global $tp_publication_types;
    $pub_type_list = array();
    $pub_types = $tp_publication_types->get();
    foreach ( $pub_types as $pub_type ) {
        $pub_type_list[] = array ( 'text' => $pub_type['i18n_singular'], 'value' => stripslashes($pub_type['type_slug']) );
    }
    
    // List of publication templates
    $pub_templates_list = array();
    $pub_templates = tp_list_templates();
    foreach ( $pub_templates as $row ) {
        $pub_templates_list[] = array ( 'text' => $row, 'value' => $row);
    }
    
    // Current post id
    $post_id = ( isset ($_GET['post']) ) ? intval($_GET['post']) : 0;
    
    // Write javascript
    ?>
    <script type="text/javascript">
        var teachpress_courses = <?php echo json_encode($course_list); ?>;
        var teachpress_semester = <?php echo json_encode($semester_list); ?>;
        var teachpress_pub_user = <?php echo json_encode($pub_user_list); ?>;
        var teachpress_pub_types = <?php echo json_encode($pub_type_list); ?>;
        var teachpress_pub_tags = <?php echo json_encode($pub_tag_list) ?>;
        var teachpress_pub_templates = <?php echo json_encode($pub_templates_list); ?>;
        var teachpress_editor_url = '<?php echo admin_url( 'admin-ajax.php' ) . '?action=teachpressdocman&post_id=' . $post_id; ?>';
        var teachpress_cookie_path = '<?php echo SITECOOKIEPATH; ?>';
        var teachpress_file_link_css_class = '<?php echo TEACHPRESS_FILE_LINK_CSS_CLASS; ?>';
        var teachpress_course_module = true;
        var teachpress_publication_module = true;
    </script>
    <?php
}
