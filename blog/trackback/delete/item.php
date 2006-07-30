<?php
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$entryId = deleteTrackback($owner, $suri['id']);
if ($entryId !== false) {
	$skin = new Skin($skinSetting['skin']);
	$result = getTrackbacksView($entryId, $skin);
}
if ($result === false)
	printRespond(array('error' => 1));
else
	printRespond(array('error' => 0, 'result' => $result));
?>