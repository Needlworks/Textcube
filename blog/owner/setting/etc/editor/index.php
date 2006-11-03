<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if(isset($_GET['useBlogAPI'])) {
	if($_GET['useBlogAPI'] == "yes") $useBlogAPI = "yes";
} else $useBlogAPI = "no";
if (setEditor($owner, $_GET['editorMode']) || setUserSetting("useBlogAPI", $useBlogAPI)) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>