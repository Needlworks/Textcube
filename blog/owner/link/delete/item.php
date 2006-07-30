<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(deleteLink($owner, $suri['id']));
?>