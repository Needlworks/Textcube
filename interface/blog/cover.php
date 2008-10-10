<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	)
);

require ROOT . '/library/includeForBlog.php';
if (false) {
	fetchConfigVal();
}
fireEvent('OBStart');
require ROOT . '/library/piece/blog/begin.php';
if(count($coverpageMappings) > 0) {
	dress('article_rep', '', $view);
	dress('paging', '', $view);
	require ROOT . '/library/piece/blog/cover.php';
}
require ROOT . '/library/piece/blog/end.php';
fireEvent('OBEnd');
?>
