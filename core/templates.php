<?php
/**
 * Template functions for displaying publications in HTML
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.1
 */

/**
 * Interface for the template classes
 * @since 5.1.0
 */
interface tp_publication_template {
    /**
     * Returns the settings of the template
     * @return array
     * @since 5.1.0
     */
    public function get_settings();
    
    /**
     * Returns the body element for a publication list
     * @param string $content   The content of the publication list itself
     * @param array $args       An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @since 5.1.0
     */
    public function get_body($content, $args);
    
    /**
     * Returns the headline for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @scine 5.1.0
     */
    public function get_headline($content, $args);
    
    /**
     * Returns the headline (second level) for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @since 5.1.0
     */
    public function get_headline_sl($content, $args);
    
    /**
     * Returns the single entry of a publication list
     * @param object $interface     The interface object
     * @return string
     * @since 5.1.0
     */
    public function get_entry($interface);
}

/**
 * Contains all interface functions for publication templates
 * @since 5.1.0
 */
class tp_publication_interface {
    protected $data;
    
    /**
     * Returns the data for a publication row
     * @return array
     * @since 5.1.0
     * @access public
     */
    public function get_data() {
        return $this->data;
    }
    
    /**
     * Sets the data for a publication row
     * @param array $data
     * @since 5.1.0
     * @access public
     */
    public function set_data($data) {
        $this->data = $data;
    }
    
    /**
     * Returns the number for a numbered publication list
     * @param string $before
     * @param string $after
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_number ($before = '', $after = '') {
        $settings = $this->data['settings'];
        
        if ( $settings['style'] === 'std_num' || $settings['style'] === 'std_num_desc' || $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            return $before . $this->data['counter'] . $after;
        }
        
        return '';
    } 
    
    /**
     * Returns the title
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_title () {
        return $this->data['title'];
    }
    
    /**
     * Returns the type of a publication
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_type() {
        $type = $this->data['row']['type'];
        return '<span class="tp_pub_type ' . $type . '">' . tp_translate_pub_type($type) . '</span>';
    }
    
    /**
     * Returns the authors
     * @param string $before
     * @param string $after
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_author ($before = '', $after = '') {
        if ( $this->data['row']['author'] === '' && $this->data['row']['editor'] === '' ) {
            return '';
        }
        return $before . $this->data['all_authors']  . $after;
    }
    
    /**
     * Returns the meta row
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_meta () {
        return tp_html::get_publication_meta_row($this->data['row'], $this->data['settings']);
    }
    
    /**
     * Returns the tags
     * @param string $before
     * @param string $after
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_tag_line ($before = '', $after = '') {
        $tag_string = $this->data['tag_line'];
        $separator = $this->data['template_settings']['button_separator'];
        
        // meta line formatting
        if ( $tag_string !== '' ) {
            $length = mb_strlen($separator);
            $last_chars = mb_substr($tag_string, -$length);
            $tag_string = ( $last_chars === $separator ) ? mb_substr($tag_string, 0, -$length) : $tag_string;
            $tag_string = $before . $tag_string . $after;
        }
        return $tag_string;
    }
    
    /**
     * Returns the year
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_year () {
        return $this->data['row']['year'];
    }
    
    /**
     * Returns the images
     * @param string $position
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_images ($position) {
        if ( $position === 'right' ) {
            return $this->data['images']['right'];
        }
        if ( $position === 'left' ) {
            return $this->data['images']['left'];
        }
        if ( $position === 'bottom' ) {
            return $this->data['images']['bottom'];
        }
    }
    
    /**
     * Returns an info container
     * @return string
     * @since 5.1.0
     * @access public
     */
    public function get_infocontainer () {
        $content = '';
        $row = $this->data['row'];
        $keywords = $this->data['keywords'];
        $settings = $this->data['settings'];
        $container_id = $this->data['container_id'];
        
        // div bibtex
        $content .= tp_html_publication_template::get_info_container( nl2br( tp_bibtex::get_single_publication_bibtex($row, $keywords, $settings['convert_bibtex']) ), 'bibtex', $container_id );
        
        // div abstract
        if ( $row['abstract'] != '' ) {
            $content .= tp_html_publication_template::get_info_container( tp_html::prepare_text($row['abstract']), 'abstract', $container_id );
        }
        
        // div links
        if ( ($row['url'] != '' || $row['doi'] != '') && ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) ) {
            $content .= tp_html_publication_template::get_info_container( tp_html_publication_template::prepare_url($row['url'], $row['doi'], 'list'), 'links', $container_id );
        }
        
        return $content;
    }                      
                        
}

/**
 * This class contains all functions related to the HTML publication template generator
 * @since 5.1.0
 */
class tp_html_publication_template {
    
    /**
     * Gets a single publication in html format
     * @param array $row        The publication array (used keys: title, image_url, ...)
     * @param array $all_tags   Array of tags (used_keys: pub_id, tag_id, name)
     * @param array $settings   Array with all settings (keys: author_name, editor_name, style, image, with_tags, link_style, date_format, convert_bibtex, container_suffix)
     * @param object $template  The template object
     * @param int $pub_count    The counter for numbered publications (default: 0)
     * @return string
     * @since 5.1.0
    */
    public static function get_single ($row, $all_tags, $settings, $template, $pub_count = 0) {
        $container_id = ( $settings['container_suffix'] != '' ) ? $row['pub_id'] . '_' . $settings['container_suffix'] : $row['pub_id'];
        $template_settings = $template->get_settings();
        $separator = $template_settings['button_separator'];
        $name = self::prepare_publication_title($row, $settings, $container_id);
        $images = self::handle_images($row, $settings);
        $abstract = '';
        $url = '';
        $bibtex = '';
        $settings['use_span'] = true;
        $tag_string = '';
        $keywords = '';
        $is_button = false;
        
        // show tags
        if ( $settings['with_tags'] == 1 ) {
            $generated = self::get_tags($row, $all_tags, $settings);
            $keywords = $generated['keywords'];
            $tag_string = __('Tags') . ': ' . $generated['tags'];
        }
        
        // parse author names 
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = tp_bibtex::parse_author($row['editor'], $settings['author_name'] ) . ' (' . __('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = tp_bibtex::parse_author($row['author'], $settings['author_name'] );
        }

        // if is an abstract
        if ( $row['abstract'] != '' ) {
            $abstract = self::get_info_button(__('Abstract','teachpress'), __('Show abstract','teachpress'), 'abstract', $container_id) . $separator;
            $is_button = true;
        }
        
        // if are links
        if ( $row['url'] != '' || $row['doi'] != '' ) {
            if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
                $url = self::get_info_button(__('Links','teachpress'), __('Show links and resources','teachpress'), 'links', $container_id) . $separator;
                $is_button = true;
            }
            else {
                $url = '<span class="tp_resource_link">' . $separator . __('Links','teachpress') . ': ' . self::prepare_url($row['url'], $row['doi'], 'enumeration') . '</span>';
            }
        }
        
        // if with bibtex
        if ( $settings['show_bibtex'] === true ) {
            $bibtex = self::get_info_button(__('BibTeX','teachpress'), __('Show BibTeX entry','teachpress'), 'bibtex', $container_id) . $separator;
            $is_button = true;
        }
        
        // link style
        if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
            $tag_string = $abstract . $url . $bibtex . $tag_string;
        }
        else {
            $tag_string = $abstract . $bibtex . $tag_string . $url;
        }
        
        // load template interface
        $interface_data = array (
            'row' => $row,
            'title' => $name,
            'images' => $images,
            'tag_line' => $tag_string,
            'settings' => $settings,
            'counter' => $pub_count,
            'all_authors' => $all_authors,
            'keywords' => $keywords,
            'container_id' => $container_id,
            'template_settings' => $template_settings
        );
        
        $interface = new tp_publication_interface();
        $interface->set_data($interface_data);
        
        // load entry template
        $s = $template->get_entry($interface);
        return $s;
    }


    /**
     * Returns the show/hide buttons for the info container
     * @param string $name          The name of the button
     * @param string $title         The title/description of the button
     * @param string $type          bibtex, links, abstract
     * @param string $container_id  The suffix for the container ID
     * @return string
     * @since 5.1.0
     */
    public static function get_info_button ($name, $title, $type, $container_id) {
        $class = ( $type === 'links' ) ? 'resource' : $type;
        $s = '<span class="tp_' . $class . '_link"><a id="tp_' . $type . '_sh_' . $container_id . '" class="tp_show" onclick="teachpress_pub_showhide(' . "'" . $container_id . "','tp_" . $type . "'" . ')" title="' . $title . '" style="cursor:pointer;">' . $name . '</a></span>';
        return $s;
    }
    
    /**
     * Returns the info container for a publication
     * @param string $content       The content you want to show
     * @param string $type          bibtex, links, abstract
     * @param string $container_id  The suffix for the container ID
     * @return string
     * @since 5.1.0
     */
    public static function get_info_container ($content, $type, $container_id) {
        $s = '<div class="tp_' . $type . '" id="tp_' . $type . '_' . $container_id . '" style="display:none;">';
        $s .= '<div class="tp_' . $type . '_entry">' . $content . '</div>';
        $s .= '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . "'" . $container_id . "','tp_" . $type . "'" . ')">' . __('Close','teachpress') . '</a></p>';
        $s .= '</div>';
        return $s;
    }
    
    /**
     * This function prepares the publication title for html publication lists.
     * @param array $row                The publication array
     * @param array $settings           Array with all settings (keys: author_name, editor_name, style, image, with_tags, link_style, date_format, convert_bibtex, container_suffix)
     * @param string $container_id      The basic ID for div container
     * @return string
     * @since 5.1.0
     */
    public static function prepare_publication_title ($row, $settings, $container_id) {
        $name = '';
        // transform URL into full HTML link
        if ( $row['rel_page'] != 0 ) {
            $name = '<a href="' . get_permalink($row['rel_page']) . '">' . stripslashes($row['title']) . '</a>';
        }
        // for inline style
        elseif ( $row['url'] != '' && $settings['link_style'] === 'inline' ) {
            $name = '<a class="tp_title_link" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_links' . "'" . ')" style="cursor:pointer;">' . tp_html::prepare_title($row['title'], 'decode') . '</a>';
        }
        // for direct style 
        elseif ( $row['url'] != '' && $settings['link_style'] === 'direct' ) { 
            $parts = self::explode_url($row['url']); 
            $name = '<a class="tp_title_link" href="' . $parts[0][0] . '" title="' . $parts[0][1] . '" target="blank">' . tp_html::prepare_title($row['title'], 'decode') . '</a>'; 
        } 
        else {
            $name = tp_html::prepare_title($row['title'], 'decode');
        }
        return $name;
    }
    
    /**
     * Prepares a url link for publication resources 
     * @param string $url       The url string
     * @param string $doi       The DOI number
     * @param string $mode      list or enumeration
     * @return string
     * @since 3.0.0
     * @version 2
     * @access public
     */
    public static function prepare_url($url, $doi = '', $mode = 'list') {
        $end = '';
        $url = explode(chr(13) . chr(10), $url);
        foreach ($url as $url) {
            if ( $url == '' ) {
                continue;
            }
            $parts = explode(', ',$url);
            $parts[0] = trim( $parts[0] );
            $parts[1] = isset( $parts[1] ) ? $parts[1] : $parts[0];
            // list mode 
            if ( $mode === 'list' ) {
                $length = strlen($parts[1]);
                $parts[1] = substr($parts[1], 0 , 80);
                if ( $length > 80 ) {
                    $parts[1] .= '[...]';
                }
                $end .= '<li><a class="tp_pub_list" style="background-image: url(' . get_tp_mimetype_images( $parts[0] ) . ')" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank">' . $parts[1] . '</a></li>';
            }
            // enumeration mode
            else {
                $end .= '<a class="tp_pub_link" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank"><img class="tp_pub_link_image" alt="" src="' . get_tp_mimetype_images( $parts[0] ) . '"/></a>';
            }
        }
        
        /**
         * Add DOI-URL
         * @since 5.0.0
         */
        if ( $doi != '' ) {
            $doi_url = 'http://dx.doi.org/' . $doi;
            if ( $mode === 'list' ) {
                $end .= '<li><a class="tp_pub_list" style="background-image: url(' . get_tp_mimetype_images( 'html' ) . ')" href="' . $doi_url . '" title="' . __('Follow DOI:','teachpress') . $doi . '" target="_blank">doi:' . $doi . '</a></li>';
            }
            else {
                $end .= '<a class="tp_pub_link" href="' . $doi_url . '" title="' . __('Follow DOI:','teachpress') . $doi . '" target="_blank"><img class="tp_pub_link_image" alt="" src="' . get_tp_mimetype_images( 'html' ) . '"/></a>';
            }
        }
        
        if ( $mode === 'list' ) {
            $end = '<ul class="tp_pub_list">' . $end . '</ul>';
        }
        
        return $end;
    }

    /**
     * Generates the tag string for a single publication
     * @param array $row        The publication array
     * @param array $all_tags   An array of all tags
     * @param type $settings    The settings array
     * @return array Returns an array with tags and keywords
     * @since 5.1.0
     */
    public static function get_tags ($row, $all_tags, $settings) {
        $tag_string = '';
        $keywords = array();
        foreach ($all_tags as $tag) {
            if ($tag["pub_id"] == $row['pub_id']) {
                $keywords[] = array('name' => stripslashes($tag["name"]));
                $tag_string .= '<a href="' . $settings['permalink'] . 'tgid=' . $tag["tag_id"] . $settings['html_anchor'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($tag["name"]) . '</a>, ';
            }
        }
        return array('tags' => substr($tag_string, 0, -2),
                     'keywords' => $keywords);
    }
    
    /**
     * Generates the HTML output for images
     * @param array $row        The publication array
     * @param array $settings   The settings array
     * @return string
     * @since 5.1.0
     */
    public static function handle_images ($row, $settings) {
        $return = array('bottom' => '',
                         'left' => '',
                         'right' => '');
        
        // return if no images is set
        if ( $settings['image'] === 'none' ) {
            return $return;
        }
        
        // define the width of the image
        $width = ( $settings['image'] === 'bottom' ) ? 'style="max-width:' . ($settings['pad_size']  - 5) .'px;"' : 'width="' . ($settings['pad_size'] - 5) .'"';
        
        // generate html output
        if ( $row['image_url'] !== '' ) {
            $image = '<img name="' . tp_html::prepare_title($row['title'], 'replace') . '" src="' . $row['image_url'] . '" ' . $width . ' alt="' . tp_html::prepare_title($row['title'], 'replace') . '" />';
        }
        else {
            $image = '';
        }

        if ( $settings['image'] === 'left' ) {
            $return['left'] = '<td class="tp_pub_image_left" width="' . $settings['pad_size'] . '">' . $image . '</td>';
        }
        
        if ( $settings['image'] === 'right' ) {
            $return['right'] = '<td class="tp_pub_image_right" width="' . $settings['pad_size']  . '">' . $image . '</td>';
        }
        
        if ( $settings['image'] === 'bottom' ) {
            $return['bottom'] = '<div class="tp_pub_image_bottom">' . $image . '</div>';
        }
        
        return $return;
    }
    
}

