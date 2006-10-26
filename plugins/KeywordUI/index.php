<?php
function KeywordUI_bindKeyword($target,$mother) {
	global $blogURL, $configVal;
	requireComponent('Tattertools.Function.misc');
	$data = misc::fetchConfigVal($configVal);
	$target = "<span class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . rawurlencode($target) . "')\">{$target}</span>";

	return $target;
}

function KeywordUI_setSkin($target,$mother) {
	return "/plugins/KeywordUI/keylogSkin.html";
}
?>