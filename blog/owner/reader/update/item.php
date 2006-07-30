<?php
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
requireStrictRoute();
if ($feed = fetchQueryRow("SELECT * FROM {$database['prefix']}Feeds WHERE id = {$suri['id']}"))
	respondResultPage(updateFeed($feed));
else
	respondResultPage(-1);
?>