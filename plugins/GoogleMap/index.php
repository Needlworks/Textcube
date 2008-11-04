<?php
function GoogleMap_AddPost($mother, $target) {
	// TODO
}

function GoogleMap_UpdatePost($mother, $target) {
	// TODO
}

function GoogleMap_Header($target) {
	global $configVal;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($configVal);
	if (!is_null($config) && isset($config['apiKey'])
		$api_key = $config['apiKey'];
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script>\n";
	}
	return $target;
}

function GoogleMap_View($mother, $target) {
	// TODO
}

function GoogleMap_ConfigHandler($data) {
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($data);
	return true;
}
?>
