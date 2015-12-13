<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


if (isset($cache->contents)) {
	dress('cover', $cache->contents, $view);
} else if (isset($skin)) {
	if( rtrim( $suri['url'], '/' ) == $context->getProperty('uri.path') ) {
		/* same code exists in entries.php */
		$automaticLink .= "<link rel=\"meta\" type=\"application/rdf+xml\" title=\"FOAF\" href=\"".$context->getProperty('uri.default')."/foaf\" />\n";
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
	if(Setting::getBlogSettingGlobal('useMicroformat',3) == 3) {
		$coverView = addWebSlice($coverView, 'coverPageWebslice',  htmlspecialchars($blog['title'].' - '._t('표지')));
	}
	dress('cover', $coverView, $view);
	dress('foaf_url', $context->getProperty('uri.default')."/foaf", $view);

	if(isset($cache)) { 
		$cache->contents = $coverView;
		$cache->dbContents = $paging;
		$cache->update();
	}
}
?>
