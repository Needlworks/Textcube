<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'tree' => array('string', 'default' => 'base'),
		'colorOnTree' => array('string', 'default' => '000000'),
		'bgColorOnTree' => array('string', 'default' => ''),
		'activeColorOnTree' => array('string', 'default' => '000000'),
		'activeBgColorOnTree' => array('string', 'default' => ''),
		'labelLengthOnTree' => array('int', 'default' => 30),
		'showValueOnTree' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if(isset($suri['id'])) {
	$categories = getCategories($blogid);
	Respond::PrintResult(array('code' => urlencode(getCategoriesViewInSkinSetting(getEntriesTotalCount($blogid), $categories, $suri['id']))));
	exit;
} else {
	if (setTreeSetting($blogid, $_POST)) {
		header("Location: $blogURL/owner/skin/setting");
	} else {
	}
}
?>
