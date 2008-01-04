<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'openidonlycomment' => array('bool', 'mandatory' => true ),
		'openidlogodisplay' => array('bool', 'mandatory' => true )
	)
);

require ROOT . '/lib/includeForBlogOwner.php';
requireComponent( 'Textcube.Control.Openid' );
requireStrictRoute();

if( OpenIDConsumer::setComment( $_POST['openidonlycomment'] ) &&
	OpenIDConsumer::setOpenIDLogoDisplay( $_POST['openidlogodisplay'] ) ) {
	respondResultPage(0);
} else {
	respondResultPage(-1);
}

?>
