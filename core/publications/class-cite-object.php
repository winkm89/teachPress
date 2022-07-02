<?php
/**
 * The citation object class
 * @since 5.2.0
 */
class TP_Cite_Object {
    
    // stores the citations
    var $cite_object = array();
    
    /**
     * Returns the current count in the cite
     * @return int
     * @since 5.2.0
     */
    public function get_count() {
        $count = count($this->cite_object);
        return $count;
    }
    
    /**
     * Adds a citation to the cite object
     * @param array $cite
     * @since 5.2.0
     */
    public function add_ref($cite) {
        $count = $this->get_count();
        $count = $count + 1;
        array_push($this->cite_object, $cite);
    }
    
    /**
     * Returns the cite object
     * @return array
     * @since 5.2.0
     */
    public function get_ref() {
        return $this->cite_object;
    }
}

