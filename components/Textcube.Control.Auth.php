<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Access Request Object: i.e. user */
class Aro {

/* predefined Aros
	group.administrators: Administrators of $owner's blog system.
	group.members:        Logged in users.
	group.teambloggers:   Blogging team members;
	group.editors:        Adminitrators of $owner's $owner's postings.
	group.writers:        Writers to $owners's blog.
	group.readers:        Readers to $owners's blog.
	group.guests:         Guests
*/

	function Aro() {
	}

	function getCanonicalName( $userid ) {
		return "user.$userid";
	}

	function adjust( $aco, $aco_action )
	{
		global $owner;
		if( !Acl::isAvailable() ) {
			Acl::setAro( $owner );
		}

		$aro = Acl::getAro();
		foreach( $aco as $obj ) {
			if( $obj == "group.members" && !empty($_SESSION['userid']) && $_SESSION['userid'] != $owner ) {
				array_push($aro, "group.members");
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

	function Aco() {
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

	function Acl() {
	}

	function check($aco = null, $aco_action = '*') {
		global $owner; /*blogid*/

		if( !is_array( $aco ) ) {
			$aco = array( $aco );
		}

		/* Adujsting access control object from plugins */
		$aco = Aco::adjust($aco, $aco_action);

		/* Adujsting required object from plugins by aco*/
		$aro = Aro::adjust($aco, $aco_action);

		/* We need one of aco elements is in aro array */
		foreach( $aco as $obj ) {
			if(in_array($obj, $aro)) {
				return true;
			}
		}

		return false;
	}

	function setAro( $blogid, $aro = null, $user = null, $add = false ) {
		if( !isset( $_SESSION['acl'] ) ) {
			$_SESSION['acl'] = array();
		}

		if( !isset( $_SESSION['acl'][$blogid] ) ) {
			$_SESSION['acl'][$blogid] = array();
		}

		if( $aro === null ) {
			return;
		}

		if( !is_array($aro) ) {
			$aro = array( $aro );
		}

		if( $add ) {
			$_SESSION['acl'][$blogid] = array_merge( $_SESSION['acl'][$blogid], $aro );
		} else {
			$_SESSION['acl'][$blogid] = $aro;
		}

		if( $user !== null && !in_array($user,$_SESSION['acl'][$blogid]) ) {
			array_push( $_SESSION['acl'][$blogid], $user );
		}

	}

	function getAro($blogid=null) {
		global $owner; /*blogid*/
		if( $blogid === null ) {
			$blogid = $owner;
		}
		if( Acl::isAvailable($blogid) ) {
			return $_SESSION['acl'][$blogid];
		}
		return array();
	}

	function clearAro() {
		if( !isset( $_SESSION['acl'] ) ) {
			unset($_SESSION['acl']);
		}
	}

	function isAvailable($blogid=null) {
		global $owner; /*blogid*/
		if( $blogid === null ) {
			$blogid = $owner;
		}

		if( !isset( $_SESSION['acl'] ) || 
			!is_array( $_SESSION['acl'] ) || 
			!isset( $_SESSION['acl'][$blogid] ) ) {
			return false;
		}

		return true;
	}
}

class Auth {
	function login($loginid, $password) {
		global $owner;
		if( Auth::authenticate($owner,$loginid,$password,true) === false ) {
			return false;
		}
		return true;
	}

	function authenticate( $blogid, $loginid, $password, $blogapi = false ) {
		global $database;
		$aro = array();
		/* $aro := groups $blogid can be taken */
		$loginid = mysql_tt_escape_string($loginid);

		$blogApiPassword = getUserSetting("blogApiPassword", "");

		if ((strlen($password) == 32) && preg_match('/[0-9a-f]/i', $password)) {
			$secret = '(`password` = \'' . md5($password) . "' OR `password` = '$password')";
		} else if( $blogapi && !empty($blogApiPassword) ) {
			$password = mysql_tt_escape_string($password);
			$secret = '(`password` = \'' . md5($password) . '\' OR \'' . $password . '\' = \'' . $preKnownPassword . '\')';
		} else {
			$secret = '`password` = \'' . md5($password) . '\'';
		}

		$session = DBQuery::queryRow("SELECT userid, loginid, name FROM {$database['prefix']}Users WHERE loginid = '$loginid' AND $secret");
		if ( empty($session) ) {
			/* You should compare return value with '=== false' which checks with variable types*/
			return false;
		}
		$userid = $session['userid'];

		Acl::clearAro();
		array_push($aro, "group.members");
		if( $userid == $blogid ) {
			array_push($aro, "group.administrators");
			Acl::setAro($userid, $aro, Aro::getCanonicalName($userid), false );
		} else {
			Acl::setAro($userid, "group.adminitrators", Aro::getCanonicalName($userid), false );
			Acl::setAro($blogid, $aro, Aro::getCanonicalName($userid), false );
		}

		DBQuery::execute("UPDATE  {$database['prefix']}Users SET lastLogin = unix_timestamp() WHERE loginid = '$loginid'");

		Auth::setTeamblogAro($userid);
		return $userid;
	}

	function setTeamblogAro( $userid ) {
		global $database;

		$result = DBQuery::query("SELECT teams,acl FROM {$database['prefix']}Teamblog WHERE userid='$userid'");
		while( ($session = mysql_fetch_array($result) ) ) {
			$aro = array("group.teambloggers", "group.writers" );

			if( $session['acl'] & 0x1 ) {
				array_push($aro, "group.editors");
			}

			Acl::setAro( $session['teams'], $aro, Aro::getCanonicalName($userid), true );
		}

		DBQuery::execute("UPDATE  {$database['prefix']}Teamblog SET last = unix_timestamp() WHERE userid='$userid'");
		return;
	}	
}
?>
