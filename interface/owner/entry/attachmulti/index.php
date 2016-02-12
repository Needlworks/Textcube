<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'FILES' => array(
		'Filedata' => array('file')
	),
	'GET' => array( 
		'TSSESSION' => array( 'string' , 'default' => null) 
	)
);

if (!empty($_GET['TSSESSION']))
	$_COOKIE['TSSESSION'] = $_GET['TSSESSION'];
require ROOT . '/library/preprocessor.php';
importlib("model.blog.attachment");
$context = Model_Context::getInstance();
$file = array_pop($_FILES);
$attachment = addAttachment($context->getProperty('blog.id'), $context->getProperty('suri.id'), $file);
echo "&success";
?>
