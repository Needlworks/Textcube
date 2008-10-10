<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'APIKey' => array('string', 'default'=>'')
	)
);
require ROOT . '/library/includeForBlogOwner.php';
requireStrictRoute();
$result = false;
$result = changeAPIKey(getUserId(), $_POST['APIKey']);
if($result) respond::ResultPage(0);
else respond::ResultPage(-1);
?>
