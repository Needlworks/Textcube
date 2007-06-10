<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'targets' => array ('list')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
foreach(explode(',', $_POST['targets']) as $target) {
	// TeamBlog check
	if(!Acl::Check('group.blogwriters')){
		$isPosting = DBQuery::queryCell("SELECT team FROM {$database['prefix']}TeamEntryRelations WHERE owner='$owner' and team='".$_SESSION['admin']."' and id='".$suri['id']."'" );
		if(empty($isPosting)) {
			respondResultPage(-1);
			exit;
		}
	}
	// End TeamBlog
	
	if (!deleteEntry($owner, $target))
		respondResultPage(-1);
}
respondResultPage(0);
?>
