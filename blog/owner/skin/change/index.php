<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';

$branchFlag = isset($_GET['javascript']) && $_GET['javascript'] == "disabled" ? true : false;
$result = $branchFlag ? selectSkin($owner, $_GET['skinName']) : selectSkin($owner, $_POST['skinName']);

if ($result === true) {
	$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : printRespond(array('error' => 0));
} else {
	$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : printRespond(array('error' => 1, 'msg' => $result));
}
?>