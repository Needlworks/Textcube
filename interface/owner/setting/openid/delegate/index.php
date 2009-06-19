<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'openid_identifier' => array('string', 'mandatory' => false )
	)
);

require ROOT . '/library/preprocessor.php';
requireComponent( 'Textcube.Control.Openid' );

requireStrictRoute();

$consumer = new OpenIDConsumer;
if( $consumer->setDelegate( $_GET['openid_identifier'] ) ) {
	Skin::purgeCache();
	Utils_Respond::ResultPage(0);
} else {
	Utils_Respond::ResultPage(-1);
}

?>
