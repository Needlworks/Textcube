<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('OPENID_REGISTERS', 10); /* check also ../index.php */

/* ID Provider로부터 Redirect되어 연결이 되므로 GET 방식으로 구현되었습니다 */
$IV = array(
	'GET' => array(
		'openid_identifier' => array('string', 'default'=>''),
		'mode' => array('string'),
		'authenticated' => array('string', 'default'=>null)
	)
);

require ROOT . '/library/preprocessor.php';

global $openid_list;
$openid_list = array();
for( $i=0; $i<OPENID_REGISTERS; $i++ )
{
	$openid = Setting::getUserSetting( "openid." . $i ,null,true);
	if( !empty($openid) ) {
		array_push( $openid_list, $openid );
	}
}

function loginOpenIDforAdding($claimedOpenID)
{
	$context = Model_Context::getInstance();
	header( "Location: ".$context->getProperty('uri.blog')."/login/openid?action=try_auth" .
		"&authenticate_only=1&openid_identifier=" . urlencode($claimedOpenID) .
		"&requestURI=" .  urlencode( $context->getProperty('uri.blog') . "/owner/setting/account/openid" . "?mode=add&authenticate_only=1&openid_identifier=" . urlencode($claimedOpenID) ) );
}

function exitWithError($msg)
{
	$context = Model_Context::getInstance();
	echo "<html><head><script type=\"text/javascript\">//<![CDATA[".CRLF
		."alert('$msg'); document.location.href='" . $context->getProperty('uri.blog') . "/owner/setting/account'; //]]></script></head></html>";
	exit;
}

function addOpenID()
{
	global $openid_list;
	$context = Model_Context::getInstance();

	if( empty( $_GET['openid_identifier'] ) || strstr( $_GET['openid_identifier'], "." ) === false  ) {
			exitWithError( _t('오픈아이디를 입력하지 않았거나, 도메인 없는 오픈아이디를 입력하였습니다.') );
	}

	$currentOpenID = Acl::getIdentity( 'openid_temp' );
	$fc = new OpenIDConsumer;
	$claimedOpenID = $fc->fetch( $_GET['openid_identifier'] );

	if( in_array( $claimedOpenID, $openid_list ) ) {
			exitWithError( _t('이미 연결된 오픈아이디 입니다') . " : " . $claimedOpenID );
	}

	if( $_GET['authenticated'] === "0" ) {
		header( "Location: ".$context->getProperty('uri.blog')."/owner/setting/account" );
		exit(0);
	}

	if( empty($currentOpenID) || $claimedOpenID != $currentOpenID ) {
		loginOpenIDforAdding($claimedOpenID);
		return;
	}

	if( !in_array( $currentOpenID, $openid_list ) ) {
		for( $i=0; $i<OPENID_REGISTERS; $i++ )
		{
			$openid = Setting::getUserSetting( "openid." . $i , null, true);
			if( empty($openid) ) {
				Setting::setUserSetting( "openid." . $i, $currentOpenID , true);
				break;
			}
		}
	}

	echo "<html><head><script type=\"text/javascript\">//<![CDATA[".CRLF
		."alert('" . _t('연결하였습니다.') . " : " . $currentOpenID . "'); document.location.href='" . $context->getProperty('uri.blog') . "/owner/setting/account'; //]]></script></head></html>";

}

function deleteOpenID($openidForDel)
{
	$context = Model_Context::getInstance();
	for( $i=0; $i<OPENID_REGISTERS; $i++ )
	{
		$openid = Setting::getUserSetting( "openid." . $i , null, true);
		if( $openid == $openidForDel ) {
			Setting::removeUserSetting( "openid." . $i, true);
			break;
		}
	}

	echo "<html><head><script type=\"text/javascript\">//<![CDATA[".CRLF
		."alert('" . _t('삭제되었습니다.') . "'); document.location.href='" . $context->getProperty('uri.blog') . "/owner/setting/account'; //]]></script></head></html>";

}

switch( $_GET['mode'] ) {
	case 'del':
		deleteOpenID($_GET['openid_identifier']);
		break;
	case 'add':
	default:
		importlib('model.common.plugin');
		activatePlugin( 'CL_OpenID' );
		addOpenID();
		break;
}
?>
