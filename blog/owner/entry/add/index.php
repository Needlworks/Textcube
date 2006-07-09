<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$entry['visibility'] = $_POST['visibility'];
$entry['category'] = empty($_POST['category']) ? 0 : $_POST['category'];
$entry['title'] = $_POST['title'];
if ($_POST['permalink'] != '') {
	$entry['slogan'] = $_POST['permalink'];
}
$entry['content'] = $_POST['content'];
$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
$entry['acceptComment'] = empty($_POST['acceptComment']) ? 0 : 1;
$entry['acceptTrackback'] = empty($_POST['acceptTrackback']) ? 0 : 1;
$entry['published'] = empty($_POST['published']) ? 1 : $_POST['published'];
if ($id = addEntry($owner, $entry))
	fireEvent('AddPost', $id, $entry);
respondResultPage($id !== false);
?>