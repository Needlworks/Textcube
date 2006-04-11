<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = selectSkin($owner, $_POST['skinName']);
if ($result === true) {
	printRespond(array('error' => 0));
} else {
	printRespond(array('error' => 1, 'msg' => $result));
}
?>