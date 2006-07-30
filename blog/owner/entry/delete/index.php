<?php
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'targets' => ('list')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
foreach(explode(',', $_POST['targets']) as $target) {
	if (!deleteEntry($owner, $target))
		respondResultPage(-1);
}
respondResultPage(0);
?>