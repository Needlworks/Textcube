<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'parent' => array('int')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireModel('blog.attachment');
$result = getAttachmentSizeLabel($blogid, $_GET['parent']);
respond::PrintResult(array ('error' => empty($result) ? 1 : 0, 'result' => $result));
?> 
