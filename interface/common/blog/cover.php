<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


if (isset($cache->contents)) {
	dress('cover', $cache->contents, $view);
} else if (isset($skin)) {
	if( rtrim( $suri['url'], '/' ) == $pathURL ) {
		/* same code exists in entries.php */
		$automaticLink .= "<link rel=\"meta\" type=\"application/rdf+xml\" title=\"FOAF\" href=\"{$defaultURL}/foaf\" />\n";
	}
	$coverView = $skin->cover;
	$itemsView = '';
	handleCoverpages($skin, false);
	$coverpageModule = $skin->coverpageModule;
	foreach ($coverpageModule as $coverpageItem) {	
		$itemsView .= str_replace('[##_cover_content_##]',$coverpageItem, $skin->coverItem);
	}

	dress('cover_rep', $itemsView, $coverView);
	/* Add webslice feature */
	$coverView = addWebSlice($coverView, 'coverPageWebslice',  htmlspecialchars($blog['title'].' - '._t('표지')));
	dress('cover', $coverView, $view);
	dress('foaf_url', "$defaultURL/foaf", $view);

	if(isset($cache)) { 
		$cache->contents = $coverView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
