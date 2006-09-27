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

?>