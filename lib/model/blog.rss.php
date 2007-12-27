<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function refreshRSS($blogid) {
	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
//	$author = POD::queryCell("SELECT CONCAT(' (', name, ')') FROM {$database['prefix']}Users WHERE userid = $blogid");
	$channel['title'] = $blog['title'];
	$channel['link'] = "$defaultURL/";
	$channel['description'] = $blog['description'];
	$channel['language'] = $blog['language'];
	$channel['pubDate'] = Timestamp::getRFC1123();
	$channel['generator'] = TEXTCUBE_NAME . ' ' . TEXTCUBE_VERSION;

	if (!empty($blog['logo']) && file_exists(ROOT."/attach/$blogid/{$blog['logo']}")) {
		$logoInfo = getimagesize(ROOT."/attach/$blogid/{$blog['logo']}");
		$channel['url'] = $serviceURL."/attach/".$blogid."/".$blog['logo'];
		$channel['width'] = $logoInfo[0];
		$channel['height'] = $logoInfo[1];
	}

	if ($blog['publishEolinSyncOnRSS']) {
		$result = POD::queryAll("SELECT 
				e.*, 
				c.name AS categoryName, 
				u.name AS author
			FROM {$database['prefix']}Entries e 
			LEFT JOIN {$database['prefix']}Categories c
				ON e.blogid = c.blogid AND e.category = c.id
			LEFT JOIN {$database['prefix']}Users u
				ON e.userid = u.userid
			WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= 2 AND e.category >= 0 AND (c.visibility > 1 OR e.category = 0)
			ORDER BY e.published 
			DESC LIMIT {$blog['entriesOnRSS']}");
	} else { $result = POD::queryAll("SELECT 
				e.*, 
				c.name AS categoryName,
				u.name AS author
			FROM {$database['prefix']}Entries e 
			LEFT JOIN {$database['prefix']}Categories c 
				ON e.blogid = c.blogid AND e.category = c.id 
			LEFT JOIN {$database['prefix']}Users u
				ON e.userid = u.userid
			WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility = 3 AND e.category >= 0 AND (c.visibility > 1 OR e.category = 0)
			ORDER BY e.published 
			DESC LIMIT {$blog['entriesOnRSS']}");
	}
	if (!$result)
		$result = array();
	$channel['items'] = array();
	foreach($result as $row) {
		$entryURL = $defaultURL . '/' . ($blog['useSlogan'] ? 'entry/' . rawurlencode($row['slogan']) : $row['id']);

		$content = getEntryContentView($blogid, $row['id'], $row['content'], $row['contentFormatter'], true, 'Post', true, true);
		$content = preg_replace('/<a href=("|\')(#[^\1]+)\1/i', '<a href=$1' . htmlspecialchars($entryURL) . '$2$1', $content);
 		if (!$blog['publishWholeOnRSS']) {
			$content .= "<p><strong><a href=\"" . htmlspecialchars($entryURL) . "\">" . _t('글 전체보기') . "</a></strong></p>";
 		}

		$item = array(
			'id' => $row['id'], 
			'title' => $row['title'], 
			'link' => $entryURL, 
			'categories' => array(), 'description' => $content, 
			'author' => '('.$row['author'].')', 
			'pubDate' => Timestamp::getRFC1123($row['published']),
			'comments' => $entryURL . '#rp',
			'guid' => "$defaultURL/" . $row['id']
		);
		if (isset($service['useNumericURLonRSS'])) {
			if ($service['useNumericURLonRSS']==true) {
				$item['link'] = $defaultURL."/".$row['id'];
			}
		}
		if (!empty($row['id'])) {
			$sql = "SELECT name, size, mime FROM {$database['prefix']}Attachments WHERE parent= {$row['id']} AND blogid =$blogid AND enclosure = 1";
			$attaches = POD::queryRow($sql);
			if (count($attaches) > 0) {
				$item['enclosure'] = array('url' => "$serviceURL/attach/$blogid/{$attaches['name']}", 'length' => $attaches['size'], 'type' => $attaches['mime']);
			}
		}
		array_push($item['categories'], $row['categoryName']);
		$tag_result = POD::queryColumn("SELECT name 
				FROM {$database['prefix']}Tags, 
					{$database['prefix']}TagRelations 
				WHERE id = tag 
					AND entry = {$row['id']}
					AND blogid = $blogid 
				ORDER BY name");
		foreach($tag_result as $tag) {
			array_push($item['categories'], $tag);
		}
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
	$path .= "/$blogid.xml";
	$fileHandle = fopen($path, 'w');
	$rss = array('channel' => $channel);
	if (fwrite($fileHandle, publishRSS($blogid, $rss))) {
		fclose($fileHandle);
		@chmod($path, 0666);
		fireEvent('refreshRSS',$rss);
		return true;
	}
	fclose($fileHandle);
	return false;
}

function getCommentRSSByEntryId($blogid, $entryId) {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;

	if(empty($blogid)) $blogid = getBlogId();
	$entry = POD::queryRow("SELECT slogan, visibility, category FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;
	$channel = array();
	$channel['title'] = $blog['title']. ': '._f('%1 에 달린 댓글',$entry['slogan']);
	if($blog['useSlogan']) {
		$channel['link'] = $defaultURL."/entry/".URL::encode($entry['slogan'],true);
	} else {
		$channel['link'] = $defaultURL."/".$entryId;
	}
	$channel['description'] = $blog['description'];
	$channel['language'] = $blog['language'];
	$channel['pubDate'] = Timestamp::getRFC1123();
	$channel['generator'] = TEXTCUBE_NAME . ' ' . TEXTCUBE_VERSION;

	if (!empty($blog['logo']) && file_exists(ROOT."/attach/$blogid/{$blog['logo']}")) {
		$logoInfo = getimagesize(ROOT."/attach/$blogid/{$blog['logo']}");
		$channel['url'] = $serviceURL."/attach/".$blogid."/".$blog['logo'];
		$channel['width'] = $logoInfo[0];
		$channel['height'] = $logoInfo[1];
	}
	$result = POD::queryAll("SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".$blogid." 
			AND entry = ".$entryId."
			AND isFiltered = 0");
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$commentURL = $channel['link']."#comment".$row['id'];
		$content = $row['comment'];
		$item = array(
			'id' => $row['id'], 
			'title' => $row['title'], 
			'link' => $commentURL, 
			'categories' => array(), 'description' => $content, 
			'author' => '('.$row['name'].')', 
			'pubDate' => Timestamp::getRFC1123($row['written']),
			'guid' => $commentURL
		);
		if($row['secret']) $item['description'] = _t('비밀 댓글입니다');
		array_push($channel['items'], $item);
	}
	$rss = array('channel' => $channel);
	return publishRSS($blogid, $rss);
}

function publishRSS($blogid, $data) {
	global $blog;
	$blogid = getBlogId();
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

	if (!empty($blog['logo']) && file_exists(ROOT."/attach/$blogid/{$blog['logo']}")) {
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
		echo '			<description>', htmlspecialchars($item['description'], ENT_QUOTES), '</description>', CRLF;
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
	if (file_exists(ROOT . "/cache/rss/".getBlogId().".xml"))
		@unlink(ROOT . "/cache/rss/".getBlogId().".xml");
}
?>
