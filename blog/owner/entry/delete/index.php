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
requireModel("blog.entry");

requireStrictRoute();
foreach(explode(',', $_POST['targets']) as $target) {
	// TeamBlog check
	if(!Acl::check( 'group.writers', 'entry.delete.' . $target )){
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
?>
