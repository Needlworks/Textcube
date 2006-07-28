<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'updateCycle' => array('int',  'min' => 0),
		'feedLife' => array('int',  'min' => 1),
		'loadImage'	=> array(array( 1,2) ) ,
		'allowScript'	=> array(array( 1,2) ) ,
		'newWindow' => array(array( 1,2) ) 
	)
);
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setReaderSetting($owner, $_POST));
?>