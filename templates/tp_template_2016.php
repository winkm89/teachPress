<?php
/**
 * teachPress template file
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.1
 */

class tp_template_2016 implements tp_publication_template {
    public function get_settings() {
        return array ('name' => 'teachPress 2016',
                      'description' => 'The new 4 line style for publications. Default since teachPress 5.1',
                      'author' => 'Michael Winkler',
                      'version'=> '1.0',
                      'button_separator' => ' | '
            );
    }
    
    public function get_body() {
        
    }
    
    public function get_headline() {
        
    }
    
    public function get_entry($interface) {
        $s = '<tr class="tp_publication">';
        $s .= $interface->get_number('<td class="tp_pub_number">', '.</td>');
        $s .= $interface->get_images('left');
        $s .= '<td class="tp_pub_info">';
        $s .= $interface->get_author('<p class="tp_pub_author">', '</p>');
        $s .= '<p class="tp_pub_title">' . $interface->get_title() . ' ' . $interface->get_type() . '</p>';
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
