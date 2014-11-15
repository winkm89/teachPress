<?php
/**
 * This file contains the XLS and CSV constructor for courses and publications export
 * @package teachpress
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

// include wp-load.php
require_once( '../../../wp-load.php' );
$key = isset ( $_GET['key'] ) ? $_GET['key'] : '';

// Export single publication
if ($key != '') {
    header('Content-Type: text/plain; charset=utf-8' );
    $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $key);
    header("Content-Disposition: attachment; filename=" . $filename . ".bib");
    tp_export::get_publication_by_key($key); 
} 
elseif ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    $type = isset ( $_GET['type'] ) ? htmlspecialchars($_GET['type']) : '';
    $course_id = isset ( $_GET['course_id'] ) ? intval($_GET['course_id']) : 0;
    $user_id = isset ( $_POST['tp_user'] ) ? intval($_POST['tp_user']) : 0;
    $format = isset ( $_POST['tp_format'] ) ?  htmlspecialchars($_POST['tp_format']) : '';
    $sel = isset ( $_POST['tp_sel'] ) ?  htmlspecialchars($_POST['tp_sel']) : '';
    $filename = 'teachpress_course_' . $course_id . '_' . date('dmY');

    // Export courses
    if ($type === "xls" && $course_id != 0) {
        header("Content-type: application/vnd-ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        tp_export::get_course_xls($course_id);
    }

    if ($type === 'csv' && $course_id != 0) {
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
                header("Location: " . plugins_url() . "/teachpress/feed.php");
                exit;
            }
            else {
                header("Location: " . plugins_url() . "/teachpress/feed.php?id=$user_id");
                exit;
            }
        }
    }
}