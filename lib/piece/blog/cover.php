<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


if (isset($cache->contents)) {
	dress('cover', $cache->contents, $view);
} else if (isset($skin)) {
	$coverView = $skin->cover;
	$itemsView = '';

	foreach ($coverpageModule as $coverpageItem) {	
		$itemsView .= str_replace('[##_cover_content_##]',$coverpageItem, $skin->coverItem);
	}

	dress('cover_rep', $itemsView, $coverView);
	dress('cover', $coverView, $view);

	if(isset($cache)) { 
		$cache->contents = $coverView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
