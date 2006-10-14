<?php
function KeywordUI($target,$mother) {
	global $blogURL, $configVal;
	$data = fetchConfigVal($configVal);
	$target = "<span class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . rawurlencode($target) . "')\">{$target}</span>";
	
	return $target;
}

?>
