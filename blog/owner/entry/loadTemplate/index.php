<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');

$IV = array(
	'POST' => array(
		'templateId' => array('int', 'default' => 0)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel('blog.entry');

requireStrictRoute();
if ($entry = getEntry($blogid, $_POST['templateId'])) {
	
	
	$result = array("error"=>"0","content"=>$entry['content']);
	printRespond($result);
}
respondResultPage(1);
?>
