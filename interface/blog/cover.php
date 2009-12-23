<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	)
);

require ROOT . '/library/preprocessor.php';
if (false) {
	fetchConfigVal();
}
fireEvent('OBStart');
require ROOT . '/interface/common/blog/begin.php';
if(count($coverpageMappings) > 0) {
	dress('article_rep', '', $view);
	dress('paging', '', $view);
	require ROOT . '/interface/common/blog/cover.php';
}
require ROOT . '/interface/common/blog/end.php';
fireEvent('OBEnd');
?>
