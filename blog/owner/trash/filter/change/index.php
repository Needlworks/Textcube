<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
requireComponent('Tattertools.Data.Filter');
$filter = new Filter();
if ($_GET['command'] == 'unblock') {
	if (empty($_GET['id'])) {
		$filter->type = $_GET['mode'];
		$filter->pattern = $_GET['value'];
	} else {
		$filter->id = $_GET['id'];
	}
	if ($filter->remove()) {
		printRespond(array('error' => 0));
	} else {
		printRespond(array('error' => - 1, 'msg' => mysql_error()));
	}
} else {
	$filter->type = $_GET['mode'];
	$filter->pattern = $_GET['value'];
	if ($filter->add())
		printRespond(array('error' => 0));
	else
		printRespond(array('error' => - 1, 'msg' => mysql_error()));
}
?>