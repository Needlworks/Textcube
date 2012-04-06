<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'language'=> array('string', 'default' => 'ko'),
		'blogLanguage'=> array('string', 'default' => 'ko')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if (!empty($_GET['language']) && setBlogLanguage($blogid, $_GET['language'], $_GET['blogLanguage'])) {
	Respond::ResultPage(true);
}
Respond::ResultPage(false);
?>
