<?
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'category' => array('int', 'default' => 0),
		'location' => array('string', 'default' => '/'),
		'tag' => array('string', 'default' => ''),
		'title' => array('string'),
		'content' => array('string'),
		'acceptComment' => array(array('0', '1'), 'default' => '0'),
		'acceptTrackback' => array(array('0', '1'), 'default' => '0'),
		'published' => array('int', 0, 2, 'default' => 1)
	)
);
require ROOT . '/lib/includeForOwner.php';
if ($entry = getEntry($owner, $suri['id'])) {
	$entry['visibility'] = $_POST['visibility'];
	$entry['category'] = $_POST['category'];
	$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
	$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
	$entry['title'] = $_POST['title'];
	$entry['content'] = $_POST['content'];
	$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
	$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
	$entry['published'] = empty($_POST['published']) ? 0 : $_POST['published'];
	respondResultPage(updateEntry($owner, $entry));
}
respondResultPage(1);
?>