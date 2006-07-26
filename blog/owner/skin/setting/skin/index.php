<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setSkinSetting($owner, $_POST)) {
	printRespond(array('error' => 0));
} else {
	printRespond(array('error' => 1, 'msg' => mysql_error()));
}
?>