<?php
/**
 * This file contains all deprecated functions
 * 
 * @package teachpress\core\deprecated
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This function is deprecated. Please use tp_tags::get_tags() instead.
 * @param array $args
 * @return array|object
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tp_tags( $args = array() ) {
    trigger_error( __('get_tp_tags() is deprecated since teachpress 5.0.0. Use tp_tags::get_tags() instead.','teachpress') );
    return TP_Tags::get_tags($args);
}

/**
 * This function is deprecated. Please use tp_tags::get_tag_cloud() instead.
 * @param array $args
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tp_tag_cloud ( $args = array() ) {
    trigger_error( __('get_tp_tag_cloud() is deprecated since teachpress 5.0.0. Use tp_tags::get_tag_cloud() instead.','teachpress') );
    return TP_Tags::get_tag_cloud($args);
}

/**
 * This function is deprecated. Please use tp_publications::get_publication() instead.
 * @param int $id
 * @param string $output_type
 * @since 3.1.7
 * @return mixed
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function get_tp_publication ($id, $output_type = OBJECT) {
    trigger_error( __('get_tp_publication() is deprecated since teachpress 5.0.0. Use tp_publications::get_publication() instead.','teachpress') );
    return TP_Publications::get_publication($id, $output_type);
}

/**
 * This function is deprecated. Please use tp_publications::get_publications() instead.
 * @param array $args
 * @param boolean $count    set to true of you only need the number of rows
 * @return array|object|int
 * @since 3.1.8
 * @deprecated since version 5.0.0
 * @todo Delete function
*/
function get_tp_publications($args = array(), $count = false) {
    trigger_error( __('get_tp_publications() is deprecated since teachpress 5.0.0. Use tp_publications::get_publications() instead.','teachpress') );
    return TP_Publications::get_publications($args, $count);
}

/**
 * This function is deprecated. Please use tp_bookmarks::bookmark_exists() instead.
 * Check if an user has bookmarked a publication
 * @param int $pub_id
 * @param int $user_id
 * @return boolean
 * @since 4.0.0
 * @deprecated since version 5.0.0
 * @todo Delete function
 */
function tp_check_bookmark ($pub_id, $user_id) {
    trigger_error( __('tp_check_bookmark() is deprecated since teachpress 5.0.0. Use tp_bookmarks::bookmark_exists() instead.','teachpress') );
    return TP_Bookmarks::bookmark_exists($pub_id, $user_id);
}