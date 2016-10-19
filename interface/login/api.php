<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'save' => array(array('on'), 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
if (false) {
	doesHaveMembership();
	doesHaveOwnership();
	Session::authorize();
	login();
	fetchConfigVal();
}
?>
