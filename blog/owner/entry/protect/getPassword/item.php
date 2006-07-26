<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$password = fetchQueryCell("SELECT `password` FROM `{$database['prefix']}Entries` WHERE `owner` = $owner AND `id` = {$suri['id']}");
printRespond(array('error' => 0, 'password' => $password));
?>