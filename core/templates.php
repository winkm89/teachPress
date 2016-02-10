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
    public function get_settings();
    public function get_general_part();
    public function get_author_part($content);
    public function get_enumeration_part ($content);
    public function get_type_part ($type, $class);
}

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
        
        // meta line formatting
        if ( $tag_string !== '' ) {
            $length = mb_strlen($separator);
            $last_chars = mb_substr($tag_string, -$length);
            $tag_string = ( $last_chars === $separator ) ? mb_substr($tag_string, 0, -$length) : $tag_string;
            $tag_string = $template_settings['before_tags_line'] . $tag_string . $template_settings['after_tags_line'];
        }
        
        
        // Replace template tags
        $search = array ('##NUMBER##',
                         '##TYPE##',
                         '##TITLE##',
                         '##AUTHOR##',
                         '##META##',
                         '##TAGS##',
                         '##YEAR##',
                         '##IMAGES_LEFT##',
                         '##IMAGES_RIGHT##',
                         '##IMAGES_BOTTOM##',
                         '##INFO_CONTAINER##'
        );
        
        $replace = array (
            self::prepare_tag_enumeration($settings, $pub_count, $template),               // Number
            $template->get_type_part(tp_translate_pub_type($row['type']), 'tp_pub_type_' . $row['type']), // Type
            $name,                                                                         // Title
            self::prepare_tag_author ($row, $all_authors, $template),                      // Author
            tp_bibtex::single_publication_meta_row($row, $settings),                       // Meta
            $tag_string,                                                                   // Tags
            $row['year'],                                                                  // Year
            $images['left'],                                                               // IMAGES_LEFT
            $images['right'],                                                              // IMAGES_RIGHT
            $images['bottom'],                                                             // IMAGES_BOTTOM
            self::prepare_tag_info_container ($row, $keywords, $settings, $container_id)   // INFO_CONTAINER
        );
        
        $s = $template->get_general_part();
        return str_replace($search, $replace, $s);
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
     * Returns the information container (bibtex/abstract/links) for a single publication
     * @param array $row
     * @param array $keywords
     * @param array $settings
     * @param string $container_id
     * @return string
     * @since 5.1.0
     * @access private
     */
    private static function prepare_tag_info_container ($row, $keywords, $settings, $container_id) {
        $content = '';
        
        // div bibtex
        $content .= self::get_info_container( nl2br( tp_bibtex::get_single_publication_bibtex($row, $keywords, $settings['convert_bibtex']) ), 'bibtex', $container_id );
        
        // div abstract
        if ( $row['abstract'] != '' ) {
            $content .= self::get_info_container( tp_html::prepare_text($row['abstract']), 'abstract', $container_id );
        }
        
        // div links
        if ( ($row['url'] != '' || $row['doi'] != '') && ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) ) {
            $content .= self::get_info_container( tp_bibtex::prepare_url($row['url'], $row['doi'], 'list'), 'links', $container_id );
        }
        
        return $content;
    }

    /**
     * Prepares the author tag for templates
     * @param array $row
     * @param array $all_authors
     * @param object $template
     * @return string
     * @since 5.1.0
     * @access private
     */
    private static function prepare_tag_author ($row, $all_authors, $template) {
        if ( $row['author'] === '' && $row['editor'] === '' ) {
            return '';
        }
        return $template->get_author_part($all_authors);
    }
    
    /**
     * Prepeares the enumeration tag for templates
     * @param array $settings
     * @param int $number
     * @param object $template
     * @return string
     * @since 5.1.0
     * @access private
     */
    private static function prepare_tag_enumeration ($settings, $number, $template) {
        if ( $settings['style'] === 'std_num' || $settings['style'] === 'std_num_desc' || $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            return $template->get_enumeration_part($number);
        }
        return '';
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

