<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$context = Model_Context::getInstance();
$entryId = $suri['id'];
$IV = array(
	'GET' => array(
		"tid" => array('string', 'default' => ''),
	),
	'POST' => array(
		'key' => array('string', 32, 32),
		"comment_type" => array(array('openid'), 'mandatory' => true),
		"secret" => array(array('1', 'on'), 'mandatory' => false),
		"homepage" => array('url', 'default' => 'http://'),
		"openid_identifier" => array('string', 'default' => ''),
		"openid_errormsg" => array('string', 'default' => ''),
		"comment" => array('string', 'default' => ''),
		"requestURI" => array('string', 'default' => '' )
	)
);

$tr = array();
if( !empty( $_GET["tid"] ) ) {
	$tr = Transaction::unpickle( $_GET["tid"] );
	$_POST = $tr['_POST'];
	$_SERVER['HTTP_REFERER'] = $tr['HTTP_REFERER'];
} else {
	$_SESSION['last_comment'] = array();
	$_SESSION['last_comment']['homepage'] = $_POST["homepage"];
	$_SESSION['last_comment']['comment'] = $_POST["comment"];
}

requireStrictRoute();
header('Content-Type: text/html; charset=utf-8');

if(!Validator::validate($IV)) {
	OpenIDConsumer::printErrorReturn( 'Illegal parameters', $_POST["requestURI"] );
}

if( $_POST["comment_type"] != 'openid' ) {
	OpenIDConsumer::printErrorReturn( 'Invalid comment type', $_POST["requestURI"] );
}

if (!isset($_POST['key']) || $_POST['key'] != md5(filemtime(ROOT . '/config.php'))) {
	OpenIDConsumer::printErrorReturn( 'Illegal parameters', $_POST["requestURI"] );
}

if ($_POST["comment"] == '') {
	OpenIDConsumer::printErrorReturn( _text('본문을 입력해 주십시오.'), $_POST["requestURI"] );
}

$openid_identity = Acl::getIdentity('openid');
if( $openid_identity ) {
	/* OpenID success return path.. */
	$_POST["name"] = $_SESSION['openid']['nickname'];
	if( empty($_POST["name"]) ) {
		$_POST["name"] = $openid_identity;
	}
	if( empty($_POST["homepage"]) || $_POST["homepage"] == "http://" ) {
		$_POST["homepage"] =
			empty($_SESSION['openid']['homepage']) ? $openid_identity : $_SESSION['openid']['homepage'];
	}
} else {
	if( empty($tr['openid_errormsg']) ) {
		/* OpenID request path.. */
		$tid = Transaction::pickle( array('_POST' => $_POST, 'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ) );
		$requestURI = urlencode($context->getProperty('uri.blog')."/comment/addopenid/$entryId?tid=$tid&__T__=".$_GET['__T__']);

		/* eas_mode will redirect your browser to the IdP authentication page in EAS4.js addComment-onError handler */
		header( "Location:".$context->getProperty('uri.blog')."/login/openid?action=try_auth&openid_remember=y&requestURI=$requestURI&fallbackURI=".urlencode($_POST["requestURI"]).
			"&openid_identifier=".urlencode($_POST["openid_identifier"]) );
		exit;
	} else {
		/* OpenID failure return path.. */
		OpenIDConsumer::printErrorReturn($tr['openid_errormsg'], $_POST["requestURI"] );
	}
}

$userName = isset($_POST["name"]) ? $_POST["name"] : '';
$userSecret = isset($_POST["secret"]) ? 1 : 0;
$userHomepage = isset($_POST["homepage"]) ? $_POST["homepage"] : '';
$userComment = isset($_POST["comment"]) ? $_POST["comment"] : '';

$comment = array();
$comment['entry'] = $entryId;
$comment['parent'] = null;
$comment['name'] = $userName;
$comment['password'] = OPENID_PASSWORD;
$comment['homepage'] = ($userHomepage == '' || $userHomepage == 'http://') ? $openid_identity : $userHomepage;
$comment['secret'] = $userSecret;
$comment['comment'] = $userComment;
$comment['ip'] = $_SERVER['REMOTE_ADDR'];

$result = addComment($blogid, $comment);

$errorString = '';
if (in_array($result, array("ip", "name", "homepage", "comment", "etc"))) {
	switch ($result) {
		case "name":
			$errorString = _text('차단된 이름을 사용하고 계시므로 댓글을 남기실 수 없습니다.');
			break;
		case "ip":
			$errorString = _text('차단된 IP를 사용하고 계시므로 댓글을 남기실 수 없습니다.');
			break;
		case "homepage":
			$errorString = _text('차단된 홈페이지 주소를 사용하고 계시므로 댓글을 남기실 수 없습니다.');
			break;
		case "comment":
			$errorString = _text('금칙어를 사용하고 계시므로 댓글을 남기실 수 없습니다.');
			break;
		case "etc":
			$errorString = _text('귀하는 차단되었으므로 사용하실 수 없습니다.');
			break;
	}
} else if ($result === false) {
	$errorString = _text('댓글을 달 수 없습니다.');
}

if( $errorString ) {
	OpenIDConsumer::printErrorReturn( $errorString, $_POST["requestURI"] );
}

OpenIDConsumer::updateUserInfo( $userName, $userHomepage );

header( "Location: {$_POST['requestURI']}" );
?>
