<?php 
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$blogid = getBlogId();
Statistics::updateVisitorStatistics($blogid);
$stats = Statistics::getStatistics($blogid);
if (!empty($entries) && (count($entries) == 1))
	$pageTitle = $entries[0]['title'];
else
	$pageTitle = '';
if (!isset($skin))
	$skin = new Skin($skinSetting['skin']);

$view = $skin->outter;
$view = str_replace('[##_SKIN_head_end_##]',getScriptsOnHead().'[##_SKIN_head_end_##]', $view); // TO DO : caching this part.
$view = str_replace('[##_SKIN_body_start_##]',getUpperView(isset($paging) ? $paging : null).'[##_SKIN_body_start_##]', $view);
$view = str_replace('[##_SKIN_body_end_##]',getLowerView().getScriptsOnFoot().'[##_SKIN_body_end_##]', $view); // care the order for js function overloading issue.

$automaticLink = "	<link rel=\"stylesheet\" href=\"".$context->getProperty('uri.service')."/resources/style/system.css\" type=\"text/css\" media=\"screen\" />\n";
dress('SKIN_head_end', $automaticLink."[##_SKIN_head_end_##]", $view);

if($context->getProperty('blog.useBlogIconAsIphoneShortcut') == true && file_exists(ROOT."/attach/".$context->getProperty('blog.id')."/index.gif")) {
	dress('SKIN_head_end', '<link rel="apple-touch-icon" href="'.$context->getProperty('uri.default')."/index.gif".'" />'."[##_SKIN_head_end_##]",$view);
}

if (defined('__TEXTCUBE_COVER__')) {
	dress('body_id',"tt-body-cover",$view);
} else if (!empty($category)) {
	dress('body_id',getCategoryBodyIdById($blogid,$category) ? getCategoryBodyIdById($blogid,$category) : 'tt-body-category',$view);
} else if (!empty($search)) {
	dress('body_id',"tt-body-search",$view);
} else if (!empty($period)) {
	dress('body_id',"tt-body-archive",$view);
//} else if (isset($list)) {
//	dress('body_id',$suri['value'],$view);
} else if (($suri['directive'] == '/' && is_numeric($suri['value'])) || $suri['directive'] == '/owner/entry/preview') {
	dress('body_id',"tt-body-entry",$view);
} else if ($suri['directive'] == '/') {
	dress('body_id',"tt-body-pages",$view);
} else {
	dress('body_id',"tt-body-".ltrim($suri['directive'],'/'),$view);
}
?>
