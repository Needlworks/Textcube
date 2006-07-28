<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'pwd'   => array('any'),
		'prevPwd'       => array('any')
	)
);
require ROOT . '/lib/includeForOwner.php';
if (changePassword($owner, $_POST['pwd'], $_POST['prevPwd'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>