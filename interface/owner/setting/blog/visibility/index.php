<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'visibility' => array('int',0,3,'mandatory'=>false),
		'useiPhoneUI' => array('int',0,1,'mandatory'=>false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('blog.feed');

requireStrictRoute();
$result = false;
if(isset($_GET['visibility'])) {
	if (setBlogSetting('visibility',$_GET['visibility'])) {
		CacheControl::flushCommentRSS();
		CacheControl::flushTrackbackRSS();
		clearFeed();
		$result = true;
	}
}
if(isset($_GET['useiPhoneUI'])) {
	if($_GET['useiPhoneUI'] == 1) $useiPhoneUI = true;
	else $useiPhoneUI = false;
	if(setBlogSetting('useiPhoneUI',$useiPhoneUI)) $result = true;
}
if($result)	{$gCacheStorage->purge();Respond::ResultPage(0);}
else Respond::ResultPage(-1);
?>
