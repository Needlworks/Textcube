<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
// get style files list in current skin.
$styleFileList = array();
$tempStyleFileList = Misc::getFileListByRegExp(ROOT . "/skin/blog/{$skinSetting['skin']}", '\.css$', true);

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
	$currentStyleFile = str_replace(ROOT . "/skin/blog/{$skinSetting['skin']}/", '', $styleFileList[$tempKeys[0]]);
}

$skin = '';
if (file_exists(ROOT . "/skin/blog/{$skinSetting['skin']}/skin.html")) {
	$skin = @file_get_contents(ROOT . "/skin/blog/{$skinSetting['skin']}/skin.html");
}
$skin_keyword = '';
if (file_exists(ROOT . "/skin/blog/{$skinSetting['skin']}/skin_keyword.html")) {
	$skin_keyword = @file_get_contents(ROOT . "/skin/blog/{$skinSetting['skin']}/skin_keyword.html");
}

$htmlFilePerms = preg_replace('@^[0-9]{2}|[0-9]{2}$@', '', strrev(decoct(fileperms(ROOT . "/skin/blog/{$skinSetting['skin']}/skin.html"))));
$styleFilePerms = preg_replace('@^[0-9]{2}|[0-9]{2}$@', '', $temp = strrev(decoct(fileperms(ROOT . "/skin/blog/{$skinSetting['skin']}/" . $currentStyleFile))));

require ROOT . '/interface/common/owner/header.php';

?>
						<script type="text/javascript">
							//<![CDATA[
								var skinHTMLSaved = true;
								var skinStyleSaved = true;
								
								function setSkin(mode) {
									var skin = document.getElementById(mode);
									var file = document.getElementById(mode + 'FileName');
									var skinData = '';
									if ((mode == 'skin' && skinHTMLSaved == false) || (mode == 'style' && skinStyleSaved == false)) {
										if(mode == 'skin' && currentTag != 'all') {
											skinData = skinCode;
										} else {
											skinData = skin.value;
										}

										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/edit/skin/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											
											if (mode == 'skin') {
												skinHTMLSaved = true;
												if(document.getElementById('skin-download').innerHTML == '') {
													document.getElementById('skin-download').innerHTML = '<a href="<?php echo $blogURL;?>/owner/skin/edit/download/?file=skin.html"><?php echo _t('내려받기');?></a>';
												}
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
										request.send('mode='+mode+'&body='+encodeURIComponent(skinData)+'&file='+ file.value);
									}
								}

								var currentTag = 'all';
								var currentCode = '';
								function changeTab(mode,tag) {
									currentCode = document.getElementById(mode).value;
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/edit/crop/");									
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('불러왔습니다.');?>", "center", "top");
										document.getElementById(mode).value = (this.getText("/response/code") ? this.getText("/response/code") : ''); 
										skinCode = this.getText("/response/skinCode");
										document.getElementById('skin-'+currentTag).className = '';
										document.getElementById('skin-'+tag).className = 'selected';										
										currentTag = tag;
										switch(mode) {
											case 'skin':
											undoBuffer[0] = document.getElementById(mode).value;
											break;
										}
									}
									request.onError = function() {
										if (this.getText("/response/msg"))
											alert(this.getText("/response/msg"));
										else
											alert('<?php echo _t('실패했습니다.');?>');
									}
									request.send('skinCode='+encodeURIComponent(skinCode)
										+'&currentTag='+encodeURIComponent(currentTag)
										+'&currentCode='+encodeURIComponent(currentCode)
										+'&nextTag='+encodeURIComponent(tag));
								}
								function undo(mode) {
									switch(mode) {
										case 'skin':
											document.getElementById(mode).value = undoBuffer[0];
											break;
									}
									return true;
								}
<?php
if (count($styleFileList) > 0 && !empty($currentStyleFile) && file_exists(ROOT . "/skin/blog/{$skinSetting['skin']}/" . $currentStyleFile)) {
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
										PM.showMessage("<?php echo _t('불러왔습니다.');?>", "center", "bottom");
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
if (file_exists(ROOT . "/skin/blog/{$skinSetting['skin']}/index.xml")) {
	$xml = file_get_contents(ROOT . "/skin/blog/{$skinSetting['skin']}/index.xml");
	$xmls = new XMLStruct();
	$xmls->open($xml, $service['encoding']);
	$skinName = $xmls->getValue('/skin/information/name') . ($skinSetting['skin'] == "customize/$blogid" ? _t('(수정한 스킨)') : NULL);
} else {
	$skinName = $skinSetting['skin'];
}
?>
						<div id="part-skin-edit" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _f('스킨을 편집합니다 : "%1"', $skinName);?></span></h2>
							
							<div class="data-inbox">
								<div class="main-explain-box">
									<p class="explain"><?php echo _t('이 곳에서 스킨을 편집할 수 있습니다. 편집이 끝난 스킨은 저장하기를 눌러 반영할 수 있습니다. 수정 중 원래 스킨으로 되돌리고 싶을 때는 되돌리기 버튼을 눌러 원래대로 되돌릴 수 있습니다.').'<br />'._t('수정한 파일은 내려받기 링크를 통해 다운로드 할 수 있습니다. 가장 최근에 수정한 파일을 내려 받기 위해서는 다운로드 전에 꼭 저장을 먼저 해 주시기 바랍니다. 스킨 파일은 하나의 html 파일로 되어 있기 때문에 다양한 웹 에디터를 사용해서 편집하셔도 문제가 없습니다. 내려받은 파일을 웹 에디터로 편집하신 후에 스킨 편집창에 붙여넣는 식으로 스킨을 수정하셔도 됩니다.');?></p>
								</div>
								
								
								
								<ul id="skin-tabs-box" class="tabs-box">
									<li id="skin-all" class="selected"><a href="#" onclick="changeTab('skin','all');return false;"><?php echo _t('전체');?></a></li>
									<li id="skin-article_rep"><a href="#" onclick="changeTab('skin','article_rep');return false;"><?php echo _t('본문');?></a></li>
									<li id="skin-rp"><a href="#" onclick="changeTab('skin','rp');return false;"><?php echo _t('댓글 영역');?></a></li>
									<li id="skin-tb"><a href="#" onclick="changeTab('skin','tb');return false;"><?php echo _t('트랙백 영역');?></a></li>									
									<li id="skin-list"><a href="#" onclick="changeTab('skin','list');return false;"><?php echo _t('리스트');?></a></li>
									<li id="skin-rplist"><a href="#" onclick="changeTab('skin','rplist');return false;"><?php echo _t('댓글 리스트');?></a></li>	
									<li id="skin-tblist"><a href="#" onclick="changeTab('skin','tblist');return false;"><?php echo _t('트랙백 리스트');?></a></li>
									<li id="skin-local"><a href="#" onclick="changeTab('skin','local');return false;"><?php echo _t('지역로그');?></a></li>
									<li id="skin-tag"><a href="#" onclick="changeTab('skin','tag');return false;"><?php echo _t('태그');?></a></li>
									<li id="skin-guest"><a href="#" onclick="changeTab('skin','guest');return false;"><?php echo _t('방명록');?></a></li>
									<li id="skin-keyword"><a href="#" onclick="changeTab('skin','keyword');return false;"><?php echo _t('키워드');?></a></li>
									<li id="skin-keylog_rep"><a href="#" onclick="changeTab('skin','keylog_rep');return false;"><?php echo _t('키로그');?></a></li>
									<li id="skin-cover"><a href="#" onclick="changeTab('skin','cover');return false;"><?php echo _t('표지');?></a></li>
								</ul>
								
								<form id="htmlSectionForm" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<ul>
										<li class="selected"><a><img src="<?php echo $serviceURL . $adminSkinSetting['skin'];?>/image/img_html_document_on.gif" alt="" /><strong>skin.html</strong></a>
											<span id="skin-download" class="download">
<?php
if (file_exists(ROOT . "/skin/blog/customize/".getBlogId()."/skin.html")) {
?>
											<a href="<?php echo $blogURL;?>/owner/skin/edit/download/?file=skin.html"><?php echo _t('내려받기');?></a>
<?php
}
?>
											</span>
										</li>
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
										<input type="reset" class="reset-button input-button" onclick="undo('skin');return false;" value="<?php echo _t('되돌리기');?>" />
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
$currentStyleContents = file_get_contents(ROOT . "/skin/blog/{$skinSetting['skin']}/{$currentStyleFile}");

if (count($styleFileList) > 0) {
?>
								<hr class="hidden" />
								
								<form id="cssSectionForm" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/edit/skin/">
									<ul>
<?php
	$count = 0;
	
	foreach ($styleFileList as $styleFile) {
		$tempFile = str_replace(ROOT . "/skin/blog/{$skinSetting['skin']}/", '', $styleFile);
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
						<script type="text/javascript">
							//<![CDATA[
								var skinCode    = document.getElementById('skin').value;
								var undoBuffer = new Array();
								undoBuffer[0] = skinCode;
							//]]>
						</script>							
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
