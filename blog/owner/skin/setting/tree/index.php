<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'tree' => array('string', 'default' => 'base'),
		'colorOnTree' => array('string', 'default' => '000000'),
		'bgColorOnTree' => array('string', 'default' => ''),
		'activeColorOnTree' => array('string', 'default' => 'FFFFFF'),
		'activeBgColorOnTree' => array('string', 'default' => '00ADEF'),
		'labelLengthOnTree' => array('int', 'default' => 30),
		'showValueOnTree' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
if (setTreeSetting($owner, $_POST)) {
	header("Location: $blogURL/owner/skin/setting");
} else {
}
?>