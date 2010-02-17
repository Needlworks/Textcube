<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'openid_identifier' => array('string', 'mandatory' => false )
	)
);

require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireStrictRoute();
$skin = new Skin($skinSetting['skin']);

$consumer = new OpenIDConsumer;
if( $consumer->setDelegate( $_GET['openid_identifier'] ) ) {
	$skin->purgeCache();
	Respond::ResultPage(0);
} else {
	Respond::ResultPage(-1);
}

?>
