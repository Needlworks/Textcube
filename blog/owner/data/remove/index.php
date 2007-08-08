<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'confirmativePassword' => array('string', 'mandatory' => false),
		'removeAttachments' => array(array('0', '1'), 'dafault' => null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
requireComponent('Textcube.Data.DataMaintenance');
if (empty($_POST['confirmativePassword']) || !User::confirmPassword($_POST['confirmativePassword']))
	respondResultPage(1);
DataMaintenance::removeAll(Validator::getBool(@$_POST['removeAttachments']));
CacheControl::flushAll();
respondResultPage(0);
?>
