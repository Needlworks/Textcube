<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Teamblog - Check ACL and move pages if ACL is not enough.
if(!empty($_SESSION['admin'])){
	// 
	$acceptAC = array('/center/dashboard',
		'/center/about',
		'/setting/account',
		'/setting/account/profile',
		'/setting/account/password',
		'/setting/teamblog',
		'/setting/teamblog/profileText',
		'/setting/teamblog/profileImage',
		'/setting/teamblog/nameStyle',
		'/setting/teamblog/changeBlog',
		'/reader');
	$acceptPC = array('',
		'/post',
		'/add',
		'/post',
		'/visibility',
		'/delete',
		'/edit');

	$pc = Acl::Check('group.writers');	// Teamblog moderator
	$ac = Acl::Check('group.administrators');	// Teamblog administrator

	if(empty($ac) && !eregi('/owner/entry', $suri['directive'])){
		$setAC = 0;
		foreach($acceptAC as $dir){
			if('/owner' . $dir == $suri['directive']){
				$setAC = 1;
			}
		}
		if(empty($setAC)){
			header("location:".$blogURL ."/owner/center/dashboard"); exit;
		}
	}
	else if(empty($pc) && eregi('/owner/entry', $suri['directive'])){
		$setPC = 0;
		foreach($acceptPC as $dir){
			if(eregi('/owner/entry' . $dir, $suri['directive'])){
				$setPC = 1;
			}
		}
		if(empty($setPC)){
			header("location:".$blogURL ."/owner/entry"); exit;
		}
	}
}
// End TeamBlog
?>
