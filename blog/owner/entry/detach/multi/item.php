<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'names' => array('string', 'default' => null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
if (!empty($_POST['names']) && deleteAttachmentMulti($owner, $suri['id'], $_POST['names']))
	respondResultPage(0);
else
	respondResultPage( - 1);
?>