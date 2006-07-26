<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
respondResultPage(updateLink($owner, $_POST));
?>