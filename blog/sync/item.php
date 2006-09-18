<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
$result = mysql_query("SELECT e.*, c.name AS categoryName FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.id = {$suri['id']} AND e.draft = 0 AND e.visibility = 3");
if ($result && ($row = mysql_fetch_array($result))) {
	$author = fetchQueryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $owner");
	$item = array('title' => $row['title'], 'description' => $row['content'], 'link' => "$defaultURL/".($blog['useSlogan'] ? "entry/{$row['slogan']}": $row['id']), 'categories' => array(), 'location' => $row['location'], 'pubDate' => Timestamp::getRFC1123GMT($row['published']));
	array_push($item['categories'], $row['categoryName']);
	$tag_result = mysql_query("select name from {$database['prefix']}Tags, {$database['prefix']}TagRelations where id = tag and owner = $owner and entry = {$row['id']} order by name");
	while (list($tag) = mysql_fetch_array($tag_result))
		array_push($item['categories'], $tag);
	echo '<response>', CRLF;
	echo '	<language>', $blog['language'], '</language>', CRLF;
	echo '	<site>', htmlspecialchars($blog['title']), '</site>', CRLF;
	echo '	<siteURL>', htmlspecialchars("$defaultURL/"), '</siteURL>', CRLF;
	echo '	<postURL>', htmlspecialchars($item['link']), '</postURL>', CRLF;
	echo '	<subject>', htmlspecialchars($item['title']), '</subject>', CRLF;
	echo '	<description>', htmlspecialchars(getEntryContentView($owner, $suri['id'], $item['description'])), '</description>', CRLF;
	foreach ($item['categories'] as $category) {
		if ($category = trim($category))
			echo '	<category>', htmlspecialchars($category), '</category>', CRLF;
	}
	echo '	<location>', htmlspecialchars($item['location']), '</location>', CRLF;
	echo '	<author>', htmlspecialchars($author), '</author>', CRLF;
	echo '	<pubDate>', $item['pubDate'], '</pubDate>', CRLF;
	echo '</response>';
	exit;
}
?>
<response>
  <error>1</error>
  <message>Entry was not found</message>
</response>
