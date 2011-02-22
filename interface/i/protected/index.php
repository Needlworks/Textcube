<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
$IV = array(
	'POST' => array(
		'password' => array('string','default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
$entry = getEntry($blogid, $suri['id']);
if(!is_null($entry) && isset($_POST['password']) && $entry['password'] == $_POST['password']) {
	setcookie('GUEST_PASSWORD', $_POST['password'], time() + 86400, "$blogURL/");
	header("Location: $blogURL/entry/{$suri['id']}");
}else{
	printIphoneErrorPage(_text('Password (again)!'), _text('Wrong password.'), "$blogURL/entry/{$suri['id']}");
}
?>
