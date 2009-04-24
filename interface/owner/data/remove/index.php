<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'confirmativePassword' => array('string', 'mandatory' => false),
		'removeAttachments' => array(array('0', '1'), 'dafault' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (empty($_POST['confirmativePassword']) || !Model_User::confirmPassword(Model_User::getBlogOwner(getBlogId()), $_POST['confirmativePassword']))
	respond::ResultPage(1);
$dm = new Model_DataMaintenance();
$dm->removeAll(Validator::getBool(@$_POST['removeAttachments']));
Cache_Control::flushAll();
respond::ResultPage(0);
?>
