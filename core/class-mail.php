<?php
/**
 * This file contains all functions of teachpress mail system
 * 
 * @package teachpress\core\mail
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * teachPress E-Mail class
 * @package teachpress\core\mail
 * @since 3.0.0
 */
class TP_Mail {
     
    /**
     * Send an e-mail
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $options
     * @param string $attachments
     * @return boolean
     * @since 3.0.0
     */
    public static function sendMail($from, $to, $subject, $message, $options, $attachments = '') {
        $current_user = wp_get_current_user();
        $message = htmlspecialchars($message);

        if ( $from == '' || $message == '' ) {
            return false;
        }

        // Send mail
        // Use the normal wp_mail()
        // The "Return-Path" seems to be useless, I'm no sure why
        if ( !defined('TP_MAIL_SYSTEM') ) {
            // Prepare header attributes
            if ( $from === 'currentuser' ) {
                $headers[] = 'From: ' . $current_user->display_name . ' <' . $current_user->user_email . '>';
                $headers[] = 'Return-Path: ' . $current_user->user_email;
            }
            else {
                $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                $headers[] = 'Return-Path: ' . get_bloginfo('admin_email');
            }
            
            // Preprare header attribute: Bcc
            if ( $options['recipients'] === 'Bcc' ) {
                $headers[] = self::prepareBCC($to);
                $to = $current_user->user_email;
            }
            
            // Send backup mail
            if ( $options['backup_mail'] == 'backup' ) {
                wp_mail($current_user->user_email, $subject, $message, '', $attachments);
            }
            $ret = wp_mail($to, $subject, $message, $headers, $attachments);
        }
        /**
         * @expectedException only for TU Chemnitz servers
         */
        else {
            require_once('php/mail.inc');
            
            // Set from info
            $from = ( $from === 'currentuser' ) ? $current_user->display_name . ' <' . $current_user->user_email . '>' : get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
            
            // Set Bcc info
            if ( $options['recipients'] === 'Bcc' ) {
                $to = explode(',', $to);
            }
            
            // Send mail
            $ret = tuc_mail($to, $from, $subject, $message, '');
            
            // Display errors
            if ( $ret !== true ) {
                get_tp_message( htmlspecialchars($ret), 'red' );
            }
            else {
                // Send backup mail
                if ( $options['backup_mail'] == 'backup' ) {
                    tuc_mail($current_user->user_email, get_bloginfo('admin_email'), $subject, $message, '');
                }
            }
        }
        return $ret;
    }

    /**
     * Prepare BCC field for E-Mail header
     * @param string $recipients
     * @return string
     * @since 3.0.0
     * @access private
    */
    private static function prepareBCC($recipients) {
        $array = explode(",",$recipients);
        $bcc = '';
        foreach ($array as $recipient) {
            $recipient = trim($recipient);
            
            if ( !is_email($recipient) ) { 
                continue; 
            }
            
            if ( empty($recipient) ) {
                continue;
            }

            if ($bcc == '') {
                $bcc = 'Bcc: ' . $recipient;
            }
            else {
                $bcc = $bcc . ', ' . $recipient;
            }
            
        }
        return $bcc . "\r\n";
    }
}
