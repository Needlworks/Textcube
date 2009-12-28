<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if (!defined('ROOT')) {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}

if( !defined( 'OPENID_REGISTERS' ) ) {
	define('OPENID_REGISTERS', 10);
}

global $hostURL, $service;

function openid_Logout($target)
{
	OpenIDConsumer::logout();
	return $target;
}

function openid_hardcore_login($target)
{
	$context = Model_Context::getInstance();
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
	header( "Location: ".$context->getProperty('uri.blog')."/login/openid?action=hardcore&requestURI=" . urlencode($_SERVER["REQUEST_URI"]) );
	exit;
}

function openid_add_delegate($target)
{
	$context = Model_Context::getInstance();
	$openid_delegate = Setting::getBlogSettingGlobal( 'OpenIDDelegate', '' );
	$openid_server = Setting::getBlogSettingGlobal( 'OpenIDServer', '' );
	$openid_xrduri = Setting::getBlogSettingGlobal( 'OpenIDXRDSUri', '' );
	if( empty($openid_delegate) ) {
		return $target;
	}
	if( $context->getProperty('suri.directive') != '/' ) {
		return $target;
	}
	$target ="<!--OpenID Delegation Begin-->
<link rel=\"openid.server\" href=\"$openid_server\" />
<link rel=\"openid.delegate\" href=\"$openid_delegate\" />
<meta http-equiv=\"X-XRDS-Location\" content=\"$openid_xrduri\" />
<!--OpenID Delegation End-->
$target";
	header( "X-XRDS-Location: $openid_xrduri" );
	return $target;
}

function openid_ViewCommenter($name, $comment)
{
	$context = Model_Context::getInstance();

	if( $comment['secret'] ) {
		return $name;
	}
	if( empty($comment['openid']) ) {
		return $name;
	}
	$openidlogodisplay = Setting::getBlogSettingGlobal( "OpenIDLogoDisplay", 0 );
	if( $openidlogodisplay ) {
		$name = "<a href=\"".$comment['openid']."\" class=\"openid\"><img src=\"" .$context->getProperty('service.path'). "/resources/image/icon_openid.gif\" alt=\"OpenID Logo\" title=\"" .
			_textf("오픈아이디(%1)로 작성하였습니다", $comment['openid'] ) . "\" /></a>" . $name;
	} else {
		preg_match_all('@<a(.*)>(.*)</a>@Usi', $name, $temp);
		
		for ($i=0; $i<count($temp[0]); $i++) {
			if (strip_tags($temp[2][$i]) == $comment['name'])
				$name = str_replace($temp[0][$i], "<a{$temp[1][$i]} title='" ._textf("오픈아이디(%1)로 작성하였습니다", $comment['openid'] )."'>".$temp[2][$i]."</a>", $name);
		}
		$name .= "<a href=\"".$comment['openid']."\" class=\"openid\">&nbsp;</a>";
	}
	return $name;
}

function openid_OpenIDAffiliateLinks( $links, $requestURI )
{
	include_once "affiliate.php";
	return array( $openid_help_link, $openid_signup_link );
}
?>
