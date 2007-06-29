<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'profile'=>array('string'),
		'is'=>array('int')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
function TeamcancelInvite($profile,$is){
	global $owner,$database,$service ,$blogURL,$hostURL,$user,$blog;
	$KEYprofile = $profile;
	if($is == 1){
		if(DBQuery::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE blogid='$owner' and userid='0' AND md5(profile)='$KEYprofile'")){
			return true;
		}
		else return false;
	}
	else if($is == 2){
		$row = DBQuery::queryRow("SELECT * FROM `{$database['prefix']}Teamblog` WHERE blogid='$owner' and userid='0' AND md5(profile)='$KEYprofile'");
		if(empty($row['profile'])) return false;
		$ttmp = explode("::", $row['profile']);
		$email = trim($ttmp[0]);
		$name = $ttmp[1];
		
		
		$oUser = DBQuery::queryCell("SELECT password FROM `{$database['prefix']}Users` WHERE loginid='$email'");
		if(!empty($oUser)){
			$password = $oUser;
			$is_inputpass = 1;
		}
		else if(empty($ttmp[2])){
			$password = md5(generatePassword());
			$is_inputpass = 0;
		}
		else{
			$password = $ttmp[2];
			$is_inputpass = 1;
		}
		
		if(empty($email) || empty($name)) return false;
		if(!ereg('^[^@]+@([[:alnum:]]+(-[[:alnum:]]+)*\.)+[[:alnum:]]+(-[[:alnum:]]+)*$',$email)) return false;
		
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
		
		$loginid=mysql_tt_escape_string(mysql_lessen($email,64));
		$name=mysql_tt_escape_string(mysql_lessen($name,32));
		$identify=mysql_tt_escape_string(mysql_lessen($identify,32));
		$blogName=$identify;
		
			$result=DBQuery::query("SELECT * FROM `{$database['prefix']}Teamblog` a, `{$database['prefix']}Users` b WHERE b.loginid = '$loginid' and a.blogid='$owner' and a.userid=b.userid");
			if($result&&(mysql_num_rows($result)>0)){
				return false;
			}
		
			$result=DBQuery::query("SELECT * FROM `{$database['prefix']}Users` WHERE loginid = '$loginid'");
			
		if(!$result||(mysql_num_rows($result)==0)){
			$isold = 0;
		
			$result=DBQuery::query("INSERT INTO `{$database['prefix']}Users` (userid, loginid, password, name, created, lastLogin, host) VALUES (NULL, '$loginid', '$password', '$name', UNIX_TIMESTAMP(), 0, $owner)");
			if(!$result||(mysql_affected_rows()==0)){
				return false;
			}
			$id=mysql_insert_id();
			$baseTimezone=mysql_tt_escape_string($service['timezone']);
			$result=DBQuery::query("INSERT INTO `{$database['prefix']}BlogSettings` (owner, name, language, blogLanguage, timezone) VALUES ($id, '$identify', '{$service['language']}', '{$service['language']}', '{$baseTimezone}')");
			if(!$result||(mysql_affected_rows()==0)){
				DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
				return false;
			}
			$result=DBQuery::query("INSERT INTO `{$database['prefix']}SkinSettings` (owner, skin) VALUES ($id, '{$service['skin']}')");
			if(!$result||(mysql_affected_rows()==0)){
				DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
				return false;
			}
			$result=DBQuery::query("INSERT INTO `{$database['prefix']}FeedSettings` (owner) VALUES ($id)");
			if(!$result||(mysql_affected_rows()==0)){
				DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
				return false;
			}
			$result=DBQuery::query("INSERT INTO `{$database['prefix']}FeedGroups` (owner, id) VALUES ($id, 0)");
			if(!$result||(mysql_affected_rows()==0)){
				DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
				DBQuery::query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
				return false;
			}
			$enduser = $id;
			
		}
		else{
			$isold = 1;
			$res = mysql_fetch_array($result);
			$id = $res['userid'];
			$enduser = 1;
		}
				
			// 팀블로그 DB 에 사용자 정보 추가	
		  $Nprofile = $name . '님의 글입니다.';

			$time = time();
			$result=DBQuery::query("UPDATE `{$database['prefix']}Teamblog` SET userid='$id', `create`=$time, profile='$Nprofile', enduser='$enduser' WHERE md5(profile)='$KEYprofile'");
			if(!$result||(mysql_affected_rows()==0)){
				if(empty($isold)){
					DBQuery::query("DELETE FROM `{$database['prefix']}Users` WHERE `userid` = $id");
					DBQuery::query("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `owner` = $id");
					DBQuery::query("DELETE FROM `{$database['prefix']}SkinSettings` WHERE `owner` = $id");
					DBQuery::query("DELETE FROM `{$database['prefix']}FeedSettings` WHERE `owner` = $id");
					DBQuery::query("DELETE FROM `{$database['prefix']}FeedGroups` WHERE `owner` = $id");
				}
				return false;
			}
		
		
			$result=DBQuery::queryRow("SELECT loginid, name FROM `{$database['prefix']}Users` WHERE userid = '$owner'");
			$senderName = $result['name'];
			$senderEmail = $result['loginid'];
			
			if(empty($is_inputpass)){
				$comment = '블로그의 팀원으로 초대완료되었습니다<br />위의 주소에 접속해서 패스워드를 변경해주세요.';
			}
			else{
				$comment = '블로그 사용 승인이 완료되었습니다.';
				$isold = 1;
			}
				
			$headers='From: '.encodeMail($senderName).'<'.$senderEmail.">\n".'X-Mailer: '.TATTERTOOLS_NAME."\n"."MIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\n";
			if(empty($name))
				$subject=_textf(_text('귀하를 %1님이 초대합니다'),$senderName);
			else
				$subject=_textf(_text('%1님을 %2님이 초대합니다'),$name,$senderName);
			$message=file_get_contents("../../../../../style/letter/letter.html");
			$message=str_replace('[##_title_##]',_text('초대장'),$message);
			$message=str_replace('[##_content_##]',$comment,$message);
			$message=str_replace('[##_images_##]',"$hostURL{$service['path']}/style/letter",$message);
			if($isold == 1) $message=str_replace('[##_link_##]',getDefaultURL($owner).'/login?loginid='.rawurlencode($email).'&requestURI='.rawurlencode(getDefaultURL($owner)."/owner/center/dashboard/"),$message);	
			else $message=str_replace('[##_link_##]',getDefaultURL($owner).'/login?loginid='.rawurlencode($email).'&password='.rawurlencode($password).'&requestURI='.rawurlencode(getDefaultURL($owner)."/owner/setting/account?password=".rawurlencode($password)),$message);
			$message=str_replace('[##_go_blog_##]',getDefaultURL($owner),$message);
			$message=str_replace('[##_link_title_##]',_text('블로그 바로가기'),$message);
			if(empty($name)){
				$message=str_replace('[##_to_##]','',$message);
			}else{
				$message=str_replace('[##_to_##]',_text('받는 사람').': '.$name,$message);
			}
			$message=str_replace('[##_sender_##]',_text('보내는 사람').': '.$senderName,$message);
			@mail($email,encodeMail($subject),$message,$headers);
			return true;

	
	
	}
	else{
		return false;	
	}
}
if (TeamcancelInvite($_POST['profile'],$_POST['is'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
