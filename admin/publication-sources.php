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
                        <input name="tp_stop" type="submit" class="button-cancel" value="<?php _e('Stop'); ?>"/>
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
}