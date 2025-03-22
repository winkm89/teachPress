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
        'title'     => esc_html__('Create a new publication','teachpress'),
        'content'   => '<p><b>' . esc_html__('Required fields','teachpress') . '</b></p>
                        <p>' . esc_html__('The required fields for a new publication:','teachpress') . ' <b>' .  esc_html__('title, author, bibtex key, tags','teachpress') . '</b></p>
                        <p><b>' . esc_html__('URL/Files','teachpress') . '</b></p>
                        <p>' . esc_html__('You can add one URL or file per line. Insert the name of the URL/file behind the address and separate it by a comma and a space. Example:', 'teachpress') . '<br />http://mywebsite.com/docs/readme.pdf, Basic Instructions</p>'
    ) );
} 

/** 
 * New publication / edit publication
 * from show_publications.php (GET):
 * @param int $pub_id       publication ID
 * @since 5.0.0
*/
function tp_add_publication_page() {
   
    // WordPress current unser info
    $current_user = wp_get_current_user();
    $user = $current_user->ID;
    $fields = get_tp_options('teachpress_pub','`setting_id` ASC', ARRAY_A);

    // form variables from add_publication.php
    $data = tp_get_default_structure();
    $data['bibtex'] = isset( $_POST['bibtex'] ) ? htmlspecialchars($_POST['bibtex']) : '';
    $data['type'] = isset( $_POST['type'] ) ? htmlspecialchars($_POST['type']) : '';
    $data['award'] = isset( $_POST['award'] ) ? htmlspecialchars($_POST['award']) : '';
    $data['title'] = isset( $_POST['tp_post_title'] ) ? htmlspecialchars($_POST['tp_post_title']) : '';
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
    $data['issue'] = isset( $_POST['issue'] ) ? htmlspecialchars($_POST['issue']) : '';
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

    echo '<div class="wrap">';
    
    // headline
    if ( $pub_id === 0 ) {
        echo '<h2>' . esc_html__('Add a new publication','teachpress') . '</h2>';
    }
    else {
        echo '<h2>' . esc_html__('Edit publication','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php" class="add-new-h2">' . esc_html__('Create','teachpress') . '</a></h2>';
    }
    
    echo '<form name="form1" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '" id="form1">';
    wp_nonce_field( 'verify_teachpress_pub_edit', 'tp_nonce', true, true );
   
    // create related content (post/page/...)
    if ( isset($_POST['create_rel_content']) ) {
        TP_Publication_Page::check_nonce_field();
        $data['rel_page'] = tp_add_publication_as_post( $data['title'], $data['bibtex'], $data['date'], get_tp_option('rel_page_publications'), $tags, array(get_tp_option('rel_content_category')) );
    }
    
    // create publication and related page
    if ( isset($_POST['create_pub']) ) {
        TP_Publication_Page::check_nonce_field();
        $pub_id = TP_Publications::add_publication($data, $tags, $new_bookmarks);
        TP_DB_Helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        $message = esc_html__('Publication added','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php">' . esc_html__('Add new','teachpress') . '</a>';
        get_tp_message($message);
    }
    
    // save publication
    if ( isset($_POST['speichern']) ) {
        TP_Publication_Page::check_nonce_field();
        TP_Publications::delete_pub_meta($pub_id);
        TP_Publications::change_publication($pub_id, $data, $tags, $delbox, $new_bookmarks, $del_bookmarks);
        TP_DB_Helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        get_tp_message( esc_html__('Saved') );
    }
    
    // Default values
    if ( $pub_id != 0 ) {
        $pub_data = TP_Publications::get_publication($pub_id, ARRAY_A);
        $pub_meta = TP_Publications::get_pub_meta($pub_id);
    }
    else {
        $pub_data = tp_get_default_structure();
        $pub_meta = array ( array('meta_key' => '', 'meta_value' => '') );
    }

    // Check format of author/editor field
    if ( $pub_id != 0 && !isset($_POST['create_pub']) ) {
        $check = ( strpos($pub_data['author'], ',') !== false || strpos($pub_data['editor'], ',') !== false) ? true : false;
        if ( $check === true ) {
            get_tp_message( esc_html__('Please check the format of author/editor information and correct it to the following format: firstname1 lastname1 and firstname2 lastname 2. Example: Adam Smith and John M. Keynes','teachpress') , 'orange');
        }
    }
    
    // input fields
    echo '<input name="page" type="hidden" value="teachpress/addpublications.php">';
    if ( $pub_id != 0 ) {
        echo '<input type="hidden" name="pub_id" value="' . intval($pub_id) . '" />';
    }
    
    echo '<div class="tp_postbody">';
    
    echo '<div class="tp_postcontent">';
    echo '<div id="post-body">';
    echo '<div id="post-body-content">';
    
    echo '<div id="titlediv" style="padding-bottom: 15px;">';
    echo '<div id="titlewrap">';
    echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">' . esc_html__('Title','teachpress') . '</label>';
    echo '<input type="text" name="tp_post_title" size="30" title="' . esc_html__('Title','teachpress') . '" tabindex="1" value="' . stripslashes($pub_data["title"]) . '" id="title" placeholder="' . esc_html__('Title','teachpress') . '" autocomplete="off" />';
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Publications','teachpress') . '</span></h3>');
        
        // Add, Save, Reset buttons
        TP_HTML::line('<div id="major-publishing-actions">');
        TP_HTML::line('<div style="text-align: center;"> ');
        if ( $pub_id === 0 ) { 
            TP_HTML::line('<input type="reset" name="Reset" value="' . esc_html__('Reset','teachpress') . '" id="teachpress_reset" class="button-secondary" style="padding-right: 30px;">');
            TP_HTML::line('<input name="create_pub" type="submit" class="button-primary" id="create_publication_submit" value="' . esc_html__('Create','teachpress') . '">');
        }
        else { 
            TP_HTML::line('<input type="submit" name="speichern" id="save_publication_submit" value="' . esc_html__('Save') . '" class="button-primary" title="' . esc_html__('Save') . '">');
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Bookmarks','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // Current Bookmarks
        self::get_current_bookmarks($pub_id, $user);
        
        // Add Bookmarks
        TP_HTML::line('<p><b>' . esc_html__('New','teachpress') . '</b></p>');
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Comments','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // comment
        TP_Admin::get_form_field(
            array(
                'name'      => 'comment',
                'title'     => esc_html__('A not vissible private comment','teachpress'),
                'label'     => esc_html__('Private comment','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['comment'],
                'tabindex'  => 31,
                'display'   => 'block', 
                'style'     => 'width:95%; height: 75px;'), true );
        
        // note
        TP_Admin::get_form_field(
            array(
                'name'      => 'note',
                'title'     => esc_html__('Additional information','teachpress'),
                'label'     => esc_html__('Note','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['note'],
                'tabindex'  => 32,
                'display'   => 'block', 
                'style'     => 'width:95%; height: 75px;'), true );
        
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
        TP_HTML::line('<p><b>' . esc_html__('Current','teachpress') . '</b></p>');
        foreach ($current_tags as $row){
            $id = $row->con_id;
            $label = stripslashes($row->name);
            $onclick = "teachpress_change_label_color('delbox_" . $id . "', 'delbox_label_" . $id . "')";
            TP_HTML::line('<input name="delbox[]" type="checkbox" value="' . $id . '" id="delbox_' . $id . '" onclick="' . $onclick . '"/> <label for="delbox_' . $id . '" title="Tag &laquo;' . $label . '&raquo; ' . esc_html__('Delete','teachpress') . '" id="delbox_label_' . $id . '">' . $label . '</label><br />');
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
        
        TP_HTML::line('<p><b>' . esc_html__('Current','teachpress') . '</b></p>');
        
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
            
        TP_HTML::line('<input type="checkbox" name="del_bookmarks[]" id="bookmark_' . $id . '" value="' . $id . '" onclick="' . $onclick . '" title="' . esc_html__('Delete bookmark for','teachpress') . ' ' . $name . '"/> <label for="bookmark_' . $id . '" title="' . esc_html__('Delete bookmark for','teachpress') . ' ' . $name . '" id="bookmark_label_' . $id . '" class="tp_bookmarks">' . $name . $icon . '</label><br />');
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('General information','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        TP_HTML::line('<table>');
        TP_HTML::line('<tr>');
        
        $tabindex = 1;
        // Publication type
        TP_HTML::line('<td style="border:none; padding:0; margin: 0;">');
        $title = esc_html__('The type of publication','teachpress');
        TP_HTML::line('<p><label for="type" title="' . $title . '"><b>' . esc_html__('Type') . '</b></label></p>');
        $tabindex++;
        TP_HTML::line('<select name="type" id="type" title="' . $title . '" onchange="teachpress_publicationFields(' . "'std'" . ')" tabindex="'.$tabindex.'">');
        echo get_tp_publication_type_options ($pub_data["type"], $mode = 'sng');
        TP_HTML::line('</select>');
        TP_HTML::line('</td>');
        
        // BibTex key
        TP_HTML::line('<td style="border:none; padding: 0 0 0 30px; margin: 0;">');
        $title = esc_html__('A simple unique key without spaces','teachpress');
        TP_HTML::line('<p><label for="bibtex" title="' . $title . '"><b>' . esc_html__('BibTeX key') . '</b></label></p>');
        $tabindex++;
        TP_HTML::line('<input name="bibtex" id="bibtex" type="text" title="' . $title . '" value="' . stripslashes($pub_data["bibtex"]) . '" style="width: 350px;" tabindex="'.$tabindex.'" />');
        TP_HTML::line('<a id="bibtex_key_gen" style="cursor: pointer;" title="' . esc_html__('Generate BibTeX key','teachpress') . '"><i class="fas fa-retweet"></i></a>');
        TP_HTML::line('</td>');

        // Award of publication
        TP_HTML::line('<td style="border:none; padding: 0 0 0 30px; margin: 0;">');
        $title = esc_html__('Award','teachpress');
        TP_HTML::line('<p><label for="award" title="'. $title .'"><b>'.esc_html__('Award').'</b></label></p>');
        $tabindex++;
        TP_HTML::line('<select name="award" id="award" title="' . $title . '" tabindex="'.$tabindex.'">');
        echo get_tp_award_options ($pub_data["award"]);
        TP_HTML::line('</select>');
        TP_HTML::line('</td>');
        
        TP_HTML::line('</tr>');
        TP_HTML::line('</table>');
      
        // author
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'author',
                'title'     => esc_html__('The names of the authors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),
                'label'     => esc_html__('Author(s)','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['author'],
                'tabindex'  => $tabindex,
                'display'   => 'block', 
                'style'     => 'width:95%; height: 65px;'), true );
        
        // editor
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'editor',
                'title'     => esc_html__('The names of the editors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),
                'label'     => esc_html__('Editor(s)','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['editor'],
                'tabindex'  => $tabindex,
                'display'   => 'block', 
                'style'     => 'width:95%; height: 65px;'), true );
        
        // pubdate
        $title = esc_html__('Date of publishing','teachpress');
        $placeholder = esc_html__('YYYY-MM-DD','teachpress');
        $value = ($pub_id != 0) ? $pub_data["date"] : $placeholder;
        $checked = ( $pub_data['status'] === 'forthcoming' ) ? 'checked="checked"' : '';
        TP_HTML::line('<p><label for="pubdate"><b>' . $title . '</b></label></p>');
        $tabindex++;
        TP_HTML::line('<input type="text" name="pubdate" id="pubdate" title="' . $title . '" value="' . $value . '" placeholder="' . $placeholder . '" tabindex="'.$tabindex.'"/>');
        TP_HTML::line('<input type="checkbox" name="forthcoming" id="forthcoming" value="true" ' . $checked . ' />');
        TP_HTML::line('<label for="forthcoming">' . esc_html__('Forthcoming','teachpress') . '</label>');
               
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
        TP_HTML::line('<h3 class="tp_postbox">' . esc_html__('Image','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        
        // Image URL
        if ( $pub_data["image_url"] != '' ) {
            TP_HTML::line('<p><img name="tp_pub_image" src="' . $pub_data["image_url"] . '" alt="' . $pub_data["title"] . '" title="' . $pub_data["title"] . '" style="max-width:100%;"/></p>');
        }
        
        $title = esc_html__('With the image field you can add an image to a publication. You can display images in all publication lists','teachpress');
        TP_HTML::line('<p><label for="image_url" title="' . $title . '"><b>' . esc_html__('Image URL','teachpress') . '</b></label></p>');
        
        TP_HTML::line('<input name="image_url" id="image_url" class="upload" type="text" title="' . $title . ' style="width:90%;" value="' . $pub_data["image_url"] . '" tabindex="34"/>');
        TP_HTML::line('<a class="upload_button_image" title="' . esc_html__('Add Image','teachpress') . '" style="cursor:pointer; border:none;"><i class="far fa-image"></i></a>');
        
        // Image Link Target
        TP_HTML::line( '<p><label for="image_target" title="' . esc_html__('Define the link target for the image.','teachpress') . '"><b>' . esc_html__('Image Link Target','teachpress') . '</b></label></p>');
        TP_HTML::line( '<select name="image_target" id="image_target" title="' . esc_html__('Define the link target for the image.','teachpress') . '" style="width:90%;" tabindex="35">');

        TP_Admin::get_select_option('none', esc_html__('none','teachpress'), $pub_data["image_target"], true);
        TP_Admin::get_select_option('self', esc_html__('Self','teachpress'), $pub_data["image_target"], true);
        TP_Admin::get_select_option('rel_page', esc_html__('Related content','teachpress'), $pub_data["image_target"], true);
        TP_Admin::get_select_option('external', esc_html__('External URL','teachpress'), $pub_data["image_target"], true);

        TP_HTML::line('</select>');

        // External Image Link
        TP_Admin::get_form_field(
            array(
                'name'      => 'image_ext',
                'title'     => esc_html__('If you choice an external link target for the image, then you can define the URL of this target here.','teachpress'),
                'label'     => esc_html__('External Image Link','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['image_ext'],
                'tabindex'  => 36,
                'display'   => 'block', 
                'style'     => 'width:90%;'), true );
               
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Related content','teachpress') . '</span></h3>');
        TP_HTML::div_open('inside');
        TP_HTML::line('<p><label for="rel_page" title="' . esc_html__('Select a post/page with releated content.','teachpress') . '"><b>' . esc_html__('Related content','teachpress') . '</b></label></p>');
        TP_HTML::line('<div style="overflow:hidden;">');
        
        // SELECT field
        TP_HTML::line('<select name="rel_page" id="rel_page" title="' . esc_html__('Select a post/page with releated content.','teachpress') . '" style="width:90%;" tabindex="37">');
        $post_type = get_tp_option('rel_page_publications');
        get_tp_wp_pages("menu_order", "ASC", $pub_data["rel_page"], $post_type, 0, 0); 
        TP_HTML::line('</select>');
        
        // New related content link
        TP_HTML::line('<p style="padding:5px 0 0 5px;">');
        $value = ( get_tp_option('rel_content_auto') == '1' ) ? '1' : '0';
        TP_Admin::get_checkbox('create_rel_content', esc_html__('Create related content','teachpress'), $value, false, true); 
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
        
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Detailed information','teachpress') . '</span> | <small><a id="show_all_fields" onclick="teachpress_publicationFields(' . "'" . 'all' . "'" . ');" style="cursor:pointer; display:inline;">' . esc_html__('Show all fields','teachpress') . '</a> <a id="show_recommend_fields" onclick="teachpress_publicationFields(' . "'" . 'std2' . "'" . ');" style="cursor:pointer; display:none;">' . esc_html__('Show recommend fields','teachpress') . '</a></small></h3>');
        
        TP_HTML::div_open('inside');
        
        $tabindex = 8;
        // booktitle
        TP_Admin::get_form_field(
            array(
                'name'      => 'booktitle',
                'title'     => esc_html__('The title of a book','teachpress'),
                'label'     => esc_html__('Booktitle','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['booktitle'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('booktitle', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%; height: 58px;'), 
            true );

        // issuetitle
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'issuetitle',
                'title'     => esc_html__('The subtitle of a periodical publication','teachpress'),
                'label'     => esc_html__('Issuetitle','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['issuetitle'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('issuetitle', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%; height: 58px;'), 
            true );

        // journal
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'journal',
                'title'     => esc_html__('The title of a journal','teachpress'),
                'label'     => esc_html__('Journal','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['journal'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('journal', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'), 
            true );
        
        // volume
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'volume',
                'title'     => esc_html__('The volume of a journal or book','teachpress'),
                'label'     => esc_html__('Volume','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['volume'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('volume', $default_fields) ) ? 'block' : 'none'), 
            true );
        
        // volume
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'issue',
                'title'     => esc_html__('The issue of a journal','teachpress'),
                'label'     => esc_html__('Issue','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['issue'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('issue', $default_fields) ) ? 'block' : 'none'), 
            true );

        // number
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'number',
                'title'     => esc_html__('The number of a book, journal or work in a series','teachpress'),
                'label'     => esc_html__('Number','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['number'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('number', $default_fields) ) ? 'block' : 'none'), 
            true );

        // pages
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'pages',
                'title'     => esc_html__('The page you are referring to.','teachpress'),
                'label'     => esc_html__('Pages','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['pages'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('pages', $default_fields) ) ? 'block' : 'none'), 
            true );
        

        // publisher
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'publisher',
                'title'     => esc_html__('The names of publisher','teachpress'),
                'label'     => esc_html__('Publisher','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['publisher'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('publisher', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'), 
            true );

        // address
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'address',
                'title'     => esc_html__('The address of the publisher or the place of confernece','teachpress'),
                'label'     => esc_html__('Address','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['address'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('address', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true);
        

        // edition
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'edition',
                'title'     => esc_html__('The edition of a book','teachpress'),
                'label'     => esc_html__('Edition','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['edition'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('edition', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true);

        // chapter
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'chapter',
                'title'     => esc_html__('The chapter or the section number','teachpress'),
                'label'     => esc_html__('Chapter','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['chapter'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('chapter', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true);

        // institution
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'institution',
                'title'     => esc_html__('The name of a sponsoring institution','teachpress'),
                'label'     => esc_html__('Institution','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['institution'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('institution', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true);

        // organization
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'organization',
                'title'     => esc_html__('The names of a sponsoring organization','teachpress'),
                'label'     => esc_html__('Organization','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['organization'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('organization', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true );

        // school
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'school',
                'title'     => esc_html__('The names of the academic instituion where a thesis was written','teachpress'),
                'label'     => esc_html__('School','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['school'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('school', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;'),
            true );

        // series
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'series',
                'title'     => esc_html__('The name of a series','teachpress'),
                'label'     => esc_html__('Series','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['series'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('series', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;' ),
            true );

        // crossref
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'crossref',
                'title'     => esc_html__('The BibTeX key this work is referring to','teachpress'),
                'label'     => esc_html__('Crossref','teachpress'), 
                'type'      => 'input',
                'value'     => $pub_data['crossref'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('crossref', $default_fields) ) ? 'block' : 'none',
                'style'     => 'width:95%;' ),
            true );

        // abstract
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'abstract',
                'title'     => esc_html__('A short summary of the publication','teachpress'),
                'label'     => esc_html__('Abstract','teachpress'),
                'type'      => 'textarea',
                'value'     => $pub_data['abstract'],
                'tabindex'  => $tabindex,
                'display'   => 'block',
                'style'     => 'width:95%; height: 150px;'),
            true );

        // howpublished
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'howpublished',
                'title'     => esc_html__('An unusual method for publishing','teachpress'),
                'label'     => esc_html__('Howpublished','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['howpublished'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('howpublished', $default_fields) ) ? 'block' : 'none', 
                'style'     => 'width:95%;'),
            true );
        
        // key
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'key',
                'title'     => esc_html__('If there is no author or editor given, so this field is used for the sorting.','teachpress'),
                'label'     => esc_html__('Key','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['key'],
                'tabindex'  => $tabindex,
                'display'   => 'block', 
                'style'     => ''),
            true );

        // techtype
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'techtype',
                'title'     => esc_html__('The type of a technical report, thesis, incollection or inbook.','teachpress'),
                'label'     => esc_html__('Type'),
                'type'      => 'input',
                'value'     => $pub_data['techtype'],
                'tabindex'  => $tabindex,
                'display'   => ( in_array('techtype', $default_fields) ) ? 'block' : 'none', 
                'style'     => ''),
            true );
        
        // isbn
        $checked_1 = ( $pub_data["is_isbn"] == '1' || $pub_id === 0 ) ? 'checked="checked"' : '';
        $checked_2 = ($pub_data["is_isbn"] == '0') ? 'checked="checked"' : '';
        TP_HTML::div_open('div_isbn');
        TP_HTML::line('<p><label for="isbn"><b>' . esc_html__('ISBN/ISSN','teachpress') . '</b></label></p>');
        $tabindex++;
        TP_HTML::line('<input type="text" name="isbn" id="isbn" title="' . esc_html__('The ISBN or ISSN of the publication','teachpress') . '" value="' . $pub_data["isbn"] . '" tabindex="'.$tabindex.'">');
        TP_HTML::line('<span style="padding-left:7px;">');
        $tabindex++;
        TP_HTML::line('<label><input name="is_isbn" type="radio" id="is_isbn_0" value="1" ' . $checked_1 . ' tabindex="'.$tabindex.'"/>' . esc_html__('ISBN','teachpress') . '</label>');
        $tabindex++;
        TP_HTML::line('<label><input name="is_isbn" type="radio" value="0" id="is_isbn_1" ' . $checked_2 . ' tabindex="'.$tabindex.'"/>' . esc_html__('ISSN','teachpress') . '</label>');
        TP_HTML::line('</span>');
        TP_HTML::div_close('div_isbn');   
      
        // doi
        $tabindex++;
        TP_Admin::get_form_field(
            array(
                'name'      => 'doi',
                'title'     => esc_html__('DOI number','teachpress'),
                'label'     => esc_html__('DOI number','teachpress'),
                'type'      => 'input',
                'value'     => $pub_data['doi'],
                'tabindex'  => $tabindex,
                'display'   => 'block', 
                'style'     => 'width:95%;'),
            true );
        
        // urldate
        $display = ($pub_data["type"] === 'online' || $pub_data["type"] === 'periodical') ? 'style="display:block;"' : 'style="display:none;"';
        $title = esc_html__('The date you have visited the online resource','teachpress');
        $placeholder = esc_html__('YYYY-MM-DD','teachpress');
        $value = ($pub_id != 0) ? $pub_data["date"] : $placeholder;
        TP_HTML::line('<div id="div_urldate" ' . $display . '>');
        TP_HTML::line('<p><label for="urldate" title="' . $title . '"><b>' . esc_html__('Urldate','teachpress') . '</b></label></p>');
        $tabindex++;
        TP_HTML::line('<input type="text" name="urldate" id="urldate" title="' . $title . '" value="' . $value . '" placeholder="' . $placeholder . '" tabindex="'.$tabindex.'"/>');
        TP_HTML::div_close('div_urldate');
        
        // url
        TP_HTML::div_open('div_url');
        TP_HTML::line('<p><label for="url" title="' . esc_html__('URL/Files', 'teachpress') . '"><b>' . esc_html__('URL/Files', 'teachpress') . '</b></label> | ');
        TP_HTML::line('<a class="upload_button" style="cursor:pointer;" title="' . esc_html__('Insert a file from the WordPress Media Library','teachpress') . '"><i class="far fa-caret-square-up"></i> ' . esc_html__('Add/Upload','teachpress') . '</a></p>');
        TP_HTML::line('<input name="upload_mode" id="upload_mode" type="hidden" value="" />');
        $tabindex++;
        TP_HTML::line('<textarea name="url" type="text" id="url" class="upload" title="' . esc_html__('You can add one URL or file per line. Insert the name of the URL/file behind the address and separate it by a comma and a space. Example:', 'teachpress') . ' http://mywebsite.com/docs/readme.pdf, Basic Instructions" style="width:95%" rows="4" tabindex="'.$tabindex.'">' . $pub_data["url"] . '</textarea>');
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
        TP_HTML::line('<h3 class="tp_postbox"><span>' . esc_html__('Tags') . '</span></h3>');
        TP_HTML::div_open('inside');

        if ($pub_id != 0) {
            TP_Publication_Page::get_current_tags ( $pub_id );
        }

        // New tags field
        TP_HTML::line('<p><label for="tags"><b>' . esc_html__('New','teachpress') . '</b></label></p>');
        TP_HTML::line('<select name="tags[]" id="tags" tabindex="33" multiple style="width:90%;">');
        $tags = TP_Tags::get_tags( array('group_by' => true, 'output_type'   => ARRAY_A) );
        foreach ($tags as $row) {
            TP_HTML::line('<option value="' . esc_js($row['name']) . '">' . $row['name'] . '</option>');
        }
        TP_HTML::line('</select>');

        TP_HTML::div_close('inside');
        TP_HTML::div_close('postbox');
    }
    
    /**
     * Convert $tags array to a comma separate string
     * @param array $tags
     * @return string
     */
    public static function prepare_tags($tags) {
        $end = '';
        foreach ( $tags as $element ) {
            $end = ( $end === '' ) ? $element : $end . ',' . $element;
        }
        return $end;
    }
    
    /**
     * Checks the nonce field of the form. If the check fails wp_die() will be executed
     * @since 9.0.5
     */
    public static function check_nonce_field () {
        if ( ! isset( $_POST['tp_nonce'] ) 
            || ! wp_verify_nonce( $_POST['tp_nonce'], 'verify_teachpress_pub_edit' ) 
        ) {
           wp_die('teachPress error: This request could not be verified!');
           exit;
        }
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
                        alert('<?php esc_html_e('Please enter an author before!','teachpress') ?>');
                        return;
                    }
                    else {
                        author = editor;
                    }
                }
                if ( isNaN(year) ) {
                    alert('<?php esc_html_e('Please enter the date before!','teachpress') ?>');
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
