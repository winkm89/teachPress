<?php
/**
 * This file contains the server side part for the teachpress document manager for tinyMCE
 * @package teachpress
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

// include wp-load.php
require_once( '../../../../wp-load.php' );
if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    ?>
<html>
    <head>
        <?php wp_head(); ?>
    </head>
    <body>
        <form method="post">
            Select Course
            <select name="course_id">
                <option></option>
            </select>
        </form>
    </body>
</html>
    <?php
}
