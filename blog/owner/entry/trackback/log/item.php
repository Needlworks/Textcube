<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = getTrackbackLog($owner, $suri['id']);
if ($result !== false)
	printRespond(array('error' => 0, 'result' => $result));
else
	printRespond(array('error' => 1, 'msg' => mysql_error()));
?> 