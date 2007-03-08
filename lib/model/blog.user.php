<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if (doesHaveMembership()) {
	$user = array('id' => getUserId());
	$user['name'] = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = {$user['id']}");
	$user['homepage'] = getDefaultURL($user['id']);
} else {
	$user = null;
}
?>
