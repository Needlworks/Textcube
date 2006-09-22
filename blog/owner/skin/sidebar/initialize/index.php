<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

DBQuery::execute("DELETE FROM `{$database['prefix']}UserSettings` WHERE `user` = {$owner} AND `name` = 'sidebarOrder'");
header("Location: ".$_SERVER['HTTP_REFERER']);
?>
