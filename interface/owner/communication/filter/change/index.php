<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
	'GET' => array(
		'command' => array('any', 'mandatory' => false, 'default'=>'block'),
		'id' => array('id', 'mandatory' => false ),
		'mode' => array(array('ip', 'url', 'content', 'name', 'whiteurl' ) ),
		'value' => array('string', 'mandatory' => false)
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();

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
		$isAjaxRequest ? Respond::PrintResult(array('error' => 0)) : header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
		$isAjaxRequest ? Respond::PrintResult(array('error' => 1, 'msg' => POD::error())) : header("Location: ".$_SERVER['HTTP_REFERER']);
	}
} else {
	$filter->type = $_GET['mode'];
	$filter->pattern = $_GET['value'];
	if ($filter->add())
		$isAjaxRequest ? Respond::PrintResult(array('error' => 0,'id' => $filter->id)) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? Respond::PrintResult(array('error' => 1, 'msg' => POD::error())) : header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
