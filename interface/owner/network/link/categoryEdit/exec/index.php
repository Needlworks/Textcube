<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(  
	'POST' => array(
		'id'   => array('id'),
		'name' => array('string','min' => 0,'max' => 255)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

requireStrictRoute();
respond::ResultPage(updateLinkCategory($blogid, $_POST));
?>
