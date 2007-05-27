<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Auth {
	function login($loginid, $password) {
		if( login($loginid,$password) ) { /* Call login function in lib/auth.php */
			return true; 
		}

		$blogApiPassword = getUserSetting("blogApiPassword", "");

		if( empty( $blogApiPassword ) ) {
			return false;
		}

		return login($loginid,$password,$blogApiPassword); /* Call login function in lib/auth.php */
	}
}
?>
