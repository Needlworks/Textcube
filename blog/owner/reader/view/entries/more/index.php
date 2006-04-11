<?
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
ob_start();
$count = printFeedEntriesMore($owner, $_POST['group'], $_POST['feed'], $_POST['unread'] == '1', $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword'], $_POST['loaded']);
$result['count'] = $count;
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>