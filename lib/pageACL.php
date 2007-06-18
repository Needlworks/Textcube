<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Teamblog - Check ACL and move pages if ACL is not enough.
if(!empty($_SESSION['acl'])){
	$uriAco = Aco::getAcoFromUri( $suri['directive'] );
	if( !empty($uriAco) && !Acl::check($uriAco) ) {
		if( in_array( 'group.administrators', $uriAco ) ) {
			header("location:".$blogURL ."/owner/center/dashboard"); exit;
		} else {
			header("location:".$blogURL ."/owner/entry"); exit;
		}
	}

}
// End TeamBlog
?>
