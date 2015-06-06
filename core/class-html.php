<?php
/**
 * This file contains all functions of the tp_html class
 * @package teachpress\core\html
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.10
 */

/**
 * This class contains some html formatting functions
 * @package teachpress\core\html
 * @since 5.0.10
 */
class tp_html {
    
    /**
     * Prepares a title string for normal html output. Works like htmlspecialchars_decode, but with a white list
     * @param string $input     The input string
     * @param string $mode      decode or replace
     * @return string
     * @since 5.0.10
     * @access public
     */
    public static function prepare_title ($input, $mode = 'decode') {
        $search = array('&lt;sub&gt;', '&lt;/sub&gt;',
                        '&lt;sup&gt;', '&lt;/sup&gt;',
                        '&lt;small&gt;', '&lt;/small&gt;',
                        '&lt;i&gt;', '&lt;/i&gt;',
                        '&lt;b&gt;', '&lt;/b&gt;',
                        '&lt;s&gt;', '&lt;/s&gt;',
                        '&lt;del&gt;', '&lt;/del&gt;',
                        '&lt;em&gt;', '&lt;/em&gt;',
                        '&lt;u&gt;', '&lt;/u&gt;');
        
        if ( $mode === 'decode' ) {
            $replace = array('<sub>', '</sub>', 
                             '<sup>', '</sup>',
                             '<small>', '</small>',
                             '<i>', '</i>',
                             '<b>', '</b>', 
                             '<s>', '</s>',
                             '<del>', '</del>',
                             '<em>', '</em>', 
                             '<u>', '</u>' );
        }
        else {
            $replace = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        }
        
        $output = str_replace($search, $replace, $input);
        return stripslashes($output);
    }
    
    /**
     * Prepares a text for normal html output. Works like htmlspecialchars_decode, but with a white list
     * @param string $input
     * @return string
     * @since 5.0.10
     * @access public
     */
    public static function prepare_text ($input) {
        $search = array('&lt;sub&gt;', '&lt;/sub&gt;',
                        '&lt;sup&gt;', '&lt;/sup&gt;',
                        '&lt;i&gt;', '&lt;/i&gt;',
                        '&lt;b&gt;', '&lt;/b&gt;',
                        '&lt;s&gt;', '&lt;/s&gt;',
                        '&lt;em&gt;', '&lt;/em&gt;',
                        '&lt;u&gt;', '&lt;/u&gt;',
                        '&lt;ul&gt;', '&lt;/ul&gt;', 
                        '&lt;li&gt;', '&lt;/li&gt;', 
                        '&lt;ol&gt;', '&lt;/ol&gt;' );
        $replace = array('<sub>', '</sub>', 
                         '<sup>', '</sup>',
                         '<i>', '</i>',
                         '<b>', '</b>', 
                         '<s>', '</s>',
                         '<em>', '</em>', 
                         '<u>', '</u>', 
                         '<ul>', '</ul>', 
                         '<li>', '</li>', 
                         '<ol>', '</ol>' );
        $output = str_replace($search, $replace, $input);
        return nl2br(stripslashes($output));
    }
}
