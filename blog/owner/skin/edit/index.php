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
										PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
										saved = true;
									}
									request.onError = function() {
										if (this.getText("/response/msg"))
											alert(this.getText("/response/msg"));
										else
											alert('<?=_t('실패했습니다.')?>');
									}
									request.send('mode='+mode+'&body='+encodeURIComponent(skin.value));
								}
							//]]>
						</script>
<?php
if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/index.xml")) {
	$xml = file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/index.xml");
	$xmls = new XMLStruct();
	$xmls->open($xml, $service['encoding']);
	$skinName = $xmls->getValue('/skin/information/name');
} else {
	$skinName = $skinSetting['skin'];
}
?>
						<div id="part-skin-edit" class="part">
							<h2 class="caption"><span class="main-text"><?=_f('스킨을 편집합니다 : "%1"', $skinName)?></span></h2>
							
							<div class="data-inbox">
								<div id="html-section" class="section">
									<div class="file-name">skin.html</div>
									<textarea id="skin" name="skin_html" cols="60" rows="25" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?=htmlspecialchars($skin)?></textarea>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setSkin('skin');"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
								
								<!--div id="keyword-section" class="section">
									<div class="file-name">skin_keyword.html</div>
									<textarea id="skin_keyword"name="s_cache_keyword_html" cols="60" rows="25"onkeyup="saved=false">﻿<?=htmlspecialchars($skin_keyword)?></textarea>
									<a class="save-button button" href="#void" onclick="setSkin('skin_keyword');"><span class="text"><?=_t('저장하기')?></span></a>		  
								</div-->
								
								<hr class="hidden" />
								
								<div id="css-section" class="section">
									<div class="file-name">style.css</div>
									<textarea id="style" name="s_cache_style_css" cols="60" rows="25" onkeyup="saved=false"><?=htmlspecialchars($style)?></textarea>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setSkin('style');"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
							</div>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>