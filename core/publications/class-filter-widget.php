<?php
/**
 * This file contains the teachPress Filter Widget class and its FSE block registration.
 *
 * @package teachpress\core\widgets
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since   9.0.13
 */


/**
 * teachPress Filter widget class
 *
 * Renders a list of publication years or tags as filter links,
 * compatible with the teachPress shortcodes [tplist], [tpcloud], [tpsearch].
 *
 * @since 9.0.13
 */
class TP_Filter_Widget extends WP_Widget {

    /**
     * Constructor
     */
    function __construct() {
        $widget_ops  = [
            'classname'   => 'widget_teachpress_filter',
            'description' => esc_html__( 'Filters a teachPress publication list by year or tag', 'teachpress' ),
        ];
        $control_ops = array( 'width' => 500, 'height' => 350 );
        parent::__construct( false, $name = esc_html__( 'teachPress Filter', 'teachpress' ), $widget_ops, $control_ops );
    }

    /**
     * Widget front-end output
     *
     * @see    WP_Widget::widget
     * @param  array $args     Display arguments (before_widget, after_widget, etc.)
     * @param  array $instance Saved widget settings
     */
    function widget( $args, $instance ) {
        $title       = apply_filters( 'widget_title', isset( $instance['title'] )       ? $instance['title']              : '' );
        $mode        = isset( $instance['mode'] )        ? $instance['mode']        : 'years';
        $target_page = isset( $instance['target_page'] ) ? $instance['target_page'] : 'self';
        $pub_type    = isset( $instance['pub_type'] )    ? $instance['pub_type']    : 'all';
        $min_pubs    = isset( $instance['min_pubs'] )    ? absint( $instance['min_pubs'] )    : 1;
        $show_active = isset( $instance['show_active'] ) ? (bool) $instance['show_active']    : true;
        $order_years = isset( $instance['order_years'] ) ? $instance['order_years'] : 'DESC';
        $order_tags  = isset( $instance['order_tags'] )  ? $instance['order_tags']  : 'relevance';

        $base_url = ( $target_page === 'self' ) ? get_permalink() : get_permalink( absint( $target_page ) );
        if ( ! $base_url ) {
            return;
        }

        $active_yr   = isset( $_GET['yr'] )   ? absint( $_GET['yr'] )   : 0;
        $active_tgid = isset( $_GET['tgid'] ) ? absint( $_GET['tgid'] ) : 0;

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }

        if ( $mode === 'years' ) {
            $this->render_years( $base_url, $pub_type, $order_years, $show_active, $active_yr );
        } else {
            $this->render_tags( $base_url, $pub_type, $min_pubs, $order_tags, $show_active, $active_tgid );
        }

        echo $args['after_widget'];
    }

    /**
     * Renders the list of publication years as filter links
     *
     * @param  string $base_url    Base URL for the filter links
     * @param  string $pub_type    'all' or a specific publication type slug
     * @param  string $order       'ASC' or 'DESC'
     * @param  bool   $show_active Highlight the currently active year
     * @param  int    $active_yr   Currently active year from the URL (?yr=)
     */
    function render_years( $base_url, $pub_type, $order, $show_active, $active_yr ) {
        global $wpdb;

        $order = ( strtoupper( $order ) === 'ASC' ) ? 'ASC' : 'DESC';

        if ( $pub_type !== 'all' ) {
            $years = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT DATE_FORMAT(date, '%%Y') AS yr
                     FROM " . TEACHPRESS_PUB . "
                     WHERE type = %s AND date != '0000-00-00'
                     ORDER BY yr $order",
                    $pub_type
                )
            );
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $years = $wpdb->get_col(
                "SELECT DISTINCT DATE_FORMAT(date, '%Y') AS yr
                 FROM " . TEACHPRESS_PUB . "
                 WHERE date != '0000-00-00'
                 ORDER BY yr $order"
            );
        }

        if ( empty( $years ) ) {
            TP_HTML::line( '<p>' . esc_html__( 'No publications found.', 'teachpress' ) . '</p>' );
            return;
        }

        TP_HTML::line( '<ul class="tp_filter_years">' );
        foreach ( $years as $year ) {
            $year      = intval( $year );
            $link      = esc_url( add_query_arg( 'yr', $year, $base_url ) );
            $is_active = ( $show_active && $active_yr === $year );
            $class     = $is_active ? ' class="active"' : '';
            $aria      = $is_active ? ' aria-current="true"' : '';
            TP_HTML::line( '<li' . $class . '><a href="' . $link . '"' . $aria . '>' . esc_html( $year ) . '</a></li>' );
        }
        TP_HTML::line( '</ul>' );
    }

    /**
     * Renders the list of tags as filter links
     *
     * The publication count used to sort by relevance and to apply the
     * $min_pubs threshold respects the $pub_type filter: if a type is
     * selected, only publications of that type are counted.
     *
     * @param  string $base_url    Base URL for the filter links
     * @param  string $pub_type    'all' or a specific publication type slug
     * @param  int    $min_pubs    Minimum number of publications required to show a tag
     * @param  string $order       'relevance' (by count DESC) or 'alpha' (A-Z)
     * @param  bool   $show_active Highlight the currently active tag
     * @param  int    $active_tgid Currently active tag ID from the URL (?tgid=)
     */
    function render_tags( $base_url, $pub_type, $min_pubs, $order, $show_active, $active_tgid ) {
        global $wpdb;

        $order_clause = ( $order === 'alpha' ) ? 't.name ASC' : 'pub_count DESC';
        $min_pubs     = max( 1, absint( $min_pubs ) );

        if ( $pub_type !== 'all' ) {
            $tags = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT t.tag_id, t.name, COUNT(r.pub_id) AS pub_count
                     FROM " . TEACHPRESS_TAGS . " t
                     INNER JOIN " . TEACHPRESS_RELATION . " r ON t.tag_id = r.tag_id
                     INNER JOIN " . TEACHPRESS_PUB . " p ON r.pub_id = p.pub_id
                     WHERE p.type = %s
                     GROUP BY t.tag_id, t.name
                     HAVING pub_count >= %d
                     ORDER BY $order_clause",
                    $pub_type,
                    $min_pubs
                )
            );
        } else {
            $tags = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT t.tag_id, t.name, COUNT(r.pub_id) AS pub_count
                     FROM " . TEACHPRESS_TAGS . " t
                     INNER JOIN " . TEACHPRESS_RELATION . " r ON t.tag_id = r.tag_id
                     GROUP BY t.tag_id, t.name
                     HAVING pub_count >= %d
                     ORDER BY $order_clause",
                    $min_pubs
                )
            );
        }

        if ( empty( $tags ) ) {
            TP_HTML::line( '<p>' . esc_html__( 'No tags found.', 'teachpress' ) . '</p>' );
            return;
        }

        TP_HTML::line( '<ul class="tp_filter_tags">' );
        foreach ( $tags as $tag ) {
            $link      = esc_url( add_query_arg( 'tgid', intval( $tag->tag_id ), $base_url ) );
            $is_active = ( $show_active && $active_tgid === intval( $tag->tag_id ) );
            $class     = $is_active ? ' class="active"' : '';
            $aria      = $is_active ? ' aria-current="true"' : '';
            TP_HTML::line( '<li' . $class . '><a href="' . $link . '"' . $aria . '>' . esc_html( stripslashes( $tag->name ) ) . '</a></li>' );
        }
        TP_HTML::line( '</ul>' );
    }

    /**
     * Update / save widget settings
     *
     * @see    WP_Widget::update
     * @param  array $new_instance Settings just submitted by the user
     * @param  array $old_instance Previously saved settings
     * @return array               Sanitised settings to store
     */
    function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title'] = sanitize_text_field( $new_instance['title'] );

        $instance['mode'] = in_array( $new_instance['mode'], array( 'years', 'tags' ), true )
            ? $new_instance['mode']
            : 'years';

        if ( isset( $new_instance['target_page'] ) && $new_instance['target_page'] === 'self' ) {
            $instance['target_page'] = 'self';
        } else {
            $page_id = absint( isset( $new_instance['target_page_page'] ) ? $new_instance['target_page_page'] : 0 );
            $instance['target_page'] = ( $page_id > 0 ) ? $page_id : 'self';
        }

        if ( isset( $new_instance['pub_type'] ) && $new_instance['pub_type'] === 'all' ) {
            $instance['pub_type'] = 'all';
        } else {
            $type_value = sanitize_key( isset( $new_instance['pub_type_value'] ) ? $new_instance['pub_type_value'] : '' );
            $instance['pub_type'] = ( $type_value !== '' ) ? $type_value : 'all';
        }

        $instance['min_pubs']    = max( 1, absint( isset( $new_instance['min_pubs'] ) ? $new_instance['min_pubs'] : 1 ) );
        $instance['show_active'] = ! empty( $new_instance['show_active'] );

        $order_years_raw         = strtoupper( isset( $new_instance['order_years'] ) ? $new_instance['order_years'] : 'DESC' );
        $instance['order_years'] = in_array( $order_years_raw, array( 'ASC', 'DESC' ), true ) ? $order_years_raw : 'DESC';

        $order_tags_raw         = isset( $new_instance['order_tags'] ) ? $new_instance['order_tags'] : 'relevance';
        $instance['order_tags'] = in_array( $order_tags_raw, array( 'relevance', 'alpha' ), true ) ? $order_tags_raw : 'relevance';

        return $instance;
    }

    /**
     * Widget admin form
     *
     * @see    WP_Widget::form
     * @param  array $instance Currently saved settings
     */
    function form( $instance ) {
        $title       = isset( $instance['title'] )       ? esc_attr( $instance['title'] )    : '';
        $mode        = isset( $instance['mode'] )        ? $instance['mode']                  : 'years';
        $target_page = isset( $instance['target_page'] ) ? $instance['target_page']           : 'self';
        $pub_type    = isset( $instance['pub_type'] )    ? $instance['pub_type']              : 'all';
        $min_pubs    = isset( $instance['min_pubs'] )    ? absint( $instance['min_pubs'] )    : 1;
        $show_active = isset( $instance['show_active'] ) ? (bool) $instance['show_active']    : true;
        $order_years = isset( $instance['order_years'] ) ? $instance['order_years']           : 'DESC';
        $order_tags  = isset( $instance['order_tags'] )  ? $instance['order_tags']            : 'relevance';

        /* ── Section 1: General ───────────────────────────────────── */
        TP_HTML::line( '<p><label for="' . $this->get_field_id( 'title' ) . '">' . esc_html__( 'Title', 'teachpress' ) . ': <input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $title . '" /></label></p>' );

        TP_HTML::line( '<p><strong>' . esc_html__( 'Mode', 'teachpress' ) . '</strong><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'mode' ) . '" value="years"' . checked( $mode, 'years', false ) . ' /> ' . esc_html__( 'Years', 'teachpress' ) . '</label>&nbsp;&nbsp;' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'mode' ) . '" value="tags"' . checked( $mode, 'tags', false ) . ' /> ' . esc_html__( 'Tags', 'teachpress' ) . '</label></p>' );

        /* ── Section 2: Target page ───────────────────────────────── */
        TP_HTML::line( '<p><strong>' . esc_html__( 'Target page', 'teachpress' ) . '</strong><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'target_page' ) . '" value="self"' . checked( $target_page, 'self', false ) . ' /> ' . esc_html__( 'Same page as widget', 'teachpress' ) . '</label></p>' );
        TP_HTML::line( '<p><label><input type="radio" name="' . $this->get_field_name( 'target_page' ) . '" value="custom"' . checked( ( $target_page !== 'self' ? 'custom' : 'self' ), 'custom', false ) . ' /> ' . esc_html__( 'Choose a page:', 'teachpress' ) . '</label>' );

        $selected_page_id = ( $target_page !== 'self' ) ? absint( $target_page ) : 0;
        wp_dropdown_pages( array(
            'name'             => $this->get_field_name( 'target_page' ) . '_page',
            'id'               => $this->get_field_id( 'target_page' ) . '_page',
            'selected'         => $selected_page_id,
            'show_option_none' => esc_html__( '— Select a page —', 'teachpress' ),
            'class'            => 'widefat',
        ) );
        TP_HTML::line( '</p>' );

        /* ── Section 3: Filters ───────────────────────────────────── */
        TP_HTML::line( '<p><strong>' . esc_html__( 'Publication filter', 'teachpress' ) . '</strong><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'pub_type' ) . '" value="all"' . checked( $pub_type, 'all', false ) . ' /> ' . esc_html__( 'All types', 'teachpress' ) . '</label><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'pub_type' ) . '" value="custom"' . checked( ( $pub_type !== 'all' ? 'custom' : 'all' ), 'custom', false ) . ' /> ' . esc_html__( 'Specific type:', 'teachpress' ) . '</label>' );
        TP_HTML::line( '<select id="' . $this->get_field_id( 'pub_type_value' ) . '" name="' . $this->get_field_name( 'pub_type_value' ) . '" class="widefat">' );
            $used_types = TP_Publications::get_used_pubtypes( array( 'output_type' => ARRAY_A ) );
            foreach ( $used_types as $row ) {
                $selected = ( $pub_type === $row['type'] ) ? ' selected="selected"' : '';
                TP_HTML::line( '<option value="' . esc_attr( $row['type'] ) . '"' . $selected . '>' . esc_html( tp_translate_pub_type( $row['type'], 'sin' ) ) . '</option>' );
            }
        TP_HTML::line( '</select></p>' );

        TP_HTML::line( '<p><label for="' . $this->get_field_id( 'min_pubs' ) . '">' . esc_html__( 'Minimum publications per tag (tags mode only)', 'teachpress' ) . ': <input class="widefat" id="' . $this->get_field_id( 'min_pubs' ) . '" name="' . $this->get_field_name( 'min_pubs' ) . '" type="number" min="1" value="' . esc_attr( $min_pubs ) . '" /></label></p>' );

        /* ── Section 4: Display ───────────────────────────────────── */
        TP_HTML::line( '<p><strong>' . esc_html__( 'Display options', 'teachpress' ) . '</strong></p>' );

        TP_HTML::line( '<p><label><input type="checkbox" id="' . $this->get_field_id( 'show_active' ) . '" name="' . $this->get_field_name( 'show_active' ) . '" value="1"' . checked( $show_active, true, false ) . ' /> ' . esc_html__( 'Highlight active filter (CSS class "active" + aria-current)', 'teachpress' ) . '</label></p>' );

        TP_HTML::line( '<p><strong>' . esc_html__( 'Year order', 'teachpress' ) . '</strong><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'order_years' ) . '" value="DESC"' . checked( $order_years, 'DESC', false ) . ' /> ' . esc_html__( 'Newest first', 'teachpress' ) . '</label>&nbsp;&nbsp;' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'order_years' ) . '" value="ASC"' . checked( $order_years, 'ASC', false ) . ' /> ' . esc_html__( 'Oldest first', 'teachpress' ) . '</label></p>' );

        TP_HTML::line( '<p><strong>' . esc_html__( 'Tag order', 'teachpress' ) . '</strong><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'order_tags' ) . '" value="relevance"' . checked( $order_tags, 'relevance', false ) . ' /> ' . esc_html__( 'By relevance (most used first)', 'teachpress' ) . '</label><br />' );
        TP_HTML::line( '<label><input type="radio" name="' . $this->get_field_name( 'order_tags' ) . '" value="alpha"' . checked( $order_tags, 'alpha', false ) . ' /> ' . esc_html__( 'Alphabetical (A → Z)', 'teachpress' ) . '</label></p>' );
    }
}


/**
 * Render callback for the teachPress Filter block (FSE / block editor).
 *
 * Delegates entirely to TP_Filter_Widget::widget() so front-end output
 * is identical whether the widget or the block is used.
 *
 * @param  array $attributes Block attributes as saved by the editor
 * @return string            Rendered HTML
 * @since  9.0.13
 */
function tp_render_filter_block( $attributes ) {
    $instance = array(
        'title'       => isset( $attributes['title'] )       ? $attributes['title']               : '',
        'mode'        => isset( $attributes['mode'] )        ? $attributes['mode']        : 'years',
        'target_page' => isset( $attributes['target_page'] ) ? $attributes['target_page'] : 'self',
        'pub_type'    => isset( $attributes['pub_type'] )    ? $attributes['pub_type']    : 'all',
        'min_pubs'    => isset( $attributes['min_pubs'] )    ? absint( $attributes['min_pubs'] )   : 1,
        'show_active' => isset( $attributes['show_active'] ) ? (bool) $attributes['show_active']   : true,
        'order_years' => isset( $attributes['order_years'] ) ? $attributes['order_years'] : 'DESC',
        'order_tags'  => isset( $attributes['order_tags'] )  ? $attributes['order_tags']  : 'relevance',
    );

    $widget = new TP_Filter_Widget();
    $args   = array(
        'before_widget' => '<div class="widget widget_teachpress_filter">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    );

    ob_start();
    $widget->widget( $args, $instance );
    return ob_get_clean();
}

/**
 * Registers the teachPress Filter block for FSE / block editor compatibility.
 *
 * Hooked to 'init' in teachpress.php.
 * The editor UI is handled by blocks/filter/index.js (vanilla ES5, no build step).
 *
 * @since 9.0.13
 */
function tp_register_filter_block() {
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

    wp_register_script(
        'tp-filter-block',
        plugins_url( 'js/tp-filter-block.js', TEACHPRESS_GLOBAL_PATH . 'teachpress.php' ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
        get_tp_version(),
        true
    );

    $used_types   = TP_Publications::get_used_pubtypes( array( 'output_type' => ARRAY_A ) );
    $type_options = array( array( 'label' => __( 'All types', 'teachpress' ), 'value' => 'all' ) );
    foreach ( $used_types as $row ) {
        $type_options[] = array(
            'label' => tp_translate_pub_type( $row['type'], 'sin' ),
            'value' => $row['type'],
        );
    }
    wp_localize_script( 'tp-filter-block', 'tpFilterBlock', array(
        'pubTypes' => $type_options,
    ) );

    register_block_type( 'teachpress/filter', array(
        'editor_script'   => 'tp-filter-block',
        'render_callback' => 'tp_render_filter_block',
        'attributes'      => array(
            'title'       => array( 'type' => 'string',  'default' => '' ),
            'mode'        => array( 'type' => 'string',  'default' => 'years' ),
            'target_page' => array( 'type' => 'string',  'default' => 'self' ),
            'pub_type'    => array( 'type' => 'string',  'default' => 'all' ),
            'min_pubs'    => array( 'type' => 'integer', 'default' => 1 ),
            'show_active' => array( 'type' => 'boolean', 'default' => true ),
            'order_years' => array( 'type' => 'string',  'default' => 'DESC' ),
            'order_tags'  => array( 'type' => 'string',  'default' => 'relevance' ),
        ),
    ) );
}
