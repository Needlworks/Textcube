<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'save' => array(array('on'), 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForBlog.php';
if (false) {
	doesHaveMembership();
	doesHaveOwnership();
	authorizeSession();
	login();
	fetchConfigVal();
}
?>
