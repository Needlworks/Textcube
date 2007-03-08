<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int', 0),
		'feed' => array('int', 0),
		'unread' => array(array('0', '1')),
		'starred' => array(array('0', '1')),
		'keyword' => array('string', 'default' => ''),
		'loaded' => array('int', 'default' => 0)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
$result = array('error' => '0');
ob_start();
$count = printFeedEntriesMore($owner, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword'], $_POST['loaded']);
$result['count'] = $count;
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>