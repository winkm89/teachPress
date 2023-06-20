<?php
/**
 * The citation object class
 * @since 5.2.0
 */
class TP_Cite_Object {
    
    // stores the association between id and citation
    var $cite_object = array();
    
    /**
     * Returns the current count in the cite
     * @return int
     * @since 5.2.0
     */
    public function get_count() {
        return count($this->cite_object);
    }
    
    /**
     * Adds a citation to the cite object
     * @param array $cite
     * @return the index of the citation in the cite object
     * @since 5.2.0
     */
    public function add_ref($cite) {
        // Get global option
        $ref_grouped = ( get_tp_option('ref_grouped') == '1' ) ? true : false;

        // add the citation
        if ($ref_grouped) {
            // did we already added this citation?
            $existing_index = array_search($cite['pub_id'], array_keys($this->cite_object));
            if ($existing_index === false) {
                // first time we see this publication, adding it
                $this->cite_object[$cite['pub_id']] = $cite;
                // so this publication is the last of our list
                return $this->get_count();
            } else {
                // we already added this publication in the past
                return $existing_index + 1;
            }
        } else {
            // add this citation
            array_push($this->cite_object, $cite);
            // so this publication is the last of our list
            return $this->get_count();
        }
    }
    
    /**
     * Returns the cite object
     * @return array
     * @since 5.2.0
     */
    public function get_ref() {
        return array_values($this->cite_object);
    }
}

