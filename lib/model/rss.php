<?

function refreshRSS($owner) {
	global $database;
	global $hostURL, $blogURL, $blog;
	$channel = array();
	$author = fetchQueryCell("SELECT CONCAT(' (', name, ')') FROM {$database['prefix']}Users WHERE userid = $owner");
	$channel['title'] = $blog['title'];
	$channel['link'] = "$hostURL$blogURL/";
	$channel['description'] = $blog['description'];
	$channel['language'] = $blog['language'];
	$channel['pubDate'] = Timestamp::getRFC1123();
	if ($blog['publishEolinSyncOnRSS'])
	$result = mysql_query("SELECT e.*, c.name AS categoryName FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 AND e.visibility >= 2 AND e.category >= 0 ORDER BY e.published DESC LIMIT {$blog['entriesOnRSS']}");
	else $result = mysql_query("SELECT e.*, c.name AS categoryName FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id WHERE e.owner = $owner AND e.draft = 0 AND e.visibility = 3 AND e.category >= 0 ORDER BY e.published DESC LIMIT {$blog['entriesOnRSS']}");
	if (!$result)
		return false;
	$channel['items'] = array();
	while ($row = mysql_fetch_array($result)) {
		if (!$blog['publishWholeOnRSS']) {
			$content = '';
			if (strlen($row['content']) > 100)
				$content .= utf8Lessen($row['content'], 100);
			$content .= "<br/><br/><strong><a href=\"$hostURL$blogURL/" . ($blog['useSlogan'] ? "entry/{$row['slogan']}" : $row['id']) . "\">" . _t('글 전체보기') . "</a></strong>";
		} else {
			$content = $row['content'];
		}
		$item = array('id' => $row['id'], 'title' => $row['title'], 'link' => "$hostURL$blogURL/" . ($blog['useSlogan'] ? 'entry/' . rawurlencode($row['slogan']) : $row['id']), 'categories' => array(), 'description' => $content, 'author' => $author, 'pubDate' => Timestamp::getRFC1123($row['published']));
		if (!empty($row['id'])) {
			$sql = "SELECT name, size, mime FROM {$database['prefix']}Attachments WHERE parent= {$row['id']} AND owner =$owner AND enclosure = 1";
			$attaches = fetchQueryRow($sql);
			if (count($attaches) > 0) {
				$item['enclosure'] = array('url' => "$hostURL$blogURL/attach/$owner/{$attaches['name']}", 'length' => $attaches['size'], 'type' => $attaches['mime']);
			}
		}
		array_push($item['categories'], $row['categoryName']);
		$tag_result = mysql_query("SELECT name FROM {$database['prefix']}Tags, {$database['prefix']}TagRelations WHERE id = tag AND entry = $row[1] AND owner = $owner ORDER BY name");
		while (list($tag) = mysql_fetch_array($tag_result))
			array_push($item['categories'], $tag);
		array_push($channel['items'], $item);
	}
	$path = ROOT . '/cache/rss';
	if (file_exists($path)) {
		if (!is_dir($path))
			return false;
	} else {
		if (!mkdir($path))
			return false;
		@chmod($path, 0777);
	}
	$path .= "/$owner.xml";
	$fileHandle = fopen($path, 'w');
	$rss = array('channel' => $channel);
	if (fwrite($fileHandle, publishRSS($owner, $rss))) {
		fclose($fileHandle);
		@chmod($path, 0666);
		return true;
	}
	fclose($fileHandle);
	return false;
}

function publishRSS($owner, $data) {
	ob_start();
	echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
	echo '<rss version="2.0">', CRLF;
	echo '	<channel>', CRLF;
	echo '		<title>', htmlspecialchars($data['channel']['title']), '</title>', CRLF;
	echo '		<link>', $data['channel']['link'], '</link>', CRLF;
	echo '		<description>', htmlspecialchars($data['channel']['description']), '</description>', CRLF;
	echo '		<language>', $data['channel']['language'], '</language>', CRLF;
	echo '		<pubDate>', $data['channel']['pubDate'], '</pubDate>', CRLF;
	foreach ($data['channel']['items'] as $item) {
		echo '		<item>', CRLF;
		echo '			<title>', htmlspecialchars($item['title']), '</title>', CRLF;
		echo '			<link>', $item['link'], '</link>', CRLF;
		echo '			<description>', htmlspecialchars(getEntryContentView($owner, $item['id'], $item['description'], array(), 'Post', true)), '</description>', CRLF;
		foreach ($item['categories'] as $category) {
			if ($category = trim($category))
				echo '			<category>', htmlspecialchars($category), '</category>', CRLF;
		}
		echo '			<author>', htmlspecialchars($item['author']), '</author>', CRLF;
		echo '			<pubDate>', $item['pubDate'], '</pubDate>', CRLF;
		if (!empty($item['enclosure'])) {
			echo '			<enclosure url="', $item['enclosure']['url'], '" length="', $item['enclosure']['length'], '" type="', $item['enclosure']['type'], '" />', CRLF;
		}
		echo '		</item>', CRLF;
	}
	echo '	</channel>', CRLF;
	echo '</rss>', CRLF;
	$rss = ob_get_contents();
	ob_end_clean();
	return $rss;
}

function clearRSS() {
	global $owner;
	@unlink(ROOT . "/cache/rss/$owner.xml");
}
?>
