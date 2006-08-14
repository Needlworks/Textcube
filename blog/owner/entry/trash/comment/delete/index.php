<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$targets = explode('~*_)', $_POST['targets']);
for ($i = 0; $i < count($targets); $i++) {
	if ($targets[$i] == '')
		continue;
	deleteCommentInOwner($owner, $targets[$i], false);
}
respondResultPage(0);
?>