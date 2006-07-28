<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'updateCycle' => array('int', 0),
		'feedLife' => array('int', 0),
		'loadImage' => array(array('1', '2')),
		'allowScript' => array(array('1', '2')),
		'newWindow' => array(array('1', '2')) 
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(setReaderSetting($owner, $_POST));
?>