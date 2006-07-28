<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'description' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
if (setBlogDescription($owner, trim($_POST['description']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>