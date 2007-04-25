<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(  
	'POST' => array(
		'id' => array( 'id' ),
		'name' => array( 'string' , 'min' => 0 ,  'max' => 255),
		'rss' => array( 'string' , 'min' => 0 ,  'max' => 255 , 'mandatory' => false),
		'url' => array( 'string' , 'min' => 0 ,  'max' => 255)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
respondResultPage(updateLink($owner, $_POST));
?>
