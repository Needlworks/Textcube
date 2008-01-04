<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'action' => array('string', 'mandatory' => false ),
		'openid_identifier' => array('string', 'mandatory' => false ),
		'openid_remember' => array('string', 'mandatory' => false ),
		'openid_cancel' => array('string', 'mandatory' => false ),
		'openid_cancel_x' => array('string', 'mandatory' => false ),
		'requestURI' => array('string', 'mandatory' => false ),
		'authenticate_only' => array('number', 'mandatory' => false ),
		'need_writers' => array('number', 'mandatory' => false ),
		'mode' => array('string', 'mandatory' => false ),
		'tid' => array('string', 'mandatory' => false ),
	)
);

if( in_array( $_GET['action'], array( 'setdelegate' ) ) ) {
	require ROOT . '/lib/includeForBlogOwner.php';
} else {
	require ROOT . '/lib/includeForBlog.php';
}
requireComponent( "Textcube.Core" );
requireComponent( "Textcube.Function.misc" );
requireComponent( "Textcube.Control.Openid" );

global $service;
global $openid_session_name, $openid_session_id, $openid_session, $openid_session_path;

function _openid_ip_address()
{
	return substr( "@{$_SERVER['REMOTE_ADDR']}", 0, 15 );
}

function TryAuthByRequest()
{
	global $hostURL, $blogURL;
	if( isset($_GET['openid_cancel']) || isset($_GET['openid_cancel_x']) ) {
		header( "Location: " . $hostURL . $blogURL);
		exit(0);
	}

	$tr = array();
	if( !empty($_GET['need_writers'])) {
		$fallback_location = "$blogURL/login";
		$tr['need_writers'] = '1';
	} else {
		$fallback_location = "$blogURL/login/openid/guest";
		$tr['need_writers'] = '';
	}

	$openid = $_GET['openid_identifier'];
	$errmsg = "";
	if (empty($openid)) {
		$errmsg = _text("오픈아이디를 입력하세요");
	} else if (strstr($openid, ".") === false) {
		$errmsg = _text("오픈아이디에 도메인 부분이 없습니다. 예) textcube.idtail.com");
	}
	if( $errmsg ) {
		$location = "$fallback_location?requestURI=" . urlencode($requestURI);
		OpenIDConsumer::printErrorReturn( $errmsg, $location );
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

	if( !empty( $_GET['requestURI'] ) ) {
		$requestURI = $_GET['requestURI'];
	} else {
		$requestURI = $blogURL;
	}
	$tr['requestURI'] = $requestURI;
	$tid = Transaction::pickle( $tr );
	$tr['finishURL'] = $hostURL . $blogURL . "/login/openid?action=finish&tid=$tid";
	Transaction::repickle( $tid, $tr );

	$consumer = new OpenIDConsumer($tid);
	return $consumer->tryAuth( $tid, $openid, $remember_openid );
}

function TryHardcoreAuth()
{
	global $hostURL, $blogURL;
	$tr = array();
	$tr['requestURI'] = $_GET["requestURI"];
	$tid = Transaction::pickle( $tr );
	$tr['finishURL'] = $hostURL . $blogURL . "/login/openid?action=finish&tid=$tid";
	Transaction::repickle( $tid, $tr );
	$consumer = new OpenIDConsumer;
	$consumer->tryAuth( $tid, $_COOKIE['openid'], true );
}

function FinishAuth()
{
	if( empty($_GET['tid']) ) {
		global $blogURL;
		OpenIDConsumer::printErrorReturn( _text('잘못된 트랜잭션입니다'), $blogURL );
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
