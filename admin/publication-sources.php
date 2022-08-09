<?php

/**
 * This file contains all functions for displaying the publication sources page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */


/**
 * Add help tab for sources page
 */
function tp_import_publication_sources_help() {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_import_publication_sources_help',
        'title'     => __('Sources'),
        'content'   => '<p><strong>' . __('Publication sources') . '</strong></p>
                        <p>' . __("Additional publication sources to scan regularly.",'teachpress') . '</p>',
     ) );
}


/**
 * The controller for the import page of teachPress
 * @since 6.0.0
*/ 
function tp_show_publication_sources_page() {
    echo '<div class="wrap">';
    echo '<h2>' . __('Publication sources','teachpress') . '</h2>';
    echo '</div>';
}
