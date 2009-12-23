<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getPagingView( & $paging, & $template, & $itemTemplate, $useSkinCache = false) {
	global $service;
	if (($paging === false) || empty($paging['page'])) {
		$paging['url'] = NULL;
		$paging['prefix'] = NULL;
		$paging['postfix'] = NULL;
		$paging['total'] = NULL;
		$paging['pages'] = 1;
		$paging['page'] = 1;
		$paging['next'] = NULL;
	}
	
	$url = str_replace('/%3F/', '/?/', URL::encode($paging['url'], $service['useEncodedURL']));
	$prefix = $paging['prefix'];
	$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
	ob_start();
	if (isset($paging['first'])) {
		$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
		dress('paging_rep_link_num', '<span>1</span>', $itemView, $useSkinCache);
		dress('paging_rep_link', "href='$url$prefix{$paging['first']}$postfix'", $itemView, $useSkinCache);
		print ($itemView);
	} else if ($paging['page'] > 5) {
		$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
		dress('paging_rep_link_num', '<span>1</span>', $itemView, $useSkinCache);
		dress('paging_rep_link', "href='$url{$prefix}1$postfix'", $itemView, $useSkinCache);
		print ($itemView);
	}
	if (isset($paging['before']))
		$page = $paging['page'] - count($paging['before']);
	else
		$page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
	if (isset($paging['before'])) {
		foreach ($paging['before'] as $value) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useSkinCache);
			dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView, $useSkinCache);
			print ($itemView);
			$page++;
		}
	} else {
		for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useSkinCache);
			dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView, $useSkinCache);
			print ($itemView);
			$page++;
		}
	}
	if (($page == $paging['page']) && ($page <= $paging['pages'])) {
		$itemView = $itemTemplate;
		dress('paging_rep_link_num', "<span class=\"selected\" >$page</span>", $itemView, $useSkinCache);
		dress('paging_rep_link', '', $itemView, $useSkinCache);
		print ($itemView);
		$page++;
	}
	if (isset($paging['before'])) {
		foreach ($paging['after'] as $value) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useSkinCache);
			dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView, $useSkinCache);
			print ($itemView);
			$page++;
		}
	} else {
		for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "<span>$page</span>", $itemView, $useSkinCache);
			dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView, $useSkinCache);
			print ($itemView);
			$page++;
		}
	}
	if (isset($paging['last'])) {
		$itemView = " <span class=\"interword\">...</span> $itemTemplate";
		dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useSkinCache);
		dress('paging_rep_link', "href='$url$prefix{$paging['last']}$postfix'", $itemView, $useSkinCache);
		print ($itemView);
	} else if (($paging['pages'] - $paging['page']) > 4) {
		$itemView = " <span class=\"interword\">...</span> $itemTemplate";
		dress('paging_rep_link_num', "<span>{$paging['pages']}</span>", $itemView, $useSkinCache);
		dress('paging_rep_link', "href='$url$prefix{$paging['pages']}$postfix'", $itemView, $useSkinCache);
		print ($itemView);
	}
	$itemsView = ob_get_contents();
	ob_end_clean();
	$view = $template;
	dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '', $view, $useSkinCache);
	dress('paging_rep', $itemsView, $view, $useSkinCache);
	dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '', $view, $useSkinCache);
	dress('no_more_prev', isset($paging['prev']) ? '' : 'no-more-prev', $view, $useSkinCache);
	dress('no_more_next', isset($paging['next']) ? '' : 'no-more-next', $view, $useSkinCache);
	
	return $view; 
}
?>
