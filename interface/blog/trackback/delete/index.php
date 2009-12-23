<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

require ROOT . '/library/preprocessor.php';

requireModel('blog.trash');
requireModel('blog.response.remote');
requireModel('blog.sidebar');
requireLibrary('blog.skin');

requireStrictRoute();
$blogid = getBlogId();
$entryId = trashTrackback($blogid, $suri['id']);
if ($entryId !== false) {
	$skin = new Skin($skinSetting['skin']);
	
	$trackbackCount = getTrackbackCount($blogid, $entryId);
	list($tempTag, $trackbackCountContent) = getTrackbackCountPart($trackbackCount, $skin);
	$recentTrackbackContent = getRecentTrackbacksView(getRecentTrackbacks($blogid), $skin->recentTrackback, $skin->recentTrackbackItem);
	$entry = array();
	$entry['id'] = $entryId;
	$entry['slogan'] = getSloganById($blogid, $entry['id']);
	$trackbackListContent = getTrackbacksView($entry, $skin, true);
}
if ($trackbackListContent === false)
	Respond::PrintResult(array('error' => 1));
else
	Respond::PrintResult(array('error' => 0, 'trackbackList' => $trackbackListContent, 'trackbackCount' => $trackbackCountContent, 'recentTrackbacks' => $recentTrackbackContent));
?>
