<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (isset($_GET['requestURI']))
	$_POST['requestURI'] = $_GET['requestURI'];
$IV = array(
	'POST' => array(
		'requestURI' => array('string', 'mandatory' => false)
	)
);
if(!Validator::validate($IV))
	respondNotFoundPage();
if (doesHaveMembership()) {
	logout();
	if (!empty($_POST['requestURI']))
		header("Location: {$_POST['requestURI']}");
	else
		header("Location: {$user['homepage']}");
} else {
	header('Location: /');
}
?>