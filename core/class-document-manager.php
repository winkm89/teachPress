<?php
/**
 * This file contains all functions for the document manager
 * @package teachpress\core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions for the document manager
 * @package teachpress\core\ajax
 * @since 5.0.0
 */
class tp_document_manager {
    
    /**
     * Inits the document manager
     * @param int       $course_id     The course ID
     * @param string    $mode          course or tinyMCE
     * @since 5.0.0
     */
    public static function init($course_id, $mode = 'course') {
        self::get_interface($course_id, $mode);
        self::print_scripts($course_id, $mode);
    }
    
    /**
     * Returns the init values for plupload
     * @param int       $course_id     The course ID
     * @return array
     * @since 5.0.0
     * @access private
     */
    private static function get_plupload_init_values ($course_id) {
        return array(
          'runtimes'            => 'html5,silverlight,flash,html4',
          'browse_button'       => 'plupload-browse-button',
          'container'           => 'plupload-upload-ui',
          'drop_element'        => 'drag-drop-area',
          'file_data_name'      => 'async-upload',            
          'multiple_queues'     => true,
          'max_file_size'       => wp_max_upload_size().'b',
          'url'                 => admin_url('admin-ajax.php'),
          'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
          'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
          'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
          'multipart'           => true,
          'urlstream_upload'    => true,

          'multipart_params'    => array(
            '_ajax_nonce' => wp_create_nonce('document-upload'),
            'action'      => 'tp_document_upload',
            'course_id'   => $course_id
          ),
        );
    }
    
    /**
     * Gets the interface of the document manager
     * @param int       $course_id    The course ID
     * @param string    $mode         course or tinyMCE
     * @since 5.0.0
     * @access private
     */
    private static function get_interface ($course_id, $mode) {
        ?>
        <div id="plupload-upload-ui" class="hide-if-no-js">
            <div id="drag-drop-area">
                <div class="drag-drop-inside">
                 <p class="drag-drop-info"><?php _e('Drop files here'); ?></p>
                 <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>
                 <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" /></p>
                </div>
            </div>
            <h3 id="document_headline"><?php _e('Documents','teachpress') ?></h3>
            <div id="tp_add_headline">
                <?php _e('Add headline','teachpress'); ?>
                <input id="tp_add_headline_name" name="tp_add_headline_name" type="text" value="" style="width: 400px;"/>
                <a id="tp_add_headline_button" class="button-secondary"><?php _e('Add','teachpress'); ?></a>
            </div>
            <ul class="tp_filelist" id="tp_sortable">
                <?php
                $documents = tp_documents::get_documents($course_id);
                $upload_dir = wp_upload_dir();
                foreach ($documents as $row) {
                    $class = 'tp_file tp_file_headline';
                    $style = '';
                    $size = '';
                    $checkbox = '';
                    $name = '<span class="tp_file_name">' . stripslashes($row['name']) . '</span>';
                    if ( $row['path'] !== '' ) {
                        $class = 'tp_file';
                        $style = 'background-image: url(' . get_tp_mimetype_images( $row['path'] ) . ');';
                        $size = '<span class="tp_file_size">' . tp_convert_file_size($row['size']) . '</span>';
                    }
                    if ( $mode === 'tinyMCE' && $row['path'] !== '' ) {
                        $checkbox = '<input type="checkbox" name="tp_file_checkbox[]" id="tp_file_checkbox_' . $row['doc_id'] . '" class="tp_file_checkbox" data_1="' . esc_js($row['name']) . '" data_2="' . esc_url($upload_dir['baseurl'] . $row['path']) . '" value="' . $row['doc_id'] . '" />';
                        $name = '<label class="tp_file_label" for="tp_file_checkbox_' . $row['doc_id'] . '"><span class="tp_file_name">' . stripslashes($row['name']) . '</span></label>';
                    }
                    echo '<li class="' . $class . '" id="tp_file_' . $row['doc_id'] . '" style="' . $style . '">' . $checkbox . $name . ' ' . $size . ' <span class="tp_file_actions"><a class="tp_file_view" href="' . $upload_dir['baseurl'] . $row['path'] . '" target="_blank">' . __('Show','teachpress') . '</a> | <a class="tp_file_edit" style="cursor:pointer;" document_id="' . $row['doc_id'] . '" >' . __('Edit','teachpress') . '</a> | <a class="tp_file_delete" style="cursor:pointer;" document_id="' . $row['doc_id'] . '" >' . __('Delete','teachpress') . '</a></span></li>';
                }
                ?>
            </ul>
        </div>
 
        <?php
    }
    
    /**
     * Gets the javascripts
     * @param int       $course_id    The course ID
     * @param string    $mode         course or tinyMCE
     * @since 5.0.0
     * @access private
     */
    private static function print_scripts ($course_id, $mode) {
        // we should probably not apply this filter, plugins may expect wp's media uploader...
        $plupload_init = apply_filters('plupload_init', self::get_plupload_init_values ($course_id) ); ?>

        <script type="text/javascript" charset="utf-8">
          jQuery(document).ready(function($){

            // create the uploader and pass the config from above
            var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

            // checks if browser supports drag and drop upload, makes some css adjustments if necessary
            uploader.bind('Init', function(up){
              var uploaddiv = $('#plupload-upload-ui');

              if(up.features.dragdrop){
                uploaddiv.addClass('drag-drop');
                  $('#drag-drop-area')
                    .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                    .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

              }else{
                uploaddiv.removeClass('drag-drop');
                $('#drag-drop-area').unbind('.wp-uploader');
              }
            });

            uploader.init();

            // a file was added in the queue
            uploader.bind('FilesAdded', function(up, files){
              var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

              plupload.each(files, function(file){
                if (max > hundredmb && file.size > hundredmb && up.runtime !== 'html5'){
                    // file size error?
                } 
                else {
                    $.get("<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>?mimetype_input=" + file.name, 
                    function(text){
                        <?php if ( $mode === 'tinyMCE' ) { ?>
                        $('.tp_filelist').append('<li class="tp_file" id="' + file.id + '" style="background-image: url(' + text + ');"><input type="checkbox" name="tp_file_checkbox[]" id="tp_file_checkbox_' + file.id + '" disabled="disabled" class="tp_file_checkbox" data_1="' + file.name + '" data_2="" value=""/><label class="tp_file_label" for="tp_file_checkbox_' + file.id + '"><span class="tp_file_name">' +
                        file.name + '</span></label> (<span class="tp_file_size">' + plupload.formatSize(0) + '/</span>' + plupload.formatSize(file.size) + ') ' + '<div class="tp_fileprogress"></div></li>');
                        <?php } else { ?>
                        $('.tp_filelist').append('<li class="tp_file" id="' + file.id + '" style="background-image: url(' + text + ');"><span class="tp_file_name">' +
                        file.name + '</span> (<span class="tp_file_size">' + plupload.formatSize(0) + '/</span>' + plupload.formatSize(file.size) + ') ' + '<div class="tp_fileprogress"></div></li>');
                        <?php } ?>
                        console.log(file);
                    });
                    
                }
              });

              up.refresh();
              up.start();
            });

            // while a file is uploaded
            uploader.bind('UploadProgress', function(up, file) {
                $('#' + file.id + " .tp_fileprogress").width(file.percent + "%");
                $('#' + file.id + " .tp_file_size").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });

            // a file was uploaded
            uploader.bind('FileUploaded', function(up, file, response) {
                
                // Check uploaded file info
                console.log(response.response);
                var response_splitted = response.response.split(" | ");
                response_splitted[0] = parseInt(response_splitted[0]);
                if ( isNaN( response_splitted[0] ) === true ) {
                    $('<div class="teachpress_message teachpress_message_red"><strong>' + response.response + '</strong></div>').prependTo(".wrap");
                    $('#' + file.id + " .tp_fileprogress").css( "background-color", "red" );
                    $('.teachpress_message').delay( 2400 ).fadeOut('slow');
                    return;
                }
                
                // Change DOM and update values
                $('#' + file.id + " .tp_fileprogress").width("0%");
                $('<span class="tp_file_actions"><a class="tp_file_view" href="' + response_splitted[2] + '" target="_blank"><?php _e('Show','teachpress'); ?></a> | <a class="tp_file_edit" style="cursor:pointer;" document_id="' + response_splitted[0] + '" ><?php _e('Edit','teachpress'); ?></a> | <a class="tp_file_delete" style="cursor:pointer;" document_id="' + response_splitted[0] + '" ><?php _e('Delete','teachpress'); ?></a></span>').appendTo('#' + file.id);
                $('#' + file.id).attr("id","tp_file_" + response_splitted[0]);
                $('#tp_file_checkbox_' + file.id).attr("value",response_splitted[0]);
                $('#tp_file_checkbox_' + file.id).attr("data_2",response_splitted[2]);
                $('#tp_file_checkbox_' + file.id).attr('disabled', false);
                $('#tp_file_checkbox_' + file.id).attr("id","tp_file_checkbox_" + response_splitted[0]);
                
                // Save new sort order
                var data = $('#tp_sortable').sortable('serialize');
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: '<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>'
                });
                
            });

          });  

        </script>
        
        <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($){
            // Drag & Drop sorting
            $( '#tp_sortable' ).sortable({
                placeholder: "ui-state-highlight",
                opacity:.5,
                update: function (event, ui) {
                    var data = $(this).sortable('serialize');
                    $.ajax({
                        data: data,
                        type: 'POST',
                        url: '<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>'
                    });
                } 
            });
            $( "#tp_sortable" ).disableSelection();
            
            // Add headlines
            $("#tp_add_headline_button").live("click", function() {
                var value = $("#tp_add_headline_name").val();
                if ( value !== '' ) {
                    $.get("<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>?add_document=" + value + "&course_id=<?php echo $course_id; ?>", 
                    function(new_doc_id){
                        new_doc_id = parseInt(new_doc_id);
                        $('.tp_filelist').append('<li class="tp_file tp_file_headline" id="tp_file_' + new_doc_id + '" document_id="' + new_doc_id + '"><span class="tp_file_name">' + value + '</span> ' + '</li>');
                        $('<span class="tp_file_actions"><a class="tp_file_edit" style="cursor:pointer;" document_id="' + new_doc_id + '" ><?php _e('Edit','teachpress'); ?></a> | <a class="tp_file_delete" style="cursor:pointer;" document_id="' + new_doc_id + '" ><?php _e('Delete','teachpress'); ?></a></span>').appendTo('#tp_file_' + new_doc_id);
                        $("#tp_add_headline_name").val('');
                        
                        // Save new sort order
                        var data = $('#tp_sortable').sortable('serialize');
                        $.ajax({
                            data: data,
                            type: 'POST',
                            url: '<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>'
                        });
                    });
                }
            });
            
            // Sets a cookie
            function setCookie(cname, cvalue, exdays) {
                var d = new Date();
                d.setTime(d.getTime() + (exdays*24*60*60*1000));
                var expires = "expires="+d.toUTCString();
                document.cookie = cname + "=" + cvalue + "; " + expires + "; path=<?php echo SITECOOKIEPATH; ?>";
            }

            // Gets a cookie
            function getCookie(cname) {
                var name = cname + "=";
                var ca = document.cookie.split(';');
                for(var i=0; i<ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0)===' ') c = c.substring(1);
                    if (c.indexOf(name) !== -1) return c.substring(name.length, c.length);
                }
                return "";
            }
            
            // Checkboxes for file inserts (tinyMCE Document Manager only)
            $(".tp_file_checkbox").live( "click", function() {
                var value = '';
                // var tp_saved_cookie = getCookie("teachpress_data_store");
                $(".tp_file_checkbox").each(function( index ) {
                    if ( $(this).prop('checked') ) {
                        value = value + '[name = {"' + $(this).attr("data_1") + '"}, url = {"' + $(this).attr("data_2") + '"}]:::';
                    }
                });
                setCookie("teachpress_data_store", value, 1);
            });
            
            // Edit documents: add menu
            $(".tp_file_edit").live( "click", function() {
                var document_id = $(this).attr("document_id");
                
                $.get("<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>?get_document_name=" + document_id, 
                function(text){
                    $("#tp_file_" + document_id).append('<div id="tp_file_edit_' + document_id + '"><input id="tp_file_edit_text_' + document_id + '" type="text" value="' + text + '" style="width:75%;" /><p><a class="button-primary tp_file_edit_save" document_id="' + document_id + '"><?php _e('Save'); ?></a> <a class="button-secondary tp_file_edit_cancel" document_id="' + document_id + '"><?php _e('Cancel'); ?></a></p></div>');
                });
            });
            
            // Edit documents: cancel
            $(".tp_file_edit_cancel").live( "click", function() {
                var document_id = $(this).attr("document_id");
                $("#tp_file_edit_" + document_id).remove();
            });
            
            // Edit documents: save
            $(".tp_file_edit_save").live( "click", function() {
                var document_id = $(this).attr("document_id");
                var value = $("#tp_file_edit_text_" + document_id).val();
                
                $.post( "<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>", { change_document: document_id, new_document_name: value });
                $("#tp_file_" + document_id + " .tp_file_name").text(value);
                $('#tp_file_checkbox_' + document_id).attr("data_1",value);
                $("#tp_file_edit_" + document_id).remove();
                
            });
            
            // Delete documents
            $(".tp_file_delete").live( "click", function() {
                var document_id = $(this).attr("document_id");
                $("#tp_file_" + document_id).remove().hide();
                $.get("<?php echo WP_PLUGIN_URL . '/teachpress/ajax.php' ;?>?del_document=" + document_id, 
                function(text){
                    if ( text.search('true') !== -1 ) {
                        $('<div class="teachpress_message teachpress_message_green"><strong><?php _e('Removing successful','teachpress'); ?></strong></div>').prependTo(".wrap");
                    }
                    else {
                        $('<div class="teachpress_message teachpress_message_red"><strong><?php _e('Removing failed','teachpress'); ?></strong></div>').prependTo(".wrap");
                    }
                    $('.teachpress_message').delay( 2400 ).fadeOut('slow');
                });
            });
        });
        
        </script>
        <?php
    }
}
