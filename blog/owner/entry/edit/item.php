<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'draft' => array('any', 'mandatory' => false),
		'popupEditor' => array('any', 'mandatory' => false),
		'returnURL' => array('string', 'mandatory' => false)
	),
	'POST' => array(
		'category' => array('int', 'default' => 0),
		'search' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
if (false) {
	fetchConfigVal();
}
$isKeyword = false;
define('__TATTERTOOLS_EDIT__', true);
if (defined('__TATTERTOOLS_POST__'))
	$suri['id'] = 0;
if (!isset($_GET['draft']) || (!$entry = getEntry($owner, $suri['id'], true))) {
	$entry = getEntry($owner, $suri['id'], false);
	if (!$entry)
		respondErrorPage(_t('포스트 정보가 존재하지 않습니다.'));
	$isKeyword = ($entry['category'] == -1);
}

if (isset($_GET['popupEditor'])) {
	require ROOT . '/lib/piece/owner/header8.php';
} else {
	require ROOT . '/lib/piece/owner/header0.php';
}

if (defined('__TATTERTOOLS_POST__')) {
	if (isset($_GET['popupEditor']))
		require ROOT . '/lib/piece/owner/contentMenu81.php';
	else
		require ROOT . '/lib/piece/owner/contentMenu04.php';
	printOwnerEditorScript();
} else {
	if (isset($_GET['popupEditor']))
		require ROOT . '/lib/piece/owner/contentMenu81.php';
	else
		require ROOT . '/lib/piece/owner/contentMenu00.php';
	printOwnerEditorScript($entry['id']);
}
?>
						<script type="text/javascript" src="<?php echo $service['path'];?>/script/generaltag.js"></script>
						<script type="text/javascript" src="<?php echo $service['path'];?>/script/locationtag.js"></script>
						<script type="text/javascript">
							//<![CDATA[
								var enclosured = "<?php echo getEnclosure($entry['id']);?>";
								var originalPermalink = "<?php echo htmlspecialchars($entry['slogan']);?>";
								
								window.onerror = function(errType, errURL,errLineNum) {
									window.status = "Error: " + errType +" (on line " + errLineNum + " of " + errURL + ")";
									return true;
								}
								
								function setEnclosure(value) {
									var filename = value.substring(0, value.indexOf("|"));
									
									if(document.getElementById("fileList").selectedIndex == -1) {
										alert("<?php echo _t('파일을 선택하십시오.');?>");
										return false;
									}
									
									if(!(new RegExp("\.mp3$", "i").test(filename))) {
										alert("<?php echo _t('MP3만 사용할 수 있습니다.');?>");
										return false;
									}
									
									try {
										if(STD.isIE) 
											var uploader = document.getElementById("uploader");
										else 
											var uploader = document.getElementById("uploader2");
									} catch(e) { }
									
									if(filename == enclosured) {
										var order = 0;
										try { uploader.SetVariable("/:enclosure", ""); } catch(e) { }
									}
									else {
										var order = 1;
										try { uploader.SetVariable("/:enclosure", filename); } catch(e) { }
									}
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/attach/enclosure/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										var fileList = document.getElementById("fileList");
										fileList.selectedIndex = -1;
										for(var i=0; i<fileList.length; i++)
											fileList[i].style.backgroundColor = (order == 1 && fileList[i].value.indexOf(filename) == 0) ? "#c6a6e7" : "#fff";
										enclosured = (order == 1) ? filename : "";
									}
									
									request.onError= function () {
									PM.removeRequest(this);
										alert("<?php echo _t('변경하지 못했습니다.');?>");
									}
									PM.addRequest(request, "<?php echo _t('변경하고 있습니다.');?>");
									request.send("fileName=" + encodeURIComponent(filename) + "&order=" + order);
								}
								
								function EntryManager() {
									this.savedData = null;
<?php

if (defined('__TATTERTOOLS_POST__')) {
?>
									this.isSaved = false;

<?php

} else {

?>
									this.isSaved = true;

<?php

}

?>

									this.entryId = <?php echo $entry['id'];?>;

									this.pageHolder = new PageHolder(false, "<?php echo _t('아직 저장되지 않았습니다.');?>");
									this.pageHolder.isHolding = function () {
										return (entryManager.savedData != entryManager.getData());
									}
									this.delay = false;
									this.nowsaving = false;
									this.getData = function (check) {
										if (check == undefined)
											check = false;
										var oForm = document.forms[0];
										
										var title = trim(oForm.title.value);
										var permalink = trim(oForm.permalink.value);
										if (check && (title.length == 0)) {
											alert("<?php echo _t('제목을 입력해 주십시오.');?>");
											oForm.title.focus();
											return null;
										}
										
										var visibility = 0;
										for (var i = 0; i < oForm.visibility.length; i++) {
											if (oForm.visibility[i].checked) {
												visibility = oForm.visibility[i].value;
												break;
											}
										}

										var entrytype = 0;
										for (var i = 0; i < oForm.entrytype.length; i++) {
											if (oForm.entrytype[i].checked) {
												entrytype = oForm.entrytype[i].value;
												break;
											}
										}

										try {
											if (editor.editMode == "WYSIWYG")
												oForm.content.value = editor.html2ttml(editor.contentDocument.body.innerHTML);
										} catch(e) {
										}
										var content = trim(oForm.content.value);
										if (check && (content.length == 0)) {
											alert("<?php echo _t('본문을 입력해 주십시오.');?>");
											return null;
										}
											
										var locationValue = "/";
										try {
											locationValue = oLocationTag.getValues();
										} catch(e) {
											locationValue = oForm.location.value;
										}
								
										var tagValue = "";
										try {
											tagValue = oTag.getValues().join(",");
										} catch (e) {
											tagValue = oForm.tag.value;
										}

										var published = 0;
										for (var i = 0; i < oForm.published.length; i++) {
											if (oForm.published[i].checked) {
												published = oForm.published[i].value;
												break;
											}
										}
										if (published == 2) {
											published = Date.parse(oForm.appointed.value);
											if (isNaN(published)) {
												if (check)
													alert("<?php echo _t('등록 예약 시간이 올바르지 않습니다.');?>");
												return null;
											}
											published = Math.floor(published / 1000);
										}
										return (
											"visibility=" + visibility +
											"&title=" + encodeURIComponent(title) +
											"&permalink=" + encodeURIComponent(permalink) +
											"&content=" + encodeURIComponent(content) +
											"&published=" + published +
											"&category=" + ((entrytype!=0) ? entrytype : oForm.category.value) +
											"&location=" + encodeURIComponent(locationValue) +
											"&tag=" + encodeURIComponent(tagValue) +
											"&acceptComment=" + (oForm.acceptComment.checked ? 1 : 0) +
											"&acceptTrackback=" + (oForm.acceptTrackback.checked ? 1 : 0)
										);
									}
									
									this.setEnclosure = function(fileName) {
										
									}
									
									this.save = function () {
										var data = this.getData(true);
										if (data == null)
											return false;
										this.nowsaving = true;
										if(entryManager.isSaved == true) {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/update/"+entryManager.entryId);
										} else {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/add/");
										}

										request.message = "<?php echo _t('저장하고 있습니다.');?>";
										request.onSuccess = function () {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
											if(entryManager.isSaved == false) {

												entryManager.entryId = this.getText("/response/entryId");

												entryManager.isSaved = true;
											}

											PM.removeRequest(this);
											entryManager.savedData = this.content;
											if (entryManager.savedData == entryManager.getData())
												entryManager.pageHolder.release();
										}
										request.onError = function () {
											PM.removeRequest(this);
											alert("<?php echo _t('저장하지 못했습니다.');?>");
										}
										PM.addRequest(request, "<?php echo _t('저장하고 있습니다.');?>");
										request.send(this.getData());
									}
																		
									this.saveAndReturn = function () {
										var data = this.getData(true);
										if (data == null)
											return false;
										this.nowsaving = true;

										if(entryManager.isSaved == true) {

											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/update/"+entryManager.entryId);
										} else {

											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/add/");
										}

										request.message = "<?php echo _t('저장하고 있습니다.');?>";
										request.onSuccess = function () {
											entryManager.pageHolder.isHolding = function () {
												return false;
											}
											PM.removeRequest(this);
											var returnURI = "";
											var oForm = document.forms[0];
											var changedPermalink = trim(oForm.permalink.value);
<?php
if (isset($_GET['popupEditor'])) {
?>
											opener.location.href = opener.location.href;
											window.close();
<?php
} else if (isset($_GET['returnURL'])) {
?>
											if(originalPermalink == changedPermalink) {
												returnURI = "<?php echo escapeJSInCData($_GET['returnURL']);?>";
											} else {
												returnURI = "<?php echo escapeJSInCData("$blogURL/" . $entry['id']);?>";
											}
											window.location = returnURI;
<?php
} else {
?>
											window.location.href = "<?php echo $blogURL;?>/owner/entry";
<?php
}
?>
										}
										request.onError = function () {
											PM.removeRequest(this);
											alert("<?php echo _t('저장하지 못했습니다.');?>");
										}
										PM.addRequest(request, "<?php echo _t('저장하고 있습니다.');?>");
										request.send(this.getData());
									}
									this.saveAuto = function () {
										if (this.timer == null)
											this.timer = window.setTimeout("entryManager.saveDraft()", 5000);
										else
											this.delay = true;
									}
									this.saveDraft = function () {
										var data = this.getData();
										if ((data == null) || (data == this.savedData) || (this.nowsaving == true))
											return;
										this.timer = null;
										if (this.delay) {
											this.delay = false;
											this.timer = window.setTimeout("entryManager.saveDraft()", 5000);
											return;
										}
										
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/draft/<?php echo $entry['id'];?>");
										request.onSuccess = function () {
											PM.showMessage("<?php echo _t('자동으로 임시 저장되었습니다.');?>", "center", "bottom");
											entryManager.savedData = this.content;
											if (entryManager.savedData == entryManager.getData())
												entryManager.pageHolder.release();
										}
										request.send(data);
									}
									this.preview = function () {
										var data = this.getData();
										if (data == null)
											return;

										if (data == this.savedData) {
											window.open("<?php echo $blogURL;?>/owner/entry/preview/"+entryManager.entryId, "previewEntry"+entryManager.entryId, "location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
											return;
										}
										
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/draft/<?php echo $entry['id'];?>");
										request.async = false;
										request.message = "<?php echo _t('미리보기를 준비하고 있습니다.');?>";
										request.onSuccess = function () {
											entryManager.savedData = this.content;
											window.open("<?php echo $blogURL;?>/owner/entry/preview/<?php echo $entry['id'];?>", "previewEntry<?php echo $entry['id'];?>", "location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
										}
										request.onError = function () {
										}
										request.send(data);
									}
									this.savedData = this.getData();
								}
								var entryManager;

								function keepSessionAlive() {
									var request = new HTTPRequest("<?php echo $blogURL;?>/owner/keep/");
									request.persistent = false;
									request.onVerify = function () {
										return true;
									}
									request.send();
								}
								window.setInterval("keepSessionAlive()", 600000);
								
								function changeEditorMode() {
									editWindow = document.getElementById("editWindow");
									var indicatorMode = document.getElementById("indicatorMode");
									
									if (editWindow.style.display == "block" || editWindow.style.display == "inline") {
										if (document.getElementById("visualEditorWindow")) {
											indicatorMode.className = indicatorMode.className.replace("inactive-class", "active-class");
											indicatorMode.innerHTML = '<span class="text"><?php echo _t('HTML 모드');?><\/span>';
											indicatorMode.setAttribute("title", "<?php echo _t('클릭하시면 WYSIWYG 모드로 변경합니다.');?>");
										} else {
											indicatorMode.className = indicatorMode.className.replace("inactive-class", "active-class");
											indicatorMode.innerHTML = '<span class="text"><?php echo _t('HTML 모드');?><\/span>';
											indicatorMode.removeAttribute("title");
										}
									} else {
										indicatorMode.className = indicatorMode.className.replace("active-class", "inactive-class");
										indicatorMode.setAttribute("title", "<?php echo _t('클릭하시면 HTML 모드로 변경합니다.');?>");
										indicatorMode.innerHTML = '<span class="text"><?php echo _t('WYSIWYG 모드');?><\/span>';
									}
								}
								
								function changeButtonStatus(obj, palette) {
									if (!document.getElementById('indicatorColorPalette').className.match('inactive-class')) {
										document.getElementById('indicatorColorPalette').className = document.getElementById('indicatorColorPalette').className.replace('active-class', 'inactive-class');
									}
									if (!document.getElementById('indicatorMarkPalette').className.match('inactive-class')) {
										document.getElementById('indicatorMarkPalette').className = document.getElementById('indicatorMarkPalette').className.replace('active-class', 'inactive-class');
									}
									if (!document.getElementById('indicatorTextBox').className.match('inactive-class')) {
										document.getElementById('indicatorTextBox').className = document.getElementById('indicatorTextBox').className.replace('active-class', 'inactive-class');
									}
									
									if (obj != null) {
										if (document.getElementById(palette).style.display == "block") {
											obj.className = obj.className.replace('inactive-class', 'active-class');
										} else {
											if (!obj.className.match('inactive-class')) {
												obj.className = obj.className.replace('active-class', 'inactive-class');
											}
										}
									}
								}
								
								function checkCategory(type) {
									switch(type) {
										case "type_keyword":
											document.getElementById("title-line-label").innerHTML = "<?php echo _t('키워드');?>";
											document.getElementById("category").disabled = true;
											break;
										case "type_notice":
											document.getElementById("title-line-label").innerHTML = "<?php echo _t('제목');?>";
											document.getElementById("category").disabled = true;
											break;
										case "type_post":
											document.getElementById("title-line-label").innerHTML = "<?php echo _t('제목');?>";
											document.getElementById("category").disabled = false;
									}
									if(type == "type_keyword") {
										var radio = document.forms[0].visibility;
										if(radio[1].checked)
											radio[0].checked = true;
										if(radio[3].checked)
											radio[2].checked = true;
										document.getElementById("permalink-line").style.display = "none";
										document.getElementById("status-protected").style.display = "none";
										document.getElementById("status-syndicated").style.display = "none";
										document.getElementById("power-line").style.display = "none";
									}
									else {
										document.getElementById("permalink-line").style.display = "";
										document.getElementById("status-protected").style.display = "";
										document.getElementById("status-syndicated").style.display = "";
										document.getElementById("power-line").style.display = "";
									}
								}
							//]]>
						</script>
						<form id="editor-form" method="post" action="<?php echo $blogURL;?>/owner/entry">
							<div id="part-editor" class="part">
								<h2 class="caption"><span class="main-text"><?php


if (defined('__TATTERTOOLS_POST__')) {
	echo _t('글을 작성합니다');
} else {
	echo _t('선택한 글을 수정합니다');
}
?></span></h2>
									
								<div id="editor" class="data-inbox">
									<div id="title-section" class="section">
										<h3><?php echo _t('머리말');?></h3>
										
										<dl id="title-line" class="line">
											<dt><label for="title" id="title-line-label"><?php echo $isKeyword ? _t('키워드') : _t('제목');?></label></dt>
											<dd>
												<input type="text" id="title" class="input-text" name="title" value="<?php echo htmlspecialchars($entry['title']);?>" onkeypress="return preventEnter(event);" size="60" />
											</dd>
										</dl>
										<dl id="category-line" class="line">
											<dt><label for="permalink"><?php echo _t('분류');?></label></dt>
											<dd>
												<div class="entrytype-notice"><input type="radio" id="type_notice" class="radio" name="entrytype" value="-2" onclick="checkCategory('type_notice')"<?php echo ($entry['category'] == -2 ? ' checked="checked"' : '');?> /><label for="type_notice"><?php echo _t('공지');?></label></div>
												<div class="entrytype-keyword"><input type="radio" id="type_keyword" class="radio" name="entrytype" value="-1" onclick="checkCategory('type_keyword')"<?php echo ($entry['category'] == -1 ? ' checked="checked"' : '');?> /><label for="type_keyword"><?php echo _t('키워드');?></label></div>
												<div class="entrytype-post">
													<input type="radio" id="type_post" class="radio" name="entrytype" value="0" onclick="checkCategory('type_post')"<?php echo ($entry['category'] >= 0 ? ' checked="checked"' : '');?> /><label for="type_post"><?php echo _t('글');?></label>
													<select id="category" name="category"<?php if($isKeyword) echo ' disabled="disabled"';?>>
														<option value="0"><?php echo htmlspecialchars(getCategoryNameById($owner,0) ? getCategoryNameById($owner,0) : _t('전체'));?></option>
<?php
		foreach (getCategories($owner) as $category) {
			if ($category['id']!= 0) {
?>
														<option value="<?php echo $category['id'];?>"<?php echo ($category['id'] == $entry['category'] ? ' selected="selected"' : '');?>><?php echo ($category['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($category['name']);?></option>
<?php
			}
			foreach ($category['children'] as $child) {
				if ($category['id']!= 0) {
?>
														<option value="<?php echo $child['id'];?>"<?php echo ($child['id'] == $entry['category'] ? ' selected="selected"' : '');?>>&nbsp;― <?php echo ($category['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($child['name']);?></option>
<?php
				}
			}
		}
?>
													</select>
												</div>
											</dd>
										</dl>
									</div>
									
									<div id="textarea-section" class="section">
										<h3><?php echo _t('본문');?></h3>
											
<?php
printEntryEditorPalette();
?>
										<div id="editor-textbox" class="container">
											<textarea id="editWindow" name="content" cols="80" rows="20" onselect="savePosition(); editorChanged()" onclick="savePosition();editorChanged()" onkeyup="savePosition();editorChanged()"><?php echo htmlspecialchars($entry['content']);?></textarea>
											<script type="text/javascript" src="<?php echo $service['path'];?>/script/editor2.js"></script>
											<script type="text/javascript">
												//<![CDATA[
													var editor = new TTEditor();
													editor.initialize(document.getElementById("editWindow"), "<?php echo $service['path'];?>/attach/<?php echo $owner;?>/", "<?php echo getUserSetting('editorMode', 1) == 1 ? 'WYSIWYG' : 'TEXTAREA';?>", "<?php echo true ? 'BR' : 'P';?>");
												//]]>
											</script>
										</div>
										<div id="status-container" class="container"><span id="pathStr"><?php echo _t('path');?></span><span class="divider"> : </span><span id="pathContent"></span></div>
<?php
	$view = fireEvent('AddPostEditorToolbox', '');
	if (!empty($view)) {
?>
										<div id="toolbox-container" class="container"><?php echo $view;?></div>
<?php
	}
?>
									</div>
									
									<hr class="hidden" />
									
									<div id="property-section" class="section">
										<h3><?php echo _t('속성 상자');?></h3>
										
										<div id="property-container" class="container">
<?php
printEntryEditorProperty();
?>
										</div>
									</div>

									<hr class="hidden" />
									
									<div id="taglocal-section" class="section">
										<h3><?php echo _t('태그 &amp; 위치');?></h3>
												
										<div id="tag-location-container" class="container">
											<dl id="tag-line">
												<dt><span class="label"><?php echo _t('태그');?></span></dt>
												<dd id="tag"></dd>
											</dl>
											
											<dl id="location-line">
												<dt><span class="label"><?php echo _t('지역');?></span></dt>
												<dd id="location"></dd>
											</dl>
											
											<script type="text/javascript">
												//<![CDATA[
													try {
														var oLocationTag = new LocationTag(document.getElementById("location"), "<?php echo $blog['language'];?>", <?php echo isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false';?>);
														oLocationTag.setInputClassName("input-text");
														oLocationTag.setValue("<?php echo addslashes($entry['location']);?>");	
													} catch (e) {
														document.getElementById("location").innerHTML = '<input type="text" class="input-text" name="location" value="<?php echo addslashes($entry['location']);?>" /><br /><?php echo _t('지역태그 스크립트를 사용할 수 없습니다. 슬래시(/)로 구분된 지역을 직접 입력해 주십시오.(예: /대한민국/서울/강남역)');?>';
														// TODO : 이부분(스크립트를 실행할 수 없는 환경일 때)은 직접 입력보다는 0.96 스타일의 팝업이 좋을 듯
													}
													
													try {
														var oTag = new Tag(document.getElementById("tag"), "<?php echo $blog['language'];?>", <?php echo isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false';?>);
														oTag.setInputClassName("input-text");
<?php
		$tags = array();
		if (!defined('__TATTERTOOLS_POST__')) {
			foreach (getTags($entry['id']) as $tag) {
				array_push($tags, $tag['name']);
				echo 'oTag.setValue("' . addslashes($tag['name']) . '");';
			}
		}
?>
													} catch(e) {
														document.getElementById("tag").innerHTML = '<input type="text" class="input-text" name="tag" value="<?php echo addslashes(str_replace('"', '&quot;', implode(', ', $tags)));?>" /><br /><?php echo _t('태그 입력 스크립트를 사용할 수 없습니다. 콤마(,)로 구분된 태그를 직접 입력해 주십시오.(예: 태터툴즈, BLOG, 테스트)');?>';
													}
												//]]>
											</script> 
										</div>
									</div>
									
									<hr class="hidden" />
									
									<div id="upload-section" class="section">
										<h3><?php echo _t('업로드');?></h3>
										
										<div id="attachment-container" class="container">
<?php
$param = array(
		'uploadPath'=> "$blogURL/owner/entry/attachmulti/{$entry['id']}", 
		'singleUploadPath'=> "$blogURL/owner/entry/attach/{$entry['id']}", 
		'deletePath'=>"$blogURL/owner/entry/detach/multi/". ($entry['id'] ? $entry['id'] : '0') ,
		'labelingPath'=> "$blogURL/owner/entry/attachmulti/list/{$entry['id']}", 
		'refreshPath'=> "$blogURL/owner/entry/attachmulti/refresh/". ($entry['id'] ? $entry['id'] : '0') , 
		'fileSizePath'=> "$blogURL/owner/entry/size?parent={$entry['id']}");		
printEntryFileList(getAttachments($owner, $entry['id'], 'label'), $param);
?>
										</div>
										
										<div id="insert-container" class="container">
											<a class="image-left" href="#void" onclick="linkImage1('1L')" title="<?php echo _t('선택한 파일을 글의 왼쪽에 정렬합니다.');?>"><span class="text"><?php echo _t('왼쪽 정렬');?></span></a>
											<a class="image-center" href="#void" onclick="linkImage1('1C')" title="<?php echo _t('선택한 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬');?></span></a>
											<a class="image-right" href="#void" onclick="linkImage1('1R')" title="<?php echo _t('선택한 파일을 글의 오른쪽에 정렬합니다.');?>"><span class="text"><?php echo _t('오른쪽 정렬');?></span></a>
											<a class="image-2center" href="#void" onclick="linkImage2()" title="<?php echo _t('선택한 두개의 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬(2 이미지)');?></span></a>
											<a class="image-3center" href="#void" onclick="linkImage3()" title="<?php echo _t('선택한 세개의 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬(3 이미지)');?></span></a>
											<a class="image-free" href="#void" onclick="linkImageFree()" title="<?php echo _t('선택한 파일을 글에 삽입합니다. 문단의 모양에 영향을 주지 않습니다.');?>"><span class="text"><?php echo _t('파일 삽입');?></span></a>
											<a class="image-imazing" href="#void" onclick="viewImazing()" title="<?php echo _t('이메이징(플래쉬 갤러리)을 삽입합니다.');?>"><span class="text"><?php echo _t('이메이징(플래쉬 갤러리) 삽입');?></span></a>
											<a class="image-sequence" href="#void" onclick="viewGallery()" title="<?php echo _t('이미지 갤러리를 삽입합니다.');?>"><span class="text"><?php echo _t('갤러리 삽입');?></span></a>
											<a class="image-mp3" href="#void" onclick="viewJukebox()" title="<?php echo _t('쥬크박스를 삽입합니다.');?>"><span class="text"><?php echo _t('쥬크박스 삽입');?></span></a>
											<a class="image-podcast" href="#void" onclick="setEnclosure(document.getElementById('fileList').value)" title="<?php echo _t('팟캐스트로 지정합니다.');?>"><span class="text"><?php echo _t('팟캐스트 지정');?></span></a>
										</div>
<?php
printEntryFileUploadButton($entry['id']);
?>
									</div>
									
									<hr class="hidden" />
									
									<div id="power-section" class="section">
										<div id="power-container" class="container">
											<dl id="permalink-line" class="line"<?php if($isKeyword) echo _t('style="display: none"');?>>
												<dt><label for="permalink"><?php echo _t('절대 주소');?></label></dt>
												<dd>
													<samp><?php echo _f('%1/entry/', link_cut(getBlogURL()));?></samp><input type="text" id="permalink" class="input-text" name="permalink" onkeypress="return preventEnter(event);" value="<?php echo htmlspecialchars($entry['slogan']);?>" />
													<p>* <?php echo _t('입력하지 않으면 글의 제목이 절대 주소가 됩니다.');?></p>
												</dd>
											</dl>
											<dl id="date-line" class="line">
												<dt><span class="label"><?php echo _t('등록일자');?></span></dt>
												<dd>
<?php
if (defined('__TATTERTOOLS_POST__')) {
?>
													<div class="publish-update"><input type="radio" id="publishedUpdate" class="radio" name="published" value="1" checked="checked" /><label for="publishedUpdate"><?php echo _t('갱신');?></label></div>
<?php
} else {
?>
													<div class="publish-nochange"><input type="radio" id="publishedNoChange" class="radio" name="published" value="0" <?php echo (!isset($entry['republish']) && !isset($entry['appointed']) ? 'checked="checked"' : '');?> /><label for="publishedNoChange"><?php echo _t('유지');?> (<?php echo Timestamp::format5($entry['published']);?>)</label></div>
													<div class="publish-update"><input type="radio" id="publishedUpdate" class="radio" name="published" value="1" <?php echo (isset($entry['republish']) ? 'checked="checked"' : '');?> /><label for="publishedUpdate"><?php echo _t('갱신');?></label></div>
<?php
}
?>
													<div class="publish-preserve">
														<input type="radio" id="publishedPreserve" class="radio" name="published" value="2" <?php echo (isset($entry['appointed']) ? 'checked="checked"' : '');?> /><label for="publishedPreserve" onclick="document.getElementById('appointed').select()"><?php echo _t('예약');?></label>
														<input type="text" id="appointed" class="input-text" name="appointed" value="<?php echo Timestamp::format5(isset($entry['appointed']) ? $entry['appointed'] : $entry['published']);?>" onfocus="document.forms[0].published[document.forms[0].published.length - 1].checked = true" onkeypress="return preventEnter(event);" />
													</div>
												</dd>
											</dl>
											<dl id="status-line" class="line">
												<dt><span class="label"><?php echo _t('공개여부');?></span></dt>
												<dd>
													<div id="status-private" class="status-private"><input type="radio" id="visibility_private" class="radio" name="visibility" value="0"<?php echo (abs($entry['visibility']) == 0 ? ' checked="checked"' : '');?> /><label for="visibility_private"><?php echo _t('비공개');?></label></div>
													<div id="status-protected" class="status-protected"<?php if($isKeyword) echo _t('style="display: none"');?>><input type="radio" id="visibility_protected" class="radio" name="visibility" value="1"<?php echo (abs($entry['visibility']) == 1 ? ' checked="checked"' : '');?> /><label for="visibility_protected"><?php echo _t('보호');?></label></div>
													<div id="status-public" class="status-public"><input type="radio" id="visibility_public" class="radio" name="visibility" value="2"<?php echo (abs($entry['visibility']) == 2 ? ' checked="checked"' : '');?> /><label for="visibility_public"><?php echo _t('공개');?></label></div>
													<div id="status-syndicated" class="status-syndicated"<?php if($isKeyword) echo _t('style="display: none"');?>><input type="radio" id="visibility_syndicated" class="radio" name="visibility" value="3"<?php echo (abs($entry['visibility']) == 3 ? ' checked="checked"' : '');?> /><label for="visibility_syndicated"><?php echo _t('발행');?></label></div>
												</dd>
											</dl>
											<dl id="power-line" class="line"<?php if($isKeyword) echo _t('style="display: none"');?>>
												<dt><span class="label"><?php echo _t('권한');?></span></dt>
												<dd>
													<div class="comment-yes"><input type="checkbox" id="acceptComment" class="checkbox" name="acceptComment"<?php echo ($entry['acceptComment'] ? ' checked="checked"' : '');?> /><label for="acceptComment"><span class="text"><?php echo _t('댓글 작성을 허용합니다.');?></span></label></div>
												  	<div class="trackback-yes"><input type="checkbox" id="acceptTrackback" class="checkbox" name="acceptTrackback"<?php echo ($entry['acceptTrackback'] ? ' checked="checked"' : '');?> /><label for="acceptTrackback"><span class="text"><?php echo _t('글을 걸 수 있게 합니다.');?></span></label></div>
												</dd>
											</dl>
										</div>
									</div>
								</div>
								
								<hr class="hidden" />
										
<?php
if (isset($_GET['popupEditor'])) {
?>
								<div class="button-box two-button-box">
									<input type="button" value="<?php echo _t('미리보기');?>" class="preview-button input-button" onclick="entryManager.preview();return false;" />
									<span class="hidden">|</span>
									<input type="submit" value="<?php echo _t('저장하기');?>" class="save-button input-button" onclick="entryManager.save();return false;" />
									<span class="hidden">|</span>
									<input type="submit" value="<?php echo _t('완료하기');?>" class="save-and-return-button input-button" onclick="entryManager.saveAndReturn();return false;" />									
								</div>
<?php
} else {
?>
								<div class="button-box three-button-box">
									<input type="button" value="<?php echo _t('미리보기');?>" class="preview-button input-button" onclick="entryManager.preview();return false;" />
									<span class="hidden">|</span>
							       	<input type="submit" value="<?php echo _t('저장하기');?>" class="save-button input-button" onclick="entryManager.save();return false;" />
									<span class="hidden">|</span>
							       	<input type="submit" value="<?php echo _t('완료하기');?>" class="save-and-return-button input-button" onclick="entryManager.saveAndReturn();return false;" />
									<span class="hidden">|</span>
									<input type="submit" value="<?php echo _t('목록으로');?>" class="list-button input-button" onclick="window.location.href='<?php echo $blogURL;?>/owner/entry'" />
								</div>
<?php
}
?>
								<input type="hidden" name="categoryAtHome" value="<?php echo (isset($_POST['category']) ? $_POST['category'] : '0');?>" />
								<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
								<input type="hidden" name="withSearch" value="<?php echo (empty($_POST['search']) ? '' : 'on');?>" />
								<input type="hidden" name="search" value="<?php echo (isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '');?>" />
							</div>
						</form>
						<script type="text/javascript">
							//<![CDATA[
								entryManager = new EntryManager();
							//]]>
						</script> 
<?php
if (isset($_GET['popupEditor']))
	require ROOT . '/lib/piece/owner/footer8.php';
else
	require ROOT . '/lib/piece/owner/footer1.php';
?>
