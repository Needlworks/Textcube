<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Paging {
	function initPaging($url, $prefix = '?page=') {
		return array('url' => rtrim($url,'?'), 'prefix' => $prefix, 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0, 'before' => array(), 'after' => array());
	}
	
	function getPagingView( & $paging, & $template, & $itemTemplate, $useCache = false) {
		requireComponent('Textcube.Function.misc');
		
		if (($paging === false) || empty($paging['page'])) {
			$paging['url'] = NULL;
			$paging['prefix'] = NULL;
			$paging['postfix'] = NULL;
			$paging['total'] = NULL;
			$paging['pages'] = 1;
			$paging['page'] = 1;
			$paging['next'] = NULL;
		}
		
		$url = encodeURL($paging['url']);
		$prefix = $paging['prefix'];
		$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
		ob_start();
		if (isset($paging['first'])) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['first']}$postfix'", $itemView, $useCache);
			print ($itemView);
		} else if ($paging['page'] > 5) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
			misc::dress('paging_rep_link', "href='$url{$prefix}1$postfix'", $itemView, $useCache);
			print ($itemView);
		}
		if (isset($paging['before']))
			$page = $paging['page'] - count($paging['before']);
		else
			$page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
		if (isset($paging['before'])) {
			foreach ($paging['before'] as $value) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				misc::dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				misc::dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		}
		if (($page == $paging['page']) && ($page <= $paging['pages'])) {
			$itemView = $itemTemplate;
			misc::dress('paging_rep_link_num', "<span class=\"selected\" >$page</span>", $itemView, $useCache);
			misc::dress('paging_rep_link', '', $itemView, $useCache);
			print ($itemView);
			$page++;
		}
		if (isset($paging['before'])) {
			foreach ($paging['after'] as $value) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				misc::dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				misc::dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		}
		if (isset($paging['last'])) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['last']}$postfix'", $itemView, $useCache);
			print ($itemView);
		} else if (($paging['pages'] - $paging['page']) > 4) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['pages']}$postfix'", $itemView, $useCache);
			print ($itemView);
		}
		$itemsView = ob_get_contents();
		ob_end_clean();
		$view = $template;
		misc::dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '', $view, $useCache);
		misc::dress('paging_rep', $itemsView, $view, $useCache);
		misc::dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '', $view, $useCache);
		misc::dress('no_more_prev', isset($paging['prev']) ? '' : 'no-more-prev', $view, $useCache);
		misc::dress('no_more_next', isset($paging['next']) ? '' : 'no-more-next', $view, $useCache);
		
		return $view;
	}
	
	function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
		global $folderURL, $service;
		if ($url === null)
			$url = $folderURL;
		$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '');
		if (empty($sql))
			return array(array(), $paging);
		if (preg_match('/\s(FROM.*)(ORDER BY.*)$/si', $sql, $matches))
			$from = $matches[1];
		else
			return array(array(), $paging);
		$paging['total'] = POD::queryCell("SELECT COUNT(*) $from");
		if ($paging['total'] === null)
			return array(array(), $paging);
		if (empty($count)) $count = 1;
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
		if ($offset < 0) $offset = 0;
		if ($countItem !== null) $count = $countItem;
		//return array(POD::queryAll("$sql LIMIT $offset OFFSET $count"), $paging);
		return array(POD::queryAll("$sql LIMIT $count OFFSET $offset"), $paging);
	}
}
?>
