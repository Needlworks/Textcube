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
						
						<form id="part-sidebar-order" class="part" method="post" action="sidebar/register">
							<h2 class="caption"><span class="main-text"><?php echo _t('사이드바 기능을 관리합니다');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain">다른 사람의 블로그에 단 댓글에 대한 댓글이 등록되면 알려줍니다. 알리미가 동작하기 위해서는 댓글 작성시 '홈페이지' 기입란에 자신의 블로그 주소(<samp>http://127.0.0.1/branch/graphittie</samp>)를 입력하셔야 합니다.</p>
							</div>
							
							<div id="sidebar-box" class="data-inbox">
								<h3><?php echo _t('사이드바')?></h3>
<?php
$sidebarPluginArray = array();
for ($i=0; $i<count($sidebarMappings); $i++) {
	$sidebarPluginArray[$sidebarMappings[$i]['plugin']] = array("plugin" => $sidebarMappings[$i]['plugin'], "class" => $sidebarMappings[$i]['class'], "title" => $sidebarMappings[$i]['title'], "display" => $sidebarMappings[$i]['display'], "handler" => $sidebarMappings[$i]['handler']);
}

$skin = new Skin($skinSetting['skin']);
$usedSidebarBasicModule = array();
$sidebarCount = count($skin->sidebarBasicModules);

// 사용중인 사이드바 모듈 리스트 출력.
$bFirstRadio = true;
$sidebarConfig = getSidebarModuleOrderData($sidebarCount);
for ($i=0; $i<$sidebarCount; $i++) {
	$orderConfig = $sidebarConfig[$i];
?>
								<div class="section">
									<h4><input type="radio" id="sidebar-<?php echo $i + 1;?>" class="radio" name="sidebarNumber" value="<?php echo $i;?>"<?php echo $bFirstRadio ? " checked" : NULL;?> /><label for="sidebar-<?php echo $i + 1;?>"><?php echo _t('사이드바').' '.($i + 1);?></label></h4>
									
									<ul id="sidebar-<?php echo $i;?>" class="sidebar">
<?php
	for ($j=0; $j<count($orderConfig); $j++) {
		if (preg_match("/^([0-9]+)\-([0-9]+)$/", $orderConfig[$j]['id'], $temp)) {
?>
										<li class="sidebar-module sidebar-basic-module">
											<?php echo $skin->sidebarBasicModules[$temp[1]][$temp[0]]['title'];?>
											<div class="button-box">
<?php
			if ($j == 0) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
			} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j - 1;?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
			}
				
			if ($j == count($orderConfig) - 1) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
			} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j + 1;?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
			}
?>
											
												<a href="sidebar/delete/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
											</div>
										</li>
<?php
			array_push($usedSidebarBasicModule, $orderConfig[$j]['id']);
		} else if (function_exists($orderConfig[$j]['id'])) {
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
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j - 1;?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
			}
				
			if ($j == count($orderConfig) - 1) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
			} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j + 1;?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
			}
?>
												<a href="sidebar/delete/?module=<?php echo $i.'-'.$j;?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
											</div>
										</li>
<?php
		}
	}
?>
									</ul>
								</div>
<?php
	$bFirstRadio = false;
}
?>
							</div>
							
							<div class="data-subbox">
								<h3><?php echo _t('추가 가능한 사이드바 모듈');?></h3>
								
								<fieldset id="sidebar-basic-module-box" class="section">
									<legend><?php echo _t('추가 가능한 모듈(스킨 기본)');?></legend>
									
									<ul>
<?php
// 사용중이지 않은 스킨 내장형 사이드바 모듈 리스트 출력.
$sortedArray = array();
for ($i=0; $i<$sidebarCount; $i++) {
	$moduleCountInSidebar = count($skin->sidebarBasicModules[$i]);
	for ($j=0; $j<$moduleCountInSidebar; $j++) {
		if (!in_array("{$i}-{$j}", $usedSidebarBasicModule)) {
			$sortedArray[$skin->sidebarBasicModules[$i]["{$i}-{$j}"]['title']] = "{$i}-{$j}";
		}
	}
}

$sortedKeys = array_keys($sortedArray);
sort($sortedKeys);
foreach ($sortedKeys as $nowKey) {
?>
										<li class="sidebar-module">
											<input type="radio" id="module<?php echo $sortedArray[$nowKey];?>" class="radio" name="moduleId" value="<?php echo $sortedArray[$nowKey];?>" onclick="alert(this.value)" /><label for="module<?php echo $sortedArray[$nowKey];?>"><?php echo $nowKey;?></label>
										</li>
<?php
}
?>
									</ul>
								</fieldset>
								
								<fieldset id="sidebar-plugin-module-box" class="section">
									<legend><?php echo _t('추가 가능한 플러그인');?></legend>
									
									<ul>
<?php
// 사이드바 플러그인 모듈을 리스트에 포함시킨다.
$sortedArray = array();
foreach($sidebarPluginArray as $module) {
	$sortedArray[$module['plugin']] = $module['display'];
}

$sortedKeys = array_keys($sortedArray);
sort($sortedKeys);
foreach ($sortedKeys as $nowKey) {
?>
										<li class="sidebar-module">
											<input type="radio" id="module-<?php echo $nowKey;?>" class="radio" name="moduleId" value="<?php echo $nowKey;?>" onclick="alert(this.value)" /><label for="module-<?php echo $nowKey;?>"><?php echo $sortedArray[$nowKey];?></label>
										</li>
<?php
}
?>	
									</ul>
								</fieldset>
								
								<div class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('모듈 추가');?>" title="<?php echo _t('사이드바에 선택된 모듈의 기능을 추가합니다.');?>"/>
									<a class="button" href="sidebar/initialize" onclick="if (!confirm('<?php echo _t('정말 사이드바 기능을 초기화하시겠습니까?');?>')) return false;" title="<?php echo _t('사이드바의 기능을 스킨 설정 상태로 초기화합니다.');?>"><span class="text"><?php echo _t('초기화');?></span></a>
								</div>
							</div>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>