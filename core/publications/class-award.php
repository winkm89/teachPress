<?php
/**
 * The award object class
 * @since 9.0.0
 */
class TP_Award {
    protected $pub_awards = array();
    
    /**
     * Register a page
     * 
     * @param array $atts {                
     *      @type string $award_slug            The internal key fo the award
     *      @type string $i18n_singular         The singular label for the award
     *      @type string $i18n_plural           The plural label for the award
     * }
     */
    public function register($atts){
        // define defaults
        $param = shortcode_atts(array(
            'award_slug'        => '',
            'i18n_singular'     => '',
            'i18n_plural'       => '',
            'icon'              => ''
        ), $atts);
        
        if ( $param['award_slug'] !== '' ) {
            $this->pub_awards[ $param['award_slug'] ] = array(
                'award_slug'        => $param['award_slug'],
                'i18n_singular'     => $param['i18n_singular'],
                'i18n_plural'       => $param['i18n_plural'],
                'icon'              => $param['icon']
            );
        }
    }
    
    /**
     * Returns all registered pages as array
     * @return array
     */
    public function get() {
        return $this->pub_awards;
    }
    
    /**
     * Returns the page data by the given award_slug
     * @param string $award_slug
     * @return array
     */
    public function get_data($award_slug) {
        if ( isset( $this->pub_awards[$award_slug] ) ) {
            return $this->pub_awards[$award_slug];
        }
        return null;
    } 
}

/**
 * Registers a award
 * @global type $tp_awards
 * @param array $atts
 * @since 9.0.0
 */
function tp_register_award($atts) {
    global $tp_awards;
    
    // Instance the object if it's not done yet
    if ( ! ( $tp_awards instanceof TP_Award ) ) {
        $tp_awards = new TP_Award();
    }
    
    $tp_awards->register($atts);
}