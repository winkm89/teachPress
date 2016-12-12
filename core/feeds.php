<?php
/**
 * This file contains the feed constructors
 * @package teachpress/core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Generate RSS feed for publications
 * @since 6.0.0
 */
function tp_pub_rss_feed_func () {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $tag = isset($_GET['tag']) ? intval($_GET['tag']) : 0;
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . esc_url($_SERVER['REQUEST_URI']);
    header("Content-Type: application/xml;");
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>' . chr(13) . chr(10);
    echo '<rss version="2.0"
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:wfw="http://wellformedweb.org/CommentAPI/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:atom="http://www.w3.org/2005/Atom"
            xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
            xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            >' . chr(13) . chr(10);
    echo '<channel>
            <title>' . get_bloginfo('name') . '</title>
            <atom:link href="' . $url . '" rel="self" type="application/rss+xml" />
            <link>' . get_bloginfo('url') . '</link>
            <description>' . get_bloginfo('description') . '</description>
            <language>' . get_bloginfo('language') . '</language>
            <sy:updatePeriod>daily</sy:updatePeriod>
            <sy:updateFrequency>1</sy:updateFrequency>
            <copyright>' . get_bloginfo('name') . '</copyright>
            <pubDate>' . date('r') . '</pubDate>
            <dc:creator>' . get_bloginfo('name') . '</dc:creator>' . chr(13) . chr(10);
    $row = tp_publications::get_publications(array('user' => $id, 'tag' => $tag, 'output_type' => ARRAY_A));
    foreach ($row as $row) {

        // prepare url
        if ( $row['url'] != '' ) {
            $new = explode(', ', $row['url']);
            $item_link = $new[0];
        } elseif ($row['rel_page'] != '') {
            $item_link = get_bloginfo('url') . '/?page=' . $row['rel_page'];
        } else {
            $item_link = get_bloginfo('url');
        }

        // prepare author name
        if ( $row['type'] === 'collection' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = str_replace(' and ', ', ', tp_html::convert_special_chars( $row['editor'] ) ) . ' (' . __('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = str_replace(' and ', ', ', tp_html::convert_special_chars( $row['author'] ) );
        }

        $row['title'] = tp_html::convert_special_chars($row['title']);
        $item_link = str_replace( array("\r\n", "\r", "\n"), ',', tp_html::convert_special_chars($item_link) );
        $item_link1 = explode(',', $item_link);
        $settings['editor_name'] = 'simple';
        $settings['style'] = 'simple';
        $settings['use_span'] = false;
        echo '
            <item>
               <title><![CDATA[' . tp_html::prepare_title($row['title'], 'replace') . ']]></title>
               <description>' . tp_html::get_publication_meta_row($row, $settings) . '</description>
               <link><![CDATA[' . $item_link1[0] . ']]></link>
               <dc:creator>' . stripslashes($all_authors) . '</dc:creator>
               <guid isPermaLink="false">' . get_bloginfo('url') . '?publication=' . $row['pub_id'] . '</guid>
               <pubDate>' . date('r', strtotime($row['date'])) . '</pubDate>
            </item>' . chr(13) . chr(10);
    }
    echo '</channel>' . chr(13) . chr(10);
    echo '</rss>';
}

/**
 * Generates the BibTeX publication feed
 * @since 6.0.0
 */
function tp_pub_bibtex_feed_func () {
    $id = isset( $_GET['id'] ) ? intval($_GET['id']) : 0;
    $tag = isset( $_GET['tag'] ) ? intval($_GET['tag']) : 0;
    $use_bibtool = isset( $_GET['use_bibtool'] ) ? true : false;
    header('Content-Type: text/plain; charset=utf-8;');
    $convert_bibtex = ( get_tp_option('convert_bibtex') == '1' ) ? true : false;
    $row = tp_publications::get_publications(array('user' => $id, 'tag' => $tag, 'output_type' => ARRAY_A));
    $result = '';
    foreach ($row as $row) {
        $tags = tp_tags::get_tags(array('pub_id' => $row['pub_id'], 'output_type' => ARRAY_A));
        // if you want to use bibtool
        if ( $use_bibtool === true ) {
            $result .= tp_bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex);
        }
        // the general way
        else {
            echo tp_bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex);
        }
    }
    if ( $use_bibtool === true ) {
        $trimmed = trim(preg_replace('/\s+/', ' ', $result));
        passthru('echo ' . escapeshellarg($trimmed) . ' | bibtool -f "%-2n(author)_%-3T(title)_%2d(year)" -q ');
    }
}

/**
 * Generates the export stream
 * @since 6.0.0
 */
function tp_export_feed_func() {
    $key = isset ( $_GET['key'] ) ? $_GET['key'] : '';
    // Export single publication
    if ( $key != '' ) {
        header('Content-Type: text/plain; charset=utf-8' );
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $key);
        header("Content-Disposition: attachment; filename=" . $filename . ".bib");
        tp_export::get_publication_by_key($key);
    }
    elseif ( is_user_logged_in() && current_user_can('use_teachpress') ) {
        $type = isset ( $_GET['type'] ) ? htmlspecialchars($_GET['type']) : '';
        $course_id = isset ( $_GET['course_id'] ) ? intval($_GET['course_id']) : 0;
        $user_id = isset ( $_GET['tp_user'] ) ? intval($_GET['tp_user']) : 0;
        $format = isset ( $_GET['tp_format'] ) ?  htmlspecialchars($_GET['tp_format']) : '';
        $sel = isset ( $_GET['tp_sel'] ) ?  htmlspecialchars($_GET['tp_sel']) : '';
        $filename = 'teachpress_course_' . $course_id . '_' . date('dmY');

        // Export courses
        if ( $type === "xls" && $course_id != 0 ) {
            header("Content-type: application/vnd-ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=" . $filename . ".xls");
            tp_export::get_course_xls($course_id);
        }

        if ( $type === 'csv' && $course_id != 0 ) {
            header('Content-Type: text/x-csv');
            header("Content-Disposition: attachment; filename=" . $filename . ".csv");
            tp_export::get_course_csv($course_id);
        }

        // Export publication lists
        if ( $type === 'pub' ) {
            $filename = 'teachpress_pub_' . date('dmY');
            $encoding = ( get_tp_option('convert_bibtex') == '1' ) ? 'Cp1252' : 'UTF-8';
            if ( $format === 'bib' ) {
                header('Content-Type: text/plain; charset=utf-8' );
                header("Content-Disposition: attachment; filename=" . $filename . ".bib");
                echo '% This file was created with teachPress ' . get_tp_version() . chr(13) . chr(10);
                echo '% Encoding: ' . $encoding . chr(13) . chr(10) . chr(13) . chr(10);
                if ( $sel == '' ) {
                    tp_export::get_publications($user_id);
                }
                else {
                    tp_export::get_selected_publications($sel);
                }
            }
            if ( $format === 'txt' ) {
                header('Content-Type: text/plain; charset=utf-8' );
                header("Content-Disposition: attachment; filename=" . $filename . ".txt");
                tp_export::get_publications($user_id,'bibtex');
            }
            if ( $format === 'rtf' ) {
                header('Content-Type: text/plain; charset=utf-8' );
                header("Content-Disposition: attachment; filename=" . $filename . ".rtf");
                tp_export::get_publications($user_id,'rtf');
            }
            if ( $format === 'rss' ) {
                if ( $user_id == 0 ) {
                    header("Location: " . home_url() . '?feed=tp_pub_rss');
                    exit;
                }
                else {
                    header("Location: " . home_url() . "?feed=tp_pub_rss&amp;id=$user_id");
                    exit;
                }
            }
        }
        else {
             // return a plain text with nothing
            header('Content-Type: text/plain; charset=utf-8' );
            return;
        }
    }
    else {
        // return a plain text with nothing
        header('Content-Type: text/plain; charset=utf-8' );
        return;
    }
}
