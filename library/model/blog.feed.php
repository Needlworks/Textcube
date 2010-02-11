<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function RSSMessage($message) {
	$isPublic = (Setting::getBlogSettingGlobal('visibility',2) == 2 ? true : false);
	return ($isPublic ? $message : _text('비공개'));
}

function refreshFeed($blogid, $mode = 'both') {
	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$result = POD::queryAll("SELECT 
			e.*, 
			c.name AS categoryName, 
			u.name AS author
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN {$database['prefix']}Users u
			ON e.userid = u.userid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($blog['publishEolinSyncOnRSS'] ? '2' : '3')." AND e.category >= 0 AND (c.visibility > 1 OR e.category = 0)
		ORDER BY e.published 
		DESC LIMIT {$blog['entriesOnRSS']}");
	if (!$result)
		$result = array();
	$channel['items'] = getFeedItemByEntries($result);
	// RSS
	if($mode == 'both' || $mode == 'rss') {
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
			@chmod($path, 0666);
			fireEvent('refreshRSS',$rss);
			$result = true;
		} else $result = false;
		fclose($fileHandle);
	}
	// ATOM
	if($mode == 'both' || $mode == 'atom') {
		$path = ROOT . '/cache/atom';
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
		$atom = array('channel' => $channel);
		if (fwrite($fileHandle, publishATOM($blogid, $atom))) {
			@chmod($path, 0666);
			fireEvent('refreshATOM',$atom);
			$result = true;
		} else $result = false;
		fclose($fileHandle);
	}
	if($result) return true;
	else return false;
}

function initializeRSSchannel($blogid = null) {
	global $serviceURL, $defaultURL, $blogURL, $blog;

	if(empty($blogid)) $blogid = getBlogId();

	$channel = array();
	$channel['title'] = RSSMessage($blog['title']);
	$channel['link'] = "$defaultURL/";
	$channel['description'] = RSSMessage($blog['description']);
	$channel['language'] = $blog['language'];
	$channel['pubDate'] = Timestamp::getUNIXtime();
	$channel['generator'] = TEXTCUBE_NAME . ' ' . TEXTCUBE_VERSION;

	if ((Setting::getBlogSettingGlobal('visibility',2) == 2) && !empty($blog['logo']) && file_exists(ROOT."/attach/$blogid/{$blog['logo']}")) {
		$logoInfo = getimagesize(ROOT."/attach/$blogid/{$blog['logo']}");
		$channel['url'] = $serviceURL."/attach/".$blogid."/".$blog['logo'];
		$channel['width'] = $logoInfo[0];
		$channel['height'] = $logoInfo[1];
	}
	return $channel;
}

function getFeedItemByEntries($entries) {
	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channelItems = array();
	foreach($entries as $row) {
		$entryURL = $defaultURL . '/' . ($blog['useSloganOnPost'] ? 'entry/' . rawurlencode($row['slogan']) : $row['id']);

		$content = getEntryContentView($row['blogid'], $row['id'], $row['content'], $row['contentformatter'], true, 'Post', true, true);
		$content = preg_replace('/<a href=("|\')(#[^\1]+)\1/i', '<a href=$1' . htmlspecialchars($entryURL) . '$2$1', $content);
 		if (!$blog['publishWholeOnRSS']) {
			$content .= "<p><strong><a href=\"" . htmlspecialchars($entryURL) . "\">" . _t('글 전체보기') . "</a></strong></p>";
 		} else {
			$content .= "<p><strong><a href=\"" . htmlspecialchars($entryURL) ."?commentInput=true#entry".$row['id']."WriteComment\">" . _t('댓글 쓰기') . "</a></strong></p>";
		}
		$row['repliesCount'] = $row['comments'] + $row['trackbacks'];
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage($row['title']), 
			'link' => $entryURL, 
			'categories' => array(), 'description' => RSSMessage($content), 
			'author' => RSSMessage($row['author']), 
			'pubDate' => $row['published'],
			'updDate' => $row['modified'],
			'comments' => $entryURL . '#entry' . $row['id'] . 'comment',
			'guid' => "$defaultURL/" . $row['id'],
			'replies' => array(
				'count' => $row['repliesCount'])
		);
		if (isset($service['useNumericURLonRSS'])) {
			if ($service['useNumericURLonRSS']==true) {
				$item['link'] = $defaultURL."/".$row['id'];
			}
		}
		if (!empty($row['id'])) {
			$sql = "SELECT name, size, mime FROM {$database['prefix']}Attachments WHERE parent= {$row['id']} AND blogid = {$row['blogid']} AND enclosure = 1";
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
					AND blogid = {$row['blogid']}
				ORDER BY name");
		foreach($tag_result as $tag) {
			array_push($item['categories'], $tag);
		}
		array_push($channelItems, $item);
	}
	return $channelItems;
}

function getFeedItemByLines($lines) {
	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channelItems = array();
	foreach($lines as $row) {
		$entryURL = $defaultURL . '/line#' . ($row['id']);
		$content = $row['content'];
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage(Timestamp::format5($row['created'])), 
			'link' => $entryURL, 
			'author' => RSSMessage($row['author']), 
			'categories' => array(), 
			'description' => RSSMessage($content), 
			'pubDate' => $row['created'],
			'updDate' => $row['created'],
			'guid' => $entryURL
		);
		array_push($channelItems, $item);
	}
	return $channelItems;
}

function getResponseFeedTotal($blogid, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;
	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = $blog['title']. ': '._text('최근 댓글/트랙백 목록');

	$recentComment = getCommentFeedTotal($blogid,true,$mode);
	$recentTrackback = getTrackbackFeedTotal($blogid,true,$mode);
	$merged = array_merge($recentComment, $recentTrackback);
	$channel['items'] = $merged;
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getResponseFeedByEntryId($blogid, $entryId, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;
	
	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, category FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;
	$channel = array();

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1에 달린 최근 댓글/트랙백 목록',$entry['slogan']));

	$recentComment = getCommentFeedByEntryId($blogid,$entryId,true,$mode);
	$recentTrackback = getTrackbackFeedByEntryId($blogid,$entryId,true,$mode);
	$merged = array_merge($recentComment, $recentTrackback);
	$channel['items'] = $merged;
	
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getCommentFeedTotal($blogid, $rawMode = false, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = $blog['title']. ': '._text('최근 댓글 목록');
	
	$result = getRecentComments($blogid, Setting::getBlogSettingGlobal('commentsOnRSS',20), false, true);
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$commentURL = $defaultURL."/".$row['entry']."#comment";
		$content = htmlspecialchars($row['comment']);
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage(UTF8::lessen($row['title'],30).' : '._textf('%1님의 댓글',$row['name'])), 
			'link' => $commentURL.$row['id'], 
			'categories' => array(), 'description' => RSSMessage($content), 
			'author' => RSSMessage($row['name']), 
			'pubDate' => $row['written'],
			'comments' => $commentURL,
			'guid' => $commentURL.$row['id']
		);
		if($row['secret']) $item['title'] = $item['author'] = $item['description'] = _text('비밀 댓글입니다');
		array_push($channel['items'], $item);
	}
	if($rawMode == true) return $channel['items'];
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getCommentFeedByEntryId($blogid = null, $entryId, $rawMode = false, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;
	
	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, title, category FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1 에 달린 댓글',$entry['title']));
	if($blog['useSloganOnPost']) {
		$channel['link'] = $defaultURL."/entry/".URL::encode($entry['slogan'],true);
	} else {
		$channel['link'] = $defaultURL."/".$entryId;
	}
	$result = POD::queryAll("SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".$blogid." 
			AND entry = ".$entryId."
			AND isfiltered = 0");
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$commentURL = $channel['link']."#comment";
		$content = htmlspecialchars($row['comment']);
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage(_textf('%1님의 댓글',$row['name'] )), 
			'link' => $commentURL.$row['id'], 
			'categories' => array(), 'description' => RSSMessage($content), 
			'author' => RSSMessage($row['name']), 
			'pubDate' => $row['written'],
			'comments' => $commentURL,
			'guid' => $commentURL.$row['id']
		);
		if($row['secret']) $item['title'] = $item['author'] = $item['description'] = _text('비밀 댓글입니다');
		array_push($channel['items'], $item);
	}
	if($rawMode == true) return $channel['items'];
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}


function getTrackbackFeedTotal($blogid, $rawMode = false, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;

	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($blog['title']. ': '._text('최근 트랙백 목록'));
	$result = getRecentTrackbacks($blogid, Setting::getBlogSettingGlobal('commentsOnRSS',20), true);
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$trackbackURL = $defaultURL."/".$row['entry']."#trackback";
		$content = htmlspecialchars($row['excerpt']);
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage($row['subject']), 
			'link' => $trackbackURL.$row['id'], 
			'categories' => array(), 'description' => RSSMessage($content), 
			'author' => RSSMessage(htmlspecialchars($row['site'])), 
			'pubDate' => $row['written'],
			'comments' => $trackbackURL,
			'guid' => $trackbackURL.$row['id']
		);
		array_push($channel['items'], $item);
	}
	if($rawMode == true) return $channel['items'];
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getTrackbackFeedByEntryId($blogid = null, $entryId, $rawMode = false, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;

	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, category FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;
	$channel = array();

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1 에 달린 트랙백',$entry['slogan']));
	if($blog['useSloganOnPost']) {
		$channel['link'] = $defaultURL."/entry/".URL::encode($entry['slogan'],true);
	} else {
		$channel['link'] = $defaultURL."/".$entryId;
	}
	$result = POD::queryAll("SELECT * 
		FROM {$database['prefix']}RemoteResponses
		WHERE blogid = ".$blogid." 
			AND entry = ".$entryId."
			AND isfiltered = 0
			AND type = 'trackback'");
	if (!$result)
		$result = array();

	$channel['items'] = array();

	foreach($result as $row) {
		$trackbackURL = $channel['link']."#trackback";
		$content = htmlspecialchars($row['excerpt']);
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage($row['subject']), 
			'link' => $trackbackURL.$row['id'], 
			'categories' => array(), 'description' => RSSMessage($content), 
			'author' => RSSMessage(htmlspecialchars($row['site'])), 
			'pubDate' => $row['written'],
			'comments' => $trackbackURL,
			'guid' => $trackbackURL.$row['id']
		);
		array_push($channel['items'], $item);
	}
	if($rawMode == true) return $channel['items'];
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getCommentNotifiedFeedTotal($blogid, $mode = 'rss') {
	global $database, $serviceURL, $defaultURL, $blogURL, $blog, $service;

	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($blog['title']. ': '._text('최근 댓글 알리미 목록'));
	$mergedComments = array();
	list($comments, $paging) = getCommentsNotifiedWithPagingForOwner($blogid, '', '', '', '', 1, 20);
	for ($i = 0; $i < count($comments); $i++) {
		array_push($mergedComments, $comments[$i]);
		$result = getCommentCommentsNotified($comments[$i]['id']);
		for ($j = 0; $j < count($result); $j++) {
			array_push($mergedComments, $result[$j]);
		}
	}	
	
	if (!$mergedComments)
		$mergedComments = array();

	$channel['items'] = array();
	foreach($mergedComments as $row) {
		$item = array(
			'id' => $row['id'], 
			'title' => RSSMessage($row['entrytitle']), 
			'link' => $row['url'], 
			'categories' => array(), 
			'description' => RSSMessage(htmlspecialchars($row['comment'])), 
			'author' => RSSMessage(htmlspecialchars($row['name'])), 
			'pubDate' => $row['written'],
			'comments' => $row['entryurl'],
			'guid' => $row['url']
		);
		array_push($channel['items'], $item);
	}
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getTagFeedByTagId($blogid, $tagId, $mode = 'rss', $tagTitle = null) {

	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$entries = POD::queryAll("SELECT 
			e.*, 
			c.name AS categoryName, 
			u.name AS author
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN {$database['prefix']}Users u
			ON e.userid = u.userid
		LEFT JOIN {$database['prefix']}TagRelations t 
			ON e.id = t.entry AND e.blogid = t.blogid 
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($blog['publishEolinSyncOnRSS'] ? '2' : '3')." AND c.visibility > 1 AND t.tag = $tagId
		ORDER BY e.published 
		DESC LIMIT {$blog['entriesOnRSS']}");
	if (!$entries)
		$entries = array();

	$channel['items'] = getFeedItemByEntries($entries);
	if(!is_null($tagTitle)) {
		$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1 태그 글 목록',htmlspecialchars($tagTitle)));
	}
	$rss = array('channel' => $channel);

	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getSearchFeedByKeyword($blogid, $search, $mode = 'rss', $title = null) {

	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$search = escapeSearchString($search);
	$entries = POD::queryAll("SELECT 
			e.*, 
			c.name AS categoryName, 
			u.name AS author
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN {$database['prefix']}Users u
			ON e.userid = u.userid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($blog['publishEolinSyncOnRSS'] ? '2' : '3')." AND c.visibility > 1 AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%') 
		ORDER BY e.published 
		DESC LIMIT {$blog['entriesOnRSS']}");
	if (!$entries)
		$entries = array();

	$channel['items'] = getFeedItemByEntries($entries);
	if(!is_null($title)) {
		$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1 이 포함된 글 목록',htmlspecialchars($title)));
	}
	$rss = array('channel' => $channel);

	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getCategoryFeedByCategoryId($blogid, $categoryIds, $mode = 'rss', $categoryTitle = null) {

	global $database, $serviceURL, $defaultURL, $blog, $service;
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$entries = POD::queryAll("SELECT 
			e.*, 
			c.name AS categoryName, 
			u.name AS author
		FROM {$database['prefix']}Entries e 
		LEFT JOIN {$database['prefix']}Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN {$database['prefix']}Users u
			ON e.userid = u.userid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($blog['publishEolinSyncOnRSS'] ? '2' : '3')." AND e.category IN (".implode(',',$categoryIds).")
		ORDER BY e.published 
		DESC LIMIT {$blog['entriesOnRSS']}");
	if (!$entries)
		$entries = array();
	$channel['items'] = getFeedItemByEntries($entries);
	if(!is_null($categoryTitle)) {
		$channel['title'] = RSSMessage($blog['title']. ': '._textf('%1 카테고리 글 목록',htmlspecialchars($categoryTitle)));
	}
	$rss = array('channel' => $channel);

	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}
function getLinesFeed($blogid, $category = 'public', $mode = 'atom') {
	global $blog;
	$channel = array();
	$channel = initializeRSSchannel($blogid);	
	$lineobj = Model_Line::getInstance();
	$lineobj->reset();
	$lineobj->setFilter(array('created','bigger',Timestamp::getUNIXTime()-86400));
	$lineobj->setFilter(array('blogid','equals',$blogid));
	$lineobj->setFilter(array('category','equals',$category,true));
	$lines = $lineobj->get();
	
	$channel['items'] = getFeedItemByLines($lines);
	$channel['title'] = RSSMessage($blog['title']. ': '._text('Lines'));

	$rss = array('channel' => $channel);

	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function publishRSS($blogid, $data) {
	$context = Model_Context::getInstance();
	$blogid = getBlogId();
	ob_start();
	echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
	echo '<rss version="2.0">', CRLF;
	echo '	<channel>', CRLF;
	echo '		<title>', htmlspecialchars($data['channel']['title'], ENT_QUOTES), '</title>', CRLF;
	echo '		<link>', $data['channel']['link'], '</link>', CRLF;
	echo '		<description>', htmlspecialchars($data['channel']['description'], ENT_QUOTES), '</description>', CRLF;
	echo '		<language>', $data['channel']['language'], '</language>', CRLF;
	echo '		<pubDate>', Timestamp::getRFC1123($data['channel']['pubDate']), '</pubDate>', CRLF;
	echo '		<generator>', $data['channel']['generator'], '</generator>', CRLF;

	if ($context->getProperty('blog.logo') && file_exists(ROOT."/attach/$blogid/{$context->getProperty('blog.logo')}")) {
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
		echo '			<pubDate>', Timestamp::getRFC1123($item['pubDate']), '</pubDate>', CRLF;
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

function clearFeed() {
	if (file_exists(ROOT . "/cache/rss/".getBlogId().".xml"))
		@unlink(ROOT . "/cache/rss/".getBlogId().".xml");
	if (file_exists(ROOT . "/cache/atom/".getBlogId().".xml"))
		@unlink(ROOT . "/cache/atom/".getBlogId().".xml");
}


function publishATOM($blogid, $data) {
	global $blog;
	$blogid = getBlogId();
	ob_start();
	echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
	echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:thr="http://purl.org/syndication/thread/1.0">', CRLF;
	echo '  <title type="html">', htmlspecialchars($data['channel']['title'], ENT_QUOTES), '</title>', CRLF;
	echo '  <id>', $data['channel']['link'], '</id>', CRLF;
	echo '  <link rel="alternate" type="text/html" hreflang="', $data['channel']['language'] ,'" href="', $data['channel']['link'] , '" />', CRLF;
	echo '  <subtitle type="html">', htmlspecialchars($data['channel']['description'], ENT_QUOTES), '</subtitle>', CRLF;
	echo '  <updated>', Timestamp::getISO8601($data['channel']['pubDate']), '</updated>', CRLF;
	echo '  <generator>', $data['channel']['generator'], '</generator>', CRLF;

	foreach ($data['channel']['items'] as $item) {
		echo '  <entry>', CRLF;
		echo '    <title type="html">', htmlspecialchars($item['title'], ENT_QUOTES), '</title>', CRLF;
		echo '    <link rel="alternate" type="text/html" href="', $item['link'], '" />', CRLF;
		if(isset($item['replies'])) {
			echo '    <link rel="replies" type="application/atom+xml" href="', $data['channel']['link'], 'atom/response/', $item['id'], '" thr:count="', $item['replies']['count'] ,'"/>', CRLF;
		}
		foreach ($item['categories'] as $category) {
			if ($category = trim($category))
				echo '    <category term="', htmlspecialchars($category, ENT_QUOTES), '" />', CRLF; 
		}
		if(isset($item['author'])) {
			echo '    <author>', CRLF;
			echo '      <name>', htmlspecialchars($item['author'], ENT_QUOTES), '</name>', CRLF;
			echo '    </author>', CRLF;
		}
		echo '    <id>', $item['link'] ,'</id>', CRLF;
		if(isset($item['updDate']))
			echo '    <updated>', Timestamp::getISO8601($item['updDate']), '</updated>', CRLF;
		echo '    <published>', Timestamp::getISO8601($item['pubDate']), '</published>', CRLF;
/*		if (!empty($item['enclosure'])) {
			echo '			<enclosure url="', $item['enclosure']['url'], '" length="', $item['enclosure']['length'], '" type="', $item['enclosure']['type'], '" />', CRLF;
		}*/
		echo '    <summary type="html">', htmlspecialchars($item['description'], ENT_QUOTES), '</summary>', CRLF;

		echo '  </entry>', CRLF;
	}
	echo '</feed>', CRLF;
	$atom = ob_get_contents();
	ob_end_clean();
	return $atom;
}
?>
