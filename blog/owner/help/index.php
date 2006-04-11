<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$path = $blog['language'] . '.php';
if (file_exists($path)) {
	include_once ($path);
} else {
	include_once ('ko.php');
}
?>