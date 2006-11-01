<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if (!array_key_exists($_REQUEST, 'viewMode')) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

DBQuery::execute("DELETE FROM `{$database['prefix']}UserSettings` WHERE `user` = {$owner} AND `name` = 'sidebarOrder'");
header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
?>
