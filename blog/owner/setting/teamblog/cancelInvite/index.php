<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'userid'=>array('id')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
function TeamcancelInvite($userid){
	global $owner,$database;
	if(DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `lastLogin` = 0")==0)
		return false;
	if(DBQuery::queryCell("SELECT count(*) FROM `{$database['prefix']}Users` WHERE `userid` = $userid AND `host` = $owner")===0)
		return false;
	if(DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $userid")){
		if(DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $userid")){
			if(DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $userid")){
				if(DBQuery::execute("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $userid")){
					DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE teams='$owner' and userid='$userid'");
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}else{
		return false;
	}
}
if (TeamcancelInvite($_POST['userid'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>