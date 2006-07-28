<?
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'targets' => array('list'),
		'category' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if(changeCategoryOfEntries($owner,$_POST['targets'], $_POST['category'])) {
	respondResultPage(0);
} else {
	respondResultPage(1);
}

?>