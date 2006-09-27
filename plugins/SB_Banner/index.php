<?php

function SB_Banner_withImage($parameters)
{
	if (!isset($parameters['imgsrc']) || !isset($parameters['href'])) return '';
	$imgsrc = $parameters['imgsrc'];
	$refsrc = $parameters['href'];
	
	$retVal = '<a href="' . $refsrc . '" ><img src="' . $refsrc . '" /></a>';
	
	return $retVal;
}

?>