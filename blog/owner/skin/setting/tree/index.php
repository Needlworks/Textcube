<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setTreeSetting($owner, $_POST)) {
	header("Location: $blogURL/owner/skin/setting");
} else {
}
?>