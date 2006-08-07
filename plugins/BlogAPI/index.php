<?php
function AddRSD($target)
{
	global $hostURL, $blogURL;
	$target .= '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.$hostURL.$blogURL.'/api?rsd" />'.CRLF;
	return $target;
}
?>
