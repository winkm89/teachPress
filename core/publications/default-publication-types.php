<?php
/**
 * Registers all default publication types
 * @since 7.2.0
 */
function tp_register_all_publication_types() {
    // Article
    tp_register_publication_type(
        array(
            'type_slug'         => 'article',
            'bibtex_key_ext'    => 'article',
            'i18n_singular'     => __('Journal Article','teachpress'),
            'i18n_plural'       => __('Journal Articles','teachpress'),
            'default_fields'    => array('journal', 'volume', 'number', 'pages'),
            'html_meta_row'     => '{IN}{journal}{volume}{number}{pages}{year}{isbn}{note}' 
        ) );
    
    // Book
    tp_register_publication_type(
        array(
            'type_slug'         => 'book',
            'bibtex_key_ext'    => 'book',
            'i18n_singular'     => __('Book','teachpress'),
            'i18n_plural'       => __('Books','teachpress'),
            'default_fields'    => array('volume', 'number', 'publisher', 'address', 'edition', 'series'),
            'html_meta_row'     => '{edition}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Booklet
    tp_register_publication_type(
        array(
            'type_slug'         => 'booklet',
            'bibtex_key_ext'    => 'booklet',
            'i18n_singular'     => __('Booklet','teachpress'),
            'i18n_plural'       => __('Booklets','teachpress'),
            'default_fields'    => array('volume', 'address', 'howpublished'),
            'html_meta_row'     => '{howpublished}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Collection
    tp_register_publication_type(
        array(
            'type_slug'         => 'collection',
            'bibtex_key_ext'    => 'collection',
            'i18n_singular'     => __('Collection','teachpress'),
            'i18n_plural'       => __('Collections','teachpress'),
            'default_fields'    => array('booktitle', 'volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series'), 
            'html_meta_row'     => '{edition}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Conference
    tp_register_publication_type(
        array(
            'type_slug'         => 'conference',
            'bibtex_key_ext'    => 'conference',
            'i18n_singular'     => __('Conference','teachpress'),
            'i18n_plural'       => __('Conferences','teachpress'), 
            'default_fields'    => array('booktitle', 'volume', 'number', 'pages', 'publisher', 'address', 'organization', 'series'), 
            'html_meta_row'     => '{booktitle}{volume}{number}{series}{organization}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    /**
     * Bachelor Thesis
     * Note: We use mastersthesis as bibtex key for compatibility reasons
     */
    tp_register_publication_type(
        array(
            'type_slug'         => 'bachelorthesis',
            'bibtex_key_ext'    => 'thesis',
            'i18n_singular'     => __('Bachelor Thesis','teachpress'), 
            'i18n_plural'       => __('Bachelor Theses','teachpress'),   
            'default_fields'    => array('address', 'school', 'techtype'),   
            'html_meta_row'     => '{school}{address}{year}{isbn}{note}' 
        ) );
    
    /**
     * Diploma Thesis
     * Note: We use mastersthesis as bibtex key for compatibility reasons
     */
    tp_register_publication_type(
        array(
            'type_slug'         => 'diplomathesis',
            'bibtex_key_ext'    => 'mastersthesis',
            'i18n_singular'     => __('Diploma Thesis','teachpress'), 
            'i18n_plural'       => __('Diploma Theses','teachpress'),   
            'default_fields'    => array('address', 'school', 'techtype'),   
            'html_meta_row'     => '{school}{address}{year}{isbn}{note}' 
        ) );
    
    // Inbook
    tp_register_publication_type(
        array(
            'type_slug'         => 'inbook',
            'bibtex_key_ext'    => 'inbook',
            'i18n_singular'     => __('Book Chapter','teachpress'), 
            'i18n_plural'       => __('Book Chapters','teachpress'), 
            'default_fields'    => array('volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series'), 
            'html_meta_row'     => '{IN}{editor}{booktitle}{volume}{number}{chapter}{pages}{publisher}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Incollection
    tp_register_publication_type(
        array(
            'type_slug'         => 'incollection',
            'bibtex_key_ext'    => 'incollection',
            'i18n_singular'     => __('Incollection','teachpress'), 
            'i18n_plural'       => __('Incollections','teachpress'), 
            'default_fields'    => array('volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series', 'techtype'),  
            'html_meta_row'     => '{IN}{editor}{booktitle}{volume}{number}{pages}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Inproceedings
    tp_register_publication_type(
        array(
            'type_slug'         => 'inproceedings',
            'bibtex_key_ext'    => 'inproceedings',
            'i18n_singular'     => _x('Inproceedings','Singular form of inproceedings, if it exists','teachpress'),
            'i18n_plural'       => __('Inproceedings','teachpress'),  
            'default_fields'    => array('booktitle', 'volume', 'number', 'pages', 'publisher', 'address', 'organization', 'series'),   
            'html_meta_row'     => '{IN}{editor}{booktitle}{pages}{organization}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Manual
    tp_register_publication_type(
        array(
            'type_slug'         => 'manual',
            'bibtex_key_ext'    => 'manual',
            'i18n_singular'     => __('Technical Manual','teachpress'),
            'i18n_plural'       => __('Technical Manuals','teachpress'),  
            'default_fields'    => array('address', 'edition', 'organization', 'series'),   
            'html_meta_row'     => '{editor}{organization}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Masters Thesis
    tp_register_publication_type(
        array(
            'type_slug'         => 'mastersthesis',
            'bibtex_key_ext'    => 'mastersthesis',
            'i18n_singular'     => __('Masters Thesis','teachpress'), 
            'i18n_plural'       => __('Masters Theses','teachpress'),   
            'default_fields'    => array('address', 'school', 'techtype'),   
            'html_meta_row'     => '{school}{address}{year}{isbn}{note}' 
        ) );
    
    /**
     * Media
     * @link https://github.com/winkm89/teachPress/issues/110 
     */
    tp_register_publication_type(
        array(
            'type_slug'         => 'media',
            'bibtex_key_ext'    => 'misc',
            'i18n_singular'     => __('Medium','teachpress'),
            'i18n_plural'       => __('Media','teachpress'), 
            'default_fields'    => array('publisher', 'address', 'howpublished'), 
            'html_meta_row'     => '{publisher}{address}{howpublished}{year}{urldate}{note}' 
        ) );
    
    // Misc
    tp_register_publication_type(
        array(
            'type_slug'         => 'misc',
            'bibtex_key_ext'    => 'misc',
            'i18n_singular'     => __('Miscellaneous','teachpress'),
            'i18n_plural'       => __('Miscellaneous','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{year}{isbn}{note}' 
        ) );
    
    // Online
    tp_register_publication_type(
        array(
            'type_slug'         => 'online',
            'bibtex_key_ext'    => 'online',
            'i18n_singular'     => __('Online','teachpress'),
            'i18n_plural'       => __('Online','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{editor}{organization}{year}{urldate}{note}' 
        ) );
    
    // Patent
    tp_register_publication_type(
        array(
            'type_slug'         => 'patent',
            'bibtex_key_ext'    => 'patent',
            'i18n_singular'     => __('Patent','teachpress'),
            'i18n_plural'       => __('Patents','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{number}{year}{note}' 
        ) );
    
    // Periodical
    tp_register_publication_type(
        array(
            'type_slug'         => 'periodical',
            'bibtex_key_ext'    => 'periodical',
            'i18n_singular'     => __('Periodical','teachpress'),
            'i18n_plural'       => __('Periodicals','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{issuetitle}{series}{volume}{number}{year}{urldate}{isbn}{note}' 
        ) );
    
    // PhD Thesis
    tp_register_publication_type(
        array(
            'type_slug'         => 'phdthesis',
            'bibtex_key_ext'    => 'phdthesis',
            'i18n_singular'     => __('PhD Thesis','teachpress'),
            'i18n_plural'       => __('PhD Theses','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{school}{year}{isbn}{note}' 
        ) );
    
    // Presentation
    tp_register_publication_type(
        array(
            'type_slug'         => 'presentation',
            'bibtex_key_ext'    => 'presentation',
            'i18n_singular'     => __('Presentation','teachpress'),
            'i18n_plural'       => __('Presentations','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{address}{date}{isbn}{note}' 
        ) );
    
    // Proceedings
    tp_register_publication_type(
        array(
            'type_slug'         => 'proceedings',
            'bibtex_key_ext'    => 'proceedings',
            'i18n_singular'     => __('Proceeding','teachpress'),
            'i18n_plural'       => __('Proceedings','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{organization}{publisher}{address}{volume}{number}{year}{isbn}{note}' 
        ) );
    
    // Techreport
    tp_register_publication_type(
        array(
            'type_slug'         => 'techreport',
            'bibtex_key_ext'    => 'techreport',
            'i18n_singular'     => __('Technical Report','teachpress'),
            'i18n_plural'       => __('Technical Reports','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{institution}{address}{techtype}{number}{year}{isbn}{note}' 
        ) );
    
    // Unpublished
    tp_register_publication_type(
        array(
            'type_slug'         => 'unpublished',
            'bibtex_key_ext'    => 'unpublished',
            'i18n_singular'     => __('Unpublished','teachpress'),
            'i18n_plural'       => __('Unpublished','teachpress'),
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{year}{isbn}{note}' 
        ) );
    
    // Workshop
    tp_register_publication_type(
        array(
            'type_slug'         => 'workshop',
            'bibtex_key_ext'    => 'workshop',
            'i18n_singular'     => __('Workshop','teachpress'),
            'i18n_plural'       => __('Workshops','teachpress'),
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{booktitle}{volume}{number}{series}{organization}{publisher}{address}{year}{isbn}{note}' 
        ) );

}
