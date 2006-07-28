<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$skin = @file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/skin.html");
$skin_keyword = @file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/skin_keyword.html");
$style = @file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/style.css");
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu31.php';
?>
<script type="text/javascript">
	//<![CDATA[
	function setSkin(mode) {
		var skin = document.getElementById(mode);
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/skin/edit/skin/");
		request.onSuccess = function() {
			PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			saved = true;
		}
		request.onError = function() {
			if (this.getText("/response/msg"))
				alert(this.getText("/response/msg"));
			else
				alert('<?=_t('실패 했습니다')?>');
		}
		request.send('mode='+mode+'&body='+encodeURIComponent(skin.value));
	}
	//]]>
</script>
<table cellspacing="0" style="width:100%; height:28px;">
<tr>
  <td style="width:18px;"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
  <td style="padding:3px 0px 0px 4px;"><?=_f('"%1"을 편집합니다', $skinSetting['skin'])?></td>
</tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
	<tr>
	  <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;"><div style="padding:0 0 0 10px;"> <br />
		  skin.html <br />
		  <textarea rows="25" name="skin_html" id="skin" style="width:98%;" cols="" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?=htmlspecialchars($skin)?></textarea>
		  <br />
		  <table class="buttonTop" cellspacing="0" onclick="setSkin('skin');">
			<tr>
			  <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
			  <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장')?></td>
			  <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
			</tr>
		  </table><br />
<!--		  
		  <br />
		  <br />
		  skin_keyword.html <br />
		  <textarea rows="25" name="s_cache_keyword_html" id="skin_keyword" style="width:98%;" cols="" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace">﻿<?=htmlspecialchars($skin_keyword)?></textarea>
		  <br />                                  
		  <table class="buttonTop" cellspacing="0"  onclick="setSkin('skin_keyword');">
			<tr>
			  <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
			  <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장')?></td>
			  <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
			</tr>
		  </table>
		  <br />
		  <br />
-->		  
		  style.css <br />
		  <textarea rows="25" name="s_cache_style_css" id="style" style="width:98%;" cols="" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?=htmlspecialchars($style)?></textarea>
		  <br />                                  
		  <table class="buttonTop" cellspacing="0" onclick="setSkin('style');">
			<tr>
			  <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
			   <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장')?></td>
			  <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
			</tr>
		  </table>
		  </div></td>
	</tr>
</table>
