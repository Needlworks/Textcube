<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Of course, BITWISE must be BITWISE! (2^)
define( 'BITWISE_EDITOR', 0x1 );              // 00001
define( 'BITWISE_ADMINISTRATOR', 0x2 );       // 00010
define( 'BITWISE_INVITER', 0x8 );             // 01000
define( 'BITWISE_OWNER', 0x10 );              // 10000

/* static */
global $sAcoPredefinedChain;
$sAcoPredefinedChain = 
	array(	"group.owners"         => array( "group.administrators", "group.editors" ),
		"group.administrators" => array( "group.writers" ),
		"group.editors"        => array( "group.writers" ),
		"group.writers"	       => array( "group.readers" )
		);

/* static */
global $requiredPrivFromUri;
$requiredPrivFromUri = array(
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
			'/owner/entry/post',
			'/owner/entry',
			'/owner/entry/comment',
			'/owner/entry/notify',
			'/owner/entry/trackback',
			'/owner/entry/trash',
			'/owner/plugin/admin*'
			),
		"group.writers" => array(
			'/owner/center/dashboard',
			'/owner/center/about',
			'/owner/entry/post',
			'/owner/entry',
			'/owner/entry/comment',
			'/owner/entry/notify',
			'/owner/entry/trackback',
			'/owner/entry/trash',
			'/owner/setting/account*',
			'/owner/setting/teamblog/changeBlog',
			'/owner/reader',
			'/owner/plugin/admin*'
			)
		);

/* Access Request Object: i.e. user */
class Privilege {

/* predefined Aros
	group.owners:         Owners of $blogid's blog system. (Usually unique id.);
	group.administrators: Administrators of $blogid's blog system.
	group.editors:        Adminitrators of $blogid's $blogid's postings.
	group.writers:        Writers to $blogids's blog.
	group.readers:        Readers to $blogids's blog.
	group.guests:         Guests
*/
	function Privilege() {
	}

	function expand($priv) {
		global $sAcoPredefinedChain;
		$predefined_aros = array_keys( $sAcoPredefinedChain );
		do {
			$done = true;
			$new_added_obj = array();
			foreach( $priv as $obj ) {
				if( !in_array( $obj, $predefined_aros ) ) {
					continue;
				}

				foreach( $sAcoPredefinedChain[$obj] as $expand_obj ) {
					if( in_array( $expand_obj, $priv ) ) {
						continue;
					}
					array_push( $new_added_obj, $expand_obj );
				}
			}
			if( !empty( $new_added_obj ) ) {
				$priv = array_merge( $priv, $new_added_obj );
				$done = false;
			}
		} while( ! $done );

		$arranged_objs = array();
		foreach( $priv as $obj ) {
			if( !in_array( $obj, $arranged_objs ) ) {
				array_push( $arranged_objs, $obj );
			}
		}
		return $arranged_objs;
	}

	function adjust( $priv )
	{
		$blogid = getBlogId();
		if( !Acl::isAvailable($blogid) ) {
			Acl::setAcl( $blogid );
		}

		$currpriv = Acl::getCurrentPrivilege();
		foreach( $priv as $obj ) {
			if( function_exists("fireEvent") ) {
				$currpriv = call_user_func( "fireEvent", "AclAdjustPrivilege", $currpriv, $obj );
			}
		}

		return $currpriv;
	}
}

/* Access Control Object: i.e. uri, components, functions */
class Aco {
	function Aco() {
	}

	function adjust( $priv, $otherPriv ) {
		// $priv is an string array
		if( !empty($otherPriv) ) {
			if( is_array($otherPriv) ) {
				$priv = array_merge($priv, $otherPriv);
			} else {
				array_push($priv, $otherPriv);
			}
		}
		if( function_exists("fireEvent") ) {
			$priv = call_user_func("fireEvent", "AclAdjustAco", $priv);
		}
		return $priv;
	}

	function getRequiredPrivFromUrl( $testingUri ) {
		global $requiredPrivFromUri;
		if( substr($testingUri, 0, 6) != "/owner" ) {
			return array();
		}
		//$priv = array( "group.owners" );
		$priv = array();
		foreach( $requiredPrivFromUri as $acoObj => $uriArray ) {
			foreach( $uriArray as $uri ) {
				if ($testingUri == $uri ) {
					array_push( $priv, $acoObj );
					break;
				} elseif( substr($uri,-1) == "*" ) {
					if( substr($testingUri, 0, strlen($uri)-1) == substr($uri,0,-1) ) {
						array_push( $priv, $acoObj );
						break;
					}
				} 

			}
		}
		return $priv;
	}
}

class Acl {

	function Acl() {
	}

	function authorize( $domain, $identity ) {
		if( !isset( $_SESSION['identity'] ) ) {
			$_SESSION['identity'] = array();
		}
		if( !isset( $_SESSION['identity'][$domain] ) ) {
			$_SESSION['identity'][$domain] = array();
		}
		$_SESSION['identity'][$domain] = $identity;

		/* Support code for legacy */
		if( $domain == 'textcube' ) {
			$_SESSION['userid'] = $identity;
		}
	}

	function getIdentity( $domain ) {
		if( empty($_SESSION['identity'][$domain]) ) {
			return null;
		}
		return $_SESSION['identity'][$domain];
	}

	function check($requiredPriv = null, $otherPriv = null) {
		if( !is_array( $requiredPriv ) ) {
			$requiredPriv = array( $requiredPriv );
		}

		/* Adujsting access control object from plugins */
		$requiredPriv = Aco::adjust($requiredPriv, $otherPriv);

		/* Adujsting required object from plugins by requiredPriv*/
		$currentPriv = Privilege::adjust($requiredPriv);

		/* We need one of requiredPriv elements is in currentPriv array */
		foreach( $requiredPriv as $obj ) {
			if(in_array($obj, $currentPriv)) {
				return true;
			}
		}

		return false;
	}

	function setAcl( $blogid, $priv = null, $add = false ) {

		if( !isset( $_SESSION['acl'] ) ) {
			$_SESSION['acl'] = array();
		}

		if( !isset( $_SESSION['acl']["blog.$blogid"] ) ) {
			$_SESSION['acl']["blog.$blogid"] = array();
		}

		if( $priv === null ) {
			return;
		}

		if( !is_array($priv) ) {
			$priv = array( $priv );
		}

		if( $add ) {
			$priv = array_merge( $_SESSION['acl']["blog.$blogid"], $priv );
		}

		$_SESSION['acl']["blog.$blogid"] = Privilege::expand($priv);
	}

	function getCurrentPrivilege($blogid=null) {
		if( $blogid === null ) {
			$blogid = getBlogId();
		}
		if( Acl::isAvailable($blogid) ) {
			return $_SESSION['acl']["blog.$blogid"];
		}
		return array();
	}

	function clearAcl() {
		if( isset( $_SESSION['acl'] ) ) {
			unset($_SESSION['acl']);
		}
		if( isset( $_SESSION['identity'] ) ) {
			unset($_SESSION['identity']);
		}
	}

	function isAvailable($blogid) {
		if( !isset( $_SESSION['acl'] ) || 
			!is_array( $_SESSION['acl'] ) || 
			!isset( $_SESSION['acl']["blog.$blogid"] ) ) {
			return false;
		}

		return true;
	}

	function setBasicAcl( $userid ) {
		global $database;
		$result = DBQuery::queryColumn("SELECT blogid
			FROM {$database['prefix']}Teamblog
			WHERE userid = $userid
				AND acl > 15");
		foreach( $result as $blogids) {
			Acl::setAcl($blogids, array("group.owners", "textcube.$userid"), false );
			if($userid == 1) {	// Give invite privilege to super administrator
				Acl::setAcl(getBlogId(), array("group.inviters", "textcube.$userid"), true );
			}
		}
	}

	function setTeamAcl( $userid ) {
		global $database;
		$blogid = getBlogId();
		$result = DBQuery::queryAllWithCache("SELECT blogid,acl FROM {$database['prefix']}Teamblog WHERE userid='$userid'");
		foreach( $result as $session ) {
			$priv = array("group.writers");

			if( $session['acl'] & BITWISE_EDITOR ) {
				array_push($priv, "group.editors");
			}
			if( $session['acl'] & BITWISE_ADMINISTRATOR ) {
				array_push($priv, "group.administrators");
			}

			Acl::setAcl( $session['blogid'], $priv, true );
		}

		DBQuery::execute("UPDATE  {$database['prefix']}Teamblog SET lastLogin = unix_timestamp() WHERE blogid='$blogid' AND userid='$userid'");
		return;
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

		Acl::clearAcl();
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

		Acl::authorize( 'textcube', $userid );
		Acl::setBasicAcl($userid);
		Acl::setTeamAcl($userid);
		DBQuery::execute("UPDATE  {$database['prefix']}Users SET lastLogin = unix_timestamp() WHERE loginid = '$loginid'");
		return $userid;
	}

}

?>
