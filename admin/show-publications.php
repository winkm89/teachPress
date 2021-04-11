<?php
/**
 * This file contains all functions for displaying the show_publications page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Add screen options for show publications page
 */
function tp_show_publications_page_screen_options() {
    global $tp_admin_all_pub_page;
    global $tp_admin_your_pub_page;
    $screen = get_current_screen();
 
    if(!is_object($screen) || ( $screen->id != $tp_admin_all_pub_page && $screen->id != $tp_admin_your_pub_page ) ) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_pubs_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * Add help tab for show publications page
 */
function tp_show_publications_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_show_publications_help',
        'title'     => __('Display publications','teachpress'),
        'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                        <p>' . __('You can use publications in a page or article with the following shortcodes:','teachpress') . '</p>
                        <p>' . __('For a single publication:','teachpress') .  '<strong>[tpsingle]</strong></p>
                        <p>' . __('For a publication list with tag cloud:','teachpress') . ' <strong>[tpcloud]</strong></p>
                        <p>' . __('For normal publication lists:','teachpress') . ' <strong>[tplist]</strong></p>
                        <p><strong>' . __('More information','teachpress') . '</strong></p>
                        <p><a href="https://github.com/winkm89/teachPress/wiki#shortcodes" target="_blank" title="teachPress Shortcode Reference (engl.)">teachPress Shortcode Reference (engl.)</a></p>',
    ) );
}

/**
 * Controller for show publications page
 * @global object $current_user
 * @since 5.0.0
 */
function tp_show_publications_page() {
    // WordPress User informations
    $current_user = wp_get_current_user();
    
    // teachPress Publication Types
    global $tp_publication_types;
    
    // Get screen options
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($current_user->ID, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }

    $array_variables['checkbox'] = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
    $array_variables['action'] = isset( $_GET['action'] ) ? $_GET['action'] : '';
    $array_variables['page'] = isset( $_GET['page'] ) ? htmlspecialchars($_GET['page']) : '';
    $array_variables['type'] = ( isset( $_GET['filter'] ) && $_GET['filter'] != '0' ) ? htmlspecialchars($_GET['filter']) : '';
    $array_variables['year'] = isset( $_GET['tp_year'] ) ? intval($_GET['tp_year']) : '';
    $array_variables['search'] = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $array_variables['tag_id'] = isset( $_GET['tag'] ) ? intval($_GET['tag']) : '';
    $user = $current_user->ID;

    // Page menu
    $array_variables['per_page'] = $per_page;
    // Handle limits
    if ( isset($_GET['limit']) ) {
        $array_variables['curr_page'] = intval($_GET['limit']);
        if ( $array_variables['curr_page'] <= 0 ) {
            $array_variables['curr_page'] = 1;
        }
        $array_variables['entry_limit'] = ( $array_variables['curr_page'] - 1 ) * $per_page;
    }
     else {
        $array_variables['entry_limit'] = 0;
        $array_variables['curr_page'] = 1;
    }
    
    echo '<div class="wrap">';
    
    $publication_types = $tp_publication_types->get();
    
    // test if teachpress database is up to date
    TP_Admin::database_test();
    
    // Add a bookmark for the publication
    if ( isset( $_GET['add_id'] ) ) {
        TP_Bookmarks::add_bookmark( $_GET['add_id'], $current_user->ID );
    }
    
    // Delete bookmark for the publication
    if ( isset( $_GET['del_id'] ) ) {
        TP_Bookmarks::delete_bookmark( $_GET['del_id'] );
    }
    
    // Add a bookmark for the publication (bulk version)
    if ( $array_variables['action'] === 'add_list' ) {
        $max = count( $array_variables['checkbox'] );
        for( $i = 0; $i < $max; $i++ ) {
            $array_variables['checkbox'][$i] = intval($array_variables['checkbox'][$i]);
            $test = TP_Bookmarks::bookmark_exists($array_variables['checkbox'][$i], $current_user->ID);
            if ( $test === false ) {
                TP_Bookmarks::add_bookmark( $array_variables['checkbox'][$i], $current_user->ID );
            }
        }
        get_tp_message( __('Publications added','teachpress') );
    }
    
    // delete publications - part 2
    if ( isset($_GET['delete_ok']) ) {
        TP_Publications::delete_publications($array_variables['checkbox']);
        get_tp_message( __('Removing successful','teachpress') );
    }
    
    // Bulk edit of publications
    if ( isset($_GET['bulk_edit']) ) {
        $mass_edit = ( isset($_GET['mass_edit']) ) ? $_GET['mass_edit'] : '';
        $tags = ( isset($_GET['add_tags']) ) ? $_GET['add_tags'] : '';
        $delbox = ( isset($_GET['delbox']) ) ? $_GET['delbox'] : array();
        TP_Tags::change_tag_relations($mass_edit, $tags, $delbox);
        get_tp_message( __('Bulk edit executed','teachpress') );
    }
    
    // Show page
    if ( $array_variables['action'] === 'bibtex' ) {
        TP_Publications_Page::get_bibtex_screen($array_variables);
    }
    else {
        TP_Publications_Page::get_tab($user, $array_variables);
    }
    
    echo '</div>';
}

/**
 * This class contains all functions for the show publications screen
 * @package teachpress\admin\publications
 * @since 5.0.0
 */
class TP_Publications_Page {
    
    /**
     * bibtex mode for show publications page
     * @param array $array_variables
     * @since 5.0.0
     */
    public static function get_bibtex_screen($array_variables) {
        $convert_bibtex = ( get_tp_option('convert_bibtex') == '1' ) ? true : false;
        $sel = '';
        echo '<h2>' . __('BibTeX','teachpress') . '</h2>';
        echo '<form name="form1">';
        echo '<p><a href="admin.php?page=' . $array_variables['page'] . '&amp;search=' . $array_variables['search'] . '&amp;limit=' . $array_variables['curr_page'] . '" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
        
        echo '<textarea name="bibtex_area" rows="20" style="width:90%;" >';

        if ( $array_variables['checkbox'] != '' ) {
            $max = count ($array_variables['checkbox']);
            for ($i=0; $i < $max; $i++) {
                $pub = intval($array_variables['checkbox'][$i]);
                $row = TP_Publications::get_publication( $pub, ARRAY_A );
                $tags = TP_Tags::get_tags( array('output_type' => ARRAY_A, 'pub_id' => $pub) );
                echo TP_Bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex);
                $sel = ( $sel !== '' ) ? $sel . ',' . $pub : $pub;
            }
        }
        else {
            $row = TP_Publications::get_publications( array('output_type' => ARRAY_A) );
            foreach ( $row as $row ) {
                $tags = TP_Tags::get_tags( array('output_type' => ARRAY_A, 'pub_id' => $row['pub_id']) );
                echo TP_Bibtex::get_single_publication_bibtex($row, $tags, $convert_bibtex);
            }
        }

        echo '</textarea>';
        echo '</form>';
        echo '<script type="text/javascript">
               document.form1.bibtex_area.focus();
               document.form1.bibtex_area.select();
               </script>';
        if ( $sel != '' ) {
            echo '<form id="tp_export" method="get" action="' . home_url() . '">';
            echo '<input type="hidden" name="tp_sel" value="' . $sel . '"/>';
            echo '<input type="hidden" name="tp_format" value="bib"/>';
            echo '<input type="hidden" name="type" value="pub"/>';
            echo '<input type="hidden" name="feed" value="tp_export"/>';
            echo '<input type="submit" name="tp_submit" class="button-primary" value="' . __('Export','teachpress') . ' (.bibtex)"/>';
            echo '</form>';
        }
    }
    
    /**
    * Bulk edit screen for show publications page
    * @param array $array_variables
    * @since 5.0.0
    */
    public static function get_bulk_edit_screen($array_variables) {
        $selected_publications = '';
        $max = count($array_variables['checkbox']);
        for ( $i = 0; $i < $max; $i++ ) {
            $selected_publications = ( $selected_publications === '' ) ? $array_variables['checkbox'][$i] : $selected_publications . ',' . $array_variables['checkbox'][$i];
        }
        echo '<tr class="inline-edit-row" id="tp-inline-edit-row" style="display:table-row;">';
        echo '<td colspan="8" class="colspanchange" style="padding-bottom:7px;">';
        echo '<h4>' . __('Bulk editing','teachpress') . '</h4>';
        echo '<div id="bulk-titles" style="width:30%; float:left;">';
        echo '<ul>';
        $list = TP_Publications::get_publications( array('include' => $selected_publications, 'output_type' => ARRAY_A) );
        foreach ( $list as $row ) {
            echo '<li><input type="checkbox" name="mass_edit[]" id="mass_edit_'. $row['pub_id'] . '" value="'. $row['pub_id'] . '" checked="checked"/> <label for="mass_edit_'. $row['pub_id'] . '">'. $row['title'] . '</label></li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '<div class="tp_mass_edit_right">';
        echo '<p><b>' . __('Delete current tags','teachpress') . '</b></p>';
        $used_tags = TP_Tags::get_tags( array('pub_id' => $selected_publications, 'output_type' => ARRAY_A, 'group_by' => true) );
        $s = "'";
        echo '<p>';
        foreach ( $used_tags as $row ) {
            echo'<input name="delbox[]" type="checkbox" value="' . $row['tag_id'] . '" id="checkbox_' . $row['tag_id']. '" onclick="teachpress_change_label_color(' . $s . $row['tag_id'] . $s . ')"/> <label for="checkbox_' . $row['tag_id'] . '" title="Tag &laquo;' . $row['name'] . '&raquo; ' . __('Delete','teachpress') . '" id="tag_label_' . $row['tag_id'] . '">' . $row['name'] . '</label> | ';
        }
        echo '</p>';
        echo '<p><label for="add_tags"><b>' . __('New (separate by comma)','teachpress') . '</b></label></p> <p><input name="add_tags" id="add_tags" type="text" style="width:70%;"/></p>';
        echo '</div>';
        echo '<p class="submit inline-edit-save"><a accesskey="c" onclick="teachpress_showhide(' . $s . 'tp-inline-edit-row' . $s . ')" class="button-secondary cancel alignleft">' . __('Cancel') . '</a> <input type="submit" name="bulk_edit" id="bulk_edit" class="button button-primary alignright" value="' . __('Save') . '" accesskey="s"></p>';
        echo '</td>';
        echo '</tr>';
        ?>
        <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
          var availableTags = [
              <?php
              $sql = TP_Tags::get_tags( array('group_by' => true) );
              foreach ($sql as $row) {
                  echo '"' . $row->name . '",';        
              } ?>
          ];
          function split( val ) {
              return val.split( /,\s*/ );
          }
          function extractLast( term ) {
              return split( term ).pop();
          }

          $( "#add_tags" )
              // don't navigate away from the field on tab when selecting an item
              .bind( "keydown", function( event ) {
                  if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active ) {
                      event.preventDefault();
                  }
              })
              .autocomplete({
                  minLength: 0,
                  source: function( request, response ) {
                      // delegate back to autocomplete, but extract the last term
                      response( $.ui.autocomplete.filter(
                          availableTags, extractLast( request.term ) ) );
                  },
                  focus: function() {
                      // prevent value inserted on focus
                      return false;
                  },
                  select: function( event, ui ) {
                      var terms = split( this.value );
                      // remove the current input
                      terms.pop();
                      // add the selected item
                      terms.push( ui.item.value );
                      // add placeholder to get the comma-and-space at the end
                      terms.push( "" );
                      this.value = terms.join( ", " );
                      return false;
                  }
              });
        });
        </script>
        <?php
    }
    
    /**
     * Gets a single publication row for the main table
     * @param object $row
     * @param array $array_variables
     * @param array $bookmarks
     * @param array $tags
     * @param string $tr_class
     * @param string $get_string
     * @since 5.0.0
     * @access private
     */
    private static function get_publication_row ($row, $array_variables, $bookmarks, $tags, $tr_class, $get_string) {
        TP_HTML::line('<tr ' . $tr_class . '>');
        TP_HTML::line('<td style="font-size:20px; padding-top:8px; padding-bottom:0px; padding-right:0px;">');
        // check if the publication is already in users publication list
        $test2 = false;
        foreach ( $bookmarks as $bookmark ) {
            if ( $bookmark['pub_id'] == $row->pub_id ) {
                $test2 = $bookmark['bookmark_id'];
                break;
            }
        }
        if ( $array_variables['page'] === 'publications.php' ) {
           // Add to your own list icon
           if ($test2 === false) {
              echo '<a href="admin.php?page=' . $array_variables['page'] . '&amp;add_id='. $row->pub_id . $get_string . '" title="' . __('Add to your own list','teachpress') . '">+</a>';
           }
        }
        else {
           // Delete from your own list icon
           echo '<a href="admin.php?page=' . $array_variables['page'] .'&amp;del_id='. $test2 . $get_string . '" title="' . __('Delete from your own list','teachpress') . '">&laquo;</a>';
        }
        TP_HTML::line('</td>');
        
        $checked = '';
        if ( ( $array_variables['action'] === "delete" || $array_variables['action'] === "edit" ) && is_array($array_variables['checkbox']) ) { 
            $max = count( $array_variables['checkbox'] );
            for( $k = 0; $k < $max; $k++ ) { 
                if ( $row->pub_id == $array_variables['checkbox'][$k] ) { 
                    $checked = 'checked="checked" ';
                } 
            } 
        }
        TP_HTML::line('<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' value="' . $row->pub_id . '" /></th>');
        TP_HTML::line('<td>');
        echo '<a href="admin.php?page=teachpress/addpublications.php&amp;pub_id=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '"><strong>' . TP_HTML::prepare_title($row->title, 'decode') . '</strong></a>';
        if ( $row->status === 'forthcoming' ) {
            echo '<span class="tp_pub_label_status">' . __('Forthcoming','teachpress') . '</span>';
        }
        echo '<div class="tp_row_actions"><a href="admin.php?page=teachpress/addpublications.php&amp;pub_id=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '">' . __('Edit','teachpress') . '</a> | <a href="' . admin_url( 'admin-ajax.php' ) . '?action=teachpress&cite_id=' . $row->pub_id . '" class="teachpress_cite_pub teachpress_link">' . __('Cite', 'teachpress') . '</a> | <a class="tp_row_delete" href="admin.php?page=' . $array_variables['page']  .'&amp;checkbox%5B%5D=' . $row->pub_id . '&amp;action=delete' . $get_string . '" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a></div>';
        TP_HTML::line('</td>');
        TP_HTML::line('<td>' . $row->pub_id . '</td>');
        TP_HTML::line('<td>' . tp_translate_pub_type($row->type) . '</td>');
        if ( $row->type === 'collection' || ( $row->author === '' && $row->editor !== '' ) ) {
            TP_HTML::line('<td>' . TP_Bibtex::parse_author_simple($row->editor) . ' (' . __('Ed.','teachpress') . ')</td>');
        }
        else {
            TP_HTML::line('<td>' . TP_Bibtex::parse_author_simple($row->author) . '</td>');
        }
        TP_HTML::line('<td>');
        echo TP_Publications_Page::get_tags_for_single_row($row->pub_id, $tags, $array_variables);
        TP_HTML::line('</td>');
        TP_HTML::line('<td>' . $row->year . '</td>');
        TP_HTML::line('</tr>');
        
    }
    
    /**
     * Returns the tags for a publication
     * @param int $pub_id
     * @param array $tags
     * @param array $array_variables
     * @since 5.0.0
     * @access private
     */
    private static function get_tags_for_single_row ($pub_id, $tags, $array_variables) {
        $tag_string = '';
        foreach ($tags as $temp) {
            if ($temp["pub_id"] != $pub_id) {
               continue;
            }
            if ($temp["tag_id"] == $array_variables['tag_id']) {
               $tag_string .= '<a href="admin.php?page=' . $array_variables['page']  . '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;limit=' . $array_variables['curr_page'] . '&amp;tp_year=' . $array_variables['year'] . '" title="' . __('Delete tag as filter','teachpress') . '"><strong>' . stripslashes($temp["name"]) . '</strong></a>, ';
            }
            else {
               $tag_string .= '<a href="admin.php?page=' . $array_variables['page']  . '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $temp["tag_id"] . '&amp;tp_year=' . $array_variables['year'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($temp["name"]) . '</a>, ';
            }
        }
        return substr($tag_string, 0, -2);
    }
    
    /**
     * Gets the filter box for publication tags
     * @param array $array_variables    An associative array
     * @param int $user                 The ueser ID
     * @since 5.0.0
     * @access private
     */
    private static function get_tag_filter ($array_variables, $user) {
        $array_tags = TP_Tags::get_tags( array(
                        'user' => ($array_variables['page'] == 'publications.php') ? '' : $user, 
                        'group_by' => true, 
                        'order' => 'ASC' ) );
        TP_HTML::line('<select name="tag">');
        TP_HTML::line('<option value="0">- ' . __('All tags','teachpress') . ' -</option>');      
        foreach ( $array_tags as $row ) {
            $selected = ( $array_variables['tag_id'] == $row->tag_id ) ? 'selected="selected"' : '';
            TP_HTML::line('<option value="' . $row->tag_id . '" ' . $selected . '>' . $row->name . '</option>');
        }      
        TP_HTML::line('</select>');
    }
    
    /**
     * Gets the filter box for publication types
     * @param array $array_variables    An associative array
     * @param int $user                 The ueser ID
     * @since 5.0.0
     * @access private
     */
    private static function get_type_filter ($array_variables, $user) {
        $array_types = TP_Publications::get_used_pubtypes( array(
             'user' => ($array_variables['page'] == 'publications.php') ? '' : $user ) );
        
        TP_HTML::line('<select name="filter">');
        TP_HTML::line('<option value="0">- ' . __('All types','teachpress') . ' -</option>');
        foreach ( $array_types as $row ) {
            $selected = ( $array_variables['type'] === $row['type'] ) ? 'selected="selected"' : '';
            TP_HTML::line('<option value="' . $row['type'] . '" ' . $selected . '>' . tp_translate_pub_type($row['type'],'pl') . '</option>');
        }
        TP_HTML::line('</select>');    
    }
    
    /**
     * Gets the filter box for publication years
     * @param array $array_variables    An associative array
     * @param int $user                 The ueser ID
     * @since 5.0.0
     * @access private
     */
    private static function get_year_filter ($array_variables, $user) {
        $array_years = TP_Publications::get_years( array(
            'order' => 'DESC', 
            'user' => ($array_variables['page'] == 'publications.php') ? '' : $user) );
        
        TP_HTML::line('<select name="tp_year">');
        TP_HTML::line('<option value="0">- ' . __('All years','teachpress') . ' -</option>');
        foreach ( $array_years as $row ) {
            $selected = ( $array_variables['year'] == $row->year ) ? 'selected="selected"' : '';
            TP_HTML::line('<option value="' . $row->year . '" ' . $selected . '>' . $row->year . '</option>');
        }        
        TP_HTML::line('</select>');
    }
   
    /**
     * Show publications main screen
     * @param int $user
     * @param array $array_variables
     * @since 5.0.0
     */
    public static function get_tab($user, $array_variables) {
        $title = ($array_variables['page'] == 'publications.php' && $array_variables['search'] == '') ? __('All publications','teachpress') : __('Your publications','teachpress');
        TP_HTML::line('<h2>' . $title . '<a href="admin.php?page=teachpress/addpublications.php" class="add-new-h2">' . __('Add new','teachpress') . '</a></h2>');
        TP_HTML::line('<form id="show_publications_form" name="form1" method="get" action="admin.php">');
        TP_HTML::line('<input type="hidden" name="page" id="page" value="' . $array_variables['page'] . '" />');
        TP_HTML::line('<input type="hidden" name="tag" id="tag" value="' . $array_variables['tag_id'] . '" />');

        // Delete publications - part 1
        if ( $array_variables['action'] == "delete" ) {
            TP_HTML::line('<div class="teachpress_message">
                  <p class="teachpress_message_headline">' . __('Do you want to delete the selected items?','teachpress') . '</p>
                  <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
                  <a href="admin.php?page=publications.php&search=' . $array_variables['search'] . '&amp;limit=' . $array_variables['curr_page'] . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
                  </div>');
        }
        
        $args = array('search' => $array_variables['search'],
                      'user' => ($array_variables['page'] == 'publications.php') ? '' : $user,
                      'tag' => $array_variables['tag_id'],
                      'year' => $array_variables['year'],
                      'limit' => $array_variables['entry_limit'] . ',' .  $array_variables['per_page'],
                      'type' => $array_variables['type'],
                      'order' => 'date DESC, title ASC'
                     );
        $test = TP_Publications::get_publications($args, true);
        
        // Load tags
        $tags = TP_Tags::get_tags( array('output_type' => ARRAY_A) );
        
        // Load bookmarks
        $bookmarks = TP_Bookmarks::get_bookmarks( array('user'=> $user, 'output_type' => ARRAY_A) );
        
        // Searchbox
        TP_HTML::line('<div id="tp_searchbox">');
        if ( $array_variables['search'] != '' ) { 
              TP_HTML::line( '<a href="admin.php?page=' . $array_variables['page'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $array_variables['tag_id'] . '&amp;tp_year=' . $array_variables['year'] . '" class="tp_search_cancel" title="' . __('Cancel the search','teachpress') . '">X</a>');
           } 
        TP_HTML::line('<input type="search" name="search" id="pub_search_field" value="' . stripslashes($array_variables['search']) . '"/>');
        TP_HTML::line('<input type="submit" name="pub_search_button" id="pub_search_button" value="' . __('Search','teachpress') . '" class="button-secondary"/>');
        TP_HTML::div_close('tp_searchbox');
        
        // Actions
        TP_HTML::line('<div class="tablenav" style="padding-bottom:5px;">');
        TP_HTML::div_open('alignleft actions');
        TP_HTML::line('<select name="action">');
        TP_HTML::line('<option value="0">- ' . __('Bulk actions','teachpress') . ' -</option>');
        TP_HTML::line('<option value="edit">' . __('Edit','teachpress') . '</option>');
        TP_HTML::line('<option value="bibtex">' . __('Show as BibTeX entry','teachpress') . '</option>');
        if ( $array_variables['page'] === 'publications.php' ) {
            TP_HTML::line('<option value="add_list">' . __('Add to your own list','teachpress') . '</option>');
            TP_HTML::line('<option value="delete">' . __('Delete','teachpress') . '</option>');
        }
        TP_HTML::line('</select>');
        TP_HTML::line('<input name="ok" id="doaction" value="' . __('OK','teachpress') . '" type="submit" class="button-secondary"/>');
        
        TP_HTML::div_close('alignleft actions');
        
        // Filters
        TP_HTML::div_open('alignleft actions');
        TP_Publications_Page::get_type_filter($array_variables, $user);
        TP_Publications_Page::get_year_filter($array_variables, $user);
        TP_Publications_Page::get_tag_filter($array_variables, $user);
        TP_HTML::line('<input name="filter-ok" value="' . __('Limit selection','teachpress') . '" type="submit" class="button-secondary"/>');
        TP_HTML::div_close('alignleft actions');
           
        // Page Menu
        $link = 'search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $array_variables['tag_id'];
        echo tp_page_menu(array('number_entries' => $test,
                                'entries_per_page' => $array_variables['per_page'],
                                'current_page' => $array_variables['curr_page'],
                                'entry_limit' => $array_variables['entry_limit'],
                                'page_link' => 'admin.php?page=' . $array_variables['page'] . '&amp;',
                                'link_attributes' => $link));
        TP_HTML::div_close('tablenav');
        
        // Publication table
        TP_HTML::line('<table class="widefat">');
        TP_HTML::line('<thead>');
        TP_HTML::line('<tr>');
        TP_HTML::line('<th>&nbsp;</th>');
        TP_HTML::line('<td class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes(' . "'checkbox', 'tp_check_all'" . ');" /></td>');
        TP_HTML::line('<th>' . __('Title','teachpress') . '</th>');
        TP_HTML::line('<th>' . __('ID') . '</th>');
        TP_HTML::line('<th>' . __('Type') . '</th>');
        TP_HTML::line('<th>' . __('Author(s)','teachpress') . '</th>');
        TP_HTML::line('<th>' . __('Tags') . '</th>');
        TP_HTML::line('<th>' . __('Year','teachpress') . '</th>');
        TP_HTML::line('</tr>');
        TP_HTML::line('</thead>');
        TP_HTML::line('<tbody>');
        
        // Bulk edit
        if ( $array_variables['action'] === 'edit' && $array_variables['checkbox'] !== '' ) {
            TP_Publications_Page::get_bulk_edit_screen($array_variables);
        }

        if ($test === 0) {
            TP_HTML::line('<tr><td colspan="7"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>');
        }

        else {
            $row = TP_Publications::get_publications($args);
            $class_alternate = true;
            $get_string = '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;limit=' . $array_variables['curr_page'] . '&amp;site=' . $array_variables['page'] . '&amp;tag=' . $array_variables['tag_id'] . '&amp;tp_year=' . $array_variables['year'];
            foreach ($row as $row) { 
                if ( $class_alternate === true ) {
                    $tr_class = 'class="alternate"';
                    $class_alternate = false;
                }
                else {
                    $tr_class = '';
                    $class_alternate = true;
                }
                TP_Publications_Page::get_publication_row($row, $array_variables, $bookmarks, $tags, $tr_class, $get_string);
            }
        }
        TP_HTML::line('</tbody>');
        TP_HTML::line('<table>');
        
        TP_HTML::line('<div class="tablenav"><div class="tablenav-pages" style="float:right;">');
        
        if ( $test > $array_variables['per_page'] ) {
            echo tp_page_menu(array('number_entries' => $test,
                                    'entries_per_page' => $array_variables['per_page'],
                                    'current_page' => $array_variables['curr_page'],
                                    'entry_limit' => $array_variables['entry_limit'],
                                    'page_link' => 'admin.php?page=' . $array_variables['page'] . '&amp;',
                                    'link_attributes' => $link,
                                    'mode' => 'bottom'));
        } 
        else {
            if ($test === 1) {
               echo "$test " . __('entry','teachpress');
            }
            else {
               echo "$test " . __('entries','teachpress');
            }
        }
     
        TP_HTML::line( '</div></div>' );
        
        // print_scripts
        TP_Publications_Page::print_scripts();
        
        TP_HTML::line( '</form>' );
    } 
    
    /**
     * Prints the js scripts
     * @since 6.0.0
     */
    public static function print_scripts () {
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($){
                // Start cite publication window
                $(".teachpress_cite_pub").each(function() {
                    var $link = $(this);
                    var $dialog = $('<div></div>')
                        .load($link.attr('href') + ' #content')
                        .dialog({
                                autoOpen: false,
                                title: '<?php _e('Cite publication','teachpress'); ?>',
                                width: 600
                        });
                        
                    $link.click(function() {
                        $dialog.dialog('open');
                        $('.tp_cite_full').focus();
                        $('.tp_cite_full').select();
                        return false;
                    });
                    
                });
                
                // bibtex button in the cite publication window 
                $("body").on('click','.tp_cite_bibtex', function() {
                    var pub_id = $(this).attr("pub_id");
                    $.get("<?php echo admin_url( 'admin-ajax.php' ) ;?>?action=teachpress&cite_pub=" + pub_id + "&cite_type=bibtex", 
                    function(text){
                        $("#tp_cite_full_" + pub_id).text(text);
                        $("#tp_cite_full_" + pub_id).select();
                        $("#tp_cite_bibtex_" + pub_id).addClass("nav-tab-active");
                        $("#tp_cite_text_" + pub_id).removeClass("nav-tab-active");
                    });
                });
                
                // text button in the cite publication window 
                $("body").on('click','.tp_cite_text',function() {
                    var pub_id = $(this).attr("pub_id");
                    $.get("<?php echo admin_url( 'admin-ajax.php' ) ;?>?action=teachpress&cite_pub=" + pub_id + "&cite_type=text", 
                    function(text){
                        $("#tp_cite_full_" + pub_id).text(text);
                        $("#tp_cite_full_" + pub_id).select();
                        $("#tp_cite_text_" + pub_id).addClass("nav-tab-active");
                        $("#tp_cite_bibtex_" + pub_id).removeClass("nav-tab-active");
                    });
                });
            });
        </script>
        <?php
    }
    
}