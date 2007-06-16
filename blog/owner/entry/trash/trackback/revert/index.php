<?php
define('ROOT', '../../../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");
$targets = explode('~*_)', $_POST['targets']);
for ($i = 0; $i < count($targets); $i++) {
	if ($targets[$i] == '')
		continue;
	revertTrackback($owner, $targets[$i]);
}
respondResultPage(0);
?>
