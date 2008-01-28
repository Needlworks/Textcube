<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if (!defined('ROOT')) {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}

if( !defined( 'OPENID_REGISTERS' ) ) {
	define('OPENID_REGISTERS', 10);
}

global $hostURL, $service;

requireComponent( "Textcube.Function.misc" );

function openid_Logout($target)
{
	requireComponent( "Textcube.Control.Openid" );
	OpenIDConsumer::logout();
	return $target;
}

function openid_hardcore_login($target)
{
	global $suri;
	global $hostURL, $service, $blogURL;
	if( !isset($_COOKIE['openid_auto']) || $_COOKIE['openid_auto'] != 'y' ) {
		return $target;
	}
	if( Acl::getIdentity('openid') ) {
		return $target;
	}
	if( empty($_COOKIE['openid']) ) {
		return $target;
	}
	if( strstr( $_SERVER["REQUEST_URI"], "/login/openid" ) !== false ) {
		return $target;
	}
	if( headers_sent() ) {
		return $target;
	}
	header( "Location: $blogURL/login/openid?action=hardcore&requestURI=" . urlencode($_SERVER["REQUEST_URI"]) );
	exit;
}

function openid_add_delegate($target)
{
	global $suri;
	$openid_delegate = setting::getBlogSettingGlobal( 'OpenIDDelegate', '' );
	$openid_server = setting::getBlogSettingGlobal( 'OpenIDServer', '' );
	$openid_xrduri = setting::getBlogSettingGlobal( 'OpenIDXRDSUri', '' );
	if( empty($openid_delegate) ) {
		return $target;
	}
	if( $suri['directive'] != '/' ) {
		return $target;
	}
	$target ="<!--OpenID Delegation Begin-->
<link rel='openid.server' href='$openid_server' />
<link rel='openid.delegate' href='$openid_delegate' />
<meta http-equiv='X-XRDS-Location' content='$openid_xrduri' />
<!--OpenID Delegation End-->
$target";
	header( "X-XRDS-Location: $openid_xrduri" );
	return $target;
}

function _openid_fix_table()
{
	global $database, $store_path;

	$checkup_path = ROOT .DS."cache".DS."_php_consumer";
	if( ! file_exists( $checkup_path ) ) {
		mkdir( $checkup_path, 0777 );
	}

	$checkup_path = ROOT .DS."cache".DS."_php_consumer".DS."checkup";
	$openid_check_magic = '2007-07-31';
	$fix0 = false;
	$fix1 = false;
	$fix2 = true;
	$fix3 = false;
	$fix4 = false;

	if( file_exists($checkup_path) && ($fixed = file_get_contents( $checkup_path) ) ) {
		if( $fixed == $openid_check_magic ) {
			return;
		}
	}

	$rows = POD::queryAll("DESC {$database['prefix']}OpenIDUsers");
	foreach( $rows as $row ) {
		if( $row['Field'] == 'owner' )    { $fix0 = true; }
		if( $row['Field'] == 'blocked' )  { $fix1 = true; }
		if( $row['Field'] == 'data' )     { $fix2 = false; }
		if( $row['Field'] == 'nickname' ) { $fix3 = true; }
	}
	$rows = POD::queryAll("DESC {$database['prefix']}OpenIDComments");
	foreach( $rows as $row ) {
		if( $row['Field'] == 'owner' )    { $fix4 = true; }
	}

	if( $fix0 ) {
		POD::execute("alter table {$database['prefix']}OpenIDUsers change column owner blogid int(11) not null default 0");
	}
	if( $fix1 ) {
		POD::execute("alter table {$database['prefix']}OpenIDUsers drop column blocked");
		POD::execute("alter table {$database['prefix']}OpenIDUsers drop column admin");
		POD::execute("alter table {$database['prefix']}OpenIDUsers drop column member");
		POD::execute("alter table {$database['prefix']}OpenIDUsers drop column comment");
	}

	if( $fix2 ) {
		POD::execute("alter table {$database['prefix']}OpenIDUsers add column data text");
	}

	if( $fix3 ) {
		$rows = POD::queryAll("select blogid,openid,nickname from {$database['prefix']}OpenIDUsers");
		foreach( $rows as $row ) {
			$blogid = $row["blogid"];
			$openid = $row["openid"];
			$data = serialize( array( "nickname" => $row["nickname"], "homepage" => $openid ) );
			POD::execute("update {$database['prefix']}OpenIDUsers set data='{$data}' where blogid={$blogid} and openid='{$openid}'");
		}
		POD::execute("alter table {$database['prefix']}OpenIDUsers drop column nickname");
	}
	if( $fix4 ) {
		POD::execute("alter table {$database['prefix']}OpenIDComments change column owner blogid int(11) not null default 0");
	}
	$f = fopen( $checkup_path, "w");
	if( $f ) {
		fwrite($f,$openid_check_magic);
		fclose($f);
	}
}

function openid_ViewCommenter($name, $comment)
{
	global $database;
	global $hostURL, $service, $blogURL;
	$blogid = getBlogId();

	if( $comment['secret'] ) {
		return $name;
	}
	$row = OpenIDConsumer::getOpenIDComment( $blogid, $comment['id'] );
	if( empty($row['openid']) ) {
		return $name;
	}
	$openidlogodisplay = setting::getBlogSettingGlobal( "OpenIDLogoDisplay", 0 );
	if( $openidlogodisplay ) {
		$name = "<a href=\"".$row['openid']."\" class=\"openid\"><img src=\"" .$service['path']. "/image/icon_openid.gif\" alt=\"OpenID Logo\" title=\"" .
			sprintf( _text("오픈아이디(%s)로 작성하였습니다"), $row['openid'] ) . "\" /></a>" . $name;
	} else {
		preg_match_all('@<a(.*)>(.*)</a>@Usi', $name, $temp);
		
		for ($i=0; $i<count($temp[0]); $i++) {
			if (strip_tags($temp[2][$i]) == $comment['name'])
				$name = str_replace($temp[0][$i], "<a{$temp[1][$i]} title='" .sprintf( _text("오픈아이디(%s)로 작성하였습니다"), $row['openid'] )."'>".$temp[2][$i]."</a>", $name);
		}
		$name .= "<a href=\"".$row['openid']."\" class=\"openid\">&nbsp;</a>";
	}
	return $name;
}

function openid_OpenIDAffiliateLinks( $links, $requestURI )
{
	include_once "affiliate.php";
	return array( $openid_help_link, $openid_signup_link );
}
?>
