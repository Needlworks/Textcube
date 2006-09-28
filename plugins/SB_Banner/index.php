<?php

function SB_Banner_withImage($parameters)
{
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '<a href="refsrc"><img src="imgsrc"/></a>';
		return htmlspecialchars($retval);
	}
	if (!isset($parameters['imgsrc']) || !isset($parameters['href'])) return '';
	$imgsrc = $parameters['imgsrc'];
	$refsrc = $parameters['href'];
	
	$retVal = '<a href="' . $refsrc . '" ><img src="' . $imgsrc . '" /></a>';
	
	return $retVal;
}

function SB_Banner_withCode($parameters)
{
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '<html codes~>';
		return htmlspecialchars($retval);
	}
	if (!isset($parameters['text'])) return '';
	$text = $parameters['text'];
	return $text;
}

?>