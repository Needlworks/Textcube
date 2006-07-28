<?php
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'id'    => array('id'),
		'title' => array('string', 'max' => 255),
		'current'       => array('int', 'min' => 0)
	)
);

require ROOT . '/lib/includeForOwner.php';
$result = array('error' => editFeedGroup($owner, $_POST['id'], $_POST['title']));
ob_start();
printFeedGroups($owner, $_POST['current']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>