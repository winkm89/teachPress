<?php
/**
 * This file contains the widget class
 * 
 * @package teachpress\core\widgets
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */


/** 
 * teachPress Books widget class 
 * @since 0.40.0
 */
class TP_Books_Widget extends WP_Widget {
    /** 
     * constructor 
     */
    function __construct() {
        $widget_ops = [
            'classname'     => 'widget_teachpress_books', 
            'description'   => esc_html__('Shows a random book in the sidebar', 'teachpress') 
        ];
        $control_ops = array('width' => 500, 'height' => 300);
        parent::__construct(false, $name = esc_html__('teachPress books','teachpress'), $widget_ops, $control_ops);
    }

    /** 
     * Widget content area
     * @see WP_Widget::widget 
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $all_url = get_permalink($instance['url']);
        $books = $instance['books'];
        $random_id = rand(0, count($books) - 1);
        $pub_id = $books[$random_id];
        $row = TP_Publications::get_publication($pub_id);
        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        TP_HTML::line( '<p style="text-align:center"><a href="' . get_permalink($row->rel_page) . '" title="' . $row->title . '"><img class="tp_image" src="' . $row->image_url . '" alt="' . $row->title . '" title="' . $row->title . '" /></a></p>');
        TP_HTML::line( '<p style="text-align:center" class="tp_allbooks_link"><a href="' . $all_url . '" title="' . esc_html__('All books','teachpress') . '">' . esc_html__('All books','teachpress') . '</a></p>' );
        echo $after_widget;
    }

    /** 
     * Update values
     * @see WP_Widget::update 
     * @param array $new_instance
     * @param array $old_instance
     */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** 
     * Widget admin area
     * @see WP_Widget::form 
     * @param array $instance
     */
    function form($instance) {	
        $title = isset ($instance['title']) ? esc_attr($instance['title']) : '';
        $url = isset ($instance['url']) ? esc_attr($instance['url']) : '';
        $books = isset ($instance['books']) ? $instance['books'] : array();
        TP_HTML::line( '<p><label for="' . $this->get_field_id('title') . '">' . esc_html__('Title', 'teachpress') . ': <input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>' );

        TP_HTML::line( '<p><label for="' . $this->get_field_id('books') . '">' . esc_html__('Books', 'teachpress') . ': <select class="widefat" id="' . $this->get_field_id('books') . '" name="' . $this->get_field_name('books') . '[]" style="height:auto; max-height:25em" multiple="multiple" size="10">' );

        $row = TP_Publications::get_publications( array('type' => 'book', 'order' => 'title DESC') );
        foreach ($row as $row) {
            $selected = ( in_array($row->pub_id, $books) ) ? '" selected="selected"' : '';
            TP_HTML::line( '<option value="' . $row->pub_id . '" ' . $selected . '>(ID: ' . $row->pub_id . ') ' . $row->title . ' </option>' );  
        }
        echo '</select></label><small class="setting-description">' . esc_html__('use &lt;Ctrl&gt; key to select multiple books', 'teachpress') . '</small></p>';

        TP_HTML::line( '<p><label for="' . $this->get_field_id('url') . '">' . esc_html__('Releated Page for &laquo;all books&raquo; link:', 'teachpress') . ' <select class="widefat" id="' . $this->get_field_id('url') . '" name="' . $this->get_field_name('url') . '>' );
        echo '<option value="">' . esc_html__('none','teachpress') . '</option>';

        $post_type = get_tp_option('rel_page_publications');
        get_tp_wp_pages("menu_order","ASC",$url,$post_type,0,0);
            echo '</select></label></p>';
    }
}
