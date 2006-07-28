<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$IV = array(
	'POST' => array(
		'body' => array('string'),
		'mode' => array('string')
	)
);
if(!Validator::validate($IV))
	printRespond(array('error' => 1));
	
$result = writeSkinHtml($owner, $_POST['body'], $_POST['mode']);
if ($result === true)
	printRespond(array('error' => 0));
else
	printRespond(array('error' => 1, 'msg' => $result));
?>