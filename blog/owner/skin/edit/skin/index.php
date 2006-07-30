<?php
ini_set('magic_quotes_gpc', 'off');
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'skin' => array('string','default'=>''),
		'style' => array('string','default'=>'')
	)
);

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
	
$result = writeSkin($owner, $_POST['skin'], $_POST['style']);
if ( $result === true)
	printRespond(array('error' => 0));
else
	printRespond(array('error' => 1, 'msg' => $result));
?>