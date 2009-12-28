<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

global $__gUserInfo;

// NOTICE : THIS MODEL WILL BE DEPRECATED FROM TEXTCUBE 1.6.1. USE User COMPONENT INSTEAD.
function getUserEmail($userid) {
	return User::getEmail($userid);
}

function getUserIdByEmail($email) {
	return User::getUserIdByEmail($email);
}

?>
