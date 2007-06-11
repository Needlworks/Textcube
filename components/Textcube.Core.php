<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

requireComponent( "Textcube.Control.Auth" );

function encodeURL($url) {
	global $service;
	if (isset($service['useEncodedURL']) && $service['useEncodedURL'])
		return str_replace('%2F', '/', rawurlencode($url));
	else
		return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F'), $url);
}

class User {
	/*@static@*/
	function getName($userid = null) {
		global $database, $owner;
		if (!isset($userid))
			$userid = $owner;
		return DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function getEmail($userid = null) {
		global $database, $owner;
		if (!isset($userid))
			$userid = $owner;
		return DBQuery::queryCell("SELECT loginid FROM {$database['prefix']}Users WHERE userid = $userid");
	}
	
	/*@static@*/
	function confirmPassword($password) {
		global $database, $owner;
		$password = md5($password);
		return DBQuery::queryExistence("SELECT userid FROM {$database['prefix']}Users WHERE userid = $owner AND password = '$password'");
	}
}


class teamblogUser{
	function authorName($owner,$entryId){
		requireComponent('Eolin.PHP.Core');
		global $database, $owner, $entry;

		// Read userId of entry from relation table.
		$userId = DBQuery::queryCell("SELECT team 
				FROM ".$database['prefix']."TeamEntryRelations 
				WHERE owner =".$owner." 
					AND id = ".$entryId);
		if(isset($userId)) {
			$author = DBQuery::queryCell("SELECT profile
					FROM {$database['prefix']}Teamblog
					WHERE teams=".$owner."
						AND userid = ".$userId);
			return $author;
		} else {
			return false;
		}
	}

	function myBlog(){
		global $database, $owner, $blogURL, $_SERVER, $blog, $service;
		
		if($service['type'] == "path")
			$Path = str_replace($service['path']."/".$blog['name'], "", $_SERVER["REQUEST_URI"]);
		else
			$Path = str_replace("/".$blog['name'], "", $_SERVER["REQUEST_URI"]);
	
		$blogn = "<select id=\"teamblog\" onchange=\"location.href='{$blogURL}/owner/setting/teamblog/changeBlog/?bs='+this.value+'&path={$Path}'\">";
	
		$isEnd = $_SESSION['admin']+1;
		$myres = DBQuery::queryRow("SELECT * FROM `{$database['prefix']}Teamblog` WHERE `userid`='".$_SESSION['admin']."' and enduser='".$isEnd."'");
		if(!empty($myres['profile'])){
			if($owner == $_SESSION['admin'] && $myres['userid'] > 1) $myblogsel = ' selected="selected"';
			$blogn .= '<option value="'.$myres['userid'].'" '. $myblogsel .'>'._t('내 블로그').'</option>';
		}
	
		$teamblogInfo = DBQuery::queryAll("SELECT * FROM ".$database['prefix']."Teamblog WHERE userid='".$_SESSION['admin']."'");
		foreach($teamblogInfo as $res){
			if($res['teams'] == $res['userid'] && $res['enduser'] > '0'){
				continue;
			} else {
				$title = DBQuery::queryCell("SELECT title FROM ".$database['prefix']."BlogSettings WHERE owner='".$res['teams']."'");
				if(empty($title)){
					$title = _f('%1 님의 블로그',DBQuery::queryCell("SELECT name FROM ".$database['prefix']."Users WHERE userid='".$res['teams']."'"));
				}
				$blogn .= '<option value="' . $res['teams'] . '"';
				if($res['teams'] == $owner) $blogn .= ' selected="selected"';
				$blogn .= '>' . $title . '</option>';
			}
		}
		$blogn .= '</select>';

		return $blogn;
	}
}

?>
