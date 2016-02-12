<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/** Common remote response part */
function getRemoteResponsesWithPagingForOwner($blogid, $category, $site, $ip, $url, $search, $page, $count, $type = null, $filter_till = null) {
    $pool = DBModel::getInstance();

    $postfix = '';
    if ($category > 0) { // Perform before RemoteResponse pool call to prevent DBModel spoofing.
        $pool->init("Categories");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("parent", "eq", $category);
        $categories = $pool->getColumn("id");
        array_push($categories, $category);
    }

    $pool->init("RemoteResponses");
    $pool->setAlias("RemoteResponses", "t");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Entries", "left", array(array("t.blogid", "eq", "e.blogid"), array("t.entry", "eq", "e.id"), array("e.draft", "eq", 0)));
    $pool->join("Categories", "left", array(array("t.blogid", "eq", "c.blogid"), array("e.category", "eq", "c.id")));
    $pool->setQualifier("t.blogid", "eq", $blogid);
    if (!is_null($filter_till) && $filter_till >= 0) {
        $pool->setQualifier("t.isfiltered", ">", $filter_till);
    } else {
        $pool->setQualifier("t.isfiltered", "eq", 0);
    }
    if ($category > 0) {
        $pool->setQualifier("e.category", "hasoneof", $categories);
        $postfix .= '&amp;category=' . rawurlencode($category);
    } else {
        $pool->setQualifier("e.category", ">=", 0);
    }
    if (!empty($site)) {
        $pool->setQualifier("t.site", "eq", $site, true);
        $postfix .= '&amp;site=' . rawurlencode($site);
    }
    if (!empty($ip)) {
        $pool->setQualifier("t.ip", "eq", $ip, true);
        $postfix .= '&amp;ip=' . rawurlencode($ip);
    }
    if (!empty($url)) {
        $pool->setQualifier("t.url", "eq", $url, true);
        $postfix .= '&amp;url=' . rawurlencode($url);
    }
    if (!is_null($type)) {
        $pool->setQualifier("t.responsetype", "eq", $type, true);
    }

    if (!empty($search)) {
        $search = escapeSearchString($search);
        $pool->setQualifierSet(array("t.site", "like", $search, true), "OR", array("t.subject", "like", $search, true), "OR", array("t.excerpt", "like", $search, true));
        $postfix .= '&amp;search=' . rawurlencode($search);
    }
    $pool->setOrder("t.written", "desc");
    $pool->setProjection("t.*", "c.name AS categoryName");
    list($responses, $paging) = Paging::fetch($pool, $page, $count);
    if (strlen($postfix) > 0) {
        $paging['postfix'] .= $postfix . '&amp;withSearch=on';
    }
    return array($responses, $paging);
}

function getRemoteResponsesWithPaging($blogid, $entryId, $page, $count, $url = null, $prefix = '?page=', $postfix = '', $countItem = null, $type = 'trackback') {
    $pool = DBModel::getInstance();

    $pool->init("RemoteResponses");
    $pool->setAlias("RemoteResponses", "t");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Entries", "left", array(array("t.blogid", "eq", "e.blogid"), array("t.entry", "eq", "e.id"), array("e.draft", "eq", 0)));
    $pool->join("Categories", "left", array(array("t.blogid", "eq", "c.blogid"), array("e.category", "eq", "c.id")));
    $pool->setQualifier("t.blogid", "eq", $blogid);
    $pool->setQualifier("t.isfiltered", "eq", 0);


    if (!is_null($type)) {
        $pool->setQualifier("t.responsetype", "eq", $type, true);
    }
    if ($entryId != -1) {
        $pool->setQualifier("t.entry", "eq", $entryId);
    }
    $postfix = '';
    if (doesHaveOwnership()) {
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    $pool->setProjection("t.*", "c.name AS categoryName");
    $pool->setOrder("t.written", "desc");
    list($responses, $paging) = Paging::fetch($pool, $page, $count, $url, $prefix, $countItem);
    $paging['postfix'] .= $postfix;
    return array($responses, $paging);
}

function getRemoteResponseLogsWithPagingForOwner($blogid, $category, $site, $ip, $search, $page, $count, $type = null) {
    $pool = DBModel::getInstance();

    $postfix = '&amp;status=sent';

    if ($category > 0) { // Perform before RemoteResponse pool call to prevent DBModel spoofing.
        $pool->init("Categories");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("parent", "eq", $category);
        $categories = $pool->getColumn("id");
        array_push($categories, $category);
    }

    $pool->init("RemoteResponses");
    $pool->setAlias("RemoteResponses", "t");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Entries", "left", array(array("t.blogid", "eq", "e.blogid"), array("t.entry", "eq", "e.id"), array("e.draft", "eq", 0)));
    $pool->join("Categories", "left", array(array("t.blogid", "eq", "c.blogid"), array("e.category", "eq", "c.id")));
    $pool->setQualifier("t.blogid", "eq", $blogid);

    if ($category > 0) {
        $pool->setQualifier("e.category", "hasoneof", $categories);
        $postfix .= '&amp;category=' . rawurlencode($category);
    } else {
        $pool->setQualifier("e.category", ">=", 0);
    }
    if (!is_null($type)) {
        $pool->setQualifier("t.responsetype", "eq", $type, true);
    }

    if (!empty($search)) {
        $search = escapeSearchString($search);
        $pool->setQualifierSet(array("e.title", "like", $search, true), "OR", array("e.content", "like", $search, true));
        $postfix .= '&amp;search=' . rawurlencode($search);
    }

    $pool->setOrder("t.written", "desc");
    $pool->setProjection("t.*", "e.title AS subject", "c.name AS categoryName");

    list($responses, $paging) = Paging::fetch($pool, $page, $count);
    if (strlen($postfix) > 0) {
        $paging['postfix'] .= $postfix . '&amp;withSearch=on';
    }
    return array($responses, $paging);
}

function getRemoteResponses($entry, $type = null) {
    $pool = DBModel::getInstance();
    $pool->init("RemoteResponses");
    $pool->setQualifier("blogid", "eq", getBlogId());
    $pool->setQualifier("entry", "eq", $entry);
    $pool->setQualifier("isfiltered", "eq", 0);

    if (!is_null($type)) {
        $pool->setQualifier("responsetype", "eq", $type, true);
    }
    $pool->setOrder("written", "desc");
    $result = $pool->getAll();
    if (!empty($result)) {
        return $result;
    } else {
        return array();
    }
}

function getRemoteResponseList($blogid, $search, $type = null) {
    $pool = DBModel::getInstance();

    $list = array('title' => "$search", 'items' => array());

    $pool->init("RemoteResponses");
    $pool->setAlias("RemoteResponses", "t");
    $pool->setAlias("Entries", "e");
    $pool->join("Entries", "left", array(array("t.entry", "eq", "e.id"), array("t.blogid", "eq", "e.blogid"), array("e.draft", "eq", 0)));
    $pool->setQualifier("t.blogid", "eq", $blogid);
    $pool->setQualifier("t.isfiltered", "eq", 0);
    $pool->setQualifier("t.entry", ">", 0);
    if (doesHaveOwnership()) {
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (!is_null($type)) {
        $pool->setQualifier("responsetype", "eq", $type, true);
    }
    if (!empty($search)) {
        $search = escapeSearchString($search);
        $pool->setQualifierSet(array("t.excerpt", "like", $search, true), "OR", array("t.subject", "like", $search, true));
    }
    if ($result = $pool->getAll("t.id, t.entry, t.url, t.site, t.subject, t.excerpt, t.written, e.slogan")) {
        foreach ($result as $response)
            array_push($list['items'], $response);
    }
    return $list;
}

function getRecentRemoteResponses($blogid, $count = false, $guestShip = false, $type = null) {
    $pool = DBModel::getInstance();
    $context = Model_Context::getInstance();
    $pool->init("RemoteResponses");
    $pool->setAlias("RemoteResponses", "t");
    $pool->setAlias("Entries", "e");
    if (!is_null($type)) {
        $pool->setQualifier("t.responsetype", "eq", $type, true);
    }
    $pool->setQualifier("t.blogid", "eq", $blogid);
    $pool->setQualifier("t.isfiltered", "eq", 0);

    if (doesHaveOwnership() && !$guestShip) {
        $pool->join("Entries", "left", array(array("t.entry", "eq", "e.id"), array("t.blogid", "eq", "e.blogid"), array("e.draft", "eq", 0)));

    } else {
        $pool->join("Entries", "left", array(array("t.entry", "eq", "e.id"), array("t.blogid", "eq", "e.blogid")));
        $pool->setQualifier("e.draft", "eq", 0);
        $pool->setQualifier("e.visibility", ">=", 2);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    $pool->setOrder("t.written", "desc");
    $pool->setLimit(($count != false ? $count : $context->getProperty('skin.trackbacksOnRecent')));
    if ($result = $pool->getAll('*', array("useDBcache" => true, "cachePrefix" => 'remoteResponse'))) {
        return $result;
    } else {
        return array();
    }
}

function deleteRemoteResponse($blogid, $id) {
    $pool = DBModel::getInstance();
    importlib('model.blog.entry');
    if (!is_numeric($id)) {
        return null;
    }
    $pool->init("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $entry = $pool->getCell("entry");
    if ($entry === null) {
        return false;
    }
    if (!$pool->delete()) {
        return false;
    }
    CacheControl::flushDBCache('trackback');
    CacheControl::flushDBCache('remoteResponse');
    if (updateRemoteResponsesOfEntry($blogid, $entry)) {
        return $entry;
    }
    return false;
}

function trashRemoteResponse($blogid, $id) {
    $pool = DBModel::getInstance();
    importlib('model.blog.entry');
    if (!is_numeric($id)) {
        return null;
    }
    $pool->init("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $entry = $pool->getCell("entry");

    if ($entry === null) {
        return false;
    }
    $pool->setAttribute("isfiltered", Timestamp::getUNIXtime());
    if (!$pool->update()) {
        return false;
    }
    CacheControl::flushDBCache('trackback');
    CacheControl::flushDBCache('remoteResponse');
    if (updateRemoteResponsesOfEntry($blogid, $entry)) {
        return $entry;
    }
    return false;
}

function trashRemoteResponsesByIP($blogid, $ip) {
    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("ip", "eq", $ip, true);
    $affectedEntries = $pool->getColumn("entry");
    $pool->reset("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("ip", "eq", $ip, true);
    $pool->setAttribute("isfiltered", Timestamp::getUNIXtime());
    if ($pool->update()) {
        CacheControl::flushDBCache('trackback');
        CacheControl::flushDBCache('remoteResponse');
        foreach ($affectedEntries as $entry) {
            updateRemoteResponsesOfEntry($blogid, $entry);
        }
        return true;
    }
    return false;
}

function revertRemoteResponse($blogid, $id) {
    if (!is_numeric($id)) {
        return null;
    }
    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $entry = $pool->getCell("entry");

    if ($entry === null) {
        return false;
    }

    $pool->setAttribute("isfiltered", 0);
    if (!$pool->update()) {
        return false;
    }
    CacheControl::flushDBCache('trackback');
    CacheControl::flushDBCache('remoteResponse');
    importlib('model.blog.entry');
    if (updateRemoteResponsesOfEntry($blogid, $entry)) {
        return $entry;
    }
    return false;
}

function getRemoteResponseLog($blogid, $entryId, $type = null) {
    $pool = getRemoteResponseLogModel($blogid, $entryId, $type);
    $result = $pool->getAll();
    $str = '';
    if (!empty($result)) {
        foreach ($result as $row) {
            $str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
        }
    }
    return $str;
}

function getRemoteResponseLogs($blogid, $entryId, $type = null) {
    $pool = getRemoteResponseLogModel($blogid, $entryId, $type);
    $result = $pool->getAll();
    $logs = array();
    if (!empty($result)) {
        $logs = $result;
    }
    return $logs;
}

function getRemoteResponseLogModel($blogid, $entry, $type = null) {
    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponseLogs");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("entry", "eq", $entry);

    if ($type !== null) {
        $pool->setQualifier("responsetype", "eq", $type, true);
    }
    return $pool;
}

function deleteRemoteResponseLog($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponseLogs");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $result = $pool->delete(null, 'count');
    return ($result == 1) ? true : false;
}

function getRemoteResponseCount($blogid, $entryId = null, $type = null) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier("draft", "eq", 0);
    if (is_null($entryId)) {
		$result = $pool->getRow("SUM(trackbacks) AS t, SUM(pingbacks) AS p");
		if (!is_null($type)) {
			switch($type) {
				case 'trackback': return $result['t'];
				case 'pingback': return $result['p'];
			}
		} 
		return $result['t'] + $result['p'];
    } else {
        $pool->setQualifier('id', 'eq', $entryId);
		$result = $pool->getRow('trackbacks, pingbacks');
		if (!is_null($type)) {
			switch($type) {
				case 'trackback': return $result['trackbacks'];
				case 'pingback': return $result['pingbacks'];
			}
		} 
        return $result['trackbacks'] + $result['pingbacks'];
    }
}

function lastIndexOf($string, $item) {
    $index = strpos(strrev($string), strrev($item));
    if ($index) {
        $index = strlen($string) - strlen($item) - $index;
        return $index;
    } else {
        return -1;
    }
}

/** Trackback specific part */

function getTrackbacks($entry) {
    return getRemoteResponses($entry, 'trackback');
}

function getTrackbackList($blogid, $search) {
    return getRemoteResponseList($blogid, $search, 'trackback');
}

function getRecentTrackbacks($blogid, $count = false, $guestShip = false) {
    return getRecentRemoteResponses($blogid, $count, $guestShip, 'trackback');
}

function sendTrackbackPing($entryId, $permalink, $url, $site, $title) {
    $rpc = new XMLRPC();
    $rpc->url = TEXTCUBE_SYNC_URL;
    $summary = array(
        'permalink' => $permalink,
        'url' => $url,
        'blogName' => $site,
        'title' => $title
    );
    $rpc->async = true;
    $rpc->call('sync.trackback', $summary);
}

function receiveTrackback($blogid, $entry, $title, $url, $excerpt, $site) {
    if (empty($url)) {
        return 5;
    }
    $post = new Post;
    if (!$post->doesAcceptTrackback($entry)) {
        return 3;
    }

    $filtered = 0;

    if (!Filter::isAllowed($url)) {
        if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $url)) {
            $filtered = 1;
        } else {
            if (Filter::isFiltered('content', $excerpt)) {
                $filtered = 1;
            } else {
                if (!fireEvent('AddingTrackback', true, array('entry' => $entry, 'url' => $url, 'site' => $site, 'title' => $title, 'excerpt' => $excerpt))) {
                    $filtered = 1;
                }
            }
        }
    }

    $title = correctTTForXmlText($title);
    $excerpt = correctTTForXmlText($excerpt);

    $url = Utils_Unicode::lessenAsEncoding($url);
    $site = Utils_Unicode::lessenAsEncoding($site);
    $title = Utils_Unicode::lessenAsEncoding($title);
    $excerpt = Utils_Unicode::lessenAsEncoding($excerpt);

    $trackback = new Trackback();
    $trackback->entry = $entry;
    $trackback->url = $url;
    $trackback->site = $site;
    $trackback->title = $title;
    $trackback->excerpt = $excerpt;
    if ($filtered > 0) {
        $trackback->isfiltered = true;
    }
    if ($trackback->add()) {
        if ($filtered == 0) {
            CacheControl::flushDBCache('trackback');
        }
        return ($filtered == 0) ? 0 : 3;
    } else {
        return 4;
    }
    return 0;
}

function deleteTrackback($blogid, $id) {
    return deleteRemoteResponse($blogid, $id);
}

function trashTrackback($blogid, $id) {
    return trashRemoteResponse($blogid, $id);
}

function revertTrackback($blogid, $id) {
    return revertRemoteResponse($blogid, $id);
}

function sendTrackback($blogid, $entryId, $url) {
    importlib('model.blog.entry');
    importlib('model.blog.keyword');
    $context = Model_Context::getInstance();
    $entry = getEntry($blogid, $entryId);
    if (is_null($entry)) {
        return false;
    }
    $link = $context->getProperty('uri.default') . "/" . $entryId;
    $title = htmlspecialchars($entry['title']);
    $entry['content'] = getEntryContentView($blogid, $entryId, $entry['content'], $entry['contentformatter'], getKeywordNames($blogid));
    $excerpt = str_tag_on(Utils_Unicode::lessen(removeAllTags(stripHTML($entry['content'])), 255));
    $blogTitle = $context->getProperty('blog.title');
    $isNeedConvert =
        strpos($url, '/rserver.php?') !== false // 구버전 태터
        || strpos($url, 'blog.naver.com/tb') !== false // 네이버 블로그
        || strpos($url, 'news.naver.com/tb/') !== false // 네이버 뉴스
        || strpos($url, 'blog.empas.com') !== false // 엠파스 블로그
        || strpos($url, 'blog.yahoo.com') !== false // 야후 블로그
        || strpos($url, 'www.blogin.com/tb/') !== false // 블로긴
        || strpos($url, 'cytb.cyworld.nate.com') !== false // 싸이 페이퍼
        || strpos($url, 'www.cine21.com/Movies/tb.php') !== false // cine21
    ;
    if ($isNeedConvert) {
        $title = Utils_Unicode::convert($title, 'EUC-KR');
        $excerpt = Utils_Unicode::convert($excerpt, 'EUC-KR');
        $blogTitle = Utils_Unicode::convert($blogTitle, 'EUC-KR');
        $content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
        $request = new HTTPRequest('POST', $url);
        $request->contentType = 'application/x-www-form-urlencoded; charset=euc-kr';
        $isSuccess = $request->send($content);
    } else {
        $content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
        $request = new HTTPRequest('POST', $url);
        $request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
        $isSuccess = $request->send($content);
    }

    if ($isSuccess && (checkResponseXML($request->responseText) === 0)) {
        $trackbacklog = new TrackbackLog;
        $trackbacklog->entry = $entryId;
        $trackbacklog->url = Utils_Unicode::lessenAsEncoding($url, 255);
        $trackbacklog->add();
        return true;
    }
    return false;
}

function getTrackbackLog($blogid, $entry) {
    return getRemoteResponseLog($blogid, $entry, 'trackback');
}

function getTrackbackLogs($blogid, $entryId) {
    return getRemoteResponseLogs($blogid, $entryId, 'trackback');
}

function deleteTrackbackLog($blogid, $id) {
    return deleteRemoteResponseLog($blogid, $id);
}

function getTrackbackCount($blogid, $entryId = null) {
    return getRemoteResponseCount($blogid, $entryId, 'trackback');
}


function getTrackbackCountPart($trackbackCount, &$skin) {
    $noneTrackbackMessage = $skin->noneTrackbackMessage;
    $singleTrackbackMessage = $skin->singleTrackbackMessage;

    if ($trackbackCount == 0 && !empty($noneTrackbackMessage)) {
        dress('article_rep_tb_cnt', 0, $noneTrackbackMessage);
        $trackbackView = $noneTrackbackMessage;
    } else {
        if ($trackbackCount == 1 && !empty($singleTrackbackMessage)) {
            dress('article_rep_tb_cnt', 1, $singleTrackbackMessage);
            $trackbackView = $singleTrackbackMessage;
        } else {
            $trackbackPart = $skin->trackbackCount;
            dress('article_rep_tb_cnt', $trackbackCount, $trackbackPart);
            $trackbackView = $trackbackPart;
        }
    }

    return array("tb_count", $trackbackView);
}

/** Remote analysis part */
function getInfoFromTrackbackURL($url) {
    $url = trim($url);
    $result = array('service' => 'etc', 'url' => null, 'trackbackURL' => $url);

    $pieces = @parse_url($url);
    if ($pieces === false) {
        return $result;
    }
    $pieces['host'] = strtolower($pieces['host']);
    $pieces['path'] = rtrim($pieces['path'], '/');

    $host = $pieces['scheme'] . '://' . $pieces['host'];

    switch ($pieces['host']) {
        case 'blog.naver.com':
            $result['service'] = 'naver';
            if (strpos(strtolower($url), 'http://blog.naver.com/tb/') === 0) {
                $result['url'] = 'http://blog.naver.com/' . substr($url, 25);
            }
            break;
        case 'kr.blog.yahoo.com':
            $result['service'] == 'yahoo';
            if (preg_match('!^(.+)/trackback/[0-9]+/([0-9]+)$!i', $pieces['path'], $match)) {
                $result['url'] = $host . $match[1] . '/' . $match[2];
            }
            break;
        case 'blog.daum.net':
        case 'blog.empas.com':
            $result['service'] = ($pieces['host'] == 'blog.daum.net' ? 'daum' : 'empas');
            if (preg_match('!^(.+)/tb/(.+)$!i', $pieces['path'], $match)) {
                $result['url'] = $host . $match[1] . '/' . $match[2];
            }
            break;
        case 'cyhome.cyworld.com':
            $result['service'] = 'cyworld blog';
            $result['url'] = null;
            break;
        case 'cytb.cyworld.com':
            $result['service'] = 'cyworld paper';
            $result['url'] = 'http://paper.cyworld.com' . $pieces['path'];
            break;
        case 'blog.paran.com':
            $result['service'] = 'paran';
            $result['url'] = null;
            break;
        case 'blog.jinbo.net':
            $result['service'] = 'jinbo.net';
            if (substr($pieces['path'], -14) == '/trackback.php') {
                $result['url'] = $host . substr($pieces['path'], 0, -13) . '?' . $pieces['query'];
            }
            break;
        case 'blog.dreamwiz.com':
            $result['service'] = 'dreamwiz';
            $result['url'] = $result['trackbackURL'];
            break;
        case 'blog.hani.co.kr':
            $result['service'] = 'hani';
            if (preg_match('!^(.+)/tb/(.+)$!i', $pieces['path'], $match)) {
                $result['url'] = $host . $match[1] . '/' . $match[2];
            }
            break;
        case 'blog.ohmynews.com':
            $result['service'] = 'ohmynews';
            if (preg_match('!^(.+)/rmfdurrl/(.+)$!i', $pieces['path'], $match)) {
                $result['url'] = $host . $match[1] . '/' . $match[2];
            }
            break;
        case 'blog.joins.com':
            $result['service'] = 'joins';
            $result['url'] = $result['trackbackURL'];
            break;
        case 'blog.aladdin.co.kr':
            $result['service'] = 'aladdin';
            if (strpos(strtolower($pieces['path']), '/trackback/') === 0) {
                $result['url'] = $host . '/' . substr($pieces['path'], 11);
            }
            break;
        default:
            if (substr($pieces['host'], -11) == '.egloos.com') {
                $result['service'] = 'egloos';
                if (strpos(strtolower($pieces['path']), '/tb/') === 0) {
                    $result['url'] = $host . '/' . substr($pieces['path'], 4);
                }
            } elseif (substr($pieces['host'], -16) == '.spaces.live.com') {
                $result['service'] = 'live.com';
                if (strtolower(substr($pieces['path'], -5)) == '.trak') {
                    $result['url'] = $host . substr($pieces['path'], 0, -5) . '.entry';
                }
            } elseif ($pieces['host'] == 'www.mediamob.co.kr' || $pieces['host'] == 'mediamob.co.kr') {
                $result['service'] = 'mediamob';
                if (strtolower(substr($pieces['path'], -8)) == '/tb.aspx') {
                    $result['url'] = $host . substr($pieces['path'], 0, -8) . '/Blog.aspx?' . $pieces['query'];
                }
            } elseif (strpos(strtolower($pieces['path']), '/rserver.php') === 0) {
                $result['service'] = 'tattertools';
                parse_str($pieces['query'], $query);
                if (isset($query['mode']) && $query['mode'] == 'tb' && isset($query['sl'])) {
                    $result['url'] = $host . substr($pieces['path'], 0, -12) . 'index.php?pl=' . $query['sl'];
                }
            } elseif (strpos(strtolower($pieces['path']), '/wp-trackback.php') === 0) {
                $result['service'] = 'wordpress';
                parse_str($pieces['query'], $query);
                if (isset($query['p'])) {
                    $result['url'] = $host . substr($pieces['path'], 0, -16) . '?p=' . $query['p'];
                }
            } elseif (preg_match('!^(.*)/trackback/([0-9]+)$!i', $pieces['path'], $match)) {
                $result['service'] = array('textcube', 'tistory', 'innori');
                $result['url'] = $host . $match[1] . '/' . $match[2];
            } elseif (preg_match('!^(.+)/trackback$!i', $pieces['path'], $match)) {
                $result['service'] = array('wordpress', 'textcube.com');
                $result['url'] = $host . $match[1];
            }
            break;
    }

    return $result;
}

function getInfoFromURL($url) {
    $url = trim($url);
    $result = array('service' => null);

    $pieces = @parse_url($url);
    if ($pieces === false) {
        return $result;
    }
    $pieces['host'] = strtolower($pieces['host']);
    $pieces['path'] = rtrim($pieces['path'], '/');

    $host = $pieces['scheme'] . '://' . $pieces['host'];

    switch ($pieces['host']) {
        case 'blog.naver.com':
            $result['service'] = 'naver';
            break;
        case 'kr.blog.yahoo.com':
            $result['service'] = 'yahoo';
            break;
        case 'blog.daum.net':
            $result['service'] = 'daum';
            break;
        case 'blog.empas.com':
            $result['service'] = 'empas';
            break;
        case 'cyworld.com':
        case 'www.cyworld.com':
            $result['service'] = 'cyworld blog';
            break;
        case 'paper.cyworld.com':
            $result['service'] = 'cyworld paper';
            break;
        case 'blog.paran.com':
            $result['service'] = 'paran';
            break;
        case 'blog.jinbo.net':
            $result['service'] = 'jinbo.net';
            break;
        case 'blog.dreamwiz.com':
            $result['service'] = 'dreamwiz';
            break;
        case 'blog.hani.co.kr':
            $result['service'] = 'hani';
            break;
        case 'blog.ohmynews.com':
            $result['service'] = 'ohmynews';
            break;
        case 'blog.joins.com':
            $result['service'] = 'joins';
            break;
        case 'blog.aladdin.co.kr':
            $result['service'] = 'aladdin';
            break;
        case 'mediamob.co.kr':
        case 'www.mediamob.co.kr':
            $result['service'] = 'mediamob';
            break;
        default:
            $chunk = array_reverse(explode('.', $pieces['host']));

            switch ($chunk[1] . '.' . $chunk[0]) {
                case 'egloos.com':
                    $result['service'] = 'egloos';
                    break;
                case 'textcube.com':
                    $result['service'] = 'textcube.com';
                    break;
                case 'tistory.com':
                    $result['service'] = 'tistory.com';
                    break;
                case 'blogspot.com':
                    $result['service'] = 'blogger.com';
                    break;
                case 'wordpress.com':
                    $result['service'] = 'wordpress.com';
                    break;
                case 'live.com':
                    if (count($chunk) > 4 && $chunk[2] == 'spaces') {
                        $result['service'] = 'live.com';
                    }
                    break;
                default:
                    $request = new HTTPRequest($url);
                    if ($request->send()) {
                        $html = $request->responseText;

                        if (strpos($html, '<meta name="generator" content="Textcube.com 2.0 Garnet" />') !== false) {
                            $result['service'] = 'textcube.com';
                        } elseif (strpos($html, '<meta name="generator" content="Blogger" />') !== false) {
                            $result['service'] = 'blogger.com';
                        } elseif (strpos($html, '<meta name="generator" content="WordPress.com" />') !== false) {
                            $result['service'] = 'wordpress.com';
                        } elseif (preg_match('@<!--\\s*Tistory [0-9]\\.[0-9]@', $html)) {
                            $result['service'] = 'tistory';
                        }
                    }
            }
            break;
    }

    return $result;
}

function getTrackbackURLFromInfo($url, $blogType) {
    $url = trim($url);

    switch ($blogType) {
        case 'naver':
            if (strpos(strtolower($url), 'http://blog.naver.com/') === 0) {
                return 'http://blog.naver.com/tb/' . substr($url, 22);
            }
            break;
        case 'yahoo':
            // TODO
            break;
        case 'daum':
        case 'empas':
        case 'hani':
            $pieces = explode('/', substr($url, 7), 3);

            if (count($pieces) >= 2) {
                return 'http://' . $pieces[0] . '/' . $pieces[1] . '/tb/' . implode('/', array_slice($pieces, 2));
            }
            break;
        case 'cyworld blog':
            // TODO
            break;
        case 'cyworld paper':
            if (strpos(strtolower($url), 'http://paper.cyworld.com/') === 0) {
                return 'http://cytb.cyworld.com/' . substr($url, 25);
            }
            break;
        case 'paran':
            // TODO
            break;
        case 'jinbo.net':
            $pieces = @parse_url($url);
            if ($pieces !== false) {
                return 'http://blog.jinbo.net/' . trim($pieces['path'], '/') . '/trackback.php?' . $pieces['query'];
            }
            break;
        case 'dreamwiz':
            return $url;
        case 'ohmynews':
            $pieces = explode('/', substr($url, 7), 3);

            if (count($pieces) >= 2) {
                return 'http://' . $pieces[0] . '/' . $pieces[1] . '/rmfdurrl/' . implode('/', array_slice($pieces, 2));
            }
            break;
        case 'joins':
            return $url;
        case 'aladdin':
            if (strpos(strtolower($url), 'http://blog.aladdin.co.kr/') === 0) {
                return 'http://blog.aladdin.co.kr/trackback/' . substr($url, 26);
            }
            break;
        case 'egloos':
            $position = strpos($url, '/', 7);
            if ($position !== false) {
                return substr($url, 0, $position) . '/tb/' . substr($url, $position + 1);
            }
            break;
        case 'live.com':
            $pieces = @parse_url($url);
            if ($pieces !== false && substr($pieces['path'], -6) == '.entry') {
                return 'http://' . $pieces['host'] . substr($pieces['path'], -6) . '.trak';
            }
            break;
        case 'mediamob':
            $pieces = @parse_url($url);
            if ($pieces !== false && substr($pieces['path'], -10) == '/Blog.aspx') {
                return 'http://' . $pieces['host'] . substr($pieces['path'], -10) . '/tb.aspx?' . $pieces['query'];
            }
            break;
        case 'tattertools':
            $pieces = @parse_url($url);
            if ($pieces !== false) {
                parse_str($pieces['query'], $query);
                if (isset($query['pl'])) {
                    if (substr($pieces['path'], -10) == '/index.php') {
                        $pieces['path'] = substr($pieces['path'], 0, -10);
                    }
                    return 'http://' . $pieces['host'] . rtrim($pieces['path']) . '/rserver.php?mode=tb&sl=' . $query['pl'];
                }
            }
            break;
        case 'wordpress':
            $pieces = @parse_url($url);
            if ($pieces !== false) {
                if ($pieces['p']) {
                    if (substr($pieces['path'], -10) == '/index.php') {
                        $pieces['path'] = substr($pieces['path'], 0, -10);
                    }
                    return 'http://' . $pieces['host'] . rtrim($pieces['path'], '/') . '/wp-trackback.php?' . $pieces['query'];
                } else {
                    return 'http://' . $pieces['host'] . rtrim($pieces['path'], '/') . '/trackback';
                }
            }
            break;
        case 'textcube':
        case 'tistory':
        case 'innori':
            $pieces = @parse_url($url);
            if ($pieces !== false) {
                $position = strrpos($pieces['path'], '/');
                if ($position !== false) {
                    return 'http://' . $pieces['host'] . substr($pieces['path'], 0, $position - 1) . '/trackback/' . substr($pieces['path'], $position + 1);
                }
            }
            break;
        case 'textcube.com':
            $pieces = @parse_url($url);
            if ($pieces !== false) {
                return 'http://' . $pieces['host'] . rtrim($pieces['path'], '/') . '/trackback';
            }
            break;
    }

    return false;
}

function getRDFfromURL($url) {
    $request = new HTTPRequest($url);

    if (!$request->send() || !$request->responseText) {
        return false;
    }

    if (!preg_match('!<rdf:RDF\s+([^>]+)>\s*(<rdf:Description\s+([^>]+)>)\s*</rdf:RDF>!s', $request->responseText, $match)) {
        return false;
    }

    if (class_exists('DOMDocument')) {
        $doc = DOMDocument::loadXML($match[0]);
        if (!$doc) {
            return false;
        }
        $desc = $doc->getElementsByTagNameNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Description');
        $desc = $desc->item(0);
        if ($desc === null) {
            return false;
        }
        return array(
            'title' => $desc->getAttributeNS('http://purl.org/dc/elements/1.1/', 'title'),
            'url' => $desc->getAttributeNS('http://purl.org/dc/elements/1.1/', 'identifier'),
            'trackbackURL' => $desc->getAttributeNS('http://madskills.com/public/xml/rss/module/trackback/', 'ping')
        );
    } else {
        preg_match_all('/(\S+?)=(["\']?)(.*?)\2/', $match[1], $attribs, PREG_SET_ORDER);
        $namespace = array('rdf' => 'rdf', 'dc' => 'dc', 'trackback' => 'trackback');

        foreach ($attribs as $attrib) {
            if (substr(strtolower($attrib[1]), 0, 6) == 'xmlns:') {
                $name = substr($attrib[1], 6);

                switch (trim($attrib[3])) {
                    case 'http://www.w3.org/1999/02/22-rdf-syntax-ns#':
                        $namespace['rdf'] = $name;
                        break;
                    case 'http://purl.org/dc/elements/1.1/':
                        $namespace['dc'] = $name;
                        break;
                    case 'http://madskills.com/public/xml/rss/module/trackback/':
                        $namespace['trackback'] = $name;
                        break;
                }
            }
        }

        preg_match_all('/(\S+?)=(["\']?)(.*?)\2/', $match[2], $attribs, PREG_SET_ORDER);
        $result = array('title' => null, 'url' => null, 'trackbackURL' => null);

        foreach ($attribs as $attrib) {
            switch ($attrib[1]) {
                case $namespace['dc'] . ':title':
                    $result['title'] = $attrib[3];
                    break;
                case $namespace['dc'] . ':identifier':
                    $result['url'] = $attrib[3];
                    break;
                case $namespace['trackback'] . ':ping':
                    $result['trackbackURL'] = $attrib[3];
            }
        }

        return (isset($result['trackbackURL'])) ? $result : false;
    }
}

/** ETC */
function getURLForFilter($value) {
    $value = rawurlencode($value);
    $value = str_replace('http://', '', $value);
    $value = str_replace('https://', '', $value);
    $lastSlashPos = lastIndexOf($value, '/');
    if ($lastSlashPos > -1) {
        $value = substr($value, 0, $lastSlashPos);
    }
    return $value;
}
