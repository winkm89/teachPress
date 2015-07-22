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
        'content'   => '<p><strong>' . __('Required fields','teachpress') . '</strong></p>
                        <p>' . __('The required fields for a new publication: title, author, bibtex key, tags','teachpress') . '</p>
                        <p><strong>' . __('URL/Files','teachpress') . '</strong></p>
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
 * @param string $year      for a return to the search
 * @param string $site      for a return to the search
 * @param string $limit      for a return to the search
 * @since 5.0.0
*/
function tp_add_publication_page() {
   
    // WordPress current unser info
    global $current_user;
    get_currentuserinfo();
    $user = $current_user->ID;
    $fields = get_tp_options('teachpress_pub','`setting_id` ASC', ARRAY_A);

    // form variables from add_publication.php
    $data = get_tp_var_types('publication_array');
    $data['title'] = isset( $_POST['post_title'] ) ? htmlspecialchars($_POST['post_title']) : '';
    $data['type'] = isset( $_POST['type'] ) ? htmlspecialchars($_POST['type']) : '';
    $data['bibtex'] = isset( $_POST['bibtex'] ) ? htmlspecialchars($_POST['bibtex']) : '';
    $data['author'] = isset( $_POST['author'] ) ? htmlspecialchars($_POST['author']) : '';
    $data['editor'] = isset( $_POST['editor'] ) ? htmlspecialchars($_POST['editor']) : '';
    $data['isbn'] = isset( $_POST['isbn'] ) ? htmlspecialchars($_POST['isbn']) : '';
    $data['url'] = isset( $_POST['url'] ) ? htmlspecialchars($_POST['url']) : '';
    $data['date'] = isset( $_POST['date'] ) ? htmlspecialchars($_POST['date']) : '';
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
    $data['doi'] = isset( $_POST['doi'] ) ? htmlspecialchars($_POST['doi']) : '';
    $data['rel_page'] = isset( $_POST['rel_page'] ) ? intval($_POST['rel_page']) : '';
    $data['is_isbn'] = isset( $_POST['is_isbn'] ) ? intval($_POST['is_isbn']) : '';

    $tags = isset( $_POST['tags'] ) ? htmlspecialchars($_POST['tags']) : '';
    $delbox = isset( $_POST['delbox'] ) ? $_POST['delbox'] : '';
    $bookmark = isset( $_POST['bookmark'] ) ? $_POST['bookmark'] : '';

    // from show_publications.php
    $pub_id = isset( $_REQUEST['pub_id'] ) ? intval($_REQUEST['pub_id']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
    $site = isset( $_GET['site'] ) ? htmlspecialchars($_GET['site']) : '';
    $tag_id = isset( $_GET['tag'] ) ? htmlspecialchars($_GET['tag']) : '';
    $year = isset( $_GET['year'] ) ? intval($_GET['year']) : '';
    $entry_limit = isset( $_GET['limit'] ) ? htmlspecialchars($_GET['limit']) : '';

    echo '<div class="wrap">';
    echo '<form name="form1" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '" id="form1">';
   
    // create related content (post/page/...)
    if ( isset($_POST['create_rel_content']) ) {
        $data['rel_page'] = tp_add_publication_as_post( $data['title'], $data['bibtex'], $data['date'], get_tp_option('rel_page_publications'), $tags, array(get_tp_option('rel_content_category')) );
    }
    
    // create publication and related page
    if ( isset($_POST['create_pub']) ) {
        $pub_id = tp_publications::add_publication($data, $tags, $bookmark);
        tp_db_helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        $message = __('Publication added','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php">' . __('Add new','teachpress') . '</a>';
        get_tp_message($message);
    }
    
    // save publication
    if ( isset($_POST['speichern']) ) {
        tp_publications::delete_pub_meta($pub_id);
        tp_publications::change_publication($pub_id, $data, $bookmark, $delbox, $tags);
        tp_db_helpers::prepare_meta_data($pub_id, $fields, $_POST, 'publications');
        get_tp_message( __('Saved') );
    }
    
    // Default vaulues
    if ( $pub_id != 0 ) {
        $pub_data = tp_publications::get_publication($pub_id, ARRAY_A);
        $pub_meta = tp_publications::get_pub_meta($pub_id);
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

    if ( $pub_id != 0 && !isset($_POST['create_pub']) ) {
        echo '<p style="margin-bottom:0px;"><a href="admin.php?page=publications.php&amp;search=' . $search . '&amp;filter=' .  $filter . '&amp;limit=' . $entry_limit . '&amp;tag=' . $tag_id . '&amp;year=' . $year . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __("Back",'teachpress') . '</a></p>';
    }
    
    // headline
    if ( $pub_id === 0 ) {
        echo '<h2>' . __('Add a new publication','teachpress') . '</h2>';
    }
    else {
        echo '<h2>' . __('Edit publication','teachpress') . ' <a href="admin.php?page=teachpress/addpublications.php" class="add-new-h2">' . __('Create','teachpress') . '</a></h2>';
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
        echo '<input type="hidden" name="year" id="year" value="' . $year . '" />';
    }
    
    echo '<div style="min-width:780px; width:100%;">';
    echo '<div style="width:30%; float:right; padding-right:2%; padding-left:1%;">';
    tp_publication_page::get_boobmarks_box ($pub_id, $user);
    tp_publication_page::get_tags_box ($pub_id);
    tp_publication_page::get_image_box ($pub_data);
    echo '</div>';
    
    echo '<div style="width:67%; float:left;">';
    echo '<div id="post-body">';
    echo '<div id="post-body-content">';
    
    echo '<div id="titlediv" style="padding-bottom: 15px;">';
    echo '<div id="titlewrap">';
    echo '<label class="hide-if-no-js" style="display:none;" id="title-prompt-text" for="title">' . __('Title','teachpress') . '</label>';
    echo '<input type="text" name="post_title" size="30" title="' . __('Title','teachpress') . '" tabindex="1" value="' . stripslashes($pub_data["title"]) . '" id="title" autocomplete="off" />';
    echo '</div>';
    echo '</div>';
    
    tp_publication_page::get_general_box ($pub_id, $pub_data);
    tp_publication_page::get_main_box ($pub_id, $pub_data);
    tp_publication_page::get_comments_box ($pub_data);
    if ( count($fields) !== 0 ) { 
        tp_admin::display_meta_data($fields, $pub_meta);       
    } 
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    tp_publication_page::print_scripts();
    echo '</div>';
}

/**
 * This class contains all funcitons for the add_publication_page
 * @package teachpress\admin\publications
 * @since 5.0.0
 */
class tp_publication_page {
    
    /**
     * Gets select boxes for all users which have at least one bookmark
     * @param int $pub_id   The ID of the publications
     * @param int $user     The ID of the current user
     * @since 5.0.0
     * @access private
     */
    private static function get_bookmarks ($pub_id, $user) {
        // search users with min. one bookmark
        $row = tp_publications::get_pub_users();
        foreach( $row as $row ) {
            $user_info = get_userdata($row->user);
            if ($user == $row->user || $user_info === false) {
                continue;
            }
            
            $test = ( $pub_id !== 0 ) ? tp_bookmarks::bookmark_exists($pub_id, $user_info->ID) : false;
            if ($test === true) {
                echo '<p><input type="checkbox" name="bookmark[]" id="bookmark_' . $user_info->ID . '" disabled="disabled" checked="checked"/> <label for="bookmark_' . $user_info->ID . '" class="tp_bookmarks_checked">' . $user_info->display_name . '</label></p>';
            }
            else {
                echo '<p><input type="checkbox" name="bookmark[]" id="bookmark_' . $user_info->ID . '" value="' . $user_info->ID . '" title="' . __('Bookmark for','teachpress') . ' ' . $user_info->display_name . '"/> <label for="bookmark_' . $user_info->ID . '" title="' . __('Bookmark for','teachpress') . ' ' . $user_info->display_name . '" class="tp_bookmarks">' . $user_info->display_name . '</label></p>';
            }
        }
    }


    /**
     * Gets the bookmarks box
     * @param int $pub_id       The ID of the publication
     * @param int $user         The ID of the current user
     * @since 5.0.0
     */
    public static function get_boobmarks_box ($pub_id, $user) {
        
        echo '<div class="postbox">';
        echo '<h3 class="tp_postbox"><span>' . __('Publications','teachpress') . '</span></h3>';
        echo '<div class="inside">';
        
        echo '<p><label for="bookmark" title="' . __('Add a publication to different publication lists','teachpress') . '"><strong>' . __('Bookmarks','teachpress') . '</strong></label></p>';
        echo '<div class="bookmarks" style="background-attachment: scroll; border:1px #DFDFDF solid; display: block; height: 100px; max-height: 205px; overflow-x: auto; overflow-y: auto; padding: 6px 11px;">';
        $test = ( $pub_id !== 0 ) ? tp_bookmarks::bookmark_exists($pub_id, $user) : false;
        if ( $test === true ) {
            echo '<p><input type="checkbox" name="bookmark[]" id="bookmark" disabled="disabled" checked="checked"/> <label for="bookmark">' . __('add to your own list','teachpress') . '</label></p>';
        }
        else {
            echo '<p><input type="checkbox" name="bookmark[]" id="bookmark" value="' . $user . '" title="' . __('Click to add the publication in your own list','teachpress') . '"/> <label for="bookmark" title="' . __('Click to add the publication in your own list','teachpress') . '">' . __('add to your own list','teachpress') . '</label></p>';
        }
        tp_publication_page::get_bookmarks ($pub_id, $user);
        echo '</div>';
        echo '</div>';
        
        echo '<div id="major-publishing-actions">';
        echo '<div style="text-align: center;"> ';
        if ($pub_id === 0) { ?>
              <input type="reset" name="Reset" value="<?php _e('Reset','teachpress'); ?>" id="teachpress_reset" class="button-secondary" style="padding-right: 30px;"><input name="create_pub" type="submit" class="button-primary" id="create_publication_submit" onclick="teachpress_validateForm('tags','','R','title','','R','bibtex','','R');return document.teachpress_returnValue;" value="<?php _e('Create','teachpress'); ?>">
        <?php } 
        else { 
            echo '<input type="submit" name="speichern" id="save_publication_submit" value="' . __('Save') . '" class="button-primary" title="' . __('Save') . '">';
        }  
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Gets the comment box
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_comments_box ($pub_data) {
        echo '<div class="postbox">';
        echo '<h3 class="tp_postbox"><span>' . __('Comments','teachpress') . '</span></h3>';
        echo '<div class="inside">';
        // comment
        echo tp_admin::get_form_field('comment', __('A not vissible private comment','teachpress'),__('private comment','teachpress'),'textarea', '', $pub_data["comment"], array(''), 31, 'width:95%; height: 75px;');
        // note
        echo tp_admin::get_form_field('note', __('Additional information','teachpress'),__('note','teachpress'),'textarea', '', $pub_data["note"], array(''), 32, 'width:95%; height: 75px;');
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Gets the current tags of a publication
     * @param int $pub_id   The ID of the publication
     * @since 5.0.0
     * @access private
     */
    private static function get_current_tags ( $pub_id ) {
        $current_tags = tp_tags::get_tags( array('pub_id' => $pub_id) );
        if ( count ($current_tags) === 0 ) {
            return;
        }
        echo '<p><strong>' . __('Current','teachpress') . '</strong></p>';
        foreach ($current_tags as $row){
            echo'<input name="delbox[]" type="checkbox" value="' . $row->con_id . '" id="checkbox_' . $row->con_id . '" onclick="teachpress_change_label_color(' . "'" . $row->con_id . "'" . ')"/> <label for="checkbox_' . $row->con_id . '" title="Tag &laquo;' . $row->name . '&raquo; ' . __('Delete','teachpress') . '" id="tag_label_' . $row->con_id . '">' . $row->name . '</label> | ';
        } 
    }
    
    /**
     * Gets the general box
     * @param int $pub_id       The ID of the publication
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_general_box ($pub_id, $pub_data){
        ?>
          <div class="postbox">
            <h3 class="tp_postbox"><span><?php _e('General information','teachpress'); ?></span></h3>
            <div class="inside">
               <table>
                <tr>
                <td style="border:none; padding:0 0 0 0; margin: 0 0 0 0;">
                    <p><label for="type" title="<?php _e('The type of publication','teachpress'); ?>"><strong><?php _e('Type'); ?></strong></label></p>
                    <select name="type" id="type" title="<?php _e('The type of publication','teachpress'); ?>" onchange="teachpress_publicationFields('std');" tabindex="2">
                        <?php echo get_tp_publication_type_options ($pub_data["type"], $mode = 'sng'); ?>
                    </select>
                </td>
                <td style="border:none; padding:0 0 0 0; margin: 0 0 0 0;">
                    <p><label for="bibtex" title="<?php _e('A simple unique key without spaces','teachpress'); ?>"><strong><?php _e('BibTeX Key','teachpress'); ?></strong></label></p>
                    <input name="bibtex" id="bibtex" type="text" title="<?php _e('A simple unique key without spaces','teachpress'); ?>" value="<?php echo stripslashes($pub_data["bibtex"]); ?>" tabindex="3" /> <a href="javascript:teachpress_generate_bibtex_key();" style="border:none;" title="<?php _e('Generate BibTeX key','teachpress') ?>"><img src="<?php echo plugins_url() . '/teachpress/images/view-refresh-3.png'; ?>" alt=""/></a>
                </td>
                </tr>
              </table>
                <?php
                // author
                echo tp_admin::get_form_field('author', __('The names of the authors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),__('Author(s)','teachpress'),'textarea', '', $pub_data["author"], array(''), 4, 'width:95%; height: 65px;');
                // editor
                echo tp_admin::get_form_field('editor', __('The names of the editors, separate by `and`. Example: Mark Twain and Albert Einstein','teachpress'),__('Editor(s)','teachpress'),'textarea', '', $pub_data["editor"], array(''), 5, 'width:95%; height: 65px;');
                ?>
          
             <p><label for="date"><strong><?php _e('date of publishing','teachpress'); ?></strong></label></p>
             <input type="text" name="date" id="date" title="<?php _e('date of publishing','teachpress'); ?>" value="<?php if ($pub_id != 0) { echo $pub_data["date"]; } else {_e('JJJJ-MM-TT','teachpress'); } ?>" onblur="if(this.value==='') this.value='<?php _e('JJJJ-MM-TT','teachpress'); ?>';" onfocus="if(this.value==='<?php _e('JJJJ-MM-TT','teachpress'); ?>') this.value='';" tabindex="6"/>
            </div>
          </div>
        <?php
    }

    /**
     * Gets the image box
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_image_box ($pub_data) {
        ?>
        <div class="postbox">
            <h3 class="tp_postbox"><span><?php _e('Image &amp; Related content','teachpress'); ?></span></h3>
            <div class="inside">
                <?php if ($pub_data["image_url"] != '') {
                    echo '<p><img name="tp_pub_image" src="' . $pub_data["image_url"] . '" alt="' . $pub_data["title"] . '" title="' . $pub_data["title"] . '" style="max-width:100%;"/></p>';
                } ?>
                <p><label for="image_url" title="<?php _e('With the image field you can add an image to a publication. You can display images in all publication lists','teachpress'); ?>"><strong><?php _e('Image URL','teachpress'); ?></strong></label></p>
                <input name="image_url" id="image_url" class="upload" type="text" title="<?php _e('With the image field you can add an image to a publication. You can display images in all publication lists','teachpress'); ?>" style="width:90%;" value="<?php echo $pub_data["image_url"]; ?>"/>
                <a class="upload_button_image" title="<?php _e('Add Image','teachpress'); ?>" style="cursor:pointer; border:none;"><img src="images/media-button-image.gif" alt="<?php _e('Add image','teachpress'); ?>" /></a>
                <p><label for="rel_page" title="<?php _e('Select a post/page with releated content.','teachpress'); ?>"><strong><?php _e('Related content','teachpress'); ?></strong></label></p>
                <div style="overflow:hidden;">
                <select name="rel_page" id="rel_page" title="<?php _e('Select a post/page with releated content.','teachpress'); ?>" style="width:90%;">
                <?php
                $post_type = get_tp_option('rel_page_publications');
                get_tp_wp_pages("menu_order", "ASC", $pub_data["rel_page"], $post_type, 0, 0); 
                ?>
                </select>
                <p style="padding:5px 0 0 5px;">
                    <?php 
                    $value = ( get_tp_option('rel_content_auto') == '1' ) ? '1' : '0';
                    echo tp_admin::get_checkbox('create_rel_content', __('Create related content','teachpress'), $value); 
                    ?>
                </p>
            </div>
              </div>
          </div>
        <?php
    }
    
    /**
     * Gets the main box
     * @param int $pub_id       The ID of the publication
     * @param array $pub_data   An associative array with publication data
     * @since 5.0.0
     */
    public static function get_main_box ($pub_id, $pub_data) {
        
        echo '<div class="postbox">';
        echo '<h3 class="tp_postbox"><span>' . __('Detailed information','teachpress') . '</span> <small><a id="show_all_fields" onclick="teachpress_publicationFields(' . "'" . 'all' . "'" . ');" style="cursor:pointer; display:inline;">' . __('Show all fields','teachpress') . '</a> <a id="show_recommend_fields" onclick="teachpress_publicationFields(' . "'" . 'std2' . "'" . ');" style="cursor:pointer; display:none;">' . __('Show recommend fields','teachpress') . '</a></small></h3>';
        echo '<div class="inside">';
        
        // booktitle
        echo tp_admin::get_form_field('booktitle', __('The title of a book','teachpress'), __('booktitle','teachpress'), 'textarea', $pub_data["type"], $pub_data["booktitle"], array('conference','incollection','inproceedings'), 7, 'width:95%; height: 58px;');

        // issuetitle
        echo tp_admin::get_form_field('issuetitle', __('The subtitle of a periodical publication','teachpress'), __('issuetitle','teachpress'), 'textarea', $pub_data["type"], $pub_data["issuetitle"], array('periodical'), 7, 'width:95%; height: 58px;');

        // journal
        echo tp_admin::get_form_field('journal', __('The title of a journal','teachpress'), __('journal','teachpress'), 'input', $pub_data["type"], $pub_data["journal"], array('article','periodical',''), 8, 'width:95%;');

        // volume
        echo tp_admin::get_form_field('volume', __('The volume of a journal or book','teachpress'), __('volume','teachpress'), 'input', $pub_data["type"],$pub_data["volume"], array('article','book','booklet','collection','conference','inbook','incollection','inproceedings','periodical','proceedings',''), 9);

        // number
        echo tp_admin::get_form_field('number', __('The number of a book, journal or work in a series','teachpress'), __('Number','teachpress'), 'input', $pub_data["type"], $pub_data["number"], array('article','book','collection','conference','inbook','incollection','inproceedings','periodical','proceedings','techreport',''), 10);

        // pages
        echo tp_admin::get_form_field('pages', __('The page you are referring to.','teachpress'), __('pages','teachpress'), 'input', $pub_data["type"], $pub_data["pages"], array('article','conference','inbook','incollection','inproceedings',''), 11);

        // publisher
        echo tp_admin::get_form_field('publisher', __('The names of publisher','teachpress'), __('publisher','teachpress'), 'input', $pub_data["type"], $pub_data["publisher"], array('book','collection','conference','inbook','incollection','inproceedings','proceedings'), 12, 'width:95%;');

        // address
        echo tp_admin::get_form_field('address', __('The address of the publisher or the place of confernece','teachpress'), __('address','teachpress'),'input', $pub_data["type"], $pub_data["address"] ,array('book','booklet','collection','conference','inbook','incollection','inproceedings','manual','mastersthesis','phdthesis','proceedings','techreport'), 13, 'width:95%;');

        // edition
        echo tp_admin::get_form_field('edition', __('The edition of a book','teachpress'), __('edition','teachpress'), 'input', $pub_data["type"], $pub_data["edition"], array('book','collection','inbook','incollection','manual'), 14);

        // chapter
        echo tp_admin::get_form_field('chapter', __('The chapter or the section number','teachpress'), __('chapter','teachpress'), 'input', $pub_data["type"], $pub_data["chapter"], array('inbook','incollection'), 15);

        // institution
        echo tp_admin::get_form_field('institution', __('The name of a sponsoring institution','teachpress'), __('institution','teachpress'), 'input', $pub_data["type"], $pub_data["institution"], array('techreport'), 16, 'width:95%;');

        // organization
        echo tp_admin::get_form_field('organization', __('The names of a sponsoring organization','teachpress'), __('organization','teachpress'), 'input', $pub_data["type"], $pub_data["organization"], array('conference','inproceedings','manual','proceedings','online'), 17, 'width:95%;');

        // school
        echo tp_admin::get_form_field('school', __('The names of the academic instituion where a thesis was written','teachpress'), __('school','teachpress'), 'input', $pub_data["type"], $pub_data["school"], array('mastersthesis','phdthesis'), 18, 'width:95%;');

        // series
        echo tp_admin::get_form_field('series', __('The name of a series','teachpress'), __('series','teachpress'), 'input', $pub_data["type"], $pub_data["series"], array('book','collection','conference','inbook','incollection','inproceedings','periodical','proceedings'), 19);

        // crossref
        echo tp_admin::get_form_field('crossref', __('The BibTeX key this work is referring to','teachpress'), __('crossref','teachpress'), 'input', 'nothing', $pub_data["crossref"], array(''), 20);

        // abstract
        echo tp_admin::get_form_field('abstract', __('A short summary of the publication','teachpress'), __('abstract','teachpress'), 'textarea', '', $pub_data["abstract"], array(''), 21, 'width:95%; height: 150px;');

        // howpublished
        echo tp_admin::get_form_field('howpublished', __('An unusual method for publishing','teachpress'), __('howpublished','teachpress'), 'input', $pub_data["type"], $pub_data["howpublished"], array('booklet','misc'), 22, 'width:95%;');

        // key
        echo tp_admin::get_form_field('key', __('If there is no author or editor given, so this field is used for the sorting.','teachpress'), __('Key','teachpress'), 'input', 'nothing', $pub_data["key"], array(''), 23);

        // techtype
        echo tp_admin::get_form_field('techtype', __('The type of a technical report, thesis, incollection or inbook.','teachpress'), __('Type'), 'input', $pub_data["type"], $pub_data["techtype"], array('inbook','incollection','mastersthesis','phdthesis','techreport'), 24);
             
        ?>
        <div id="div_isbn">
        <p><label for="isbn"><strong><?php _e('ISBN/ISSN','teachpress'); ?></strong></label></p>
        <input type="text" name="isbn" id="isbn" title="<?php _e('The ISBN or ISSN of the publication','teachpress'); ?>" value="<?php echo $pub_data["isbn"]; ?>" tabindex="25">
              <span style="padding-left:7px;">
                <label><input name="is_isbn" type="radio" id="is_isbn_0" value="1" <?php if ($pub_data["is_isbn"] == '1' || $pub_id === 0) { echo 'checked="checked"'; }?> tabindex="26"/><?php _e('ISBN','teachpress'); ?></label>
                <label><input name="is_isbn" type="radio" value="0" id="is_isbn_1" <?php if ($pub_data["is_isbn"] == '0') { echo 'checked="checked"'; }?> tabindex="27"/><?php _e('ISSN','teachpress'); ?></label>
              </span>
        </div>
        <?php
        // doi
        echo tp_admin::get_form_field('doi', __('DOI number','teachpress'), __('DOI number','teachpress'), 'input', '', $pub_data["doi"], array(''), 28, 'width:95%;');
        
        $display = ($pub_data["type"] === 'online' || $pub_data["type"] === 'periodical') ? 'style="display:block;"' : 'style="display:none;"';
        ?>
        <div id="div_urldate" <?php echo $display; ?>>
            <p><label for="urldate" title="<?php _e('The date you have visited the online resource','teachpress'); ?>"><strong><?php _e('Urldate','teachpress'); ?></strong></label></p>
        <input type="text" name="urldate" id="urldate" title="<?php _e('The date you have visited the online resource','teachpress'); ?>" value="<?php if ($pub_id != 0) { echo $pub_data["urldate"]; } else {_e('JJJJ-MM-TT','teachpress'); } ?>" onblur="if(this.value==='') this.value='<?php _e('JJJJ-MM-TT','teachpress'); ?>';" onfocus="if(this.value==='<?php _e('JJJJ-MM-TT','teachpress'); ?>') this.value='';" tabindex="29"/>
        </div>
        <div id="div_url">
           <p style="margin-bottom:0;"><label for="url" title="<?php _e('URL/Files', 'teachpress'); ?>"><strong><?php _e('URL/Files', 'teachpress'); ?></strong></label></p>
           <input name="upload_mode" id="upload_mode" type="hidden" value="" />
           <a class="upload_button" style="cursor:pointer; border:none; float:right; padding-right: 34px;" title="<?php _e('Insert a file from the WordPress Media Library','teachpress'); ?>"><?php _e('Add/Upload','teachpress'); ?> <img src="images/media-button-other.gif"/></a>
           <textarea name="url" type="text" id="url" class="upload" title="<?php echo __('You can add one URL or file per line. Insert the name of the URL/file behind the address and separate it by a comma and a space. Example:', 'teachpress') . ' http://mywebsite.com/docs/readme.pdf, Basic Instructions'; ?>" style="width:95%" rows="4" tabindex="30"><?php echo $pub_data["url"]; ?></textarea>
        </div>
           
        </div>
        </div>
        <?php
    }


    /**
     * Gets the tags box
     * @param int $pub_id   The ID of the publication
     * @since 5.0.0
     */
    public static function get_tags_box ($pub_id) {
            echo '<div class="postbox">';
            echo '<h3 class="tp_postbox"><span>' . __('Tags') . '</span></h3>';
            echo '<div class="inside">';
       
            if ($pub_id != 0) {
                tp_publication_page::get_current_tags ( $pub_id );
            }
            
            echo '<p><label for="tags"><strong>' . __('New (separate by comma)','teachpress'). '</strong></label></p>';
            echo '<input name="tags" type="text" id="tags" title="' . __('New (separate by comma)','teachpress'). '" style="width:95%">';
            echo '<div class="teachpress_cloud" style="padding-top:15px;">';
            
            // Font sizes
            $maxsize = 25;
            $minsize = 11;
           
            $temp = tp_tags::get_tag_cloud( array('number_tags' => 30, 'output_type' => ARRAY_A) );
            $max = $temp['info']->max;
            $min = ( $temp['info']->min === 1 ) ? 0 : $temp['info']->min;
            if ( count($temp['tags']) != 0 ) {
                foreach ($temp['tags'] as $tagcloud) {
                    $divisor = ( ($max - $min) === 0 ) ? 1 : ($max - $min);  // fix division through zero
                    $size = floor(( $maxsize * ( $tagcloud['tagPeak'] - $min ) / $divisor ));
                    if ($size < $minsize) {
                        $size = $minsize ;
                    }
                    echo '<span style="font-size:' . $size . 'px;"><a href="javascript:teachpress_inserttag(' . "'" . esc_js($tagcloud['name']) . "'" . ')" title="&laquo;' . $tagcloud['name'] . '&raquo; ' . __('add as tag','teachpress') . '">' . $tagcloud['name'] . '</a></span>';
                }
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
    }
    
    /**
     * Gets the javascripts for this page
     * @since 5.0.0
     */
    public static function print_scripts () {
        ?>
        <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            $('#date').datepicker({showWeek: true, changeMonth: true, changeYear: true, showOtherMonths: true, firstDay: 1, renderer: $.extend({}, $.datepicker.weekOfYearRenderer), onShow: $.datepicker.showStatus, dateFormat: 'yy-mm-dd', yearRange: '1950:c+5'});
            $('#urldate').datepicker({showWeek: true, changeMonth: true, changeYear: true, showOtherMonths: true, firstDay: 1, renderer: $.extend({}, $.datepicker.weekOfYearRenderer), onShow: $.datepicker.showStatus, dateFormat: 'yy-mm-dd', yearRange: '1990:c+5'});
            $('#abstract').resizable({handles: "se", minHeight: 80, minWidth: 500});
            $('#url').resizable({handles: "se", minHeight: 80, minWidth: 500});
            $('#comment').resizable({handles: "se", minHeight: 70, minWidth: 400});
            $('#note').resizable({handles: "se", minHeight: 70, minWidth: 400});

            var availableTags = [
                <?php
                $sql = tp_tags::get_tags( array('group_by' => true) );
                $start = '';
                foreach ($sql as $row) {
                    if ( $start === '' ) {
                        echo '"' . esc_js($row->name) . '"';
                        $start = '1';
                    }
                    else {
                        echo ',"' . esc_js($row->name) . '"';
                    }
                } ?>];
                            
            var availableAuthors = [
                <?php
                $start2 = '';
                $sql2 = tp_authors::get_authors( array('group_by' => true, 'include_editors' =>true) );
                foreach ($sql2 as $row) {
                    if ( $start2 === '' ) {
                        echo '"' . $row->name . '"';
                        $start2 = '1';
                    }
                    else {
                        echo ',"' . $row->name . '"';
                    }        
                } ?>];
            
            
            function split( val ) {
                return val.split( /,\s*/ );
            }

            function split_authors( val ) {
                return val.split( /\sand\s*/ );
            }

            function extractLast( term ) {
                return split( term ).pop();
            }
            
            function extractLast_authors( term ) {
                return split_authors( term ).pop();
            }

            $( "#tags" )
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