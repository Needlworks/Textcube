<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$file = array_pop($_FILES);
$attachment = addAttachment($owner, $suri['id'], $file);
?>
