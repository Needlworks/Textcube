<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu53.php';
?>
<script type="text/javascript">
//<![CDATA[
	function activatePlugin(plugin) {
		var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/activate?name=" + plugin);
		request.presetProperty(document.getElementById("plugin" + plugin + "IsActive").style, "display", "inline");
		request.presetProperty(document.getElementById("plugin" + plugin + "IsInactive").style, "display", "none");
		request.send();
	}
	function deactivatePlugin(plugin) {
		var request = new HTTPRequest("<?=$blogURL?>/owner/setting/plugins/deactivate?name=" + plugin);
		request.presetProperty(document.getElementById("plugin" + plugin + "IsActive").style, "display", "none");
		request.presetProperty(document.getElementById("plugin" + plugin + "IsInactive").style, "display", "inline");
		request.send();
	}
//]]>
</script>   
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('설치된 플러그인입니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" border="0" style="width:100%; margin-bottom:1px; table-layout:fixed; border-bottom:solid 2px #00A6ED" id="list">
              <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')" >
                <td width="200" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('제목')?></td>
                <td width="50" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('버전')?></td>
			 	<td align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('설명')?></td>
			 	<td width="160" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('만든이')?></td>
                <td width="70" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('상태')?></td>
              </tr>
<?
$more = false;
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
	if ($more) {
?>
              <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                <td height="1" colspan="5"></td>
              </tr>
<?
	} else {
		$more = true;
	}
	$link = $xmls->getValue('/plugin/link[lang()]');
	$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
	$authorLink = $xmls->getAttribute('/plugin/author[lang()]', 'link');
	$author = htmlspecialchars($xmls->getValue('/plugin/author[lang()]'));
	$active = in_array($plugin, $activePlugins);
?>
              <tr style="height:22px" onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
                <td align="center" class="row"><?=($link ? '<a href="' . htmlspecialchars($link) . '">' . $title . '</a>' : $title)?></td>
                <td align="center" class="row"><?=htmlspecialchars($xmls->getValue('/plugin/version[lang()]'))?></td>
			 	<td class="row"><?=htmlspecialchars($xmls->getValue('/plugin/description[lang()]'))?></td>
			 	<td align="center" class="row"><?=($authorLink ? '<a href="' . htmlspecialchars($authorLink) . '">' . $author . '</a>' : $author)?></td>
                <td align="center" class="row" nowrap="nowrap">
					<span id="plugin<?=$plugin?>IsActive" style="cursor:pointer; display:<?=($active ? 'inline' : 'none')?>" onclick="deactivatePlugin('<?=$plugin?>')"><img src="<?=$service['path']?>/image/pluginUsed.gif" alt="<?=_t('사용중')?>"/> <?=_t('사용중')?></span>
					<span id="plugin<?=$plugin?>IsInactive" style="cursor:pointer; display:<?=($active ? 'none' : 'inline')?>" onclick="activatePlugin('<?=$plugin?>')"><img src="<?=$service['path']?>/image/pluginUnused.gif" alt="<?=_t('미사용')?>"/> <?=_t('미사용')?></span>
				</td>
              </tr>
<?
}
echo '</table>';
require ROOT . '/lib/piece/owner/footer.php';
?>