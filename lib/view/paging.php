<?

function getPagingView( & $paging, & $template, & $itemTemplate) {
	if (($paging === false) || empty($paging['page']))
		return '';
	$url = encodeURL($paging['url']);
	$prefix = $paging['prefix'];
	$postfix = isset($paging['postfix']) ? $paging['postfix'] : '';
	ob_start();
	if (isset($paging['first'])) {
		$itemView = "$itemTemplate ..";
		dress('paging_rep_link_num', '1', $itemView);
		dress('paging_rep_link', "href='$url$prefix{$paging['first']}$postfix'", $itemView);
		print ($itemView);
	} else if ($paging['page'] > 5) {
		$itemView = "$itemTemplate ..";
		dress('paging_rep_link_num', '1', $itemView);
		dress('paging_rep_link', "href='$url{$prefix}1$postfix'", $itemView);
		print ($itemView);
	}
	if (isset($paging['before']))
		$page = $paging['page'] - count($paging['before']);
	else
		$page = $paging['page'] < 5 ? 1 : $paging['page'] - 4;
	if (isset($paging['before'])) {
		foreach ($paging['before'] as $value) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "$page", $itemView);
			dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView);
			print ($itemView);
			$page++;
		}
	} else {
		for ($i = 0; ($i < 4) && ($page < $paging['page']); $i++) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "$page", $itemView);
			dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView);
			print ($itemView);
			$page++;
		}
	}
	if (($page == $paging['page']) && ($page <= $paging['pages'])) {
		$itemView = $itemTemplate;
		dress('paging_rep_link_num', "$page", $itemView);
		dress('paging_rep_link', 'style="color:red"', $itemView);
		print ($itemView);
		$page++;
	}
	if (isset($paging['before'])) {
		foreach ($paging['after'] as $value) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "$page", $itemView);
			dress('paging_rep_link', "href='$url$prefix$value$postfix'", $itemView);
			print ($itemView);
			$page++;
		}
	} else {
		for ($i = 0; ($i < 4) && ($page <= $paging['pages']); $i++) {
			$itemView = $itemTemplate;
			dress('paging_rep_link_num', "$page", $itemView);
			dress('paging_rep_link', "href='$url$prefix$page$postfix'", $itemView);
			print ($itemView);
			$page++;
		}
	}
	if (isset($paging['last'])) {
		$itemView = ".. $itemTemplate";
		dress('paging_rep_link_num', "{$paging['pages']}", $itemView);
		dress('paging_rep_link', "href='$url$prefix{$paging['last']}$postfix'", $itemView);
		print ($itemView);
	} else if (($paging['pages'] - $paging['page']) > 4) {
		$itemView = ".. $itemTemplate";
		dress('paging_rep_link_num', "{$paging['pages']}", $itemView);
		dress('paging_rep_link', "href='$url$prefix{$paging['pages']}$postfix'", $itemView);
		print ($itemView);
	}
	$itemsView = ob_get_contents();
	ob_end_clean();
	$view = $template;
	dress('prev_page', isset($paging['prev']) ? "href='$url$prefix{$paging['prev']}$postfix'" : '', $view);
	dress('paging_rep', $itemsView, $view);
	dress('next_page', isset($paging['next']) ? "href='$url$prefix{$paging['next']}$postfix'" : '', $view);
	return $view;
}
?>