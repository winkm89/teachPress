<?php
/**
 * This file contains the server side part for the teachpress document manager for tinyMCE
 * @package teachpress
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  lang="de-DE">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="de-DE" style="overflow: hidden;">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>teachPress Document Manager</title>
<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var pagenow = 'toplevel_page_teachpress/teachpress',
    typenow = '',
    adminpage = 'toplevel_page_teachpress-teachpress',
    thousandsSeparator = '.',
    decimalPoint = ',',
    isRtl = 0;
</script>
<?php
// include wp-load.php
require_once( '../../../../wp-load.php' );
if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    
    // Load scripts and styles
    wp_enqueue_script(array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-resizable', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-dialog', 'plupload'));
    wp_enqueue_script('media-upload');
    add_thickbox();
    
    wp_enqueue_script('teachpress-standard', plugins_url() . '/teachpress/js/backend.js');
    wp_enqueue_style('teachpress.css', plugins_url() . '/teachpress/styles/teachpress.css');
    wp_enqueue_style('teachpress-jquery-ui.css', plugins_url() . '/teachpress/styles/jquery.ui.css');
    wp_enqueue_style('teachpress-jquery-ui-dialog.css', includes_url() . '/css/jquery-ui-dialog.min.css');
    
    do_action( 'admin_print_scripts' );
    do_action( 'admin_print_styles' );
    
    global $current_user;
    
    // Define post_id and course_id
    $post_id = ( isset($_GET['post_id']) ) ? intval($_GET['post_id']) : 0;
    $course_id = ( isset($_POST['sel_course_id']) ) ? intval($_POST['sel_course_id']) : 0;
    
    // default
    if ( $post_id !== 0 && $course_id === 0 ) {
        $course_id = intval (tp_courses::is_used_as_related_content($post_id) );
    }
    // For user's selection
    else if ( $course_id !== 0 ) {
        $post_id = tp_courses::get_course_data($course_id, 'rel_page');
    }
    
?>
<link rel="stylesheet" id="teachpress-document-manager-css"  href="<?php echo plugins_url(); ?>/teachpress/styles/teachpress_document_manager.css?ver=<?php echo get_tp_version(); ?>" type="text/css" media="all" />
</head>
<body>
    <div class="wrap">
        <form method="post">
            <div id="tp_select_course">
            <select name="sel_course_id">
                <option value="">- <?php _e('Select Course','teachpress') ?> -</option>
                <?php
                // List of courses
                $semester = get_tp_options('semester', '`setting_id` DESC');
                foreach ( $semester as $row ) {
                    $courses = tp_courses::get_courses( array('parent' => 0, 'semester' => $row->value) );
                    if ( count($courses) !== 0 ) {
                        echo '<optgroup label="' . $row->value . '">';
                    }
                    foreach ($courses as $course) {
                        $selected = ( $course_id == $course->course_id ) ? 'selected="selected"' : '';
                        echo '<option value="' . $course->course_id . '" ' . $selected . '>' . $course->name . ' (' . $course->semester . ')</option>/r/n';
                    }
                    if ( count($courses) > 0 ) {
                        echo '</optgroup>';
                    }
                }
                ?>
            </select>
                <input type="submit" name="sel_course_submit" class="button-secondary" value="<?php _e('Select','teachpress') ?>"/>
            </div>
            <?php 
            if ( $course_id !== 0 ) { 
                $capability = tp_courses::get_capability($course_id, $current_user->ID);
                // check capabilities
                if ( $capability !== 'owner' && $capability !== 'approved' ) {
                    get_tp_message(__('You have no capabilites to use this course','teachpress'), 'red');
                }
                else {
                    tp_document_manager::init($course_id, 'tinyMCE');
                }
            } 
            ?>
        </form>
    </div>
    
<?php 
    wp_footer();
} ?>  
</body>
</html>