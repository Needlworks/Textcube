<?
updateVisitorStatistics($owner);
$stats = getStatistics($owner);
if (!empty($entries) && (count($entries) == 1))
	$pageTitle = $entries[0]['title'];
else
	$pageTitle = '';
$skin = new Skin($skinSetting['skin']);
$view = str_replace('[##_t3_##]', getUpperView(isset($paging) ? $paging : null) . $skin->skin . getLowerView() . getScriptsOnFoot(), $skin->outter);

if ($suri['directive'] == '/')
	$view = str_replace('[##_body_id_##]','page', $view);
else if (isset($list))
	$view = str_replace('[##_body_id_##]',$suri['value'], $view);
else
	$view = str_replace('[##_body_id_##]',ltrim($suri['directive'],'/'), $view);
?>
