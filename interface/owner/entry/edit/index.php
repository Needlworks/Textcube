<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'draft'       => array('any', 'mandatory' => false),
		'popupEditor' => array('any', 'mandatory' => false),
		'returnURL'   => array('string', 'mandatory' => false),
		'slogan'      => array('string', 'mandatory' => false),
		'category'    => array('int', 'default' => 0),
		'editor'      => array('string', 'mandatory' => false)
	),
	'POST' => array(
		'category'  => array('int', 'default' => 0),
		'search'    => array('string', 'default' => ''),
		'slogan'    => array('string', 'mandatory' => false),
		'returnURL' => array('string', 'mandatory' => false)
	)
);

require ROOT . '/library/preprocessor.php';
requireModel("blog.entry");
requireModel("blog.tag");
requireModel("blog.locative");
requireModel("blog.attachment");

$context = Model_Context::getInstance();

$isKeyword = false;
define('__TEXTCUBE_EDIT__', true);
if (defined('__TEXTCUBE_POST__'))
	$suri['id'] = 0;

if (isset($_GET['draft'])) {
	$entry = getEntry(getBlogId(), $suri['id'], true);
} else {
	$entry = getEntry(getBlogId(), $suri['id'], false);
}
if (is_null($entry)) {
	Respond::ErrorPage(_t('포스트 정보가 존재하지 않습니다.'));
	$isKeyword = ($entry['category'] == -1);
}

if (defined('__TEXTCUBE_POST__') && isset($_GET['category'])) {
	$entry['category'] = $_GET['category'];
}

if(isset($_POST['slogan'])) $_GET['slogan'] = $_POST['slogan'];
if(defined('__TEXTCUBE_ADD__') && (isset($_GET['slogan']))) {
	$entry['slogan'] = $_GET['slogan'];
}

// Check whether or not user has permission to edit.
if(Acl::check('group.editors')===false && !empty($suri['id'])) {
	if(getUserIdOfEntry(getBlogId(), $suri['id']) != getUserId()) {
		@header("location:".$context->getProperty('uri.blog') ."/owner/entry");
		exit;
	}
}
// Read editor configuration
$editors = getAllEditors();
if (isset($_GET['editor']) && in_array($_GET['editor'],array_keys($editors))) {
	$entry['contenteditor'] = $_GET['editor'];
}

$context->setProperty('editor.key',$entry['contenteditor']);
$context->setProperty('formatter.key',$entry['contentformatter']);

if (isset($_GET['popupEditor'])) {
	require ROOT . '/interface/common/owner/headerForPopupEditor.php';
} else {
	require ROOT . '/interface/common/owner/header.php';
}

if (isset($_POST['returnURL']) && !empty($_POST['returnURL'])) {
	$_GET['returnURL'] = $_POST['returnURL'];
}
switch($entry['category']) {
	case -1:
		$titleText = _t('키워드');
		break;
	case -2:
		$titleText = _t('공지');
		break;
	case -4:
		$titleText = _t('서식');
		break;
	default:
		$titleText = _t('글');
}

if (defined('__TEXTCUBE_POST__')) {
	printOwnerEditorScript();
} else {
	printOwnerEditorScript($entry['id']);
}
?>
						<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/generaltag.js"></script>
						<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/locationtag.js"></script>
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

									if(document.getElementById("TCfilelist").selectedIndex == -1) {
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

									var request = new HTTPRequest("POST", "<?php echo $context->getProperty('uri.blog');?>/owner/entry/attach/enclosure/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										var fileList = document.getElementById("TCfilelist");
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

								var star = <?php echo ($entry['starred'] == 2 ? 'true' : 'false');?>;

								function setStar() {
									if(star == true) {
										star = false;
										document.getElementById("starred").className = 'unstar-icon';
									} else {
										star = true;
										document.getElementById("starred").className = 'star-icon';
									}
									return true;
								}

								function EntryManager() {
									var self = this;
									self.savedData = null;
<?php
if (defined('__TEXTCUBE_POST__')) {
?>
									self.isSaved   = false;
<?php
} else {
?>
									self.isSaved   = true;
<?php
}
?>
									self.autoSave  = false;
									self.delay     = false;
									self.nowsaving = false;
									self.isPreview   = false;
									self.changeEditor = false;
									self.draftSaved = false;
									self.currentEditor = "<?php echo $entry['contenteditor'];?>";
									self.entryId   = <?php echo $entry['id'];?>;

									self.pageHolder = new PageHolder(false, "<?php echo _t('아직 저장되지 않았습니다.');?>");

									self.pageHolder.isHolding = function () {
										return (self.savedData != self.getData(true));
									}
<?php
if (isset($_GET['returnURL'])) {
?>
									self.returnURL = "<?php echo escapeJSInCData($_GET['returnURL']);?>";
<?php
} else {
?>
									self.returnURL = null;
<?php
}
?>
									self.getData = function (check) {
										if (check == undefined)
											check = false;
										var oForm = document.getElementById('editor-form');

										var title = trim(oForm.title.value);
										var permalink = trim(oForm.permalink.value);
										if (check && (title.length == 0)) {
											if(self.autoSave == true) {
												title = trim("<?php echo _t('[자동 저장 문서]');?>");
												oForm.title.value = title;
												permalink = "TCDraftPost";
												oForm.permalink.value = permalink;
											} else {
												alert("<?php echo _t('제목을 입력해 주십시오.');?>");
												oForm.title.focus();
												return null;
											}
										} else if (title != trim("<?php echo _t('[자동 저장 문서]');?>")) {
											if(permalink.indexOf("TCDraftPost") != -1) {
												permalink = "";
												oForm.permalink.value = permalink;
											}
										}
										var visibility = 0;
										for (var i = 0; i < oForm.visibility.length; i++) {
											if (oForm.visibility[i].checked) {
												visibility = oForm.visibility[i].value;
												break;
											}
										}
										var starred  = 2;
										if(star == true) starred = 2;
										else starred = 0;

										var entrytype = 0;
										for (var i = 0; i < oForm.entrytype.length; i++) {
											if (oForm.entrytype[i].checked) {
												entrytype = oForm.entrytype[i].value;
												break;
											}
										}

										try {
											editor.syncTextarea();
										} catch(e) {
										}
										var content = trim(oForm.content.value);
										if (check && (content.length == 0)) {
											if (self.changeEditor == true) {
												content = "&nbsp;";
												oForm.elements["content"].value = "&nbsp;";
											} else if(self.autoSave == true) {
												return null;
											} else {
												alert("<?php echo _t('본문을 입력해 주십시오.');?>");
												return null;
											}
										}

										var locationValue = "/";
										try {
											locationValue = oLocationTag.getValues();
										} catch(e) {
											locationValue = oForm.location.value;
										}

										var latitudeValue = "";
										try {
											latitudeValue = jQuery('input[name=latitude]').val()
										} catch(e) {}
										if(latitudeValue == undefined) {
											latitudeValue = null;
										}
										var longitudeValue = "";
										try {
											longitudeValue = jQuery('input[name=longitude]').val()
										} catch(e) {}
										if(longitudeValue == undefined) {
											longitudeValue = null;
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
											"&starred=" + starred +
											"&title=" + encodeURIComponent(title) +
											"&permalink=" + encodeURIComponent(permalink) +
											"&content=" + encodeURIComponent(content) +
											"&contentformatter=" + encodeURIComponent(oForm.contentformatter.value) +
											"&contenteditor=" + encodeURIComponent(oForm.contenteditor.value) +
											"&published=" + published +
											"&category=" + ((entrytype!=0) ? entrytype : oForm.category.value) +
											"&location=" + encodeURIComponent(locationValue) +
											"&latitude=" + encodeURIComponent(latitudeValue) +
											"&longitude=" + encodeURIComponent(longitudeValue) +
											"&tag=" + encodeURIComponent(tagValue) +
											"&acceptcomment=" + (oForm.acceptcomment.checked ? 1 : 0) +
											"&accepttrackback=" + (oForm.accepttrackback.checked ? 1 : 0)
										);
									}

									self.setEnclosure = function(fileName) {

									}
									self.loadTemplate = function (templateId,title) {
										editor.syncTextarea();
										var oForm = document.forms[0];
										var content = trim(oForm.content.value);
										if (content.length != 0) {
											if(confirm("<?php echo _t('본문에 내용이 있습니다. 서식이 현재 본문 내용을 덮어쓰게 됩니다. 계속하시겠습니까?');?>")!=1)
												return null;
										}

										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/loadTemplate/");
										request.message = "<?php echo _t('불러오고 있습니다');?>";
										request.onSuccess = function () {
											PM.removeRequest(this);
											PM.showMessage("<?php echo _t('서식을 반영하였습니다.');?>", "center", "bottom");
											templateTitle = this.getText("/response/title");
											templateContents = this.getText("/response/content");
											self.entryId = this.getText("/response/entryId");
											self.isSaved = true;
											var title = trim(oForm.title.value);
											if(title.length == 0) {
												oForm.title.value = templateTitle;
											}
											oForm.content.value = templateContents;
											reloadUploader();
											try {
												editor.syncEditorWindow();
											} catch(e) {
											}
										}
										request.onError = function() {
											PM.removeRequest(this);
											alert("<?php echo _t('불러오지 못했습니다');?>");
										}
										PM.addRequest(request, "<?php echo _t('불러오고 있습니다');?>");
										request.send("templateId="+templateId
											+"&isSaved="+self.isSaved
											+"&entryId="+self.entryId);
									}
									self.saveEntry = function () {
										if(self.nowsaving == true)
											return false;
										self.nowsaving = true;
										var data = self.getData(true);
										if (data == null) {
											self.nowsaving = false;
											return false;
										}
										if (data == self.savedData) {
											self.nowsaving = false;
											return false;
										}
										if(self.isSaved == true) {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/draft/"+self.entryId);
										} else {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/add/");
										}
										if(self.autoSave != true) {
											request.message = "<?php echo _t('저장하고 있습니다.');?>";
										}
										request.onSuccess = function () {
											PM.removeRequest(this);
											if(self.autoSave == true) {
												document.getElementById("saveButton").value = "<?php echo _t('자동으로 저장됨');?>";
												document.getElementById("saveButton").style.color = "#BBB";
												self.autoSave = false;
											} else {
												document.getElementById("saveButton").value = "<?php echo _t('저장됨');?>";
												document.getElementById("saveButton").style.color = "#BBB";
												PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											}
											if(self.isSaved == false) {	// First save.
												self.entryId = this.getText("/response/entryId");
												self.isSaved = true;
												self.draftSaved = false;
												reloadUploader();
											} else {
												self.draftSaved = true;
											}

											self.savedData = data;
											if (self.savedData == self.getData(true)) {
												self.pageHolder.release();
											}
											self.nowsaving = false;
											if (self.isPreview == true) {
												self.openPreviewPopup();
												self.isPreview = false;
											}
											if (self.changeEditor == true) {
												reloadEditor();
											}
										}
										request.onError = function () {
											PM.removeRequest(this);
											PM.showErrorMessage("<?php echo _t('저장하지 못했습니다');?>", "center", "bottom");
											self.nowsaving = false;
										}
										if(self.autoSave != true) {
											PM.addRequest(request, "<?php echo _t('저장하고 있습니다.');?>");
										} else {
											PM.addRequest(request);
										}
										document.getElementById("saveButton").value = "<?php echo _t('저장중...');?>";
										request.send(data);

										return true;
									}
									self.openPreviewPopup = function () {
										window.open("<?php echo $blogURL;?>/owner/entry/preview/"+self.entryId, "previewEntry"+self.entryId, "location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0");
									}
									self.saveAndReturn = function () {
										self.nowsaving = true;
										var data = self.getData(true);
										if (data == null)
											return false;
										if(self.isSaved == true) {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/finish/"+self.entryId);
										} else {
											var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/entry/finish/");
										}

										request.message = "<?php echo _t('저장하고 있습니다.');?>";
										request.onSuccess = function () {
											self.pageHolder.isHolding = function () {
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
	if(strpos($_GET['returnURL'],'/owner/entry')!==false) {
?>
											returnURI = "<?php echo escapeJSInCData($_GET['returnURL']);?>";
<?php
	} else {
?>
											if(originalPermalink == changedPermalink) {
												returnURI = "<?php echo escapeJSInCData($_GET['returnURL']);?>";
											} else {
												returnURI = "<?php echo escapeJSInCData("$blogURL/" . $entry['id']);?>";
											}
<?php
	}
?>
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
											alert("<?php echo _t('저장하지 못했습니다');?>");
											self.nowsaving = false;
											document.getElementById("saveAndReturnButton").value = "<?php echo _t('저장 후 돌아가기');?>";

										}
										document.getElementById("saveAndReturnButton").value = "<?php echo _t('저장중...');?>";
										PM.addRequest(request, "<?php echo _t('저장하고 있습니다.');?>");
										request.send(data);
									}
									/// Do postprocessing after editor area is changed first.
									/// e.g. starting writing, clicking, etc.
									self.stateChanged = function () {
										if(document.getElementById('templateDialog').style.display != 'none') {
											toggleTemplateDialog();
										}
										document.getElementById("saveButton").value = "<?php echo _t('중간 저장');?>";
										document.getElementById("saveButton").style.color = "#000";
										/// If draft timer is empty, set draft timer.
										/// If timer runs, set the delay to the timer.
										if (self.timer == null)
											self.timer = window.setTimeout(self.saveDraft, 5000);
										else
											self.delay = true;
									}
									self.saveDraft = function () {
										self.autoSave = true;
										if (self.nowsaving == true) {
											self.timer = null;
											self.autoSave = false;
											return;
										}
										//self.timer = null;
										if (self.changeEditor != true && self.delay) {
											self.delay = false;
											self.autoSave = false;
											self.timer = null;
											return;
										}
										self.saveEntry();
										self.timer = null;
										return;
									}

									self.preview = function () {
										self.isPreview = true;
										if (!self.saveEntry()) {
											self.openPreviewPopup();
											self.isPreview = false;
										}
										return;
									}
									self.savedData = self.getData();
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
										case "type_template":
											document.getElementById("title-line-label").innerHTML = "<?php echo _t('서식이름');?>";
											document.getElementById("category").disabled = true;
											break;
										case "type_post":
											document.getElementById("title-line-label").innerHTML = "<?php echo _t('제목');?>";
											document.getElementById("category").disabled = false;
									}
									if(type == "type_keyword" || type == "type_template") {
										var radio = document.forms[0].visibility;
										if(radio[1].checked)
											radio[0].checked = true;
										if(radio[3].checked)
											radio[2].checked = true;
										document.getElementById("permalink-line").style.display = "none";
										document.getElementById("status-protected").style.display = "none";
										document.getElementById("status-syndicated").style.display = "none";
										document.getElementById("power-line").style.display = "none";
										if(type == "type_template") {
											document.getElementById("date-line").style.display = "none";
											document.getElementById("status-line").style.display = "none";
										}
									}
									else {
										document.getElementById("permalink-line").style.display = "";
										document.getElementById("status-protected").style.display = "";
										document.getElementById("status-syndicated").style.display = "";
										document.getElementById("power-line").style.display = "";
										document.getElementById("date-line").style.display = "";
										document.getElementById("status-line").style.display = "";
									}
									if (type == "type_notice") {
										document.getElementById("permalink-prefix").innerHTML = document.getElementById("permalink-prefix").innerHTML.replace(new RegExp("/entry/$"), "/notice/");
									} else {
										document.getElementById("permalink-prefix").innerHTML = document.getElementById("permalink-prefix").innerHTML.replace(new RegExp("/notice/$"), "/entry/");
									}
									return true;
								}

								function toggleTemplateDialog() {
									if(document.getElementById('templateDialog').style.display != 'none') {
										document.getElementById('templateDialog').style.display = 'none';
									} else {
										document.getElementById('templateDialog').style.display = 'block';
									}
									return false;
								}

								function returnToList() {
									window.location.href='<?php echo $blogURL;?>/owner/entry';
									return true;
								}

							//]]>
						</script>

						<form id="editor-form" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/entry">
							<div id="part-editor" class="part">
								<h2 class="caption"><span class="main-text"><?php

if (defined('__TEXTCUBE_POST__')) {
	echo _f('%1 작성',$titleText);
} else {
	echo _f('선택한 %1 수정',$titleText);
}
?></span></h2>

								<div id="editor" class="data-inbox">
									<div id="title-section" class="section">
										<h3><?php echo _t('머리말');?></h3>

										<dl id="title-line" class="line">
											<dt><label for="title" id="title-line-label"><?php echo $isKeyword ? _t('키워드') : _t('제목');?></label></dt>
											<dd>
												<div id="starred" class="<?php echo ($entry['starred'] == 2 ? 'star-icon' : 'unstar-icon');?>">
													<a href="#void" onclick="setStar(); return false;" title="<?php echo _t('별표를 줍니다.');?>"><span class="text"><?php echo _t('별표');?></span></a>
												</div>
												<input type="text" id="title" class="input-text" name="title" value="<?php echo htmlspecialchars($entry['title']);?>" onkeypress="return preventEnter(event);" size="60" />
											</dd>
										</dl>
									</div>
									<div id="category-section" class="section">
										<h3><?php echo _t('분류');?></h3>
										<dl id="category-line" class="line">
											<dt><label for="category"><?php echo _t('분류');?></label></dt>
											<dd>
												<div class="entrytype-notice"><input type="radio" id="type_notice" class="radio" name="entrytype" value="-2" onclick="checkCategory('type_notice')"<?php echo ($entry['category'] == -2 ? ' checked="checked"' : '');?> /><label for="type_notice"><?php echo _t('공지');?></label></div>
												<div class="entrytype-keyword"><input type="radio" id="type_keyword" class="radio" name="entrytype" value="-1" onclick="checkCategory('type_keyword')"<?php echo ($entry['category'] == -1 ? ' checked="checked"' : '');?> /><label for="type_keyword"><?php echo _t('키워드');?></label></div>
												<div class="entrytype-template"><input type="radio" id="type_template" class="radio" name="entrytype" value="-4" onclick="checkCategory('type_template')"<?php echo ($entry['category'] == -4 ? ' checked="checked"' : '');?> /><label for="type_template"><?php echo _t('서식');?></label></div>
												<div class="entrytype-post">
													<input type="radio" id="type_post" class="radio" name="entrytype" value="0" onclick="checkCategory('type_post')"<?php echo ($entry['category'] >= 0 ? ' checked="checked"' : '');?> /><label for="type_post"><?php echo _t('글');?></label>
													<select id="category" name="category"<?php if($isKeyword) echo ' disabled="disabled"';?>>
														<option value="0"><?php echo htmlspecialchars(getCategoryNameById($blogid,0) ? getCategoryNameById($blogid,0) : _t('전체'));?></option>
<?php
		foreach (getCategories($blogid) as $category) {
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

										<dl class="editoroption">
											<dt><label for="contentformatter"><?php echo _t('포매터');?></label></dt>
											<dd><select id="contentformatter" name="contentformatter" onchange="return setFormatter(this.value, document.getElementById('contenteditor'), changeEditor);">
<?php
	foreach (getAllFormatters() as $key => $formatter) {
?>
												<option value="<?php echo htmlspecialchars($key);?>"<?php echo ($entry['contentformatter'] == $key ? ' selected="selected"' : '');?>><?php echo htmlspecialchars($formatter['name']);?></option>
<?php
	}
?>
											</select></dd>
											<dt><label for="contenteditor"><?php echo _t('편집기');?></label></dt>
											<dd><select id="contenteditor" name="contenteditor" onfocus="return saveEditor(this);" onchange="return setEditor(this) &amp;&amp; changeEditor(this.value);">
<?php
	foreach ($editors as $key => $editor) {
?>
												<option value="<?php echo htmlspecialchars($key);?>"<?php echo ($entry['contenteditor'] == $key ? ' selected="selected"' : '');?>><?php echo htmlspecialchars($editor['name']);?></option>
<?php
	}
?>
											</select></dd>
										</dl>
										<div id="formatbox-container" class="container"></div>
										<div class="editorbox-container"><textarea id="editWindow" name="content" cols="80" rows="20"><?php echo htmlspecialchars($entry['content']);?></textarea></div>
										<div id="status-container" class="container"><span id="pathStr"><?php echo _t('path');?></span><span class="divider"> : </span><span id="pathContent"></span></div>
<?php
	$view = fireEvent('AddPostEditorToolbox', '');
	if (!empty($view)) {
?>
										<div id="toolbox-container" class="container"><?php echo $view;?></div>
<?php
	}
?>

										<div id="templateDialog" class="entry-editor-property<?php echo defined('__TEXTCUBE_POST__') ? NULL : ' hidden';?>">
											<div class="temp-box">
												<h4><?php echo _t('서식 선택');?></h4>

												<p class="message">
													<?php echo _t('새 글을 쓰거나 아래의 서식들 중 하나를 선택하여 글을 쓸 수 있습니다. 서식은 자유롭게 작성하여 저장할 수 있습니다.');?>
												</p>

												<dl>
													<dt><?php echo _t('서식 목록');?></dt>
<?php
$templateLists = getTemplates(getBlogId(),'id,title');
if (count($templateLists) == 0) {
	echo '												<dd class="noItem">' . _t('등록된 서식이 없습니다.') . '</dd>' . CRLF;
} else {
	foreach($templateLists as $templateList) {
		echo '												<dd><a href="#void" onclick="entryManager.loadTemplate('.$templateList['id'].',\''.htmlspecialchars($templateList['title']).'\');return false;">'.htmlspecialchars($templateList['title']).'</a></dd>'.CRLF;
	}
}
?>
												</dl>

												<div class="button-box">
													<button class="close-button input-button" onclick="toggleTemplateDialog();return false;" title="<?php echo _t('이 대화상자를 닫습니다.');?>"><span class="text"><?php echo _t('닫기');?></span></button>
									 			</div>
									 		</div>
								 		</div>
									</div>

									<hr class="hidden" />

									<div id="setting-section">
									<script type="text/javascript">
									//<![CDATA[
										var settingMenus = new Array("upload-container","tag-location-container","power-container");
									//]]>
									</script>
									<div id="upload-section" class="section">
										<h3><a href="#void" onclick="focusLayer('upload-container',settingMenus);return false;"><?php echo _t('업로드');?></a></h3>
										<div id="upload-container">
											<div id="attachment-container" class="container">
<?php
$param = array(
		'uploadPath'=>       $context->getProperty('uri.blog')."/owner/entry/attachmulti/",
		'singleUploadPath'=> $context->getProperty('uri.blog')."/owner/entry/attach/",
		'deletePath'=>       $context->getProperty('uri.blog')."/owner/entry/detach/multi/",
		'labelingPath'=>     $context->getProperty('uri.blog')."/owner/entry/attachmulti/list/",
		'refreshPath'=>      $context->getProperty('uri.blog')."/owner/entry/attachmulti/refresh/",
		'fileSizePath'=>     $context->getProperty('uri.blog')."/owner/entry/size?parent=");
printEntryFileList(getAttachments($blogid, $entry['id'], 'label'), $param);
?>
											</div>
<?php
printEntryFileUploadButton($entry['id']);
?>
											<div id="insert-container" class="container">
												<a class="image-left" href="#void" onclick="editorAddObject(editor, 'Image1L');return false;" title="<?php echo _t('선택한 파일을 글의 왼쪽에 정렬합니다.');?>"><span class="text"><?php echo _t('왼쪽 정렬');?></span></a>
												<a class="image-center" href="#void" onclick="editorAddObject(editor, 'Image1C');return false;" title="<?php echo _t('선택한 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬');?></span></a>
												<a class="image-right" href="#void" onclick="editorAddObject(editor, 'Image1R');return false;" title="<?php echo _t('선택한 파일을 글의 오른쪽에 정렬합니다.');?>"><span class="text"><?php echo _t('오른쪽 정렬');?></span></a>
												<a class="image-2center" href="#void" onclick="editorAddObject(editor, 'Image2C');return false;" title="<?php echo _t('선택한 두개의 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬(2 이미지)');?></span></a>
												<a class="image-3center" href="#void" onclick="editorAddObject(editor, 'Image3C');return false;" title="<?php echo _t('선택한 세개의 파일을 글의 중앙에 정렬합니다.');?>"><span class="text"><?php echo _t('중앙 정렬(3 이미지)');?></span></a>
												<a class="image-free" href="#void" onclick="editorAddObject(editor, 'ImageFree');return false;" title="<?php echo _t('선택한 파일을 글에 삽입합니다. 문단의 모양에 영향을 주지 않습니다.');?>"><span class="text"><?php echo _t('파일 삽입');?></span></a>
												<a class="image-imazing" href="#void" onclick="editorAddObject(editor, 'Imazing');return false;" title="<?php echo _t('이메이징(플래쉬 갤러리)을 삽입합니다.');?>"><span class="text"><?php echo _t('이메이징(플래쉬 갤러리) 삽입');?></span></a>
												<a class="image-sequence" href="#void" onclick="editorAddObject(editor, 'Gallery');return false;" title="<?php echo _t('이미지 갤러리를 삽입합니다.');?>"><span class="text"><?php echo _t('갤러리 삽입');?></span></a>
												<a class="image-mp3" href="#void" onclick="editorAddObject(editor, 'Jukebox');return false;" title="<?php echo _t('쥬크박스를 삽입합니다.');?>"><span class="text"><?php echo _t('쥬크박스 삽입');?></span></a>
												<a class="image-podcast" href="#void" onclick="setEnclosure(document.getElementById('TCfilelist').value);return false;" title="<?php echo _t('팟캐스트로 지정합니다.');?>"><span class="text"><?php echo _t('팟캐스트 지정');?></span></a>
											</div>
										</div>
									</div>

									<hr class="hidden" />

									<div id="taglocal-section" class="section">
										<h3><a href="#void" onclick="focusLayer('tag-location-container',settingMenus);return false;"><?php echo _t('태그 &amp; 위치');?></a></h3>

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
														var oLocationTag = new LocationTag(document.getElementById("location"), "<?php echo $blog['language'];?>", true);
														oLocationTag.setInputClassName("input-text");
														oLocationTag.setValue("<?php echo addslashes($entry['location']);?>");
													} catch (e) {
														document.getElementById("location").innerHTML = '<input type="text" class="input-text" name="location" value="<?php echo addslashes($entry['location']);?>" /><br /><?php echo _t('지역태그 스크립트를 사용할 수 없습니다. 슬래시(/)로 구분된 지역을 직접 입력해 주십시오.(예: /대한민국/서울/강남역)');?>';
														// TODO : 이부분(스크립트를 실행할 수 없는 환경일 때)은 직접 입력보다는 0.96 스타일의 팝업이 좋을 듯
													}

													try {
														var oTag = new Tag(document.getElementById("tag"), "<?php echo $blog['language'];?>", true);
														oTag.setInputClassName("input-text");
<?php
		$tags = array();
		if (!defined('__TEXTCUBE_POST__')) {
			foreach (getTags($entry['blogid'], $entry['id']) as $tag) {
				array_push($tags, $tag['name']);
				echo 'oTag.setValue("' . addslashes($tag['name']) . '");';
			}
		}
?>
													} catch(e) {
														document.getElementById("tag").innerHTML = '<input type="text" class="input-text" name="tag" value="<?php echo addslashes(str_replace('"', '&quot;', implode(', ', $tags)));?>" /><br /><?php echo _t('태그 입력 스크립트를 사용할 수 없습니다. 콤마(,)로 구분된 태그를 직접 입력해 주십시오.(예: 텍스트큐브, BLOG, 테스트)');?>';
													}
												//]]>
											</script>
										</div>
									</div>

									<hr class="hidden" />

									<div id="power-section" class="section">
										<h3><a href="#void" onclick="focusLayer('power-container',settingMenus);return false;"><?php echo _t('기타 설정');?></a></h3>

										<div id="power-container" class="container">
											<dl id="permalink-line" class="line"<?php if($isKeyword) echo ' style="display: none"';?>>
												<dt><label for="permalink"><?php echo _t('절대 주소');?></label></dt>
												<dd>
													<samp id="permalink-prefix"><?php echo _f('%1/entry/', link_cut(getBlogURL()));?></samp><input type="text" id="permalink" class="input-text" name="permalink" onkeypress="return preventEnter(event);" value="<?php echo htmlspecialchars($entry['slogan']);?>" />
													<p>* <?php echo _t('입력하지 않으면 글의 제목이 절대 주소가 됩니다.');?></p>
												</dd>
											</dl>
											<dl id="date-line" class="line">
												<dt><span class="label"><?php echo _t('등록일자');?></span></dt>
												<dd>
<?php
if (defined('__TEXTCUBE_POST__')) {
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
														<input type="text" id="appointed" class="input-text" name="appointed" value="<?php echo Timestamp::format5(isset($entry['appointed']) ? $entry['appointed'] : $entry['published']);?>" onfocus="document.getElementById('editor-form').published[document.getElementById('editor-form').published.length - 1].checked = true" onkeypress="return preventEnter(event);" />
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
													<div class="comment-yes"><input type="checkbox" id="acceptcomment" class="checkbox" name="acceptcomment"<?php echo ($entry['acceptcomment'] ? ' checked="checked"' : '');?> /><label for="acceptcomment"><span class="text"><?php echo _t('댓글 작성을 허용합니다.');?></span></label></div>
												  	<div class="trackback-yes"><input type="checkbox" id="accepttrackback" class="checkbox" name="accepttrackback"<?php echo ($entry['accepttrackback'] ? ' checked="checked"' : '');?> /><label for="accepttrackback"><span class="text"><?php echo _t('글을 걸 수 있게 합니다.');?></span></label></div>
												</dd>
											</dl>
										</div>
									</div>
									</div><!-- setting-section -->
									<hr class="hidden clear" />

									<div id="save-section">
<?php
if (isset($_GET['popupEditor'])) {
?>
										<div class="button-box two-button-box">
											<input type="button" value="<?php echo _t('미리보기');?>" class="preview-button input-button" onclick="entryManager.preview();return false;" />
											<span class="hidden">|</span>
											<input type="submit" id="saveButton" value="<?php echo _t('중간 저장');?>" class="save-button input-button" onclick="entryManager.save();return false;" />
											<span class="hidden">|</span>
											<input type="submit" id="saveAndReturnButton" value="<?php echo _t('저장 후 돌아가기');?>" class="save-and-return-button input-button" onclick="entryManager.saveAndReturn();return false;" />
										</div>
<?php
} else {
?>
										<div class="button-box three-button-box">
											<input type="button" value="<?php echo _t('미리보기');?>" class="preview-button input-button" onclick="entryManager.preview();return false;" />
											<span class="hidden">|</span>
							    	  	 		<input type="submit" id="saveButton" value="<?php echo _t('중간 저장');?>" class="save-button input-button" onclick="entryManager.save();return false;" />
											<span class="hidden">|</span>
							       			<input type="submit" id="saveAndReturnButton" value="<?php echo _t('저장 후 돌아가기');?>" class="save-and-return-button input-button" onclick="entryManager.saveAndReturn();return false;" />
											<span class="hidden">|</span>
											<input type="submit" value="<?php echo _t('목록으로');?>" class="list-button input-button" onclick="returnToList();return false;" />
										</div>
<?php
}
?>
									</div>

									<hr class="hidden" />

								</div>

								<input type="hidden" name="categoryAtHome" value="<?php echo (isset($_POST['category']) ? $_POST['category'] : '0');?>" />
								<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
								<input type="hidden" name="withSearch" value="<?php echo (empty($_POST['search']) ? '' : 'on');?>" />
								<input type="hidden" name="search" value="<?php echo (isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '');?>" />
<?php
if (isset($entry['latitude']) && !is_null($entry['latitude'])) {
?>
								<input type="hidden" name="latitude" value="<?php echo $entry['latitude'];?>" />
								<input type="hidden" name="longitude" value="<?php echo $entry['longitude'];?>" />
<?php
}
?>
							</div>
						</form>
						<div id="feather" class="clear"></div>
						<script type="text/javascript">
							//<![CDATA[
								var contentformatterObj = document.getElementById('contentformatter');
								var contenteditorObj = document.getElementById('contenteditor');
								setFormatter(contentformatterObj.value, contenteditorObj, false);
								setCurrentEditor(contenteditorObj.value);
								entryManager = new EntryManager();
								reloadUploader();
								window.setInterval("entryManager.saveDraft();", 300000);
								//window.setTimeout(entryManager.saveDraft, 5000);
								checkCategory('<?php
switch($entry['category']) {
	case -1:
		echo 'type_keyword';break;
	case -2:
		echo 'type_notice';break;
	case -4:
		echo 'type_template';break;
	default:
		echo 'type_post';break;
		}?>');

							//]]>
						</script>
<?php
if (isset($_GET['popupEditor']))
	require ROOT . '/interface/common/owner/footerForPopupEditor.php';
else
	require ROOT . '/interface/common/owner/footer.php';
?>
