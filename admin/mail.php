<?php
/**
 * This file contains all functions for displaying the mail page in admin menu
 * 
 * @package teachpress\admin\courses
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Mail form
 * 
 * @since 3.0.0
 */
function tp_show_mail_page() {

    global $current_user;
    get_currentuserinfo();

    $course_id = isset( $_GET['course_id'] ) ? intval($_GET['course_id']) : 0;
    $redirect = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $student_id = isset( $_GET['student_id'] ) ? intval($_GET['student_id']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $sem = isset( $_GET['sem'] ) ? htmlspecialchars($_GET['sem']) : '';
    $single = isset( $_GET['single'] ) ? htmlspecialchars($_GET['single']) : '';
    $students_group = isset( $_GET['students_group'] ) ? htmlspecialchars($_GET['students_group']) : '';
    $limit = isset( $_GET['limit'] ) ? intval($_GET['limit']) : 0;
    $group = isset( $_GET['group'] ) ? htmlspecialchars($_GET['group']) : '';
    $waitinglist = '';

    if( !isset( $_GET['single'] ) ) {	
        // E-Mails of registered participants
        if ( $group === 'reg' ) {
            $waitinglist = 0;	
        }
        // E-Mails of participants in waitinglist
        if ( $group === 'wtl' ) {
            $waitinglist = 1;		
        }
        $mails = tp_courses::get_signups(array('output_type' => ARRAY_A, 
                                                'course_id' => $course_id, 
                                                'waitinglist' => $waitinglist ) );
    }
    ?>
    <div class="wrap">
        <?php
        if ( isset( $_GET['course_id'] ) ) {
            $return_url = "admin.php?page=teachpress/teachpress.php&amp;course_id=$course_id&amp;sem=$sem&amp;search=$search&amp;redirect=$redirect&amp;action=enrollments";
        }
        if ( isset( $_GET['student_id'] ) ) {
            $return_url = "admin.php?page=teachpress/students.php&amp;student_id=$student_id&amp;search=$search&amp;students_group=$students_group&amp;limit=$limit";
        }
        ?>
        <p><a href="<?php echo $return_url; ?>" class="button-secondary">&larr; <?php _e('Back','teachpress'); ?></a></p>
        <h2><?php _e('Writing an E-Mail','teachpress'); ?></h2>
        <form name="form_mail" method="post" action="<?php echo $return_url; ?>">
        <table class="form-table">
            <tr>
            <th scope="row" style="width: 65px;"><label for="mail_from"><?php _e('From','teachpress'); ?></label</th>
            <td>
                <select name="from" id="mail_from">
                    <option value="currentuser"><?php echo $current_user->display_name . ' (' . $current_user->user_email . ')'; ?></option>
                    <option value="wordpress"><?php echo get_bloginfo('name') . ' (' . get_bloginfo('admin_email') . ')'; ?></option>
                </select>
            </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;">
                    <select name="recipients_option" id="mail_recipients_option">
                        <option value="To"><?php _e('To','teachpress'); ?></option>
                        <option value="Bcc"><?php _e('Bcc','teachpress'); ?></option>
                    </select>
                </th>
                <td>
                    <?php
                    if( !isset( $_GET['single'] ) ) {
                        $link = "admin.php?page=teachpress/teachpress.php&amp;course_id=$course_id&amp;sem=$sem&amp;search=$search&amp;action=mail&amp;type=course";
                        if ($group == "wtl") {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachpress') . '</a> | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachpress') . '</a> | ' . __('Only waitinglist','teachpress') . '</strong><p>';
                        }
                        elseif ( $group == "reg" ) {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachpress') . '</a> | ' . __('Only participants', 'teachpress') . ' | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachpress') . '</a></strong><p>';
                        }
                        else {
                            echo '<p><strong>' . __('All', 'teachpress') . ' | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachpress') . '</a> | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachpress') . '</a></strong><p>';
                        }
                    }
                    
                    if( !isset( $_GET['single'] ) ) {
                        $to = '';
                        foreach($mails as $mail) { 
                            $to = ( $to === '' ) ? $mail["email"] : $to . ', ' . $mail["email"]; 
                        }
                    }
                    else {
                        $to = $single;
                    }
                    ?> 
                    <textarea name="recipients" id="mail_recipients" rows="3" style="width: 590px;"><?php echo $to; ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;"><label for="mail_subject"><?php _e('Subject','teachpress'); ?></label></th>
                <td><input name="subject" id="mail_subject" type="text" style="width: 580px;"/></td>
            </tr>
        </table>
        <br />
        <textarea name="text" id="mail_text" style="width: 685px;" rows="15"></textarea>
        <table>
            <tr>
                <td><input type="checkbox" name="backup_mail" id="backup_mail" title="<?php _e('Send me the e-mail as separate copy','teachpress'); ?>" value="backup" checked="checked" /></td>
                <td><label for="backup_mail"><?php _e('Send me the e-mail as separate copy','teachpress'); ?></label></td>
            </tr>
        </table>
        <br />
        <input type="submit" class="button-primary" name="send_mail" value="<?php _e('Send','teachpress'); ?>"/>
        <script type="text/javascript" charset="utf-8" src="<?php echo plugins_url(); ?>/teachpress/js/admin_mail.js"></script>
        </form>
    </div>
    <?php
}