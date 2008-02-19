<?php 
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$blogid = getBlogId();
requireModel('blog.statistics');
updateVisitorStatistics($blogid);
$stats = getStatistics($blogid);
if (!empty($entries) && (count($entries) == 1))
	$pageTitle = $entries[0]['title'];
else
	$pageTitle = '';
if (!isset($skin))
	$skin = new Skin($skinSetting['skin']);

$view = str_replace('[##_t3_##]', getUpperView(isset($paging) ? $paging : null) . $skin->skin . getLowerView() . getScriptsOnFoot(), $skin->outter);

if (!empty($category)) {
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
	dress('body_id',"tt-body-page",$view);
} else {
	dress('body_id',"tt-body-".ltrim($suri['directive'],'/'),$view);
}
?>
