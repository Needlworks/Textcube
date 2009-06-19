<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

requireComponent( "Textcube.Control.Openid" );

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
	$openid = Data_IAdapter::escapeString($openid);
	$row = Data_IAdapter::queryRow("SELECT * from {$database['prefix']}Comments ".
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
	$sql = "SELECT c.*, e.title, c2.name parentName
		FROM {$database['prefix']}Comments c
		LEFT JOIN {$database['prefix']}Entries e ON c.blogid = e.blogid AND c.entry = e.id AND e.draft = 0$userLimit
		LEFT JOIN {$database['prefix']}Comments c2 ON c.parent = c2.id AND c.blogid = c2.blogid
		WHERE c.blogid = $blogid AND c.isFiltered = 0";
	if ($category > 0) {
		$categories = Data_IAdapter::queryColumn("SELECT id FROM {$database['prefix']}Categories WHERE parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
		$postfix .= '&amp;category=' . rawurlencode($category);
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($name)) {
		$sql .= ' AND c.name = \'' . Data_IAdapter::escapeString($name) . '\'';
		$postfix .= '&amp;name=' . rawurlencode($name);
	}
	if (!empty($ip)) {
		$sql .= ' AND c.ip = \'' . Data_IAdapter::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = Data_IAdapter::escapeSearchString($search);
		$sql .= " AND (c.name LIKE '%$search%' OR c.homepage LIKE '%$search%' OR c.comment LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}

	$sql .= ' ORDER BY c.written DESC';
	list($comments, $paging) = fetchWithPaging($sql, $page, $count);
	if (strlen($postfix) > 0) {
		$postfix .= '&amp;withSearch=on';
		$paging['postfix'] .= $postfix;
	}

	return array($comments, $paging);
}

function getGuestbookWithPagingForOwner($blogid, $name, $ip, $search, $page, $count) {
	global $database;

	$postfix = '&amp;status=guestbook';

	$sql = "SELECT c.*, c2.name parentName
		FROM {$database['prefix']}Comments c
		LEFT JOIN {$database['prefix']}Comments c2 ON c.parent = c2.id AND c.blogid = c2.blogid
		WHERE c.blogid = $blogid AND c.entry = 0 AND c.isFiltered = 0";
	if (!empty($name)) {
		$sql .= ' AND c.name = \'' . Data_IAdapter::escapeString($name) . '\'';
		$postfix .= '&amp;name=' . rawurlencode($name);
	}
	if (!empty($ip)) {
		$sql .= ' AND c.ip = \'' . Data_IAdapter::escapeString($ip) . '\'';
		$postfix .= '&amp;ip=' . rawurlencode($ip);
	}
	if (!empty($search)) {
		$search = Data_IAdapter::escapeSearchString($search);
		$sql .= " AND (c.name LIKE '%$search%' OR c.homepage LIKE '%$search%' OR c.comment LIKE '%$search%')";
		$postfix .= '&amp;search=' . rawurlencode($search);
	}

	$sql .= ' ORDER BY c.written DESC';
	list($comments, $paging) = fetchWithPaging($sql, $page, $count);
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
						{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteId = csiteinfo.id
				WHERE c.blogid = $blogid AND (c.parent is null)";
		$sql .= ' ORDER BY c.modified DESC';
	} else {
		if (!empty($search)) {
			$search = Data_IAdapter::escapeSearchString($search);
		}

		$preQuery = "SELECT parent FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND parent is NOT NULL";
		if (!empty($name))
			$preQuery .= ' AND name = \''. Data_IAdapter::escapeString($name) . '\' ';
		if (!empty($ip))
			$preQuery .= ' AND ip = \''. Data_IAdapter::escapeString($ip) . '\' ';
		if (!empty($search)) {
			$preQuery .= " AND ((name LIKE '%$search%') OR (homepage LIKE '%$search%') OR (comment LIKE '%$search%'))";
		}

		$childList = array_unique(Data_IAdapter::queryColumn($preQuery));
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
				{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteId = csiteinfo.id
			WHERE c.blogid = $blogid AND (c.parent is null) ";
		if (!empty($name)) {
			$sql .= ' AND ( c.name = \'' . Data_IAdapter::escapeString($name) . '\') ' ;
			$postfix .= '&amp;name=' . rawurlencode($name);
		}
		if (!empty($ip)) {
			$sql .= ' AND ( c.ip = \'' . Data_IAdapter::escapeString($ip) . '\') ';
			$postfix .= '&amp;ip=' . rawurlencode($ip);
		}
		if (!empty($search)) {
			$sql .= " AND ((c.name LIKE '%$search%') OR (c.homepage LIKE '%$search%') OR (c.comment LIKE '%$search%')) ";
			$postfix .= '&amp;search=' . rawurlencode($search);
		}
		$sql .= $childListStr . ' ORDER BY c.modified DESC';
	}

	list($comments, $paging) = fetchWithPaging($sql, $page, $count);
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
				{$database['prefix']}CommentsNotifiedSiteInfo csiteinfo ON c.siteId = csiteinfo.id
			WHERE c.blogid = ".getBlogId()." AND c.parent = $parent";
	$sql .= ' ORDER BY c.written ASC';
	if ($result = Data_IAdapter::queryAll($sql)) {
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

function getCommentsWithPagingForGuestbook($blogid, $page, $count) {
	global $database;
	$sql = "SELECT * FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND entry = 0
			AND parent IS NULL
			AND isFiltered = 0
		ORDER BY written DESC";
	$result = fetchWithPaging($sql, $page, $count);
	return $result;
}

function getCommentAttributes($blogid, $id, $attributeNames) {
	global $database;
	return Data_IAdapter::queryRow("SELECT $attributeNames FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
}

function getComments($entry) {
	global $database;
	$comments = array();
	$authorized = doesHaveOwnership();
	$aux = ($entry == 0 ? 'ORDER BY written DESC' : 'ORDER BY id ASC');
	$sql = "SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId()."
			AND entry = $entry
			AND parent IS NULL
			AND isFiltered = 0 $aux";
	if ($result = Data_IAdapter::queryAll($sql)) {
		foreach ($result as $comment) {
			if (($comment['secret'] == 1) && !$authorized) {
				if( !doesHaveOpenIDPriv($comment) ) {
					$comment['name'] = '';
					$comment['homepage'] = '';
					$comment['comment'] = _text('관리자만 볼 수 있는 댓글입니다.');
				}
			}
			if(!empty($comment['replier'])) {
				$comment['homepage'] = Model_User::getHomepage($comment['replier']);
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

function getCommentComments($parent,$parentComment=null) {
	global $database;
	$comments = array();
	$authorized = doesHaveOwnership();
	if ($result = Data_IAdapter::queryAll("SELECT *
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId()."
			AND parent = $parent
			AND isFiltered = 0
		ORDER BY written")) {
		if( $parentComment == null ) {
			$parentComment = Data_IAdapter::queryRow(
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
				$comment['homepage'] = Model_User::getHomepage($comment['replier']);
			}
			array_push($comments, $comment);
		}
	}
	return $comments;
}

function isCommentWriter($blogid, $commentId) {
	global $database;
	if (!doesHaveMembership())
		return false;
	return Data_IAdapter::queryExistence("SELECT replier
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND id = $commentId
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
	if ($result = Data_IAdapter::queryRow($sql)) {
		if($restriction != true) $result['password'] = null; // scope.
		return $result;
	}
	return false;
}

function getCommentList($blogid, $search) {
	global $database;
	$list = array('title' => "$search", 'items' => array());
	$search = Data_IAdapter::escapeSearchString($search);
	$authorized = doesHaveOwnership() ? '' : 'AND c.secret = 0 '.getPrivateCategoryExclusionQuery($blogid);
	if ($result = Data_IAdapter::queryAll("SELECT c.id, c.entry, c.parent, c.name, c.comment, c.written, e.slogan
		FROM {$database['prefix']}Comments c
		INNER JOIN {$database['prefix']}Entries e ON c.entry = e.id AND c.blogid = e.blogid AND e.draft = 0
		WHERE c.entry > 0
			AND c.blogid = $blogid $authorized
			and c.isFiltered = 0
			and (c.comment like '%$search%' OR c.name like '%$search%')
		ORDER BY c.written")) {
		foreach ($result as $comment)
			array_push($list['items'], $comment);
	}
	return $list;
}

function updateCommentsOfEntry($blogid, $entryId) {
	global $database;
	requireComponent('Needlworks.Cache.PageCache');
	$commentCount = Data_IAdapter::queryCell("SELECT COUNT(*)
		FROM {$database['prefix']}Comments
		WHERE blogid = $blogid
			AND entry = $entryId
			AND isFiltered = 0");
	Data_IAdapter::query("UPDATE {$database['prefix']}Entries
		SET comments = $commentCount
		WHERE blogid = $blogid
			AND id = $entryId");
	if($entryId >=0) Cache_Control::flushEntry($entryId);
	return $commentCount;
}

function sendCommentPing($entryId, $permalink, $name, $homepage) {
	global $database, $blog;
	$blogid = getBlogId();
	if($slogan = Data_IAdapter::queryCell("SELECT slogan
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid
			AND id = $entryId
			AND draft = 0
			AND visibility = 3 
			AND acceptComment = 1")) {
		$rpc = new Utils_XMLRPC();
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
		if (!Model_Filter::isAllowed($comment['homepage'])) {
			if (Model_Filter::isFiltered('ip', $comment['ip'])) {
				$blockType = "ip";
				$filtered = 1;
			} else if (Model_Filter::isFiltered('name', $comment['name'])) {
				$blockType = "name";
				$filtered = 1;
			} else if (Model_Filter::isFiltered('url', $comment['homepage'])) {
				$blockType = "homepage";
				$filtered = 1;
			} elseif (Model_Filter::isFiltered('content', $comment['comment'])) {
				$blockType = "comment";
				$filtered = 1;
			} elseif ( !Acl::check( "group.writers" ) && !$openid &&
				getBlogSetting('AddCommentMode', '') == 'openid' ) {
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
		$result = Data_IAdapter::queryCount("SELECT *
			FROM {$database['prefix']}Entries
			WHERE blogid = $blogid
				AND id = {$comment['entry']}
				AND draft = 0
				AND visibility > 0
				AND acceptComment = 1");
		if (!$result || $result == 0)
			return false;
	}
	$parent = $comment['parent'] == null ? 'null' : $comment['parent'];
	if ($user !== null) {
		$comment['replier'] = getUserId();
		$name = Data_IAdapter::escapeString($user['name']);
		$password = '';
		$homepage = Data_IAdapter::escapeString($user['homepage']);
		if( empty($homepage) && $openid ) { $homepage = Data_IAdapter::escapeString($openid); }
	} else {
		$comment['replier'] = 'null';
		$name = Data_IAdapter::escapeString($comment['name']);
		$password = empty($comment['password']) ? '' : md5($comment['password']);
		$homepage = Data_IAdapter::escapeString($comment['homepage']);
	}
	$comment0 = Data_IAdapter::escapeString($comment['comment']);
	$filteredAux = ($filtered == 1 ? "UNIX_TIMESTAMP()" : 0);
	$insertId = getCommentsMaxId() + 1;
	$result = Data_IAdapter::query("INSERT INTO {$database['prefix']}Comments
		(blogid,replier,id,openid,entry,parent,name,password,homepage,secret,comment,ip,written,isFiltered)
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
		Cache_Control::flushCommentRSS($comment['entry']);
		Cache_Control::flushDBCache('comment');
		if ($parent != 'null' && $comment['secret'] < 1) {
			$insertId = getCommentsNotifiedQueueMaxId() + 1;
			Data_IAdapter::execute("INSERT INTO `{$database['prefix']}CommentsNotifiedQueue`
					( `blogid` , `id`, `commentId` , `sendStatus` , `checkDate` , `written` )
				VALUES
					('".$blogid."' , '".$insertId."', '" . $id . "', '0', '0', UNIX_TIMESTAMP())");
		}
		updateCommentsOfEntry($blogid, $comment['entry']);
		fireEvent($comment['entry'] ? 'AddComment' : 'AddGuestComment', $id, $comment);
		if ($filtered == 1)
			return $blockType;
		else
			return $id;
	}
	return false;
}

function updateComment($blogid, $comment, $password) {
	global $database, $user;

	$openid = Acl::getIdentity('openid');
	if (!doesHaveOwnership()) {
		// if filtered, only block and not send to trash
		if (!Model_Filter::isAllowed($comment['homepage'])) {
			if (Model_Filter::isFiltered('ip', $comment['ip']))
				return 'blocked';
			if (Model_Filter::isFiltered('name', $comment['name']))
				return 'blocked';
			if (Model_Filter::isFiltered('url', $comment['homepage']))
				return 'blocked';
			if (Model_Filter::isFiltered('content', $comment['comment']))
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
		$name = Data_IAdapter::escapeString($user['name']);
		$setPassword = 'password = \'\',';
		$homepage = Data_IAdapter::escapeString($user['homepage']);
		if( empty($homepage) && $openid ) { $homepage = Data_IAdapter::escapeString($openid); }
	} else {
		$name = Data_IAdapter::escapeString($comment['name']);
		if ($comment['password'] !== true)
			$setPassword = 'password = \'' . (empty($comment['password']) ? '' : md5($comment['password'])) . '\', ';
		$homepage = Data_IAdapter::escapeString($comment['homepage']);
	}
	$comment0 = Data_IAdapter::escapeString($comment['comment']);

	$guestcomment = false;
	if (Data_IAdapter::queryExistence("SELECT *
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

	$result = Data_IAdapter::query("UPDATE {$database['prefix']}Comments
				SET
					name = '$name',
					$setPassword
					homepage = '$homepage',
					secret = {$comment['secret']},
					comment = '$comment0',
					ip = '{$comment['ip']}',
					written = UNIX_TIMESTAMP(),
					isFiltered = {$comment['isFiltered']},
					replier = {$replier}
				WHERE blogid = $blogid
					AND id = {$comment['id']} $wherePassword");
	if($result) {
		Cache_Control::flushCommentRSS($comment['entry']); // Assume blogid = current blogid.
		Cache_Control::flushDBCache('comment');
		return true;
	} else return false;
}

function deleteComment($blogid, $id, $entry, $password) {
	global $database;

	if (!is_numeric($id)) return false;
	if (!is_numeric($entry)) return false;

	$guestcomment = false;
	if (Data_IAdapter::queryExistence("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id AND replier IS NULL")) {
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
	if(Data_IAdapter::queryCount($sql . $wherePassword)) {
		Cache_Control::flushCommentRSS($entry);
		Cache_Control::flushDBCache('comment');
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
		SET isFiltered = UNIX_TIMESTAMP()
		WHERE blogid = $blogid
			AND id = $id
			AND entry = $entry";
	$affected = Data_IAdapter::queryCount($sql);
	$sql = "UPDATE {$database['prefix']}Comments
		SET isFiltered = UNIX_TIMESTAMP()
		WHERE blogid = $blogid
			AND parent = $id
			AND entry = $entry";
	$affectedChildren = Data_IAdapter::queryCount($sql);
	if ($affected + $affectedChildren > 0) {
		Cache_Control::flushCommentRSS($entry);
		Cache_Control::flushDBCache('comment');
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
		SET isFiltered = 0
		WHERE blogid = $blogid
			AND id = $id
			AND entry = $entry";
	if(Data_IAdapter::query($sql)) {
		Cache_Control::flushCommentRSS($entry);
		Cache_Control::flushDBCache('comment');
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
			r.blogid = $blogid".($isGuestbook != false ? " AND r.entry=0" : " AND r.entry>0")." AND r.isFiltered = 0
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
			.($isGuestbook != false ? " AND r.entry = 0" : " AND r.entry > 0")." AND r.isFiltered = 0
		ORDER BY
			r.written
		DESC LIMIT
			".($count != false ? $count : $skinSetting['commentsOnRecent']);
	if ($result = Data_IAdapter::queryAllWithDBCache($sql,'comment')) {
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
			r.blogid = $blogid AND r.entry = 0 AND r.isFiltered = 0
		ORDER BY
			r.written
		DESC LIMIT ".($count != false ? $count : $skinSetting['commentsOnRecent']);

	if ($result = Data_IAdapter::queryAll($sql)) {
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
	global $database, $skinSetting;
	$totalGuestbookId = Data_IAdapter::queryColumn("SELECT id
		FROM {$database['prefix']}Comments
		WHERE
			blogid = $blogid AND entry = 0 AND isFiltered = 0 AND parent is null
		ORDER BY
			written DESC");
	$order = array_search($id, $totalGuestbookId);
	if($order == false) {
		$parentCommentId = Data_IAdapter::queryCell("SELECT parent
			FROM {$database['prefix']}Comments
			WHERE
				blogid = $blogid AND entry = 0 AND isFiltered = 0 AND id = $id");
		if($parentCommentId != false) {
			$order = array_search($parentCommentId, $totalGuestbookId);
		} else {
			return false;
		}
	}
	return intval($order / $skinSetting['commentsOnGuestbook'])+1;
}

function deleteCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = Data_IAdapter::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	if(Data_IAdapter::queryCount("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id") == 1) {
		if (Data_IAdapter::query("DELETE FROM {$database['prefix']}Comments WHERE blogid = $blogid AND parent = $id")) {
			Cache_Control::flushCommentRSS($entryId);
			updateCommentsOfEntry($blogid, $entryId);
			return true;
		}
	}
	return false;
}

function trashCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = Data_IAdapter::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
//	$result = Data_IAdapter::queryCount("UPDATE {$database['prefix']}Comments SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id");
//	if ($result && $result == 1) {
	if(Data_IAdapter::query("UPDATE {$database['prefix']}Comments SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $id")) {
		if (Data_IAdapter::query("UPDATE {$database['prefix']}Comments SET isFiltered = UNIX_TIMESTAMP() WHERE blogid = $blogid AND parent = $id")) {
			Cache_Control::flushCommentRSS($entryId);
			Cache_Control::flushDBCache('comment');
			updateCommentsOfEntry($blogid, $entryId);
			return true;
		}
	}
	return false;
}

function revertCommentInOwner($blogid, $id) {
	global $database;
	if (!is_numeric($id)) return false;
	$entryId = Data_IAdapter::queryCell("SELECT entry FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	$parent = Data_IAdapter::queryCell("SELECT parent FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = $id");
	if(Data_IAdapter::queryCount("UPDATE {$database['prefix']}Comments SET isFiltered = 0 WHERE blogid = $blogid AND id = $id") == 1) {
		if (is_null($parent) || Data_IAdapter::query("UPDATE {$database['prefix']}Comments SET isFiltered = 0 WHERE blogid = $blogid AND id = $parent")) {
			Cache_Control::flushCommentRSS($entryId);
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

	$entryId = Data_IAdapter::queryCell("SELECT entry FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND id = $id");
	if(Data_IAdapter::queryCount("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND id = $id") == 1) {
		if (Data_IAdapter::query("DELETE FROM {$database['prefix']}CommentsNotified WHERE blogid = $blogid AND parent = $id")) {
			updateCommentsOfEntry($blogid, $entryId);
			Cache_Control::flushCommentNotifyRSS();
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
				CNQ.commentId AS commentId,
				CNQ.sendStatus AS sendStatus,
				CNQ.checkDate AS checkDate,
				CNQ.written  AS queueWritten
			FROM
				{$database['prefix']}CommentsNotifiedQueue AS CNQ
			LEFT JOIN
				{$database['prefix']}Comments AS CN ON CNQ.commentId = CN.id
			WHERE
				CNQ.sendStatus = '0'
				and CN.parent is not null
			ORDER BY CNQ.id ASC
			LIMIT 0, 1
		";
	$queue = Data_IAdapter::queryRow($sql);
	if (empty($queue) && empty($queue['queueId'])) {
		//Data_IAdapter::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
		return false;
	}
	$comments = (Data_IAdapter::queryRow("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$queue['commentId']}"));
	if (empty($comments['parent']) || $comments['secret'] == 1) {
		Data_IAdapter::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
		return false;
	}
	$parentComments = (Data_IAdapter::queryRow("SELECT * FROM {$database['prefix']}Comments WHERE blogid = $blogid AND id = {$comments['parent']}"));
	if (empty($parentComments['homepage'])) {
		Data_IAdapter::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
		return false;
	}
	$entry = (Data_IAdapter::queryRow("SELECT * FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id={$comments['entry']}"));
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
	Data_IAdapter::execute("DELETE FROM {$database['prefix']}CommentsNotifiedQueue WHERE id={$queue['queueId']}");
}

function receiveNotifiedComment($post) {
	if (empty($post['mode']) || $post['mode'] != 'fb')
		return 1;
	global $database;

	Cache_Control::flushCommentNotifyRSS();
	$post = fireEvent('ReceiveNotifiedComment', $post);
	if ($post === false) return 7;

	$blogid = getBlogId();
	$title = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['s_home_title'], 255));
	$name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['s_name'], 255));
	$entryId = Data_IAdapter::escapeString($post['s_no']);
	$homepage = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['url'], 255));
	$entryUrl = Data_IAdapter::escapeString($post['s_url']);
	$entryTitle = Data_IAdapter::escapeString($post['s_post_title']);
	$parent_id = $post['r1_no'];
	$parent_name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r1_name'], 80));
	$parent_parent = $post['r1_rno'];
	$parent_homepage = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r1_homepage'], 80));
	$parent_written = $post['r1_regdate'];
	$parent_comment = Data_IAdapter::escapeString($post['r1_body']);
	$parent_url = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r1_url'], 255));
	$child_id = $post['r2_no'];
	$child_name = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r2_name'], 80));
	$child_parent = $post['r2_rno'];
	$child_homepage = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r2_homepage'], 80));
	$child_written = $post['r2_regdate'];
	$child_comment = Data_IAdapter::escapeString($post['r2_body']);
	$child_url = Data_IAdapter::escapeString(UTF8::lessenAsEncoding($post['r2_url'],255));
	$siteId = Data_IAdapter::queryCell("SELECT id FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE url = '$homepage'");
	if (empty($siteId)) {
		$insertId = getCommentsNotifiedSiteInfoMaxId() + 1;
		if (Data_IAdapter::execute("INSERT INTO {$database['prefix']}CommentsNotifiedSiteInfo
			( id, title, name, url, modified)
			VALUES ($insertId, '$title', '$name', '$homepage', UNIX_TIMESTAMP());"))
			$siteId = $insertId;
		else
			return 2;
	}
	$parentId = Data_IAdapter::queryCell("SELECT id
		FROM {$database['prefix']}CommentsNotified
		WHERE entry = $entryId
			AND siteId = $siteId
			AND blogid = $blogid
			AND remoteId = $parent_id");
	if (empty($parentId)) {
		$insertId = getCommentsNotifiedMaxId() + 1;
		$sql = "INSERT INTO {$database['prefix']}CommentsNotified
			( blogid , replier , id , entry , parent , name , password , homepage , secret , comment , ip , written, modified , siteId , isNew , url , remoteId ,entryTitle , entryUrl )
			VALUES (
				$blogid, NULL , $insertId, " . $entryId . ", " . (empty($parent_parent) ? 'null' : $parent_parent) . ", '" . $parent_name . "', '', '" . $parent_homepage . "', '', '" . $parent_comment . "', '', " . $parent_written . ",UNIX_TIMESTAMP(), " . $siteId . ", 1, '" . $parent_url . "'," . $parent_id . ", '" . $entryTitle . "', '" . $entryUrl . "'
)";
		if (!Data_IAdapter::execute($sql))
			return 3;
		$parentId = $insertId;
	}
	if (Data_IAdapter::queryCell("SELECT count(*) FROM {$database['prefix']}CommentsNotified WHERE siteId=$siteId AND remoteId=$child_id") > 0)
		return 4;
	$insertId = getCommentsNotifiedMaxId() + 1;
	$sql = "INSERT INTO {$database['prefix']}CommentsNotified
		( blogid , replier , id , entry , parent , name , password , homepage , secret , comment , ip , written, modified , siteId , isNew , url , remoteId ,entryTitle , entryUrl )
		VALUES (
			$blogid, NULL , $insertId, " . $entryId . ", $parentId, '$child_name', '', '$child_homepage', '', '$child_comment', '', $child_written, UNIX_TIMESTAMP(), $siteId, 1, '$child_url', $child_id, '$entryTitle', '$entryUrl')";
	if (!Data_IAdapter::execute($sql))
		return 5;
	$sql = "UPDATE {$database['prefix']}CommentsNotified SET modified = UNIX_TIMESTAMP() WHERE blogid = $blogid AND id = $parentId";
	if (!Data_IAdapter::execute($sql))
		return 6;
	return 0;
}

function getCommentCount($blogid, $entryId = null) {
	global $database;
	if (is_null($entryId))
		return Data_IAdapter::queryCell("SELECT SUM(comments) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft= 0 ");
	return Data_IAdapter::queryCell("SELECT comments FROM {$database['prefix']}Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
}

function getGuestbookCount($blogid) {
	global $database;
	return Data_IAdapter::queryCell("SELECT count(id) FROM {$database['prefix']}Comments WHERE blogid = $blogid AND entry = 0");
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
	$maxId = Data_IAdapter::queryCell("SELECT max(id)
		FROM {$database['prefix']}Comments
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedMaxId() {
	global $database;
	$maxId = Data_IAdapter::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotified
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedQueueMaxId() {
	global $database;
	$maxId = Data_IAdapter::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotifiedQueue
		WHERE blogid = ".getBlogId());
	return empty($maxId) ? 0 : $maxId;
}

function getCommentsNotifiedSiteInfoMaxId() {
	global $database;
	$maxId = Data_IAdapter::queryCell("SELECT max(id)
		FROM {$database['prefix']}CommentsNotifiedSiteInfo");
	return empty($maxId) ? 0 : $maxId;
}

?>
