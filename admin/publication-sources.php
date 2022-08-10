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
 * @since 9.0.0
*/ 
function tp_show_publication_sources_page() {
    if ( isset($_POST['tp_submit']) ) {        
        TP_Publication_Sources_Page::sources_actions($_POST);
    } else if ( isset($_GET['tp_stop_sched']) ) {
        // TODO
    }
    
    TP_Publication_Sources_Page::sources_tab();      
}

/**
 * This class contains functions for generating the publication sources page.
 * @since 9.0.0
 */
class TP_Publication_Sources_Page {
    /**
     * Shows the sources
     * @since 9.0.0
     * @access public
    */
    public static function sources_tab () {
        echo '<div class="wrap">';
        echo '<h2>' . __('Auto-publish','teachpress') . '</h2>';
        ?>
        <form id="tp_sources" name="tp_sources" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" method="post">
        <input type="hidden" name="page" value="teachpress/sources.php"/>
        <div class="tp_postbody">
            <div class="tp_postcontent">
                <label for="sources_area">These URLs will be periodically scanned for changes and imported</label>
                <textarea name="sources_area" id="sources_area" rows="20" style="width:100%;" title="<?php _e('Type the URLs here','teachpress'); ?>"></textarea>
            </div>
            <div class="tp_postcontent_right">
                <div class="postbox">
                    <h3 class="tp_postbox"><?php _e('Import options','teachpress'); ?></h3>
                    <div class="inside">
                        <?php 
                        // Overwrite option
                        if ( get_tp_option('import_overwrite') === '1' ) { 
                            echo TP_Admin::get_checkbox(
                                    'overwrite', 
                                    __('Update existing publications','teachpress'), 
                                    '1', 
                                    __('If the bibtex key is similar with a publication in the database, teachPress updates this publication with the import information.','teachpress'), true);
                            echo '<br/>';
                        }
                    
                        // Ignore tags option
                        echo TP_Admin::get_checkbox(
                                'ignore_tags', 
                                __('Ignore Tags','teachpress'), 
                                '0', 
                                __('Ignore tags or keywords in the import data.','teachpress'), true); ?>
                    </div>
                    <div id="major-publishing-actions" style="text-align: center;">
                        <input name="tp_submit" type="submit" class="button-primary" value="<?php _e('Schedule'); ?>"/>
                        <a class="tp_row_delete" href="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>&amp;tp_stop_sched=1">Stop auto-publish</a>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="tp_postbox"><?php _e('Data options','teachpress'); ?></h3>
                    <div class="inside">
                        <p><strong><label for="author_format_0"><?php _e('Author/Editor Format','teachpress'); ?></label></strong></p>
                        <label>
                            <input type="radio" name="author_format" value="dynamic" id="author_format_1" checked="checked" disabled="disabled"/>
                            <?php _e('Dynamic detection','teachpress');?>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="default" id="author_format_0" disabled="disabled" />
                            Firstname1 Lastname1 and Firstname2 Lastname2 and ...
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="author_format" value="lastfirst" id="author_format_1" disabled="disabled"     />
                            Lastname1, Firstname1 and Lastname2, Firstname2 and ...
                        </label>
                        <br />
                        <p><strong><label for="keyword_option"><?php _e('Keyword Separator','teachpress'); ?></label></strong></p>
                        <input type="text" name="keyword_option" id="keyword_option" title="<?php _e('Keyword Separator','teachpress'); ?>" value="," size="3" disabled="disabled" />
                    </div>
                </div>
            </div>
        </div>
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
        $sources_area = isset($post['sources_area']) ? trim($post['sources_area']) : '';
        $sources_to_monitor = array_filter(preg_split("/\r\n|\n|\r/", $sources_area), 
                                           function($k) { return strlen(trim($k)) > 0; });
        
        // overwrite the existing entries with the new ones, even if there are none
        $installed = TP_Publication_Sources_Page::install_sources($sources_to_monitor);
        
        get_tp_message( __(sprintf('Now monitoring the %d URL(s) specified.', count($installed)),'teachpress') );
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
    
}

