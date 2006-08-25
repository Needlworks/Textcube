<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'name' => array('string', 'default' => ''),
		'identify' => array('string'),
		'comment' => array('string', 'default' => ''),
		'senderName' => array('string', 'default' => ''),
		'senderEmail' => array('email')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (($service['type'] == 'single') || (getUserId() > 1))
	respondResultPage(false);

respondResultPage($result);
?>