<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'entriesOnPage' => array('int'),
		'entriesOnList' => array('int'),
		'entriesOnRecent' => array('int'),
		'noticesOnRecent' => array('int'),
		'commentsOnRecent' => array('int'),
		'commentsOnGuestbook' => array('int'),
		'archivesOnPage' => array('int'),
		'tagboxAlign' => array('int'),
		'tagsOnTagbox' => array('int'),
		'trackbacksOnRecent' => array('int'),
		'showListOnCategory' => array('int'),
		'showListOnArchive' => array('int'),
		'showListOnTag' => array('int'),
		'showListOnAuthor' => array('int'),
		'showListOnSearch' => array('int'),
		'expandComment' => array('int'),
		'expandTrackback' => array('int'),
		'recentNoticeLength' => array('int'),
		'recentEntryLength' => array('int'),
		'recentCommentLength' => array('int'),
		'recentTrackbackLength' => array('int'),
		'linkLength' => array('int'),
		'useAjaxComment' => array('int'),
		'useMicroformat' => array('int'),
		'useFOAF' => array('int')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if (setSkinSetting($blogid, $_POST)) {
	Respond::PrintResult(array('error' => 0));
} else {
	Respond::PrintResult(array('error' => 1, 'msg' => POD::error()));
}
?>
