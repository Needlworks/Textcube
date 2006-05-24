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
												
												if (!tempStr.match(/<?=_t('사용중')?>/ig)) {
													var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/activate?name=" + plugin);
													//request.presetProperty(document.getElementById("plugin" + plugin + "IsActive").style, "display", "inline");
													//request.presetProperty(document.getElementById("plugin" + plugin + "IsInactive").style, "display", "none");
													request.send();
												
													document.getElementById("plugin_" + num).className = 'active-icon';
												
													document.getElementById("plugin" + num + "Link").innerHTML = '<span><?=_t('사용중')?></span>';
													document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
													document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>');
												} else {
													var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/deactivate?name=" + plugin);
													//request.presetProperty(document.getElementById("plugin" + plugin + "IsActive").style, "display", "inline");
													//request.presetProperty(document.getElementById("plugin" + plugin + "IsInactive").style, "display", "none");
													request.send();
												
													document.getElementById("plugin_" + num).className = 'inactive-icon';
												
													document.getElementById("plugin" + num + "Link").innerHTML = '<span><?=_t('미사용')?></span>';
													document.getElementById("plugin_" + num).setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
													document.getElementById("plugin" + num + "Link").setAttribute('title', '<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>');
												}
											}
										//]]>
									</script>
									
									<div id="part-setting-plugins" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('설치된 플러그인입니다')?></span></h2>
										
										<table class="data-inbox" cellspacing="0" cellpadding="0" border="0">
											<tr class="tr-head">
												<td class="title"><span><?=_t('제목')?></span></td>
												<td class="version"><span><?=_t('버전')?></span></td>
							 					<td class="explain"><span><?=_t('설명')?></span></td>
							 					<td class="maker"><span><?=_t('만든이')?></span></td>
												<td class="statue"><span><?=_t('상태')?></span></td>
											</tr>
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
	
	if ($i == sizeof($plugins) - 1) {
?>
											<tr class="tr-last-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="title"><?=($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title)?></td>
												<td class="version"><?=htmlspecialchars($xmls->getValue('/plugin/version[lang()]'))?></td>
							 					<td class="explain"><?=htmlspecialchars($xmls->getValue('/plugin/description[lang()]'))?></td>
							 					<td class="maker"><?=($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author)?></td>
												<td class="statue">
<?
		if ($active) {
?>
													<span id="plugin_<?=$i?>" class="active-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span><?=_t('사용중')?></span></a>
<?
		} else {
?>
													<span id="plugin_<?=$i?>" class="inactive-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span><?=_t('미사용')?></span></a>
<?
		}
?>
												</td>
											</tr>
<?
	} else {
?>
											<tr class="tr-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
												<td class="title"><?=($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title)?></td>
												<td class="version"><?=htmlspecialchars($xmls->getValue('/plugin/version[lang()]'))?></td>
							 					<td class="explain"><?=htmlspecialchars($xmls->getValue('/plugin/description[lang()]'))?></td>
							 					<td class="maker"><?=($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author)?></td>
												<td class="statue">
<?
		if ($active) {
?>
													<span id="plugin_<?=$i?>" class="active-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중입니다. 클릭하시면 사용을 중지합니다.')?>"><span><?=_t('사용중')?></span></a>
<?
		} else {
?>
													<span id="plugin_<?=$i?>" class="inactive-icon bullet" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span></span></span><a id="plugin<?=$i?>Link" href="#void" onclick="togglePlugin('<?=$plugin?>',<?=$i?>)" title="<?=_t('이 플러그인은 사용중지 상태입니다. 클릭하시면 사용을 시작합니다.')?>"><span><?=_t('미사용')?></span></a>
<?
		}
?>
												</td>
											</tr>
<?
	}
?>

<?
}
?>
										</table>
									</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>