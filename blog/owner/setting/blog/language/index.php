<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'language'=> array('string', 'default' => 'ko'),
		'blogLanguage'=> array('string', 'default' => 'ko')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (!empty($_GET['language']) && setBlogLanguage($owner, $_GET['language'], $_GET['blogLanguage'])) {
	respondResultPage(true);
}
respondResultPage(false);
?>