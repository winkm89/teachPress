<?php
/**
 * Registers all default publication types
 * @since 8.0.0
 */
function tp_register_all_publication_types() {
    // Article
    tp_register_publication_type(
        array(
            'type_slug'         => 'article',
            'bibtex_key_ext'    => 'article',
            'i18n_singular'     => esc_html__('Journal Article','teachpress'),
            'i18n_plural'       => esc_html__('Journal Articles','teachpress'),
            'default_fields'    => array('journal', 'volume', 'number', 'issue', 'pages'),
            'html_meta_row'     => '{IN}{journal}{volume}{issue}{number}{pages}{year}{isbn}{note}' 
        ) );
    
    // Book
    tp_register_publication_type(
        array(
            'type_slug'         => 'book',
            'bibtex_key_ext'    => 'book',
            'i18n_singular'     => esc_html__('Book','teachpress'),
            'i18n_plural'       => esc_html__('Books','teachpress'),
            'default_fields'    => array('volume', 'number', 'publisher', 'address', 'edition', 'series'),
            'html_meta_row'     => '{edition}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Booklet
    tp_register_publication_type(
        array(
            'type_slug'         => 'booklet',
            'bibtex_key_ext'    => 'booklet',
            'i18n_singular'     => esc_html__('Booklet','teachpress'),
            'i18n_plural'       => esc_html__('Booklets','teachpress'),
            'default_fields'    => array('volume', 'address', 'howpublished'),
            'html_meta_row'     => '{howpublished}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Collection
    tp_register_publication_type(
        array(
            'type_slug'         => 'collection',
            'bibtex_key_ext'    => 'collection',
            'i18n_singular'     => esc_html__('Collection','teachpress'),
            'i18n_plural'       => esc_html__('Collections','teachpress'),
            'default_fields'    => array('booktitle', 'volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series'), 
            'html_meta_row'     => '{edition}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Conference
    tp_register_publication_type(
        array(
            'type_slug'         => 'conference',
            'bibtex_key_ext'    => 'conference',
            'i18n_singular'     => esc_html__('Conference','teachpress'),
            'i18n_plural'       => esc_html__('Conferences','teachpress'), 
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
            'bibtex_key_ext'    => 'mastersthesis',
            'i18n_singular'     => esc_html__('Bachelor Thesis','teachpress'), 
            'i18n_plural'       => esc_html__('Bachelor Theses','teachpress'),   
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
            'i18n_singular'     => esc_html__('Diploma Thesis','teachpress'), 
            'i18n_plural'       => esc_html__('Diploma Theses','teachpress'),   
            'default_fields'    => array('address', 'school', 'techtype'),   
            'html_meta_row'     => '{school}{address}{year}{isbn}{note}' 
        ) );
    
    // Inbook
    tp_register_publication_type(
        array(
            'type_slug'         => 'inbook',
            'bibtex_key_ext'    => 'inbook',
            'i18n_singular'     => esc_html__('Book Chapter','teachpress'), 
            'i18n_plural'       => esc_html__('Book Chapters','teachpress'), 
            'default_fields'    => array('volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series'), 
            'html_meta_row'     => '{IN}{editor}{booktitle}{volume}{number}{chapter}{pages}{publisher}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Incollection
    tp_register_publication_type(
        array(
            'type_slug'         => 'incollection',
            'bibtex_key_ext'    => 'incollection',
            'i18n_singular'     => esc_html__('Book Section','teachpress'),
            'i18n_plural'       => esc_html__('Book Sections','teachpress'),
            'default_fields'    => array('volume', 'number', 'pages', 'publisher', 'address', 'edition', 'chapter', 'series', 'techtype'),  
            'html_meta_row'     => '{IN}{editor}{booktitle}{volume}{number}{pages}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Inproceedings
    tp_register_publication_type(
        array(
            'type_slug'         => 'inproceedings',
            'bibtex_key_ext'    => 'inproceedings',
            'i18n_singular'     => esc_html__('Proceedings Article','teachpress'),
            'i18n_plural'       => esc_html__('Proceedings Articles','teachpress'),
            'default_fields'    => array('booktitle', 'volume', 'number', 'pages', 'publisher', 'address', 'organization', 'series'),   
            'html_meta_row'     => '{IN}{editor}{booktitle}{pages}{organization}{publisher}{address}{year}{isbn}{note}' 
        ) );
    
    // Manual
    tp_register_publication_type(
        array(
            'type_slug'         => 'manual',
            'bibtex_key_ext'    => 'manual',
            'i18n_singular'     => esc_html__('Technical Manual','teachpress'),
            'i18n_plural'       => esc_html__('Technical Manuals','teachpress'),  
            'default_fields'    => array('address', 'edition', 'organization', 'series'),   
            'html_meta_row'     => '{editor}{organization}{address}{edition}{year}{isbn}{note}' 
        ) );
    
    // Masters Thesis
    tp_register_publication_type(
        array(
            'type_slug'         => 'mastersthesis',
            'bibtex_key_ext'    => 'mastersthesis',
            'i18n_singular'     => esc_html__('Masters Thesis','teachpress'), 
            'i18n_plural'       => esc_html__('Masters Theses','teachpress'),   
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
            'i18n_singular'     => esc_html__('Medium','teachpress'),
            'i18n_plural'       => esc_html__('Media','teachpress'), 
            'default_fields'    => array('publisher', 'address', 'howpublished'), 
            'html_meta_row'     => '{publisher}{address}{howpublished}{year}{urldate}{note}' 
        ) );
    
    // Misc
    tp_register_publication_type(
        array(
            'type_slug'         => 'misc',
            'bibtex_key_ext'    => 'misc',
            'i18n_singular'     => esc_html__('Miscellaneous','teachpress'),
            'i18n_plural'       => esc_html__('Miscellaneous','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{year}{isbn}{note}' 
        ) );
    
    // Online
    tp_register_publication_type(
        array(
            'type_slug'         => 'online',
            'bibtex_key_ext'    => 'online',
            'i18n_singular'     => esc_html__('Online','teachpress'),
            'i18n_plural'       => esc_html__('Online','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{editor}{organization}{year}{urldate}{note}' 
        ) );
    
    // Patent
    tp_register_publication_type(
        array(
            'type_slug'         => 'patent',
            'bibtex_key_ext'    => 'patent',
            'i18n_singular'     => esc_html__('Patent','teachpress'),
            'i18n_plural'       => esc_html__('Patents','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{number}{year}{note}' 
        ) );
    
    // Periodical
    tp_register_publication_type(
        array(
            'type_slug'         => 'periodical',
            'bibtex_key_ext'    => 'periodical',
            'i18n_singular'     => esc_html__('Periodical','teachpress'),
            'i18n_plural'       => esc_html__('Periodicals','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{issuetitle}{series}{volume}{number}{year}{urldate}{isbn}{note}' 
        ) );
    
    // PhD Thesis
    tp_register_publication_type(
        array(
            'type_slug'         => 'phdthesis',
            'bibtex_key_ext'    => 'phdthesis',
            'i18n_singular'     => esc_html__('PhD Thesis','teachpress'),
            'i18n_plural'       => esc_html__('PhD Theses','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{school}{year}{isbn}{note}' 
        ) );
    
    // Presentation
    tp_register_publication_type(
        array(
            'type_slug'         => 'presentation',
            'bibtex_key_ext'    => 'presentation',
            'i18n_singular'     => esc_html__('Presentation','teachpress'),
            'i18n_plural'       => esc_html__('Presentations','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{address}{date}{isbn}{note}' 
        ) );
    
    // Proceedings
    tp_register_publication_type(
        array(
            'type_slug'         => 'proceedings',
            'bibtex_key_ext'    => 'proceedings',
            'i18n_singular'     => esc_html__('Proceedings','teachpress'),
            'i18n_plural'       => esc_html__('Proceedings','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{organization}{publisher}{address}{volume}{number}{year}{isbn}{note}' 
        ) );
    
    // Techreport
    tp_register_publication_type(
        array(
            'type_slug'         => 'techreport',
            'bibtex_key_ext'    => 'techreport',
            'i18n_singular'     => esc_html__('Technical Report','teachpress'),
            'i18n_plural'       => esc_html__('Technical Reports','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{institution}{address}{techtype}{number}{year}{isbn}{note}' 
        ) );
    
    // Unpublished
    tp_register_publication_type(
        array(
            'type_slug'         => 'unpublished',
            'bibtex_key_ext'    => 'unpublished',
            'i18n_singular'     => esc_html__('Unpublished','teachpress'),
            'i18n_plural'       => esc_html__('Unpublished','teachpress'),
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{year}{isbn}{note}' 
        ) );
    
    // Working paper
    tp_register_publication_type(
        array(
            'type_slug'         => 'workingpaper',
            'bibtex_key_ext'    => 'misc',
            'i18n_singular'     => esc_html__('Working paper','teachpress'),
            'i18n_plural'       => esc_html__('Working papers','teachpress'), 
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{howpublished}{year}{isbn}{note}' 
        ) );
    
    // Workshop
    tp_register_publication_type(
        array(
            'type_slug'         => 'workshop',
            'bibtex_key_ext'    => 'workshop',
            'i18n_singular'     => esc_html__('Workshop','teachpress'),
            'i18n_plural'       => esc_html__('Workshops','teachpress'),
            'default_fields'    => array('howpublished'), 
            'html_meta_row'     => '{booktitle}{volume}{number}{series}{organization}{publisher}{address}{year}{isbn}{note}' 
        ) );

}
