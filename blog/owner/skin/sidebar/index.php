<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu33.php';
?>
						<script type="text/javascript">
							//<![CDATA[
						
							//]]>
						</script>
						
						<div id="part-sidebar-order" class="part" method="post" action="<?php echo $blogURL.'/owner/skin/sidebar/order';?>">
							<h2 class="caption"><span class="main-text"><?php echo _t('사이드바 순서를 조정합니다');?></span></h2>
							
							<div id="sidebar-box" class="data-inbox">
<?php
// 사이드바 번호별로 사이드바를 생성한다.
$skin = new Skin($skinSetting['skin']);
for ($i=0; $i<count($moduleOrders); $i++) {
	// 저장된 사이드바 정렬 순서 정보를 가져온다.
	$orders = getSidebarModuleOrderData($i, $skin->sidebarDefaultOrder[$i]);
?>
								<ul id="sidebar-<?php echo $i;?>" class="section">
<?php
	for ($j=0; $j<count($orders); $j++) {
?>
									<li><?php echo loadSidebarModule($orders[$j]['moduleId']);?></li>
<?php
	}
?>
								</ul>
<?php
}
?>						
								</div>
							</div>
							
							<form id="sidebarModule" class="data-subbox">
								
							</form>
							
							<form id="sidebarPlugin" class="data-subbox">
<?php
$plugins = array();
$pluginAttrs = array();

// 사이드바이면 기본 내장 플러그인 모듈을 리스트에 포함시킨다.
$innerSidebarIds = array_keys($skin->sidebarBasicModules);

for ($i=0; $i<count($innerSidebarIds); $i++) {
	$innerSidebarId = $innerSidebarIds[$i];
	echo $innerSidebarId;
}
?>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>