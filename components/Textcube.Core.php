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

	function adjust( $aco, $aco_action )
	{
		global $owner;
		if( !Acl::isAvailable() ) {
			Acl::setCurrentAro( $owner );
		}

		$aro = Acl::getCurrentAro();
		foreach( $aco as $obj ) {
			if( $obj == "group.members" && !empty($_SESSION['userid']) && $_SESSION['userid'] != $owner ) {
				$aro[] = "group.members";
			}
			if( function_exists("fireEvent") ) {
				$aro = call_user_func( "fireEvent", "AclAdjustAro", $aro, $obj );
			}
		}
		return $aro;
	}
}

/* Access Control Object: i.e. uri, components, functions */
class Aco {
	var $predefined;

	function Aco( $predefined = null ) {
		$this->predefined = $predefined;
	}

	function adjust( $aco, $aco_action ) {
		// $aco is an string array
		if( function_exists("fireEvent") ) {
			$aco = call_user_func("fireEvent", "AclAdjustAco", $aco );
		}
		return $aco;
	}
}

class Acl {
	function check($aco = null, $aco_action = '*') {
		global $owner; /*blogid*/

		if( !is_array( $aco ) ) {
			$aco = array( $aco );
		}

		/* Adjusting access control object from plugins */
		$aco = Aco::adjust($aco, $aco_action);

		/* Adjusting required object from plugins by aco*/
		$aro = Aro::adjust($aco, $aco_action);

		/* We need one of aco elements is in aro array */

		foreach( $aco as $obj ) {
			if(in_array($obj, $aro)) {
				return true;
			}
		}

		return false;
	}

	function setCurrentAro( $blogid, $group = null, $user = null, $add = false ) {
		if( !isset( $_SESSION['acl'] ) ) {
			$_SESSION['acl'] = array();
		}

		if( !isset( $_SESSION['acl'][$blogid] ) ) {
			$_SESSION['acl'][$blogid] = array();
		}

		if( $group === null ) {
			return;
		}

		if( $add ) {
			$_SESSION['acl'][$blogid] = array_merge( $_SESSION['acl'][$blogid], array( $group, $user ) );
		} else {
			$_SESSION['acl'][$blogid] = array( $group, $user );
		}
	}

	function getCurrentAro() {
		global $owner; /*blogid*/
		if( Acl::isAvailable() ) {
			return $_SESSION['acl'][$owner];
		}
		return array();
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
