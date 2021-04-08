<?php
/**
 * teachPress template file
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

class TP_Template_Orig_S implements TP_Publication_Template {
    
    public function get_settings() {
        return array ('name'                => 'teachPress original small',
                      'description'         => 'The original 1-line style template for publication lists.',
                      'author'              => 'Michael Winkler',
                      'version'             => '1.2',
                      'button_separator'    => ' | ',
                      'citation_style'      => 'teachPress_small'
        );
    }
    
    /**
     * Returns the body element for a publication list
     * @param string $content   The content of the publication list itself
     * @param array $args       An array with some basic settings for the publication list 
     * @return string
     */
    public function get_body ($content, $args = array() ) {
        return '<table class="teachpress_publication_list">' . $content . '</table>';
    }
    
    /**
     * Returns the headline for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list
     * @return string
     */
    public function get_headline ($content, $args = array()) {
        return '<tr>
                    <td' . $args['colspan'] . '>
                        <h3 class="tp_h3" id="tp_h3_' . esc_attr($content) .'">' . $content . '</h3>
                    </td>
                </tr>';
    }
    
    /**
     * Returns the headline (second level) for a publication list or a part of that
     * @param type $content     The content of the headline
     * @param type $args        An array with some basic settings for the publication list
     * @return string
     */
    public function get_headline_sl ($content, $args = array()) {
        return '<tr>
                    <td' . $args['colspan'] . '>
                        <h4 class="tp_h4" id="tp_h4_' . esc_attr($content) .'">' . $content . '</h4>
                    </td>
                </tr>';
    }
    
    /**
     * Returns the container for publication images
     * @param string $content               The image element
     * @param string $position              The image position: left, right or buttom
     * @param string $optional_attributes   Optional attributes for the framing container element
     * @return string
     * @since 7.2
     */
    public function get_image($content, $position, $optional_attributes = '') {
        return '<td class="tp_pub_image_' . $position . '" ' . $optional_attributes . '>' . $content . '</td>';
    }
    
    /**
     * Returns the single entry of a publication list
     * @param object $interface     The interface object
     * @return string
     */
    public function get_entry ($interface) {
        $class = ' tp_publication_simple_' . $interface->get_type('');
        $s = '<tr class="tp_publication_simple' . $class . '">';
        $s .= $interface->get_number('<td class="tp_pub_number_simple">', '.</td>');
        $s .= $interface->get_images('left');
        $s .= '<td class="tp_pub_info_simple">';
        $s .= $interface->get_author('<span class="tp_pub_author_simple">', '</span>: ');
        // $s .= '<span class="tp_pub_year_simple"> (' . $interface->get_year() . ')</span>: ';
        $s .= '<span class="tp_pub_title_simple">' . $interface->get_title() . '</span>. ';
        $s .= '<span class="tp_pub_additional_simple">' . $interface->get_meta() . '</span> ';
        $s .= '<span class="tp_pub_tags_simple">(' . __('Type','teachpress') . ': ' . $interface->get_type() . ' | ' . $interface->get_tag_line() . ')</span>';
        $s .= $interface->get_infocontainer();
        $s .= $interface->get_images('bottom');
        $s .= '</td>';
        $s .= $interface->get_images('right');
        $s .= '</tr>';
        return $s;
    }
    
}

