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
							
							<div class="main-explain-box">
								<p class="explain">다른 사람의 블로그에 단 댓글에 대한 댓글이 등록되면 알려줍니다. 알리미가 동작하기 위해서는 댓글 작성시 '홈페이지' 기입란에 자신의 블로그 주소(<samp>http://127.0.0.1/branch/graphittie</samp>)를 입력하셔야 합니다.</p>
							</div>
							
							<div id="sidebar-box" class="section">
								<h3><?php echo _t('사용중인 사이드바 모듈');?></h3>
<?php
$sidebarPluginArray = array();
for ($i=0; $i<count($sidebarMappings); $i++) {
	$sidebarPluginArray[$sidebarMappings[$i]['plugin']] = array("class" => $sidebarMappings[$i]['class'], "title" => $sidebarMappings[$i]['title'], "display" => $sidebarMappings[$i]['display'], "handler" => $sidebarMappings[$i]['handler']);
}

$skin = new Skin($skinSetting['skin']);
$usedSidebarBasicModule = array();
$sidebarCount = count($skin->sidebarBasicModules);

// 사용중인 사이드바 모듈 리스트 출력.
for ($i=0; $i<$sidebarCount; $i++) {
	$orderConfig = getSidebarModuleOrderData($i);
?>
								<ul id="sidebar-<?php echo $i;?>" class="sidebar">
<?php
	if (is_null($orderConfig)) {
		$moduleCountInSidebar = count($skin->sidebarBasicModules[$i]);
		for ($j=0; $j<$moduleCountInSidebar; $j++) {
?>
									<li class="sidebar-module sidebar-basic-module">
										<?php echo $skin->sidebarBasicModules[$i][$j]['title'];?>
										<div class="button-box">
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
										</div>
									</li>
<?php
			array_push($usedSidebarBasicModule, $j);
		}
	} else {
		for ($j=0; $j<count($orderConfig); $j++) {
			if (preg_match("/^[0-9]+$/", $orderConfig[$j]['id'])) {
?>
									<li class="sidebar-module sidebar-basic-module">
										<?php echo $skin->sidebarBasicModules[$i][$orderConfig[$j]['id']]['title'];?>
										<div class="button-box">
<?php
				if ($j == 0) {
?>
											<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
				} else {
?>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
				}
				
				if ($j == count($orderConfig) - 1) {
?>
											<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
				} else {
?>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
				}
?>
											
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
										</div>
									</li>
<?php
				array_push($usedSidebarBasicModule, $orderConfig[$j]['id']);
			} else {
?>
									<li class="sidebar-module sidebar-plugin-module">
										<?php echo $sidebarPluginArray[$orderConfig[$j]['id']]['display'];?>
										<div class="button-box">
<?php
				if ($j == 0) {
?>
											<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
				} else {
?>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
				}
				
				if ($j == count($orderConfig) - 1) {
?>
											<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
				} else {
?>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
				}
?>
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
										</div>
									</li>
<?php
			}
		}
	}
?>
								</ul>
<?php
}
?>
							</div>
							
							<div id="sidebar-basic-module-box" class="section">
								<h3><?php echo _t('스킨 내장형 사이드바');?></h3>
								
								<ul>
<?php
// 사용중이지 않은 플러그인형 사이드바 모듈 리스트 출력.
for ($i=0; $i<$sidebarCount; $i++) {
	$iCount = 0;
	$moduleCountInSidebar = count($skin->sidebarBasicModules[$i]);
	for ($j=0; $j<$moduleCountInSidebar; $j++) {
		if (!in_array($j, $usedSidebarBasicModule)) {
?>
									<li class="sidebar-module sidebar-plugin-module">
										<?php echo $skin->sidebarBasicModules[$i][$j]['title'];?>
										<div class="button-box">
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_add_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
										</div>
									</li>
<?php
			$iCount++;
		}
	}
}
?>
								</ul>
							</div>
							
							<div id="sidebar-plugin-module-box" class="section">
								<h3><?php echo _t('플러그인형 사이드바 모듈');?></h3>
								
								<ul>
<?php
// 사이드바 플러그인 모듈을 리스트에 포함시킨다.
$iCount = 0;
foreach($sidebarPluginArray as $module) {
?>
									<li class="sidebar-module sidebar-plugin-module">
										<?php echo $module['display'];?>
										<div class="button-box">
											<a href="#void" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
										</div>
									</li>
<?php
	$iCount++;
}
?>	
								</ul>
							</div>
							
							<div class="button-box">
								
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>