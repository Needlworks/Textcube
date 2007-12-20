<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';

// get style files list in current skin.
$styleFileList = array();
$tempStyleFileList = getFileListByRegExp(ROOT . "/skin/{$skinSetting['skin']}", '\.css$', true);

foreach ($tempStyleFileList as $styleFile) {
	$styleFileList[basename($styleFile)] = $styleFile;
}

@ksort($styleFileList);
unset($tempStyleFileList);

// set current css.
if (isset($_GET['style'])) {
	$currentStyleFile = $_GET['style'];
} else {
	$tempKeys = array_keys($styleFileList);
	$currentStyleFile = str_replace(ROOT . "/skin/{$skinSetting['skin']}/", '', $styleFileList[$tempKeys[0]]);
}

$skin = '';
if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/skin.html")) {
	$skin = @file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/skin.html");
}
$skin_keyword = '';
if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/skin_keyword.html")) {
	$skin_keyword = @file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/skin_keyword.html");
}

$htmlFilePerms = preg_replace('@^[0-9]{2}|[0-9]{2}$@', '', strrev(decoct(fileperms(ROOT . "/skin/{$skinSetting['skin']}/skin.html"))));
$styleFilePerms = preg_replace('@^[0-9]{2}|[0-9]{2}$@', '', $temp = strrev(decoct(fileperms(ROOT . "/skin/{$skinSetting['skin']}/" . $currentStyleFile))));

require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								var skinHTMLSaved = true;
								var skinStyleSaved = true;
								
								function setSkin(mode) {
									var skin = document.getElementById(mode);
									var file = document.getElementById(mode + 'FileName');
									
									if ((mode == 'skin' && skinHTMLSaved == false) || (mode == 'style' && skinStyleSaved == false)) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/edit/skin/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
											
											if (mode == 'skin') {
												skinHTMLSaved = true;
											} else {
												skinStyleSaved = true;
											}
										}
										request.onError = function() {
											if (this.getText("/response/msg"))
												alert(this.getText("/response/msg"));
											else
												alert('<?php echo _t('실패했습니다.');?>');
										}
										request.send('mode='+mode+'&body='+encodeURIComponent(skin.value)+'&file='+ file.value);
									}
								}
								
<?php
if (count($styleFileList) > 0 && !empty($currentStyleFile) && file_exists(ROOT . "/skin/{$skinSetting['skin']}/" . $currentStyleFile)) {
?>		
								var currentStyleLiNumber = 0;
								var documentIcons = new Array();
								documentIcons['css_off'] = new Image();
								documentIcons['css_off'].src = '<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_css_document_off.gif';
								documentIcons['css_on'] = new Image();
								documentIcons['css_on'].src = '<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_css_document_on.gif';
								
								function changeCSSFile(obj, file) {
									if (skinStyleSaved == false && confirm("<?php echo _t('변경된 스타일이 아직 저장되지 않았습니다.\n저장하시겠습니까?');?>"))
										setSkin('style');
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/edit/loadCSS/");
									request.onSuccess = function() {
										document.getElementById('cssSectionForm').s_cache_style_css.value = this.getText("/response/content");
										document.getElementById('cssSectionForm').file.value = file;
										PM.showMessage("<?php echo _t('불러 왔습니다.');?>", "center", "bottom");
										skinStyleSaved = true;
										
										tempUL = getParentByTagName('UL', obj);
										
										tempCount = 0;
										for (i=0; i<tempUL.childNodes.length; i++) {
											if (tempUL.childNodes[i].tagName == 'LI') {
												tempLI = tempUL.childNodes[i];
												tempLI.className = '';
												tempLink = tempLI.childNodes[0];
												tempStrong = tempLink.childNodes[0];
												if (tempStrong != undefined && tempStrong.tagName == 'STRONG') {
													tempStrong.childNodes[0].src = documentIcons['css_off'].src;
													tempLink.innerHTML = tempStrong.innerHTML;
												}
											}
										}
										
										tempLI = getParentByTagName('LI', obj);
										tempLI.className = 'selected';
										tempStrong = document.createElement('STRONG');
										tempLI.childNodes[0].childNodes[0].src = documentIcons['css_on'].src;
										tempStrong.innerHTML = tempLI.childNodes[0].innerHTML;
										tempLI.childNodes[0].innerHTML = '';
										tempLI.childNodes[0].appendChild(tempStrong);
									}
									request.onError = function() {
										alert('<?php echo _t('실패했습니다.');?>');
									}
									request.send('file=' + file);
								}
<?php
}
?>

								/* ToDo : CSS 속성제어용 자바스크립트 에디터
								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
									var CSSeditor = new SkinEditor();
									CSSeditor.initialize('style');
								}*/
							//]]>
						</script>
<?php
if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/index.xml")) {
	$xml = file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/index.xml");
	$xmls = new XMLStruct();
	$xmls->open($xml, $service['encoding']);
	$skinName = $xmls->getValue('/skin/information/name') . ($skinSetting['skin'] == "customize/$blogid" ? _t('(사용자 수정본)') : NULL);
} else {
	$skinName = $skinSetting['skin'];
}
?>
						<div id="part-skin-edit" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _f('스킨을 편집합니다 : "%1"', $skinName);?></span></h2>
							
							<div class="data-inbox">
								<form id="htmlSectionForm" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<ul>
										<li class="selected"><a><img src="<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_html_document_on.gif" alt="" /><strong>skin.html</strong></a></li>
									</ul>

<?php
if ($htmlFilePerms < 6) {
?>
									<p class="explain"><em class="attention"><span><?php echo _t('skin.html 파일을 수정하실 수 있는 권한이 없습니다.');?></span></em></p>
<?php
}
?>
									<div id="html-container">
										<textarea id="skin" name="skin_html" cols="60" rows="25" onkeyup="skinHTMLSaved=false"><?php echo htmlspecialchars($skin);?></textarea>
									</div>
<?php
if ($htmlFilePerms >= 6) {
?>
									<div class="button-box">
										<input type="hidden" id="skinFileName" name="file" value="skin.html" />
										<input type="reset" class="reset-button input-button" value="<?php echo _t('되돌리기');?>" />
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setSkin('skin'); return false" />
									</div>
<?php
}
?>
								</form>
								
								<!--form id="keyword-section" class="section">
									<div class="file-name">skin_keyword.html</div>
									<textarea id="skin_keyword" name="s_cache_keyword_html" cols="60" rows="25" onkeyup="saved=false">﻿<?php echo htmlspecialchars($skin_keyword);?></textarea>
									<a class="save-button button" href="#void" onclick="setSkin('skin_keyword');"><span class="text"><?php echo _t('저장하기');?></span></a>		  
								</form-->
								
<?php
// get current style's contents.
$currentStyleContents = file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/{$currentStyleFile}");

if (count($styleFileList) > 0) {
?>
								<hr class="hidden" />
								
								<form id="cssSectionForm" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<ul>
<?php
	$count = 0;
	
	foreach ($styleFileList as $styleFile) {
		$tempFile = str_replace(ROOT . "/skin/{$skinSetting['skin']}/", '', $styleFile);
		if ($tempFile == $currentStyleFile) {
?>
										<li class="selected"><a href="<?php echo $blogURL;?>/owner/skin/edit/?style=<?php echo $tempFile;?>" onclick="changeCSSFile(this, '<?php echo $tempFile;?>'); return false;"><strong><img src="<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_css_document_on.gif" alt="" /><?php echo basename($tempFile);?></strong></a></li>
<?php
		} else {
?>
										<li><a href="<?php echo $blogURL;?>/owner/skin/edit/?style=<?php echo $tempFile;?>" onclick="changeCSSFile(this, '<?php echo $tempFile;?>'); return false;"><img src="<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_css_document_off.gif" alt="" /><?php echo basename($tempFile);?></a></li>
<?php
		}
		
		$count++;
	}
?>
									</ul>
									
<?php
	if ($styleFilePerms < 6) {
?>
									<p class="explain"><em class="attention"><span><?php echo _f('%1 파일을 수정하실 수 있는 권한이 없습니다.', $currentStyleFile);?></span></em></p>
<?php
	}
?>
									<div id="style-box">
										<textarea id="style" name="s_cache_style_css" cols="60" rows="25" onkeyup="skinStyleSaved=false"><?php echo htmlspecialchars($currentStyleContents);?></textarea>
									</div>
<?php
	if ($styleFilePerms >= 6) {
?>
									<div class="button-box">
										<input type="hidden" id="styleFileName" name="file" value="<?php echo $currentStyleFile;?>" />
										<input type="reset" class="reset-button input-button" value="<?php echo _t('되돌리기');?>" />
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setSkin('style'); return false" />
									</div>
<?php
	}
?>
								</form>
<?php
}
?>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
