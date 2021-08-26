<?php
/**
 * The publication object class
 * @since 8.0.0
 */
class TP_Publication_Type {
    protected $pub_types = array();
    
    /**
     * Register a page
     * 
     * @param array $atts {                
     *      @type string $type_slug             The internal key fo the type
     *      @type string $bibtex_key_ext        The external bibtex key for the type
     *      @type string $i18n_singular         The singular label for the type
     *      @type string $i18n_plural           The plural label for the type
     *      @type array $default_fields         An array with the default fields fo the type
     *      @type string html_meta_row          A template string for the HTML meta row
     * }
     */
    public function register($atts){
        // define defaults
        $param = shortcode_atts(array(
            'type_slug'         => '',
            'bibtex_key_ext'    => '',
            'i18n_singular'     => '',
            'i18n_plural'       => '',
            'default_fields'    => '',
            'html_meta_row'     => ''
        ), $atts);
        
        if ( $param['type_slug'] !== '' ) {
            $this->pub_types[ $param['type_slug'] ] = array(
                'type_slug'         => $param['type_slug'],
                'bibtex_key_ext'    => $param['bibtex_key_ext'],
                'i18n_singular'     => $param['i18n_singular'],
                'i18n_plural'       => $param['i18n_plural'],
                'default_fields'    => $param['default_fields'],
                'html_meta_row'     => $param['html_meta_row']
            );
        }
    }
    
    /**
     * Returns all registered pages as array
     * @return array
     */
    public function get() {
        return $this->pub_types;
    }
    
    /**
     * Returns the page data by the given type_slug
     * @param string $type_slug
     * @return array
     */
    public function get_data($type_slug) {
        if ( isset( $this->pub_types[$type_slug] ) ) {
            return $this->pub_types[$type_slug];
        }
        return null;
    } 
}

/**
 * Registers a publication type
 * @global type $tp_publication_types
 * @param array $atts
 */
function tp_register_publication_type($atts) {
    global $tp_publication_types;
    
    // Instance the object if it's not done yet
    if ( ! ( $tp_publication_types instanceof TP_Publication_Type ) ) {
        $tp_publication_types = new TP_Publication_Type();
    }
    
    $tp_publication_types->register($atts);
}