<?php
define('ROOT', '../../../../..');

$IV = array(
	'GET' => array(
		'ajaxRequest' => array('string', 'mandatory' => false),
		'command' => array('any', 'mandatory' => false),
		'id' => array('id', 'mandatory' => false ),
		'mode' => array(array('ip', 'url', 'content', 'name' ) ),
		'value' => array('string', 'mandatory' => false)
	)
);

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
requireComponent('Tattertools.Data.Filter');

$isAjaxRequest = checkAjaxRequest();
$filter = new Filter();

if ($_GET['command'] == 'unblock') {
	if (empty($_GET['id'])) {
		$filter->type = $_GET['mode'];
		$filter->pattern = $_GET['value'];
	} else {
		$filter->id = $_GET['id'];
	}
	if ($filter->remove()) {
		$isAjaxRequest ? printRespond(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
		$isAjaxRequest ? printRespond(array('error' => 1, 'msg' => mysql_error())) : header("Location: ".$_SERVER['HTTP_REFERER']);
	}
} else {
	$filter->type = $_GET['mode'];
	$filter->pattern = $_GET['value'];
	if ($filter->add())
		$isAjaxRequest ? printRespond(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? printRespond(array('error' => 1, 'msg' => mysql_error())) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
