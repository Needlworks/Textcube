<?php 
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

updateVisitorStatistics($owner);
$stats = getStatistics($owner);
if (!empty($entries) && (count($entries) == 1))
	$pageTitle = $entries[0]['title'];
else
	$pageTitle = '';
if (!isset($skin))
	$skin = new Skin($skinSetting['skin']);

$view = str_replace('[##_t3_##]', getUpperView(isset($paging) ? $paging : null) . $skin->skin . getLowerView() . getScriptsOnFoot(), $skin->outter);

if (!empty($category)) {
	dress('body_id',getCategoryBodyIdById($owner,$category) ? getCategoryBodyIdById($owner,$category) : 'tt-body-category',$view);
} else if (!empty($search)) {
	dress('body_id',"tt-body-search",$view);
} else if (!empty($period)) {
	dress('body_id',"tt-body-archive",$view);
//} else if (isset($list)) {
//	dress('body_id',$suri['value'],$view);
} else if ($suri['directive'] == '/' && is_numeric($suri['value'])) {
	dress('body_id',"tt-body-entry",$view);
} else if ($suri['directive'] == '/') {
	dress('body_id',"tt-body-page",$view);
} else {
	dress('body_id',"tt-body-".ltrim($suri['directive'],'/'),$view);
}
?>
