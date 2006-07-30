<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'name' => array('string'),
		'rss' => array('string', 'default' => ''),
		'url' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(addLink($owner, $_POST));
?>
