<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/* Access Request Object: i.e. user */
class Aro {

/* predefined Aros
	group.owners:         Owners of $owner's blog system. (Usually unique id.);
	group.administrators: Administrators of $owner's blog system.
	group.editors:        Adminitrators of $owner's $owner's postings.
	group.writers:        Writers to $owners's blog.
	group.readers:        Readers to $owners's blog.
	group.guests:         Guests
*/
	static $predefined = 
		array(	"group.owners"         => array( "group.administrators", "group.editors" ),
			"group.administrators" => array( "group.writers" ),
			"group.editors"        => array( "group.writers" ),
			"group.writers"	       => array( "group.readers" )
			);

	function Aro() {
	}

	function getCanonicalName( $userid ) {
		return "user.$userid";
	}


	function expand($aro) {
		$predefined_aros = array_keys( Aro::$predefined );
		do {
			$done = true;
			$new_added_obj = array();
			foreach( $aro as $obj ) {
				if( !in_array( $obj, $predefined_aros ) ) {
					continue;
				}

				foreach( Aro::$predefined[$obj] as $expand_obj ) {
					if( in_array( $expand_obj, $aro ) ) {
						continue;
					}
					array_push( $new_added_obj, $expand_obj );
				}
			}
			if( !empty( $new_added_obj ) ) {
				$aro = array_merge( $aro, $new_added_obj );
				$done = false;
			}
		} while( ! $done );

		$arranged_objs = array();
		foreach( $aro as $obj ) {
			if( !in_array( $obj, $arranged_objs ) ) {
				array_push( $arranged_objs, $obj );
			}
		}
		return $arranged_objs;
	}

	function adjust( $aco, $aco_action )
	{
		global $owner;
		if( !Acl::isAvailable() ) {
			Acl::setAro( $owner );
		}

		$aro = Acl::getAro();
		foreach( $aco as $obj ) {
			if( function_exists("fireEvent") ) {
				$aro = call_user_func( "fireEvent", "AclAdjustAro", $aro, $obj );
			}
		}

		return $aro;
	}
}

define( 'BITWISE_EDITOR', 0x1 );
define( 'BITWISE_ADMINISTRATOR', 0x2 );

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
			$aro = array_merge( $_SESSION['acl'][$blogid], $aro );
		}

		if( !empty($user) ) {
			array_push( $aro, $user );
		}

		$_SESSION['acl'][$blogid] = Aro::expand($aro);
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

		Auth::setBasicAro($userid);
		Auth::setTeamblogAro($userid);
		DBQuery::execute("UPDATE  {$database['prefix']}Users SET lastLogin = unix_timestamp() WHERE loginid = '$loginid'");
		return $userid;
	}

	function setBasicAro( $userid ) {
		Acl::clearAro();
		Acl::setAro($userid, "group.owners", Aro::getCanonicalName($userid), false );
	}

	function setTeamblogAro( $userid ) {
		global $database;

		$result = DBQuery::query("SELECT teams,acl FROM {$database['prefix']}Teamblog WHERE userid='$userid'");
		while( ($session = mysql_fetch_array($result) ) ) {
			$aro = array();

			if( $session['acl'] & BITWISE_EDITOR ) {
				array_push($aro, "group.editors");
			}
			if( $session['acl'] & BITWISE_ADMINISTRATOR ) {
				array_push($aro, "group.administrators");
			}

			Acl::setAro( $session['teams'], $aro, Aro::getCanonicalName($userid), true );
		}

		DBQuery::execute("UPDATE  {$database['prefix']}Teamblog SET last = unix_timestamp() WHERE userid='$userid'");
		return;
	}	
}

?>
