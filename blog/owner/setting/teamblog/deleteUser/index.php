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
function deleteUser($userid){
	global $owner, $database;

	$result = DBQuery::query("SELECT Id FROM `{$database['prefix']}TeamEntryRelations` WHERE Owner='$owner' AND Team='$userid'");
	while($res = mysql_fetch_array($result)){
		DBQuery::execute("UPDATE `{$database['prefix']}TeamEntryRelations` SET Team='$owner' WHERE Owner='$owner' AND Id='$res[Id]'");
	}
	$En=mysql_fetch_array(DBQuery::query("SELECT enduser FROM `{$database['prefix']}Teamblog` WHERE teams='$owner' AND userid='$userid'"));
	$isp = intval($En['enduser']-$userid);
	if($isp == 0 || $isp == 1){
		DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE teams='$userid' and userid='$userid'");
	}
	if(DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE teams='$owner' and userid='$userid'")){
		$En=DBQuery::queryCell("SELECT userid FROM `{$database['prefix']}Teamblog` WHERE userid='$userid'");
		if(empty($En)){
			@DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $userid");
			@DBQuery::execute("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $userid");
		}
		return true;
	}else{
		return false;
	}
}
if (deleteUser($_POST['userid'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>