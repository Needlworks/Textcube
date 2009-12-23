<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'id' => array ('int','min'=>1)
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

$line = Model_Line::getInstance();
$line->reset();
$line->setFilter(array('blogid','equals',getBlogId()));
$line->setFilter(array('id','equals',$_POST['id']));

if($line->remove()) {
	fireEvent('DeleteLine',0,$line);
	Respond::ResultPage(0);
} else {
	fireEvent('DeleteLine',-1,$line);
	Respond::ResultPage(-1);
}
?>
