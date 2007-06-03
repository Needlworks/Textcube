<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'stype'=>array('int'),
		'userid'=>array('int')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
function CHgetIdentify($userid){
	global $database;

	$email = DBQuery::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid=$userid");
	
	$Itmp = explode("@", $email);
	$Id = $Itmp[0];

	while(1){
		$check = DBQuery::queryCell("SELECT name FROM {$database['prefix']}BlogSettings WHERE name=$Id");
		if(!empty($check)){
			$Id = $Itmp . rand(10,999);		
		}
		else{
			break;
		}
	}
	
	return $Id;	
}
function changeAdmin($owner,$stype,$userid){
	global $database;
	if(empty($stype)||empty($userid))
		return false;

	$res = DBQuery::queryRow("SELECT admin, posting, enduser 
			FROM {$database['prefix']}Teamblog 
			WHERE teams='$owner' and userid='$userid'");

	if($stype == 1){
		if(empty($res['Admin'])) $admin = 1;
		else $admin = 0;
		$sql = "UPDATE `{$database['prefix']}Teamblog` 
			SET admin = '$admin' 
			WHERE teams = '$owner' and userid = '$userid'";
	}
	else if($stype == 2){
		if(empty($res['Posting'])) $post = 1;
		else $post = 0;
		$sql = "UPDATE `{$database['prefix']}Teamblog` SET 
			posting = '$post' 
			WHERE teams = '$owner' and userid = '$userid'";
	}
	else{
		$result = DBQuery::query("SELECT * 
				FROM `{$database['prefix']}Teamblog` 
				WHERE teams = '$userid' and userid = '$userid'");
		if(!$result||(mysql_affected_rows()==0)){
			$name = DBQuery::queryCell("SELECT name 
					FROM {$database['prefix']}Users 
					WHERE userid = '$userid'");
			$profile = _f('%1 님의 글입니다.',$name);
			DBQuery::query("INSERT INTO `{$database['prefix']}Teamblog`  
					VALUES('$userid', '$userid', '1', '1', '1', '$profile', '', '0', '#000000', '10', '0', UNIX_TIMESTAMP(), '0')");
		}
		
		$enduser = $res['enduser'] - $userid;
		if(empty($enduser)){
			$enduser = $userid + 1;
			$new_name = CHgetIdentify($userid);
		}
		else{
			$enduser = $userid;
			$new_name = substr(md5(time()),4,9);
		}
		$Psql = "UPDATE `{$database['prefix']}BlogSettings` 
			SET name='$new_name' 
			WHERE owner='$userid'";
		DBQuery::execute($Psql);
		$sql="UPDATE `{$database['prefix']}Teamblog` 
			SET enduser = '$enduser' 
			WHERE teams='$owner' and userid = '$userid'";
	}
	
	
	return DBQuery::execute($sql);
}
if (changeAdmin($owner,$_POST['stype'],$_POST['userid'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
