<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'openidonlycomment' => array('bool', 'mandatory' => true ),
		'openidlogodisplay' => array('bool', 'mandatory' => true )
	)
);

require ROOT . '/library/preprocessor.php';
importlib('blogskin');
requireStrictRoute();
importlib('model.common.plugin' );
$skin = new Skin($skinSetting['skin']);

if( OpenIDConsumer::setComment( $_POST['openidonlycomment'] ) &&
	OpenIDConsumer::setOpenIDLogoDisplay( $_POST['openidlogodisplay'] ) ) {
	if( !empty($_POST['openidonlycomment']) || !empty($_POST['openidlogodisplay']) ) {
		activatePlugin('CL_OpenID');
	}
	$skin->purgeCache();
	Respond::ResultPage(0);
} else {
	Respond::ResultPage(-1);
}
?>
