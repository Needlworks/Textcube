<?php

function initPaging($url, $prefix = '?page=') {
	return array('url' => $url, 'prefix' => $prefix, 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0, 'before' => array(), 'after' => array());
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=') {
	global $folderURL;
	if ($url === null)
		$url = $folderURL;
	$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '');
	if (empty($sql))
		return array(array(), $paging);
	if (eregi('[[:space:]]{1}(FROM.*)$', $sql, $matches))
		$from = $matches[1];
	else
		return array(array(), $paging);
	$paging['total'] = fetchQueryCell("SELECT COUNT(*) $from");
	if ($paging['total'] === null)
		return array(array(), $paging);
	$paging['pages'] = intval(ceil($paging['total'] / $count));
	$paging['page'] = is_numeric($page) ? $page : 1;
	if ($paging['page'] > $paging['pages']) {
		$paging['page'] = $paging['pages'];
		if ($paging['pages'] > 0)
			$paging['prev'] = $paging['pages'] - 1;
		//return array(array(), $paging);
	}
	if ($paging['page'] > 1)
		$paging['prev'] = $paging['page'] - 1;
	if ($paging['page'] < $paging['pages'])
		$paging['next'] = $paging['page'] + 1;
	$offset = ($paging['page'] - 1) * $count;
	return array(fetchQueryAll("$sql LIMIT $offset, $count"), $paging);
}
?>
