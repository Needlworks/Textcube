<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
$entryId = trashTrackback($owner, $suri['id']);
if ($entryId !== false) {
	$skin = new Skin($skinSetting['skin']);
	$result = getTrackbacksView($entryId, $skin);
}
if ($result === false)
	printRespond(array('error' => 1));
else
	printRespond(array('error' => 0, 'result' => $result));
?>