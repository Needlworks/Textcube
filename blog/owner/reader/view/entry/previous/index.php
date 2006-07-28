<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'group' => array('int', 'min' => 0 , 'default' => 0),
		'feed' => array('int', 'min' => 0, 'default' => 0),
		'entry' => array('int', 'min' => 0, 'default' => 0),
		'unread' => array(array('0','1')),
		'starred' => array(array('0','1')),
		'keyword' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
$entry = getFeedEntry($owner, $_POST['group'], $_POST['feed'], $_POST['entry'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword'], 'before', 'unread');
$result['id'] = $entry['id'];
printRespond($result);
?>