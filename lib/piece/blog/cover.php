<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)


if (isset($cache->contents)) {
	dress('cover', $cache->contents, $view);
} else if (isset($skin)) {
	$automaticLink = "<link rel=\"stylesheet\" href=\"{$serviceURL}/resources/style/system.css\" type=\"text/css\" media=\"screen\" />\n";
	if( rtrim( $suri['url'], '/' ) == $pathURL ) {
		/* same code exists in entries.php */
		$automaticLink .= "<link rel=\"meta\" type=\"application/rdf+xml\" title=\"FOAF\" href=\"{$defaultURL}/foaf\" />\n";
	}
	dress('SKIN_head_end', $automaticLink."[##_SKIN_head_end_##]", $view);
	$coverView = $skin->cover;
	$itemsView = '';
	handleCoverpages($skin, false);
	$coverpageModule = $skin->coverpageModule;
	foreach ($coverpageModule as $coverpageItem) {	
		$itemsView .= str_replace('[##_cover_content_##]',$coverpageItem, $skin->coverItem);
	}

	dress('cover_rep', $itemsView, $coverView);
	dress('cover', $coverView, $view);
	dress('foaf_url', "$defaultURL/foaf", $view);

	if(isset($cache)) { 
		$cache->contents = $coverView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
