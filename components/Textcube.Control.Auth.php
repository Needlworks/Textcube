<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'BITWISE_EDITOR', 0x1 );
define( 'BITWISE_ADMINISTRATOR', 0x2 );

/* static */
global $sAcoPredefinedChain;
$sAcoPredefinedChain = 
	array(	"group.owners"         => array( "group.administrators", "group.editors" ),
		"group.administrators" => array( "group.writers" ),
		"group.editors"        => array( "group.writers" ),
		"group.writers"	       => array( "group.readers" )
		);

/* static */
global $sAcoFromUri;
$sAcoFromUri = array(
		"group.administrators" => array( 
			'/owner/center/dashboard',
			'/owner/center/about',
			'/owner/entry*',
			'/owner/reader',
			'/owner/setting*',
			'/owner/plugin/admin*'
			),
		"group.editors" => array(
			'/owner/center/dashboard',
			'/owner/center/about',
			'/owner/entry*',
			'/owner/plugin/admin*'
			),
		"group.writers" => array(
			'/owner/center/dashboard',
			'/owner/center/about',
			'/owner/entry*',
			'/owner/setting/account*',
			'/owner/reader',
			'/owner/plugin/admin*'
			)
		);

/* Access Request Object: i.e. user */
class Aro {

/* predefined Aros
	group.owners:         Owners of $blogid's blog system. (Usually unique id.);
	group.administrators: Administrators of $blogid's blog system.
	group.editors:        Adminitrators of $blogid's $blogid's postings.
	group.writers:        Writers to $blogids's blog.
	group.readers:        Readers to $blogids's blog.
	group.guests:         Guests
*/
	function Aro() {
	}

	function getCanonicalName( $userid ) {
		return "user.$userid";
	}


	function expand($aro) {
		global $sAcoPredefinedChain;
		$predefined_aros = array_keys( $sAcoPredefinedChain );
		do {
			$done = true;
			$new_added_obj = array();
			foreach( $aro as $obj ) {
				if( !in_array( $obj, $predefined_aros ) ) {
					continue;
				}

				foreach( $sAcoPredefinedChain[$obj] as $expand_obj ) {
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

	function adjust( $aco )
	{
		global $blogid;
		if( !Acl::isAvailable() ) {
			Acl::setAro( $blogid );
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

/* Access Control Object: i.e. uri, components, functions */
class Aco {
	function Aco() {
	}

	function adjust( $aco, $extra_aco ) {
		// $aco is an string array
		if( !empty($extra_aco) ) {
			if( is_array($extra_aco) ) {
				$aco = array_merge($aco, $extra_aco);
			} else {
				array_push($aco, $extra_aco);
			}
		}
		if( function_exists("fireEvent") ) {
			$aco = call_user_func("fireEvent", "AclAdjustAco", $aco);
		}
		return $aco;
	}

	function getAcoFromUri( $testingUri ) {
		global $sAcoFromUri;
		if( substr($testingUri, 0, 6) != "/owner" ) {
			return array();
		}
		//$aco = array( "group.owners" );
		$aco = array();
		foreach( $sAcoFromUri as $acoObj => $uriArray ) {
			foreach( $uriArray as $uri ) {
				if ($testingUri == $uri ) {
					array_push( $aco, $acoObj );
					break;
				} elseif( substr($uri,-1) == "*" ) {
					if( substr($testingUri, 0, strlen($uri)-1) == substr($uri,0,-1) ) {
						array_push( $aco, $acoObj );
						break;
					}
				} 

			}
		}
		return $aco;
	}
}

class Acl {

	function Acl() {
	}

	function check($aco = null, $extra_aco = null) {
		global $blogid; /*blogid*/

		if( !is_array( $aco ) ) {
			$aco = array( $aco );
		}

		/* Adujsting access control object from plugins */
		$aco = Aco::adjust($aco, $extra_aco);

		/* Adujsting required object from plugins by aco*/
		$aro = Aro::adjust($aco);

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
		global $blogid; /*blogid*/
		if( $blogid === null ) {
			$blogid = $blogid;
		}
		if( Acl::isAvailable($blogid) ) {
			return $_SESSION['acl'][$blogid];
		}
		return array();
	}

	function clearAro() {
		if( isset( $_SESSION['acl'] ) ) {
			unset($_SESSION['acl']);
		}
	}

	function isAvailable($blogid=null) {
		global $blogid; /*blogid*/
		if( $blogid === null ) {
			$blogid = $blogid;
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
		global $blogid;
		if( Auth::authenticate($blogid,$loginid,$password,true) === false ) {
			return false;
		}
		return true;
	}

	function authenticate( $blogid, $loginid, $password, $blogapi = false ) {
		global $database;

		Acl::clearAro();
		$loginid = mysql_tt_escape_string($loginid);

		$blogApiPassword = getBlogSetting("blogApiPassword", "");

		if ((strlen($password) == 32) && preg_match('/[0-9a-f]/i', $password)) {
			$secret = '(`password` = \'' . md5($password) . "' OR `password` = '$password')";
		} else if( $blogapi && !empty($blogApiPassword) ) {
			$password = mysql_tt_escape_string($password);
			$secret = '(`password` = \'' . md5($password) . '\' OR \'' . $password . '\' = \'' . $blogApiPassword . '\')';
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
		$_SESSION['userid'] = $userid;
		Acl::setAro($userid, "group.owners", Aro::getCanonicalName($userid), false );
	}

	function setTeamblogAro( $userid ) {
		global $database;

		$result = DBQuery::query("SELECT blogid,acl FROM {$database['prefix']}Teamblog WHERE userid='$userid'");
		while( ($session = mysql_fetch_array($result) ) ) {
			$aro = array("group.writers");

			if( $session['acl'] & BITWISE_EDITOR ) {
				array_push($aro, "group.editors");
			}
			if( $session['acl'] & BITWISE_ADMINISTRATOR ) {
				array_push($aro, "group.administrators");
			}

			Acl::setAro( $session['blogid'], $aro, Aro::getCanonicalName($userid), true );
		}

		DBQuery::execute("UPDATE  {$database['prefix']}Teamblog SET lastLogin = unix_timestamp() WHERE userid='$userid'");
		return;
	}	
}

?>
