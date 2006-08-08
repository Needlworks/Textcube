<?php
define('ROOT', '../..');
$IV = array(
	'POST' => array(
		'save' => array(array('on'), 'mandatory' => false)
	)
);
require ROOT . '/lib/include.php';
if (false) {
	doesHaveMembership();
	doesHaveOwnership();
	authorizeSession();
	login();
	fetchConfigVal();
}
?>
