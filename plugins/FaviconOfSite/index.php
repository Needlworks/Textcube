<?php
function FaviconOfSite_main($target, $mother) {
	if (empty($mother['homepage']))
		return $target;
	$slash = ($mother['homepage']{strlen($mother['homepage']) - 1} == '/' ? '' : '/');
	return "<img src=\"{$mother['homepage']}{$slash}favicon.ico\" width=\"16\" height=\"16\" onerror=\"this.parentNode.removeChild(this)\" /> $target";
}
?>