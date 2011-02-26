<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'publishWholeOnRSS' => array('int', 0, 1, 'default' => 0),
		'publishEolinSyncOnRSS' => array('int', 0, 1, 'default' => 0),
		'entriesOnRSS' => array('int', 'default' => 5),
		'commentsOnRSS' => array('int', 'default' => 5),
		'useFeedViewOnCategory' => array('int',0,1,'default'=> 1),
		'rssURL' => array('url','mandatory'=>false),
		'atomURL' => array('url','mandatory'=>false)
		)
	);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
setCommentsOnRSS($blogid, $_POST['commentsOnRSS']);
setEntriesOnRSS($blogid, $_POST['entriesOnRSS']);

// Feed range 
Setting::setBlogSettingGlobal('publishWholeOnRSS',$_POST['publishWholeOnRSS']);
Setting::setBlogSettingGlobal('publishEolinSyncOnRSS',$_POST['publishEolinSyncOnRSS']);

// Category Feed
Setting::setBlogSettingGlobal('useFeedViewOnCategory',$_POST['useFeedViewOnCategory']);
Setting::setBlogSettingGlobal('atomURL',$_POST['atomURL']);
Setting::setBlogSettingGlobal('rssURL',$_POST['rssURL']);
clearFeed();
CacheControl::flushSkin();
Respond::ResultPage(0);
?>
