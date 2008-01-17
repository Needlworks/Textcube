<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'GET' => array(
		'openid_identifier' => array('string', 'mandatory' => false )
	)
);

require ROOT . '/lib/includeForBlogOwner.php';
requireComponent( 'Textcube.Control.Openid' );
requireStrictRoute();

if( OpenIDConsumer::setOpenIDLogoDisplay( empty($_GET['mode']) ? "" : "openid" ) ) {
	respond::ResultPage(0);
} else {
	respond::ResultPage(-1);
}

?>
