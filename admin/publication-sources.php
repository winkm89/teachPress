<?php

/**
 * This file contains all functions for displaying the publication sources page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

if ( !defined('TEACHPRESS_CRON_SOURCES_HOOK') ) {
/**
 * This constant defines the hook name for cron update task.
 * @since 9.0.0
*/
    define('TEACHPRESS_CRON_SOURCES_HOOK', 'tp_sources_cron_hook');
}

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
 * Auxiliary function to get source url from dict.
 */
function tp_get_source_url($source) {
    return trim($source['src_url']);
}
    
/**
 * The controller for the import page of teachPress
 * @since 9.0.0
*/ 
function tp_show_publication_sources_page() {
    if ( isset($_POST['tp_sources_save']) ) {
        TP_Publication_Sources_Page::sources_actions($_POST);
    }
    
    TP_Publication_Sources_Page::sources_tab();      
}

/**
 * This class contains functions for generating the publication sources page.
 * @since 9.0.0
 */
class TP_Publication_Sources_Page {
    /**
     * Returns current sources.
     */
    public static function get_current_sources() {
        global $wpdb;
        $source_urls = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_MONITORED_SOURCES);
        $result = array();
        
        foreach ($source_urls as $src_url) {
            $result[] = array("src_url" => $src_url->name, "last_res" => $src_url->last_res);
        }
        
        return $result;
    }
    
    /**
     * Returns the table rows for sources rendering
     */
    public static function get_pages_rows($current_pages) {
        $result = "";
        
        $alternate = true;
        
        foreach ($current_pages as $src_url) {
            $last_res = $src_url['last_res'];
            if (strlen($last_res) == 0) {
                $last_res = __("URL not scanned yet.", "teachpress");
            }
            $result .= sprintf("<tr class='%s'><td class='tp_url'>%s</td><td>%s</td></tr>",
                               $alternate ? "alternate" : "", $src_url['src_url'], $last_res);
            $alternate = ! $alternate;
        }
        
        return $result;
    }
    
    /**
     * Shows the sources
     * @since 9.0.0
     * @access public
    */
    public static function sources_tab () {
        ?>

        <div class="wrap">
            <h2><?php echo __('Auto-publish','teachpress'); ?></h2>
            <p>The following URLs can be scanned regularly and their bibtex entries
               automatically imported if they have changed. The publication log can
               be consulted on the Import/Export page.</p>
            <form id="tp_sources" name="tp_sources"
                  action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" method="post">
                <p>
                    <label for="tp_source_freq">Update frequency:</label>

                    <select name="tp_source_freq" id="tp_source_freq">
                        <?php
                            $cur_freq = TP_Publication_Sources_Page::get_update_freq();
                            $all_freqs = array("never" => "Never (disable updates)",
                                               "hourly" => "Hourly",
                                               "twicedaily" => "Twice a day",
                                               "daily" => "Daily");
                            foreach ($all_freqs as $val => $render) {
                                print(sprintf("<option value='%s' %s>%s</option>", $val, $val == $cur_freq ? "selected='selected'" : "", $render));
                            }
                        ?>
                    </select>
                </p>
                
                <p id="tp_sources_holder">
                    <table id="tp_sources_table" class="widefat" cellspacing="0" cellpadding="0" border="0">
                        <thead>
                            <tr><td>URL</td><td>Previous update result</td></tr>
                        </thead>
                        <tbody>
                            <?php $cur_sources = TP_Publication_Sources_Page::get_current_sources();
                                   print(TP_Publication_Sources_Page::get_pages_rows($cur_sources)); ?>
                        <tbody>
                    </table>
                    <label style="display:none;" id="tp_sources_area_lbl" for="tp_sources_area">One URL per line. Start each URL with http:// or https://.</label>
                    <textarea id="tp_sources_area" name="tp_sources_area" style="width: 100%; display: none;"><?php
                              $cur_sources = TP_Publication_Sources_Page::get_current_sources();
                              print(implode(array_map('tp_get_source_url', $cur_sources), "\n"));
                    ?></textarea>
                </p>

                <p><button class="button-secondary" name="tp_edit_sources" id="tp_edit_sources"
                           type="button" onclick="teachpress_edit_sources()">Edit URL list</button>
                    <button class="button-secondary" name="tp_sources_cancel" id="tp_sources_cancel"
                    type="button" onclick="teachpress_edit_sources()" style="display: none;">Cancel</button></p>

                <p style="margin-top: 60px;"><button class="button-primary"
                   name="tp_sources_save" type="submit" >Save configuration</button></p>
            </form>
        </div>

        <?php
    }
    
    /**
     * This function executes all source publication action calls
     * @global object $current_user
     * @param array $post                   The $_POST array
     * @since 9.0.0
     * @access public
     */
    public static function sources_actions ($post) {
        $sources_area = isset($post['tp_sources_area']) ? trim($post['tp_sources_area']) : '';
        $sources_to_monitor = array_filter(preg_split("/\r\n|\n|\r/", $sources_area),
                                           function($k) { return strlen(trim($k)) > 0; });
        $new_freq = isset($post['tp_source_freq']) ? trim($post['tp_source_freq']) : 'hourly';
        
        // overwrite the existing entries with the new ones, even if there are none
        $installed = TP_Publication_Sources_Page::install_sources($sources_to_monitor);
                
        // manage cron hook
        if (count($installed) == 0 || $new_freq == 'never') {
            TP_Publication_Sources_Page::uninstall_cron();
        } else {
            TP_Publication_Sources_Page::install_cron($new_freq);
        }
        
        $new_freq = TP_Publication_Sources_Page::get_update_freq();
        get_tp_message( __(sprintf('Configuration updated with %d URL(s) at frequency "%s".',
                                   count($installed), $new_freq),'teachpress') );
    }

    /**
     * Finds the current frequency of schedule.
     * @return Current frequency, or 'never' if none scheduled.
     * @since 9.0.0
     * @access public
     */
    public static function get_update_freq() {
        $result = wp_get_schedule(TEACHPRESS_CRON_SOURCES_HOOK);
        if ($result === false) {
            $result = 'never';
        }
        return $result;
    }
            
    /**
     * This function installs monitored bibtex sources.
     * @global object $current_user
     * @param array $sources    An array of source URLs.
     * @return URLs monitored.
     * @since 9.0.0
     * @access public
     */
    public static function install_sources($sources) {
        global $wpdb;
        $result = array();
        
        // empty table first 
        $wpdb->query( "DELETE FROM " . TEACHPRESS_MONITORED_SOURCES );
        
        // write new entries -- could be done in a single statement
        foreach( $sources as $element ) {
            $element = esc_sql( trim($element) );
            $wpdb->insert(TEACHPRESS_MONITORED_SOURCES, array('name' => $element, 'md5' => 0), array('%s', '%d'));            
            $result[] = $element;
        }
        
        return $result;
    }
            
    /**
     * This function installs the cron hook.
     * @param string $freq    Frequency of cron.
     * @since 9.0.0
     * @access public
     */
    public static function install_cron($freq) {
        // install action if required
        if ( ! has_action( TEACHPRESS_CRON_SOURCES_HOOK, 'TP_Publication_Sources_Page::tp_cron_exec' ) ) {
            add_action( TEACHPRESS_CRON_SOURCES_HOOK, 'TP_Publication_Sources_Page::tp_cron_exec' );
        }
        
        // schedule hook
        if ( TP_Publication_Sources_Page::get_update_freq() != $freq && $freq != 'never' ) {
            wp_schedule_event( time(), $freq, TEACHPRESS_CRON_SOURCES_HOOK );
        }
    }

    /**
     * This function uninstalls the cron hook.
     * @since 9.0.0
     * @access public
     */
    public static function uninstall_cron() {
        $timestamp = wp_next_scheduled( TEACHPRESS_CRON_SOURCES_HOOK );
        wp_unschedule_event( $timestamp, TEACHPRESS_CRON_SOURCES_HOOK );
    }
            
    /**
     * Execute the scheduled sources update.
     * @since 9.0.0
     * @access public
     */
    public static function tp_cron_exec() {
        TP_Publication_Sources_Page::update_sources();
    }
        
    /**
     * Performs update for all sources present.
     * @since 9.0.0
     */
    public static function update_sources() {
        // list all sources
        global $wpdb;
        $source_urls = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_MONITORED_SOURCES);
        
        foreach ($source_urls as $src_url) {
            $res = TP_Publication_Sources_Page::update_source($src_url->name, $src_url->md5);
        }
    }
            
    /**
     * Performs update for a single source.
     * @param $url   The URL of the source.
     * @param previous_sig   Digest the last time the file was polled, 0 if this is the first time.
     * @return new_signature, nb_updates, status_message
     * @since 9.0.0
     */
    public static function update_source($url, $previous_sig) {
        $new_signature = '';
        $nb_updates = 0;
        $status_message = 'Unknown error.';
        
        $req = wp_remote_get($url, array('sslverify' => false));
        if (is_wp_error($req)) {
            $status_message = 'Error while retrieving URL.';
        } else {
            $code = $req["response"]["code"];
            if (!preg_match("#^2\d+$#", $code)) {
                $status_message = 'Error code while connecting to URL server.';
            } else {
                $body = wp_remote_retrieve_body($req);
                if ($body) {
                    if ($new_signature != $previous_sig) {
                        if ( TP_Bibtex::is_utf8($body) === false ) {
                            $body = utf8_encode($body);
                        }

                        $settings = array(
                            'keyword_separator' => ',',
                            'author_format'     => 'author_format_1',
                            'overwrite'         => true,
                            'ignore_tags'       => false,
                        );

                        $entries = TP_Bibtex_Import::init($body, $settings);
                        $status_message = 'Successfully read and imported.';
                        $new_signature = md5($body);
                        $nb_updates = count($entries);
                    } else {
                        $status_message = 'File unchanged.';
                        $new_signature = $previous_signature;
                    }
                } else {
                    $status_message = 'Invalid body in server response.';
                }
            }
        }
        
        return array($new_signature, $nb_updates, $status_message);
    }

}

