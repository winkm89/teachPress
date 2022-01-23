<?php 
/**
 * This file contains all functions for displaying the add_publication page in admin menu
 * 
 * @package teachpress\admin\publications
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * Add help tab for add new courses page
 */
function tp_add_publication_page_help () {
    $screen = get_current_screen();
    $screen->add_help_tab( array(
        'id'        => 'tp_add_publication_help',
        'title'     => __('Create a new publication','teachpress'),
        'content'   => '<p><b>' . __('Required fields','teachpress') . '</b></p>
                        <p>' . __('The required fields for a new publication:','teachpress') . ' <b>' .  __('title, author, bibtex key, tags','teachpress') . '</b></p>
                        <p><b>' . __('URL/Files','teachpress') . '</b></p>
                        <p>' . __('You can add one URL or file per line. Insert the name of the URL/file behind the address and separate it by a comma and a space. Example:', 'teachpress') . '<br />http://mywebsite.com/docs/readme.pdf, Basic Instructions</p>'
    ) );
} 

/** 
 * New publication / edit publication
 * from show_publications.php (GET):
 * @param int $pub_id       publication ID
 * @param string $search    for a return to the search
 * @param string $filter    for a return to the search
 * @param string $tag       for a return to the search
 * @param string $tp_year      for a return to the search
 * @param string $site      for a return to the search
 * @param string $limit      for a return to the search
 * @since 5.0.0
*/
function tp_add_publication_page() {
   
    // WordPress current unser info
    $current_user = wp_get_current_user();
    $user = $current_user->ID;
    $fields = get_tp_options('teachpress_pub','`setting_id` ASC', ARRAY_A);

    // form variables from add_publication.php
    $data = get_tp_var_types('publication_array');
    $data['title'] = isset( $_POST['tp_post_title'] ) ? htmlspecialchars($_POST['tp_post_title']) : '';
    $data['type'] = isset( $_POST['type'] ) ? htmlspecialchars($_POST['type']) : '';
    $data['bibtex'] = isset( $_POST['bibtex'] ) ? htmlspecialchars($_POST['bibtex']) : '';
    $data['author'] = isset( $_POST['author'] ) ? htmlspecialchars($_POST['author']) : '';
    $data['editor'] = isset( $_POST['editor'] ) ? htmlspecialchars($_POST['editor']) : '';
    $data['isbn'] = isset( $_POST['isbn'] ) ? htmlspecialchars($_POST['isbn']) : '';
    $data['url'] = isset( $_POST['url'] ) ? htmlspecialchars($_POST['url']) : '';
    $data['date'] = isset( $_POST['pubdate'] ) ? htmlspecialchars($_POST['pubdate']) : '';
    $data['status'] = isset( $_POST['forthcoming'] ) ? 'forthcoming' : 'published';
    $data['urldate'] = isset( $_POST['urldate'] ) ? htmlspecialchars($_POST['urldate']) : '';
    $data['booktitle'] = isset( $_POST['booktitle'] ) ? htmlspecialchars($_POST['booktitle']) : '';
    $data['issuetitle'] = isset( $_POST['issuetitle'] ) ? htmlspecialchars($_POST['issuetitle']) : '';
    $data['journal'] = isset( $_POST['journal'] ) ? htmlspecialchars($_POST['journal']) : '';
    $data['volume'] = isset( $_POST['volume'] ) ? htmlspecialchars($_POST['volume']) : '';
    $data['number'] = isset( $_POST['number'] ) ? htmlspecialchars($_POST['number']) : '';
    $data['pages'] = isset( $_POST['pages'] ) ? htmlspecialchars($_POST['pages']) : '';
    $data['publisher'] = isset( $_POST['publisher'] ) ? htmlspecialchars($_POST['publisher']) : '';
    $data['address'] = isset( $_POST['address'] ) ? htmlspecialchars($_POST['address']) : '';
    $data['edition'] = isset( $_POST['edition'] ) ? htmlspecialchars($_POST['edition']) : '';
    $data['chapter'] = isset( $_POST['chapter'] ) ? htmlspecialchars($_POST['chapter']) : '';
    $data['institution'] = isset( $_POST['institution'] ) ? htmlspecialchars($_POST['institution']) : '';
    $data['organization'] = isset( $_POST['organization'] ) ? htmlspecialchars($_POST['organization']) : '';
    $data['school'] = isset( $_POST['school'] ) ? htmlspecialchars($_POST['school']) : '';
    $data['series'] = isset( $_POST['series'] ) ? htmlspecialchars($_POST['series']) : '';
    $data['crossref'] = isset( $_POST['crossref'] ) ? htmlspecialchars($_POST['crossref']) : '';
    $data['abstract'] = isset( $_POST['abstract'] ) ? htmlspecialchars($_POST['abstract']) : '';
    $data['howpublished'] = isset( $_POST['howpublished'] ) ? htmlspecialchars($_POST['howpublished']) : '';
    $data['key'] = isset( $_POST['key'] ) ? htmlspecialchars($_POST['key']) : '';
    $data['techtype'] = isset( $_POST['techtype'] ) ? htmlspecialchars($_POST['techtype']) : '';
    $data['comment'] = isset( $_POST['comment'] ) ? htmlspecialchars($_POST['comment']) : '';
    $data['note'] = isset( $_POST['note'] ) ? htmlspecialchars($_POST['note']) : '';
    $data['image_url'] = isset( $_POST['image_url'] ) ? htmlspecialchars($_POST['image_url']) : '';
    $data['image_target'] = isset( $_POST['image_target'] ) ? htmlspecialchars($_POST['image_target']) : '';
    $data['image_ext'] = isset( $_POST['image_ext'] ) ? htmlspecialchars($_POST['image_ext']) : '';
    $data['doi'] = isset( $_POST['doi'] ) ? htmlspecialchars($_POST['doi']) : '';
    $data['rel_page'] = isset( $_POST['rel_page'] ) ? intval($_POST['rel_page']) : '';
    $data['is_isbn'] = isset( $_POST['is_isbn'] ) ? intval($_POST['is_isbn']) : '';

    $tags = isset( $_POST['tags'] ) ? TP_Publication_Page::prepare_tags($_POST['tags']) : '';
    $delbox = isset( $_POST['delbox'] ) ? $_POST['delbox'] : '';
    $new_bookmarks = isset( $_POST['new_bookmarks'] ) ? $_POST['new_bookmarks'] : '';
    $del_bookmarks = isset( $_POST['del_bookmarks'] ) ? $_POST['del_bookmarks'] : '';

    // from show_publications.php
    $pub_id = isset( $_REQUEST['pub_id'] ) ? intval($_REQUEST['pub_id']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
    $site = isset( $_GET['site'] ) ? htmlspecialchars($_GET['site']) : '';
    $tag_id = isset( $_GET['tag'] ) ? htmlspecialchars($_GET['tag']) : '';
    $year = isset( $_GET['tp_year'] ) ? intval($_GET['tp_year']) : '';
    $entry_limit = isset( $_GET['limit'] ) ? htmlspecialchars($_GET['limit']) : '';

    echo '<div class="wrap">';
    
    // headline
    if ( $pub_id === 0 ) {
        echo '<h2>' . __('Add a new publication','teachpress') . '</h2>';
    }
    else {
        echo '<h2>' . __('Edit publication','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php" class="add-new-h2">' . __('Create','teachpress') . '</a></h2>';
    }
    
    
    
    echo '<form name="form1" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '" id="form1">';
   
    // create related content (post/page/...)
    if ( isset($_POST['create_rel_content']) ) {
        $data['rel_page'] = tp_add_publication_as_post( $data['title'], $data['bibtex'], $data['date'], get_tp_option('rel_page_publications'), $tags, array(get_tp_option('rel_content_category')) );
    }
    
    // create publication and related page
    if ( isset($_POST['create_pub']) ) {
        $pub_id = TP_Publications::add_publication($data, $tags, $new_bookmark);
        TP_DB_Helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        $message = __('Publication added','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php">' . __('Add new','teachpress') . '</a>';
        get_tp_message($message);
    }
    
    // save publication
    if ( isset($_POST['speichern']) ) {
        TP_Publications::delete_pub_meta($pub_id);
        TP_Publications::change_publication($pub_id, $data, $tags, $delbox, $new_bookmarks, $del_bookmarks);
        TP_DB_Helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        get_tp_message( __('Saved') );
    }
    
    // Default values
    if ( $pub_id != 0 ) {
        $pub_data = TP_Publications::get_publication($pub_id, ARRAY_A);
        $pub_meta = TP_Publications::get_pub_meta($pub_id);
    }
    else {
        $pub_data = get_tp_var_types('publication_array');
        $pub_meta = array ( array('meta_key' => '', 'meta_value' => '') );
    }

    // Check format of author/editor field
    if ( $pub_id != 0 && !isset($_POST['create_pub']) ) {
        $check = ( strpos($pub_data['author'], ',') !== false || strpos($pub_data['editor'], ',') !== false) ? true : false;
        if ( $check === true ) {
            get_tp_message( __('Please check the format of author/editor information and correct it to the following format: firstname1 lastname1 and firstname2 lastname 2. Example: Adam Smith and John M. Keynes','teachpress') , 'orange');
        }
    }
    
    // input fields
    echo '<input name="page" type="hidden" value="teachpress/addpublications.php">';
    if ($pub_id != 0) {
        echo '<input type="hidden" name="pub_id" value="' . $pub_id . '" />';
        echo '<input type="hidden" name="search" value="' . stripslashes($search) . '" />';
        echo '<input type="hidden" name="limit" id="limit" value="' . $entry_limit . '" />';
        echo '<input type="hidden" name="site" id="site" value="' . $site . '" />';
        echo '<input type="hidden" name="filter" id="filter" value="' . $filter . '" />';
        echo '<input type="hidden" name="tag" id="tag" value="' . $tag_id . '" />';
        echo '<input type="hidden" name="tp_year" id="tp_year" value="' . $year . '" />';
    }
    
    echo '<div class="tp_postbody">';
    
    echo '<div class="tp_postcontent">';
    echo '<div id="post-body">';
    echo '<div id="post-body-content">';
    
    echo '<div id="titlediv" style="padding-bottom: 15px;">';
    echo '<div id="titlewrap">';
    echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">' . __('Title','teachpress') . '</label>';
    echo '<input type="text" name="tp_post_title" size="30" title="' . __('Title','teachpress') . '" tabindex="1" value="' . stripslashes($pub_data["title"]) . '" id="title" placeholder="' . __('Title','teachpress') . '" autocomplete="off" />';
    echo '</div>';
    echo '</div>';
    
    TP_Publication_Page::get_general_box ($pub_id, $pub_data);
    TP_Publication_Page::get_main_box ($pub_id, $pub_data);
    TP_Publication_Page::get_comments_box ($pub_data);
    if ( count($fields) !== 0 ) { 
        TP_Admin::display_meta_data($fields, $pub_meta);       
    } 
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    TP_HTML::div_open('tp_postcontent_right');
    TP_Publication_Page::get_publication_box($pub_id);
    TP_Publication_Page::get_bookmarks_box ($pub_id, $user);
    TP_Publication_Page::get_tags_box ($pub_id);
    TP_Publication_Page::get_image_box ($pub_data);
    TP_Publication_Page::get_rel_page_box ($pub_data);
    TP_HTML::div_close('tp_postcontent_right');
    
    echo '</form>';
    TP_Publication_Page::print_scripts();
    echo '</div>';
}

/**
 * This class contains all funcitons for the add_publication_page
 * @package teachpress\admin\publications
 * @since 5.0.0
 */
class TP_Publication_Page {
    
    public static function get_publication_box($pub_id) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Publications','teachpress') . '</span></h3>');
        
        // Add, Save, Reset buttons
        TP_HTML::line('<div id="major-publishing-actions">');
        TP_HTML::line('<div style="text-align: center;"> ');
        if ( $pub_id === 0 ) { 
            TP_HTML::line('<input type="reset" name="Reset" value="' . __('Reset','teachpress') . '" id="teachpress_reset" class="button-secondary" style="padding-right: 30px;">');
            TP_HTML::line('<input name="create_pub" type="submit" class="button-primary" id="create_publication_submit" value="' . __('Create','teachpress') . '">');
        }
        else { 
            TP_HTML::line('<input type="submit" name="speichern" id="save_publication_submit" value="' . __('Save') . '" class="button-primary" title="' . __('Save') . '">');
        }  
        TP_HTML::line('</div>');
        TP_HTML::line('</div>');
        TP_HTML::div_close('postbox');
    }


    /**
     * Gets the bookmarks box
     * @param int $pub_id       The ID of the publication
     * @param int $user         The ID of the current user
     * @since 5.0.0
     */
    public static function get_bookmarks_box ($pub_id, $user) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Bookmarks','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // Current Bookmarks
        self::get_current_bookmarks($pub_id, $user);
        
        // Add Bookmarks
        TP_HTML::line('<p><b>' . __('New','teachpress') . '</b></p>');
        TP_HTML::line('<select name="new_bookmarks[]" id="new_bookmarks" multiple style="width:90%;">');
        $users = get_users();
        foreach ( $users as $row ) {
            TP_HTML::line('<option value="' . $row->ID . '">' . $row->display_name . '</option>');
        }
        var_dump($users);
        TP_HTML::line('</select>');
        
        
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }
    
    /**
     * Gets the comment box
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_comments_box ($pub_data) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Comments','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // comment
        echo TP_Admin::get_form_field(
            array(
                'name' => 'comment',
                'title' => __('A not vissible private comment','teachpress'),
                'label' => __('Private comment','teachpress'),
                'type' => 'textarea',
                'value' => $pub_data['comment'],
                'tabindex' => 31,
                'display' => 'block', 
                'style' => 'width:95%; height: 75px;') );
        
        // note
        echo TP_Admin::get_form_field(
            array(
                'name' => 'note',
                'title' => __('Additional information','teachpress'),
                'label' => __('Note','teachpress'),
                'type' => 'textarea',
                'value' => $pub_data['note'],
                'tabindex' => 32,
                'display' => 'block', 
                'style' => 'width:95%; height: 75px;') );
        
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }
    
    /**
     * Gets the current tags of a publication
     * @param int $pub_id   The ID of the publication
     * @since 5.0.0
     * @access private
     */
    private static function get_current_tags ( $pub_id ) {
        $current_tags = TP_Tags::get_tags( array('pub_id' => $pub_id) );
        if ( count ($current_tags) === 0 ) {
            return;
        }
        TP_HTML::line('<p><b>' . __('Current','teachpress') . '</b></p>');
        foreach ($current_tags as $row){
            $id = $row->con_id;
            $label = stripslashes($row->name);
            $onclick = "teachpress_change_label_color('delbox_" . $id . "', 'delbox_label_" . $id . "')";
            TP_HTML::line('<input name="delbox[]" type="checkbox" value="' . $id . '" id="delbox_' . $id . '" onclick="' . $onclick . '"/> <label for="delbox_' . $id . '" title="Tag &laquo;' . $label . '&raquo; ' . __('Delete','teachpress') . '" id="delbox_label_' . $id . '">' . $label . '</label><br />');
        } 
    }
    
    /**
     * Prints the current bookmarks list
     * @param int $pub_id
     * @param int $current_user_id
     * @since 8.1.0
     * @access private
     */
    private static function get_current_bookmarks ( $pub_id, $current_user_id ) {
        if ( $pub_id === 0 ) {
            return;
        }
        
        $bookmarks = TP_Bookmarks::get_bookmarks( array( 
                        'pub_id'        => $pub_id,
                        'output_type'   => ARRAY_A  ) );
        
        TP_HTML::line('<p><b>' . __('Current','teachpress') . '</b></p>');
        
        foreach ( $bookmarks as $row ) {
            $user_info = get_userdata($row['user']);
            
            // if there is no data
            if ($user_info === false) {
                continue;
            }
            
            // Print use name with checkbox
            $id = $row['bookmark_id'];
            $user_id = $user_info->ID;
            $name = $user_info->display_name;
            $icon = ( $user_id === $current_user_id ) ? ' <i class="fas fa-user"></i>' : '';
            $onclick = "teachpress_change_label_color('bookmark_" . $id . "', 'bookmark_label_" . $id . "')";
            
        TP_HTML::line('<input type="checkbox" name="del_bookmarks[]" id="bookmark_' . $id . '" value="' . $id . '" onclick="' . $onclick . '" title="' . __('Delete bookmark for','teachpress') . ' ' . $name . '"/> <label for="bookmark_' . $id . '" title="' . __('Delete bookmark for','teachpress') . ' ' . $name . '" id="bookmark_label_' . $id . '" class="tp_bookmarks">' . $name . $icon . '</label><br />');
        }
        
            
    }


    /**
     * Gets the general box
     * @param int $pub_id       The ID of the publication
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_general_box ($pub_id, $pub_data){
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('General information','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        TP_HTML::line('<table>');
        TP_HTML::line('<tr>');
        
        // Publication type
        TP_HTML::line('<td style="border:none; padding:0; margin: 0;">');
        $title = __('The type of publication','teachpress');
        TP_HTML::line('<p><label for="type" title="' . $title . '"><b>' . __('Type') . '</b></label></p>');
        TP_HTML::line('<select name="type" id="type" title="' . $title . '" onchange="teachpress_publicationFields(' . "'std'" . ')" tabindex="2">');
        echo get_tp_publication_type_options ($pub_data["type"], $mode = 'sng');
        TP_HTML::line(' </select>');
        TP_HTML::line('</td>');
        
        // BibTex Key
        TP_HTML::line('<td style="border:none; padding:0; margin: 0;">');
        $title = __('A simple unique key without spaces','teachpress');
        TP_HTML::line('<p><label for="bibtex" title="' . $title . '"><b>' . __('BibTeX Key') . '</b></label></p>');
        TP_HTML::line('<input name="bibtex" id="bibtex" type="text" title="' . $title . '" value="' . stripslashes($pub_data["bibtex"]) . '" style="width: 350px;" tabindex="3" />');
        TP_HTML::line('<a id="bibtex_key_gen" style="cursor: pointer;" title="' . __('Generate BibTeX key','teachpress') . '"><i class="fas fa-retweet"></i></a>');
        TP_HTML::line('</td>');
        
        TP_HTML::line('</tr>');
        TP_HTML::line('</table>');
      
        // author
        echo TP_Admin::get_form_field(
            array(
                'name' => 'author',
                'title' => __('The names of the authors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),
                'label' => __('Author(s)','teachpress'),
                'type' => 'textarea',
                'value' => $pub_data['author'],
                'tabindex' => 4,
                'display' => 'block', 
                'style' => 'width:95%; height: 65px;') );
        
        // editor
        echo TP_Admin::get_form_field(
            array(
                'name' => 'editor',
                'title' => __('The names of the editors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),
                'label' => __('Editor(s)','teachpress'),
                'type' => 'textarea',
                'value' => $pub_data['editor'],
                'tabindex' => 5,
                'display' => 'block', 
                'style' => 'width:95%; height: 65px;') );
        
        // pubdate
        $title = __('Date of publishing','teachpress');
        $placeholder = __('JJJJ-MM-TT','teachpress');
        $value = ($pub_id != 0) ? $pub_data["date"] : $placeholder;
        $checked = ( $pub_data['status'] === 'forthcoming' ) ? 'checked="checked"' : '';
        TP_HTML::line('<p><label for="pubdate"><b>' . $title . '</b></label></p>');
        TP_HTML::line('<input type="text" name="pubdate" id="pubdate" title="' . $title . '" value="' . $value . '" placeholder="' . $placeholder . '" tabindex="6"/>');
        TP_HTML::line('<input type="checkbox" name="forthcoming" id="forthcoming" value="true" ' . $checked . ' />');
        TP_HTML::line('<label for="forthcoming">' . __('Forthcoming','teachpress') . '</label>');
               
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }

    /**
     * Gets the image box
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_image_box ($pub_data) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox">' . __('Image','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // Image URL
        if ( $pub_data["image_url"] != '' ) {
            TP_HTML::line('<p><img name="tp_pub_image" src="' . $pub_data["image_url"] . '" alt="' . $pub_data["title"] . '" title="' . $pub_data["title"] . '" style="max-width:100%;"/></p>');
        }
        
        $title = __('With the image field you can add an image to a publication. You can display images in all publication lists','teachpress');
        TP_HTML::line('<p><label for="image_url" title="' . $title . '"><b>' . __('Image URL','teachpress') . '</b></label></p>');
        
        TP_HTML::line('<input name="image_url" id="image_url" class="upload" type="text" title="' . $title . ' style="width:90%;" value="' . $pub_data["image_url"] . '" tabindex="34"/>');
        TP_HTML::line('<a class="upload_button_image" title="' . __('Add Image','teachpress') . '" style="cursor:pointer; border:none;"><i class="far fa-image"></i></a>');
        
        // Image Link Target
        TP_HTML::line( '<p><label for="image_target" title="' . __('Define the link target for the image.','teachpress') . '"><b>' . __('Image Link Target','teachpress') . '</b></label></p>');
        TP_HTML::line( '<select name="image_target" id="image_target" title="' . __('Define the link target for the image.','teachpress') . '" style="width:90%;" tabindex="35">');

        echo TP_Admin::get_select_option('none', __('none','teachpress'), $pub_data["image_target"]);
        echo TP_Admin::get_select_option('self', __('Self','teachpress'), $pub_data["image_target"]);
        echo TP_Admin::get_select_option('rel_page', __('Related content','teachpress'), $pub_data["image_target"]);
        echo TP_Admin::get_select_option('external', __('External URL','teachpress'), $pub_data["image_target"]);

        TP_HTML::line('</select>');

        // External Image Link
        echo TP_Admin::get_form_field(
            array(
                'name' => 'image_ext',
                'title' => __('If you choice an external link target for the image, then you can define the URL of this target here.','teachpress'),
                'label' => __('External Image Link','teachpress'),
                'type' => 'input',
                'value' => $pub_data['image_ext'],
                'tabindex' => 36,
                'display' => 'block', 
                'style' => 'width:90%;') );
               
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }
    
    /**
     * Gets the related page box
     * @param array $pub_data   An associative array with publication data
     * @since 7.1.0
     */
    public static function get_rel_page_box ($pub_data) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Related content','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        TP_HTML::line('<p><label for="rel_page" title="' . __('Select a post/page with releated content.','teachpress') . '"><b>' . __('Related content','teachpress') . '</b></label></p>');
        TP_HTML::line('<div style="overflow:hidden;">');
        
        // SELECT field
        TP_HTML::line('<select name="rel_page" id="rel_page" title="' . __('Select a post/page with releated content.','teachpress') . '" style="width:90%;" tabindex="37">');
        $post_type = get_tp_option('rel_page_publications');
        get_tp_wp_pages("menu_order", "ASC", $pub_data["rel_page"], $post_type, 0, 0); 
        TP_HTML::line('</select>');
        
        // New related content link
        TP_HTML::line('<p style="padding:5px 0 0 5px;">');
        $value = ( get_tp_option('rel_content_auto') == '1' ) ? '1' : '0';
        echo TP_Admin::get_checkbox('create_rel_content', __('Create related content','teachpress'), $value); 
        TP_HTML::line('</p>');
        
        TP_HTML::div_close();
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    } 
    
    /**
     * Gets the main box
     * @param int $pub_id       The ID of the publication
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_main_box ($pub_id, $pub_data) {
        // teachPress Publication Types
        global $tp_publication_types;
        $publication_types = $tp_publication_types->get();
        if ( isset( $publication_types[ $pub_data['type'] ]['default_fields'] ) ) {
            $default_fields = $publication_types[ $pub_data['type'] ]['default_fields'];
        }
        else {
            $default_fields = $publication_types['article']['default_fields'];
        }
        
        TP_HTML::div_open('postbox');
        
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Detailed information','teachpress') . '</span> | <small><a id="show_all_fields" onclick="teachpress_publicationFields(' . "'" . 'all' . "'" . ');" style="cursor:pointer; display:inline;">' . __('Show all fields','teachpress') . '</a> <a id="show_recommend_fields" onclick="teachpress_publicationFields(' . "'" . 'std2' . "'" . ');" style="cursor:pointer; display:none;">' . __('Show recommend fields','teachpress') . '</a></small></h3>');
        
        TP_HTML::div_open('inside');
        
        // booktitle
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'booktitle',
                'title'     => __('The title of a book','teachpress'),
                'label'     => __('Booktitle','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['booktitle'],
                'tabindex'  => 6,
                'display'   => ( in_array('booktitle', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%; height: 58px;') );

        // issuetitle
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'issuetitle',
                'title'     => __('The subtitle of a periodical publication','teachpress'),
                'label'     => __('Issuetitle','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['issuetitle'],
                'tabindex'  => 7,
                'display'   => ( in_array('issuetitle', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%; height: 58px;') );

        // journal
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'journal',
                'title'     => __('The title of a journal','teachpress'),
                'label'     => __('Journal','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['journal'],
                'tabindex'  => 8,
                'display'   => ( in_array('journal', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );
        
        // volume
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'volume',
                'title'     => __('The volume of a journal or book','teachpress'),
                'label'     => __('Volume','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['volume'],
                'tabindex'  => 9,
                'display'   => ( in_array('volume', $default_fields) ) ? 'block' : 'none') );

        // number
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'number',
                'title'     => __('The number of a book, journal or work in a series','teachpress'),
                'label'     => __('Number','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['number'],
                'tabindex'  => 10,
                'display'   => ( in_array('number', $default_fields) ) ? 'block' : 'none') );

        // pages
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'pages',
                'title'     => __('The page you are referring to.','teachpress'),
                'label'     => __('Pages','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['pages'],
                'tabindex'  => 11,
                'display'   => ( in_array('pages', $default_fields) ) ? 'block' : 'none') );
        

        // publisher
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'publisher',
                'title'     => __('The names of publisher','teachpress'),
                'label'     => __('Publisher','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['publisher'],
                'tabindex'  => 12,
                'display'   => ( in_array('publisher', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // address
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'address',
                'title'     => __('The address of the publisher or the place of confernece','teachpress'),
                'label'     => __('Address','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['address'],
                'tabindex'  => 13,
                'display'   => ( in_array('address', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );
        

        // edition
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'edition',
                'title'     => __('The edition of a book','teachpress'),
                'label'     => __('Edition','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['edition'],
                'tabindex'  => 14,
                'display'   => ( in_array('edition', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // chapter
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'chapter',
                'title'     => __('The chapter or the section number','teachpress'),
                'label'     => __('Chapter','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['chapter'],
                'tabindex'  => 15,
                'display'   => ( in_array('chapter', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // institution
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'institution',
                'title'     => __('The name of a sponsoring institution','teachpress'),
                'label'     => __('Institution','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['institution'],
                'tabindex'  => 16,
                'display'   => ( in_array('institution', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // organization
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'organization',
                'title'     => __('The names of a sponsoring organization','teachpress'),
                'label'     => __('Organization','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['organization'],
                'tabindex'  => 17,
                'display'   => ( in_array('organization', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // school
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'school',
                'title'     => __('The names of the academic instituion where a thesis was written','teachpress'),
                'label'     => __('School','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['school'],
                'tabindex'  => 18,
                'display'   => ( in_array('school', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;') );

        // series
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'series',
                'title'     => __('The name of a series','teachpress'),
                'label'     => __('Series','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['series'],
                'tabindex'  => 19,
                'display'   => ( in_array('series', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;' ) );

        // crossref
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'crossref',
                'title'     => __('The BibTeX key this work is referring to','teachpress'),
                'label'     => __('Crossref','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['crossref'],
                'tabindex'  => 20,
                'display'   => ( in_array('crossref', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;' ) );

        // abstract
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'abstract',
                'title'     => __('A short summary of the publication','teachpress'),
                'label'     => __('Abstract','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['abstract'],
                'tabindex'  => 21,
                'display'   => 'block',
                'style'     => 'width:95%; height: 150px;') );

        // howpublished
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'howpublished',
                'title'     => __('An unusual method for publishing','teachpress'),
                'label'     => __('Howpublished','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['howpublished'],
                'tabindex'  => 22,
                'display'   => ( in_array('howpublished', $default_fields) ) ? 'block' : 'none', 
                'style'     => 'width:95%;') );
        
        // key
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'key',
                'title'     => __('If there is no author or editor given, so this field is used for the sorting.','teachpress'),
                'label'     => __('Key','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['key'],
                'tabindex'  => 23,
                'display'   => 'block', 
                'style'     => '') );

        // techtype
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'techtype',
                'title'     => __('The type of a technical report, thesis, incollection or inbook.','teachpress'),
                'label'     => __('Type'),
                'type'      => 'input',
                'value'     => $pub_data['techtype'],
                'tabindex'  => 24,
                'display'   => ( in_array('techtype', $default_fields) ) ? 'block' : 'none', 
                'style'     => '') );
        
        // isbn
        $checked_1 = ( $pub_data["is_isbn"] == '1' || $pub_id === 0 ) ? 'checked="checked"' : '';
        $checked_2 = ($pub_data["is_isbn"] == '0') ? 'checked="checked"' : '';
        TP_HTML::div_open('div_isbn');
        TP_HTML::line('<p><label for="isbn"><b>' . __('ISBN/ISSN','teachpress') . '</b></label></p>');
        TP_HTML::line('<input type="text" name="isbn" id="isbn" title="' . __('The ISBN or ISSN of the publication','teachpress') . '" value="' . $pub_data["isbn"] . '" tabindex="25">');
        TP_HTML::line('<span style="padding-left:7px;">');
        TP_HTML::line('<label><input name="is_isbn" type="radio" id="is_isbn_0" value="1" ' . $checked_1 . ' tabindex="26"/>' . __('ISBN','teachpress') . '</label>');
        TP_HTML::line('<label><input name="is_isbn" type="radio" value="0" id="is_isbn_1" ' . $checked_2 . ' tabindex="27"/>' . __('ISSN','teachpress') . '</label>');
        TP_HTML::line('</span>');
        TP_HTML::div_close('div_isbn');   
      
        // doi
        echo TP_Admin::get_form_field(
            array(
                'name'      => 'doi',
                'title'     => __('DOI number','teachpress'),
                'label'     => __('DOI number','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['doi'],
                'tabindex'  => 28,
                'display'   => 'block', 
                'style'     => 'width:95%;') );
        
        // urldate
        $display = ($pub_data["type"] === 'online' || $pub_data["type"] === 'periodical') ? 'style="display:block;"' : 'style="display:none;"';
        $title = __('The date you have visited the online resource','teachpress');
        $placeholder = __('JJJJ-MM-TT','teachpress');
        $value = ($pub_id != 0) ? $pub_data["date"] : $placeholder;
        TP_HTML::line('<div id="div_urldate" ' . $display . '>');
        TP_HTML::line('<p><label for="urldate" title="' . $title . '"><b>' . __('Urldate','teachpress') . '</b></label></p>');
        TP_HTML::line('<input type="text" name="urldate" id="urldate" title="' . $title . '" value="' . $value . '" placeholder="' . $placeholder . '" tabindex="29"/>');
        TP_HTML::div_close('div_urldate');
        
        // url
        TP_HTML::div_open('div_url');
        TP_HTML::line('<p><label for="url" title="' . __('URL/Files', 'teachpress') . '"><b>' . __('URL/Files', 'teachpress') . '</b></label> | ');
        TP_HTML::line('<a class="upload_button" style="cursor:pointer;" title="' . __('Insert a file from the WordPress Media Library','teachpress') . '"><i class="far fa-caret-square-up"></i> ' . __('Add/Upload','teachpress') . '</a></p>');
        TP_HTML::line('<input name="upload_mode" id="upload_mode" type="hidden" value="" />');
        TP_HTML::line('<textarea name="url" type="text" id="url" class="upload" title="' . __('You can add one URL or file per line. Insert the name of the URL/file behind the address and separate it by a comma and a space. Example:', 'teachpress') . ' http://mywebsite.com/docs/readme.pdf, Basic Instructions" style="width:95%" rows="4" tabindex="30">' . $pub_data["url"] . '</textarea>');
        TP_HTML::div_close('div_url');
        
        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
        
    }


    /**
     * Gets the tags box
     * @param int $pub_id   The ID of the publication
     * @since 5.0.0
     */
    public static function get_tags_box ($pub_id) {
        TP_HTML::div_open('postbox');
        TP_HTML::line('<h3 class="tp_postbox"><span>' . __('Tags') . '</span></h3>');
        TP_HTML::div_open('inside');

        if ($pub_id != 0) {
            TP_Publication_Page::get_current_tags ( $pub_id );
        }

        // New tags field
        TP_HTML::line('<p><label for="tags"><b>' . __('New','teachpress') . '</b></label></p>');
        TP_HTML::line('<select name="tags[]" id="tags" tabindex="33" multiple style="width:90%;">');
        $tags = TP_Tags::get_tags( array('group_by' => true, 'output_type'   => ARRAY_A) );
        foreach ($tags as $row) {
            TP_HTML::line('<option value="' . esc_js($row['name']) . '">' . $row['name'] . '</option>');
        }
        TP_HTML::line('</select>');

        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }
    
    public static function prepare_tags($tags) {
        $end = '';
        foreach ( $tags as $element ) {
            $end = ( $end === '' ) ? $element : $end . ',' . $element;
        }
        return $end;
    }
    
    /**
     * Gets the javascripts for this page
     * @since 5.0.0
     */
    public static function print_scripts () {
        global $tp_publication_types;
        $publication_types = $tp_publication_types->get();
        
        ?>
        <script>
            // SELECT fields
            new SlimSelect({
                select: '#tags',
                allowDeselect: true,
                closeOnSelect: false,
                addable: function (value) {
                    // return false or null if you do not want to allow value to be submitted
                    if (value === '') {return false;}

                    // Return the value string
                    return value;

                  }
            });
            new SlimSelect({
               select: '#new_bookmarks',
               allowDeselect: true,
                closeOnSelect: false
            });
        </script>
        <script>
            <?php
            // Print pub type data to javascript
            foreach ( $publication_types as $row ) {
                $default_fields = '';
                foreach ( $row['default_fields'] as $r2 ) {
                    $default_fields = ( $default_fields === '' ) ? '"' . $r2 . '"' : $default_fields . ', ' . '"' . $r2 . '"';
                }
                TP_HTML::line('var tp_type_' . $row['type_slug'] . ' = [' . $default_fields . '];' );
            }
            ?>
            jQuery(document).ready(function($){
            $( "#bibtex_key_gen" ).click(function() {
                var author = $("#author").val();
                var editor = $("#editor").val();
                var year = $("#pubdate").val().substr(0,4);
                if ( author === '' ) {
                    if ( editor === '' ) {
                        alert('<?php _e('Please enter an author before!','teachpress') ?>');
                        return;
                    }
                    else {
                        author = editor;
                    }
                }
                if ( isNaN(year) ) {
                    alert('<?php _e('Please enter the date before!','teachpress') ?>');
                    return;
                }
                // split author string
                author = author.split(" and ");

                // split name of first author
                var name = author[0].split(",");
                name[0] = teachpress_trim(name[0]);
                name = name[0].split(" ");

                var count = name.length;
                var prefix = "";
                var first_char = "";
                // Search surname titles like 'van der', 'von den', 'del la',...
                for ( i = 0; i < count; i++ ) {
                    name[i] = teachpress_trim(name[i]);
                    first_char = name[i].charCodeAt(0);
                    if ( first_char >= 97 && first_char <= 122 ) {
                        prefix = prefix + name[i];
                    }
                }
                var last_name = prefix + name[count - 1];
                
                $.get(ajaxurl + "?action=teachpress&bibtex_key_check=" + last_name + year, 
                    function(text){
                        document.getElementById("bibtex").value = text;
                    });
            });

        });
        </script>
        <script>
        jQuery(document).ready(function($) {
            $('#pubdate').datepicker({showWeek: true, changeMonth: true, changeYear: true, showOtherMonths: true, firstDay: 1, renderer: $.extend({}, $.datepicker.weekOfYearRenderer), onShow: $.datepicker.showStatus, dateFormat: 'yy-mm-dd', yearRange: '1950:c+5'});
            $('#urldate').datepicker({showWeek: true, changeMonth: true, changeYear: true, showOtherMonths: true, firstDay: 1, renderer: $.extend({}, $.datepicker.weekOfYearRenderer), onShow: $.datepicker.showStatus, dateFormat: 'yy-mm-dd', yearRange: '1990:c+5'});
            $('#abstract').resizable({handles: "se", minHeight: 80, minWidth: 500});
            $('#url').resizable({handles: "se", minHeight: 80, minWidth: 500});
            $('#comment').resizable({handles: "se", minHeight: 70, minWidth: 400});
            $('#note').resizable({handles: "se", minHeight: 70, minWidth: 400});
                            
            var availableAuthors = [
                <?php
                $start2 = '';
                $sql2 = TP_Authors::get_authors( array('group_by' => true, 'include_editors' =>true) );
                foreach ($sql2 as $row) {
                    if ( $start2 === '' ) {
                        echo '"' . esc_js($row->name) . '"';
                        $start2 = '1';
                    }
                    else {
                        echo ',"' . esc_js($row->name) . '"';
                    }        
                } ?>];
            
            
            function split( val ) {
                return val.split( /,\s*/ );
            }

            function split_authors( val ) {
                return val.split( /\sand\s*/ );
            }
            
            function extractLast_authors( term ) {
                return split_authors( term ).pop();
            }

            $( "#author" )
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
                        availableAuthors, extractLast_authors( request.term ) ) );
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split_authors( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( " and " );
                    return false;
                }
            });

            $( "#editor" )
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
                        availableAuthors, extractLast_authors( request.term ) ) );
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split_authors( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( " and " );
                    return false;
                }
            });
	});
	</script>
        <?php
    }

}