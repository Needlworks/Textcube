<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';

$branchFlag = isset($_GET['javascript']) && $_GET['javascript'] == "disabled" ? true : fals

if (deleteCommentInOwner($owner, $suri['id']) === true)
	$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : respondResultPage(0);
else
	$branchFlag ? header("Location: ".$_SERVER['HTTP_REFERER']) : respondResultPage( - 1);
?>