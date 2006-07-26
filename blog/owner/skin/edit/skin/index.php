<?php
ini_set('magic_quotes_gpc', 'off');
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = writeSkinHtml($owner, $_POST['body'], $_POST['mode']);
if ($result === true)
	printRespond(array('error' => 0));
else
	printRespond(array('error' => 1, 'msg' => $result));
?>