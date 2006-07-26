<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(protectEntry($suri['id'], isset($_POST['password']) ? $_POST['password'] : ''));
?>