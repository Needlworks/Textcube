<?php
requireComponent( "Eolin.PHP.Core" );

class Services_Textcube_xmlparser extends Services_Yadis_XMLParser
{
	function Services_Textcube_xmlparser()
	{
        $this->xml = null;
        $this->xmlstruct = null;
    }

    function setXML($xml_string)
    {
        $this->xml = $xml_string;
        $this->xmlstruct = new XMLStruct();
        return $this->xmlstruct->open($xml_string,"utf-8",true);
    }

    function registerNamespace($prefix, $uri)
    {
    	$this->xmlstruct->setNameSpacePrefix( $prefix, $uri );
        return true;
    }

    function evalXPath($xpath, $node = null)
    {
        return $this->xmlstruct->selectNodes( $xpath );
    }

    function content($node)
    {
		return $this->getText($node);
    }

    function attributes($node)
    {
        if (isset($node['.attributes'])) {
				return $node['.attributes'];
        }
		return null;
    }
}
?>
