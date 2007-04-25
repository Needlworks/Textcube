<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function refreshRSS($owner) {
	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
	$author = DBQuery::queryCell("SELECT CONCAT(' (', name, ')') FROM {$database['prefix']}Users WHERE userid = $owner");
	$channel['title'] = $blog['title'];
	$channel['link'] = "$defaultURL/";
	$channel['description'] = $blog['description'];
	$channel['language'] = $blog['language'];
	$channel['pubDate'] = Timestamp::getRFC1123();
	$channel['generator'] = TEXTCUBE_NAME . ' ' . TEXTCUBE_VERSION;

	if (!empty($blog['logo']) && file_exists(ROOT."/attach/$owner/{$blog['logo']}")) {
		$logoInfo = getimagesize(ROOT."/attach/$owner/{$blog['logo']}");
		$channel['url'] = $defaultURL."/attach/".$owner."/".$blog['logo'];
		$channel['width'] = $logoInfo[0];
		$channel['height'] = $logoInfo[1];
	}

	if ($blog['publishEolinSyncOnRSS']) {
		$result = DBQuery::query("SELECT e.*, c.name AS categoryName 
			FROM {$database['prefix']}Entries e 
			LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id 
			WHERE e.owner = $owner AND e.draft = 0 AND e.visibility >= 2 AND e.category >= 0 AND (c.visibility > 1 OR e.category = 0)
			ORDER BY e.published 
			DESC LIMIT {$blog['entriesOnRSS']}");
	} else { $result = DBQuery::query("SELECT e.*, c.name AS categoryName 
			FROM {$database['prefix']}Entries e 
			LEFT JOIN {$database['prefix']}Categories c ON e.owner = c.owner AND e.category = c.id 
			WHERE e.owner = $owner AND e.draft = 0 AND e.visibility = 3 AND e.category >= 0 AND (c.visibility > 1 OR e.category = 0)
			ORDER BY e.published 
			DESC LIMIT {$blog['entriesOnRSS']}");
	}
	if (!$result)
		return false;
	$channel['items'] = array();
	while ($row = mysql_fetch_array($result)) {
		if (!$blog['publishWholeOnRSS']) {
			$content = UTF8::lessen(removeAllTags(stripHTML($row['content'])), 255) . "<p><strong><a href=\"$defaultURL/" . ($blog['useSlogan'] ? "entry/{$row['slogan']}" : $row['id']) . "\">" . _t('글 전체보기') . "</a></strong></p>";
		} else {
			$content = $row['content'];
		}
		$item = array(
			'id' => $row['id'], 
			'title' => $row['title'], 
			'link' => "$defaultURL/" . ($blog['useSlogan'] ? 'entry/' . rawurlencode($row['slogan']) : $row['id']), 
			'categories' => array(), 'description' => $content, 
			'author' => $author, 
			'pubDate' => Timestamp::getRFC1123($row['published']),
			'comments' => "$defaultURL/" . ($blog['useSlogan'] ? 'entry/' . rawurlencode($row['slogan']) : $row['id']) . '#entry' . $row['id'] . 'comment',
			'guid' => "$defaultURL/" . $row['id']
		);
		if (isset($service['useNumericURLonRSS'])) {
			if ($service['useNumericURLonRSS']==true) {
				$item['link'] = $defaultURL."/".$row['id'];
			}
		}
		if (!empty($row['id'])) {
			$sql = "SELECT name, size, mime FROM {$database['prefix']}Attachments WHERE parent= {$row['id']} AND owner =$owner AND enclosure = 1";
			$attaches = DBQuery::queryRow($sql);
			if (count($attaches) > 0) {
				$item['enclosure'] = array('url' => "$serviceURL/attach/$owner/{$attaches['name']}", 'length' => $attaches['size'], 'type' => $attaches['mime']);
			}
		}
		array_push($item['categories'], $row['categoryName']);
		$tag_result = DBQuery::query("SELECT name FROM {$database['prefix']}Tags, {$database['prefix']}TagRelations WHERE id = tag AND entry = $row[1] AND owner = $owner ORDER BY name");
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
		fireEvent('refreshRSS',$rss);
		return true;
	}
	fclose($fileHandle);
	return false;
}

function publishRSS($owner, $data) {
	global $blog, $owner;
	ob_start();
	echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
	echo '<rss version="2.0">', CRLF;
	echo '	<channel>', CRLF;
	echo '		<title>', htmlspecialchars($data['channel']['title'], ENT_QUOTES), '</title>', CRLF;
	echo '		<link>', $data['channel']['link'], '</link>', CRLF;
	echo '		<description>', htmlspecialchars($data['channel']['description'], ENT_QUOTES), '</description>', CRLF;
	echo '		<language>', $data['channel']['language'], '</language>', CRLF;
	echo '		<pubDate>', $data['channel']['pubDate'], '</pubDate>', CRLF;
	echo '		<generator>', $data['channel']['generator'], '</generator>', CRLF;

	if (!empty($blog['logo']) && file_exists(ROOT."/attach/$owner/{$blog['logo']}")) {
		echo '		<image>', CRLF;
		echo '		<title>', htmlspecialchars($data['channel']['title'], ENT_QUOTES), '</title>', CRLF;
		echo '		<url>', $data['channel']['url'], '</url>', CRLF;
		echo '		<link>', $data['channel']['link'], '</link>', CRLF;
		echo '		<width>', $data['channel']['width'], '</width>', CRLF;
		echo '		<height>', $data['channel']['height'], '</height>', CRLF;
		echo '		<description>', htmlspecialchars($data['channel']['description'], ENT_QUOTES), '</description>', CRLF;
		echo '		</image>', CRLF;
	}

	foreach ($data['channel']['items'] as $item) {
		echo '		<item>', CRLF;
		echo '			<title>', htmlspecialchars($item['title'], ENT_QUOTES), '</title>', CRLF;
		echo '			<link>', $item['link'], '</link>', CRLF;
		echo '			<description>', htmlspecialchars(getEntryContentView($owner, $item['id'], $item['description'], true, 'Post', true, true), ENT_QUOTES), '</description>', CRLF;
		foreach ($item['categories'] as $category) {
			if ($category = trim($category))
				echo '			<category>', htmlspecialchars($category, ENT_QUOTES), '</category>', CRLF; 
		}
		echo '			<author>', htmlspecialchars($item['author'], ENT_QUOTES), '</author>', CRLF;
		echo '			<guid>', $item['guid'], '</guid>',CRLF;
		echo '			<comments>', $item['comments'] , '</comments>',CRLF;
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
