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
													alert('<?=_t('실패 했습니다.')?>');
											}
											request.send('mode='+mode+'&body='+encodeURIComponent(skin.value));
										}
									//]]>
								</script>
								
								<div id="part-skin-edit" class="part">
									<h2 class="caption"><span class="main-text"><?=_f('"%1"을 편집합니다', $skinSetting['skin'])?></span></h2>
									
									<div class="data-inbox">
										<div id="html-area" class="section">
											<div class="file-name">skin.html</div>
											<textarea id="skin" name="skin_html" cols="60" rows="25" onkeyup="saved=false" style="font-family: 'Courier New', Courier, monospace"><?=htmlspecialchars($skin)?></textarea>
											<div class="button-box">
												<a class="save-button button" href="#void" onclick="setSkin('skin');"><span><?=_t('저장하기')?></span></a>
											</div>
										</div>
										
										<!--div id="keyword-area" class="section">
											<div class="file-name">skin_keyword.html</div>
											<textarea id="skin_keyword"name="s_cache_keyword_html" rows="25"onkeyup="saved=false">﻿<?=htmlspecialchars($skin_keyword)?></textarea>
											<a class="save-button button" href="#void" onclick="setSkin('skin_keyword');"><span><?=_t('저장하기')?></span></a>		  
										</div-->
										
										<hr class="hidden" />
										
										<div id="css-area" class="section">
											<div class="file-name">style.css</div>
											<textarea id="style" name="s_cache_style_css" cols="60" rows="25" onkeyup="saved=false"><?=htmlspecialchars($style)?></textarea>
											<div class="button-box">
												<a class="save-button button" href="#void" onclick="setSkin('style');"><span><?=_t('저장하기')?></span></a>
											</div>
										</div>
									</div>
								</div>
<?
require ROOT . '/lib/piece/owner/footer2.php';
?>