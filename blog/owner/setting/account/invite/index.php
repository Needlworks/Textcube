<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'email' => array('string'),
		'name' => array('string', 'mandatory' => false),
		'identify' => array('string'),
		'comment' => array('string', 'mandatory' => false),
		'senderName' => array('string', 'mandatory' => false),
		'senderEmail'  => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (($service['type'] == 'single') || (getUserId() > 1))
	return false;
respondResultPage($result);


?>