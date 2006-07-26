<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => addFeedGroup($owner, $_POST['title']));
ob_start();
printFeedGroups($owner, $_POST['current']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>