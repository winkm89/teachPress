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
                      'button_separator' => ' | ',
                      'before_tags_line' => '(',
                      'after_tags_line'  => ')'
            );
    }
    
    public function get_general_part() {
        $s = '<tr class="tp_publication">';
        $s .= '##NUMBER##';
        $s .= '##IMAGES_LEFT##';
        $s .= '<td class="tp_pub_info">';
        $s .= '##AUTHOR##';
        $s .= '<p class="tp_pub_title">##TITLE## ##TYPE##</p>';
        $s .= '<p class="tp_pub_additional">##META##</p>';
        $s .= '<p class="tp_pub_tags">##TAGS##</p>';
        $s .= '##INFO_CONTAINER##';
        $s .= '##IMAGES_BOTTOM##';
        $s .= '</td>';
        $s .= '##IMAGES_RIGHT##';
        $s .= '</tr>';
        return $s;
    }
    
    public function get_author_part($content) {
        return '<p class="tp_pub_author">' . stripslashes($content) . '</p>';
    }
    
    public function get_enumeration_part ($content) {
        return '<td class="tp_pub_number">' . $content . '.</td>';
    }
    
    public function get_type_part ($type, $class) {
        return '<span class="tp_pub_type ' . $class . '">' . $type . '</span>';
    }
}
