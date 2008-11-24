<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'openid_identifier' => array('string', 'mandatory' => false )
	)
);

require ROOT . '/library/dispatcher.php';
requireComponent( 'Textcube.Control.Openid' );
requireLibrary('blog.skin');
requireStrictRoute();

$consumer = new OpenIDConsumer;
if( $consumer->setDelegate( $_GET['openid_identifier'] ) ) {
	Skin::purgeCache();
	respond::ResultPage(0);
} else {
	respond::ResultPage(-1);
}

?>
