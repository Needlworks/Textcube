<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'adminSkin' => array('directory', 'default' => 'default')
	)
);

require ROOT . '/library/preprocessor.php';

if (empty($_POST['adminSkin']) || !file_exists(ROOT."/skin/admin/{$_POST['adminSkin']}/index.xml") || !Setting::setBlogSettingGlobal("adminSkin", $_POST['adminSkin']))
	Respond::ResultPage(false);
else
	Respond::ResultPage(true);
?>
