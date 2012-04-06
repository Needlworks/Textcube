<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'POST' => array(
		'skinCode' => array('string'),
		'currentCode' => array('string','mandatory'=>false),
		'currentTag' => array('string'),
		'nextTag' => array('string')
	)
);
require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireStrictRoute();
if($_POST['currentTag'] != 'all') {
	list($refCode, $inner) = Skin::cutSkinTag($_POST['skinCode'], $_POST['currentTag'],
	'<s_'.$_POST['currentTag'].'>'.$_POST['currentCode'].'</s_'.$_POST['currentTag'].'>');
} else $refCode = $_POST['currentCode'];

if($_POST['nextTag'] != 'all') {
	list($outer, $inner) = Skin::cutSkinTag($refCode, $_POST['nextTag']);
} else $inner = $refCode;

$result = array("error"=>"0",
	"code"=>$inner,
	'skinCode'=>$refCode);
Respond::PrintResult($result);
?>
