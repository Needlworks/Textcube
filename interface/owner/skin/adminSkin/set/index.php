<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'adminSkin' => array('directory', 'default' => 'default')
	)
);

require ROOT . '/lib/includeForBlogOwner.php';

if (empty($_POST['adminSkin']) || !file_exists(ROOT."/resources/style/admin/{$_POST['adminSkin']}/index.xml") || !setBlogSetting("adminSkin", $_POST['adminSkin']))
	header("Location: ".$_SERVER['HTTP_REFERER']);
else
	header("Location: ".$_SERVER['HTTP_REFERER']);
?>
