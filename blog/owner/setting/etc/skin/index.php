<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');

$IV = array(
	'POST' => array(
		'adminSkin' => array('directory', 'default' => 'default')
	)
);

require ROOT . '/lib/includeForOwner.php';

if (empty($_POST['adminSkin']) || !file_exists(ROOT."/style/admin/{$_POST['adminSkin']}/index.xml") || !setUserSetting("adminSkin", $_POST['adminSkin']))
	header("Location: ".$_SERVER['HTTP_REFERER']);
else
	header("Location: ".$_SERVER['HTTP_REFERER']);
?>