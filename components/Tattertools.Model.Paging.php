<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Paging {
	function getPagingView( & $paging, & $template, & $itemTemplate) {
		requireComponent('Tattertools.Function.misc');
		if (($paging === false) || empty($paging['page']))
			return '';
		$url = encodeURL($paging['url']);
		$prefix = $paging['prefix'];
		$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
		ob_start();
		if (isset($paging['first'])) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			misc::dress('paging_rep_link_num', '1', $itemView);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['first']}$postfix'", $itemView);
			print ($itemView);
		} else if ($paging['page'] > 5) {
			$itemView = "$itemTemplate <span class=\"interword\">...</span> ";
			misc::dress('paging_rep_link_num', '1', $itemView);
			misc::dress('paging_rep_link', "href='$url{$prefix}1$postfix'", $itemView);
			print ($itemView);
		}
		if (isset($paging['before']))
			$page = $paging['page'] - count($paging['before']);
		else
			$page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
		if (isset($paging['before'])) {
			foreach ($paging['before'] as $value) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "$page", $itemView);
				misc::dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "$page", $itemView);
				misc::dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView);
				print ($itemView);
				$page++;
			}
		}
		if (($page == $paging['page']) && ($page <= $paging['pages'])) {
			$itemView = $itemTemplate;
			misc::dress('paging_rep_link_num', "$page", $itemView);
			misc::dress('paging_rep_link', 'class="selected"', $itemView);
			print ($itemView);
			$page++;
		}
		if (isset($paging['before'])) {
			foreach ($paging['after'] as $value) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "$page", $itemView);
				misc::dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView);
				print ($itemView);
				$page++;
			}
		} else {
			for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
				$itemView = $itemTemplate;
				misc::dress('paging_rep_link_num', "$page", $itemView);
				misc::dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView);
				print ($itemView);
				$page++;
			}
		}
		if (isset($paging['last'])) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			misc::dress('paging_rep_link_num', "{$paging['pages']}", $itemView);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['last']}$postfix'", $itemView);
			print ($itemView);
		} else if (($paging['pages'] - $paging['page']) > 4) {
			$itemView = " <span class=\"interword\">...</span> $itemTemplate";
			misc::dress('paging_rep_link_num', "{$paging['pages']}", $itemView);
			misc::dress('paging_rep_link', "href='$url$prefix{$paging['pages']}$postfix'", $itemView);
			print ($itemView);
		}
		$itemsView = ob_get_contents();
		ob_end_clean();
		$view = $template;
		$divClass = isset($paging['prev']) ? '' : 'no-more-prev ';
		misc::dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '', $view);
		misc::dress('paging_rep', $itemsView, $view);
		$divClass = $divClass . (isset($paging['next']) ? '' : 'no-more-next');
		misc::dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '', $view);
		return '<div' . (!empty($divClass) ? ' class="' . $divClass . '">' : '>' ) . $view . '</div>'; 
	}
}
?>
