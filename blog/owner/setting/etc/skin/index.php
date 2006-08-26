<?php
define('ROOT', '../../../../..');

$IV = array(
	'POST' => array(
		'ajaxRequest' => array('string', 'mandatory' => false),
		'adminSkin' => array('directory', 'default' => 'default')
	)
);

require ROOT . '/lib/includeForOwner.php';

$isAjaxRequest = checkAjaxRequest();

if (empty($_POST['adminSkin']) || !file_exists(ROOT."/style/admin/{$_POST['adminSkin']}/index.xml") || !setUserSetting("adminSkin", $_POST['adminSkin']))
	$isAjaxRequest ? printRespond(array('error' => 1)) : header("Location: ".$_SERVER['HTTP_REFERER']);
else
	$isAjaxRequest ? printRespond(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
?>