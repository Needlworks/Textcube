<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu53.php';
?>
							<script type="text/javascript">
								//<![CDATA[
									function togglePlugin(plugin, num) {
										tempStr = document.getElementById("plugin" + num + "Link").innerHTML;
										
										if (!tempStr.match('<?=_t('사용중')?>')) {
											var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/activate?name=" + plugin);
											request.onSuccess = function() {												
												document.getElementById("plugin_" + num).className = 'active-icon bullet';
											
												document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?=_t('사용중')?></span>';
												document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
												document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
												
												objTR = getParentTr(document.getElementById("plugin" + num + "Link"));
												objTR.className = objTR.className.replace('inactive', 'active');
											}
											request.onError = function() {
												alert("<?=_t('플러그인을 활성화하는데 실패했습니다.')?>");
											}
											request.send();
										} else {
											var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/deactivate?name=" + plugin);
											request.onSuccess = function() {
												document.getElementById("plugin_" + num).className = 'inactive-icon bullet';
												
												document.getElementById("plugin" + num + "Link").innerHTML = '<span class="text"><?=_t('미사용')?></span>';
												document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
												document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
												
												objTR = getParentTr(document.getElementById("plugin" + num + "Link"));
												objTR.className = objTR.className.replace('active', 'inactive');
											}
											request.onError = function() {
												alert("<?=_t('플러그인을 비활성화하는데 실패했습니다.')?>");
											}
											request.send();
										}
									}
									
									function getParentTr(obj){
										while (obj.tagName != "TR") {
											obj = obj.parentNode;
										}
										return obj;
									}
								//]]>
							</script>
							
							<div id="part-setting-plugins" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('설치된 플러그인입니다')?></span></h2>
								
								<div class="main-explain-box">
									<p class="explain"><?php echo _t('플러그인은 태터툴즈의 기능을 확장해 줍니다. 설치된 플러그인은 이 메뉴에서 사용여부를 결정합니다.')?></p>
								</div>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<td class="title"><span class="text"><?=_t('제목')?></span></td>
											<td class="version"><span class="text"><?=_t('버전')?></span></td>
											<td class="explain"><span class="text"><?=_t('설명')?></span></td>
											<td class="maker"><span class="text"><?=_t('만든이')?></span></td>
											<td class="status"><span class="text"><?=_t('상태')?></span></td>
										</tr>
									</thead>
									<tbody>
<?
$plugins = array();
$dir = dir(ROOT . '/plugins/');
while ($plugin = $dir->read()) {
	if (!ereg('^[[:alnum:] _-]+$', $plugin))
		continue;
	if (!is_dir(ROOT . '/plugins/' . $plugin))
		continue;
	if (!file_exists(ROOT . "/plugins/$plugin/index.xml"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml")))
		continue;
	$plugins[] = $plugin;
}

for ($i=0; $i<sizeof($plugins); $i++) {
	$plugin = $plugins[$i];
	
	$xmls = new XMLStruct();
	$xmls->open(file_get_contents(ROOT . "/plugins/$plugin/index.xml"));
	$link = $xmls->getValue('/plugin/link[lang()]');
	$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
	$authorLink = $xmls->getAttribute('/plugin/author[lang()]', 'link');
	$author = htmlspecialchars($xmls->getValue('/plugin/author[lang()]'));
	$active = in_array($plugin, $activePlugins);
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($plugins) - 1) ? ' last-line' : '';
	$className .= $active ? ' active-class' : ' inactive-class';
?>
										<tr class="<?php echo $className?>" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="title"><?=($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title)?></td>
											<td class="version"><?=htmlspecialchars($xmls->getValue('/plugin/version[lang()]'))?></td>
											<td class="explain"><?=htmlspecialchars($xmls->getValue('/plugin/description[lang()]'))?></td>
											<td class="maker"><?=($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author)?></td>
											<td class="status">
<?
	if ($active) {
?>
												<span id="plugin_<?=$i?>" class="active-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span class="text"><?=_t('사용중')?></span></a>
<?
	} else {
?>
												<span id="plugin_<?=$i?>" class="inactive-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span class="text"><?=_t('미사용')?></span></a>
<?
	}
?>
											</td>
										</tr>
<?
}
?>
									</tbody>
								</table>
							</div>
							
							<div id="part-setting-more" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('플러그인을 구하려면')?></span></h2>
								
								<div class="main-explain-box">
									<p class="explain"><?php echo _t('추가 플러그인은 <a href="http://www.tattertools.com/plugins" onclick="window.open(this.href); return false;" title="태터툴즈 홈페이지에 개설되어 있는 플러그인 업로드 게시판으로 연결합니다.">태터툴즈 홈의 플러그인 게시판</a>에서 구하실 수 있습니다. 일반적으로 플러그인 파일을 태터툴즈의 plugin 디렉토리로 업로드하면 설치가 완료됩니다. 업로드가 완료된 플러그인은 이 메뉴에서 \'사용중\'으로 전환하여 사용을 시작합니다. 추천 플러그인에 대한 정보는 <a href="http://plugin.tattertools.com" onclick="window.open(this.href); return false;">TnF의 플러그인 리뷰</a>를 참고하십시오.')?></p>
								</div>
							</div>	
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>