<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/* There are 3 pages related with the user as openid login */
/* 
	1. requestURI: the target uri with authentication success.
	2. fallbackURI: the openid identifier input form uri.
	3. tryAuthURI: the entrance uri of openid authentication.
 */

define('__TEXTCUBE_ADMINPANEL__',true);

$IV = array(
	'GET' => array(
		'action' => array('string', 'mandatory' => false ),
		'openid_identifier' => array('string', 'mandatory' => false ),
		'openid_remember' => array('string', 'mandatory' => false ),
		'openid_cancel' => array('string', 'mandatory' => false ),
		'openid_cancel_x' => array('string', 'mandatory' => false ),
		'openid_claimed_id' => array('string', 'mandatory' => false ),
		'openid_mode' => array('string', 'mandatory' => false ),
		'openid_login' => array('string', 'mandatory' => false ),
		'openid_identity' => array('string', 'mandatory' => false ),
		'openid_return_to' => array('string', 'mandatory' => false ),
		'openid_assoc_handle' => array('string', 'mandatory' => false ),
		'openid_signed' => array('string', 'mandatory' => false ),
		'openid_sig' => array('string', 'mandatory' => false ),
		'openid_ns_sreg' => array('string', 'mandatory' => false ),
		'openid_sreg_nickname' => array('string', 'mandatory' => false ),
		'openid1_claimed_id' => array('string', 'mandatory' => false ),
		'requestURI' => array('string', 'mandatory' => false ),
		'fallbackURI' => array('string', 'mandatory' => false ),
		'authenticate_only' => array('number', 'mandatory' => false ),
		'need_writers' => array('number', 'mandatory' => false ),
		'mode' => array('string', 'mandatory' => false ),
		'tid' => array('string', 'mandatory' => false ),
		'janrain_nonce' => array('string', 'mandatory' => false ),
	)
);
require ROOT . '/library/preprocessor.php';

global $openid_session_name, $openid_session_id, $openid_session, $openid_session_path;

function _openid_ip_address()
{
	return substr( "@{$_SERVER['REMOTE_ADDR']}", 0, 15 );
}

function TryAuthByRequest()
{
	$context = Model_Context::getInstance();

	/* User clicked cancel button at login form */
	if( isset($_GET['openid_cancel']) || isset($_GET['openid_cancel_x']) ) {
		header( "Location: " . $context->getProperty('uri.host').$context->getProperty('uri.blog'));
		exit(0);
	}

	if( !empty( $_GET['requestURI'] ) ) {
		$requestURI = $_GET['requestURI'];
	} else {
		$requestURI = $context->getProperty('uri.blog');
	}

	$tr = array();
	$tr['need_writers'] = '';
	if( empty($_GET['fallbackURI']) ) {
		if( !empty($_GET['need_writers'])) {
			$tr['fallbackURI'] = $context->getProperty('uri.blog')."/login?requestURI=" . urlencode($requestURI);
			$tr['need_writers'] = '1';
		} else {
			$tr['fallbackURI'] = $context->getProperty('uri.blog')."/login/openid/guest?requestURI=" . urlencode($requestURI);
		}
	} else {
		$tr['fallbackURI'] = $_GET['fallbackURI'];
	}

	$errmsg = "";
	$openid = "";
	if( !empty($_GET['openid_identifier']) ) {
		$openid = $_GET['openid_identifier'];
	}
	if (empty($openid)) {
		$errmsg = _text("오픈아이디를 입력하세요");
	} else if (strstr($openid, ".") === false ) {
		require_once(ROOT.'/framework/legacy/Textcube.Control.Openid.php');
		require_once OPENID_LIBRARY_ROOT."Auth/Yadis/XRI.php";
		if( Auth_Yadis_identifierScheme($openid) == 'URI' ) {
			$errmsg = _text("오픈아이디에 도메인 부분이 없습니다. 예) textcube.idtail.com");
		}
	}
	if( $errmsg ) {
		OpenIDConsumer::printErrorReturn( $errmsg, $tr['fallbackURI'] );
		exit(0);
	}

	if( isset($_GET['openid_remember']) ) {
		$remember_openid = true;
	} else {
		$remember_openid = false;
	}

	if( !empty($_GET['authenticate_only'])) {
		$tr['authenticate_only'] = '1';
	} else {
		$tr['authenticate_only'] = '';
	}

	$tr['requestURI'] = $requestURI;
	$tid = Transaction::pickle( $tr );
	$tr['finishURL'] = $context->getProperty('uri.host') . $context->getProperty('uri.blog') . "/login/openid?action=finish&tid=$tid";
	Transaction::repickle( $tid, $tr );

	$consumer = new OpenIDConsumer($tid);
	return $consumer->tryAuth( $tid, $openid, $remember_openid );
}

function TryHardcoreAuth()
{
	$context = Model_Context::getInstance();
	$tr = array();
	$tr['requestURI'] = $_GET["requestURI"];
	$tid = Transaction::pickle( $tr );
	$tr['finishURL'] = $context->getProperty('uri.host').$context->getProperty('uri.blog') . "/login/openid?action=finish&tid=$tid";
	Transaction::repickle( $tid, $tr );
	$consumer = new OpenIDConsumer;
	$consumer->tryAuth( $tid, $_COOKIE['openid'], true );
}

function FinishAuth()
{
	if( empty($_GET['tid']) ) {
		$context = Model_Context::getInstance();
		OpenIDConsumer::printErrorReturn( _text('잘못된 트랜잭션입니다'), $context->getProperty('uri.blog'));
	}
	$tid = $_GET['tid'];
	$consumer = new OpenIDConsumer($tid);
	$consumer->finishAuth($tid);
}

function LogoutOpenID()
{
	OpenIDConsumer::logout();
	header("HTTP/1.0 302 Moved Temporarily");
	header("Location: ".$_GET['requestURI']);

	// Hack for avoiding textcube zero-length content
	print( "<html><body></body></html>" );
}

if( empty($_GET['action']) ) {
	$_GET['action'] = 'try_auth';
}
switch( $_GET['action'] ) {
case 'try_auth':
	TryAuthByRequest();
	break;
case 'finish':
	/* Internal function */
	FinishAuth();
	break;
case 'hardcore':
	TryHardcoreAuth();
	break;
case 'logout':
	LogoutOpenID();
	break;
default:
	exit;
}
?>
