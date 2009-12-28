<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(  
	'POST' => array(
		'id'   => array('id'),
		'name' => array('string','min' => 0,'max' => 255)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

requireStrictRoute();
Respond::ResultPage(updateLinkCategory($blogid, $_POST));
?>
