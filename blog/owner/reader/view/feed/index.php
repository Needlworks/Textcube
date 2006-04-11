<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = array('error' => '0');
ob_start();
printFeeds($owner, $_POST['group'], $_POST['starred'] == '1', $_POST['keyword'] == '' ? null : $_POST['keyword']);
$result['view'] = escapeCData(ob_get_contents());
ob_end_clean();
printRespond($result);
?>