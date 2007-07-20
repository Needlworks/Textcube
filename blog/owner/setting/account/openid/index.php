<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
define('OPENID_REGISTERS', 10); /* check also ../index.php */

$IV = array(
	'GET' => array(
		'openid_identifier' => array('string'),
		'mode' => array('string'),
		'authenticated' => array('string', 'default'=>null)
	)
);

require ROOT . '/lib/includeForBlogOwner.php';

global $openid_list;
$openid_list = array();
for( $i=0; $i<OPENID_REGISTERS; $i++ )
{
	$openid = getUserSetting( "openid." . $i );
	if( !empty($openid) ) {
		array_push( $openid_list, $openid );
	}
}

function loginOpenIDforAdding()
{
	global $blogURL;
	header( "Location: $blogURL/plugin/openid/try_auth" .
		"?authenticate_only=1&openid_identifier=" . urlencode($_GET['openid_identifier']) . 
		"&requestURI=" .  urlencode( $blogURL . "/owner/setting/account/openid" . "?mode=add&authenticate_only=1&openid_identifier=" . urlencode($_GET['openid_identifier']) ) );
}

function addOpenID()
{
	global $openid_list;
	global $blogURL;

	$currentOpenID = Acl::getIdentity( 'openid_temp' );
	$claimedOpenID = fireEvent("OpenIDFetch", $_GET['openid_identifier']);

	if( $_GET['authenticated'] === "0" ) {
		header( "Location: $blogURL/owner/setting/account" );
		exit(0);
	}

	if( empty($currentOpenID) || $claimedOpenID != $currentOpenID ) {
		loginOpenIDforAdding();
		return;
	}

	if( !in_array( $currentOpenID, $openid_list ) ) {
		for( $i=0; $i<OPENID_REGISTERS; $i++ )
		{
			$openid = getUserSetting( "openid." . $i );
			if( empty($openid) ) {
				setUserSetting( "openid." . $i, $currentOpenID );
				fireEvent("OpenIDSetUserId", $currentOpenID);
				break;
			}
		}
	}

	echo "<html><head><script>alert('" . _t('추가하였습니다.') . "'); document.location.href='" . $blogURL . "/owner/setting/account'</script></head></html>";

}

function deleteOpenID($openidForDel)
{
	global $blogURL;
	for( $i=0; $i<OPENID_REGISTERS; $i++ )
	{
		$openid = getUserSetting( "openid." . $i );
		if( $openid == $openidForDel ) {
			removeUserSetting( "openid." . $i );
			fireEvent("OpenIDResetUserId", $openidForDel);
			break;
		}
	}

	echo "<html><head><script>alert('" . _t('삭제되었습니다.') . "'); document.location.href='" . $blogURL . "/owner/setting/account'</script></head></html>";

}

switch( $_GET['mode'] ) {
	case 'del':
		deleteOpenID($_GET['openid_identifier']);
		break;
	case 'add':
	default:
		addOpenID();
		break;
}
?>
