<?php
/**
 * teachPress template file
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

class tp_template_2016 implements tp_publication_template {
    /**
     * Returns the settings of the template
     * @return array
     */
    public function get_settings() {
        return array ('name' => 'teachPress 2016',
                      'description' => 'A new 4 line style template for publication lists.',
                      'author' => 'Michael Winkler',
                      'version'=> '1.0',
                      'button_separator' => ' | ',
                      'citation_style'      => 'teachPress'
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
     * @param type $args        An array with some basic settings for the publication list (source: shortcode settings)
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
     * @param type $args        An array with some basic settings for the publication list (source: shortcode settings)
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
     * Returns the single entry of a publication list
     * 
     * Contents of the interface object:
     *   'row'               => An array of the related publication data
     *   'title'             => The title of the publication (completely prepared for HTML output)
     *   'images'            => The images array (HTML code for left, bottom, right)
     *   'tag_line'          => The HTML tag string
     *   'settings'          => The settings array (shortcode options)
     *   'counter'           => The publication counter (integer)
     *   'all_authors'       => The prepared author string
     *   'keywords'          => An array of related keywords
     *   'container_id'      => The ID of the HTML container
     *   'template_settings' => The template settings array (name, description, author, citation_style)
     * 
     * @param object $interface     The interface object
     * @return string
     */
    public function get_entry ($interface) {
        $s = '<tr class="tp_publication">';
        $s .= $interface->get_number('<td class="tp_pub_number">', '.</td>');
        $s .= $interface->get_images('left');
        $s .= '<td class="tp_pub_info">';
        $s .= $interface->get_author('<p class="tp_pub_author">', '</p>');
        $s .= '<p class="tp_pub_title">' . $interface->get_title() . ' ' . $interface->get_type() . ' ' . $interface->get_label('status', array('forthcoming') ) . '</p>';
        $s .= '<p class="tp_pub_additional">' . $interface->get_meta() . '</p>';
        $s .= '<p class="tp_pub_tags">' . $interface->get_tag_line() . '</p>';
        $s .= $interface->get_infocontainer();
        $s .= $interface->get_images('bottom');
        $s .= '</td>';
        $s .= $interface->get_images('right');
        $s .= '</tr>';
        return $s;
    }
}
