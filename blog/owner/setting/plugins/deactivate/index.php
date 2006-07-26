<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['name'])) {
	deactivatePlugin($_GET['name']);
	respondResultPage(0);
}
respondResultPage(1);
?>