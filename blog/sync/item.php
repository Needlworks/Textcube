<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
header('Content-Type: text/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<response>\r\n";
list($allComments, $allTrackbacks) = DBQuery::queryRow("SELECT SUM(comments), SUM(trackbacks) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND visibility = 3", MYSQL_NUM);
if($entry = DBQuery::queryRow("SELECT e.*, c.name AS categoryName FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.id = {$suri['id']} AND e.draft = 0 AND e.visibility = 3")) {
	echo '<version>1.1</version>', "\r\n";
	echo '<status>1</status>', "\r\n";
	echo '<blog>', "\r\n";
	echo '<generator>Tattertools/1.1</generator>', "\r\n";
	echo '<language>', htmlspecialchars($blog['language']), '</language>', "\r\n";
	echo '<url>', htmlspecialchars($defaultURL), '</url>', "\r\n";
	echo '<title>', htmlspecialchars($blog['title']), '</title>', "\r\n";
	echo '<description>', htmlspecialchars($blog['description']), '</description>', "\r\n";
	echo '<comments>', $allComments, '</comments>', "\r\n";
	echo '<trackbacks>', $allTrackbacks, '</trackbacks>', "\r\n";
	echo '</blog>', "\r\n";
	echo '<entry>', "\r\n";
	echo '<permalink>', htmlspecialchars("$defaultURL/".($blog['useSlogan'] ? "entry/{$entry['slogan']}": $entry['id'])), '</permalink>', "\r\n";
	echo '<title>', htmlspecialchars($entry['title']), '</title>', "\r\n";
	echo '<content>', htmlspecialchars(getEntryContentView($owner, $suri['id'], $entry['content'])), '</content>', "\r\n";
	echo '<author>', htmlspecialchars($user['name']), '</author>', "\r\n";
	echo '<category>', htmlspecialchars($entry['categoryName']), '</category>', "\r\n";
	$result = mysql_query("SELECT name FROM {$database['prefix']}Tags, {$database['prefix']}TagRelations WHERE id = tag AND owner = $owner AND entry = {$entry['id']} ORDER BY name");
	while(list($tag) = mysql_fetch_row($result))
		echo '<tag>', htmlspecialchars($tag), '</tag>', "\r\n";
	mysql_free_result($result);
	echo '<location>', htmlspecialchars($entry['location']), '</location>', "\r\n";
	echo '<comments>', $entry['comments'], '</comments>', "\r\n";
	echo '<trackbacks>', $entry['trackbacks'], '</trackbacks>', "\r\n";
	echo '<written>', Timestamp::getRFC1123($entry['published']), '</written>', "\r\n";
	foreach(getAttachments($owner, $entry['id']) as $attachment) {
		echo '<attachment>', "\r\n";
		echo '<mimeType>', $attachment['mime'], '</mimeType>', "\r\n";
		echo '<filename>', $attachment['label'], '</filename>', "\r\n";
		echo '<length>', $attachment['size'], '</length>', "\r\n";
		echo '<url>', $defaultURL, '/attachment/', $attachment['name'], '</url>', "\r\n";
		echo '</attachment>', "\r\n";
	}
	echo '</entry>', "\r\n";
}
else
	echo '<version>1.1</version>', "\r\n", '<status>0</status>', "\r\n";
echo "</response>";
?>