<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getReaderSetting($blogid) {
    global $database;
    return POD::queryRow("SELECT * FROM {$database['prefix']}FeedSettings WHERE blogid = $blogid");
}

function setReaderSetting($blogid, $setting) {
    global $database;
    $sql = "UPDATE {$database['prefix']}FeedSettings SET ";
    if (getUserId() == 1) {
        if (isset($setting['updatecycle'])) {
            POD::query("UPDATE {$database['prefix']}FeedSettings SET updatecycle = {$setting['updatecycle']}");
        }
        if (isset($setting['feedlife'])) {
            POD::query("UPDATE {$database['prefix']}FeedSettings SET feedlife = {$setting['feedlife']}");
        }
    }
    if (!empty($setting['loadimage'])) {
        $sql .= "loadimage = {$setting['loadimage']}, ";
    }
    if (!empty($setting['allowscript'])) {
        $sql .= "allowscript = {$setting['allowscript']}, ";
    }
    if (!empty($setting['newwindow'])) {
        $sql .= "newwindow = {$setting['newwindow']}, ";
    }
    return POD::execute("$sql blogid = blogid WHERE blogid = $blogid");
}

function markAsUnread($blogid, $id) {
    global $database;
    return POD::execute("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $blogid AND item = $id");
}

function markAsRead($blogid, $id) {
    global $database;
    return POD::execute("INSERT INTO {$database['prefix']}FeedReads (blogid, item) VALUES ($blogid, $id)");
}

function markAsReadAll($blogid) {
    global $database;
    $registeredFeeds = POD::queryColumn("SELECT feed
		FROM {$database['prefix']}FeedGroupRelations
		WHERE blogid = $blogid");
    if (isset($registeredFeeds)) {
        $feedItems = array();
        foreach ($registeredFeeds as $feed) {    /// Gather feed information
            $feedIds = POD::queryColumn("SELECT id FROM {$database['prefix']}FeedItems WHERE feed = $feed");
            if (!empty($feedIds)) {
                $feedItems = array_merge($feedItems, $feedIds);
            }
        }

        if (!empty($feedItems)) {
            $readFeedItems = POD::queryColumn("SELECT item FROM {$database['prefix']}FeedReads
				WHERE blogid = $blogid");
            $unreadFeedItems = array_diff($feedItems, $readFeedItems);
            if (!empty($unreadFeedItems)) {
                foreach ($unreadFeedItems as $item) {
                    POD::execute("INSERT INTO {$database['prefix']}FeedReads (blogid, item) VALUES ($blogid, $item)");
                }

            }
        }
    }
    return true;
}

function markAsStar($blogid, $id, $flag) {
    global $database;
    if (POD::queryCell("SELECT i.id FROM {$database['prefix']}FeedGroups g, {$database['prefix']}FeedGroupRelations gr, {$database['prefix']}Feeds f, {$database['prefix']}FeedItems i WHERE g.blogid = $blogid AND gr.feed = f.id AND gr.blogid = g.blogid AND gr.groupid = g.id AND f.id = i.feed AND i.id = $id")) {
        if ($flag) {
            POD::query("REPLACE INTO {$database['prefix']}FeedStarred VALUES($blogid, $id)");
        } else {
            POD::query("DELETE FROM {$database['prefix']}FeedStarred WHERE blogid = $blogid AND item = $id");
        }
        return true;
    } else {
        return false;
    }
}

function getFeedGroups($blogid, $starredOnly = false, $searchKeyword = null) {
    global $database;
    $searchKeyword = escapeSearchString($searchKeyword);
    if ($starredOnly !== false) {
        $condition = "AND s.item IS NOT NULL";
    } else {
        if ($searchKeyword !== null) {
            $condition = "AND (i.title LIKE '%{$searchKeyword}%' OR i.description LIKE '%{$searchKeyword}%')";
        } else {
            $condition = '';
        }
    }
    $sql = "SELECT
					g.id, g.title
				FROM
					{$database['prefix']}FeedGroups g
				LEFT JOIN
					{$database['prefix']}FeedGroupRelations r
				ON
					r.blogid = $blogid AND
					r.blogid = g.blogid AND
					g.id = r.groupid
				LEFT JOIN
					{$database['prefix']}FeedItems i
				ON
					r.feed = i.feed
				LEFT JOIN
					{$database['prefix']}FeedStarred s
				ON
					s.blogid = $blogid AND
					i.id = s.item
				WHERE
					g.blogid = $blogid
					$condition
				GROUP BY g.id, g.title
				ORDER BY g.title";
    return POD::queryAll($sql);
}

function getFeeds($blogid, $group = 0, $starredOnly = false, $searchKeyword = null) {
    global $database;
    $searchKeyword = escapeSearchString($searchKeyword);
    if ($starredOnly !== false) {
        $condition = "AND s.item IS NOT NULL";
    } else {
        if ($searchKeyword !== null) {
            $condition = "AND (i.title LIKE '%{$searchKeyword}%' OR i.description LIKE '%{$searchKeyword}%')";
        } else {
            $condition = '';
        }
    }
    $condition .= ($group == 0) ? '' : " AND g.id = $group";
    $sql = "SELECT
					f.id, f.xmlurl, f.blogURL, f.title, f.description, f.modified
				FROM
					{$database['prefix']}FeedGroups g,
					{$database['prefix']}FeedGroupRelations r,
					{$database['prefix']}Feeds f
				LEFT JOIN
					{$database['prefix']}FeedItems i
				ON
					f.id = i.feed
				LEFT JOIN
					{$database['prefix']}FeedStarred s
				ON
					s.blogid = $blogid AND
					i.id = s.item
				WHERE
					r.blogid = $blogid AND
					r.blogid = g.blogid AND
					g.id = r.groupid AND
					r.feed = f.id
					$condition
				GROUP BY f.id, f.xmlurl, f.blogURL, f.title, f.description, f.modified
				ORDER BY f.title";
    return POD::queryAll($sql);
}

function getFeedEntriesTotalCount($blogid, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null) {
    global $database;
    $searchKeyword = escapeSearchString($searchKeyword);
    if ($starredOnly !== false) {
        $condition = 'AND s.item IS NOT NULL';
    } else {
        if ($searchKeyword !== null) {
            $condition = "AND (i.title LIKE '%{$searchKeyword}%' OR i.description LIKE '%{$searchKeyword}%')";
        } else {
            $condition = '';
        }
    }
    $condition .= ($group == 0) ? '' : " AND g.id = $group";
    $condition .= ($feed == 0) ? '' : " AND f.id = $feed";
    $condition .= ($unreadOnly == false) ? '' : ' AND rd.item IS NULL';
    $sql = "SELECT
					COUNT(i.id)
				FROM
					{$database['prefix']}FeedGroups g,
					{$database['prefix']}FeedGroupRelations r,
					{$database['prefix']}Feeds f
				LEFT JOIN
					{$database['prefix']}FeedItems i
				ON
					f.id = i.feed
				LEFT JOIN
					{$database['prefix']}FeedStarred s
				ON
					s.blogid = $blogid AND
					i.id = s.item
				LEFT JOIN
					{$database['prefix']}FeedReads rd
				ON
					rd.blogid = $blogid AND
					i.id = rd.item
				WHERE
					r.blogid = $blogid AND
					r.blogid = g.blogid AND
					g.id = r.groupid AND
					r.feed = f.id
					$condition";
    return POD::queryCell($sql);
}

function getFeedEntries($blogid, $group = 0, $feed = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $offset = 0) {
    global $database;
    $searchKeyword = escapeSearchString($searchKeyword);
    if ($starredOnly !== false) {
        $condition = 'AND s.item IS NOT NULL';
    } else {
        if ($searchKeyword !== null) {
            $condition = "AND (i.title LIKE '%{$searchKeyword}%' OR i.description LIKE '%{$searchKeyword}%')";
        } else {
            $condition = '';
        }
    }
    $condition .= ($group == 0) ? '' : " AND g.id = $group";
    $condition .= ($feed == 0) ? '' : " AND f.id = $feed";
    $condition .= ($unreadOnly == false) ? '' : ' AND rd.item IS NULL';
    $sql = "SELECT
					s.item, i.id, i.title AS entry_title, i.enclosure, f.title AS blog_title, i.written, i.tags, i.author, rd.item AS wasread
				FROM
					{$database['prefix']}FeedGroups g,
					{$database['prefix']}FeedGroupRelations r,
					{$database['prefix']}Feeds f,
					{$database['prefix']}FeedItems i
				LEFT JOIN
					{$database['prefix']}FeedStarred s
				ON
					s.blogid = $blogid AND
					i.id = s.item
				LEFT JOIN
					{$database['prefix']}FeedReads rd
				ON
					rd.blogid = $blogid AND
					i.id = rd.item
				WHERE
					r.blogid = $blogid AND
					r.blogid = g.blogid AND
					g.id = r.groupid AND
					r.feed = f.id AND
					f.id = i.feed
					$condition
				GROUP BY i.id, s.item, entry_title, i.enclosure, blog_title, i.written, i.tags, i.author, wasread
				ORDER BY i.written DESC, i.id DESC";
    $sql .= " LIMIT " . ($offset == 0 ? 100 : min($offset, 400)) . " OFFSET " . $offset;
    return POD::queryAll($sql);
}

function getFeedEntry($blogid, $group = 0, $feed = 0, $entry = 0, $unreadOnly = false, $starredOnly = false, $searchKeyword = null, $position = 'current', $markAsRead = 'read') {
    // if failed, return false or null. check both!
    global $database;
    $setting = getReaderSetting($blogid);
    $searchKeyword = escapeSearchString($searchKeyword);
    if ($entry == 0 || $position != 'current') {
        if ($starredOnly !== false) {
            $condition = 'AND s.item IS NOT NULL';
        } else {
            if ($searchKeyword !== null) {
                $condition = "AND (i.title LIKE '%{$searchKeyword}%' OR i.description LIKE '%{$searchKeyword}%')";
            } else {
                $condition = '';
            }
        }
        $condition .= ($group == 0) ? '' : " AND g.id = $group";
        $condition .= ($feed == 0) ? '' : " AND f.id = $feed";
        $sql = "SELECT
						i.id, i.title AS entry_title, i.description, f.title AS blog_title, i.author, i.written, i.tags, i.permalink, rd.item AS wasread, f.language, enclosure
					FROM
						{$database['prefix']}FeedGroups g,
						{$database['prefix']}FeedGroupRelations r,
						{$database['prefix']}Feeds f,
						{$database['prefix']}FeedItems i
					LEFT JOIN
						{$database['prefix']}FeedStarred s
					ON
						s.blogid = $blogid AND
						i.id = s.item
					LEFT JOIN
						{$database['prefix']}FeedReads rd
					ON
						rd.blogid = $blogid AND
						i.id = rd.item
					WHERE
						r.blogid = $blogid AND
						r.blogid = g.blogid AND
						g.id = r.groupid AND
						r.feed = f.id AND
						f.id = i.feed
						$condition
					GROUP BY i.id, entry_title, i.description, blog_title, i.author, i.written, i.tags, i.permalink, wasread, f.language, enclosure
					ORDER BY i.written DESC, i.id DESC";
        if ($position == 'current') {
            if ($row = POD::queryRow("$sql LIMIT 1")) {
                $row['description'] = adjustRelativePathImage($row['description'], $row['permalink']);
                $row['description'] = filterJavaScript($row['description'], ($setting['allowscript'] == 1 ? false : true));
            }
            return $row;
        } else {
            $result = POD::queryAll($sql);
            $prevRow = null;
            while ($row = array_shift($result)) {
                if ($row['id'] == $entry) {
                    if ($position == 'before') {
                        while ($row = array_shift($result)) {
                            if ($unreadOnly == false || !$row['wasread']) {
                                break;
                            }
                        }
                        if ($markAsRead == 'read') {
                            POD::query("REPLACE INTO {$database['prefix']}FeedReads VALUES($blogid, {$row['id']})");
                        }
                        if ($row) {
                            $row['description'] = adjustRelativePathImage($row['description'], $row['permalink']);
                            $row['description'] = filterJavaScript($row['description'], ($setting['allowscript'] == 1 ? false : true));
                        }
                        return $row;
                    } else {
                        if ($position == 'after') {
                            if ($markAsRead == 'read') {
                                POD::query("REPLACE INTO {$database['prefix']}FeedReads VALUES($blogid, {$prevRow['id']})");
                            }
                            if ($prevRow) {
                                $prevRow['description'] = adjustRelativePathImage($prevRow['description'], $row['permalink']);
                                $prevRow['description'] = filterJavaScript($prevRow['description'], ($setting['allowscript'] == 1 ? false : true));
                            }
                            return $prevRow;
                        }
                    }
                }
                if ($unreadOnly == false || !$row['wasread']) {
                    $prevRow = $row;
                }
            }
            return null;
        }
    } else {
        POD::query("REPLACE INTO {$database['prefix']}FeedReads VALUES($blogid, $entry)");
        $sql = "SELECT
						i.id, i.title AS entry_title, i.description, f.title AS blog_title, i.author, i.written, i.tags, i.permalink, f.language, enclosure
					FROM
						{$database['prefix']}FeedGroups g,
						{$database['prefix']}FeedGroupRelations r,
						{$database['prefix']}Feeds f,
						{$database['prefix']}FeedItems i
					WHERE
						r.blogid = $blogid AND
						r.blogid = g.blogid AND
						r.feed = f.id AND
						r.groupid = g.id AND
						i.id = $entry AND
						f.id = i.feed";
        if ($row = POD::queryRow($sql)) {
            $row['description'] = adjustRelativePathImage($row['description'], $row['permalink']);
            $row['description'] = filterJavaScript($row['description'], ($setting['allowscript'] == 1 ? false : true));
        }
        return $row;
    }
}

function addFeedGroup($blogid, $title) {
    global $database;
    $title = POD::escapeString(Utils_Unicode::lessenAsEncoding($title, 255));
    if (empty($title)) {
        return 1;
    }
    if (POD::queryCell("SELECT id FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid AND title = '$title'") !== null) {
        return 2;
    }
    $id = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid") + 1;
    $result = POD::queryCount("INSERT INTO {$database['prefix']}FeedGroups VALUES($blogid, $id, '$title')");
    if ($result != 1) {
        return -1;
    }
    return 0;
}

function editFeedGroup($blogid, $id, $title) {
    global $database;
    $title = POD::escapeString(Utils_Unicode::lessenAsEncoding($title, 255));
    if (empty($title)) {
        return 1;
    }
    $prevTitle = POD::queryCell("SELECT title FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid AND id = $id");
    if ($prevTitle == $title) {
        return 0;
    }
    if ($prevTitle === null) {
        return -1;
    }
    $result = POD::queryCount("UPDATE {$database['prefix']}FeedGroups SET title = '$title' WHERE blogid = $blogid AND id = $id");
    if ($result != 1) {
        return -1;
    }
    return 0;
}

function deleteFeedGroup($blogid, $id) {
    global $database;
    if ($id == 0) {
        return -1;
    }
    POD::query("UPDATE {$database['prefix']}FeedGroupRelations SET groupid = 0 WHERE blogid = $blogid AND groupid = $id");
    $result = POD::queryCount("DELETE FROM {$database['prefix']}FeedGroups WHERE id = $id");
    if ($result != 1) {
        return 1;
    }
    return 0;
}

function addFeed($blogid, $group = 0, $url, $getEntireFeed = true, $htmlURL = '', $blogTitle = '', $blogDescription = '') {
    global $database;
    if (strpos(strtolower($url), 'http://') !== 0) {
        $url = 'http://' . $url;
    }
    $url = rtrim($url, '/');
    $escapedURL = POD::escapeString($url);
    if (POD::queryExistence("SELECT f.id FROM {$database['prefix']}Feeds f, {$database['prefix']}FeedGroups g, {$database['prefix']}FeedGroupRelations r WHERE r.blogid = $blogid AND r.blogid = g.blogid AND r.feed = f.id AND r.groupid = g.id AND f.xmlurl = '$escapedURL'")) {
        return 1;
    }
    if ($id = POD::queryCell("SELECT id FROM {$database['prefix']}Feeds WHERE xmlurl = '$escapedURL'")) {
        POD::query("INSERT INTO {$database['prefix']}FeedGroupRelations VALUES($blogid, $id, $group)");
        return 0;
    }
    if ($getEntireFeed) {
        list($status, $feed, $xml) = getRemoteFeed($url);
        if ($status > 0) {
            return $status;
        }

        $id = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Feeds");
        if (!$id) {
            $id = 0;
        }
        $id++;

        POD::query("INSERT INTO {$database['prefix']}Feeds VALUES($id, '{$feed['xmlurl']}', '{$feed['blogURL']}', '{$feed['title']}', '{$feed['description']}', '{$feed['language']}', {$feed['modified']})");
        POD::query("INSERT INTO {$database['prefix']}FeedGroupRelations VALUES($blogid, $id, $group)");
        saveFeedItems($id, $xml);
    } else {
        $htmlURL = POD::escapeString(Utils_Unicode::lessenAsEncoding($htmlURL));
        $blogTitle = POD::escapeString(Utils_Unicode::lessenAsEncoding($blogTitle));
        $blogDescription = POD::escapeString(Utils_Unicode::lessenAsEncoding(stripHTML($blogDescription)));

        $id = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Feeds");
        if (!$id) {
            $id = 0;
        }
        $id++;

        POD::query("INSERT INTO {$database['prefix']}Feeds VALUES($id, '$escapedURL', '$htmlURL', '$blogTitle', '$blogDescription', 'en-US', 0)");
        POD::query("INSERT INTO {$database['prefix']}FeedGroupRelations VALUES($blogid, $id, $group)");
    }
    return 0;
}

function getRemoteFeed($url) {
    global $service, $serviceURL;
    $xml = fireEvent('GetRemoteFeed', null, $url);
    if (empty($xml)) {
        $request = new HTTPRequest($url);
        $request->referer = $serviceURL;
        $request->timeout = 3;
        if (!$request->send()) {
            return array(2, null, null);
        }
        $xml = $request->responseText;
    }
    $feed = array('xmlurl' => (isset($request) ? $request->url : $url));
    $xmls = new XMLStruct();
    if (!$xmls->open($xml, $service['encoding'])) {
        if (preg_match_all('/<link .*?rel\s*=\s*[\'"]?alternate.*?>/i', $xml, $matches)) {
            foreach ($matches[0] as $link) {
                $attributes = Utils_Misc::getAttributesFromString($link);
                if (isset($attributes['href'])) {
                    $urlInfo = parse_url($url);
                    $rssInfo = parse_url($attributes['href']);
                    $rssURL = false;
                    if (isset($rssInfo['scheme']) && $rssInfo['scheme'] == 'http') {
                        $rssURL = $attributes['href'];
                    } else {
                        if (isset($rssInfo['path'])) {
                            if ($rssInfo['path']{0} == '/') {
                                $rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}{$rssInfo['path']}";
                            } else {
                                $rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}" . (isset($urlInfo['path']) ? rtrim($urlInfo['path'], '/') : '') . '/' . $rssInfo['path'];
                            }
                        }
                    }
                    if ($rssURL && $url != $rssURL) {
                        return getRemoteFeed($rssURL);
                    }
                }
            }
        }
        return array(3, null, null);
    }
    if ($xmls->getAttribute('/rss', 'version')) {
        $feed['blogURL'] = $xmls->getValue('/rss/channel/link');
        $feed['title'] = $xmls->getValue('/rss/channel/title');
        $feed['description'] = $xmls->getValue('/rss/channel/description');
        if (Validator::language($xmls->getValue('/rss/channel/language'))) {
            $feed['language'] = $xmls->getValue('/rss/channel/language');
        } else {
            if (Validator::language($xmls->getValue('/rss/channel/dc:language'))) {
                $feed['language'] = $xmls->getValue('/rss/channel/dc:language');
            } else {
                $feed['language'] = 'en-US';
            }
        }
        $feed['modified'] = gmmktime();
    } else {
        if ($xmls->doesExist('/feed')) {
            $feed['blogURL'] = $xmls->getAttribute('/feed/link', 'href');
            $feed['title'] = $xmls->getValue('/feed/title');
            $feed['description'] = $xmls->getValue('/feed/tagline');
            if (Validator::language($xmls->getAttribute('/feed', 'xml:lang'))) {
                $feed['language'] = $xmls->getAttribute('/feed', 'xml:lang');
            } else {
                $feed['language'] = 'en-US';
            }
            $feed['modified'] = gmmktime();
        } else {
            if ($xmls->getAttribute('/rdf:RDF', 'xmlns')) {
                if ($xmls->getAttribute('/rdf:RDF/channel/link', 'href')) {
                    $feed['blogURL'] = $xmls->getAttribute('/rdf:RDF/channel/link', 'href');
                } else {
                    if ($xmls->getValue('/rdf:RDF/channel/link')) {
                        $feed['blogURL'] = $xmls->getValue('/rdf:RDF/channel/link');
                    } else {
                        $feed['blogURL'] = '';
                    }
                }
                $feed['title'] = $xmls->getValue('/rdf:RDF/channel/title');
                $feed['description'] = $xmls->getValue('/rdf:RDF/channel/description');
                if (Validator::language($xmls->getValue('/rdf:RDF/channel/dc:language'))) {
                    $feed['language'] = $xmls->getValue('/rdf:RDF/channel/dc:language');
                } else {
                    if (Validator::language($xmls->getAttribute('/rdf:RDF', 'xml:lang'))) {
                        $feed['language'] = $xmls->getAttribute('/rdf:RDF', 'xml:lang');
                    } else {
                        $feed['language'] = 'en-US';
                    }
                }
                $feed['modified'] = gmmktime();
            } else {
                return array(3, null, null);
            }
        }
    }

    $feed['xmlurl'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($feed['xmlurl'])));
    $feed['blogURL'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($feed['blogURL'])));
    $feed['title'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($feed['title'])));
    $feed['description'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct(stripHTML($feed['description']))));
    $feed['language'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($feed['language']), 255));

    return array(0, $feed, $xml);
}

function saveFeedItems($feedId, $xml) {
    global $database, $service;
    $xmls = new XMLStruct();
    if (!$xmls->open($xml, $service['encoding'])) {
        return false;
    }
    if ($xmls->getAttribute('/rss', 'version')) {
        for ($i = 0; $link = $xmls->getValue("/rss/channel/item[$i]/link"); $i++) {
            $item = array('permalink' => rawurldecode($link));
            if (!$item['author'] = $xmls->getValue("/rss/channel/item[$i]/author")) {
                $item['author'] = $xmls->getValue("/rss/channel/item[$i]/dc:creator");
            }
            $item['title'] = $xmls->getValue("/rss/channel/item[$i]/title");
            if (!$item['description'] = $xmls->getValue("/rss/channel/item[$i]/content:encoded")) {
                $item['description'] = $xmls->getValue("/rss/channel/item[$i]/description");
            }
            $item['tags'] = array();
            for ($j = 0; $tag = $xmls->getValue("/rss/channel/item[$i]/category[$j]"); $j++)
                if (!empty($tag)) {
                    array_push($item['tags'], $tag);
                }
            for ($j = 0; $tag = $xmls->getValue("/rss/channel/item[$i]/subject[$j]"); $j++)
                if (!empty($tag)) {
                    array_push($item['tags'], $tag);
                }
            $item['enclosures'] = array();
            for ($j = 0; $url = $xmls->getAttribute("/rss/channel/item[$i]/enclosure[$j]", 'url'); $j++)
                if (!empty($url)) {
                    array_push($item['enclosures'], $url);
                }
            if ($xmls->getValue("/rss/channel/item[$i]/pubDate")) {
                $item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/pubDate"));
            } else {
                if ($xmls->getValue("/rss/channel/item[$i]/dc:date")) {
                    $item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/dc:date"));
                } else {
                    $item['written'] = 0;
                }
            }
            saveFeedItem($feedId, $item);
        }
    } else {
        if ($xmls->doesExist('/feed')) {
            for ($i = 0; $link = $xmls->getValue("/feed/entry[$i]/id"); $i++) {
                for ($j = 0; $rel = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'rel'); $j++) {
                    if ($rel == 'alternate') {
                        $link = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'href');
                        break;
                    }
                }
                $item = array('permalink' => rawurldecode($link));
                $item['author'] = $xmls->getValue("/feed/entry[$i]/author/name");
                $item['title'] = $xmls->getValue("/feed/entry[$i]/title");
                if (!$item['description'] = $xmls->getValue("/feed/entry[$i]/content")) {
                    $item['description'] = $xmls->getValue("/feed/entry[$i]/summary");
                }
                $item['tags'] = array();
                for ($j = 0; $tag = $xmls->getValue("/feed/entry[$i]/dc:subject[$j]"); $j++)
                    if (!empty($tag)) {
                        array_push($item['tags'], $tag);
                    }
                $item['enclosures'] = array();
                for ($j = 0; $url = $xmls->getAttribute("/feed/entry[$i]/enclosure[$j]", 'url'); $j++)
                    if (!empty($url)) {
                        array_push($item['enclosures'], $url);
                    }
                $item['written'] = parseDate($xmls->getValue("/feed/entry[$i]/issued"));
                saveFeedItem($feedId, $item);
            }
        } else {
            if ($xmls->getAttribute('/rdf:RDF', 'xmlns')) {
                for ($i = 0; $link = $xmls->getValue("/rdf:RDF/item[$i]/link"); $i++) {
                    $item = array('permalink' => rawurldecode($link));
                    $item['author'] = $xmls->getValue("/rdf:RDF/item[$i]/dc:creator");
                    $item['title'] = $xmls->getValue("/rdf:RDF/item[$i]/title");
                    if (!$item['description'] = $xmls->getValue("/rdf:RDF/item[$i]/content:encoded")) {
                        $item['description'] = $xmls->getValue("/rdf:RDF/item[$i]/description");
                    }
                    $item['tags'] = array();
                    $item['enclosures'] = array();
                    $item['written'] = parseDate($xmls->getValue("/rdf:RDF/item[$i]/dc:date"));
                    saveFeedItem($feedId, $item);
                }
            } else {
                return false;
            }
        }
    }
    $deadLine = 0;
    $feedlife = POD::queryCell("SELECT feedlife FROM {$database['prefix']}FeedSettings");
    if ($feedlife > 0) {
        $deadLine = gmmktime() - $feedlife * 86400;
    }
    if ($result = POD::queryAll("SELECT id FROM {$database['prefix']}FeedItems LEFT JOIN {$database['prefix']}FeedStarred ON id = item WHERE item IS NULL AND written < $deadLine")) {
        while (list($id) = array_shift($result))
            POD::query("DELETE FROM {$database['prefix']}FeedItems WHERE id = $id");
    }
    if ($result = POD::queryAll("SELECT blogid, item FROM {$database['prefix']}FeedReads LEFT JOIN {$database['prefix']}FeedItems ON id = item WHERE id IS NULL")) {
        while (list($readsOwner, $readsItem) = array_shift($result))
            POD::query("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $readsOwner AND item = $readsItem");
    }
    return true;
}

function saveFeedItem($feedId, $item) {
    global $database;

    $item = fireEvent('SaveFeedItem', $item);

    $item['permalink'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($item['permalink'])));
    $item['author'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($item['author'])));
    $item['title'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($item['title'])));
    $item['description'] = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct($item['description']), 65535));
    $tagString = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct(implode(', ', $item['tags']))));
    $enclosureString = POD::escapeString(Utils_Unicode::lessenAsEncoding(Utils_Unicode::correct(implode('|', $item['enclosures']))));

    if ($item['written'] > gmmktime() + 86400) {
        return false;
    }
    $deadLine = 0;
    $feedlife = POD::queryCell("SELECT feedlife FROM {$database['prefix']}FeedSettings");
    if ($feedlife > 0) {
        $deadLine = gmmktime() - $feedlife * 86400;
    }
    if ($id = POD::queryCell("SELECT id FROM {$database['prefix']}FeedItems WHERE permalink='{$item['permalink']}'") && $item['written'] != 0) {
        $result = POD::query("UPDATE {$database['prefix']}FeedItems SET author = '{$item['author']}', title = '{$item['title']}', description = '{$item['description']}', tags = '$tagString', enclosure = '$enclosureString', written = {$item['written']} WHERE id = $id");
        /*
        TODO : 읽은글이 읽지않은 글로 표시되는 문제 원인이 찾아질때 까지 막아둠
        if (POD::num_rows($result) > 0)
            POD::query("DELETE FROM {$database['prefix']}FeedReads WHERE item = $id");
        */
    } else {
        if ($id != null) {
            return false;
        } else {
            if ($item['written'] == 0) {
                $item['written'] = gmmktime();
            }
            if ($item['written'] > $deadLine) {
                $id = POD::queryCell("SELECT max(id) FROM {$database['prefix']}FeedItems");
                if (!$id) {
                    $id = 0;
                }
                $id++;

                POD::query("INSERT INTO {$database['prefix']}FeedItems VALUES($id, $feedId, '{$item['author']}', '{$item['permalink']}', '{$item['title']}', '{$item['description']}', '$tagString', '$enclosureString', {$item['written']})");
            }
        }
    }
    return true;
}

function editFeed($blogid, $feedId, $oldGroupId, $newGroupId, $url) {
    global $database;
    POD::query("UPDATE {$database['prefix']}FeedGroupRelations SET groupid = $newGroupId WHERE blogid = $blogid AND feed = $feedId AND groupid = $oldGroupId");
    return 0;
}

function deleteFeed($blogid, $feedId) {
    global $database;
    POD::query("DELETE FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid AND feed = $feedId");
    if (POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid AND feed = $feedId") == 0) {
        foreach (POD::queryAll("SELECT item FROM {$database['prefix']}FeedStarred s, {$database['prefix']}FeedItems i WHERE s.item = i.id AND s.blogid = $blogid AND i.feed = $feedId") as $row)
            POD::query("DELETE FROM {$database['prefix']}FeedStarred WHERE blogid = $blogid AND item = {$row['item']}");
        foreach (POD::queryAll("SELECT item FROM {$database['prefix']}FeedReads r, {$database['prefix']}FeedItems i WHERE r.item = i.id AND r.blogid = $blogid AND i.feed = $feedId") as $row)
            POD::query("DELETE FROM {$database['prefix']}FeedReads WHERE blogid = $blogid AND item = {$row['item']}");
    }
    if (POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}FeedGroupRelations WHERE feed = $feedId") == 0) {
        POD::query("DELETE FROM {$database['prefix']}FeedItems WHERE feed = $feedId");
        POD::query("DELETE FROM {$database['prefix']}Feeds WHERE id = $feedId");
    }
    return 0;
}

function deleteReaderTablesByOwner($blogid) {
    global $database;
    POD::query("DELETE FROM {$database['prefix']}FeedGroups WHERE blogid = $blogid");
    POD::query("DELETE FROM {$database['prefix']}FeedSettings WHERE blogid = $blogid");
    if ($result = POD::queryAll("SELECT feed FROM {$database['prefix']}FeedGroupRelations WHERE blogid = $blogid")) {
        while (list($feed) = array_shift($result)) {
            deleteFeed($blogid, $feed);
        }
    }
    return 0;
}

function updateRandomFeed() {
    global $database;
    $updatecycle = POD::queryCell("SELECT updatecycle FROM {$database['prefix']}FeedSettings LIMIT 1");
    if ($updatecycle != 0) {
        if ($feed = POD::queryRow("SELECT * FROM {$database['prefix']}Feeds WHERE modified < " . (gmmktime() - ($updatecycle * 60)) . " ORDER BY RAND() LIMIT 1")) {
            Setting::setServiceSetting('lastFeedUpdate', gmmktime(),true);
            return array(updateFeed($feed), $feed['xmlurl']);
        }
    }
    return array(1, 'No feeds to update');
}

function updateFeed($feedRow) {
    global $database;
    if ($feedRow['modified'] > gmmktime() - 300) {
        return true;
    }
    list($status, $feed, $xml) = getRemoteFeed($feedRow['xmlurl']);
    if ($status > 0) {
        POD::execute("UPDATE {$database['prefix']}Feeds SET modified = 0 WHERE xmlurl = '{$feedRow['xmlurl']}'");
        return $status;
    } else {
        POD::execute("UPDATE {$database['prefix']}Feeds SET blogURL = '{$feed['blogURL']}', title = '{$feed['title']}', description = '{$feed['description']}', language = '{$feed['language']}', modified = " . gmmktime() . " WHERE xmlurl = '{$feedRow['xmlurl']}'");
        return saveFeedItems($feedRow['id'], $xml) ? 0 : 1;
    }
}

function parseDate($str) {
    if (preg_match('/^(\d{4})년 (\d{2})월 (\d{2})일  (\d{2}):(\d{2}):(\d{2})$/', $str, $matches)) {
        return parseDate("{$matches[1]}-{$matches[2]}-{$matches[3]} {$matches[4]}:{$matches[5]}:{$matches[6]}");
    }
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2})$/', $str, $matches)) {
        return parseDate("{$matches[3]}-{$matches[1]}-{$matches[2]} {$matches[4]}:{$matches[5]}:00}");
    }
    if (empty($str)) {
        return 0;
    }
    $time = strtotime($str);
    if ($time !== -1) {
        return $time;
    }
    $gmt = (substr($str, strpos($str, "GMT")) == "GMT") ? 9 : 0;
    $str = str_replace("년 ", "-", $str);
    $str = str_replace("월 ", "-", $str);
    $str = str_replace("일 ", "", $str);
    $str = str_replace("GMT", "", $str);
    $str = str_replace("KST", "+0900", $str);
    if (strpos($str, "T")) {
        list($date, $time) = explode("T", $str);
        list($y, $m, $d) = explode("-", $date);
        list($time) = explode("+", $time);
        @list($h, $i, $s) = explode(":", $time);
    } else {
        if (strpos($str, ":") && strpos($str, "-")) {
            list($str) = explode(".", $str);
            list($date, $time) = explode(" ", $str);
            list($y, $m, $d) = explode("-", $date);
            if ($d > 1900) {
                $t = $y;
                $y = $d;
                $d = $m;
                $m = $t;
            }
            @list($h, $i, $s) = explode(":", $time);
        } else {
            if (strpos($str, ",") && strpos($str, ":")) {
                list($temp, $str) = explode(",", $str);
                $str = trim(str_month_check($str));
                list($d, $m, $y, $time) = explode(" ", $str);
                list($h, $i, $s) = explode(":", $time);
            } else {
                return gmmktime();
            }
        }
    }
    if (!$h) {
        $h = "00";
    }
    if (!$i) {
        $i = "00";
    }
    if (!$s) {
        $s = "00";
    }
    $h += $gmt;

    return mktime($h, $i, $s, $m, $d, $y);
}

function str_month_check($str) {
    $str = str_replace("Jan", "01", $str);
    $str = str_replace("Feb", "02", $str);
    $str = str_replace("Mar", "03", $str);
    $str = str_replace("Apr", "04", $str);
    $str = str_replace("May", "05", $str);
    $str = str_replace("Jun", "06", $str);
    $str = str_replace("Jul", "07", $str);
    $str = str_replace("Aug", "08", $str);
    $str = str_replace("Sep", "09", $str);
    $str = str_replace("Oct", "10", $str);
    $str = str_replace("Nov", "11", $str);
    return str_replace("Dec", "12", $str);
}

function importOPMLFromURL($blogid, $url) {
    global $database, $service;
    $request = new HTTPRequest($url);
    if (!$request->send()) {
        return array('error' => 1);
    }
    $result = importOPMLFromFile($blogid, $request->responseText);
    if ($result[0] == 0) {
        return array('error' => 0, 'total' => $result[1]['total'], 'success' => $result[1]['success']);
    } else {
        return array('error' => $result[0] + 1);
    }
}

function importOPMLFromFile($blogid, $xml) {
    global $database, $service;
    $xmls = new XMLStruct();
    if (!$xmls->open($xml, $service['encoding'])) {
        return array(1, null);
    }
    if ($xmls->getAttribute('/opml/body/outline', 'title')) {
        $result = array(0, 0);
        for ($i = 0; $xmls->getAttribute("/opml/body/outline[$i]", 'title'); $i++) {
            if ($xmls->getAttribute("/opml/body/outline[$i]", 'xmlUrl')) {
                $result[addFeed($blogid, $group = 0, $xmls->getAttribute("/opml/body/outline[$i]", 'xmlUrl'), false, $xmls->getAttribute("/opml/body/outline[$i]", 'htmlUrl'), $xmls->getAttribute("/opml/body/outline[$i]", 'title'), $xmls->getAttribute("/opml/body/outline[$i]", 'description'))] += 1;
            }
            for ($j = 0; $xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'title'); $j++)
                if ($xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'xmlUrl')) {
                    $result[addFeed($blogid, $group = 0, $xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'xmlUrl'), false, $xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'htmlUrl'), $xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'title'), $xmls->getAttribute("/opml/body/outline[$i]/outline[$j]", 'description'))] += 1;
                }
        }
    } else {
        return array(2, null);
    }
    return array(0, array('total' => array_sum($result), 'success' => $result[0]));
}

function adjustRelativePathImage($str, $permalink) {
    $link = parse_url($permalink);
    if (empty($link['scheme'])) {
        return $str;
    }
    $port = (empty($link['port']) || $link['port'] == 80) ? '' : ":{$link['port']}";
    $urls = array();
    preg_match_all('/<img[^>]+?src=("|\')?(.*?)("|\')/si', $str, $matches);
    foreach ($matches[2] as $src)
        array_push($urls, $src);
    foreach ($urls as $url) {
        if ($url && !preg_match('/^(http:|ftp:)/i', $url)) {
            $newSrc = ($url{0} == '/') ? $url : "/$url";
            $str = str_replace($url, "{$link['scheme']}://{$link['host']}$port$newSrc", $str);
        }
    }
    return $str;
}

?>
