<?php
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
if ($feed = fetchQueryRow("SELECT * FROM {$database['prefix']}Feeds WHERE id = {$suri['value']}"))
	respondResultPage(updateFeed($feed));
else
	respondResultPage(-1);
?>