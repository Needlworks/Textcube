<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (($service['type'] == 'single') || (getUserId() > 1))
	return false;
$result = addUser($_POST['email'], $_POST['name'], $_POST['identify'], $_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
respondResultPage($result);
?>