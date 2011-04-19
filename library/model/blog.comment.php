<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function doesHaveOpenIDPriv( & $comment )
{
	global $database;
	$blogid = getBlogId();
	$openid = Acl::getIdentity('openid');

	if( !$comment['secret'] || !$openid ) {
		return false;
	}
	if( $comment['openid'] == $openid ) {
		return true;
	}
	if( empty($comment['parent']) ) {
		return false;
	}
	$openid = POD::escapeString($openid);
	$row = POD::queryRow("SELECT * from {$database['prefix']}Comments ".
		"WHERE blogid = $blogid and id = {$comment['parent']} and openid='{$openid}'" );
	return !empty($row);
}

function decorateComment( & $comment )
{
	$authorized = doesHaveOwnership();
	$comment['hidden'] = false;
	$comment['name'] = htmlspecialchars($comment['name']);
	$comment['comment'] = htmlspecialchars($comment['comment']);
	if ($comment['secret'] == 1) {
		if($authorized) {
			$comment['comment'] = '<span class="hiddenCommentTag_content">' . _text('[비밀댓글]') . '</span> ' . $comment['comment'];
		} else {
			if( !doesHaveOpenIDPriv($comment) ) {
				$comment['hidden'] = true;
				$comment['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>';
				$comment['homepage'] = '';
				$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
			} else {
				$comment['name'] = '<span class="hiddenCommentTag_name">' . _text('비밀방문자') . '</span>'. $comment['name'];
			}
		}
	}
}

function getCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count, $isGuestbook = false) {
	global $database;

	$postfix = '';
	if(!$isGuestbook && !Acl::check("group.editors")) $userLimit = ' AND e.userid = '.getUserId();
	else $userLimit = '';
	$sql = "SELECT c.*, e.title, c2.name AS parentName
		FROM {$database['prefix']}Comments c
		LEFT JOIN {$database['prefix']}Entries e ON c.blogid = e.blogid AND c.entry = e.id AND e.draft = 0$userLimit
		LEFT JOIN {$database['prefix']}Comments c2 ON c.parent = c2.id AND c.blogid = c2.blogid
		WHERE c.blogid = $blogid AND c.isfiltered = 0";
	if ($category > 0) {
		$categories = POD::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&amp;category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($name)) {
		$sql .= ' AND c.name = \'' . POD::escapeString($name) . '\'';
		$postfix .= '&amp;name=' . rawurlencode($name);
	}
	if (!empty($ip)) {
		$sql .= ' AND c.ip = \'' . POD::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (c.name LIKE '%$search%' OR c.homepage LIKE '%$search%' OR c.comment LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}

	$sql .= ' ORDER BY c.written DESC';
	list($comments, $paging) = Paging::fetch($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$postfix .= '&amp;withSearch=on';
		$paging['postfix'] .= $postfix;
	}

	return array($comments, $paging);
}

function getGuestbookWithPagingForOwner($blogid, $name, $ip, $search, $page, $count) {
	global $database;

	$postfix = '&amp;status=guestbook';

	$sql = "SELECT c.*, c2.name AS parentName
		FROM {$database['prefix']}Comments c
		LEFT JOIN {$database['prefix']}Comments c2 ON c.parent = c2.id AND c.blogid = c2.blogid
		WHERE c.blogid = $blogid AND c.entry = 0 AND c.isfiltered = 0";
	if (!empty($name)) {
		$sql .= ' AND c.name = \'' . POD::escapeString($name) . '\'';
		$postfix .= '&amp;name=' . rawurlencode($name);
	}
	if (!empty($ip)) {
		$sql .= ' AND c.ip = \'' . POD::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$sql .= " AND (c.name LIKE '%$search%' OR c.homepage LIKE '%$search%' OR c.comment LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}

	$sql .= ' ORDER BY c.written DESC';
	list($comments, $paging) = Paging::fetch($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$postfix .= '&amp;withSearch=on';
		$paging['postfix'] .= $postfix;
	}

	return array($comments, $paging);
}

function getCommentsNotifiedWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count) {
	global $database;
	$postfix = '';

	if (empty($name) && empty($ip) && empty($search)) {
		$sql = "SELECT
					c.*,
					csiteinfo.title AS siteTitle,
					csiteinfo.name AS nickname,
					csiteinfo.url AS siteUrl,
					csiteinfo.modified AS siteModified
				FROM
					{$database['prefix']}CommentsNotified c
				LEFT JOIN
						{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteid = csiteinfo.id
				WHERE c.blogid = $blogid AND (c.parent is null)";
		$sql .= ' ORDER BY c.modified DESC';
	} else {
		if (!empty($search)) {
			$search = escapeSearchString($search);
		}

		$preQuery = "SELECT parent FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND parent is NOT NULL";
		if (!empty($name))
			$preQuery .= ' AND name = \''. POD::escapeString($name) . '\' ';
		if (!empty($ip))
			$preQuery .= ' AND ip = \''. POD::escapeString($ip) . '\' ';
		if (!empty($search)) {
			$preQuery .= " AND ((name LIKE '%$search%') OR (homepage LIKE '%$search%') OR (comment LIKE '%$search%'))";
		}

		$childList = array_unique(POD::queryColumn($preQuery));
		$childListStr = (count($childList) == 0) ? '' : ('OR c.id IN ( ' . implode(', ',$childList) . ' ) ') ;

		$sql = "SELECT
				c.*,
				csiteinfo.title AS siteTitle,
				csiteinfo.name AS nickname,
				csiteinfo.url AS siteUrl,
				csiteinfo.modified AS siteModified
			FROM
				{$database['prefix']}CommentsNotified c
				LEFT JOIN
				{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteid = csiteinfo.id
			WHERE c.blogid = $blogid AND (c.parent is null) ";
		if (!empty($name)) {
			$sql .= ' AND ( c.name = \'' . POD::escapeString($name) . '\') ' ;
			$postfix .= '&amp;name=' . rawurlencode($name);
		}
		if (!empty($ip)) {
			$sql .= ' AND ( c.ip = \'' . POD::escapeString($ip) . '\') ';
			$postfix .= '&amp;ip=' . rawurlencode($ip);
		}
		if (!empty($search)) {
			$sql .= " AND ((c.name LIKE '%$search%') OR (c.homepage LIKE '%$search%') OR (c.comment LIKE '%$search%')) ";
			$postfix .= '&amp;search=' . rawurlencode($search);
		}
		$sql .= $childListStr . ' ORDER BY c.modified DESC';
	}

	list($comments, $paging) = Paging::fetch($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$postfix .= '&amp;withSearch=on';
		$paging['postfix'] .= $postfix;
	}

	return array($comments, $paging);
}

function getCommentCommentsNotified($parent) {
	global $database;
	$comments = array();
	$authorized = doesHaveOwnership();
	$sql = "SELECT
				c.*,
				csiteinfo.title AS siteTitle,
				csiteinfo.name AS nickname,
				csiteinfo.url AS siteUrl,
				csiteinfo.modified AS siteModified
			FROM
				{$database['prefix']}CommentsNotified c
				LEFT JOIN
				{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteid = csiteinfo.id
			WHERE c.blogid = ".getBlogId()." AND c.parent = $parent";
	$sql .= ' ORDER BY c.written ASC';
	if ($result = POD::queryAll($sql)) {
		foreach($result as $comment) {
			if (($comment['secret'] == 1) && !$authorized) {
				if( !doesHaveOpenIDPriv($comment) ) {
					$comment['name'] = '';
					$comment['homepage'] = '';
					$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
				}
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

function getCommentsWithPagingByEntryId($blogid, $entryId, $page, $count, $url = null, $prefix = '?page=', $postfix = '', $countItem = null, $order = 'ASC') {
	global $database;
	$comments = array();
	if($entryId != -1) {
		$filter = 'AND entry = '.$entryId;
	} else $filter = 'AND entry > 0';
	$sql = "SELECT * FROM {$database['prefix']}Comments
		WHERE blogid = $blogid $filter
			AND parent IS NULL
			AND isfiltered = 0
		ORDER BY written ".($order == 'DESC' ? "DESC" : "ASC");
	list($comments, $paging) = Paging::fetch($sql, $page, $count, $url, $prefix, $countItem);
	$paging['postfix'] = $postfix;
	$comments = coverComments($comments);
	return array($comments, $paging);
}

function getCommentsWithPaging($blogid, $page, $count, $url = null, $prefix = '?page=', $postfix = '', $countItem = null) {
	global $database;
	$comments = array();
	$sql = "SELECT r.*
		FROM
			{$database['prefix']}Comments r
			INNER JOIN {$database['prefix']}Entries e ON r.blogid = e.blogid AND r.entry = e.id AND e.draft = 0
			LEFT OUTER JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE
			r.blogid = $blogid AND e.draft = 0 AND r.parent IS NULL".(doesHaveOwnership() ? "" : " AND e.visibility >= 2").getPrivateCategoryExclusionQuery($blogid)."
			AND r.entry > 0 AND r.isfiltered = 0
		ORDER BY
			r.written DESC";
	list($comments, $paging) = Paging::fetch($sql, $page, $count, $url, $prefix, $countItem);
	$paging['postfix'] = $postfix;
	$comments = coverComments($comments);
	return array($comments, $paging);
}
function getCommentsWithPagingForGuestbook($blogid, $page, $count) {
	return getCommentsWithPagingByEntryId($blogid, 0, $page, $count, null, '?page=','',null, 'DESC');
}

function getCommentAttributes($blogid, $id, $attributeNames) {
	global $database;
	return POD::queryRow("SELECT $attributeNames FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
}

function getComments($entry,$order = 'ASC') {
	global $database;
	$comments = array();
	$aux = ($entry == 0 ? 'ORDER BY written DESC' : 'ORDER BY id '.($order == 'DESC' ? 'DESC' : 'ASC'));
	$sql = "SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId()."
			AND entry = $entry
			AND parent IS NULL
			AND isfiltered = 0 $aux";
	if ($result = POD::queryAll($sql)) {
		$comments = coverComments($result);
	}
	return $comments;
}

function coverComments($comments) {
	$result = array();
	$authorized = doesHaveOwnership();

	foreach ($comments as $comment) {
		if (($comment['secret'] == 1) && !$authorized) {
			if( !doesHaveOpenIDPriv($comment) ) {
				$comment['name'] = '';
				$comment['homepage'] = '';
				$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
			}
		}
		if(!empty($comment['replier'])) {
			$comment['homepage'] = User::getHomepage($comment['replier']);
		}
		array_push($result, $comment);
	}
	return $result;
}

function getCommentComments($parent,$parentComment=null) {
	global $database;
	$comments = array();
	$authorized = doesHaveOwnership();
	if ($result = POD::queryAll("SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId()."
			AND parent = $parent
			AND isfiltered = 0
		ORDER BY written")) {
		if( $parentComment == null ) {
			$parentComment = POD::queryRow(
				"SELECT * FROM {$database['prefix']}Comments ".
				"  WHERE blogid = ".getBlogId()." AND id = $parent" );
		}
		$parentByOpenid = !empty( $parentComment['openid'] );
		foreach ($result as $comment) {
			if (($comment['secret'] == 1) && !$authorized) {
				if( !doesHaveOpenIDPriv($comment) ) {
					$comment['name'] = '';
					$comment['homepage'] = '';
					$comment['comment'] =
						$parentByOpenid ?
							_text('비밀글의 작성자만 읽을 수 있는 댓글입니다.') :
							_text('관리자만 볼 수 있는 댓글입니다.');
				}
			}
			if(!empty($comment['replier'])) {
				$comment['homepage'] = User::getHomepage($comment['replier']);
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

function isCommentWriter($blogid, $commentid) {
	global $database;
	if (!doesHaveMembership())
		return false;
	return POD::queryExistence("SELECT replier
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND id = $commentid
			AND replier = " . getUserId());
}

function getComment($blogid, $id, $password, $restriction = true) {
	global $database;
	$sql = "SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND id = $id";
	if($restriction == true) {
		if (!doesHaveOwnership()) {
			if (doesHaveMembership())
				$sql .= ' AND replier = ' . getUserId();
			else
				$sql .= ' AND password = \'' . md5($password) . '\'';
		}
	}
	if ($result = POD::queryRow($sql)) {
		if($restriction != true) $result['password'] = null; // scope.
		return $result;
	}
	return false;
}

function getCommentList($blogid, $search) {
	global $database;
	$list = array('title' => "$search", 'items' => array());
	$search = escapeSearchString($search);
	$authorized = doesHaveOwnership() ? '' : 'AND c.secret = 0 '.getPrivateCategoryExclusionQuery($blogid);
	if ($result = POD::queryAll("SELECT c.id, c.entry, c.parent, c.name, c.comment, c.written, e.slogan
		FROM {$database['prefix']}Comments c
		INNER JOIN {$database['prefix']}Entries e ON c.entry = e.id AND c.blogid = e.blogid AND e.draft = 0
		WHERE c.entry > 0
			AND c.blogid = $blogid $authorized
			AND c.isfiltered = 0
			AND (c.comment like '%$search%' OR c.name like '%$search%')
		ORDER BY c.written")) {
		foreach ($result as $comment)
			array_push($list['items'], $comment);
	}
	return $list;
}

function updateCommentsOfEntry($blogid, $entryId) {
	global $database;
	requireComponent('Needlworks.Cache.PageCache');
	$commentCount = POD::queryCell("SELECT COUNT(*)
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND entry = $entryId
			AND isfiltered = 0");
	POD::query("UPDATE {$database['prefix']}Entries
		SET comments = $commentCount
		WHERE blogid = $blogid
			AND id = $entryId");
	if($entryId >=0) CacheControl::flushEntry($entryId);
	return $commentCount;
}

function sendCommentPing($entryId, $permalink, $name, $homepage) {
	return true;
	global $database, $blog;
	$blogid = getBlogId();
	if($slogan = POD::queryCell("SELECT slogan
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid
			AND id = $entryId
			AND draft = 0
			AND visibility = 3 
			AND acceptcomment = 1")) {
		$rpc = new XMLRPC();
		$rpc->url = TEXTCUBE_SYNC_URL;
		$summary = array(
			'permalink' => $permalink,
			'name' => $name,
			'homepage' => $homepage
		);
		$rpc->async = true;
		$rpc->call('sync.comment', $summary);
	}
}

function addComment($blogid, & $comment) {
	global $database, $user, $blog, $defaultURL;

	$openid = Acl::getIdentity('openid');
	$filtered = 0;

	if (!doesHaveOwnership()) {
		if (!Filter::isAllowed($comment['homepage'])) {
			if (Filter::isFiltered('ip', $comment['ip'])) {
				$blockType = "ip";
				$filtered = 1;
			} else if (Filter::isFiltered('name', $comment['name'])) {
				$blockType = "name";
				$filtered = 1;
			} else if (Filter::isFiltered('url', $comment['homepage'])) {
				$blockType = "homepage";
				$filtered = 1;
			} elseif (Filter::isFiltered('content', $comment['comment'])) {
				$blockType = "comment";
				$filtered = 1;
			} elseif ( !Acl::check( "group.writers" ) && !$openid &&
					Setting::getBlogSettingGlobal('AddCommentMode', '') == 'openid' ) {
				$blockType = "openidonly";
				$filtered = 1;
			} else if (!fireEvent('AddingComment', true, $comment)) {
				$blockType = "etc";
				$filtered = 1;
			}
		}
	}

	$comment['homepage'] = stripHTML($comment['homepage']);
	$comment['name'] = UTF8::lessenAsEncoding($comment['name'], 80);
	$comment['homepage'] = UTF8::lessenAsEncoding($comment['homepage'], 80);
	$comment['comment'] = UTF8::lessenAsEncoding($comment['comment'], 65535);

	if (!doesHaveOwnership() && $comment['entry'] != 0) {
		$result = POD::queryCount("SELECT *
			FROM {$database['prefix']}Entries
			WHERE blogid = $blogid
				AND id = {$comment['entry']}
				AND draft = 0
				AND visibility > 0
				AND acceptcomment = 1");
		if (!$result || $result == 0)
			return false;
	}
	$parent = $comment['parent'] == null ? 'null' : $comment['parent'];
	if ($user !== null) {
		$comment['replier'] = getUserId();
		$name = POD::escapeString($user['name']);
		$password = '';
		$homepage = POD::escapeString($user['homepage']);
		if( empty($homepage) && $openid ) { $homepage = POD::escapeString($openid); }
	} else {
		$comment['replier'] = 'null';
		$name = POD::escapeString($comment['name']);
		$password = empty($comment['password']) ? '' : md5($comment['password']);
		$homepage = POD::escapeString($comment['homepage']);
	}
	$comment0 = POD::escapeString($comment['comment']);
	$filteredAux = ($filtered == 1 ? "UNIX_TIMESTAMP()" : 0);
	$insertId = getCommentsMaxId() + 1;
	$result = POD::query("INSERT INTO {$database['prefix']}Comments
		(blogid,replier,id,openid,entry,parent,name,password,homepage,secret,comment,ip,written,isfiltered)
		VALUES (
			$blogid,
			{$comment['replier']},
			$insertId,
			'$openid',
			{$comment['entry']},
			$parent,
			'$name',
			'$password',
			'$homepage',
			{$comment['secret']},
			'$comment0',
			'{$comment['ip']}',
			UNIX_TIMESTAMP(),
			$filteredAux
		)");
	if ($result) {
		$id = $insertId;
		if($filtered != 1) {
			CacheControl::flushCommentRSS($comment['entry']);
			CacheControl::flushDBCache('comment');
			if ($parent != 'null' && $comment['secret'] < 1) {
				$insertId = getCommentsNotifiedQueueMaxId() + 1;
				POD::execute("INSERT INTO {$database['prefix']}CommentsNotifiedQueue
						( blogid , id, commentid , sendstatus , checkdate , written )
					VALUES
						('".$blogid."' , '".$insertId."', '" . $id . "', '0', '0', UNIX_TIMESTAMP())");
			}
			updateCommentsOfEntry($blogid, $comment['entry']);
			fireEvent($comment['entry'] ? 'AddComment' : 'AddGuestComment', $id, $comment);
			return $id;
		} else {
			return $blockType;
		}
	}
	return false;
}

function updateComment($blogid, $comment, $password) {
	global $database, $user;

	$openid = Acl::getIdentity('openid');
	if (!doesHaveOwnership()) {
		// if filtered, only block and not send to trash
		if (!Filter::isAllowed($comment['homepage'])) {
			if (Filter::isFiltered('ip', $comment['ip']))
				return 'blocked';
			if (Filter::isFiltered('name', $comment['name']))
				return 'blocked';
			if (Filter::isFiltered('url', $comment['homepage']))
				return 'blocked';
			if (Filter::isFiltered('content', $comment['comment']))
				return 'blocked';
			if (!fireEvent('ModifyingComment', true, $comment))
				return 'blocked';
		}
	}

	$comment['homepage'] = stripHTML($comment['homepage']);
	$comment['name'] = UTF8::lessenAsEncoding($comment['name'], 80);
	$comment['homepage'] = UTF8::lessenAsEncoding($comment['homepage'], 80);
	$comment['comment'] = UTF8::lessenAsEncoding($comment['comment'], 65535);

	$setPassword = '';
	if ($user !== null) {
		$comment['replier'] = getUserId();
		$name = POD::escapeString($user['name']);
		$setPassword = 'password = \'\',';
		$homepage = POD::escapeString($user['homepage']);
		if( empty($homepage) && $openid ) { $homepage = POD::escapeString($openid); }
	} else {
		$name = POD::escapeString($comment['name']);
		if ($comment['password'] !== true)
			$setPassword = 'password = \'' . (empty($comment['password']) ? '' : md5($comment['password'])) . '\', ';
		$homepage = POD::escapeString($comment['homepage']);
	}
	$comment0 = POD::escapeString($comment['comment']);

	$guestcomment = false;
	if (POD::queryExistence("SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND id = {$comment['id']}
			AND replier IS NULL")) {
		$guestcomment = true;
	}

	$wherePassword = '';
	if (!doesHaveOwnership()) {
		if ($guestcomment == false) {
			if (!doesHaveMembership())
				return false;
			$wherePassword = ' AND replier = ' . getUserId();
		}
		else
		{
			if( empty($password) && $openid ) {
				$wherePassword = ' AND openid = \'' . $openid . '\'';
			} else {
				$wherePassword = ' AND password = \'' . md5($password) . '\'';
			}
		}
	}

	$replier = is_null($comment['replier']) ? 'NULL' : "'{$comment['replier']}'";

	$result = POD::query("UPDATE {$database['prefix']}Comments
				SET
					name = '$name',
					$setPassword
					homepage = '$homepage',
					secret = {$comment['secret']},
					comment = '$comment0',
					ip = '{$comment['ip']}',
					written = UNIX_TIMESTAMP(),
					isfiltered = {$comment['isfiltered']},
					replier = {$replier}
				WHERE blogid = $blogid
					AND id = {$comment['id']} $wherePassword");
	if($result) {
		CacheControl::flushCommentRSS($comment['entry']); // Assume blogid = current blogid.
		CacheControl::flushDBCache('comment');
		return true;
	} else return false;
}

function deleteComment($blogid, $id, $entry, $password) {
	global $database;

	if (!is_numeric($id)) return false;
	if (!is_numeric($entry)) return false;

	$guestcomment = false;
	if (POD::queryExistence("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id AND replier IS NULL")) {
		$guestcomment = true;
	}

	$wherePassword = '';

	$sql = "DELETE FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND id = $id
			AND entry = $entry";
	if (!doesHaveOwnership()) {
		if( Acl::getIdentity('openid') && empty($password) ) {
			$wherePassword = ' AND openid = \'' . Acl::getIdentity('openid') . '\'';
		} else {
			if ($guestcomment == false) {
				if (!doesHaveMembership()) {
					return false;
				}
				$wherePassword = ' AND replier = ' . getUserId();
			}
			else
			{
				$wherePassword = ' AND password = \'' . md5($password) . '\'';
			}
		}
	}
	if(POD::queryCount($sql . $wherePassword)) {
		CacheControl::flushCommentRSS($entry);
		CacheControl::flushDBCache('comment');
		updateCommentsOfEntry($blogid, $entry);
		return true;
	}
	return false;
}

function trashComment($blogid, $id, $entry, $password) {
	global $database;
	if (!doesHaveOwnership()) {
		return false;
	}
	if (!is_numeric($id)) return false;
	if (!is_numeric($entry)) return false;
	$sql = "UPDATE {$database['prefix']}Comments
		SET isfiltered = UNIX_TIMESTAMP()
		WHERE blogid = $blogid
			AND id = $id
			AND entry = $entry";
	$affected = POD::queryCount($sql);
	$sql = "UPDATE {$database['prefix']}Comments
		SET isfiltered = UNIX_TIMESTAMP()
		WHERE blogid = $blogid
			AND parent = $id
			AND entry = $entry";
	$affectedChildren = POD::queryCount($sql);
	if ($affected + $affectedChildren > 0) {
		CacheControl::flushCommentRSS($entry);
		CacheControl::flushDBCache('comment');
		updateCommentsOfEntry($blogid, $entry);
		return true;
	}
	return false;
}

function revertComment($blogid, $id, $entry, $password) {
	// not used, so
	return false;
	global $database;
	if (!doesHaveOwnership()) {
		return false;
	}
	if (!is_numeric($id)) return false;
	if (!is_numeric($entry)) return false;
	$sql = "UPDATE {$database['prefix']}Comments
		SET isfiltered = 0
		WHERE blogid = $blogid
			AND id = $id
			AND entry = $entry";
	if(POD::query($sql)) {
		CacheControl::flushCommentRSS($entry);
		CacheControl::flushDBCache('comment');
		updateCommentsOfEntry($blogid, $entry);
		return true;
	}
	return false;
}

function getRecentComments($blogid,$count = false,$isGuestbook = false, $guestShip = false) {
	global $skinSetting, $database;
	$comments = array();
	if(!$isGuestbook && !Acl::check("group.editors")) $userLimit = ' AND e.userid = '.getUserId();
	else $userLimit = '';
	$sql = (doesHaveOwnership() && !$guestShip) ? "SELECT r.*, e.title, e.slogan
		FROM
			{$database['prefix']}Comments r
			INNER JOIN {$database['prefix']}Entries e ON r.blogid = e.blogid AND r.entry = e.id AND e.draft = 0$userLimit
		WHERE
			r.blogid = $blogid".($isGuestbook != false ? " AND r.entry=0" : " AND r.entry>0")." AND r.isfiltered = 0
		ORDER BY
			r.written
		DESC LIMIT ".($count != false ? $count : $skinSetting['commentsOnRecent']) :
		"SELECT r.*, e.title, e.slogan
		FROM
			{$database['prefix']}Comments r
			INNER JOIN {$database['prefix']}Entries e ON r.blogid = e.blogid AND r.entry = e.id AND e.draft = 0
			LEFT OUTER JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE
			r.blogid = $blogid AND e.draft = 0 AND e.visibility >= 2".getPrivateCategoryExclusionQuery($blogid)
			.($isGuestbook != false ? " AND r.entry = 0" : " AND r.entry > 0")." AND r.isfiltered = 0
		ORDER BY
			r.written
		DESC LIMIT ".($count != false ? $count : $skinSetting['commentsOnRecent']);
	if ($result = POD::queryAllWithDBCache($sql,'comment')) {
		foreach($result as $comment) {
			if (($comment['secret'] == 1) && !doesHaveOwnership()) {
				if( !doesHaveOpenIDPriv($comment) ) {
					$comment['name'] = _text('비밀방문자');
					$comment['homepage'] = '';
					$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
				}
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

function getRecentGuestbook($blogid,$count = false) {
	global $skinSetting, $database;
	$comments = array();
	$sql = "SELECT r.*
		FROM
			{$database['prefix']}Comments r
		WHERE
			r.blogid = $blogid AND r.entry = 0 AND r.isfiltered = 0
		ORDER BY
			r.written
		DESC LIMIT ".($count != false ? $count : $skinSetting['commentsOnRecent']);

	if ($result = POD::queryAll($sql)) {
		foreach($result as $comment) {
			if (($comment['secret'] == 1) && !doesHaveOwnership()) {
				if( !doesHaveOpenIDPriv($comment) ) {
					$comment['name'] = '';
					$comment['homepage'] = '';
					$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
				}
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}
function getGuestbookPageById($blogid, $id) {
	return getCommentPageById($blogid, 0, $id);
}

function getCommentPageById($blogid, $entryId, $commentId) {
	global $database, $skinSetting;
	$totalGuestbookId = POD::queryColumn("SELECT id
		FROM {$database['prefix']}Comments
		WHERE
			blogid = $blogid AND entry = $entryId AND isfiltered = 0 AND parent is null
		ORDER BY
			written DESC");
	$order = array_search($commentId, $totalGuestbookId);
	if($order == false) {
		$parentCommentId = POD::queryCell("SELECT parent
			FROM {$database['prefix']}Comments
			WHERE
				blogid = $blogid AND entry = $entryId AND isfiltered = 0 AND id = $commentId");
		if($parentCommentId != false) {
			$order = array_search($parentCommentId, $totalGuestbookId);
		} else {
			return false;
		}
	}
	$base = ($entryId == 0 ? $skinSetting['commentsOnGuestbook'] : $skinSetting['commentsOnEntry']);
	return intval($order / $base)+1;
}

function deleteCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = POD::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	if(POD::queryCount("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id") == 1) {
		if (POD::query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid AND parent = $id")) {
			CacheControl::flushCommentRSS($entryId);
			updateCommentsOfEntry($blogid, $entryId);
			return true;
		}
	}
	return false;
}

function trashCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = POD::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
//	$result = POD::queryCount("UPDATE {$database['prefix']}Comments SET isfiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id");
//	if ($result && $result == 1) {
	if(POD::query("UPDATE {$database['prefix']}Comments SET isfiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id")) {
		if (POD::query("UPDATE {$database['prefix']}Comments SET isfiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND parent = $id")) {
			CacheControl::flushCommentRSS($entryId);
			CacheControl::flushDBCache('comment');
			updateCommentsOfEntry($blogid, $entryId);
			return true;
		}
	}
	return false;
}

function revertCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = POD::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	$parent = POD::queryCell("SELECT parent FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	if(POD::queryCount("UPDATE {$database['prefix']}Comments SET isfiltered = 0 WHERE blogid = $blogid AND id = $id") == 1) {
		if (is_null($parent) || POD::query("UPDATE {$database['prefix']}Comments SET isfiltered = 0 WHERE blogid = $blogid AND id = $parent")) {
			CacheControl::flushCommentRSS($entryId);
			updateCommentsOfEntry($blogid, $entryId);
			return true;
		}
	}
	return false;
}

function deleteCommentNotifiedInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;

	fireEvent('DeleteCommentNotified', $id);

	$entryId = POD::queryCell("SELECT entry FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND id = $id");
	if(POD::queryCount("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND id = $id") == 1) {
		if (POD::query("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND parent = $id")) {
			updateCommentsOfEntry($blogid, $entryId);
			CacheControl::flushCommentNotifyRSS();
			return true;
		}
	}
	return false;
}

function notifyComment() {
	global $database, $service, $blog, $defaultURL;
	$blogid = getBlogId();
	$sql = "SELECT
				CN.*,
				CNQ.id AS queueId,
				CNQ.commentid AS commentid,
				CNQ.sendstatus AS sendstatus,
				CNQ.checkdate AS checkdate,
				CNQ.written  AS queueWritten
			FROM
				{$database['prefix']}CommentsNotifiedQueue AS CNQ
			LEFT JOIN
				{$database['prefix']}Comments AS CN ON CNQ.commentid = CN.id
			WHERE
				CNQ.sendstatus = 0
				and CN.parent is not null
			ORDER BY CNQ.id ASC LIMIT 1 OFFSET 0";
	$queue = POD::queryRow($sql);
	if (empty($queue) && empty($queue['queueId'])) {
		return false;
	}
	$comments = (POD::queryRow("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$queue['commentid']}"));
	if (empty($comments['parent']) || $comments['secret'] == 1) {
		POD::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
		return false;
	}
	$parentComments = (POD::queryRow("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$comments['parent']}"));
	if (empty($parentComments['homepage'])) {
		POD::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
		return false;
	}
	$entry = (POD::queryRow("SELECT * FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id={$comments['entry']}"));
	if(is_null($entry)) {
		$r1_comment_check_url = rawurlencode("$defaultURL/guestbook/".$parentComments['id']."#guestbook".$parentComments['id']);
		$r2_comment_check_url = rawurlencode("$defaultURL/guestbook/".$comments['id']."#guestbook".$comments['id']);
		$entry['title'] = _textf('%1 블로그의 방명록',$blog['title']);
		$entryPermaLink = "$defaultURL/guestbook/";
		$entry['id'] = 0;
	} else {
		$r1_comment_check_url = rawurlencode("$defaultURL/" . ($blog['useSloganOnPost'] ? "entry/{$entry['slogan']}" : $entry['id']) . "#comment" . $parentComments['id']);
		$r2_comment_check_url = rawurlencode("$defaultURL/" . ($blog['useSloganOnPost'] ? "entry/{$entry['slogan']}" : $entry['id']) . "#comment" . $comments['id']);
		$entryPermaLink = "$defaultURL/" . ($blog['useSloganOnPost'] ? "entry/{$entry['slogan']}" : $entry['id']);
	}

	$data = "url=" . rawurlencode($defaultURL) . "&mode=fb" . "&s_home_title=" . rawurlencode($blog['title']) . "&s_post_title=" . rawurlencode($entry['title']) . "&s_name=" . rawurlencode($comments['name']) . "&s_no=" . rawurlencode($comments['entry']) . "&s_url=" . rawurlencode($entryPermaLink) . "&r1_name=" . rawurlencode($parentComments['name']) . "&r1_no=" . rawurlencode($parentComments['id']) . "&r1_pno=" . rawurlencode($comments['entry']) . "&r1_rno=0" . "&r1_homepage=" . rawurlencode($parentComments['homepage']) . "&r1_regdate=" . rawurlencode($parentComments['written']) . "&r1_url=" . $r1_comment_check_url. "&r2_name=" . rawurlencode($comments['name']) . "&r2_no=" . rawurlencode($comments['id']) . "&r2_pno=" . rawurlencode($comments['entry']) . "&r2_rno=" . rawurlencode($comments['parent']) . "&r2_homepage=" . rawurlencode($comments['homepage']) . "&r2_regdate=" . rawurlencode($comments['written']) . "&r2_url=" . $r2_comment_check_url . "&r1_body=" . rawurlencode($parentComments['comment']) . "&r2_body=" . rawurlencode($comments['comment']);
	if (strpos($parentComments['homepage'], "http://") === false) {
		$homepage = 'http://' . $parentComments['homepage'];
	} else {
		$homepage = $parentComments['homepage'];
	}
	$request = new HTTPRequest('POST', $homepage);
	$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
	$request->content = $data;
	if ($request->send()) {
		$xmls = new XMLStruct();
		if ($xmls->open($request->responseText)) {
			$result = $xmls->selectNode('/response/error/');
			if ($result['.value'] != '1' && $result['.value'] != '0') {
				$homepage = rtrim($homepage, '/') . '/index.php';
				$request = new HTTPRequest('POST', $homepage);
				$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
				$request->content = $data;
				if ($request->send()) {
				}
			}
		}
	} else {
	}
	POD::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
}

function receiveNotifiedComment($post) {
	if (empty($post['mode']) || $post['mode'] != 'fb')
		return 1;
	global $database;

	CacheControl::flushCommentNotifyRSS();
	$post = fireEvent('ReceiveNotifiedComment', $post);
	if ($post === false) return 7;

	$blogid = getBlogId();
	$title = POD::escapeString(UTF8::lessenAsEncoding($post['s_home_title'], 255));
	$name = POD::escapeString(UTF8::lessenAsEncoding($post['s_name'], 255));
	$entryId = POD::escapeString($post['s_no']);
	$homepage = POD::escapeString(UTF8::lessenAsEncoding($post['url'], 255));
	$entryurl = POD::escapeString($post['s_url']);
	$entrytitle = POD::escapeString($post['s_post_title']);
	$parent_id = $post['r1_no'];
	$parent_name = POD::escapeString(UTF8::lessenAsEncoding($post['r1_name'], 80));
	$parent_parent = $post['r1_rno'];
	$parent_homepage = POD::escapeString(UTF8::lessenAsEncoding($post['r1_homepage'], 80));
	$parent_written = $post['r1_regdate'];
	$parent_comment = POD::escapeString($post['r1_body']);
	$parent_url = POD::escapeString(UTF8::lessenAsEncoding($post['r1_url'], 255));
	$child_id = $post['r2_no'];
	$child_name = POD::escapeString(UTF8::lessenAsEncoding($post['r2_name'], 80));
	$child_parent = $post['r2_rno'];
	$child_homepage = POD::escapeString(UTF8::lessenAsEncoding($post['r2_homepage'], 80));
	$child_written = $post['r2_regdate'];
	$child_comment = POD::escapeString($post['r2_body']);
	$child_url = POD::escapeString(UTF8::lessenAsEncoding($post['r2_url'],255));
	$siteid = POD::queryCell("SELECT id FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE url = '$homepage'");
	if (empty($siteid)) {
		$insertId = getCommentsNotifiedSiteInfoMaxId() + 1;
		if (POD::execute("INSERT INTO {$database['prefix']}CommentsNotifiedSiteInfo
			( id, title, name, url, modified)
			VALUES ($insertId, '$title', '$name', '$homepage', UNIX_TIMESTAMP());"))
			$siteid = $insertId;
		else
			return 2;
	}
	$parentId = POD::queryCell("SELECT id
		FROM {$database['prefix']}CommentsNotified
		WHERE entry = $entryId
			AND siteid = $siteid
			AND blogid = $blogid
			AND remoteid = $parent_id");
	if (empty($parentId)) {
		$insertId = getCommentsNotifiedMaxId() + 1;
		$sql = "INSERT INTO {$database['prefix']}CommentsNotified
			( blogid , replier , id , entry , parent , name , password , homepage , secret , comment , ip , written, modified , siteid , isnew , url , remoteid ,entrytitle , entryurl )
			VALUES (
				$blogid, NULL , $insertId, " . $entryId . ", " . (empty($parent_parent) ? 'null' : $parent_parent) . ", '" . $parent_name . "', '', '" . $parent_homepage . "', '', '" . $parent_comment . "', '', " . $parent_written . ",UNIX_TIMESTAMP(), " . $siteid . ", 1, '" . $parent_url . "'," . $parent_id . ", '" . $entrytitle . "', '" . $entryurl . "'
)";
		if (!POD::execute($sql))
			return 3;
		$parentId = $insertId;
	}
	if (POD::queryCell("SELECT count(*) FROM {$database['prefix']}CommentsNotified WHERE siteid=$siteid AND remoteid=$child_id") > 0)
		return 4;
	$insertId = getCommentsNotifiedMaxId() + 1;
	$sql = "INSERT INTO {$database['prefix']}CommentsNotified
		( blogid , replier , id , entry , parent , name , password , homepage , secret , comment , ip , written, modified , siteid , isnew , url , remoteid ,entrytitle , entryurl )
		VALUES (
			$blogid, NULL , $insertId, " . $entryId . ", $parentId, '$child_name', '', '$child_homepage', '', '$child_comment', '', $child_written, UNIX_TIMESTAMP(), $siteid, 1, '$child_url', $child_id, '$entrytitle', '$entryurl')";
	if (!POD::execute($sql))
		return 5;
	$sql = "UPDATE {$database['prefix']}CommentsNotified SET modified = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $parentId";
	if (!POD::execute($sql))
		return 6;
	return 0;
}

function getCommentCount($blogid, $entryId = null) {
	global $database;
	if (is_null($entryId))
		return POD::queryCell("SELECT SUM(comments) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft= 0 ");
	return POD::queryCell("SELECT comments FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
}

function getGuestbookCount($blogid) {
	global $database;
	return POD::queryCell("SELECT count(id) FROM {$database['prefix']}Comments WHERE blogid = $blogid AND entry = 0");
}

function getCommentCountPart($commentCount, &$skin) {
	$noneCommentMessage = $skin->noneCommentMessage;
	$singleCommentMessage = $skin->singleCommentMessage;

	if ($commentCount == 0 && !empty($noneCommentMessage)) {
		dress('article_rep_rp_cnt', 0, $noneCommentMessage);
		$commentView = $noneCommentMessage;
	} else if ($commentCount == 1 && !empty($singleCommentMessage)) {
		dress('article_rep_rp_cnt', 1, $singleCommentMessage);
		$commentView = $singleCommentMessage;
	} else {
		$commentPart = $skin->commentCount;
		dress('article_rep_rp_cnt', $commentCount, $commentPart);
		$commentView = $commentPart;
	}

	return array("rp_count", $commentView);
}

function getCommentsMaxId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id)
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedMaxId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotified
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedQueueMaxId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotifiedQueue
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedSiteInfoMaxId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotifiedSiteInfo");
	return empty($maxId) ? 0 : $maxId;
}

?>
