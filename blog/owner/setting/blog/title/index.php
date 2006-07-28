<?
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'title' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['title']) && setBlogTitle($owner, trim($_GET['title']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>