<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array ('list','mandatory'=>false)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.entry");

requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
if(isset($suri['id'])) {
	if(!Acl::check("group.editors")) {
		if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) {
			respondResultPage(-1);
			exit;
		}
	}			
	
	if ($isAjaxRequest) {
		if (deleteEntry($blogid, $suri['id']) === true) {
			fireEvent('DeletePost', $suri['id'], null);
			respondResultPage(0);
		} else {
			respondResultPage(-1);
		}
	} else {
		deleteEntry($blogid, $suri['id']);
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
} else {
	foreach(explode(',', $_POST['targets']) as $target) {
		// TeamBlog check
		if(!Acl::check( 'group.writers', 'entry.delete.' . $target )) {
			if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) { 
				respondResultPage(-1);
				exit;
			}
		}
		
		if (!deleteEntry($blogid, $target))
			respondResultPage(-1);
		else {
			fireEvent('DeletePost', $target, null);
		}
	}
	respondResultPage(0);
}
?>
