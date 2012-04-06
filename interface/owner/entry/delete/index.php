<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array ('list','mandatory'=>false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.entry");


requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();
if(isset($suri['id'])) {
	if(!Acl::check("group.editors")) {
		if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) {
			Respond::ResultPage(-1);
			exit;
		}
	}			
	
	if ($isAjaxRequest) {
		if (deleteEntry($blogid, $suri['id']) === true) {
			Respond::ResultPage(0);
		} else {
			Respond::ResultPage(-1);
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
				Respond::ResultPage(-1);
				exit;
			}
		}
		
		if (!deleteEntry($blogid, $target))
			Respond::ResultPage(-1);
	}
	Respond::ResultPage(0);
}
?>
