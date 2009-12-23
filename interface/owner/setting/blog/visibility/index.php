<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array('int',0,3,'mandatory'=>false),
		'acceptComments' => array('int',0,1,'mandatory'=>false),
		'acceptTrackbacks' => array('int',0,1,'mandatory'=>false),
		'useiPhoneUI' => array('int',0,1,'mandatory'=>false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel('blog.feed');

requireStrictRoute();
$result = false;
if(isset($_POST['visibility'])) {
	if (Setting::setBlogSettingGlobal('visibility',$_POST['visibility'])) {
		CacheControl::flushCommentRSS();
		CacheControl::flushTrackbackRSS();
		clearFeed();
		$result = true;
	}
}
if(isset($_POST['acceptComments'])) {
	if(Setting::setBlogSettingGlobal('acceptComments',$_POST['acceptComments'])) $result = true;
}

if(isset($_POST['acceptTrackbacks'])) {
	if(Setting::setBlogSettingGlobal('acceptTrackbacks',$_POST['acceptTrackbacks'])) $result = true;
}
if(isset($_POST['useiPhoneUI'])) {
	if($_POST['useiPhoneUI'] == 1) $useiPhoneUI = true;
	else $useiPhoneUI = false;
	if(Setting::setBlogSettingGlobal('useiPhoneUI',$useiPhoneUI)) $result = true;
}
if($result)	{$gCacheStorage->purge();Respond::ResultPage(0);}
else Respond::ResultPage(-1);
?>
