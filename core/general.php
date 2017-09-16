<?php
/**
 * This file contains general core functions
 * 
 * @package teachpress\core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/*************************/
/* AJAX request function */
/*************************/

/**
 * AJAX callback function
 * @since 6.0.0
 */
function tp_ajax_callback () {
    
    // Check permissions
    if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
        
        /**
         * Getting author's publications (for show_authors.php)
         * Works if $_GET['author_id'] is given
         */
        $author_id = ( isset( $_GET['author_id'] ) ) ? intval( $_GET['author_id'] ) : 0;
        if ( $author_id !== 0 ) {
            tp_ajax::get_author_publications($author_id);
        }
        
        /**
         * Getting assessment screen (for show_single_course.php)
         * Works if $_GET['assessment_id'] is given
         */
        $assessment_id = ( isset( $_GET['assessment_id'] ) ) ? intval( $_GET['assessment_id'] ) : 0;
        if ( $assessment_id !== 0 ) {
            tp_ajax::get_assessment_screen($assessment_id);
        }
        
        /**
         * Getting artefact screen (for show_single_course.php)
         * Works if $_GET['artefact_id'] is given
         */
        $artefact_id = ( isset( $_GET['artefact_id'] ) ) ? intval( $_GET['artefact_id'] ) : 0;
        if ( $artefact_id !== 0 ) {
            tp_ajax::get_artefact_screen($artefact_id);
        }
        
        /**
         * Removing documents
         * Works if $_GET['del_document'] is given
         */
        $del_document = ( isset( $_GET['del_document'] ) ) ? intval( $_GET['del_document'] ) : 0;
        if ( $del_document !== 0 ) {
            tp_ajax::delete_document($del_document);
        }

        /**
         * Adding document headlines
         * Works if $_GET['add_document'] and $_GET['course_id'] are given
         */
        $add_document = ( isset( $_GET['add_document'] ) ) ? htmlspecialchars( $_GET['add_document'] ) : '';
        $course_id = ( isset( $_GET['course_id'] ) ) ? intval($_GET['course_id']) : 0;
        if ( $add_document !== '' && $course_id !== 0 ) {
            tp_ajax::add_document_headline($add_document, $course_id);
        }

        /**
         * Getting a document name
         * Works if $_GET['get_document_name'] is given
         */
        $get_document_name = ( isset( $_GET['get_document_name'] ) ) ? intval( $_GET['get_document_name'] ) : 0;
        if ( $get_document_name !== 0 ) {
            tp_ajax::get_document_name($get_document_name);
        }

        /**
         * Changing a document name
         * Works if $_POST['change_document'] and $_POST['new_document_name'] are given
         */
        $change_document = ( isset( $_POST['change_document'] ) ) ? intval( $_POST['change_document'] ) : 0;
        $new_document_name = ( isset( $_POST['new_document_name'] ) ) ? htmlspecialchars( $_POST['new_document_name'] ) : '';
        if ( $change_document !== 0 && $new_document_name !== '' ) {
            tp_ajax::change_document_name($change_document, $new_document_name);
        }

        /**
         * Saving sort order of documents
         * Works if $_POST['tp_file'] is given
         */
        if ( isset( $_POST['tp_file'] ) ) {
            tp_ajax::set_sort_order($_POST['tp_file']);
        }

        /**
         * Getting image url for mimetype
         * Works if $_GET['mimetype_input'] is given
         */
        if ( isset( $_GET['mimetype_input'] ) ) {
            tp_ajax::get_mimetype_image($_GET['mimetype_input']);
        }

        /**
         * Getting the cite dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['cite_id'] ) ) {
            tp_ajax::get_cite_screen($_GET['cite_id']);
        }

        /**
         * Getting the cite text for a cite dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['cite_pub'] ) && isset( $_GET['cite_type'] )  ) {
            tp_ajax::get_cite_text($_GET['cite_pub'], $_GET['cite_type']);
        }
        
        /**
         * Getting the edit meta field dialog
         * @since 6.0.0
         */
        if ( isset( $_GET['meta_field_id'] ) ) {
            $meta_field_id = intval( $_GET['meta_field_id'] );
            tp_ajax::get_meta_field_screen($meta_field_id);
        } 
        
        /**
         * Getting the unique version of the bibtex string
         * @since 6.1.1
         */
        if ( isset ( $_GET['bibtex_key_check'] ) ) {
            tp_ajax::get_generated_bibtex_key($_GET['bibtex_key_check']);
        }

    }

    // this is required to terminate immediately and return a proper response
    wp_die();
}

/**
 * AJAX callback function for the document manager
 * @since 6.0.0
 */
function tp_ajax_doc_manager_callback () {
    tp_document_manager::get_window();
    wp_die();
}

/**********************/
/* Template functions */
/**********************/

/**
 * Detects template files and returns an array with available templates
 * @return array
 * @since 6.0.0
 */
function tp_detect_templates() {
    $folder = TEACHPRESS_TEMPLATE_PATH;
    $files = scandir($folder);
    
    if ( $files === false ) {
        return array();
    }
    
    $return = array();
    foreach ( $files as $file ) {
        $infos = pathinfo($folder.$file);
        if ( $infos['extension'] == 'php' || $infos['extension'] == 'php5' ) {
            $return[$infos['filename']] = $folder.$file;
        }
    }
    return $return;
}

/**
 * Returns an array with the data of all available templates
 * @return array
 * @since 6.0.0
 */
function tp_list_templates () {
    $folder = TEACHPRESS_TEMPLATE_PATH;
    $files = scandir($folder);
    $return = array();
    foreach ( $files as $file ) {
        $infos = pathinfo($folder.$file);
        if ( $infos['extension'] == 'php' || $infos['extension'] == 'php5' ) {
            $return[] = $infos['filename'];
        }
    }
    return $return;
}

/**
 * Loads a template and returns the template object or false, if the template doesn't exist
 * @param string $slug
 * @return object|boolean
 * @since 6.0.0
 */
function tp_load_template($slug) {
    if ( $slug === '' ) {
        return;
    }
    
    $slug = esc_attr($slug);
    $templates = tp_detect_templates();
    
    // load template file
    if ( array_key_exists($slug, $templates) ) {
        include_once $templates[$slug];
        wp_enqueue_style($slug, TEACHPRESS_TEMPLATE_URL . $slug. '.css');
        return new $slug();
    }
    
    return false;

}

/** 
 * teachPress Page Menu
 * 
 * possible values for array $atts:
 *      @type int number_entries       Number of all available entries
 *      @type int entries_per_page     Number of entries per page
 *      @type int current_page         current displayed page
 *      @type string entry_limit       SQL entry limit
 *      @type string page_link         the name of the page you will insert the menu
 *      @type string link_atrributes   the url attributes for get parameters
 *      @type string mode              top or bottom, default: top
 * @param array $atts
 * @return string
 * @since 5.0.0
*/
function tp_page_menu ($atts) {
    extract(shortcode_atts(array(
       'number_entries' => 0,
       'entries_per_page' => 50,
       'current_page' => 1,
       'entry_limit' => 0,
       'page_link' => '',
       'link_attributes' => '',
       'mode' => 'top',
       'class' => 'tablenav-pages',
       'before' => '',
       'after' => ''
    ), $atts));
    $number_entries = intval($number_entries);
    $entries_per_page = intval($entries_per_page);
    $current_page = intval($current_page);
    $entry_limit = intval($entry_limit);
    
    // if number of entries > number of entries per page
    if ($number_entries > $entries_per_page) {
        $num_pages = floor (($number_entries / $entries_per_page));
        $mod = $number_entries % $entries_per_page;
        if ($mod != 0) {
            $num_pages = $num_pages + 1;
        }

        // first page / previous page
        if ($entry_limit != 0) {
            $back_links = '<a href="' . $page_link . 'limit=1&amp;' . $link_attributes . '" title="' . __('first page','teachpress') . '" class="page-numbers">&laquo;</a> <a href="' . $page_link . 'limit=' . ($current_page - 1) . '&amp;' . $link_attributes . '" title="' . __('previous page','teachpress') . '" class="page-numbers">&lsaquo;</a> ';
        }
        else {
            $back_links = '<a class="first-page disabled">&laquo;</a> <a class="prev-page disabled">&lsaquo;</a> ';
        }
        $page_input = ' <input name="limit" type="text" size="2" value="' .  $current_page . '" style="text-align:center;" /> ' . __('of','teachpress') . ' ' . $num_pages . ' ';

        // next page/ last page
        if ( ( $entry_limit + $entries_per_page ) <= ($number_entries)) { 
            $next_links = '<a href="' . $page_link . 'limit=' . ($current_page + 1) . '&amp;' . $link_attributes . '" title="' . __('next page','teachpress') . '" class="page-numbers">&rsaquo;</a> <a href="' . $page_link . 'limit=' . $num_pages . '&amp;' . $link_attributes . '" title="' . __('last page','teachpress') . '" class="page-numbers">&raquo;</a> ';
        }
        else {
            $next_links = '<a class="next-page disabled">&rsaquo;</a> <a class="last-page disabled">&raquo;</a> ';
        }

        // return
        if ($mode === 'top') {
            return $before . '<div class="' . $class . '"><span class="displaying-num">' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . '' . $page_input . '' . $next_links . '</div>' . $after;
        }
        else {
            return $before . '<div class="' . $class . '"><span class="displaying-num">' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . ' ' . $current_page . ' ' . __('of','teachpress') . ' ' . $num_pages . ' ' . $next_links . '</div>' . $after;
        }	
    }
}	

/** 
 * Print message
 * @param string $message   The html content of the message
 * @param string $color     green (default), orange, red
 * @version 2
 * @since 5.0.0
*/ 
function get_tp_message($message, $color = 'green') {
    echo '<div class="teachpress_message teachpress_message_' . esc_attr( $color ) . '">';
    echo '<strong>' . $message . '</strong>';
    echo '</div>';
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
 * Gives an array with all publication types
 * 
 * Definition of array[] $pub_types:
 *      $pub_types[x][0] ==> BibTeX key
 *      $pub_types[x][1] ==> i18n string (singular)
 *      $pub_types[x][2] ==> i18n string (plural)
 * 
 * @return array
*/ 
function get_tp_publication_types() {
    $pub_types[0] = array (0 => '0', 1 => __('All types','teachpress'), 2 => __('All types','teachpress'));
    $pub_types[1] = array (0 => 'article', 1 => __('Journal Article','teachpress'), 2 => __('Journal Articles','teachpress'));
    $pub_types[2] = array (0 => 'book', 1 => __('Book','teachpress'), 2 => __('Books','teachpress'));
    $pub_types[3] = array (0 => 'booklet', 1 => __('Booklet','teachpress'), 2 => __('Booklets','teachpress'));
    $pub_types[4] = array (0 => 'collection', 1 => __('Collection','teachpress'), 2 => __('Collections','teachpress'));
    $pub_types[5] = array (0 => 'conference', 1 => __('Conference','teachpress'), 2 => __('Conferences','teachpress'));
    $pub_types[6] = array (0 => 'inbook', 1 => __('Book Chapter','teachpress'), 2 => __('Book Chapters','teachpress'));
    $pub_types[7] = array (0 => 'incollection', 1 => __('Incollection','teachpress'), 2 => __('Incollections','teachpress'));
    $pub_types[8] = array (0 => 'inproceedings', 1 => _x('Inproceedings','Singular form of inproceedings, if it exists','teachpress'), 2 => __('Inproceedings','teachpress'));
    $pub_types[9] = array (0 => 'manual', 1 => __('Technical Manual','teachpress'), 2 => __('Technical Manuals','teachpress'));
    $pub_types[10] = array (0 => 'mastersthesis', 1 => __('Masters Thesis','teachpress'), 2 => __('Masters Theses','teachpress'));
    $pub_types[11] = array (0 => 'misc', 1 => __('Miscellaneous','teachpress'), 2 => __('Miscellaneous','teachpress'));
    $pub_types[12] = array (0 => 'online', 1 => __('Online','teachpress'), 2 => __('Online','teachpress'));
    $pub_types[13] = array (0 => 'patent', 1 => __('Patent','teachpress'), 2 => __('Patents','teachpress'));
    $pub_types[14] = array (0 => 'periodical', 1 => __('Periodical','teachpress'), 2 => __('Periodicals','teachpress'));
    $pub_types[15] = array (0 => 'phdthesis', 1 => __('PhD Thesis','teachpress'), 2 => __('PhD Theses','teachpress'));
    $pub_types[16] = array (0 => 'presentation', 1 => __('Presentation','teachpress'), 2 => __('Presentations','teachpress'));
    $pub_types[17] = array (0 => 'proceedings', 1 => __('Proceeding','teachpress'), 2 => __('Proceedings','teachpress'));
    $pub_types[18] = array (0 => 'techreport', 1 => __('Technical Report','teachpress'), 2 => __('Technical Reports','teachpress'));
    $pub_types[19] = array (0 => 'unpublished', 1 => __('Unpublished','teachpress'), 2 => __('Unpublished','teachpress'));
    $pub_types[20] = array (0 => 'workshop', 1 => __('Workshop','teachpress'), 2 => __('Workshops','teachpress'));
    return $pub_types;
}

/**
 * get the path to a mimetype image
 * @param string $url   --> the URL of a file
 * @return string 
 * @since 3.1.0
 */
function get_tp_mimetype_images($url) {
    $mimetype = substr($url,-4,4);
    $url = plugins_url();
    $mimetypes = array(
        '.pdf' => $url . '/teachpress/images/mimetypes/application-pdf.png',
        '.doc' => $url . '/teachpress/images/mimetypes/application-msword.png',
        'docx' => $url . '/teachpress/images/mimetypes/application-msword.png',
        '.ppt' => $url . '/teachpress/images/mimetypes/application-mspowerpoint.png',
        'pptx' => $url . '/teachpress/images/mimetypes/application-mspowerpoint.png',
        '.xls' => $url . '/teachpress/images/mimetypes/application-msexcel.png',
        'xlsx' => $url . '/teachpress/images/mimetypes/application-msexcel.png',
        '.odt' => $url . '/teachpress/images/mimetypes/application-opendocument.text.png',
        '.ods' => $url . '/teachpress/images/mimetypes/application-opendocument.spreadsheet.png',
        '.odp' => $url . '/teachpress/images/mimetypes/application-opendocument.presentation.png',
        '.odf' => $url . '/teachpress/images/mimetypes/application-opendocument.formula.png',
        '.odg' => $url . '/teachpress/images/mimetypes/application-opendocument.graphics.png',
        '.odc' => $url . '/teachpress/images/mimetypes/application-opendocument.chart.png',
        '.odi' => $url . '/teachpress/images/mimetypes/application-opendocument.image.png',
        '.rtf' => $url . '/teachpress/images/mimetypes/application-rtf.png',
        '.rdf' => $url . '/teachpress/images/mimetypes/text-rdf.png',
        '.txt' => $url . '/teachpress/images/mimetypes/text-plain.png',
        '.tex' => $url . '/teachpress/images/mimetypes/text-x-bibtex.png',
        'html' => $url . '/teachpress/images/mimetypes/text-html.png',
        '.php' => $url . '/teachpress/images/mimetypes/text-html.png',
        '.xml' => $url . '/teachpress/images/mimetypes/text-xml.png',
        '.csv' => $url . '/teachpress/images/mimetypes/text-csv.png',
        '.mp3' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.wma' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.wav' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.gif' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.jpg' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.png' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.svg' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.dvi' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.flv' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.mov' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.mp4' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.wmv' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        );
    if ( isset ($mimetypes[$mimetype]) ) {
        return $mimetypes[$mimetype];
    }
    else {
        return $mimetypes['html'];
    }
}

/**
 * Translate a publication type
 * @param string $string    The publication type
 * @param string $num       sin (singular) or pl (plural)
 * @return string
 * @since 2.0.0
 */
function tp_translate_pub_type($string, $num = 'sin') {
    $types = get_tp_publication_types();
    $max = count($types);
    $translated_string = '';
    $num = ( $num === 'sin' ) ? 1 : 2;
    for ( $i = 1; $i < $max; $i++ ) {
        if ( $string == $types[$i][0] ) {
            $translated_string = $types[$i][$num];
            break;
        }
    }
    return $translated_string;
}

/** 
 * Get publication types
 * @param string $selected  --> 
 * @param string $mode      --> sng (singular titles) or pl (plural titles)
 * 
 * @version 2
 * @since 4.1.0
 * 
 * @return string
*/
function get_tp_publication_type_options ($selected, $mode = 'sng') {
     $selected = htmlspecialchars($selected);
     $types = '';
     $pub_types = get_tp_publication_types();
     $m = ($mode === 'sng') ? 1 : 2;
     $max = count($pub_types);
     for ($i = 1; $i < $max; $i++) {
         $current = ($pub_types[$i][0] == $selected && $selected != '') ? 'selected="selected"' : '';
         $types = $types . '<option value="' . $pub_types[$i][0] . '" ' . $current . '>' . __('' . $pub_types[$i][$m] . '','teachpress') . '</option>';  
     }
   return $types;
}

/**
 * Get the array structure for a parameter
 * @param string $type  --> values: course_array, publication_array
 * @return array 
 */
function get_tp_var_types($type) {
    if ( $type == 'course_array' ) {
        $ret = array( 
            'course_id' => '',
            'name' => '',
            'type' => '',
            'room' => '',
            'lecturer' => '',
            'date' => '',
            'places' => '',
            'start' => '',
            'end' => '',
            'semester' => '',
            'comment' => '',
            'rel_page' => '',
            'parent' => '',
            'visible' => '',
            'waitinglist' => '',
            'image_url' => '',
            'strict_signup' => '',
            'use_capabilites' => '');
    }
    if ( $type == 'publication_array' ) {
        $ret = array( 
            'pub_id' => '',
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
            'doi' => '',
            'is_isbn' => '',
            'rel_page' => '',
            'status' => '',
            'added' => '',
            'modified' => '',
            'import_id' => 0);
    }
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
        $courses = tp_courses::get_courses( array('parent' => 0, 'semester' => $row->value) );
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
    $pub_users = tp_publications::get_pub_users();
    foreach ($pub_users as $row) { 
        $user_data = get_userdata($row->user);
        if ( $user_data !== false ) {
            $pub_user_list[] = array( 'text' => $user_data->display_name , 'value' => intval($row->user) );
        }
    }
    
    // List of publication tags
    $pub_tag_list = array();
    $pub_tag_list[] = array( 'text' => __('All','teachpress'), 'value' => null );
    $pub_tags = tp_tags::get_tags(array( 'group_by' => true ));
    foreach($pub_tags as $pub_tag){
	$pub_tag_list[] = array( 'text' => $pub_tag->name, 'value' => intval($pub_tag->tag_id) );
    }
    
    // List of publication types
    $pub_type_list = array();
    $pub_types = get_tp_publication_types();
    foreach ( $pub_types as $pub_type ) {
        $pub_type_list[] = array ( 'text' => $pub_type[1], 'value' => stripslashes($pub_type[0]) );
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
        var teachpress_course_module = <?php if (TEACHPRESS_COURSE_MODULE === true) { echo 'true'; } else { echo 'false'; } ?>;
        var teachpress_publication_module = <?php if (TEACHPRESS_PUBLICATION_MODULE === true) { echo 'true'; } else { echo 'false'; } ?>;
    </script>
    <?php
}
