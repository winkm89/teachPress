<?php
/**
 * This file contains the shortcode functions (without [tp_enrollments])
 *
 * @package teachpress\core\shortcodes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all shortcode helper functions
 * @since 5.0.0
 * @package teachpress\core\shortcodes
 */
class TP_Shortcodes {

    /**
     * Generates and returns filter for the shortcodes
     * @param string $key                   year/type/author/user/tag
     * @param array $filter_parameter       An associative array with filter parameter (user input). The keys are: year, type, author, user
     * @param array $sql_parameter          An associative array with SQL search parameter (user, type, exclude, exclude_tags, order)
     * @param array $settings               An associative array with settings (permalink, html_anchor,...)
     * @param int $tabindex                 The tabindex
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_filter ($key, $filter_parameter, $sql_parameter, $settings, $tabindex){

        $defaults = [
            'key'               => '',
            'title'             => '',
            'url_slug'          => '',
            'row_key'           => '',
            'filter_parameter'  => $filter_parameter,
            'custom_filters'    => explode(',', $settings['custom_filter']),
            'tabindex'          => $tabindex,
            'author_name'       => $settings['author_name'],
            'html_anchor'       => $settings['html_anchor'],
            'filter_class'      => $settings['filter_class'],
            'permalink'         => $settings['permalink']
        ];

        // year filter
        if ( $key === 'year' ) {
            $row = TP_Publications::get_years( array( 'user'            => $filter_parameter['user_preselect'],
                                                      'type'            => $filter_parameter['type_preselect'],
                                                      'include'         => $filter_parameter['year_preselect'],
                                                      'years_between'   => $sql_parameter['years_between'],
                                                      'order'           => 'DESC',
                                                      'output_type'     => ARRAY_A ) );
            $defaults['url_slug'] = 'yr';
            $defaults['key'] = 'year';
            $defaults['row_key'] = 'year';
            $defaults['title'] = __('All years','teachpress');
        }

        // type filter
        if ( $key === 'type' ) {
            $row = TP_Publications::get_used_pubtypes( array( 'user'    => $filter_parameter['user_preselect'],
                                                              'include' => $filter_parameter['type_preselect'],
                                                              'exclude' => isset($sql_parameter['exclude_types']) ? $sql_parameter['exclude_types'] : '') );
            $defaults['url_slug'] = 'type';
            $defaults['key'] = 'type';
            $defaults['row_key'] = 'type';
            $defaults['title'] = __('All types','teachpress');
        }

        // author filter
        if ( $key === 'author' ) {
            // Use the visible filter o the SQL parameter
            $author_id = ($filter_parameter['show_in_author_filter'] !== '') ? $filter_parameter['show_in_author_filter'] : $filter_parameter['author_preselect'];

            $row = TP_Authors::get_authors( array( 'user'           => $sql_parameter['user'],
                                                   'author_id'      => $author_id,
                                                   'output_type'    => ARRAY_A,
                                                   'group_by'       => true ) );
            $defaults['url_slug'] = 'auth';
            $defaults['key'] = 'author';
            $defaults['row_key'] = 'author_id';
            $defaults['title'] = __('All authors','teachpress');
        }

        // user filter
        if ( $key === 'user' ) {
            $row = TP_Publications::get_pub_users( array('output_type' => ARRAY_A) );
            $defaults['url_slug'] = 'usr';
            $defaults['key'] = 'user';
            $defaults['row_key'] = 'user';
            $defaults['title'] = __('All users','teachpress');
        }

        // tag filter
        if ( $key === 'tag' ) {
            $exclude = self::generate_merged_string($sql_parameter['exclude_tags'], $settings['hide_tags']);
            $row = TP_Tags::get_tags( array( 'output_type'      => ARRAY_A,
                                             'group_by'         => true,
                                             'order'            => 'ASC',
                                             'exclude'          => $exclude ) );
            $defaults['url_slug'] = 'tgid';
            $defaults['key'] = 'tag';
            $defaults['row_key'] = 'tag_id';
            $defaults['title'] = __('All tags','teachpress');
        }

        // Generate filter
        if ( $settings['use_jumpmenu'] === true ) {
            return self::generate_filter_jumpmenu($row, $defaults);
        }
        return self::generate_filter_selectmenu($row, $defaults);
    }
    
    /**
     * Merges for example the input for the exclude_tags and hide_tags filter to one single entry
     * @param string $string1
     * @param string $string2
     * @return string
     * @since 9.0.2
     */
    private static function generate_merged_string($string1, $string2) {
        // reduce processing 
        if ( $string1 === '' ) {
            return $string2;
        }
        
        $array1 = explode(',', $string1);
        $array2 = explode(',', $string2);
        $array = array_merge($array1, $array2);
        return implode(',', $array);
    }

    /**
     * Generates a filter for a custom select field
     * @param array $settings
     * @param array $filter_parameter
     * @param int $tabindex
     * @return type
     * @since 8.1
     */
    public static function generate_custom_filter($settings, $filter_parameter, $tabindex) {
        if ( $settings['custom_filter'] === '') {
            return;
        }

        // field name / labels are separated by comma
        $custom_filter = '';
        $filters = explode(',', $settings['custom_filter']);
        $filter_labels = explode(',', $settings['custom_filter_label']);

        //var_dump($settings['custom_filter_label']);

        $defaults = [
            'key'               => '',
            'title'             => '',
            'url_slug'          => '',
            'row_key'           => 'variable',
            'filter_parameter'  => $filter_parameter,
            'custom_filters'    => explode(',', $settings['custom_filter']),
            'tabindex'          => $tabindex,
            'author_name'       => $settings['author_name'],
            'html_anchor'       => $settings['html_anchor'],
            'filter_class'      => $settings['filter_class'],
            'permalink'         => $settings['permalink']
        ];

        // Create the filters
        for ( $i = 0; $i < count($filters); $i++ ) {
            $row = get_tp_options($filters[$i], $order = "`variable` ASC", ARRAY_A);
            $defaults['key'] = $filters[$i];
            $defaults['title'] = $filter_labels[$i];
            $defaults['url_slug'] = $filters[$i];

            if ( $settings['use_jumpmenu'] === true ) {
                $custom_filter .= self::generate_filter_jumpmenu($row, $defaults);
            }
            else {
                $custom_filter .= self::generate_filter_selectmenu($row, $defaults);
            }
            $tabindex++;
        }
        return $custom_filter;
    }

    /**
     * Generates and returns filter for the shortcodes (jumpmenus)
     * @param array $rows               The array of select options
     * @param array $args {
     *      @type string key                name/id of the field
     *      @type string title              The title for the default value
     *      @type string url_slug           The name for the field used in the URL
     *      @type string row_key            The used key in the rows array
     *      @type array filter_parameter    An array with the user input. The keys are: year, type, author, user
     *      @type array custom_filters      An array with optional custom filter keys
     *      @type int tabindex              The tabindex fo the form field
     *      @type string author_name        From $settings
     *      @type string html_anchor        From $settings
     *      @type string filter_class       From $settings
     *      @type string permalink          From $settings
     * }
     * @return string
     * @since 7.0.0
     * @version 2
     */
    private static function generate_filter_jumpmenu( $rows, $args = [] ) {
        $defaults = [
            'key'               => '',
            'title'             => '',
            'url_slug'          => '',
            'row_key'           => '',
            'filter_parameter'  => [],
            'custom_filters'    => [],
            'tabindex'          => '',
            'author_name'       => '',
            'html_anchor'       => '',
            'filter_class'      => '',
            'permalink'         => ''
        ];
        $atts = wp_parse_args($args, $defaults);
        $html = '';
        $base = self::generate_jumpmenu_url( $atts['filter_parameter'] , $atts['key'], $atts['custom_filters'] );
        $filter_parameter = $atts['filter_parameter'];
        $key = $atts ['key'];

        // generate option
        foreach ( $rows as $row ){
            $value = $row[ $atts['row_key'] ];
            // Set the values for URL parameters
            $current = ( $value == $filter_parameter[ $key ] && $filter_parameter[ $key ] != '0' ) ? 'selected="selected"' : '';

            // Set the label for each select option
            if ( $key === 'type' ) {
                $text = tp_translate_pub_type($row['type'], 'pl');
            }
            else if ( $key === 'author' ) {
                $text = TP_Bibtex::parse_author($row['name'], '', $atts['author_name']);
            }
            else if ( $key === 'user' ) {
                $user_info = get_userdata( $row['user'] );
                if ( $user_info === false ) {
                    continue;
                }
                $text = $user_info->display_name;
            }
            else if ( $key === 'tag' ) {
                $text = $row['name'];
            }
            else {
                $text = $value;
            }

            // Write the select option
            $html .= '<option value = "' . $base . '&amp;' . $atts ['url_slug'] . '=' . urlencode($value) . $atts['html_anchor'] . '" ' . $current . '>' . stripslashes(urldecode($text)) . '</option>';
        }

        // return filter menu
        return '<select class="' . $atts['filter_class'] . '" name="' . $atts ['url_slug'] . '" id="' . $atts ['url_slug'] . '" tabindex="' . $atts['tabindex'] . '" onchange="teachpress_jumpMenu(' . "'" . 'parent' . "'" . ',this, ' . "'" . stripslashes(urldecode($atts['permalink'])) . "'" . ')">
                   <option value="' . $base . '&amp;' . $atts ['url_slug'] . '=' . $atts['html_anchor'] . '">' . $atts['title'] . '</option>
                   ' . $html . '
                </select>';
    }

    /**
     * Generates the parameter part for jumpmenu URL
     * @param array $filter_parameter   The filter_parameters array
     * @param string $hide_key          The key you don't want to add to the URL here
     * @param array $additional_keys    An array iwht the custom keys in the filter_parameters array
     * @return string
     * @since 8.1.6
     */
    private static function generate_jumpmenu_url($filter_parameter, $hide_key = '', $additional_keys = [] ) {
        $basic_keys = ['tag', 'year', 'type', 'author', 'user'];
        $url_vars = ['tag'      => 'tgid',
                     'year'     => 'yr',
                     'type'     => 'type',
                     'author'   => 'auth',
                     'user'     => 'usr' ];
        $keys = array_merge($basic_keys, $additional_keys);
        $params = '';

        foreach ( $keys as $key ) {
            if ( $key === $hide_key || empty($key) ) {
                continue;
            }
            $url_param = isset ( $url_vars[ $key ] ) ? $url_vars[ $key ] : $key;
            $element = $url_param . '=' . $filter_parameter[ $key ];
            $params .= ( $params === '' ) ? $element : '&amp;' . $element;
        }
        return $params;
    }

    /**
     * Generates and returns filter for the shortcodes (selectmenus)
     * @param array $row                The array of select options
     * @param string $id                name/id of the form field
     * @param string $index             year/type/author_id/user/tag_id
     * @param string $title             The title for the default value
     * @param array $filter_parameter   An array with the user input. The keys are: year, type, author, user
     * @param array $settings           An array with SQL search parameter (user, type, exclude, exclude_tags, order)
     * @param int tabindex              The tabindex fo the form field
     * @param string $mode              year/type/author/user/tag
     * @return string
     * @since 7.0.0
     */
    private static function generate_filter_selectmenu($row, $args = [] ) {
        $options = '';
        $defaults = [
            'key'               => '',
            'title'             => '',
            'url_slug'          => '',
            'row_key'           => '',
            'filter_parameter'  => [],
            'custom_filters'    => [],
            'tabindex'          => '',
            'author_name'       => '',
            'html_anchor'       => '',
            'filter_class'      => '',
            'permalink'         => ''
        ];
        $atts = wp_parse_args($args, $defaults);
        $filter_parameter = $atts['filter_parameter'];
        $key = $atts ['key'];

        // generate option
        foreach ( $row as $row ){
            $value = $row[ $atts['row_key'] ];
            $current = ( $value == $filter_parameter[$key] && $filter_parameter[$key] != '0' ) ? 'selected="selected"' : '';

            // Set the label for each select option
            if ( $key === 'type' ) {
                $text = tp_translate_pub_type($row['type'], 'pl');
            }
            else if ( $key === 'author' ) {
                $text = TP_Bibtex::parse_author($row['name'], '', $atts['author_name']);
            }
            else if ( $key === 'user' ) {
                $user_info = get_userdata( $row['user'] );
                if ( $user_info === false ) {
                    continue;
                }
                $text = $user_info->display_name;
            }
            else if ( $key === 'tag' ) {
                $text = $row['name'];
            }
            else {
                $text = $value;
            }

            // Write the select option
            $options .= '<option value="' . $value. '" ' . $current . '>' . stripslashes($text) . '</option>';
        }

        // return filter menu
        return '<select class="' . $atts['filter_class'] . '" title="' . $atts['title'] . '" name="' . $atts ['url_slug'] . '" id="' . $atts ['url_slug'] . '" tabindex="' . $atts['tabindex'] . '">
                   <option value="">' . $atts['title'] . '</option>
                   ' . $options . '
                </select>';
    }

    /**
     * Generates the pagination limits for lists
     * @param int $pagination           0 or 1 (pagination is used or not)
     * @param int $entries_per_page     Number of entries per page
     * @param int $form_limit           Current position in the list, which is set by a form
     * @return array
     * @since 6.0.0
     */
    public static function generate_pagination_limits($pagination, $entries_per_page, $form_limit) {

        // Define page variables
        if ( $form_limit != '' ) {
            $current_page = $form_limit;
            if ( $current_page <= 0 ) {
                $current_page = 1;
            }
            $entry_limit = ( $current_page - 1 ) * $entries_per_page;
        }
        else {
            $entry_limit = 0;
            $current_page = 1;
        }

        // Define SQL limit
        if ( $pagination === 1 ) {
            $limit = $entry_limit . ',' . $entries_per_page;
        }
        else {
            $limit = ( $entries_per_page > 0 ) ? $entry_limit . ',' . $entries_per_page : '';
        }

        return array(
            'entry_limit' => $entry_limit,
            'current_page' => $current_page,
            'limit' => $limit
        );

    }

    /**
     * Generates the list of publications for [tplist], [tpcloud], [tpsearch]
     * @param array $tparray    The array of publications
     * @param object $template  The template object
     * @param array $args       An associative array with options (headline,...)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_pub_table($tparray, $template, $args ) {
        $headlines = array();
        if ( $args['headline'] == 1 ) {
            foreach( $args['years'] as $row ) {
                $headlines[$row['year']] = '';
            }
            $pubs = TP_Shortcodes::sort_pub_table( $tparray, $template, $headlines , $args );
        }
        elseif ( $args['headline'] == 2 ) {
            $pub_types = TP_Publications::get_used_pubtypes( array('user' => $args['user'] ) );
            foreach( $pub_types as $row ) {
                $headlines[$row['type']] = '';
            }
            $pubs = TP_Shortcodes::sort_pub_table( $tparray, $template, $headlines, $args );
        }
        else {
            $pubs = TP_Shortcodes::sort_pub_table( $tparray, $template, '', $args );
        }
        return $template->get_body($pubs, $args);
    }

    /**
     * Returns a tag cloud
     * @param int $user                 The user ID
     * @param array $filter_parameter   An associative array with filter parameter (user input). The keys are: year, type, author, user
     * @param array $sql_parameter      An associative array with SQL search parameter (user, type)
     * @param array $settings           An associative array with settings (permalink, html_anchor, tag_limit, maxsize, minsize)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_tag_cloud ($user, $filter_parameter, $sql_parameter, $settings){
        $temp = TP_Tags::get_tag_cloud( array(
                                        'user'          => $user,
                                        'type'          => $sql_parameter['type'],
                                        'exclude'       => $settings['hide_tags'],
                                        'number_tags'   => $settings['tag_limit'],
                                        'output_type'   => ARRAY_A ) );
       $min = $temp["info"]->min;
       $max = $temp["info"]->max;
       // level out the min
       if ($min == 1) {
          $min = 0;
       }
       // Create the cloud
       $tags = '';
       foreach ($temp["tags"] as $tagcloud) {
          $link_url = $settings['permalink'];
          $link_title = "";
          $link_class = "";
          $pub = ( $tagcloud['tagPeak'] == 1 ) ? __('Publication', 'teachpress') : __('Publications', 'teachpress');

          // division through zero check
          $divisor = ( $max - $min === 0 ) ? 1 : $max - $min;

          // calculate the font size
          // max. font size * (current occorence - min occurence) / (max occurence - min occurence)
          $size = floor(( $settings['maxsize'] *( $tagcloud['tagPeak'] - $min ) / $divisor ));
          // level out the font size
          if ( $size < $settings['minsize'] ) {
             $size = $settings['minsize'] ;
          }

          // for current tags
          if ( $filter_parameter['tag'] == $tagcloud['tag_id'] ) {
              $link_class = "teachpress_cloud_active";
              $link_title = __('Delete tag as filter','teachpress');
          }
          else {
              $link_title = $tagcloud['tagPeak'] . " $pub";
              $link_url .= "tgid=" . $tagcloud['tag_id'] . "&amp;";
          }

          // define url
          $link_url .= 'yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . $settings['html_anchor'];

          $tags .= '<span style="font-size:' . $size . 'px;"><a rel="nofollow" href="' . $link_url . '" title="' . $link_title . '" class="' . $link_class . '">' . stripslashes($tagcloud['name']) . '</a></span> ';
       }
       return $tags;
    }

    /**
     * Generates a "show all" link for clearing all filters
     * @param array $atts
     * @param array $filter_parameter
     * @param array $settings
     * @return string
     */
    public static function generate_show_all_link ($atts, $filter_parameter, $settings) {
        $custom_clean = '';
        if ( $settings['custom_filter'] !== '' ) {
            $f = explode(',', $settings['custom_filter']);
            foreach ( $f as $f ) {
                if ( $filter_parameter[$f] != '' ) {
                    $custom_clean = 'b';
                }
            }
        }
        if ( ( $filter_parameter['year'] == '' || $filter_parameter['year'] == $atts['year'] ) &&
             ( $filter_parameter['type'] == '' || $filter_parameter['type'] == $atts['type'] ) &&
             ( $filter_parameter['user'] == '' || $filter_parameter['user'] == $atts['user'] ) &&
             ( $filter_parameter['author'] == '' || $filter_parameter['author'] == $atts['author'] ) &&
             ( $filter_parameter['tag'] == '' || $filter_parameter['tag'] == $atts['tag'] ) &&
               $filter_parameter['search'] == '' && $custom_clean == ''
            ) {
            return '';
        }

        return '<a rel="nofollow" href="' . $settings['permalink'] . $settings['html_anchor'] . '" title="' . __('Show all','teachpress') . '">' . __('Show all','teachpress') . '</a>';

    }

    /**
     * Sort the table lines of a publication table
     * @param array $tparray        Array of publications
     * @param object $template      The template object
     * @param array $headlines      Array of headlines
     * @param array $args           Array of arguments
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function sort_pub_table($tparray, $template, $headlines, $args) {
        $publications = '';
        $tpz = $args['number_publications'];

        // with headlines
        if ( $args['headline'] === 1 || $args['headline'] === 2 ) {
            $publications = TP_Shortcodes::sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines);
        }
        // with headlines grouped by year then by type
        else if ($args['headline'] === 3) {
            $publications = TP_Shortcodes::sort_pub_by_year_type($tparray, $template, $tpz, $args);
        }
        // with headlines grouped by type then by year
        else if ($args['headline'] === 4) {
            $publications = TP_Shortcodes::sort_pub_by_type_year($tparray, $template, $tpz, $args);
        }
        // without headlines
        else {
            for ($i = 0; $i < $tpz; $i++) {
                $publications .= $tparray[$i][1];
            }
        }

        return $publications;
    }

    /**
     * Sorts the publications by type or by year. This is the default sort function
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines){
        $return = '';
        $field = ( $args['headline'] === 2 ) ? 2 : 0;
        for ( $i = 0; $i < $tpz; $i++ ) {
            $key = $tparray[$i][$field];
            $headlines[$key] .= $tparray[$i][1];
        }

        // custom sort order
        if ( $args['sort_list'] !== '' ) {
            $args['sort_list'] = str_replace(' ', '', $args['sort_list']);
            $sort_list = explode(',', $args['sort_list']);
            $max = count($sort_list);
            $sorted = array();
            for ($i = 0; $i < $max; $i++) {
                if ( array_key_exists($sort_list[$i], $headlines) ) {
                    $sorted[$sort_list[$i]] = $headlines[$sort_list[$i]];
                }
            }
            $headlines = $sorted;
        }

        // set headline
        foreach ( $headlines as $key => $value ) {
            if ( $value != '' ) {
                $args['id'] = $key;
                $line_title = ( $args['headline'] === 1 ) ? $key : tp_translate_pub_type($key, 'pl');
                $return .= $template->get_headline($line_title, $args);
                $return .= $value;
            }
        }
        return $return;
    }

    /**
     * Sorts the publications by type and by year (used for headline type 4)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_year($tparray, $template, $tpz, $args) {
        $return = '';
        $typeHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            $pubVal  = $tparray[$i][1];
            if(!array_key_exists($keyType, $typeHeadlines)) {
                $typeHeadlines[$keyType] = array($keyYear => $pubVal);
            }
            else if(!array_key_exists($keyYear, $typeHeadlines[$keyType])) {
                $typeHeadlines[$keyType][$keyYear] = $pubVal;
            }
            else {
                $typeHeadlines[$keyType][$keyYear] .= $pubVal;
            }
        }
        foreach ( $typeHeadlines as $type => $yearHeadlines ) {
            $args['id'] = $type;
            $return .= $template->get_headline( tp_translate_pub_type($type, 'pl'), $args );
            foreach($yearHeadlines as $year => $pubValue) {
                if ($pubValue != '' ) {
                    $args['id'] = $year;
                    $return .= $template->get_headline( $year, $args );
                    $return .= $pubValue;
                }
            }
        }
        return $return;
    }

    /**
     * Sorts the publications by year and by type (used for headline type 3)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_year_type ($tparray, $template, $tpz, $args) {
        $return = '';
        $yearHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            if(!array_key_exists($keyYear, $yearHeadlines)) {
                $yearHeadlines[$keyYear] = array($keyType => '');
            }
            else if(!array_key_exists($keyType, $yearHeadlines[$keyYear])) {
                $yearHeadlines[$keyYear][$keyType] = '';
            }
            $yearHeadlines[$keyYear][$keyType] .= $tparray[$i][1];
        }

        foreach ( $yearHeadlines as $year => $typeHeadlines ) {
            $args['id'] = $year;
            $return .= $template->get_headline($year, $args);
            foreach($typeHeadlines as $type => $value) {
                if ($value != '' ) {
                    $args['id'] = $type;
                    $return .= $template->get_headline( tp_translate_pub_type($type, 'pl'), $args );
                    $return .=  $value;
                }
            }
        }
        return $return;
    }

    /**
     * Sets and returns the publication data. Used for tp_bibtex, tp_abstract and tp_links shortcodes
     * @param array $param
     * @param array $tp_single_publication
     * @return array
     * @since 6.0.0
     * @access public
     */
    public static function set_publication ($param, $tp_single_publication) {
        if ( $param['key'] != '' ) {
            return TP_Publications::get_publication_by_key($param['key'], ARRAY_A);
        }
        elseif ( $param['id'] != 0 ) {
            return TP_Publications::get_publication($param['id'], ARRAY_A);
        }
        else {
            return $tp_single_publication;
        }
    }

    /**
     * Sets the colspan for the rows of publication list headlines
     * @param array $settings
     * @return string
     * @since 7.0.0
     */
    public static function set_colspan ($settings) {
        $count = 1;

        // if there is a numbered style
        if ( $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            $count++;
        }

        // if there is an image left or right
        if ( $settings['image']== 'left' || $settings['image']== 'right' ) {
            $count++;
        }

        // if there is an altmetric donut
        if ( $settings['show_altmetric_donut']  ) {
            $count++;
        }

        if ( $settings['show_dimensions_badge'] ) {
            $count++;
        }

        if ( $settings['show_plumx_widget'] ) {
            $count++;
        }

        if ( $count < 2 ) {
            return '';
        }
        return ' colspan="' . $count . '"';
    }

    /**
     * Utility function that returns the WordPress userid from either a numerical
     * id (in which case, it is returned as is) or from the login name (e.g. admin), in
     * which case the function returns the corresponding userid if found.
     * @param string $userid
     * @return int The user id, or 0 if user not found.
     * @since 9.0.0
     */
    public static function get_wordpress_user_id($userid) {
        $result = 0;

        $param_type = gettype($userid);

        if ($param_type == "integer" ||
            $param_type == "string" &&  is_numeric($userid)) {

            $wp_user = get_user_by("id", $userid); // validate that id exists
            if ($wp_user !== false) {
                $result = $wp_user->ID;
            }
        } else if ($param_type == "string") {
            $wp_user = get_user_by(trim("login"), $userid); // validate that id exists
            if ($wp_user !== false) {
                $result = $wp_user->ID;
            }
        }

        return $result;
    }

    /**
     * Converts a user filter to the proper format. If user logins (WordPress user names)
     * are detected in the filter, they are converted to the matching user ids.
     * @param string $user_filter - May be name1,32,name2 or 32 or name1, name2
     * @return string A filter string containing only valid userids, or empty string.
     * @since 9.0.0
     */
    public static function get_wordpress_user_id_filter($user_filter) {
        $result = '';
        $valid_ids = array();

        $parts = explode(",", trim($user_filter));
        foreach ($parts as $current_part) {
            $valid_id = TP_Shortcodes::get_wordpress_user_id($current_part);
            if ($valid_id != 0) {
                $valid_ids[] = strval($valid_id);
            }
        }

        $result = implode(",", $valid_ids);
        return $result;
    }
}

/**
 * Prints a citation link
 *
 * @param array $atts {
 *      @type int id        ID of the publication
 *      @type string key    BibTeX key of a publication
 * }
 * @return string
 * @since 6.0.0
 */
function tp_cite_shortcode ($atts) {
    global $tp_cite_object;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => ''
    ), $atts);

    // Load cite object
    if ( !isset($tp_cite_object) ) {
        $tp_cite_object = new TP_Cite_Object;
    }

    // Check parameter
    if ( $param['key'] != '' ) {
        $publication = TP_Publications::get_publication_by_key($param['key'], ARRAY_A);
    }
    else {
        $publication = TP_Publications::get_publication($param['id'], ARRAY_A);
    }

    // Add ref to cite object
    $index = $tp_cite_object->add_ref($publication);

    // Return
    return '<sup><a href="#tp_cite_' . $publication['pub_id'] . '">[' . $index . ']</a></sup>';
}

/**
 * Prints the references

 * @param array $atts {
 *      @type string author_name        last, initials or old, default: simple
 *      @type string editor_name        last, initials or old, default: initials
 *      @type string author_separator   The separator for author names, default: ;
 *      @type string editor_separator   The separator for editor names, default: ;
 *      @type string date_format        The format for date; needed for the types: presentations, online; default: d.m.Y
 *      @type int show_links            0 (false) or 1 (true), default: 0
 * }
 * @return string
 * @since 6.0.0
 */
function tp_ref_shortcode($atts) {
    global $tp_cite_object;

    // shortcode parameter defaults
    $param = shortcode_atts(array(
       'author_name'        => 'simple',
       'editor_name'        => 'initials',
       'author_separator'   => ',',
       'editor_separator'   => ';',
       'date_format'        => 'd.m.Y',
       'show_links'         => 0
    ), $atts);

    // define settings
    $settings = array(
       'author_name'        => htmlspecialchars($param['author_name']),
       'editor_name'        => htmlspecialchars($param['editor_name']),
       'author_separator'   => htmlspecialchars($param['author_separator']),
       'editor_separator'   => htmlspecialchars($param['editor_separator']),
       'date_format'        => htmlspecialchars($param['date_format']),
       'style'              => 'simple',
       'title_ref'          => 'links',
       'link_style'         => ($param['show_links'] == 1) ? 'direct' : 'none',
       'meta_label_in'      => __('In','teachpress') . ': ',
       'use_span'           => false
    );

    // define reference part
    $references = isset($tp_cite_object) ? $tp_cite_object->get_ref() : array();

    // If there is no reference to show
    if ( empty($references) ) {
        return;
    }

    $ret = '<h3 class="teachpress_ref_headline">' . __('References','teachpress') . '</h3>';
    $ret .= '<ol>';
    foreach ( $references as $row ) {
        $ret .= '<li id="tp_cite_' . $row['pub_id'] . '" class="tp_cite_entry"><span class="tp_single_author">' . stripslashes($row['author']) . '</span><span class="tp_single_year"> (' . $row['year'] . ')</span>: <span class="tp_single_title">' . TP_HTML_Publication_Template::prepare_publication_title($row, $settings, 1) . '</span>. <span class="tp_single_additional">' . TP_HTML_Publication_Template::get_publication_meta_row($row, $settings) . '</span></li>';
    }
    $ret .= '</ol>';
    return $ret;
}

/**
 * Shortcode for a single publication
 *
 * @param array $atts {
 *      @type int id                    id of a publication
 *      @type string key                bibtex key of a publication
 *      @type string author_name        last, initials or old, default: simple
 *      @type string editor_name        last, initials or old, default: simple
 *      @type string author_separator   The separator for author names, default: ;
 *      @type string editor_separator   The separator for editor names, default: ;
 *      @type string date_format        The format for date; needed for the types: presentations, online; default: d.m.Y
 *      @type string image              none, left or right; default: none
 *      @type string image_size         image width in px; default: 0
 *      @type string meta_label_in      Default: __('In','teachpress') . ': '
 *      @type string link               Set it to "true" if you want to show a link in addition of the publication title. If there are more than one link, the first one is used.
 * }
 * @return string
 * @since 2.0.0
 */
function tp_single_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id'                 => 0,
       'key'                => '',
       'author_name'        => 'simple',
       'author_separator'   => ',',
       'editor_separator'   => ';',
       'editor_name'        => 'last',
       'date_format'        => 'd.m.Y',
       'image'              => 'none',
       'image_size'         => 0,
       'meta_label_in'      => __('In','teachpress') . ': ',
       'link'               => ''
    ), $atts);

    $settings = array(
       'author_name'        => htmlspecialchars($param['author_name']),
       'editor_name'        => htmlspecialchars($param['editor_name']),
       'author_separator'   => htmlspecialchars($param['author_separator']),
       'editor_separator'   => htmlspecialchars($param['editor_separator']),
       'date_format'        => htmlspecialchars($param['date_format']),
       'style'              => 'simple',
       'meta_label_in'      => htmlspecialchars($param['meta_label_in']),
       'use_span'           => true
    );

    // Set publication
    if ( $param['key'] != '' ) {
        $publication = TP_Publications::get_publication_by_key($param['key'], ARRAY_A);
    }
    else {
        $publication = TP_Publications::get_publication($param['id'], ARRAY_A);
    }
    $tp_single_publication = $publication;

    // Set author name
    if ( $publication['type'] === 'collection' || $publication['type'] === 'periodical' || ( $publication['author'] === '' && $publication['editor'] !== '' ) ) {
        $author = TP_Bibtex::parse_author($publication['editor'], $settings['author_separator'], $settings['editor_name'] ) . ' (' . __('Ed.','teachpress') . ')';
    }
    else {
        $author = TP_Bibtex::parse_author($publication['author'], $settings['author_separator'], $settings['author_name'] );
    }

    $image_size = intval($param['image_size']);

    $asg = '<div class="tp_single_publication">';

    // add image
    if ( ( $param['image'] === 'left' || $param['image'] === 'right' ) && $publication['image_url'] != '' ) {
        $class = ( $param['image'] === 'left' ) ? 'tp_single_image_left' : 'tp_single_image_right';
        $asg .= '<div class="' . $class . '"><img name="' . $publication['title'] . '" src="' . $publication['image_url'] . '" width="' . $image_size .'" alt="" /></div>';
    }

    // define title
    if ( $param['link'] !== '' && $publication['url'] !== '' ) {
        // Use the first link in url field without the original title
        $url = explode(chr(13) . chr(10), $publication['url']);
        $parts = explode(', ',$url[0]);
        $parts[0] = trim( $parts[0] );
        $title = '<a href="' . $parts[0] . '">' . TP_HTML::prepare_title($publication['title'], 'decode') . '</a>';
    }
    else {
        $title = TP_HTML::prepare_title($publication['title'], 'decode');
    }
    $asg .= '<span class="tp_single_author">' . stripslashes($author) . ': </span> <span class="tp_single_title">' . $title . '</span>. <span class="tp_single_additional">' . TP_HTML_Publication_Template::get_publication_meta_row($publication, $settings) . '</span>';
    $asg .= '</div>';
    return $asg;
}

/**
 * Shortcode for displaying the BibTeX code of a single publication
 *
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 *
 * @param array $atts {
 *      @type int id        id of a publication
 *      @type string key    bibtex key of a publication
 * }
 * @return string
 * @since 4.2.0
 */
function tp_bibtex_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);

    $convert_bibtex = ( get_tp_option('convert_bibtex') == '1' ) ? true : false;
    $publication = TP_Shortcodes::set_publication($param, $tp_single_publication);

    $tags = TP_Tags::get_tags( array('pub_id' => $publication['pub_id'], 'output_type' => ARRAY_A) );

    return '<h2 class="tp_bibtex">BibTeX (<a href="' . home_url() . '?feed=tp_pub_bibtex&amp;key=' . $publication['bibtex'] . '">Download</a>)</h2><pre class="tp_bibtex">' . TP_Bibtex::get_single_publication_bibtex($publication, $tags, $convert_bibtex) . '</pre>';
}

/**
 * Shortcode for displaying the abstract of a single publication
 *
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 *
 * @param array $atts {
 *      @type int id        id of a publication
 *      @type string key    bibtex key of a publication
 * }
 * @return string
 * @since 4.2.0
 */
function tp_abstract_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);

    $publication = TP_Shortcodes::set_publication($param, $tp_single_publication);

    if ( isset($publication['abstract']) ) {
        return '<h2 class="tp_abstract">' . __('Abstract','teachpress') . '</h2><p class="tp_abstract">' . TP_HTML::prepare_text($publication['abstract']) . '</p>';
    }
    return;
}

/**
 * Shortcode for displaying the related websites (url) of a publication
 *
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 *
 * @param array $atts {
 *      @type int id        id of a publication
 *      @type string key    bibtex key of a publication
 * }
 * @return string
 * @scine 4.2.0
 */
function tp_links_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);

    $publication = TP_Shortcodes::set_publication($param, $tp_single_publication);

    if ( isset($publication['url']) ) {
        return '<h2 class="tp_links">' . __('Links','teachpress') . '</h2><p class="tp_abstract">' . TP_HTML_Publication_Template::prepare_url($publication['url'], $publication['doi'], 'list') . '</p>';
    }
    return;
}

/**
 * General interface for [tpcloud], [tplist] and [tpsearch]
 *
 * Parameters from $_GET:
 *      $yr (INT)               Year
 *      $type (STRING)          Publication type
 *      $auth (INT)             Author ID
 *      $tgid (INT)             Tag ID
 *      $usr (INT)              User ID
 *      $tsr (STRING)           Full text search
 *
 *
 * @param array $atts {
 *      @type string user                  the WordPress IDs or login names of on or more users (separated by commas)
 *      @type string tag                   tag IDs (separated by comma)
 *      @type string type                  the publication types you want to show (separated by comma)
 *      @type string author                author IDs (separated by comma)
 *      @type string year                  one or more years (separated by comma)
 *      @type string exclude               one or more IDs of publications you don't want to show (separated by comma)
 *      @type string include               one or more IDs of publications you want to show (separated by comma)
 *      @type string include_editor_as_author  0 (false) or 1 (true), default: 1
 *      @type string order                 title, year, bibtex or type, default: date DESC
 *      @type int headline                 show headlines with years(1), with publication types(2), with years and types (3), with types and years (4) or not(0), default: 1
 *      @type int maxsinze                 maximal font size for the tag cloud, default: 35
 *      @type int minsize                  minimal font size for the tag cloud, default: 11
 *      @type int tag_limit                number of tags, default: 30
 *      @type string hide_tags             ids of the tags you want to hide from your users (separated by comma)
 *      @type string exclude_tags          similar to hide_tags but with influence on publications; if exclude_tags is defined hide_tags will be ignored
 *      @type string exclude_types         name of the publication types you want to exclude (separated by comma)
 *      @type string image                 none, left, right or bottom, default: none
 *      @type int image_size               max. Image size, default: 0
 *      @type string image_link            none, self, rel_page or external (defalt: none)
 *      @type string author_name           Author name style options: simple, last, initials, short or old, default: initials
 *      @type string editor_name           Editor name style options: simple, last, initials, short or old, default: initials
 *      @type string author_separator      The separator for author names
 *      @type string editor_separator      The separator for author names
 *      @type string style                 List style options: numbered, numbered_desc or none, default: none
 *      @type string template              The key of the used template, default: tp_template_2021
 *      @type string title_ref             Defines the target for the title link. Options: links or abstract, default: links
 *      @type string link_style            Defines the style of the publication links. Options: inline, direct or images, default: inline
 *      @type string date_format           The format for date, needed for the types: presentations, online; default: d.m.Y
 *      @type int pagination               Activates pagination (1) or not (0), default: 1
 *      @type int entries_per_page         Number of publications per page (pagination must be set to 1), default: 50
 *      @type string sort_list             A list of publication types (separated by comma) which overwrites the default sort order for headline = 2
 *      @type string show_tags_as          Style option for the tags: cloud, pulldown, plain or none, default: cloud
 *      @type int show_author_filter       0 (false) or 1 (true), default: 1
 *      @type string show_in_author_filter Can be used to manage the visisble authors in the author filter. Uses the author IDs (separated by comma)
 *      @type int show_type_filter         0 (false) or 1 (true), default: 1
 *      @type int show_user_filter         0 (false) or 1 (true), default: 1
 *      @type int show_search_filter       0 (false) or 1 (true), default: 1
 *      @type int show_year_filter         0 (false) or 1 (true), default: 1
 *      @type int show_bibtex              Show bibtex container under each entry (1) or not (0), default: 1
 *      @type int show_comment             Show comment as a container, default: 0
 *      @type string comment_text          Set the text used for the comment link, if shown. Default: 'Comment'
 *      @type string comment_tooltip       Set the tooltip text for the comment link, if shown. Default: 'Show comment'
 *      @type string container_suffix      a suffix which can optionally set to modify container IDs in publication lists. It's not set by default.
 *      @type string filter_class          The CSS class for filter/select menus, default: default
 *      @type int show_altmetric_donut     0 (false) or 1 (true), default: 0
 *      @type int show_altmetric_entrx     0 (false) or 1 (true), default: 0
 *      @type int show_dimensions_badge    0 (false) or 1 (true), default: 0
 *      @type int show_plumx_widget        0 (false) or 1 (true), default: 0
 *      @type int use_jumpmenu             Use filter as jumpmenu (1) or not (0), default: 1
 *      @type int use_as_filter            Show all entries by default (1) o not (0), default 1
 * }
 * @return string
 * @since 7.0.0
 */
function tp_publist_shortcode ($args) {
    $atts = shortcode_atts(array(
        'user'                  => '',
        'tag'                   => '',
        'tag_name'              => '',
        'type'                  => '',
        'author'                => '',
        'year'                  => '',
        'years_between'         => '',
        'exclude'               => '',
        'include'               => '',
        'include_editor_as_author' => 1,
        'order'                 => 'date DESC',
        'headline'              => 1,
        'maxsize'               => 35,
        'minsize'               => 11,
        'tag_limit'             => 30,
        'hide_tags'             => '',
        'exclude_tags'          => '',
        'exclude_types'         => '',
        'image'                 => 'none',
        'image_size'            => 0,
        'image_link'            => 'none',
        'anchor'                => 1,
        'author_name'           => 'initials',
        'editor_name'           => 'initials',
        'author_separator'      => ';',
        'editor_separator'      => ';',
        'style'                 => 'none',
        'template'              => 'tp_template_2021',
        'title_ref'             => 'links',
        'link_style'            => 'inline',
        'date_format'           => 'd.m.Y',
        'pagination'            => 1,
        'entries_per_page'      => 50,
        'sort_list'             => '',
        'show_tags_as'          => 'cloud',
        'show_author_filter'    => 1,
        'show_in_author_filter' => '',
        'show_type_filter'      => 1,
        'show_user_filter'      => 1,
        'show_search_filter'    => 1,
        'show_year_filter'      => 1,
        'show_bibtex'           => 1,
        'show_comment'          => 0,
        'comment_text'          => 'Comment',
        'comment_tooltip'       => 'Show comment',
        'container_suffix'      => '',
        'filter_class'          => 'default',
        'custom_filter'         => '',
        'custom_filter_label'   => '',
        'show_altmetric_donut'  => 0,
        'show_altmetric_entry'  => 0,
        'show_dimensions_badge' => 0,
        'show_plumx_widget'     => 0,
        'use_jumpmenu'          => 1,
        'use_as_filter'         => 1
    ), $args);

    $settings = array(
        'author_name'           => htmlspecialchars($atts['author_name']),
        'editor_name'           => htmlspecialchars($atts['editor_name']),
        'author_separator'      => htmlspecialchars($atts['author_separator']),
        'editor_separator'      => htmlspecialchars($atts['editor_separator']),
        'headline'              => intval($atts['headline']),
        'style'                 => htmlspecialchars($atts['style']),
        'template'              => htmlspecialchars($atts['template']),
        'image'                 => htmlspecialchars($atts['image']),
        'image_link'            => htmlspecialchars($atts['image_link']),
        'link_style'            => htmlspecialchars($atts['link_style']),
        'title_ref'             => htmlspecialchars($atts['title_ref']),
        'html_anchor'           => ( $atts['anchor'] == '1' ) ? '#tppubs' . htmlspecialchars($atts['container_suffix']) : '',
        'date_format'           => htmlspecialchars($atts['date_format']),
        'permalink'             => ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;",
        'convert_bibtex'        => ( get_tp_option('convert_bibtex') == '1' ) ? true : false,
        'pagination'            => intval($atts['pagination']),
        'entries_per_page'      => intval($atts['entries_per_page']),
        'sort_list'             => htmlspecialchars($atts['sort_list']),
        'show_author_filter'    => ( $atts['show_author_filter'] == '1' ) ? true : false,
        'show_type_filter'      => ( $atts['show_type_filter'] == '1' ) ? true : false,
        'show_user_filter'      => ( $atts['show_user_filter'] == '1' ) ? true : false,
        'show_year_filter'      => ( $atts['show_year_filter'] == '1' ) ? true : false,
        'show_search_filter'    => ( $atts['show_search_filter'] == '1' ) ? true : false,
        'show_bibtex'           => ( $atts['show_bibtex'] == '1' ) ? true : false,
        'show_comment'          => ( $atts['show_comment'] == '1') ? true : false,
        'comment_text'          => htmlspecialchars($atts['comment_text']),
        'comment_tooltip'       => htmlspecialchars($atts['comment_tooltip']),
        'show_tags_as'          => htmlspecialchars($atts['show_tags_as']),
        'tag_limit'             => intval($atts['tag_limit']),
        'hide_tags'             => htmlspecialchars($atts['hide_tags']),
        'maxsize'               => intval($atts['maxsize']),
        'minsize'               => intval($atts['minsize']),
        'container_suffix'      => htmlspecialchars($atts['container_suffix']),
        'filter_class'          => htmlspecialchars($atts['filter_class']),
        'custom_filter'         => htmlspecialchars($atts['custom_filter']),
        'custom_filter_label'   => htmlspecialchars($atts['custom_filter_label']),
        'show_altmetric_entry'  => ($atts['show_altmetric_entry'] == '1') ? true : false,
        'show_altmetric_donut'  => ($atts['show_altmetric_donut'] == '1') ? true : false,
        'show_dimensions_badge' => ('1' === $atts['show_dimensions_badge']) ? true : false,
        'show_plumx_widget'     => ('1' === $atts['show_plumx_widget']) ? true : false,
        'use_jumpmenu'          => ( $atts['use_jumpmenu'] == '1' ) ? true : false
    );

    // Settings for and from form fields
    $filter_parameter = array(
        'tag'                   => ( isset ($_GET['tgid']) && $_GET['tgid'] != '' ) ? tp_convert_input_to_string($_GET['tgid'], 'int') : '',
        'tag_name'              => isset ($_GET['tgname']) ? htmlspecialchars( $_GET['tgname'] ) : '',
        'year'                  => ( isset ($_GET['yr']) && $_GET['yr'] != '' ) ? intval($_GET['yr']) : '',
        'type'                  => isset ($_GET['type']) ? htmlspecialchars( $_GET['type'] ) : '',
        'author'                => ( isset ($_GET['auth']) && $_GET['auth'] != '' ) ? intval($_GET['auth']) : '',
        'user'                  => ( isset ($_GET['usr']) && $_GET['usr'] != '' ) ? intval($_GET['usr']) : '',
        'search'                => isset ($_GET['tsr']) ? htmlspecialchars( $_GET['tsr'] ) : '',
        'show_in_author_filter' => htmlspecialchars($atts['show_in_author_filter']),
        'tag_preselect'         => htmlspecialchars($atts['tag']),
        'year_preselect'        => htmlspecialchars($atts['year']),
        'author_preselect'      => htmlspecialchars($atts['author']),
        'type_preselect'        => htmlspecialchars($atts['type']),
        'user_preselect'        => htmlspecialchars($atts['user']),
    );

    /*
     * Settings for data selection
     *
     * Default values are from the shortcode parameters
     * Can be overwritten with filter_parameter
     */
    $sql_parameter = array (
        'user'          => ( $filter_parameter['user'] !== '' ) ? $filter_parameter['user'] : htmlspecialchars($atts['user']),
        'type'          => ( $filter_parameter['type'] !== '' ) ? $filter_parameter['type'] : htmlspecialchars($atts['type']),
        'author'        => ( $filter_parameter['author'] !== '' ) ? $filter_parameter['author'] : htmlspecialchars($atts['author']),
        'year'          => ( $filter_parameter['year'] !== '' ) ? $filter_parameter['year'] : htmlspecialchars($atts['year']),
        'tag'           => ( $filter_parameter['tag'] !== '' ) ? $filter_parameter['tag'] : htmlspecialchars($atts['tag']),
        'tag_name'      => ( $filter_parameter['tag_name'] !== '' ) ? $filter_parameter['tag_name'] : htmlspecialchars($atts['tag_name']),
        'years_between' => htmlspecialchars($atts['years_between']),
        'exclude'       => htmlspecialchars($atts['exclude']),
        'exclude_tags'  => htmlspecialchars($atts['exclude_tags']),
        'exclude_types' => htmlspecialchars($atts['exclude_types']),
        'order'         => htmlspecialchars($atts['order']),
    );

    // convert possible logins into user ids
    $sql_parameter['user'] = TP_Shortcodes::get_wordpress_user_id_filter($sql_parameter['user']);
    $filter_parameter['user_preselect'] = TP_Shortcodes::get_wordpress_user_id_filter($filter_parameter['user_preselect']);

    // Add values for custom filters
    $meta_key_search = [];
    if ( $settings['custom_filter'] !== '' ) {
        $custom_fields = explode(',', $settings['custom_filter']);
        foreach ( $custom_fields as $field ) {
            $filter_parameter[ $field ] = isset ($_GET[$field]) ? htmlspecialchars( $_GET[$field] ) : '';
            $sql_parameter[ $field ] = isset ($_GET[$field]) ? htmlspecialchars( $_GET[$field] ) : '';
            $meta_key_search[$field] = isset ($_GET[$field]) ? htmlspecialchars( $_GET[$field] ) : '';
        }
    }

    // Handle limits for pagination
    $form_limit = ( isset($_GET['limit']) ) ? intval($_GET['limit']) : '';
    $pagination_limits = TP_Shortcodes::generate_pagination_limits($settings['pagination'], $settings['entries_per_page'], $form_limit);

    // ignore hide_tags if exclude_tags is given
    if ( $sql_parameter['exclude_tags'] != '' ) {
        $atts['hide_tags'] = $sql_parameter['exclude_tags'];
    }

    /*************/
    /* Tag cloud */
    /*************/
    $tag_cloud = '';
    if ( $settings['show_tags_as'] === 'cloud' ) {
        $tag_cloud = TP_Shortcodes::generate_tag_cloud($atts['user'], $filter_parameter, $sql_parameter, $settings);
    }

    /**********/
    /* Filter */
    /**********/
    $filter = '';

    // Filter year
    if ( ( $atts['year'] == '' || strpos($atts['year'], ',') !== false ) &&
            $settings['show_year_filter'] === true ) {
        $filter .= TP_Shortcodes::generate_filter('year', $filter_parameter, $sql_parameter, $settings, 2);
    }

    // Filter type
    if ( ( $atts['type'] == '' || strpos($atts['type'], ',') !== false ) &&
            $settings['show_type_filter'] === true ) {
        $filter .= TP_Shortcodes::generate_filter('type', $filter_parameter, $sql_parameter, $settings, 3);
    }

    // Filter tag
    if ( $settings['show_tags_as'] === 'pulldown' ) {
        $filter .= TP_Shortcodes::generate_filter('tag', $filter_parameter, $sql_parameter, $settings, 4);
    }

    // Filter author
    if ( ( $atts['author'] == '' || strpos($atts['author'], ',') !== false ) &&
            $settings['show_author_filter'] === true ) {
        $filter .= TP_Shortcodes::generate_filter('author', $filter_parameter, $sql_parameter, $settings, 5);
    }

    // Filter user
    if ( ( $atts['user'] == '' || strpos($atts['user'], ',') !== false ) &&
            $settings['show_user_filter'] === true ) {
        $filter .= TP_Shortcodes::generate_filter('user', $filter_parameter, $sql_parameter, $settings, 6);
    }

    // Custom filters
    if ( $settings['custom_filter'] !== '' ) {
        $custom_filter = TP_Shortcodes::generate_custom_filter($settings, $filter_parameter, 7);
        $filter .= $custom_filter;
    }

    // Show all link
    $showall = TP_Shortcodes::generate_show_all_link($atts, $filter_parameter, $settings);

    /****************/
    /* Search Field */
    /****************/

    $searchbox = '';
    $search_button = '<div class="teachpress_search_button"><input name="tps_button" class="tp_search_button" type="submit" tabindex="10" value="' . __('Search', 'teachpress') . '"/></div>';

    if ( $settings['show_search_filter'] === true ) {
        if ( !get_option('permalink_structure') ) {
            $searchbox .= '<input type="hidden" name="p" id="page_id" value="' . get_the_id() . '"/>';
        }

        $searchbox .= '<input name="tsr" id="tp_search_input_field" type="search" placeholder="' . __('Enter search word','teachpress') .'" value="' . stripslashes($filter_parameter['search']) . '" tabindex="1"/>';

        $searchbox .= ( $filter === '' ) ? $search_button  : '';
        $filter .= ( $filter !== '' ) ? $search_button : '';

    }

    /***********************/
    /* Complete the header */
    /***********************/

    $part1 = '';

    // anchor
    $part1 .= '<a name="tppubs" id="tppubs"' . $settings['container_suffix'] . '></a>';

    // tag cloud
    if ( $tag_cloud !== '' ) {
        $part1 .= '<div class="teachpress_cloud">' . $tag_cloud . '</div>';
    }

    // search
    if ( $searchbox !== '' ) {
        $part1 .= '<div class="tp_search_input">' . $searchbox . '</div>';
    }

    // filter
    if ( $filter !== '' ) {
        $part1 .= '<div class="teachpress_filter">' . $filter . '</div>';
    }

    // show all button
    if ( $showall !== '' ) {
        $part1 .= '<p style="text-align:center">' . $showall . '</p>';
    }

    // Form
    $part1 = '<form name="tppublistform" method="get">' . $part1 . '</form>';

    // Return if we don't want so display the publications fo default
    if ( intval($atts['use_as_filter']) === 0 && $showall === '' ) {
        return '<div class="teachpress_pub_list">' . $part1 . '</div>';
    }
    /************************/
    /* List of publications */
    /************************/

    // change the id
    if ( $filter_parameter['user'] != 0) {
        $atts['user'] = $filter_parameter['user'];
    }

    // Handle headline/order settings
    if ( $settings['headline'] === 2 ) {
        $sql_parameter['order'] = "type ASC, date DESC";
    }
    if ( $settings['headline'] === 3 || $settings['headline'] === 4 ) {
        $sql_parameter['order'] = "year DESC, type ASC, date DESC";
    }

    // Parameters for returning publications
    $args = array(
        'tag'                       => $sql_parameter['tag'],
        'tag_name'                  => $sql_parameter['tag_name'], 
        'year'                      => $sql_parameter['year'],
        'years_between'             => $sql_parameter['years_between'],
        'type'                      => $sql_parameter['type'],
        'user'                      => $sql_parameter['user'],
        'search'                    => $filter_parameter['search'],
        'author_id'                 => $sql_parameter['author'],
        'order'                     => $sql_parameter['order'],
        'exclude'                   => $sql_parameter['exclude'],
        'exclude_tags'              => $sql_parameter['exclude_tags'],
        'exclude_types'             => $sql_parameter['exclude_types'],
        'include'                   => $atts['include'],
        'include_editor_as_author'  => ($atts['include_editor_as_author'] == 1) ? true : false,
        'limit'                     => $pagination_limits['limit'],
        'meta_key_search'           => $meta_key_search,
        'output_type'               => ARRAY_A);

    $all_tags = TP_Tags::get_tags( array('exclude' => $atts['hide_tags'], 'output_type' => ARRAY_A) );
    $number_entries = TP_Publications::get_publications($args, true);
    $row = TP_Publications::get_publications( $args );
    $tpz = 0;
    $count = count($row);
    $tparray = array();

    // colspan setup
    $colspan = TP_Shortcodes::set_colspan($settings);
    if ($settings['image'] == 'left' || $settings['image'] == 'right' || $settings['show_altmetric_donut'] == true || true === $settings['show_dimensions_badge'] || true === $settings['show_plumx_widget']) {
        $settings['pad_size'] = intval($atts['image_size']) + 5;
    }

    // Load template
    $template = tp_load_template($settings['template']);
    if ( $template === false ) {
        $template = tp_load_template('tp_template_2021');
    }
    $template_settings = TP_HTML_Publication_Template::load_settings($template);

    // Create array of publications
    foreach ($row as $row) {
        $number = TP_HTML_Publication_Template::prepare_publication_number($number_entries, $tpz, $pagination_limits['entry_limit'], $atts['style']);
        $tparray[$tpz][0] = $row['year'] ;

        // teachPress style
        $tparray[$tpz][1] = TP_HTML_Publication_Template::get_single($row, $all_tags, $settings, $template, $template_settings, $number);

        if ( 2 <= $settings['headline'] && $settings['headline'] <= 4 ) {
            $tparray[$tpz][2] = $row['type'] ;
        }
        $tpz++;
    }

    // Sort the array
    // If there are publications
    if ( $tpz != 0 ) {
        $part2 = '';
        $link_attributes = 'tgid=' . $filter_parameter['tag'] . '&amp;yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . '&amp;tsr=' . $filter_parameter['search'] . $settings['html_anchor'];

        // Define page menu
        $menu = '';
        if ( $settings['pagination'] === 1 ) {
            $menu = tp_page_menu(
                        array( 'number_entries'     => $number_entries,
                               'entries_per_page'   => $settings['entries_per_page'],
                               'current_page'       => $pagination_limits['current_page'],
                               'entry_limit'        => $pagination_limits['entry_limit'],
                               'page_link'          => $settings['permalink'],
                               'link_attributes'    => $link_attributes,
                               'mode'               => 'bottom',
                               'before'             => '<div class="tablenav">',
                               'after'              => '</div>')
                    );
        }


        $part2 .= $menu;
        $row_year = TP_Publications::get_years(
                        array( 'user'               => $sql_parameter['user'],
                               'type'               => $sql_parameter['type'],
                               'order'              => 'DESC',
                               'output_type'        => ARRAY_A ) );

        $part2 .= TP_Shortcodes::generate_pub_table(
                        $tparray,
                        $template,
                        array( 'id'                     => '',
                               'number_publications'    => $tpz,
                               'headline'               => $settings['headline'],
                               'years'                  => $row_year,
                               'colspan'                => $colspan,
                               'user'                   => $atts['user'],
                               'sort_list'              => $settings['sort_list'] ) );
        $part2 .= $menu;
    }
    // If there are no publications founded
    else {
        $part2 = '<div class="teachpress_message_error"><p>' . __('Sorry, no publications matched your criteria.','teachpress') . '</p></div>';
    }

    // For debugging only:
    // print_r($pagination_limits);
    // print_r($settings);
    // print_r($filter_parameter);

    // Return
    return '<div class="teachpress_pub_list">' . $part1 . $part2 . '</div>';
}

/**
 * Shortcode for displaying a publication list with tag cloud
 * This is just a preset for tp_publist_shortcode()
 *
 * Parameters from $_GET:
 *      $yr (INT)              Year
 *      $type (STRING)         Publication type
 *      $auth (INT)            Author ID
 *      $tgid (INT)            Tag ID
 *      $usr (INT)             User ID
 *
 * @param array $atts
 * @return string
 * @since 0.10.0
 */
function tp_cloud_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user'                      => '',
        'tag'                       => '',
        'tag_name'                  => '',
        'type'                      => '',
        'author'                    => '',
        'year'                      => '',
        'years_between'             => '',
        'exclude'                   => '',
        'include'                   => '',
        'include_editor_as_author'  => 1,
        'order'                     => 'date DESC',
        'headline'                  => 1,
        'maxsize'                   => 35,
        'minsize'                   => 11,
        'tag_limit'                 => 30,
        'hide_tags'                 => '',
        'exclude_tags'              => '',
        'exclude_types'             => '',
        'image'                     => 'none',
        'image_size'                => 0,
        'image_link'                => 'none',
        'anchor'                    => 1,
        'author_name'               => 'initials',
        'editor_name'               => 'initials',
        'author_separator'          => ';',
        'editor_separator'          => ';',
        'style'                     => 'none',
        'template'                  => 'tp_template_2021',
        'title_ref'                 => 'links',
        'link_style'                => 'inline',
        'date_format'               => 'd.m.Y',
        'pagination'                => 1,
        'entries_per_page'          => 50,
        'sort_list'                 => '',
        'show_tags_as'              => 'cloud',
        'show_author_filter'        => 1,
        'show_in_author_filter'     => '',
        'show_type_filter'          => 1,
        'show_user_filter'          => 1,
        'show_search_filter'        => 0,
        'show_year_filter'          => 1,
        'show_bibtex'               => 1,
        'show_comment'              => 0,
        'comment_text'              => 'Comment',
        'comment_tooltip'           => 'Show comment',
        'container_suffix'          => '',
        'show_altmetric_donut'      => 0,
        'show_altmetric_entry'      => 0,
        'show_dimensions_badge'     => 0,
        'show_plumx_widget'         => 0,
        'use_jumpmenu'              => 1,
        'use_as_filter'             => 1,
        'filter_class'              => 'default',
        'custom_filter'             => '',
        'custom_filter_label'       => '',
    ), $atts);

    return tp_publist_shortcode($atts);

}

/**
 * Shortcode for displaying a publication list without filters
 * This is just a preset for tp_publist_shortcode()
 *
 * @param array $atts
 * @return string
 * @since 0.12.0
 */
function tp_list_shortcode($atts){
    $atts = shortcode_atts(array(
       'user'                       => '',
       'tag'                        => '',
       'tag_name'                   => '',
       'type'                       => '',
       'author'                     => '',
       'year'                       => '',
       'years_between'              => '',
       'exclude'                    => '',
       'include'                    => '',
       'include_editor_as_author'   => 1,
       'exclude_tags'               => '',
       'exclude_types'              => '',
       'order'                      => 'date DESC',
       'headline'                   => 1,
       'image'                      => 'none',
       'image_size'                 => 0,
       'image_link'                 => 'none',
       'anchor'                     => 1,
       'author_name'                => 'initials',
       'editor_name'                => 'initials',
       'author_separator'           => ';',
       'editor_separator'           => ';',
       'style'                      => 'none',
       'template'                   => 'tp_template_2021',
       'title_ref'                  => 'links',
       'link_style'                 => 'inline',
       'date_format'                => 'd.m.Y',
       'pagination'                 => 1,
       'entries_per_page'           => 50,
       'sort_list'                  => '',
       'show_bibtex'                => 1,
       'show_comment'               => 0,
       'comment_text'               => 'Comment',
       'comment_tooltip'            => 'Show comment',
       'show_type_filter'           => 0,
       'show_author_filter'         => 0,
       'show_in_author_filter'      => '',
       'show_search_filter'         => 0,
       'show_user_filter'           => 0,
       'show_year_filter'           => 0,
       'show_tags_as'               => 'none',
       'container_suffix'           => '',
       'show_altmetric_donut'       => 0,
       'show_altmetric_entry'       => 0,
       'show_dimensions_badge'      => 0,
       'show_plumx_widget'          => 0,
       'use_jumpmenu'               => 1,
       'use_as_filter'              => 1,
       'filter_class'               => 'default'
    ), $atts);

    return tp_publist_shortcode($atts);
}

/**
 * Shortcode for frontend search function for publications
 * This is just a preset for tp_publist_shortcode()
 *
 * @param array $atts
 * @return string
 * @since 4.0.0
 */
function tp_search_shortcode ($atts) {
    $atts = shortcode_atts(array(
       'user'                       => '',
       'tag'                        => '',
       'tag_name'                   => '',
       'type'                       => '',
       'author'                     => '',
       'year'                       => '',
       'years_between'              => '',
       'exclude'                    => '',
       'include'                    => '',
       'include_editor_as_author'   => 1,
       'order'                      => 'date DESC',
       'headline'                   => 0,
       'exclude_tags'               => '',
       'exclude_types'              => '',
       'image'                      => 'none',
       'image_size'                 => 0,
       'image_link'                 => 'none',
       'anchor'                     => 0,
       'author_name'                => 'initials',
       'editor_name'                => 'initials',
       'author_separator'           => ';',
       'editor_separator'           => ';',
       'style'                      => 'numbered',
       'template'                   => 'tp_template_2021',
       'title_ref'                  => 'links',
       'link_style'                 => 'inline',
       'date_format'                => 'd.m.Y',
       'pagination'                 => 1,
       'entries_per_page'           => 20,
       'sort_list'                  => '',
       'show_bibtex'                => 1,
       'show_comment'               => 0,
       'comment_text'               => 'Comment',
       'comment_tooltip'            => 'Show comment',
       'show_tags_as'               => 'none',
       'show_author_filter'         => 0,
       'show_in_author_filter'      => '',
       'show_type_filter'           => 0,
       'show_user_filter'           => 0,
       'show_search_filter'         => 1,
       'show_year_filter'           => 0,
       'container_suffix'           => '',
       'show_altmetric_donut'       => 0,
       'show_altmetric_entry'       => 0,
       'show_dimensions_badge'      => 0,
       'show_plumx_widget'          => 0,
       'use_jumpmenu'               => 0,
       'use_as_filter'              => 1,
       'filter_class'               => 'block',
       'custom_filter'              => '',
       'custom_filter_label'        => '',
    ), $atts);

    return tp_publist_shortcode($atts);
}
