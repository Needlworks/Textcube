<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = setEnclosure($_POST['fileName'], $_POST['order']);
printRespond(array('error' => $result < 3 ? 0 : 1, 'order' => $result));
?>