<?php
requireComponent( "Eolin.PHP.XMLTree" );

class Services_Textcube_xmlparser extends Services_Yadis_XMLParser
{
	function Services_Textcube_xmlparser()
	{
        $this->xml = null;
        $this->tree = null;
        $this->xpath = null;
        $this->errors = array();
    }

    function setXML($xml_string)
    {
        $this->xml = $xml_string;
        $this->tree = new XMLTree();
        return $this->tree->open($xml_string);
    }

    function registerNamespace($prefix, $uri)
    {
        return true;
    }

    function &evalXPath($xpath, $node = null)
    {
        return $this->tree->selectNode( $xpath );
    }

    function content($node)
    {
		return $this->getText($node);
    }

    function attributes($node)
    {
        if ($node) {
			$n = &$this->selectNode($path);
			if ( $n !== null )
				return $n['attributes'];
			else
				return null;
        }
		return null;
    }
}
?>
