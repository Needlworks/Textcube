<?php
define('ROOT', '../../../..');
if (empty($_POST['trashType']) || $_POST['trashType'] == "comment")
	require 'comment/index.php';
else
	require 'trackback/index.php';
?>