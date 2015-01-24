<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function RSSMessage($message) {
	$isPublic = (Setting::getBlogSettingGlobal('visibility',2) == 2 ? true : false);
	return ($isPublic ? $message : _text('비공개'));
}

function refreshFeed($blogid, $mode = 'both') {
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	$channel = array();
	$channel = initializeRSSchannel($blogid);

	$pool->reset("Entries");
	$pool->join("Categories","left",array(array("e.blogid","eq","c.blogid"),array("e.category","eq","c.id")));
	$pool->join("Users","left",array(array("e.userid","eq","u.userid")));
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->setAlias("Users","u");
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.visibility",">=",($context->getProperty("blog.publishEolinSyncOnRSS",false) ? 2 : 3));
	$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));
	$pool->setOrder("e.published","desc");
	$pool->setLimit($context->getProperty("blog.entriesOnRSS"));

	$result = $pool->getAll("e.*, c.name AS categoryName, u.name AS author, u.loginid AS email");
	if (!$result)
		$result = array();

	$channel['items'] = getFeedItemByEntries($result);
	// RSS
	if($mode == 'both' || $mode == 'rss') {
		$path = __TEXTCUBE_CACHE_DIR__.'/rss';
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
		$path = __TEXTCUBE_CACHE_DIR__.'/atom';
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
	$ctx = Model_Context::getInstance();

	if(empty($blogid)) $blogid = getBlogId();

	$channel = array();
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'));
	$channel['link'] = $ctx->getProperty('uri.default')."/";
	$channel['description'] = RSSMessage($ctx->getProperty('blog.description'));
	$channel['language'] = $ctx->getProperty('blog.language');
	$channel['pubDate'] = Timestamp::getUNIXtime();
	$channel['generator'] = TEXTCUBE_NAME . ' ' . TEXTCUBE_VERSION;

	if ((Setting::getBlogSettingGlobal('visibility',2) == 2) && ($ctx->getProperty('blog.logo')) && file_exists(__TEXTCUBE_ATTACH_DIR__."/$blogid/".$ctx->getProperty('blog.logo'))) {
		$logoInfo = getimagesize(__TEXTCUBE_ATTACH_DIR__."/$blogid/".$ctx->getProperty('blog.logo'));
		$channel['url'] = $ctx->getProperty('uri.service')."/attach/".$blogid."/".$ctx->getProperty('blog.logo');
		$channel['width'] = $logoInfo[0];
		$channel['height'] = $logoInfo[1];
	}
	return $channel;
}

function getFeedItemByEntries($entries) {
	$ctx = Model_Context::getInstance();
	$channelItems = array();
	foreach($entries as $row) {
		$entryURL = $ctx->getProperty('uri.default') . '/' . ($ctx->getProperty('blog.useSloganOnPost') ? 'entry/' . rawurlencode($row['slogan']) : $row['id']);

		$content = getEntryContentView($row['blogid'], $row['id'], $row['content'], $row['contentformatter'], true, 'Post', true, true);
		$content = preg_replace('/<a href=("|\')(#[^\1]+)\1/i', '<a href=$1' . htmlspecialchars($entryURL) . '$2$1', $content);
 		if ($ctx->getProperty('blog.publishWholeOnRSS')) {
			$content .= "<p><strong><a href=\"" . htmlspecialchars($entryURL) . "\">" . _t('글 전체보기') . "</a></strong></p>";
 		} else {
			$content .= "<p><strong><a href=\"" . htmlspecialchars($entryURL) ."?commentInput=true#entry".$row['id']."WriteComment\">" . _t('댓글 쓰기') . "</a></strong></p>";
		}
		$row['repliesCount'] = $row['comments'] + $row['trackbacks'];

		if(!isset($row['author']) && isset($row['userid'])) {
			$row['author'] = User::getName($row['userid']);
		}
		if(!isset($row['categoryName'])) {
			if(isset($row['categoryLabel'])) {
				$row['categoryName'] = $row['categoryLabel'];
			} else {
				$row['categoryName'] = null;
			}
		}
		$item = array(
			'id' => $row['id'],
			'title' => RSSMessage($row['title']),
			'link' => $entryURL,
			'categories' => array(), 'description' => RSSMessage($content),
			'author' => RSSMessage($row['author']),
			'pubDate' => $row['published'],
			'updDate' => $row['modified'],
			'comments' => $entryURL . '#entry' . $row['id'] . 'comment',
			'guid' => $ctx->getProperty('uri.default')."/" . $row['id'],
			'replies' => array(
				'count' => $row['repliesCount'])
		);
		if(!empty($row['email'])) {
			$item['email'] = RSSMessage($row['email']);
		}
		if ($ctx->getProperty('service.useNumericURLonRSS')) {
			$item['link'] = $ctx->getProperty('uri.default')."/".$row['id'];
		}
		if (!empty($row['id'])) {
			$sql = "SELECT name, size, mime FROM ".$ctx->getProperty('database.prefix')."Attachments WHERE parent= {$row['id']} AND blogid = {$row['blogid']} AND enclosure = 1";
			$attaches = POD::queryRow($sql);
			if (count($attaches) > 0) {
				$item['enclosure'] = array('url' => $ctx->getProperty('uri.service')."/attach/$blogid/{$attaches['name']}", 'length' => $attaches['size'], 'type' => $attaches['mime']);
			}
		}
		array_push($item['categories'], $row['categoryName']);
		$tag_result = POD::queryColumn("SELECT name
				FROM ".$ctx->getProperty('database.prefix')."Tags,
					".$ctx->getProperty('database.prefix')."TagRelations
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
	$ctx = Model_Context::getInstance();
	$channelItems = array();
	foreach($lines as $row) {
		$entryURL = $ctx->getProperty('uri.default') . '/line#' . ($row['id']);
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
	$ctx = Model_Context::getInstance();
	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = $ctx->getProperty('blog.title'). ': '._text('최근 댓글/트랙백 목록');

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
	$ctx = Model_Context::getInstance();
	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, category FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;
	$channel = array();

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'). ': '._textf('%1에 달린 최근 댓글/트랙백 목록',$entry['slogan']));

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
	$ctx = Model_Context::getInstance();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = $ctx->getProperty('blog.title'). ': '._text('최근 댓글 목록');

	$result = getRecentComments($blogid, Setting::getBlogSettingGlobal('commentsOnRSS',20), false, true);
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$commentURL = $ctx->getProperty('uri.default')."/".$row['entry']."#comment";
		$content = htmlspecialchars($row['comment']);
		$item = array(
			'id' => $row['id'],
			'title' => RSSMessage(Utils_Unicode::lessen($row['title'],30).' : '._textf('%1님의 댓글',$row['name'])),
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
	$ctx = Model_Context::getInstance();

	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, title, category FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'). ': '._textf('%1 에 달린 댓글',$entry['title']));
	if($ctx->getProperty('blog.useSloganOnPost')) {
		$channel['link'] = $ctx->getProperty('uri.default')."/entry/".URL::encode($entry['slogan'],true);
	} else {
		$channel['link'] = $ctx->getProperty('uri.default')."/".$entryId;
	}
	$result = POD::queryAll("SELECT *
		FROM ".$ctx->getProperty('database.prefix')."Comments
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
	$ctx = Model_Context::getInstance();
	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'). ': '._text('최근 트랙백 목록'));
	$result = getRecentTrackbacks($blogid, Setting::getBlogSettingGlobal('commentsOnRSS',20), true);
	if (!$result)
		$result = array();

	$channel['items'] = array();
	foreach($result as $row) {
		$trackbackURL = $ctx->getProperty('uri.default')."/".$row['entry']."#trackback";
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
	$ctx = Model_Context::getInstance();
	if(empty($blogid)) $blogid = getBlogId();

	$entry = POD::queryRow("SELECT slogan, visibility, category FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $entryId");
	if(empty($entry)) return false;
	if($entry['visibility'] < 2) return false;
	if(in_array($entry['category'], getCategoryVisibilityList($blogid, 'private'))) return false;
	$channel = array();

	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'). ': '._textf('%1 에 달린 트랙백',$entry['slogan']));
	if($ctx->getProperty('blog.useSloganOnPost')) {
		$channel['link'] = $ctx->getProperty('uri.default')."/entry/".URL::encode($entry['slogan'],true);
	} else {
		$channel['link'] = $ctx->getProperty('uri.default')."/".$entryId;
	}
	$result = POD::queryAll("SELECT *
		FROM ".$ctx->getProperty('database.prefix')."RemoteResponses
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
	$ctx = Model_Context::getInstance();
	if(empty($blogid)) $blogid = getBlogId();
	$channel = initializeRSSchannel($blogid);
	$channel['title'] = RSSMessage($ctx->getProperty('blog.title'). ': '._text('최근 댓글 알리미 목록'));
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
	$ctx = Model_Context::getInstance();
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$entries = POD::queryAll("SELECT
			e.*,
			c.name AS categoryName,
			u.name AS author,
			u.loginid AS email
		FROM ".$ctx->getProperty('database.prefix')."Entries e
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Users u
			ON e.userid = u.userid
		LEFT JOIN ".$ctx->getProperty('database.prefix')."TagRelations t
			ON e.id = t.entry AND e.blogid = t.blogid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($ctx->getProperty('blog.publishEolinSyncOnRSS') ? '2' : '3')." AND c.visibility > 1 AND t.tag = $tagId
		ORDER BY e.published
		DESC LIMIT ".$ctx->getProperty('blog.entriesOnRSS'));
	if (!$entries)
		$entries = array();
	return getFeedWithEntries($blogid, $entries, _textf('%1 태그 글 목록',$tagTitle), $mode);
}

function getSearchFeedByKeyword($blogid, $search, $mode = 'rss', $title = null) {
	$ctx = Model_Context::getInstance();
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$search = escapeSearchString($search);
	$entries = POD::queryAll("SELECT
			e.*,
			c.name AS categoryName,
			u.name AS author,
			u.loginid AS email
		FROM ".$ctx->getProperty('database.prefix')."Entries e
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Users u
			ON e.userid = u.userid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($ctx->getProperty('blog.publishEolinSyncOnRSS') ? '2' : '3')." AND c.visibility > 1 AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%')
		ORDER BY e.published
		DESC LIMIT ".$ctx->getProperty('blog.entriesOnRSS'));
	if (!$entries)
		$entries = array();
	return getFeedWithEntries($blogid, $entries, _textf('%1 이 포함된 글 목록',$title), $mode);
}

function getCategoryFeedByCategoryId($blogid, $categoryIds, $mode = 'rss', $categoryTitle = null) {
	$ctx = Model_Context::getInstance();
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$entries = POD::queryAll("SELECT
			e.*,
			c.name AS categoryName,
			u.name AS author,
			u.loginid AS email
		FROM ".$ctx->getProperty('database.prefix')."Entries e
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Categories c
			ON e.blogid = c.blogid AND e.category = c.id
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Users u
			ON e.userid = u.userid
		WHERE e.blogid = $blogid AND e.draft = 0 AND e.visibility >= ".($ctx->getProperty('blog.publishEolinSyncOnRSS') ? '2' : '3')." AND e.category IN (".implode(',',$categoryIds).")
		ORDER BY e.published
		DESC LIMIT ".$ctx->getProperty('blog.entriesOnRSS'));
	return getFeedWithEntries($blogid, $entries, _textf('%1 카테고리 글 목록',$categoryTitle), $mode);
}

function getFeedWithEntries($blogid, $entries, $title = null, $mode = 'rss'){
	$context = Model_Context::getInstance();
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	if (!$entries)
		$entries = array();
	$channel['items'] = getFeedItemByEntries($entries);
	if(!is_null($title)) {// TODO : change blog.title to support other blogs
		$channel['title'] = RSSMessage($context->getProperty('blog.title'). ': '.htmlspecialchars($title));
	}
	$rss = array('channel' => $channel);
	if($mode == 'rss') return publishRSS($blogid, $rss);
	else if($mode == 'atom') return publishATOM($blogid, $rss);
	return false;
}

function getLinesFeed($blogid, $category = 'public', $mode = 'atom') {
	$context = Model_Context::getInstance();
	$blogTitle = $context->getProperty('blog.title');
	$channel = array();
	$channel = initializeRSSchannel($blogid);
	$lineobj = Model_Line::getInstance();
	$lineobj->reset();
	$lineobj->setFilter(array('created','bigger',Timestamp::getUNIXTime()-86400));
	$lineobj->setFilter(array('blogid','equals',$blogid));
	$lineobj->setFilter(array('category','equals',$category,true));
	$lines = $lineobj->get();

	$channel['items'] = getFeedItemByLines($lines);
	$channel['title'] = RSSMessage($blogTitle. ': '._text('Lines'));

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
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">', CRLF;
	echo '	<channel>', CRLF;
	echo '		<title>', htmlspecialchars($data['channel']['title'], ENT_QUOTES), '</title>', CRLF;
	echo '		<link>', $data['channel']['link'], '</link>', CRLF;
	echo '		<description>', htmlspecialchars($data['channel']['description'], ENT_QUOTES), '</description>', CRLF;
	echo '		<language>', $data['channel']['language'], '</language>', CRLF;
	echo '		<pubDate>', Timestamp::getRFC1123($data['channel']['pubDate']), '</pubDate>', CRLF;
	echo '		<generator>', $data['channel']['generator'], '</generator>', CRLF;
	if(!empty($data['channel']['feed_link'])) {
		echo '		<atom:link href="', $data['channel']['feed_link'] , '" rel="self" type="application/rss+xml" />', CRLF;
	}
	if ($context->getProperty('blog.logo') && file_exists(__TEXTCUBE_ATTACH_DIR__."/$blogid/{$context->getProperty('blog.logo')}")) {
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
		if(!empty($item['email'])) {
			echo '			<author>',$item['email'],' (', htmlspecialchars($item['author'], ENT_QUOTES), ')</author>', CRLF;
		} else {
			echo '			<author>', htmlspecialchars($item['author'], ENT_QUOTES), '</author>', CRLF;
		}
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
	if (file_exists(__TEXTCUBE_CACHE_DIR__."/rss/".getBlogId().".xml"))
		@unlink(__TEXTCUBE_CACHE_DIR__."/rss/".getBlogId().".xml");
	if (file_exists(__TEXTCUBE_CACHE_DIR__."/atom/".getBlogId().".xml"))
		@unlink(__TEXTCUBE_CACHE_DIR__."/atom/".getBlogId().".xml");
}


function publishATOM($blogid, $data) {
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
