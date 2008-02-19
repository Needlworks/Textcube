<?php
function EmoticonOnComment_main($target, $mother) {
	global $pluginURL;
	$emoticons = array(
		':)' => '<img src="' . $pluginURL . '/emoticon01.gif" alt=":)" />',
		';)' => '<img src="' . $pluginURL . '/emoticon01.gif" alt=";)" />',
		':P' => '<img src="' . $pluginURL . '/emoticon02.gif" alt=":P" />',
		'8D' => '<img src="' . $pluginURL . '/emoticon03.gif" alt="8D" />',
		':(' => '<img src="' . $pluginURL . '/emoticon04.gif" alt=":(" />',
		'--;' => '<img src="' . $pluginURL . '/emoticon05.gif" alt="--;" />'
	);
	foreach ($emoticons as $key => $value)
		$target = str_replace($key, $value, $target);
	return $target;
}
?>
