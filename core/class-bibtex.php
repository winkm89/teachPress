<?php
/**
 * This file contains all general functions of teachpress bibtex core
 * 
 * @package teachpress\core\bibtex
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */

/**
 * teachPress BibTeX | HTML class
 *
 * @package teachpress\core\bibtex
 * @since 3.0.0
 */
class tp_bibtex {

    /**
     * Gets a single publication in bibtex format
     * @param array $row
     * @param array $all_tags               optional
     * @param boolean $convert_bibtex       Flag for the utf-8 to TeX char convertion, Default is false
     * @return string
     * @since 3.0.0
    */
    public static function get_single_publication_bibtex ($row, $all_tags = '', $convert_bibtex = false) {
        $string = '';
        $pub_fields = array('type', 'bibtex', 'title', 'author', 'editor', 'url', 'doi', 'isbn', 'date', 'urldate', 'booktitle', 'issuetitle', 'journal', 'volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'institution', 'organization', 'school', 'series', 'crossref', 'abstract', 'howpublished', 'key', 'techtype', 'note');
        $isbn_label = ( $row['is_isbn'] == 1 ) ? 'isbn' : 'issn';
        
        // initial string
        if ( $row['type'] === 'presentation' ) {
            $string = '@misc{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        else {
            $string = '@' . stripslashes($row['type']) . '{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        
        // loop for all BibTeX fields
        for ( $i = 2; $i < count($pub_fields); $i++ ) {
            // replace html chars
            if ( $pub_fields[$i] === 'author' || $pub_fields[$i] === 'title' ) {
                $row[$pub_fields[$i]] = tp_bibtex::replace_html_chars($row[$pub_fields[$i]]);
            }
            // go to the next if there is nothing
            if ( !isset( $row[$pub_fields[$i]] ) || $row[$pub_fields[$i]] == '' || $row[$pub_fields[$i]] == '0000-00-00'  ) {
                continue;
            }
            // prepare the fields
            // ISBN | ISSN
            if ( $pub_fields[$i] === 'isbn' ) {
                $string .= $isbn_label . ' = {' . $row[$pub_fields[$i]] . '},' . chr(13) . chr(10);
            }
            // year
            elseif ( $pub_fields[$i] === 'date' ) {
                $string .= 'year  = {' . $row['year'] . '},' . chr(13) . chr(10);
                $string .= tp_bibtex::prepare_bibtex_line($row[$pub_fields[$i]],$pub_fields[$i]);
            }
            // techtype
            elseif ( $pub_fields[$i] === 'techtype' ) {
                $string .= 'type = {' . $row[$pub_fields[$i]] . '},' . chr(13) . chr(10);
            }
            // abstract
            elseif ( $pub_fields[$i] === 'abstract' || $pub_fields[$i] === 'title' ) {
                $string .= tp_bibtex::prepare_text($row[$pub_fields[$i]], $pub_fields[$i]);
            }
            // normal case
            else {
                $string .= tp_bibtex::prepare_bibtex_line($row[$pub_fields[$i]],$pub_fields[$i]);
            }
            
        }
        
        // Add month
        if ( $row['type'] == 'booklet' ) {
            $date = tp_datesplit( $row['date'] );
            $string .= 'month = {' . $date[0][1] . '},' . chr(13) . chr(10);
        }
        
        // Add keywords
        if ( $all_tags != '' ) {
            $keywords = '';
            foreach ( $all_tags as $all_tags ) {
                $keywords .= $all_tags['name'] . ', ';
            }
            $string .= 'keywords = {' . substr($keywords, 0, -2) . '}';
        }
        else {
            $string .= 'keywords = {}';
        }
        
        // Add teachPress/biblatex extensions
        $string .= ',' . chr(13) . chr(10);
        $string .= 'pubstate = {' . $row['status'] . '},' . chr(13) . chr(10);
        $string .= 'tppubtype = {' . $row['type'] . '}' . chr(13) . chr(10);
        $string .= '}' . chr(13) . chr(10);
        
        // Convert utf-8 chars
        if ( $convert_bibtex === true ) {
            $string = self::convert_utf8_to_bibtex($string);
        }
        return $string;
    }

    /**
     * Gets a single publication in html format
     * @param array $row        The publication array (used keys: title, image_url, ...)
     * @param array $all_tags   Array of tags (used_keys: pub_id, tag_id, name)
     * @param array $settings   Array with all settings (keys: author_name, editor_name, style, image, with_tags, link_style, date_format, convert_bibtex, container_suffix)
     * @param int $tpz          the counter for numbered publications (default: 0)
     * @return string
     * @since 3.0.0
     * @todo Needs to be simplified
    */
    public static function get_single_publication_html ($row, $all_tags, $settings, $tpz = 0) {
        $settings['use_span'] = true; 
        $tag_string = '';
        $keywords = '';
        $container_id = ( $settings['container_suffix'] != '' ) ? $row['pub_id'] . '_' . $settings['container_suffix'] : $row['pub_id'];
        // show tags
        if ( $settings['with_tags'] == 1 ) {
            foreach ($all_tags as $tag) {
                if ($tag["pub_id"] == $row['pub_id']) {
                    $keywords[] = array('name' => stripslashes($tag["name"]));
                    $tag_string = $tag_string . '<a href="' . $settings['permalink'] . 'tgid=' . $tag["tag_id"] . $settings['html_anchor'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($tag["name"]) . '</a>, ';
                }
            }
            $tag_string = substr($tag_string, 0, -2);
        }
        // handle images
        $image_marginally = '';
        $image_bottom = '';
        $td_left = '';
        $td_right = '';
        if ( $settings['image'] === 'left' || $settings['image'] === 'right' ) {
            if ( $row['image_url'] != '' ) {
                $image_marginally = '<img name="' . $row['title'] . '" src="' . $row['image_url'] . '" width="' . ($settings['pad_size'] - 5) .'" alt="' . $row['title'] . '" />';
            }
        }
        if ( $settings['image'] === 'left' ) {
            $td_left = '<td class="tp_pub_image_left" width="' . $settings['pad_size'] . '">' . $image_marginally . '</td>';
        }
        if ( $settings['image'] === 'right' ) {
            $td_right = '<td class="tp_pub_image_right" width="' . $settings['pad_size']  . '">' . $image_marginally . '</td>';
        }
        if ( $settings['image'] === 'bottom' ) {
            if ( $row['image_url'] != '' ) {
                $image_bottom = '<div class="tp_pub_image_bottom"><img name="' . tp_html::prepare_title($row['title'], 'replace') . '" src="' . $row['image_url'] . '" style="max-width:' . ($settings['pad_size']  - 5) .'px;" alt="' . tp_html::prepare_title($row['title'], 'replace') . '" /></div>';
            }
        }
        
        // prepare the title
        $name = self::prepare_publication_title($row, $settings, $container_id);

        // parse author names 
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = self::parse_author($row['editor'], $settings['author_name'] ) . ' (' . __('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = self::parse_author($row['author'], $settings['author_name'] );
        }

        // language sensitive publication type
        $type = tp_translate_pub_type($row['type']);

        $a2 = '';
        $a3 = '';
        $abstract = '';
        $url = '';

        // if is an abstract
        if ( $row['abstract'] != '' ) {
            $abstract = '<span class="tp_abstract_link"><a id="tp_abstract_sh_' . $container_id . '" class="tp_show" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_abstract' . "'" . ')" title="' . __('Show abstract','teachpress') . '" style="cursor:pointer;">' . __('Abstract','teachpress') . '</a> | </span>';
        }
        // if are links
        if ( $row['url'] != '' || $row['doi'] != '' ) {
            if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
                $url = '<span class="tp_resource_link"><a id="tp_links_sh_' . $container_id . '" class="tp_show" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_links' . "'" . ')" title="' . __('Show links and resources','teachpress') . '" style="cursor:pointer;">' . __('Links','teachpress') . '</a> | </span>';
            }
            else {
                $url = '<span class="tp_resource_link"> | ' . __('Links','teachpress') . ': ' . self::prepare_url($row['url'], $row['doi'], 'enumeration') . '</span>';
            }
        }
        // if with tags
        if ( $settings['with_tags'] == 1 ) {
            $tag_string = ' | ' . __('Tags') . ': ' . $tag_string;
        }
        // link style
        if ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) {
            $a2 = $abstract . $url . '<span class="tp_bibtex_link"><a id="tp_bibtex_sh_' . $container_id . '" class="tp_show" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_bibtex' . "'" . ')" style="cursor:pointer;" title="' . __('Show BibTeX entry','teachpress') . '">' . __('BibTeX','teachpress') . '</a></span>' . $tag_string;
        }
        else {
            $a2 = $abstract . '<span class="tp_bibtex_link"><a id="tp_bibtex_sh_' . $container_id . '" class="tp_show" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_bibtex' . "'" . ')" style="cursor:pointer;" title="' . __('Show BibTeX entry','teachpress') . '">' . __('BibTeX','teachpress') . '</a></span>' . $tag_string . $url;
        }
        // different styles: simple and normal
        if ( $settings['style'] === 'simple' || $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            $a1 = '<tr class="tp_publication_simple">';
            if ( $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
                $a1 .= '<td class="tp_pub_number_simple">' . $tpz . '.</td>';
            }
            $a1 .= $td_left;
            $a1 .= '<td class="tp_pub_info_simple">';
            if ( $row['author'] !== '' || $row['editor'] !== '' ) {
                $a1 .= '<span class="tp_pub_author_simple">' . stripslashes($all_authors) . '</span>';
            }
            $a1 .= '<span class="tp_pub_year_simple"> (' . $row['year'] . ')</span>: ';
            $a1 .= '<span class="tp_pub_title_simple">' . $name . '</span>. ';
            $a1 .= '<span class="tp_pub_additional_simple">' . self::single_publication_meta_row($row, $settings) . '</span>';
            $a2 = ' <span class="tp_pub_tags_simple">(' . __('Type') . ': <span class="tp_pub_typ_simple">' . stripslashes($type) . '</span> | ' . $a2 . ')</span>';
        }
        else {
            $a1 = '<tr class="tp_publication">';
            if ( $settings['style'] === 'std_num' || $settings['style'] === 'std_num_desc' ) {
                $a1 .= '<td class="tp_pub_number">' . $tpz . '.</td>';
            }
            $a1 .= $td_left;
            $a1 .= '<td class="tp_pub_info">';
            if ( $row['author'] !== '' || $row['editor'] !== '' ) {
                $a1 .= '<p class="tp_pub_author">' . stripslashes($all_authors) . '</p>';
            }
            $a1 .= '<p class="tp_pub_title">' . $name . ' <span class="tp_pub_typ">(' . stripslashes($type) . ')</span></p>';
            $meta_row = self::single_publication_meta_row($row, $settings);
            if ($meta_row != '.') {
                $a1 .= '<p class="tp_pub_additional">' . $meta_row . '</p>';
            }
            $a2 = '<p class="tp_pub_tags">(' . $a2 . ')</p>';
        }
        // end styles

        // div bibtex
        $a3 = '<div class="tp_bibtex" id="tp_bibtex_' . $container_id . '" style="display:none;">';
        $a3 .= '<div class="tp_bibtex_entry">' . nl2br( self::get_single_publication_bibtex($row, $keywords, $settings['convert_bibtex']) ) . '</div>';
        $a3 .= '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_bibtex' . "'" . ')">' . __('Close','teachpress') . '</a></p>';
        $a3 .= '</div>';
        // div abstract
        if ( $row['abstract'] != '' ) {
            $a3 .= '<div class="tp_abstract" id="tp_abstract_' . $container_id . '" style="display:none;">';
            $a3 .= '<div class="tp_abstract_entry">' . tp_html::prepare_text($row['abstract']) . '</div>';
            $a3 .= '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_abstract' . "'" . ')">' . __('Close','teachpress') . '</a></p>';
            $a3 .= '</div>';
        }
        // div links
        if ( ($row['url'] != '' || $row['doi'] != '') && ( $settings['link_style'] === 'inline' || $settings['link_style'] === 'direct' ) ) {
            $a3 .= '<div class="tp_links" id="tp_links_' . $container_id . '" style="display:none;">';
            $a3 .= '<div class="tp_links_entry">' . self::prepare_url($row['url'], $row['doi'], 'list') . '</div>';
            $a3 .= '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . "'" . $container_id . "'" . ',' . "'" . 'tp_links' . "'" . ')">' . __('Close','teachpress') . '</a></p>';
            $a3 .= '</div>';
        }
        $a4 = $image_bottom . '
                </td>
                ' . $td_right . '
                </tr>';			
        $a = $a1 . $a2 . $a3 . $a4;			
        return $a;
    }

    /**
     * Gets the second line of the publications with editor, year, volume, address, edition, etc.
     * @param array $row
     * @param array $settings
     * @return string
     * @since 3.0.0
    */
    public static function single_publication_meta_row($row, $settings) {
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
            $urldate = tp_bibtex::prepare_html_line('urldate', $row['urldate'],', ' . __('visited','teachpress') . ': ', '', $use_span); 
        }
        
        // isset() doesn't work for $editor
        $editor = $row['editor'] != '' ? tp_bibtex::parse_author($row['editor'], $settings['editor_name']) . ' (' . __('Ed.','teachpress') . '): ' : '';
        $pages = isset( $row['pages'] ) ? tp_bibtex::prepare_html_line('pages', tp_bibtex::prepare_page_number($row['pages']) , __('pp.','teachpress') . ' ',', ', $use_span) : '';
        $year = isset( $row['year'] ) ? tp_bibtex::prepare_html_line('year', $row['year'],'','',$use_span) : '';
        $booktitle = isset( $row['booktitle'] ) ? tp_bibtex::prepare_html_line('booktitle', $row['booktitle'],'',', ',$use_span) : '';
        $issuetitle = isset( $row['issuetitle'] ) ? tp_bibtex::prepare_html_line('issuetitle', $row['issuetitle'],'',', ',$use_span) : '';
        $journal = isset( $row['journal'] ) ? tp_bibtex::prepare_html_line('journal', $row['journal'],'',', ',$use_span) : '';
        $volume = isset( $row['volume'] ) ? tp_bibtex::prepare_html_line('volume', $row['volume'],'',' ',$use_span) : '';
        $number = isset( $row['number'] ) ? tp_bibtex::prepare_html_line('number', $row['number'],'(','), ',$use_span) : '';
        $publisher = isset( $row['publisher'] ) ? tp_bibtex::prepare_html_line('publisher', $row['publisher'],'',', ',$use_span) : '';
        $address = isset( $row['address'] ) ? tp_bibtex::prepare_html_line('address', $row['address'],'',', ',$use_span) : '';
        $edition = isset( $row['edition'] ) ? tp_bibtex::prepare_html_line('edition', $row['edition'],'',', ',$use_span) : '';
        $chapter = isset( $row['chapter'] ) ? tp_bibtex::prepare_html_line('chapter', $row['chapter'],' ' . __('Chapter','teachpress') . ' ',', ',$use_span) : '';
        $institution = isset( $row['institution'] ) ? tp_bibtex::prepare_html_line('institution', $row['institution'],'',' ',$use_span) : '';
        $organization = isset( $row['organization'] ) ? tp_bibtex::prepare_html_line('organization', $row['organization'],'',' ',$use_span) : '';
        $school = isset( $row['school'] ) ? tp_bibtex::prepare_html_line('school', $row['school'],'',', ',$use_span) : '';
        $series = isset( $row['series'] ) ? tp_bibtex::prepare_html_line('series', $row['series'],'',' ',$use_span) : '';
        $howpublished = isset( $row['howpublished'] ) ? tp_bibtex::prepare_html_line('howpublished', $row['howpublished'],'',', ',$use_span) : '';
        $techtype = isset( $row['techtype'] ) ? tp_bibtex::prepare_html_line('techtype', $row['techtype'],'',', ',$use_span) : '';
        $note = isset( $row['techtype'] ) ? tp_bibtex::prepare_html_line('note', $row['note'],', (',')',$use_span) : '';
        
        // special cases for volume/number
        if ( $number == '' && $volume != '' ) {
            $number = ', ';
        }
        
        // special cases for article/incollection/inbook/inproceedings
        $in = '';
        if ($settings['style'] === 'simple' || $settings['style'] === 'numbered' ) {
            if ( $row['type'] === 'article' || $row['type'] === 'inbook' || $row['type'] === 'incollection' || $row['type'] === 'inproceedings') {
                $in = __('In','teachpress') . ': ';
            }
        }

        // end format after type
        if ($row['type'] === 'article') {
            $end = $in . $journal . $volume . $number . $pages . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'book') {
            $end = $edition . $publisher . $address . $year . $isbn . $note .'.';
        }
        elseif ($row['type'] === 'booklet') {
            $end = $howpublished . $address . $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'collection') {
            $end = $edition . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'conference') {
            $end = $booktitle . $volume . $number . $series . $organization . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inbook') {
            $end = $in . $editor . $booktitle . $volume . $number . $chapter . $pages . $publisher . $address . $edition. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'incollection') {
            $end = $in . $editor . $booktitle . $volume . $number . $pages . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inproceedings') {
            $end = $in . $editor . $booktitle . $pages . $organization . $publisher . $address. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'manual') {
            $end = $editor . $organization . $address. $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] == 'mastersthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'misc') {
            $end = $howpublished . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'online') {
            $end = $editor . $organization . $year . $urldate . $note . '.';
        }
        elseif ($row['type'] === 'periodical') {
            $end = $issuetitle . $series . $volume . $number . $year . $urldate . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'phdthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'presentation') {
            $date = ( array_key_exists('date_format', $settings) === true ) ? ', ' . tp_bibtex::prepare_html_line('date', date( $settings['date_format'], strtotime($row['date']) ) ,'','',$use_span) : '';
            $end = ( $howpublished === '' && $row['address'] === '' ) ? substr($date,2) . $note . '.' : $howpublished . tp_bibtex::prepare_html_line('address', $row['address'],'','',$use_span) . $date . $note . '.';
        }
        elseif ($row['type'] === 'proceedings') {
            $end = $howpublished . $organization. $publisher. $address . $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'techreport') {
            $end = $institution . $address . $techtype . $number. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'unpublished') {
            $end = $year . $isbn . $note . '.';
        }
        else {
            $end = $year . $note . '.';
        }
        $end = stripslashes($end);
        return $end;
    }
    
    /**
    * Imports a BibTeX string
    * @global class $PARSEENTRIES
    * @param string $input      The input string with bibtex entries
    * @param array $settings    With index names: keyword_separator, author_format
    * @param string $test       Set it to true for test mode. This mode disables the inserting of publications into database
    * @since 3.0.0
    */
    public static function import_bibtex ($input, $settings, $test = false) {
        // Try to set the time limit for the script
        set_time_limit(TEACHPRESS_TIME_LIMIT);
        global $PARSEENTRIES;
        $input = tp_bibtex::convert_bibtex_to_utf8($input);
        $parse = NEW PARSEENTRIES();
        $parse->expandMacro = TRUE;
        $array = array('RMP' => 'Rev., Mod. Phys.');
        $parse->loadStringMacro($array);
        $parse->loadBibtexString($input);
        $parse->extractEntries();
        
        list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
        $max = count( $entries );
        for ( $i = 0; $i < $max; $i++ ) {
            $entries[$i]['name'] = array_key_exists('name', $entries[$i]) === true ? $entries[$i]['name'] : '';
            $entries[$i]['date'] = array_key_exists('date', $entries[$i]) === true ? $entries[$i]['date'] : '';
            $entries[$i]['location'] = array_key_exists('location', $entries[$i]) === true ? $entries[$i]['location'] : '';
            $entries[$i]['keywords'] = array_key_exists('keywords', $entries[$i]) === true ? $entries[$i]['keywords'] : '';
            $entries[$i]['tags'] = array_key_exists('tags', $entries[$i]) === true ? $entries[$i]['tags'] : '';
            $entries[$i]['isbn'] = array_key_exists('isbn', $entries[$i]) === true ? $entries[$i]['isbn'] : '';
            $entries[$i]['issn'] = array_key_exists('issn', $entries[$i]) === true ? $entries[$i]['issn'] : '';
            $entries[$i]['tppubtype'] = array_key_exists('tppubtype', $entries[$i]) === true ? $entries[$i]['tppubtype'] : '';
            $entries[$i]['pubstate'] = array_key_exists('pubstate', $entries[$i]) === true ? $entries[$i]['pubstate'] : '';
            
            // for the date of publishing
            $entries[$i]['date'] = self::set_date_of_publishing($entries[$i]);
            
            // for tags
            $tags = self::set_tags($entries[$i], $settings);
            
            // correct name | title bug of old teachPress versions
            if ($entries[$i]['name'] != '') {
                $entries[$i]['title'] = $entries[$i]['name'];
            }
            
            // consider old location fields
            if ( $entries[$i]['location'] != '' ) {
                $entries[$i]['address'] = $entries[$i]['location'];
            }
            
            // for author / editor
            // for format lastname1, firstname1 and lastname2, firstname2
            if ($settings['author_format'] == 2) {
                $entries[$i]['author'] = self::set_author_name($entries[$i]);
            }
            
            // for isbn/issn detection
            if ( $entries[$i]['issn'] != '' ) {
                $entries[$i]['is_isbn'] = 0;
                $entries[$i]['isbn'] = $entries[$i]['issn'];
            }
            else {
                $entries[$i]['is_isbn'] = 1;
            }
            
            // rename to teachPress keys
            $entries[$i]['type'] = $entries[$i]['bibtexEntryType'];
            $entries[$i]['bibtex'] = $entries[$i]['bibtexCitation'];
            
            // handle export data from teachPress/biblatex
            if ( $entries[$i]['tppubtype'] != '' ) {
                $entries[$i]['type'] = $entries[$i]['tppubtype'];
            }
            if ( $entries[$i]['pubstate'] != '' ) {
                $entries[$i]['status'] = $entries[$i]['pubstate'];
            }
            
            // replace bibtex chars
            foreach ($entries[$i] as $key => $value) {
                /*
                 * @todo Leads to problems with char replacement
                if ( $key == 'author' || $key == 'editor' ) {
                    continue;
                }
                 * 
                 */
                $entries[$i][$key] = str_replace(array('{','}'), array('',''), $value);
            }
            
            // Try to fix problems with line breaks
            if ( $tags != '' ) {
                $tags = str_replace (array("\r\n", "\n", "\r"), ' ', $tags);
            }
            
            // Add the string to database
            if ( $test === false ) {
                $entries[$i]['entry_id'] = self::import_publication_to_database($entries[$i], $tags, $settings);
            }
        }
        return $entries;

    }
    
    /**
     * This function is used for the import and adds publications to the database or owerwrites existing publications
     * @param array $entry
     * @param array $tags
     * @param array $settings
     * @return int Returns the ID of the new or changed publication
     * @since 5.0.0
     * @access private
     */
    private static function import_publication_to_database ($entry, $tags, $settings) {
        $check = true;
        if ( $settings['overwrite'] === true ) {
            $entry['entry_id'] = tp_publications::change_publication_by_key($entry['bibtex'], $entry, $tags);
            $check = ( $entry['entry_id'] === false ) ? false : true;
        }
        if ( $settings['overwrite'] === false || $check === false ) {
            $entry['entry_id'] = tp_publications::add_publication($entry, $tags, '');
        }
        return $entry['entry_id'];
    }

    /**
     * Replaces some HTML special chars with the UTF-8 versions
     * @param string $input
     * @return string
     * @since 3.0.0
    */
    public static function replace_html_chars ($input) {
        $array_1 = array('&Uuml;','&uuml;',
                         '&Ouml;','&ouml;','&ograve;','&oacute;','&Ograve;','&Oacute;',
                         '&Auml;','&auml;','&aacute;','&agrave;','&Agrave;','&Aacute;',
                         '&eacute;','&egrave;','&Egrave;','&Eacute;',
                         '&sect;','&copy;','&reg;','&pound;','&yen;',
                         '&szlig;','&micro;','&amp;',
                         '&nbsp;','&ndash;','&rdquo;','&ldquo;','&raquo;','&laquo;','&shy;','&quot;');
        $array_2 = array('Ü','ü',
                         'Ö','ö','ò','ó','Ò','Ó',
                         'Ä','ä','á','à','À','Á',
                         'é','è','È','É',
                         '§','©','®','£','¥',
                         'ß','µ','&',
                         ' ','-','”','“','»','«','­','"');
        $input = str_replace($array_1, $array_2, $input);
        return $input;
    }

    /**
     * Replaces some BibTeX special chars with the UTF-8 versions and secures the input. 
     * Before teachPress 5.0, this function was called replace_bibtex_chars()
     * 
     * @param string $input
     * @return string
     * @since 3.0.0
     * @access public
     */
    public static function convert_bibtex_to_utf8 ($input) {
        // return the input if there are no bibtex chars
        if ( strpos( $input,'\\' ) === false && strpos($input,'{') === false ) { return $input; }
        // Step 1: Chars which based on a combination of two chars, delete escapes
        $array_a = array('\ss','\O','\o','\L','\l','\AE','\ae','\OE','\oe','\t{oo}','\textendash','\textemdash',chr(92));
        $array_b = array('ß','Ø','ø','Ł','ł','Æ','æ','Œ','œ','o͡o','–','—','');
        $input = str_replace( $array_a , $array_b ,$input);
        
        // Step 2: All over special chars 
        $array_1 = array('"{a}','"{A}','`{a}','`{A}',"'{a}","'{A}",'~{a}','~{A}','={a}','={A}','^{a}','^{A}','.{a}','.{A}','u{a}','u{A}','k{a}','k{A}','r{a}','r{A}',
                         '.{b}','.{B}',
                         "'{c}","'{C}",'v{c}','v{C}','c{c}','c{C}','.{c}','.{C}','^{c}','^{C}',
                         'v{d}','v{D}','.{d}','.{D}','d{d}','d{D}','{d}','{D}',
                         '"{e}','"{E}',"'{e}","'{E}",'`{e}','`{E}','^{e}','^{E}','u{e}','u{E}','v{e}','v{E}','={e}','={E}','k{e}','k{E}','.{e}','.{E}',
                         '.{f}','.{F}',
                         'u{g}','u{G}','c{g}','c{G}','.{g}','.{G}','^{g}','^{G}',
                         '.{h}','.{H}','d{h}','d{H}','^{h}','^{H}','{h}','{H}',
                         '"{i}','"{I}','~{i}','~{I}','`{i}','`{I}',"'{i}","'{I}",'^{i}','^{I}','u{i}','u{I}','={i}','={I}','k{i}','k{I}','.{i}','.{I}',
                         '^{j}','^{J}',
                         'c{k}','c{K}','d{k}','d{K}',
                         "'{l}","'{L}",'v{l}','v{L}','c{l}','c{L}','d{l}','d{L}',
                         '.{m}','.{M}','d{m}','d{M}',
                         "'{n}","'{N}",'~{n}','~{N}','v{n}','v{N}','c{n}','c{N}','.{n}','.{N}',
                         '"{o}','"{O}','`{o}','`{O}',"'{o}","'{O}",'~{o}','~{O}','^{o}','^{O}','u{o}','u{O}','.{o}','.{O}','={o}','={O}','H{o}','H{O}',
                         '.{p}','.{P}',
                         "'{r}","'{R}",'v{r}','v{R}','c{r}','c{R}','.{r}','.{R}','d{r}','d{R}',
                         "'{s}","'{S}",'v{s}','v{S}','c{s}','c{S}','.{s}','.{S}','d{s}','d{S}','^{s}','^{S}',
                         'v{t}','v{T}','c{t}','c{T}','.{t}','.{T}','d{t}','d{T}','{t}','{T}',
                         '"{u}','"{U}','`{u}','`{U}',"'{u}","'{U}",'^{u}','^{U}','d{u}','d{U}','~{u}','~{U}','u{u}','u{U}','={u}','={U}','k{u}','k{U}','r{u}','r{U}','H{u}','H{U}',
                         'd{v}','d{V}',
                         '^{w}','^{W}',
                         '"{y}','"{Y}',"'{y}","'{Y}",'^{y}','^{Y}',
                         "'{z}","'{Z}",'v{z}','v{Z}','.{z}','.{Z}');
        $array_2 = array('ä','Ä','à','À','á','Á','ã','Ã','ā','Ā','â','Â','å','Å','ă','Ă','ą','Ą','å','Å',
                         'ḃ','Ḃ',
                         'ć','Ć','č','Č','ç','Ç','ċ','Ċ','ĉ','Ĉ',
                         'ď','Ď','ḋ','Ḋ','ḍ','Ḍ','đ','Đ',
                         'ë','Ë','é','É','è','È','ê','Ê','ĕ','Ĕ','ě','Ě','ē','Ē','ę','Ę','ė','Ė',
                         'ḟ','Ḟ',
                         'ğ','Ğ','ģ','Ģ','ġ','Ġ','ĝ','Ĝ',
                         'ḣ','Ḣ','ḥ','Ḥ','ĥ','Ĥ','ħ','Ħ',
                         'ï','Ï','ĩ','Ĩ','ì','Ì','í','Í','î','Î','ĭ','Ĭ','ī','Ī','į','Į','i','İ',
                         'ĵ','Ĵ',
                         'ķ','Ķ','ḳ','Ḳ',
                         'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ḷ','Ḷ',
                         'ṁ','Ṁ','ṃ','Ṃ',
                         'ń','Ń','ñ','Ñ','ň','Ň','ņ','Ņ','ṅ','Ṅ',
                         'ö','Ö','ò','Ò','ó','Ó','õ','Õ','ô','Ô','ŏ','Ŏ','ȯ','Ȯ','ō','Ō','ő','Ő',
                         'ṗ','Ṗ',
                         'ŕ','Ŕ','ř','Ř','ŗ','Ŗ','ṙ','Ṙ','ṛ','Ṛ',
                         'ś','Ś','š','Š','ş','Ş','ṡ','Ṡ','ṣ','Ṣ','ŝ','Ŝ',
                         'ť','Ť','ţ','Ţ','ṫ','Ṫ','ṭ','Ṭ','ŧ','Ŧ',
                         'ü','Ü','ù','Ù','ú','Ú','û','Û','ụ','Ụ','ũ','Ũ','ŭ','Ŭ','ū','Ū','ų','Ų','ů','Ů','ű','Ű',
                         'ṿ','Ṿ',
                         'ŵ','Ŵ',
                         'ÿ','Ÿ','ý','Ý','ŷ','Ŷ',
                         'ź','Ź','ž','Ž','ż','Ż');
        $return = str_replace($array_1, $array_2, $input);
        return htmlspecialchars($return, ENT_NOQUOTES);
    }
    
    /**
     * Replaces some UTF-8 chars with their BibTeX/LaTeX expression.
     * @param string $input
     * @return string
     * @since 5.0.0
     */
    public static function convert_utf8_to_bibtex ($input) {
        $array_a = array('ä','Ä','à','À','á','Á','ã','Ã','ā','Ā','â','Â','å','Å','ă','Ă','ą','Ą','å','Å',
                         'ḃ','Ḃ',
                         'ć','Ć','č','Č','ç','Ç','ċ','Ċ','ĉ','Ĉ',
                         'ď','Ď','ḋ','Ḋ','ḍ','Ḍ','đ','Đ',
                         'ë','Ë','é','É','è','È','ê','Ê','ĕ','Ĕ','ě','Ě','ē','Ē','ę','Ę','ė','Ė',
                         'ḟ','Ḟ',
                         'ğ','Ğ','ģ','Ģ','ġ','Ġ','ĝ','Ĝ',
                         'ḣ','Ḣ','ḥ','Ḥ','ĥ','Ĥ','ħ','Ħ',
                         'ï','Ï','ĩ','Ĩ','ì','Ì','í','Í','î','Î','ĭ','Ĭ','ī','Ī','į','Į','İ',
                         'ĵ','Ĵ',
                         'ķ','Ķ','ḳ','Ḳ',
                         'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ḷ','Ḷ',
                         'ṁ','Ṁ','ṃ','Ṃ',
                         'ń','Ń','ñ','Ñ','ň','Ň','ņ','Ņ','ṅ','Ṅ',
                         'ö','Ö','ò','Ò','ó','Ó','õ','Õ','ô','Ô','ŏ','Ŏ','ȯ','Ȯ','ō','Ō','ő','Ő',
                         'ṗ','Ṗ',
                         'ŕ','Ŕ','ř','Ř','ŗ','Ŗ','ṙ','Ṙ','ṛ','Ṛ',
                         'ś','Ś','š','Š','ş','Ş','ṡ','Ṡ','ṣ','Ṣ','ŝ','Ŝ',
                         'ť','Ť','ţ','Ţ','ṫ','Ṫ','ṭ','Ṭ','ŧ','Ŧ',
                         'ü','Ü','ù','Ù','ú','Ú','û','Û','ụ','Ụ','ũ','Ũ','ŭ','Ŭ','ū','Ū','ų','Ų','ů','Ů','ű','Ű',
                         'ṿ','Ṿ',
                         'ŵ','Ŵ',
                         'ÿ','Ÿ','ý','Ý','ŷ','Ŷ',
                         'ź','Ź','ž','Ž','ż','Ż',
                         'ß','Ø','ø','Ł','ł','Æ','æ','Œ','œ','o͡o','–','—');
        
        $array_b = array('\"{a}', '\"{A}', '"\`{a}', '\`{A}', "\'{a}", "\'{A}", '\^{a}', '\^{A}', '\~{a}', '\~{A}', '\k{a}', '\k{A}', '\={a}', '\={A}', '\.{a}', '\.{A}', '\u{a}', '\u{A}', 'r{a}', 'r{A}',
                         '\.{b}', '\.{B}',
                         "\'{c}", "\'{C}", '\v{c}', '\v{C}', '\c{c}', '\c{C}', '\.{c}', '\.{C}', '\^{c}', '\^{C}',
                         '\v{d}', '\v{D}', '\.{d}', '\.{D}', '{d}', '{D}', '\d{d}', '\d{D}',
                         '\"{e}', '\"{E}', '\`{e}', '\`{E}', "\'{e}", "\'{E}", '\^{e}', '\^{E}', '\.{e}', '\.{E}', '\u{e}', '\u{E}', 'v{e}', 'v{E}', '={e}', '={E}', '\k{e}', '\k{E}',
                         '\.{f}', '\.{F}',
                         '\u{g}', '\u{G}', '\c{g}', '\c{G}', '\.{g}', '\.{G}', '\^{g}', '\^{G}',
                         '\.{h}', '\.{h}', '{h}', '{H}', '\d{h}', '\d{H}', '\^{h}', '\^{H}',
                         '\"{i}', '\"{I}', '\`{i}', '\`{I}', "\'{i}", "\'{I}", '\^{i}', '\^{I}', '\~{i}', '\~{I}', '\.{i}', '\.{I}', '\u{i}', '\u{I}', '\={i}', '\={I}', '\k{I}',
                         '\^{j}', '\^{J}',
                         '\c{k}', '\c{K}', '\d{k}', '\d{K}',
                         "\'{l}", "\'{L}", '\v{l}', '\v{L}', '\c{l}', '\c{L}', '\d{l}', '\d{L}',
                         '\.{m}', '\.{M}', '\d{m}', '\d{M}',
                         "\'{n}", "\'{N}", '\~{n}', '\~{N}', 'v{n}', 'v{N}', '\c{n}', '\c{N}', '\.{n}', '\.{N}',
                         '\"{o}', '\"{O}', '\`{o}', '\`{O}', "\'{o}", "\'{O}", '\^{o}', '\^{O}', '\~{o}', '\~{O}', '\u{o}', '\u{O}', '\.{o}', '\.{O}', '\={o}' , '\={O}', '\H{o}', '\H{O}',
                         '\.{p}', '\.{P}',
                         "\'{r}", "\'{R}", '\v{r}', '\v{R}', '\c{r}', '\c{R}', '\.{r}', '\.{R}', '\d{r}', '\d{R}',
                         "\'{s}", "\'{S}", '\v{s}', '\v{S}', '\c{s}', '\c{S}', '\.{s}', '\.{S}', '\d{s}', '\d{S}', '\^{s}', '\^{S}',
                         '\v{t}', '\v{T}', '\c{t}', '\c{T}', '\.{t}', '\.{T}', '{t}', '{T}', '\d{t}', '\d{T}',
                         '\"{u}', '\"{U}', '\`{u}', '\`{U}', "\'{u}", "\'{U}", '\^{u}', '\^{U}', '\H{u}', '\H{U}', '\~{u}', '\~{U}', '\d{u}', '\d{U}', 'r{u}', 'r{U}', '\u{u}', '\u{U}', '\={u}', '\={U}', '\k{u}', '\k{U}',
                         '\d{v}', '\d{V}',
                         '\^{w}', '\^{W}',
                         '\"{y}', '\"{Y}', "\'{y}", "\'{Y}", '\^{y}', '\^{Y}',
                         "\'{z}", "\'{Z}", '\v{z}', '\v{Z}', '\.{z}', '\.{Z}',
                         '{\ss}', '{\O}', '{\o}', '{\L}', '{\l}', '{\AE}', '{\ae}', '{\OE}', '{\oe}', '{\t{oo}}', '{\textendash}', '{\textemdash}'
                        );
        $return = str_replace( $array_a , $array_b ,$input);
        return $return;
    }
    
    /**
     * This function is used for the import and sets the author in a correct bibtex format.
     * @param array $entry
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function set_author_name ($entry) {
        $end = '';
        $new = explode(' and ', $entry['author'] );
        foreach ( $new as $new ) {
            $parts = explode(',', $new); 
            $num = count($parts); 
            $one = ''; 
            for ($j = 1; $j < $num; $j++) {
                $parts[$j] = trim($parts[$j]);
                $one .= ' '. $parts[$j];
            }
            $one .= ' ' . trim($parts[0]);
            $end = ( $end != '' ) ? $end . ' and ' . $one : $one;
        }
        return $end;
    }
    
    /**
     * This function is used for the import and sets the date of publishing for a publications.
     * @param array $entry
     * @return string
     * @since 5.0.0
     * @acces private
     */
    private static function set_date_of_publishing ($entry) {
        $entry['month'] = array_key_exists('month', $entry) === true ? self::parse_month($entry['month']) : '';
        $entry['day'] = array_key_exists('day', $entry) === true ? $entry['day'] : '';
        // if complete date is given
        if ( $entry['date'] != '' ) {
            $entry['date'] = $entry['date'];
        }
        // if month + year is given
        elseif ( $entry['month'] != '' && $entry['day'] === '' && $entry['year'] != '' ) {
            $entry['date'] = $entry['year'] . '-' . $entry['month'] . '-01';
        }
        // if day + month + year is given
        elseif ($entry['month'] != '' && $entry['day'] != '' && $entry['year'] != '') {
            $entry['date'] = $entry['year'] . '-' . $entry['month'] . '-' . $entry['day'];
        }
        // if year is given
        else {
            $entry['date'] = $entry['year'] . '-01-01';
        }
        return $entry['date'];
    }
    
    /**
     * This function is used for the import and sets the tags.
     * @param array $entry
     * @param array $settings
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function set_tags ($entry, $settings) {
        if ( $entry['keywords'] != '' ) {
            $tags = str_replace($settings['keyword_separator'],",",$entry['keywords']);
        }
        elseif ( $entry['tags'] != '' ) {
            $tags = str_replace($settings['keyword_separator'],",",$entry['tags']);
        }
        else {
            $tags = '';
        }
        return $tags;
    }
    
    /**
     * Prepares a (html) input for bibtex and replace expressions for bold, italic, lists, etc. with their latex equivalents
     * @param string $text          The (html) input
     * @param string $fieldname     The bibtex field name
     * @return string
     * @since 4.2.0
     */
    public static function prepare_text($text, $fieldname = 'abstract') {
        if ( $text == '' ) {
            return '';
        }
        
        $text = htmlspecialchars_decode($text);
        // Replace expressions
        $search = array ('/<sub>/i', '/<sup>/i',
                         '/<i>/i', '/<b>/i', '/<em>/i', '/<u>/i', 
                         '/<\/(sub|sup|i|b|em|u)>/i',
                         '/<(s|small|del)>/i',
                         '/<\/(s|small|del)>/i',
                         '/<ul>/i', '/<\/ul>/i',
                         '/<ol>/i', '/<\/ol>/i',
                         '/<li>/i', '/<\/li>/i');
        $replace = array ('_{', '^{',
                          '\textit{', '\textbf{', '\emph{', '\underline{', 
                          '}',
                          '',
                          '',
                          '\begin{itemize}', '\end{itemize}' . "\n",
                          '\begin{enumerate}', '\end{enumerate} . "\n"',
                          '\item ', '');
        $text = preg_replace($search, $replace, $text);
        /* Add wordwrap if necessary
         * Disabled since 4.2.1
            if (strpos($text, "\n") === false ) {
                $text = wordwrap($text, 80, "\r\n");
            } 
         */
        return tp_bibtex::prepare_bibtex_line($text, $fieldname, false);
    }

   /**
     * Prepares a page number
     * @access public
     * @param string $input
     * @return string
     * @since 4.0.0
     */
    public static function prepare_page_number ($input) {
        if ( isset($input) ) {
            return str_replace("--", "–", $input);
        }
        return '';
    }
    
    /**
     * This function prepares the publication title for html publication lists. This is used in get_single_publication_html().
     * @param array $row                The publication array
     * @param array $settings           Array with all settings (keys: author_name, editor_name, style, image, with_tags, link_style, date_format, convert_bibtex, container_suffix)
     * @param string $container_id      The basic ID for div container
     * @return string
     * @since 5.0.2
     */
    private static function prepare_publication_title ($row, $settings, $container_id) {
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
     * Prepares a single BibTeX line with the input from onde publication field
     * @param string    $input          The value of the publication field
     * @param string    $fieldname      The name of the publication field
     * @param boolean   $stripslashes   Strip slashes (true) or not (false); default is true; since 4.2.0
     * @return string
     * @since 3.0.0
     */
    public static function prepare_bibtex_line($input, $fieldname, $stripslashes = true) {
        if ($input != '') {
            $input = ( $stripslashes === true ) ? stripslashes($input) : $input;
            return $fieldname . ' = {' . $input . '},' . chr(13) . chr(10);
        }
        return '';
    }

    /**
     * Prepares a single HTML line with the input from one publication field
     * @param string $element
     * @param string $content
     * @param string $before
     * @param string $after
     * @param string $use_span 
     * @return string
     * @since 3.0.0
     * @version 3 (since 4.3.8)
     */
    public static function prepare_html_line($element, $content, $before = '', $after = '', $use_span = false) {
        if ( $content === '' ) {
            return '';
        }
        if ( $use_span === true ) {
            return '<span class="tp_pub_additional_' . $element . '">' . $before . $content . $after . '</span>';
        }
        return $before . $content . $after;
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
     * Explodes an url string into array 
     * @param string $url_string 
     * @return array 
     * @since 4.3.5 
    */ 
    public static function explode_url ($url_string) { 
        $all_urls = explode(chr(13) . chr(10), $url_string); 
        $end = array(); 
        foreach ($all_urls as $url) { 
            $parts = explode(', ',$url); 
            $parts[0] = trim( $parts[0] ); 
            if ( !isset($parts[1]) ) { 
                $parts[1] = $parts[0]; 
            } 
            $end[] = $parts; 
        } 
        return $end; 
    } 
    
    /**
     * The function splits a author/editor name and returns the lastname or NULL if there is no name was found
     * @global $PARSECREATORS
     * @param string $input     A name of an author or editor
     * @return string
     * @since 5.0.0
     */
    public static function get_lastname ($input) {
        global $PARSECREATORS;
        $creator = new PARSECREATORS();
        $creatorArray = $creator->parse($input);
        if ( isset( $creatorArray[0][2] ) ) {
            return trim($creatorArray[0][2]);
        }
        return null;
    }

    /**
     * Parses author names
     * @global $PARSECREATORS
     * @param string $input
     * @param string $mode       --> values: last, initials, old
     * @return string
     * @since 3.0.0
    */
    public static function parse_author ($input, $mode = '') {
        if ( $mode === 'last' || $mode === 'initials' ) {
            $all_authors = self::parse_author_default($input, $mode);
        }
        elseif ( $mode === 'old' ) {
            $all_authors = self::parse_author_deprecated($input);
        }
        else {
            $all_authors = self::parse_author_simple($input);
        }
        return $all_authors;
    }
    
    /**
     * This is the default parsing function for author names
     * 
     * Some examples for the parsing:
     * last:            Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf; van Beethoven, Ludwig
     * initials: 	Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F; van Beethoven, Ludwig
     * 
     * @param string $input
     * @param string $mode
     * @return string
     * @since 5.0.0
     * @access public
     * @uses PARSECREATORS() This class is a part of bibtexParse
     */
    public static function parse_author_default ($input, $mode) {
        global $PARSECREATORS;
        $creator = new PARSECREATORS();
        $creatorArray = $creator->parse($input);
        $all_authors = '';
        $max = count($creatorArray);
        for ( $i = 0; $i < $max; $i++ ) {
            $one_author = '';
            if ($creatorArray[$i][3] != '') { $one_author = trim($creatorArray[$i][3]);}
            if ($creatorArray[$i][2] != '') { $one_author .= ' ' .trim($creatorArray[$i][2]) . ',';}
            if ($creatorArray[$i][0] != '') { $one_author .= ' ' .trim($creatorArray[$i][0]);}
            if ( $mode == 'initials' && $creatorArray[$i][1] != '' ) { 
                $one_author .= ' ' .trim($creatorArray[$i][1]);
            }
            $all_authors .= stripslashes($one_author);
            if ($i < count($creatorArray) -1) {$all_authors = $all_authors . '; ';}
        }
        return $all_authors;
    }
    
    /**
     * This is the original (deprecated) parsing function for author names
     * 
     * Some examples for the parsing:
     * Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F.; van Beethoven, Ludwig
     * 
     * @param string $input
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function parse_author_deprecated ($input) {
        $all_authors = '';
        $one_author = '';
        $array = explode(" and ",$input);
        $lenth = count ($array);
        for ( $i = 0; $i < $lenth; $i++ ) {
            $array[$i] = trim($array[$i]);
            $names = explode(" ",$array[$i]);
            $lenth2 = count($names);
            for ( $j = 0; $j < $lenth2 - 1; $j++ ) {
                $one_author .= ' ' . trim( $names[$j] );
            }
            $one_author = trim( $names[$lenth2 - 1] ). ', ' . $one_author;
            $all_authors = $all_authors . $one_author;
            if ( $i < $lenth - 1 ) {
                $all_authors .= '; ';
            }
            $one_author = '';
        }
        return $all_authors;
    }
    
    /**
     * This is the simple parsing function which just replaces the "and" with ","
     * 
     * Some examples for the parsing:
     * Adolf F. Weinhold and Albert Einstein --> Adolf F. Weinhold, Albert Einstein
     * 
     * @param string $input
     * @return string
     * @since 5.0.0
     * @acces public
     */
    public static function parse_author_simple ($input) {
        $all_authors = str_replace( array(' and ', '{', '}'), array(', ', '', ''), $input );
        return stripslashes($all_authors);
    }
    
    /**
     * This function parses a month name into his numeric expression
     * @param string $input
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function parse_month ($input) {
        if ( strlen($input) > 2 ) {
            $date = date_parse($input);
            $output = ( $date['month'] < 10 ) ? '0' . $date['month'] : $date['month'];
        }
        return $output;
    }

    /**
     * Checks if a string is encoded with UTF-8 or not
     * from http://floern.com/webscripting/is-utf8-auf-utf-8-prüfen
     * 
     * @param string $string
     * @return boolean
     * @since 4.2.0
     */
    public static function is_utf8 ($string) {
        $strlen = strlen($string);
        for( $i = 0; $i < $strlen; $i++ ) {
            $ord = ord($string[$i]);
            if( $ord < 0x80 ) { 
                continue;
            }
            elseif( ($ord&0xE0) === 0xC0 && $ord > 0xC1 ) { 
                $n = 1;
            } 
            elseif( ($ord&0xF0) === 0xE0 ) { 
                $n = 2;
            }
            elseif( ($ord&0xF8) === 0xF0 && $ord < 0xF5 ) {
                $n = 3;
            }
            else {
                return false;
            } 
            for($c = 0; $c < $n; $c++) {
                if( ++$i === $strlen || ( ord($string[$i])&0xC0 ) !== 0x80 ) {
                    return false;
                }
            }
        }
        return true;
    }
}