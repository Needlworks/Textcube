<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'save' => array(array('on'), 'mandatory' => false)
	)
);
require ROOT . '/library/dispatcher.php';
if (false) {
	doesHaveMembership();
	doesHaveOwnership();
	Session::authorize();
	login();
	fetchConfigVal();
}
?>
