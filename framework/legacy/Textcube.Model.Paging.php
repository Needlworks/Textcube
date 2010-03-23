<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Paging {
	function init($url, $prefix = '?page=') {
		return array('url' => rtrim($url,'?'), 'prefix' => $prefix, 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0, 'before' => array(), 'after' => array());
	}
	
	function getPagingView( & $paging, & $template, & $itemTemplate, $useCache = false, $mode = 'href') {
		
		if (($paging === false) || empty($paging['page'])) {
			$paging['url'] = NULL;
			$paging['onclick'] = NULL;
			$paging['prefix'] = NULL;
			$paging['postfix'] = NULL;
			$paging['total'] = NULL;
			$paging['pages'] = 1;
			$paging['page'] = 1;
			$paging['next'] = NULL;
		}
		
		$url = URL::encode($paging['url']);
		$prefix = $paging['prefix'];
		$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
		ob_start();
		if (isset($paging['first'])) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			Misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
			Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix{$paging['first']}$postfix'", $itemView, $useCache);
			print ($itemView);
		} else if ($paging['page'] > 5) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			Misc::dress('paging_rep_link_num', '<span>1</span>', $itemView, $useCache);
			Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url{$prefix}1$postfix'", $itemView, $useCache);
			print ($itemView);
		}
		if (isset($paging['before']))
			$page = $paging['page'] - count($paging['before']);
		else
			$page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
		if (isset($paging['before'])) {
			foreach ($paging['before'] as $value) {
				$itemView = $itemTemplate;
				Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix$value$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
				$itemView = $itemTemplate;
				Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix$page$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		}
		if (($page == $paging['page']) && ($page <= $paging['pages'])) {
			$itemView = $itemTemplate;
			Misc::dress('paging_rep_link_num', "<span class=\"selected\" >$page</span>", $itemView, $useCache);
			Misc::dress('paging_rep_link', '', $itemView, $useCache);
			print ($itemView);
			$page++;
		}
		if (isset($paging['before'])) {
			foreach ($paging['after'] as $value) {
				$itemView = $itemTemplate;
				Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix$value$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
				$itemView = $itemTemplate;
				Misc::dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useCache);
				Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix$page$postfix'", $itemView, $useCache);
				print ($itemView);
				$page++;
			}
		}
		if (isset($paging['last'])) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			Misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
			Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix{$paging['last']}$postfix'", $itemView, $useCache);
			print ($itemView);
		} else if (($paging['pages'] - $paging['page']) > 4) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			Misc::dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useCache);
			Misc::dress('paging_rep_link', ($mode == 'href' ? "href='" : "href='#' onclick='")."$url$prefix{$paging['pages']}$postfix'", $itemView, $useCache);
			print ($itemView);
		}
		$itemsView = ob_get_contents();
		ob_end_clean();
		$view = $template;
		Misc::dress('prev_page', isset($paging['prev']) ? ($mode == 'href' ? "href=\"" : "href=\"#\" onclick=\"")."$url$prefix{$paging['prev']}$postfix\" rel=\"prev\"" : '', $view, $useCache);
		Misc::dress('prev_page_title', isset($paging['prev_title']) ? $paging['prev_title'] : '', $view, $useCache);
		Misc::dress('paging_rep', $itemsView, $view, $useCache);
		Misc::dress('next_page', isset($paging['next']) ? ($mode == 'href' ? "href=\"" : "href=\"#\" onclick=\"")."$url$prefix{$paging['next']}$postfix\" rel=\"next\"" : '', $view, $useCache);
		Misc::dress('next_page_title', isset($paging['next_title']) ? $paging['next'] : '', $view, $useCache);
		Misc::dress('no_more_prev', isset($paging['prev']) ? '' : 'no-more-prev', $view, $useCache);
		Misc::dress('no_more_next', isset($paging['next']) ? '' : 'no-more-next', $view, $useCache);
		
		return $view;
	}
	
	function fetch($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null, $onclick = null) {
		$context = Model_Context::getInstance();
		if ($url === null)
			$url = $context->getProperty('uri.folder');
		$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '', 'onclick' => $onclick);
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
		}
		if ($paging['page'] > 1)
			$paging['prev'] = $paging['page'] - 1;
		if ($paging['page'] < $paging['pages'])
			$paging['next'] = $paging['page'] + 1;
		$offset = ($paging['page'] - 1) * $count;
		if ($offset < 0) $offset = 0;
		if ($countItem !== null) $count = $countItem;
		return array(POD::queryAll("$sql LIMIT $count OFFSET $offset"), $paging);
	}
	/** Legacy methods **/

	function initPaging($url, $prefix = '?page=') {
		return self::init($url, $prefix);
	}

	function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null, $onclick = null) {
		return self::fetch($sql, $page, $count, $url, $prefix, $countItem, $onclick);
	}
}
?>
