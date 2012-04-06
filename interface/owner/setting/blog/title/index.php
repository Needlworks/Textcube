<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'title' => array('string','default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (!empty($_POST['title'])){
	requireModel('blog.blogSetting');
	if(setBlogTitle(getBlogId(), trim($_POST['title']))) {
		Respond::ResultPage(0);
	}
}
Respond::ResultPage(-1);
?>
