<?php
/**
 * Template functions for displaying publications in HTML
 * @package teachpress\core\templates
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 6.0.0
 */

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
        return false;
    }

    $slug = esc_attr($slug);
    $templates = tp_detect_templates();

    // load template file
    if ( array_key_exists($slug, $templates) ) {
        include_once $templates[$slug];
        wp_enqueue_style($slug, TEACHPRESS_TEMPLATE_URL . $slug. '.css', array(), get_tp_version() );
        return new $slug();
    }

    return false;

}

/**
 * Interface for the template classes
 * @since 6.0.0
 */
interface TP_Publication_Template {
    /**
     * Returns the settings of the template
     * @return array
     * @since 6.0.0
     */
    public function get_settings();

    /**
     * Returns the body element for a publication list
     * @param string $content   The content of the publication list itself
     * @param array $args       An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @since 6.0.0
     */
    public function get_body($content, $args = array());

    /**
     * Returns the headline for a publication list or a part of that
     * @param string $content     The content of the headline
     * @param array $args        An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @since 6.0.0
     */
    public function get_headline($content, $args = array());

    /**
     * Returns the headline (second level) for a publication list or a part of that
     * @param string $content     The content of the headline
     * @param array $args         An array with some basic settings for the publication list (colspan, user, sort_list, headline, number_publications, years)
     * @return string
     * @since 6.0.0
     */
    public function get_headline_sl($content, $args = array());

    /**
     * Returns the container for publication images
     * @param string $content               The image element
     * @param string $position              The image position: left, right or buttom
     * @param string $optional_attributes   Optional attributes for the framing container element
     * @return string
     * @since 8.0.0
     */
    public function get_image($content, $position, $optional_attributes = '');

    /**
     * Returns the single entry of a publication list
     * @param object $interface     The interface object
     * @return string
     * @since 6.0.0
     */
    public function get_entry($interface);
}

/**
 * Contains all interface functions for publication templates
 * @since 6.0.0
 */
class TP_Publication_Template_API {
    protected $data;

    /**
     * Returns the data for a publication row
     * @return array
     * @since 6.0.0
     * @access public
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Sets the data for a publication row
     * @param array $data
     * @since 6.0.0
     * @access public
     */
    public function set_data($data) {
        $this->data = $data;
    }

    /**
     * Generates a span element for the selected publication data field
     * @param string $element   The data field (for example: status, journal, type )
     * @param array $values     An array of values of the data field, which should be considered as labels
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_label ($element, $values = array()) {
        $data = ( isset ($this->data['row'][$element]) ) ? $this->data['row'][$element] : '' ;
        if ( $data === '' ) {
            return '';
        }

        if ( in_array($data, $values) ) {
            $title = ( $element === 'status' && $data === 'forthcoming' ) ? __('Forthcoming','teachpress') : $data;
            // Replace possible chars from the meta data system
            $title = str_replace(array('{','}'), array('',''), $title);
            return '<span class="tp_pub_label_' . $element . ' ' . esc_attr($data) . '">' . $title . '</span>';
        }
    }

    /**
     * Returns the number for a numbered publication list
     * @param string $before
     * @param string $after
     * @return string
     * @since 6.0.0
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
     * @since 6.0.0
     * @access public
     */
    public function get_title () {
        return $this->data['title'];
    }

    /**
     * Returns the type of a publication (as html element)
     * @param string container      Default is span, For a plain retun use get_type(''), New since 7.0.0
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_type($container = 'span') {
        $type = $this->data['row']['type'];
        if ( $container !== '' ) {
            return '<' . $container . ' class="tp_pub_type ' . $type . '">' . tp_translate_pub_type($type) . '</' . $container . '>';
        }
        return $type;
    }

    /**
     * Returns the authors
     * @param string $before
     * @param string $after
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_author ($before = '', $after = '') {
        if ( $this->data['row']['author'] === '' && $this->data['row']['editor'] === '' ) {
            return '';
        }
        return $before . $this->data['all_authors']  . $after;
    }

    /**
     * Returns the award (as html element)
     * @return string
     * @since 9.0.0
     * @access public
     */
    public function get_award($withLabel = false) {
        global $tp_awards;
        if ( $this->data['row']['award'] != '' && $this->data['row']['award'] != 'none' ) {
            $award_data = $tp_awards->get_data($this->data['row']['award']);
            $s = '<span class="tp_pub_label_award" title="'.$award_data["i18n_singular"].'"><i class="'.$award_data["icon"].'"></i>';
            if ($withLabel) {
                $s .= ' '.$award_data["i18n_singular"];
            }
            $s .= "</span>";
            return $s;
        }
        return "";
    }

    /**
     * Returns the meta row
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_meta () {
        return TP_HTML_Publication_Template::get_publication_meta_row($this->data['row'], $this->data['settings']);
    }

    /**
     * Returns the tags
     * @param string $before
     * @param string $after
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_menu_line ($before = '', $after = '') {
        $tag_string = $this->data['tag_line'];
        $separator = $this->data['template_settings']['button_separator'];

        // meta line formatting
        if ( $tag_string !== '' ) {
            // Hack fix: Replace empty sections in tag string
            $tag_string = str_replace('| <span class="tp_resource_link"> |', ' | ', $tag_string);
            $length = mb_strlen($separator);
            $last_chars = mb_substr($tag_string, -$length);
            $tag_string = ( $last_chars === $separator ) ? mb_substr($tag_string, 0, -$length) : $tag_string;
            $tag_string = $before . $tag_string . $after;
        }
        return $tag_string;
    }

    /**
     * Checks if a publication has a specific tag
     * @param string $tag_name
     * @return boolean
     * @since 6.2.3
     * @access public
     */
    public function has_tag ($tag_name) {
        $tags = $this->data['keywords'];
        foreach ( $tags as $single_array ) {
            if (in_array($tag_name, $single_array) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the year
     * @return string
     * @since 6.0.0
     * @access public
     */
    public function get_year () {
        return $this->data['row']['year'];
    }

    /**
     * Returns the images
     * @param string $position
     * @return string
     * @since 6.0.0
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
     * @since 6.0.0
     * @access public
     */
    public function get_infocontainer () {
        $content = '';
        $row = $this->data['row'];
        $keywords = $this->data['keywords'];
        $settings = $this->data['settings'];
        $container_id = $this->data['container_id'];

        // div altmetric
        if ( $settings['show_altmetric_entry']  && $row['doi'] != '' ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML_Publication_Template::prepare_altmetric($row['doi']), 'altmetric', $container_id );
        }

        if ( $settings['show_dimensions_badge'] && $row['doi'] != '' ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML_Publication_Template::prepare_dimensions( $row['doi'] ), 'dimensions', $container_id );
        }

        if ( $settings['show_plumx_widget'] && $row['doi'] != '' ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML_Publication_Template::prepare_plumx( $row['doi'] ), 'plumx', $container_id );
        }

	// div comment
	if ( $settings['show_comment'] && $row['comment'] != '' ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML::prepare_text($row['comment']), 'comment', $container_id );
	}

        // div bibtex
        $content .= TP_HTML_Publication_Template::get_info_container( nl2br( TP_Bibtex::get_single_publication_bibtex($row, $keywords, $settings['convert_bibtex']) ), 'bibtex', $container_id );

        // div abstract
        if ( $row['abstract'] != '' ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML::prepare_text($row['abstract']), 'abstract', $container_id );
        }

        // div links
        if ( ($row['url'] != '' || $row['doi'] != '') && ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) ) {
            $content .= TP_HTML_Publication_Template::get_info_container( TP_HTML_Publication_Template::prepare_url($row['url'], $row['doi'], 'list'), 'links', $container_id );
        }

        return $content;


    }

}

/**
 * This class contains all functions related to the HTML publication template generator
 * @since 6.0.0
 */
class TP_HTML_Publication_Template {

    public static function load_settings($template) {
        // default values
        $settings = array(
            'name'                  => '',
            'description'           => '',
            'author'                => '',
            'version'               => '0.0',
            'button_separator'      => ' | ',
            'menu_label_tags'       => __('Tags') . ': ',
            'menu_label_links'      => __('Links','teachpress') . ': ',
            'meta_label_in'         => __('In','teachpress') . ': ',
            'citation_style'        => 'teachPress'
        );
        // overwrite defaults
        if ( method_exists($template, 'get_settings') ) {
            $settings = shortcode_atts( $settings, $template->get_settings() );
        }
        return $settings;
    }

    /**
     * Gets a single publication in html format
     * @param array $row                The publication array (used keys: title, image_url, ...)
     * @param array $all_tags           Array of tags (used_keys: pub_id, tag_id, name)
     * @param array $settings           Array with all settings (keys: author_name, editor_name, style, image, link_style, date_format, convert_bibtex, container_suffix, show_tags_as)
     * @param object $template          The template object
     * @param array $template_settings  Array with the template settings (keys: button_separator, meta_label_in, menu_label_tags, menu_label_links)
     * @param int $pub_count            The counter for numbered publications (default: 0)
     * @return string
     * @since 6.0.0
     */
    public static function get_single ($row, $all_tags, $settings, $template, $template_settings, $pub_count = 0) {
        $container_id = ( $settings['container_suffix'] != '' ) ? $row['pub_id'] . '_' . $settings['container_suffix'] : $row['pub_id'];
        $separator = $template_settings['button_separator'];
        $name = self::prepare_publication_title($row, $settings, $container_id);
        $images = self::handle_images($row, $settings, $template);
        $abstract = '';
        $url = '';
        $bibtex = '';
        $settings['use_span'] = true;
        $settings['meta_label_in'] = $template_settings['meta_label_in'];
        $tag_string = '';
        $keywords = '';
        $all_authors = '';
        $is_button = false;
        $altmetric = '';
        $dimensions = '';
        $plumx = '';

        // show tags
        if ( $settings['show_tags_as'] != 'none' ) {
            $generated = self::get_tags($row, $all_tags, $settings);
            $keywords = $generated['keywords'];
            $tag_string = '<span class="tp_pub_tags_label">' . $template_settings['menu_label_tags'] . '</span>' . $generated['tags'];
        }

        // parse author names for teachPress style
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = TP_Bibtex::parse_author($row['editor'], $settings['author_separator'], $settings['author_name'] ) . ' (' . __('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = TP_Bibtex::parse_author($row['author'], $settings['author_separator'], $settings['author_name'] );
        }

        // if the publication has a doi -> altmetric
        if ( TEACHPRESS_ALTMETRIC_SUPPORT === true && $settings['show_altmetric_entry']  &&  $row['doi'] != '' ) {
            $altmetric = self::get_info_button(__('Altmetric','teachpress'), __('Show Altmetric','teachpress'), 'altmetric', $container_id) . $separator;
            $is_button = true;
        }

        // Dimensions
        if ( TEACHPRESS_DIMENSIONS_SUPPORT === true && $settings['show_dimensions_badge'] &&  $row['doi'] != '' ) {
            $dimensions = self::get_info_button( __( 'Dimensions', 'teachpress' ), __( 'Show Dimensions Badge', 'teachpress' ), 'dimensions', $container_id ) . $separator;
            $is_button = true;
        }

        // PlumX
        if ( TEACHPRESS_PLUMX_SUPPORT === true && $settings['show_plumx_widget'] &&  $row['doi'] != '' ) {
            $plumx = self::get_info_button( __( 'PlumX', 'teachpress' ), __( 'Show PlumX Widget', 'teachpress' ), 'plumx', $container_id ) . $separator;
            $is_button = true;
        }

        // Comment
        if ( $settings['show_comment'] && $row['comment'] != '' ) {
            $link_text = ( $settings['comment_text'] != '' ) ? $settings['comment_text'] : __('Comment', 'teachpress');
            $link_tooltip = ( $settings['comment_tooltip'] != '' ) ? $settings['comment_tooltip'] : __('Show comment', 'teachpress');
            $comment = self::get_info_button($link_text, $link_tooltip, 'comment', $container_id) . $separator;
            $is_button = true;
        }

        // if there is an abstract
        if ( $row['abstract'] != '' ) {
            $abstract = self::get_info_button(__('Abstract','teachpress'), __('Show abstract','teachpress'), 'abstract', $container_id) . $separator;
            $is_button = true;
        }

        // if there are links
        if ( $row['url'] != '' || $row['doi'] != '' ) {
            if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
                $url = self::get_info_button(__('Links','teachpress'), __('Show links and resources','teachpress'), 'links', $container_id) . $separator;
                $is_button = true;
            }
            else {
                $url = '<span class="tp_resource_link">' . $separator . '<span class="tp_pub_links_label">' . $template_settings['menu_label_links'] . '</span>' . self::prepare_url($row['url'], $row['doi'], 'enumeration') . '</span>';
            }
        }

        // if with bibtex
        if ( $settings['show_bibtex'] === true ) {
            $bibtex = self::get_info_button(__('BibTeX','teachpress'), __('Show BibTeX entry','teachpress'), 'bibtex', $container_id) . $separator;
            $is_button = true;
        }

        // link style
        if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
            $tag_string = $comment . $abstract . $url . $bibtex . $altmetric . $dimensions . $plumx. $tag_string ;
        }
        else {
            $tag_string = $comment . $abstract . $bibtex . $altmetric . $dimensions . $plumx . $tag_string . $url ;
        }

        // load template interface
        $interface_data = array (
            'row'               => $row,
            'title'             => $name,
            'images'            => $images,
            'tag_line'          => $tag_string,
            'settings'          => $settings,
            'counter'           => $pub_count,
            'all_authors'       => $all_authors,
            'keywords'          => $keywords,
            'container_id'      => $container_id,
            'template_settings' => $template_settings
        );

        $interface = new TP_Publication_Template_API();
        $interface->set_data($interface_data);

        // load entry template
        $s = $template->get_entry($interface);
        return $s;
    }

    /**
     * Gets the second line of the publications with editor, year, volume, address, edition, etc.
     * @param array $row            The data array of a single publication
     * @param array $settings       The settings array
     * @return string
     * @since 6.0.0
     */
    public static function get_publication_meta_row($row, $settings) {
        global $tp_publication_types;
        $use_span = $settings['use_span'];
        // For ISBN or ISSN number
        $isbn = '';
        if ( $row['isbn'] != '' ) {
            // test if ISBN or ISSN
            $after = ( $use_span === true ) ? '</span>' : '';
            if ($row['is_isbn'] == '0') {
                $before = ( $use_span === true ) ? '<span class="tp_pub_additional_issn">' : '';
                $isbn = ', ' . $before . 'ISSN: ' . $row['isbn'] . $after;
            }
            else {
                $before = ( $use_span === true ) ? '<span class="tp_pub_additional_isbn">' : '';
                $isbn = ', ' . $before . 'ISBN: ' . $row['isbn'] . $after;
            }
        }

        // for urldate
        $urldate = '';
        if ( isset( $row['urldate'] ) && $row['urldate'] !== '0000-00-00'  ) {
            $row['urldate'] = ( array_key_exists('date_format', $settings) === true ) ? date( $settings['date_format'], strtotime($row['urldate']) ) : $row['urldate'];
            $urldate = TP_HTML_Publication_Template::prepare_field('urldate', $row['urldate'],', ' . __('visited','teachpress') . ': ', '', $use_span);
        }

        // for number
        if ( $row['type'] === 'patent' ) {
            $number = isset( $row['number'] ) ? TP_HTML_Publication_Template::prepare_field('number', $row['number'],'',', ',$use_span) : '';
        }
        else {
            $number = isset( $row['number'] ) ? TP_HTML_Publication_Template::prepare_field('number', $row['number'],__('no.','teachpress') . ' ',', ', $use_span) : '';
        }

        // for forthcoming publications
        if ( $row['status'] === 'forthcoming' ) {
            $year = __('Forthcoming','teachpress');
        }
        else {
            $year = isset( $row['year'] ) ? TP_HTML_Publication_Template::prepare_field('year', $row['year'],'','',$use_span) : '';
        }

        // isset() doesn't work for $editor
        $editor = ( $row['editor'] != '' ) ? TP_Bibtex::parse_author($row['editor'], $settings['editor_separator'], $settings['editor_name']) . ' (' . __('Ed.','teachpress') . '): ' : '';
        $pages = isset( $row['pages'] ) ? TP_HTML_Publication_Template::prepare_field('pages', TP_Bibtex::prepare_page_number($row['pages']) , __('pp.','teachpress') . ' ',', ', $use_span) : '';
        $booktitle = isset( $row['booktitle'] ) ? TP_HTML_Publication_Template::prepare_field('booktitle', $row['booktitle'],'',', ',$use_span) : '';
        $issuetitle = isset( $row['issuetitle'] ) ? TP_HTML_Publication_Template::prepare_field('issuetitle', $row['issuetitle'],'',', ',$use_span) : '';
        $journal = isset( $row['journal'] ) ? TP_HTML_Publication_Template::prepare_field('journal', $row['journal'],'',', ',$use_span) : '';
        $volume = isset( $row['volume'] ) ? TP_HTML_Publication_Template::prepare_field('volume', $row['volume'],__('vol.','teachpress') . ' ',', ',$use_span) : '';
        $issue = isset( $row['issue'] ) ? TP_HTML_Publication_Template::prepare_field('issue', $row['issue'],__('iss.','teachpress') . ' ',', ',$use_span) : '';
        $publisher = isset( $row['publisher'] ) ? TP_HTML_Publication_Template::prepare_field('publisher', $row['publisher'],'',', ',$use_span) : '';
        $address = isset( $row['address'] ) ? TP_HTML_Publication_Template::prepare_field('address', $row['address'],'',', ',$use_span) : '';
        $edition = isset( $row['edition'] ) ? TP_HTML_Publication_Template::prepare_field('edition', $row['edition'],'',', ',$use_span) : '';
        $chapter = isset( $row['chapter'] ) ? TP_HTML_Publication_Template::prepare_field('chapter', $row['chapter'],' ' . __('Chapter','teachpress') . ' ',', ',$use_span) : '';
        $institution = isset( $row['institution'] ) ? TP_HTML_Publication_Template::prepare_field('institution', $row['institution'],'',' ',$use_span) : '';
        $organization = isset( $row['organization'] ) ? TP_HTML_Publication_Template::prepare_field('organization', $row['organization'],'',' ',$use_span) : '';
        $school = isset( $row['school'] ) ? TP_HTML_Publication_Template::prepare_field('school', $row['school'],'',', ',$use_span) : '';
        $series = isset( $row['series'] ) ? TP_HTML_Publication_Template::prepare_field('series', $row['series'],'',' ',$use_span) : '';
        $howpublished = isset( $row['howpublished'] ) ? TP_HTML_Publication_Template::prepare_field('howpublished', $row['howpublished'],'',', ',$use_span) : '';
        $techtype = isset( $row['techtype'] ) ? TP_HTML_Publication_Template::prepare_field('techtype', $row['techtype'],'',', ',$use_span) : '';
        $note = isset( $row['techtype'] ) ? TP_HTML_Publication_Template::prepare_field('note', $row['note'],', (',')',$use_span) : '';
        $date = ( array_key_exists('date_format', $settings) === true ) ? TP_HTML_Publication_Template::prepare_field('date', date( $settings['date_format'], strtotime($row['date']) ) ,'','',$use_span) : '';

        // special cases for article/incollection/inbook/inproceedings
        $in = ( $use_span === true ) ? '<span class="tp_pub_additional_in">' . $settings['meta_label_in'] . '</span>' : $settings['meta_label_in'];

        // end formator
        $type = $tp_publication_types->get_data($row['type']);
        $meta_row_template = '{year}{note}';
        if ( $type !== null ) {
            $meta_row_template = $type['html_meta_row'];
        }
        $replace_pairs = array (
            '{IN}'              => $in,
            '{address}'         => $address,
            '{booktitle}'       => $booktitle,
            '{chapter}'         => $chapter,
            '{date}'            => $date,
            '{editor}'          => $editor,
            '{edition}'         => $edition,
            '{howpublished}'    => $howpublished,
            '{institution}'     => $institution,
            '{isbn}'            => $isbn,
            '{issuetitle}'      => $issuetitle,
            '{journal}'         => $journal,
            '{note}'            => $note,
            '{number}'          => $number,
            '{organization}'    => $organization,
            '{pages}'           => $pages,
            '{publisher}'       => $publisher,
            '{school}'          => $school,
            '{series}'          => $series,
            '{techtype}'        => $techtype,
            '{urldate}'         => $urldate,
            '{volume}'          => $volume,
            '{issue}'           => $issue,
            '{year}'            => $year,
        );
        $end = strtr($meta_row_template, $replace_pairs) . '.';

        return stripslashes($end);
    }


    /**
     * Returns the show/hide buttons for the info container
     * @param string $name          The name of the button
     * @param string $title         The title/description of the button
     * @param string $type          bibtex, links, abstract
     * @param string $container_id  The suffix for the container ID
     * @return string
     * @since 6.0.0
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
     * @since 6.0.0
     */
    public static function get_info_container ($content, $type, $container_id) {
        $s = '<div class="tp_' . $type . '" id="tp_' . $type . '_' . $container_id . '" style="display:none;">';
        if ( $type === 'bibtex' ) {
            $s .= '<div class="tp_' . $type . '_entry"><pre>' . $content . '</pre></div>';
        }
        else {
            $s .= '<div class="tp_' . $type . '_entry">' . $content . '</div>';
        }
        $s .= '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . "'" . $container_id . "','tp_" . $type . "'" . ')">' . __('Close','teachpress') . '</a></p>';
        $s .= '</div>';
        return $s;
    }

    /**
     * Prepares a single publication field for HTML
     * @param string $element
     * @param string $content
     * @param string $before
     * @param string $after
     * @param string $use_span
     * @return string
     * @since 6.0.0
     */
    public static function prepare_field($element, $content, $before = '', $after = '', $use_span = false) {
        if ( $content === '' ) {
            return '';
        }
        if ( $use_span === true ) {
            return '<span class="tp_pub_additional_' . $element . '">' . $before . $content . $after . '</span>';
        }
        return $before . $content . $after;
    }

    /**
     * Generates the visible sorting number of a publication
     * @param int $number_entries       The number of selected publications
     * @param int $tpz                  The publication counter in the list
     * @param int $entry_limit          The current entry limit
     * @param string $style             The sorting styles
     * @since 6.2.2
     * @return int
     */
    public static function prepare_publication_number($number_entries, $tpz, $entry_limit, $style) {
        if ( $style === 'numbered_desc' || $style === 'std_num_desc' ) {
            return $number_entries - $tpz - $entry_limit;
        }
        return $entry_limit + $tpz + 1;
    }

    /**
     * This function prepares the publication title for html publication lists.
     * @param array $row                The publication array
     * @param array $settings           Array with all settings (keys: author_name, editor_name, style, image, link_style, title_ref, date_format, convert_bibtex, container_suffix,...)
     * @param string $container_id      The basic ID for div container
     * @return string
     * @since 6.0.0
     */
    public static function prepare_publication_title ($row, $settings, $container_id) {

        // open abstracts instead of links (ignores the rest of the method)
        if ( $settings['title_ref'] === 'abstract' ) {
            return self::prepare_title_link_to_abstracts($row, $container_id);
        }

        // Use a related page as link
        if ( $row['rel_page'] != 0 ) {
            return '<a href="' . get_permalink($row['rel_page']) . '">' . stripslashes($row['title']) . '</a>';
        }

        // for inline style
        elseif ( ($row['url'] != '' || $row['doi'] != '') && $settings['link_style'] === 'inline' ) {
            return '<a class="tp_title_link" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_links' . "'" . ')" style="cursor:pointer;">' . TP_HTML::prepare_title($row['title'], 'decode') . '</a>';
        }

        // for direct style (if a DOI numer exists)
        elseif ( $row['doi'] != '' && $settings['link_style'] === 'direct' ) {
            $doi_url = TEACHPRESS_DOI_RESOLVER . $row['doi'];
            $title = TP_HTML::prepare_title($row['title'], 'decode');
            return '<a class="tp_title_link" href="' . $doi_url . '" title="' . $title . '" target="blank">' . $title . '</a>';
        }

        // for direct style (use the first available URL)
        elseif ( $row['url'] != '' && $settings['link_style'] === 'direct' ) {
            $parts = TP_Bibtex::explode_url($row['url']);
            return '<a class="tp_title_link" href="' . $parts[0][0] . '" title="' . $parts[0][1] . '" target="blank">' . TP_HTML::prepare_title($row['title'], 'decode') . '</a>';
        }

        // if there is no link
        else {
            return TP_HTML::prepare_title($row['title'], 'decode');
        }

    }

    /**
     * Prepares a title if the link should refers to the abstract
     * @param array $row                The publication array
     * @param string $container_id      The basic ID for div container
     * @return string
     * @since 6.0.0
     * @access private
     */
    private static function prepare_title_link_to_abstracts($row, $container_id) {
        if ( $row['abstract'] != '' ) {
            return '<a class="tp_title_link" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_abstract' . "'" . ')" style="cursor:pointer;">' . TP_HTML::prepare_title($row['title'], 'decode') . '</a>';
        }
        else {
            return TP_HTML::prepare_title($row['title'], 'decode');
        }
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
        $url_displayed = array();
        foreach ($url as $url) {
            if ( $url == '' ) {
                continue;
            }
            $parts = explode(', ',$url);
            $parts[0] = trim( $parts[0] );
            $parts[1] = isset( $parts[1] ) ? $parts[1] : $parts[0];
            array_push($url_displayed, $parts[0]);
            // list mode
            if ( $mode === 'list' ) {
                $length = strlen($parts[1]);
                $parts[1] = substr($parts[1], 0 , 80);
                if ( $length > 80 ) {
                    $parts[1] .= '[...]';
                }
                $end .= '<li><i class="' . TP_Icons::get_class( $parts[0] ).'"></i><a class="tp_pub_list" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank">' . $parts[1] . '</a></li>';
            }
            // enumeration mode
            else {
                $end .= '<a class="tp_pub_link" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank"><i class="' . TP_Icons::get_class( $parts[0] ).'"></i></a>';
            }
        }

        /**
         * Add DOI-URL
         * @since 5.0.0
         */
        if ( $doi != '' ) {
            $doi_url = TEACHPRESS_DOI_RESOLVER . $doi;
            if (in_array($doi_url, $url_displayed) == False){
                if ( $mode === 'list' ) {
                    $end .= '<li><i class="' . TP_Icons::get_class( 'doi' ).'"></i><a class="tp_pub_list" href="' . $doi_url . '" title="' . __('Follow DOI:','teachpress') . $doi . '" target="_blank">doi:' . $doi . '</a></li>';
                }
                else {
                    $end .= '<a class="tp_pub_link" href="' . $doi_url . '" title="' . __('Follow DOI:','teachpress') . $doi . '" target="_blank"><i class="' . TP_Icons::get_class( 'doi').'"></i></a>';
                }
            }
        }

        if ( $mode === 'list' ) {
            $end = '<ul class="tp_pub_list">' . $end . '</ul>';
        }

        return $end;
    }

    /**
     * Prepares an altmetric info block
     * @param string $doi       The DOI number
     * @return string
     * @since 3.0.0
     * @version 2
     * @access public
     */
    public static function prepare_altmetric($doi = '') {
        $end = '';
         /**
         * Add DOI-URL
         * @since 5.0.0
         */
        if ( $doi != '' ) {
            $doi_url = TEACHPRESS_DOI_RESOLVER . $doi;

            $end .= '<div data-badge-details="right" data-badge-type="large-donut" data-doi="'.$doi .'" data-condensed="true" class="altmetric-embed"></div>';
        }

        return $end;
    }




    /**
     * Prepares a Dimensions Badge
     * @param string $doi       The DOI number
     * @return string
     * @since 9.0.0
     * @access public
     */
    public static function prepare_dimensions($doi = '') {
        if ( $doi != '' ) {
            return '<span class="__dimensions_badge_embed__" data-doi="' . urlencode( $doi ) . '" data-style="large"></span>';
        }
        return '';
    }


    /**
     * Prepares an Artifact Plum Print Widget
     * @param string $doi       The DOI number
     * @return string
     * @since 9.0.0
     * @access public
     */
    public static function prepare_plumx($doi = '') {
        if ( $doi != '' ) {
            return '<a href="https://plu.mx/plum/a/?doi=' . urlencode( $doi ) . '" data-popup="right" class="plumx-plum-print-popup"></a>';
        }
        return '';
    }


    /**
     * Generates the tag string for a single publication
     * @param array $row        The publication array
     * @param array $all_tags   An array of all tags
     * @param type $settings    The settings array
     * @return array Returns an array with tags and keywords
     * @since 6.0.0
     */
    public static function get_tags ($row, $all_tags, $settings) {
        $tag_string = '';
        $keywords = array();
        foreach ( $all_tags as $tag ) {
            if ( $tag["pub_id"] == $row['pub_id'] ) {
                $keywords[] = array('name' => stripslashes($tag["name"]));
                $tag_string .= self::prepare_tag_link($tag, $settings);
            }
        }
        return array('tags' => substr($tag_string, 0, -2),
                     'keywords' => $keywords);
    }

    /**
     * Prepares a single tag (plain or with HTML link)
     * @param array $tag        with tag_id and name
     * @param array $settings   The settings array
     * @return string
     * @since 8.0.1
     */
    private static function prepare_tag_link($tag, $settings) {
        if ( $settings['show_tags_as'] === 'plain' ) {
            return stripslashes($tag["name"]) . ', ';
        }
        else {
            return '<a rel="nofollow" href="' . $settings['permalink'] . 'tgid=' . $tag["tag_id"] . $settings['html_anchor'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($tag["name"]) . '</a>, ';
        }
    }

    /**
     * Generates the HTML output for images
     * @param array $row        The publication array
     * @param array $settings   The settings array
     * @return string
     * @since 6.0.0
     */
    public static function handle_images ($row, $settings, $template) {
        $return = array('bottom' => '',
                        'left' => '',
                        'right' => '');

        $image = '';

        // return if no images is set
        if ( $settings['image'] === 'none' ) {
            return $return;
        }

        // define the width of the image
        $width = ( $settings['image'] === 'bottom' ) ? 'style="max-width:' . ($settings['pad_size']  - 5) .'px;"' : 'width="' . ( $settings['pad_size'] - 5 ) .'"';

        // general html output
        if ( $row['image_url'] !== '' ) {
            $image = '<img name="' . TP_HTML::prepare_title($row['title'], 'replace') . '" src="' . $row['image_url'] . '" ' . $width . ' alt="' . TP_HTML::prepare_title($row['title'], 'replace') . '" />';
        }

        // image link
        $image = TP_HTML_Publication_Template::handle_image_link ($image, $row, $settings);

        // Altmetric / Dimensions / Plumx
        $altmetric = '';
        $dimensions = '';
        $plumx = '';
        
        if( $settings['show_altmetric_donut']) {
           $altmetric = '<div class="tp_pub_image_bottom"><div data-badge-type="medium-donut" data-doi="' . $row['doi']  . '" data-condensed="true" data-hide-no-mentions="true" class="altmetric-embed"></div></div>';
        }

        if ( $settings['show_dimensions_badge'] ) {
            $dimensions = '<span class="__dimensions_badge_embed__" data-doi="' . urlencode( $row['doi'] ) . '"></span>';
        }

        if ( $settings['show_plumx_widget'] ) {
            $plumx = '<div class="tp_pub_image_bottom"><a href="https://plu.mx/plum/a/?doi=' . urlencode( $row['doi'] ) . '" data-popup="hidden" class="plumx-plum-print-popup"></a></div>';
        }

        // left position
        if ( $settings['image'] === 'left' ) {
            $return['left'] = $template->get_image($image . $altmetric . $dimensions . $plumx, 'left', 'width="' . $settings['pad_size'] . '"');
        }

        // right position
        if ( $settings['image'] === 'right' ) {
            $return['right'] = $template->get_image($image . $altmetric . $dimensions . $plumx, 'right', 'width="' . $settings['pad_size'] . '"');
        }

        // bottom position
        if ( $settings['image'] === 'bottom' ) {
            $return['bottom'] = $template->get_image($image . $altmetric . $dimensions . $plumx, 'bottom');
        }

        return $return;
    }

    /**
     * Handles the image link generation
     * @param array $row
     * @param array $settings
     * @retun string
     * @since 7.1.0
     */
    public static function handle_image_link ($image, $row, $settings) {
        // Local image settings (higher priority)
        if ( $row['image_target'] === 'self'  ) {
            return '<a href="' . $row['image_url'] . '" target="_blank">' . $image . '</a>';
        }
        if ( $row['image_target'] === 'rel_page' && $row['rel_page'] != 0 ) {
            return '<a href="' . get_permalink($row['rel_page']) . '" title="' . stripslashes($row['title']) . '">' . $image . '</a>';
        }
        if ( $row['image_target'] === 'external' && $row['image_ext'] != '' ) {
            return '<a href="' . $row['image_ext'] . '" target="_blank">' . $image . '</a>';
        }

        // global shortcode settings (lower priority)
        if ( $settings['image_link'] === 'self'  ) {
            return '<a href="' . $row['image_url'] . '" target="_blank">' . $image . '</a>';
        }
        if ( $settings['image_link'] === 'rel_page' && $row['rel_page'] != 0 ) {
            return '<a href="' . get_permalink($row['rel_page']) . '" title="' . stripslashes($row['title']) . '">' . $image . '</a>';
        }
        if ( $settings['image_link'] === 'external' && $row['image_ext'] != '' ) {
            return '<a href="' . $row['image_ext'] . '" target="_blank">' . $image . '</a>';
        }

        return $image;
    }

}
