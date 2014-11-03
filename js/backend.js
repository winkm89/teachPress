// teachPress javascript for the admin menu

/**
 * Delele node
 * @param {type} id
 * @since 5.0.0
 */
function teachpress_del_node(id){
    jQuery(document).ready(function($) {
        $(id).remove();
    });
}

/**
 * for selecting all checkboxes on an admin page
 * @param {string} element_names
 * @param {string} checkbox_id
 * @since 3.0.0
 */
function teachpress_checkboxes(element_names, checkbox_id) {
    var switch_box = document.getElementById(checkbox_id);
    var checkbox = document.getElementsByName(element_names);
    var i;
    if (switch_box.checked === true) {
        for ( i = 0; i < checkbox.length; i++ ) {
            checkbox[i].checked = true;
        }
    }
    else {
        for ( i = 0; i < checkbox.length; i++ ) {
            checkbox[i].checked = false;
        }
    }
}

/**
 * for jumpmenu
 * @param {string} targ
 * @param {string} selObj
 * @param {string} restore
 * @since 1.0.0
 */
function teachpress_jumpMenu(targ,selObj,restore){
    eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
    if (restore) selObj.selectedIndex=0;
}

/**
 * for adding new tags
 * @param {string} tag
 * @since 4.2.0
 * @version 2
 */
function teachpress_inserttag(tag) {
    var old = document.getElementsByName("tags")[0].value;
    if ( old === "") {
        document.getElementsByName("tags")[0].value = tag;
    }
    else {
        old = old + ', ' + tag;
        document.getElementsByName("tags")[0].value = old;
    }	
}

/**
 * trim a string
 * @param {string} input
 * @returns {string}
 * @since 4.2.0
 */
function teachpress_trim (input) {
    input = input.replace(/^\s*(.*)/, "$1");
    input = input.replace(/(.*?)\s*$/, "$1");
    return input;
}

/**
 * for changing the color of a label
 * @param {int} id
 * @since 1.0.0
 */
function teachpress_change_label_color(id) {
    var checkbox = "checkbox_" + id;
    var label = "tag_label_" + id;
    if (document.getElementById(checkbox).checked === true) {
        document.getElementById(label).style.color = "#FF0000";
    }
    else {
        document.getElementById(label).style.color = "#333";
    }
}

/**
 * Generates a simple bibtex key
 * @since 4.2.0
 */
function teachpress_generate_bibtex_key() {
    var author = document.getElementById("author").value;
    var editor = document.getElementById("editor").value;
    var year = document.getElementById("date").value.substr(0,4);
    if ( author === '' ) {
        if ( editor === '' ) {
            alert('Please enter an author before!');
            return;
        }
        else {
            author = editor;
        }
    }
    if ( isNaN(year) ) {
        alert('Please enter the date before!');
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
    document.getElementById("bibtex").value = last_name + year;
}

/**
 * for show/hide buttons
 * @param {string} where
 * @since 1.0.0
 */
function teachpress_showhide(where) {
    var mode = "block";
    if (where === "show_all_fields" || where === "show_recommend_fields") {
        mode = "inline";
    }
    if (where === "tp-inline-edit-row") {
        mode = "table-row";
    }
    if (document.getElementById(where).style.display !== mode) {
    	document.getElementById(where).style.display = mode;
    }
    else {
     	document.getElementById(where).style.display = "none";
    }
}

/**
 * for switching rel_page options at add_course page
 * @returns {undefined}
 * @since 5.0.0
 */
function teachpress_switch_rel_page_container(){
    if (document.getElementById('rel_page_original').style.display !== "none") {
    	document.getElementById('rel_page_alternative').style.display = "block";
        document.getElementById('rel_page_original').style.display = "none";
    }
    else {
        document.getElementById('rel_page_alternative').style.display = "none";
        document.getElementById('rel_page_original').style.display = "block";
    }
}

/**
 * for show/hide sub course panel at add_course page
 * @returns {undefined}
 * @since 5.0.0
 */
function teachpress_courseFields () {
    var test = document.getElementById('parent2').value;
    if ( test === "0") {
        document.getElementById('sub_course_panel').style.display = "block";
    }
    else {
        document.getElementById('sub_course_panel').style.display = "none";
    }
}

/**
 * for edit tags
 * @param {int} tag_id
 * @since 1.0.0
 */
function teachpress_editTags(tag_id) {
    var parent = "tp_tag_row_" + tag_id;
    var message_text_field = "tp_tag_row_name_" + tag_id;
    var input_field = "tp_edit_tag_name";
    var text;

    if (isNaN(document.getElementById(input_field))) {
    }
    else {
        var reg = /<(.*?)>/g;
        text = document.getElementById(message_text_field).value;
        text = text.replace( reg, "" );
        // create div
        var editor = document.createElement('div');
        editor.id = "div_edit";
        // create hidden fields
        var field_neu = document.createElement('input');
        field_neu.name = "tp_edit_tag_id";
        field_neu.type = "hidden";
        field_neu.value = tag_id;
        // create textarea
        var tagname_new = document.createElement('input');
        tagname_new.id = input_field;
        tagname_new.name = input_field;
        tagname_new.value = text;
        tagname_new.style.width = "98%";
        // create save button
        var save_button = document.createElement('input');
        save_button.name = "tp_edit_tag_submit";
        save_button.value = "Save";
        save_button.type = "submit";
        save_button.className = "button-primary";
        // create cancel button
        var cancel_button = document.createElement('input');
        cancel_button.value = "Cancel";
        cancel_button.type = "button";
        cancel_button.className = "button";
        cancel_button.onclick = function () { document.getElementById(parent).removeChild(editor);};
        document.getElementById(parent).appendChild(editor);
        document.getElementById("div_edit").appendChild(field_neu);
        document.getElementById("div_edit").appendChild(tagname_new);
        document.getElementById("div_edit").appendChild(save_button);
        document.getElementById("div_edit").appendChild(cancel_button);
    }
}

/**
 * validate forms
 * @since 1.0.0
 */
function teachpress_validateForm() {
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=teachpress_validateForm.arguments;
    for (i = 0; i < (args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!=="") {
        if (test.indexOf('isEmail') !== -1) { p=val.indexOf('@');
          if (p < 1 || p === (val.length-1)) errors+='* '+nm+' must contain an e-mail address.\n';
        } else if ( test!== 'R') { num = parseFloat(val);
          if (isNaN(val)) errors+='* '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') !== -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='* '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) === 'R') errors += '* '+nm+' is required.\n'; }
    } if (errors) alert('Sorry, but you must relieve the following error(s):\n'+errors);
    document.teachpress_returnValue = (errors === '');
} }

/**
 * for show/hide bibtex fields
 * @param {string} mode
 * @since 2.0.0
 */
function teachpress_publicationFields(mode) {
    if ( mode === "std" || mode === "std2" ) {
        if ( mode === "std2" ) {
            teachpress_showhide("show_all_fields");
            teachpress_showhide("show_recommend_fields");
        }
        var test = document.getElementsByName("type")[0].value;
        // journal field
        if ( test === "article" || test === "periodical" ) {
            document.getElementById("div_journal").style.display = "block";
        }
        else {
            document.getElementById("div_journal").style.display = "none";
        }
        // volume field
        if (test === "article" || test === "book" || test === "booklet" || test === "collection" || test === "conference" || test === "inbook" || test ==="incollection" || test === "inproceedings" || test === "periodical" || test === "proceedings") {
            document.getElementById("div_volume").style.display = "block";
        }
        else {
            document.getElementById("div_volume").style.display = "none";
        }
        // number field
        if (test === "article" || test === "book" || test === "collection" || test === "conference" || test === "inbook" || test === "incollection" || test === "inproceedings" || test === "periodical" || test === "proceedings" || test === "techreport") {
            document.getElementById("div_number").style.display = "block";
        }
        else {
            document.getElementById("div_number").style.display = "none";
        }
        // pages field
        if (test === "article" || test === "conference" || test === "inbook" || test === "incollection" || test === "inproceedings") {
            document.getElementById("div_pages").style.display = "block";
        }
        else {
            document.getElementById("div_pages").style.display = "none";
        }
        // address field
        if (test === "book" || test === "booklet" || test === "collection" || test === "conference" || test === "inbook" || test === "incollection" || test === "inproceedings" || test === "manual" || test === "mastersthesis" || test === "phdthesis" || test === "proceedings" || test === "techreport") {
            document.getElementById("div_address").style.display = "block";
        }
        else {
            document.getElementById("div_address").style.display = "none";
        }
        // chapter field
        if (test === "inbook" || test === "incollection") {
            document.getElementById("div_chapter").style.display = "block";
        }
        else {
            document.getElementById("div_chapter").style.display = "none";
        }
        // institution field
        if (test === "techreport") {
            document.getElementById("div_institution").style.display = "block";
        }
        else {
            document.getElementById("div_institution").style.display = "none";
        }
        // school field
        if (test === "mastersthesis" || test === "phdthesis") {
            document.getElementById("div_school").style.display = "block";
        }
        else {
            document.getElementById("div_school").style.display = "none";
        }
        // series field
        if (test === "book" || test === "collection" || test === "conference" || test === "inbook" || test === "incollection" || test === "inproceedings" || test === "periodical" || test === "proceedings") {
            document.getElementById("div_series").style.display = "block";
        }
        else {
            document.getElementById("div_series").style.display = "none";
        }
        // howpublished field
        if (test === "booklet" || test === "misc") {
            document.getElementById("div_howpublished").style.display = "block";
        }
        else {
            document.getElementById("div_howpublished").style.display = "none";
        }
        // edition field
        if (test === "book" || test === "collection" || test === "inbook" || test === "incollection" || test === "manual") {
            document.getElementById("div_edition").style.display = "block";
        }
        else {
            document.getElementById("div_edition").style.display = "none";
        }
        // organization field
        if (test === "conference" || test === "inproceedings" || test === "manual" || test === "proceedings" || test === "online") {
            document.getElementById("div_organization").style.display = "block";
        }
        else {
            document.getElementById("div_organization").style.display = "none";
        }
        // techtype field
        if (test === "inbook" || test === "incollection" || test === "mastersthesis" || test === "phdthesis" || test === "techreport" ) {
            document.getElementById("div_techtype").style.display = "block";
        }
        else {
            document.getElementById("div_techtype").style.display = "none";
        }
        // booktitle field
        if (test === "conference" || test === "incollection" || test === "inproceedings") {
            document.getElementById("div_booktitle").style.display = "block";
        }
        else {
            document.getElementById("div_booktitle").style.display = "none";
        }
        // issuetitle
        if (test === "periodical") {
            document.getElementById("div_issuetitle").style.display = "block";
        }
        else {
            document.getElementById("div_issuetitle").style.display = "none";
        }
        // publisher field
        if (test === "book" || test === "collection" || test === "conference" || test === "inbook" || test === "incollection" || test === "inproceedings" || test === "proceedings") {
            document.getElementById("div_publisher").style.display = "block";
        }
        else {
            document.getElementById("div_publisher").style.display = "none";
        }
        // urldate field
        if (test === "online" || test === "periodical") {
            document.getElementById("div_urldate").style.display = "block";
        }
        else {
            document.getElementById("div_urldate").style.display = "none";
        }
        // key field
        document.getElementById("div_key").style.display = "none";
        // crossref field
        document.getElementById("div_crossref").style.display = "none";
    }
    else {
        teachpress_showhide("show_all_fields");
        teachpress_showhide("show_recommend_fields");
        document.getElementById("div_journal").style.display = "block";
        document.getElementById("div_volume").style.display = "block";
        document.getElementById("div_number").style.display = "block";
        document.getElementById("div_pages").style.display = "block";
        document.getElementById("div_address").style.display = "block";
        document.getElementById("div_chapter").style.display = "block";
        document.getElementById("div_institution").style.display = "block";
        document.getElementById("div_school").style.display = "block";
        document.getElementById("div_series").style.display = "block";
        document.getElementById("div_howpublished").style.display = "block";
        document.getElementById("div_edition").style.display = "block";
        document.getElementById("div_organization").style.display = "block";
        document.getElementById("div_techtype").style.display = "block";
        document.getElementById("div_booktitle").style.display = "block";
        document.getElementById("div_issuetitle").style.display = "block";
        document.getElementById("div_publisher").style.display = "block";
        document.getElementById("div_urldate").style.display = "block";
        document.getElementById("div_crossref").style.display = "block";
        document.getElementById("div_key").style.display = "block";
    }
}

/**
 * Make it possible to use the wordpress media uploader
 */
jQuery(document).ready(function() {
    var uploadID = '';
    var old = '';
    jQuery('.upload_button').click(function() {
        uploadID = jQuery(this).next('textarea');
        document.getElementById("upload_mode").value = "multiple";
        formfield = jQuery('.upload').attr('nam;');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });

    jQuery('.upload_button_image').click(function() {
        uploadID = jQuery(this).prev('input');
        formfield = jQuery('.upload').attr('name');
        document.getElementById("upload_mode").value = "single";
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });

    window.send_to_editor = function(html) {
        var imgurl = jQuery('img',html).attr('src');
        var sel = document.getElementById("upload_mode").value;
        if (typeof(imgurl) === "undefined") {
            imgurl = jQuery(html).attr('href');
        }
        if (sel === "multiple") {
            var old = document.getElementById("url");
            // IE
            if (document.selection){
                imgurl = old.value + imgurl;
            }
            // Firefox, Chrome, Safari, Opera
            else if (old.selectionStart || old.selectionStart == '0') {
                var startPos = old.selectionStart;
                var endPos = old.selectionEnd;
                var urlLength = imgurl.length;
                imgurl = old.value.substring(0, startPos) + imgurl + old.value.substring(endPos, old.value.length);
                old.selectionStart = startPos + urlLength;
                old.selectionEnd = startPos + urlLength;
            }
            // IE and others
            else {
                imgurl = old.value + imgurl;
            }
            old.focus();
            old.value = imgurl;
            tb_remove();
            return;
        }
        uploadID.val(imgurl);
        tb_remove();
    };
});