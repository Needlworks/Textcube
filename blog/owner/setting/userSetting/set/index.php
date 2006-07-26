<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setUserSetting($_POST['name'], $_POST['value']));
?>