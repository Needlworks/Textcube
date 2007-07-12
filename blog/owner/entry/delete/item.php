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
    if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) {
		respondResultPage(-1);
		exit;
	}
}			

if ($isAjaxRequest) {
	if (deleteEntry($blogid, $suri['id']) === true)
		respondResultPage(0);
	else
		respondResultPage(-1);
} else {
	deleteEntry($blogid, $suri['id']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
