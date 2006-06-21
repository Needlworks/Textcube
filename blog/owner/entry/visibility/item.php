<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (isset($_GET['no_javascript'])) {
	switch ($_GET['no_javascript']) {
		case "protect":
			$_GET['no_javascript'] = 1;
			break;
		case "public":
			$_GET['no_javascript'] = 2;
			break;
		case "syndicate":
			$_GET['no_javascript'] = 3;
			break;
		case "private":
		default:
			$_GET['no_javascript'] = 0;
			break;
	}
	setEntryVisibility($suri['id'], $_GET['no_javascript']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	respondResultPage(setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0));
}
?>