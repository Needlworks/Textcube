<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/edit/skin/");
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										saved = true;
									}
									request.onError = function() {
										if (this.getText("/response/msg"))
											alert(this.getText("/response/msg"));
										else
											alert('<?php echo _t('실패했습니다.');?>');
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
	$skinName = $xmls->getValue('/skin/information/name') . ($skinSetting['skin'] == "customize/$owner" ? _t('(사용자 수정본)') : NULL);
} else {
	$skinName = $skinSetting['skin'];
}
?>
						<div id="part-skin-edit" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _f('스킨을 편집합니다 : "%1"', $skinName);?></span></h2>
							
							<div class="data-inbox">
								<form id="htmlSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<div class="file-name">skin.html</div>
									<div class="edit-area">
										<textarea id="skin" name="skin_html" cols="60" rows="25" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?php echo htmlspecialchars($skin);?></textarea>
									</div>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setSkin('skin'); return false" />
									</div>
								</form>
								
								<!--form id="keyword-section" class="section">
									<div class="file-name">skin_keyword.html</div>
									<textarea id="skin_keyword"name="s_cache_keyword_html" cols="60" rows="25"onkeyup="saved=false">﻿<?php echo htmlspecialchars($skin_keyword);?></textarea>
									<a class="save-button button" href="#void" onclick="setSkin('skin_keyword');"><span class="text"><?php echo _t('저장하기');?></span></a>		  
								</form-->
								
								<hr class="hidden" />
								
								<form id="cssSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<div class="file-name">style.css</div>
									<div class="edit-area">
										<textarea id="style" name="s_cache_style_css" cols="60" rows="25" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?php echo htmlspecialchars($style);?></textarea>
									</div>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setSkin('style'); return false" />
									</div>
								</form>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
