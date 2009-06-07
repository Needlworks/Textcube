<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'entriesOnPage' => array('int'),
		'entriesOnList' => array('int'),
		'entriesonrecent' => array('int'),
		'commentsonrecent' => array('int'),
		'commentsonguestbook' => array('int'),
		'archivesonpage' => array('int'),
		'tagboxalign' => array('int'),
		'tagsontagbox' => array('int'),
		'trackbacksonrecent' => array('int'),
		'showlistoncategory' => array('int'),
		'showlistonarchive' => array('int'),
		'showlistontag' => array('int'),
		'showlistonauthor' => array('int'),
		'showlistonsearch' => array('int'),
		'expandcomment' => array('int'),
		'expandtrackback' => array('int'),
		'recentnoticelength' => array('int'),
		'recententrylength' => array('int'),
		'recentcommentlength' => array('int'),
		'recenttrackbacklength' => array('int'),
		'linklength' => array('int'),
		'useMicroformat' => array('int'),
		'useFOAF' => array('int')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireComponent('Textcube.Function.Respond');
requireStrictRoute();

if (setSkinSetting($blogid, $_POST)) {
	respond::PrintResult(array('error' => 0));
} else {
	respond::PrintResult(array('error' => 1, 'msg' => POD::error()));
}
?>
