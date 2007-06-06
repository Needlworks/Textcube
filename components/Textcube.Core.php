<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function encodeURL($url) {
	global $service;
	if (isset($service['useEncodedURL']) && $service['useEncodedURL'])
		return str_replace('%2F', '/', rawurlencode($url));
	else
		return str_replace(array('%', ' ', '"', '#', '&', '\'', '<', '>', '?'), array('%25', '%20', '%22', '%23', '%26', '%27', '%3C', '%3E', '%3F'), $url);
}

/* Access Request Object: i.e. user */
class Aro {
	var $_aro = array( 
		/* role => array( <available actions>, [<reference group>...] ) */
		'group.administrators' => array( 'blog-read', 'blog-write', 'blog-manage', 'comment-manage' ),
		'group.blogwriters' => array( 'blog-read', 'blog-write' ),
		'group.members' => array( 'comment-read', 'comment-writer' ),
		'group.guests' => array( 'comment-read', 'comment-write' )
		);

	function Aro() {
	}

	function getCanonicalName( $userid ) {
		return "textcube:$userid";
	}

}

/* Access Control Object: i.e. uri, component, function */
class Aco {
	function Aco() {
	}
}

class Acl {
	function check($aco = null, $aco_action = '*') {
		global $owner;

		if( $aco == null ) {
			if (empty($_SESSION['userid']) || ($_SESSION['userid'] != $owner))
				return false;
			return true;
		}

		if( !Acl::isAvailable() )  {
			return false;
		}

		if( !is_array( $aco ) ) {
			$aco = array( $aco );
		}

		foreach( $aco as $obj ) {
			/*owner = blogid*/
			if( in_array( $obj, $_SESSION['acl'][$owner] ) ) {
				return true;
			}
		}
		return false;
	}

	function setCurrentAro( $blogid, $group, $user, $add = false ) {
		if( !isset( $_SESSION['acl'] ) ) {
			$_SESSION['acl'] = array();
		}
		if( $add ) {
			$_SESSION['acl'][$blogid] = array_merge( $_SESSION['acl'][$blogid], array( $group, $user ) );
		} else {
			$_SESSION['acl'][$blogid] = array( $group, $user );
		}
	}

	function isAvailable() {
		global $owner; /*blogid*/

		if( !isset( $_SESSION['acl'] ) || 
			!is_array( $_SESSION['acl'] ) || 
			!isset( $_SESSION['acl'][$owner] ) ) {
			return false;
		}

		return true;
	}
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
	function name(){
		requireComponent('Eolin.PHP.Core');
		global $database, $owner, $entry;

		$res = DBQuery::queryRow("SELECT * FROM ".$database['prefix']."Teamblog 
				WHERE teams='$owner' AND userid='$owner' " );
    
		$is_style = $res['font_style'] & 1;	
		$is_admin = $res['font_style'] & 2;	
		$font_style = $res['font_style'] & 4;
		$isname = $res['font_style'] & 8;
		$is_ch = $res['font_style'] & 16;
		$name = array(4);
		$name[0] = '';
		$name[1] = '';
		$name[2] = 0;
		$name[3] = '';
 
		$styleS = '';
		$styleE = '';
		if(!isset($_SESSION['admin'])) $_SESSION['admin'] = $owner;

		$ttmp = DBQuery::queryRow("SELECT * 
				FROM ".$database['prefix']."Teamblog 
				WHERE teams='".$owner."' 
					and userid='".$_SESSION['admin']."'");
		$stmp = DBQuery::queryRow("SELECT * 
				FROM ".$database['prefix']."TeamEntryRelations 
				WHERE owner='".$owner."' 
					and id='".$entry['id']."'");
		$itmp = DBQuery::queryRow("SELECT a.*, b.name 
				FROM {$database['prefix']}Teamblog a, 
					{$database['prefix']}Users b 
				WHERE a.teams='".$owner."' 
					AND a.userid='".$stmp['team']."' 
					AND a.userid=b.userid");
 
		if(empty($font_style)){
			if(empty($is_style)){
				if(empty($is_admin)) $ures = $itmp;
				else $ures = $res;
 			
				$font_bold = $ures['font_bold'] & 1;
				if(empty($font_bold)) $font_bold = '';
				else $font_bold = 'bold';
 				
				$font_italic = $ures['font_bold'] & 2;
				if(empty($font_italic)) $font_italic = '';
				else $font_italic = 'italic';
 				
 				
				$styleS = '<font style="font-Weight:'.$font_bold.';font-Style:'.$font_italic.';font-Size:'.$ures['font_size'].'pt;color:'.$ures['font_color'].';">';
				$styleE = '</font>';
			}
			if(empty($is_ch)){
				if(empty($isname)) $name[0] = '&nbsp;&nbsp;&nbsp;by ' . $styleS  . $itmp['name'] . $styleE;
				else $name[1] = '&nbsp;&nbsp;&nbsp;by ' . $styleS . $itmp['name'] . $styleE; 			
			} else {
				$name[3] = $styleS . $itmp['name'] . $styleE;
			}
		}
	
		if(($ttmp['posting'] == 1) || ($stmp['team'] == $_SESSION['admin'])) $name[2] = 1;
 
		return $name;
	}

	function PC(){
		global $database, $owner;
		$itmp = DBQuery::queryRow("SELECT * FROM ".$database['prefix']."Teamblog WHERE teams='".$owner."' and userid='".$_SESSION['admin']."'");
		$access = 0;
		if(!empty($itmp['posting'])) $access = 1;
		return $access;
	 }
	  
	 function AC(){
		global $database, $owner;
		$itmp = DBQuery::queryRow("SELECT * FROM ".$database['prefix']."Teamblog WHERE teams='".$owner."' and userid='".$_SESSION['admin']."'");
		$access = 0;
		if(!empty($itmp['admin'])) $access = 1;
		if(($itmp['userid'] == $itmp['teams']) && ($itmp['enduser'] !=0)) $access = 2;
		return $access;
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
