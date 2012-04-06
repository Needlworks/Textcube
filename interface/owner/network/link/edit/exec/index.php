<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(  
	'POST' => array(
		'id'   => array('int', 'min' => 0),
		'name' => array('string','min' => 0,'max' => 255),
		'rss'  => array('string','min' => 0,'max' => 255 , 'mandatory' => false),
		'url'  => array('string','min' => 0,'max' => 255),
		'category'    => array('int','mandatory'=>false),
		'newCategory' => array('string','mandatory'=>false)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

requireStrictRoute();
Respond::ResultPage(updateLink($blogid, $_POST));
?>
