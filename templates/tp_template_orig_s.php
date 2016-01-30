<?php
/**
 * teachPress template file
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.1
 */

class tp_template_orig_s implements tp_publication_template {
    
    public function get_settings() {
        return array ('name' => 'teachPress original small',
                      'description' => 'The original 1 line style for publications.',
                      'button_separator' => ' | ',
                      'before_tags_line' => '',
                      'after_tags_line'  => ''
            );
    }
    
    public function get_general_part() {
        $s = '<tr class="tp_publication_simple">';
        $s .= '##NUMBER##';
        $s .= '##IMAGES_LEFT##';
        $s .= '<td class="tp_pub_info_simple">';
        $s .= '##AUTHOR##';
        $s .= '<span class="tp_pub_year_simple"> (##YEAR##)</span>: ';
        $s .= '<span class="tp_pub_title_simple">##TITLE##</span>. ';
        $s .= '<span class="tp_pub_additional_simple">##META##</span> ';
        $s .= '<span class="tp_pub_tags_simple">(##TYPE## | ##TAGS##)</span>';
        $s .= '##INFO_CONTAINER##';
        $s .= '##IMAGES_BOTTOM##';
        $s .= '</td>';
        $s .= '##IMAGES_RIGHT##';
        $s .= '</tr>';
        return $s;
    }
    
    public function get_author_part($content) {
        return '<span class="tp_pub_author_simple">' . stripslashes($content) . '</span>';
    }
    
    public function get_enumeration_part ($content) {
        return '<td class="tp_pub_number_simple">' . $content . '.</td>';
    }
    
    public function get_type_part ($type, $class) {
        return __('Type') . ': <span class="tp_pub_type_simple ' . $class . '">' . $type . '</span>';
    }
    
}

