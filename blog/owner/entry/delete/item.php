<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.entry");

requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();

// TeamBlog check.
if(!Acl::check("group.editors")){
	$isPosting = DBQuery::queryCell("SELECT team FROM {$database['prefix']}TeamEntryRelations WHERE owner='".$owner."' and team='".$_SESSION['admin']."' and id='".$suri['id']."'" );
	if(empty($isPosting)) {
		respondResultPage(-1);
		exit;
	}
}
// End TeamBlog

if ($isAjaxRequest) {
	if (deleteEntry($owner, $suri['id']) === true)
		respondResultPage(0);
	else
		respondResultPage(-1);
} else {
	deleteEntry($owner, $suri['id']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
