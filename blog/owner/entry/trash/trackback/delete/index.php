<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$targets = explode('~*_)', $_POST['targets']);
for ($i = 0; $i < count($targets); $i++) {
	if ($targets[$i] == '')
		continue;
	deleteTrackback($owner, $targets[$i]);
}
respondResultPage(0);
?>