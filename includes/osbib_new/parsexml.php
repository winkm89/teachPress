<?php
/**
 * XML Parser
 * @author Mark Grimshaw
 */
class osbib_parsexml {
    function __construct() {
    }
    // Grab a complete XML entry
    function getEntry($entries) {
        // entries now elements in $entries array
        foreach($entries as $entry) {
            // create root node in node array
            $this->nodeStack = array();
            $this->startElement(NULL, 'ROOT', array());
            // complete $xmlString and parse it
            $xmlString = "<style>" . $entry . "</style>";
            $this->entries[] = $this->parse($xmlString);
        }
    }

    // This method starts the whole process
    function extractEntries($fh) {
        $this->entries = array();
        while( !feof($fh) ) {
            if(preg_match_all("/<style.*>(.*)<\/style>/Ui", trim(fgets($fh)), $startEntry)) {
                    $this->getEntry($startEntry[1]);
            }
        }
        if( empty($this->entries) ) {
            $this->entries = FALSE;
        }
        $info['name'] = $this->entries[0]['_ELEMENTS'][0]['_ELEMENTS'][0]['_DATA'];
        $info['description'] = $this->entries[0]['_ELEMENTS'][0]['_ELEMENTS'][1]['_DATA'];
        $info['language'] = $this->entries[0]['_ELEMENTS'][0]['_ELEMENTS'][2]['_DATA'];
        // Following added to later versions so need to check in case earlier version is being loaded into the editor.
        if(array_key_exists(3, $this->entries[0]['_ELEMENTS'][0]['_ELEMENTS'])) {
            $info['version'] = $this->entries[0]['_ELEMENTS'][0]['_ELEMENTS'][3]['_DATA'];
        }
        if(!array_key_exists(2, $this->entries[0]['_ELEMENTS'])) {
            $common = $this->entries[0]['_ELEMENTS'][1]['_ELEMENTS'][0]['_ELEMENTS'];
            array_shift($this->entries[0]['_ELEMENTS'][1]['_ELEMENTS']);
            foreach($this->entries[0]['_ELEMENTS'][1]['_ELEMENTS'] as $array) {
                $types[] = $array;
            }
            $citation = $footnote = array();
        }
        else if(!array_key_exists(3, $this->entries[0]['_ELEMENTS'])) {
            $citation = $this->entries[0]['_ELEMENTS'][1]['_ELEMENTS'];
            $common = $this->entries[0]['_ELEMENTS'][2]['_ELEMENTS'][0]['_ELEMENTS'];
            array_shift($this->entries[0]['_ELEMENTS'][2]['_ELEMENTS']);
            foreach($this->entries[0]['_ELEMENTS'][2]['_ELEMENTS'] as $array) {
                $types[] = $array;
            }
            $footnote = array();
        }
        else {
            $citation = $this->entries[0]['_ELEMENTS'][1]['_ELEMENTS'];
            $footnote = $this->entries[0]['_ELEMENTS'][2]['_ELEMENTS'];
            $common = $this->entries[0]['_ELEMENTS'][3]['_ELEMENTS'][0]['_ELEMENTS'];
            array_shift($this->entries[0]['_ELEMENTS'][3]['_ELEMENTS']);
            foreach($this->entries[0]['_ELEMENTS'][3]['_ELEMENTS'] as $array) {
                $types[] = $array;
            }
        }
        return array($info, $citation, $footnote, $common, $types);
    }

    function parse($xmlString="") {
        // set up a new XML parser to do all the work for us
        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "startElement", "endElement");
        xml_set_character_data_handler($this->parser, "characterData");
        // parse the data
        xml_parse($this->parser, $xmlString);
        xml_parser_free($this->parser);
        // recover the root node from the node stack
        $rnode = array_pop($this->nodeStack);
        // return the root node _ELEMENTS array
        return($rnode["_ELEMENTS"][0]);
    }

    // create a node
    function startElement($parser, $name, $attrs) {
        $node = array();
        $node["_NAME"] = $name;
        if(!empty($attrs) && ($name == "resource"))
                $node["_ATTRIBUTES"] = $attrs;
        $node["_DATA"] = "";
        $node["_ELEMENTS"] = array();
        // add the new node to the end of the node stack
        array_push($this->nodeStack, $node);
    }

    function endElement($parser, $name) {
        // pop this element off the node stack.....
        $node = array_pop($this->nodeStack);
        $data = trim($node["_DATA"]);
        $lastnode = count($this->nodeStack);
        array_push($this->nodeStack[$lastnode - 1]["_ELEMENTS"], $node);
    }

    // Collect the data onto the end of the current chars.
    function characterData($parser, $data) {
        // add this data to the last node in the stack...
        $lastnode = count($this->nodeStack);
        $this->nodeStack[$lastnode - 1]["_DATA"] .= $data;
    }
}

