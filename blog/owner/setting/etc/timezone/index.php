<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'timezone' => array('string')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (isset($_GET['timezone'])) {
	requireComponent('Tattertools.Data.BlogSetting');
	if (BlogSetting::setTimezone($_GET['timezone']))
		respondResultPage(0);
}
respondResultPage( - 1);
?>