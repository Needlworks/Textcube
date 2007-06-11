<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if (doesHaveMembership()) {
	$user = array('id' => getUserId());
	$user['name'] = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = {$_SESSION['admin']}");
	$user['homepage'] = getDefaultURL($user['id']);
} else {
	$user = null;
}
?>
