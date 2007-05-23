<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'email' => array('email'),
		'name' => array('string', 'default' => ''),
		'password'=>array('string','default'=>''),
		'comment' => array('string', 'default' => ''),
		'senderName' => array('string', 'default' => ''),
		'senderEmail' => array('email')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if ($owner != $_SESSION['admin'])
	respondResultPage(false);

function addTeamUser($email,$name,$password,$comment,$senderName,$senderEmail){
	global $database,$service,$blogURL,$hostURL,$user,$blog,$owner;
	if(empty($email))
		return 1;
	if(!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$',$email))
		return 2;
	$Stmp = explode("@", $email);
	$Oident = $Sident = $Stmp[0];
	while(1){
		$sid = DBQuery::queryCell("SELECT name FROM `{$database['prefix']}BlogSettings` WHERE name=$Sident");
		if(empty($sid)){
			$identify = $Sident;	
			break;
		}
		else{
			$Sident = $Oident . rand(10,999);
		}
	}
	if(empty($name)){
		$tmp = explode('@', $email);
		$name=$tmp[0];
	}
	if(!ereg('^[[:alnum:]]+$',$identify))
		return 4;
	if(empty($name))
		$name=$identify;
	if(strcmp($email,mysql_lessen($email,64))!=0)
		return 11;
	$loginid=mysql_tt_escape_string(mysql_lessen($email,64));
	$name=mysql_tt_escape_string(mysql_lessen($name,32));
	$identify=mysql_tt_escape_string(mysql_lessen($identify,32));
	if(empty($password)) $password=generatePassword();
	$blogName=$identify;

	$result=DBQuery::query("SELECT * FROM `{$database['prefix']}Teamblog` a, `{$database['prefix']}Users` b WHERE b.loginid = '$loginid' and a.teams='$owner' and a.userid=b.userid");
	if($result&&(mysql_num_rows($result)>0)){
		return 21;
	}

	$result=DBQuery::query("SELECT * FROM `{$database['prefix']}Users` WHERE loginid = '$loginid'");
if(!$result||(mysql_num_rows($result)==0)){
	$isold = 0;

	$result=DBQuery::query("SELECT * FROM `{$database['prefix']}ReservedWords` WHERE word = '$blogName'");
	if($result&&(mysql_num_rows($result)>0)){
		return 60;
	}
	$result=DBQuery::query("SELECT * FROM `{$database['prefix']}BlogSettings` WHERE name = '$blogName'");
	if($result&&(mysql_num_rows($result)>0)){
		return 61;
	}
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}Users` (userid, loginid, password, name, created, lastLogin, host) VALUES (NULL, '$loginid', '".md5($password)."', '$name', UNIX_TIMESTAMP(), 0, $owner)");
	if(!$result||(mysql_affected_rows()==0)){
		return 11;
	}
	$id=mysql_insert_id();
	$baseTimezone=mysql_tt_escape_string($service['timezone']);
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}BlogSettings` (owner, name, language, blogLanguage, timezone) VALUES ('$id', '$identify', '$service[language]', '$service[language]', '$baseTimezone')");
	if(!$result||(mysql_affected_rows()==0)){
		DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		return 12;
	}
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}SkinSettings` (owner, skin) VALUES ($id, '{$service['skin']}')");
	if(!$result||(mysql_affected_rows()==0)){
		DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		return 13;
	}
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}FeedSettings` (owner) VALUES ($id)");
	if(!$result||(mysql_affected_rows()==0)){
		DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
		return 62;
	}
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}FeedGroups` (owner, id) VALUES ($id, 0)");
	if(!$result||(mysql_affected_rows()==0)){
		DBQuery::execute("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
		DBQuery::execute("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
		return 62;
	}
	$enduser = $id;
}
else{
	$ch_userid = DBQuery::queryCell("SELECT b.userid FROM {$database['prefix']}Users a, {$database['prefix']}Teamblog b WHERE `a.loginid`='$loginid' AND b.userid=a.userid");
	if(empty($ch_userid)){
		DBQuery::query("UPDATE {$database['prefix']}Users SET password='".md5($password)."', name='$name', created='UNIX_TIMESTAMP()', lastLogin='0' WHERE `loginid`='$loginid'");	
		$EndUset = $ch_userid;
	}
	else{
		$enduser = 1;
	}
	
	$isold = 1;
	$res = mysql_fetch_array($result);
	$id = $res['userid'];
	$enduser = 1;
}
	// 팀블로그 DB 에 사용자 정보 추가	
  $profile = $name . '님의 글입니다.';
	$result=DBQuery::query("INSERT INTO `{$database['prefix']}Teamblog`  VALUES('$owner', '$id', '$enduser', '0', '0', '$profile', '', '0', '#000000', '10', '0', UNIX_TIMESTAMP(), '0')");
	if(!$result||(mysql_affected_rows()==0)){
		if(empty($isold)){
			DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
			DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
			DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
			DBQuery::query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
			DBQuery::query("DELETE FROM `{$database['prefix']}FeedGroups` WHERE `owner` = $id");
		}
		return 20;
	}
	
	$headers='From: '.encodeMail($senderName).'<'.$senderEmail.">\n".'X-Mailer: '.TATTERTOOLS_NAME."\n"."MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
	if(empty($name))
		$subject=_textf('귀하를 %1님이 초대합니다',$senderName);
	else
		$subject=_textf('%1님을 %2님이 초대합니다',$name,$senderName);
	$message=file_get_contents("../../../../../style/letter/letter.html");
	$message=str_replace('[##_title_##]',_text('초대장'),$message);
	$message=str_replace('[##_content_##]',$comment,$message);
	$message=str_replace('[##_images_##]',"$hostURL{$service['path']}/style/letter",$message);
	if($isold == 1) $message=str_replace('[##_link_##]',getDefaultURL($owner).'/login?loginid='.rawurlencode($email).'&requestURI='.rawurlencode(getDefaultURL($owner)."/owner/center/dashboard/"),$message);	
	else $message=str_replace('[##_link_##]',getDefaultURL($owner).'/login?loginid='.rawurlencode($email).'&password='.rawurlencode(md5($password)).'&requestURI='.rawurlencode(getDefaultURL($owner)."/owner/setting/account?password=".rawurlencode(md5($password))),$message);
	$message=str_replace('[##_go_blog_##]',getDefaultURL($owner),$message);
	$message=str_replace('[##_link_title_##]',_text('블로그 바로가기'),$message);
	if(empty($name)){
		$message=str_replace('[##_to_##]','',$message);
	}else{
		$message=str_replace('[##_to_##]',_text('받는 사람').': '.$name,$message);
	}
	$message=str_replace('[##_sender_##]',_text('보내는 사람').': '.$senderName,$message);
	@mail($email,encodeMail($subject),$message,$headers);

	return 15;

}
$result = addTeamUser($_POST['email'], $_POST['name'], $_POST['password'], $_POST['comment'], $_POST['senderName'], $_POST['senderEmail']);
respondResultPage($result);
?>
