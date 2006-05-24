<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (defined('__TATTERTOOLS_KEYWORD__'))
	respondNotFoundPage();
if (defined('__TATTERTOOLS_POST__'))
	$suri['id'] = 0;
if (!isset($_GET['draft']) || (!$entry = getEntry($owner, $suri['id'], true))) {
	$entry = getEntry($owner, $suri['id'], false);
	if (!$entry)
		respondErrorPage('');
}
if (defined('__TATTERTOOLS_NOTICE__')) {
	$entry['category'] = -2;
	if (isset($_GET['popupEditor'])) {
		require ROOT . '/lib/piece/owner/header8.php';
	} else {
		require ROOT . '/lib/piece/owner/header7.php';
	}
} else {
	if (isset($_GET['popupEditor'])) {
		require ROOT . '/lib/piece/owner/header8.php';
	} else {
		require ROOT . '/lib/piece/owner/header0.php';
	}
}
if (defined('__TATTERTOOLS_POST__')) {
	if (defined('__TATTERTOOLS_NOTICE__')) {
		if (isset($_GET['popupEditor']))
			require ROOT . '/lib/piece/owner/contentMenu81.php';
		else
			require ROOT . '/lib/piece/owner/contentMenu71.php';
	} else {
		if (isset($_GET['popupEditor']))
			require ROOT . '/lib/piece/owner/contentMenu81.php';
		else
			require ROOT . '/lib/piece/owner/contentMenu04.php';
	}
	printOwnerEditorScript();
} else {
	if (defined('__TATTERTOOLS_NOTICE__')) {
		if (isset($_GET['popupEditor']))
			require ROOT . '/lib/piece/owner/contentMenu81.php';
		else
			require ROOT . '/lib/piece/owner/contentMenu70.php';
	} else {
		if (isset($_GET['popupEditor']))
			require ROOT . '/lib/piece/owner/contentMenu81.php';
		else
			require ROOT . '/lib/piece/owner/contentMenu00.php';
	}
	printOwnerEditorScript($entry['id']);
}
?>
								<script type="text/javascript" src="<?=$service['path']?>/script/generaltag.js"></script>
								<script type="text/javascript" src="<?=$service['path']?>/script/locationtag.js"></script>
								<script type="text/javascript">
									//<![CDATA[
										var enclosured = "<?=fetchQueryCell("SELECT name FROM {$database['prefix']}Entries e, {$database['prefix']}Attachments a WHERE e.owner = $owner AND e.id = {$suri['value']} AND a.parent = e.id AND a.enclosure = 1")?>";
										
										window.onerror = function(errType, errURL,errLineNum) {
											window.status = "Error: " + errType +" (on line " + errLineNum + " of " + errURL + ")";
											return true;
										}
										
										function setEnclosure(value) {
											var filename = value.substring(0, value.indexOf("|"));
											
											if(document.getElementById("fileList").selectedIndex == -1) {
												alert("<?=_t('파일을 선택하십시오.')?>");
												return false;
											}
											
											if(!(new RegExp("\.mp3$", "i").test(filename))) {
												alert("<?=_t('MP3만 사용할 수 있습니다.')?>");
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
											
											var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/attach/enclosure/");
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
												alert("<?=_t('변경하지 못했습니다.')?>");
											}
											PM.addRequest(request, "<?=_t('변경하고 있습니다.')?>");
											request.send("fileName=" + encodeURIComponent(filename) + "&order=" + order);
										}
										
										function EntryManager() {
											this.savedData = null;
											this.pageHolder = new PageHolder(false, "<?=_t('아직 저장되지 않았습니다.')?>");
											this.pageHolder.isHolding = function () {
												return (entryManager.savedData != entryManager.getData());
											}
											this.delay = false;
											this.getData = function (check) {
												if (check == undefined)
													check = false;
												var oForm = document.forms[0];
												
												var title = trim(oForm.title.value);
												if (check && (title.length == 0)) {
													alert("<?=_t('제목을 입력해 주십시오.')?>");
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
												try {
													if (editor.editMode == "WYSIWYG")
														oForm.content.value = editor.html2ttml(editor.contentDocument.body.innerHTML);
												} catch(e) {
												}
												var content = trim(oForm.content.value);
												if (check && (content.length == 0)) {
													alert("<?=_t('본문을 입력해 주십시오.')?>");
													return null;
												}
													
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
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

<?
	}
}
?>
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
															alert("<?=_t('등록 예약 시간이 올바르지 않습니다.')?>");
														return null;
													}
													published = Math.floor(published / 1000);
												}
												return (
													"visibility=" + visibility +
													"&title=" + encodeURIComponent(title) +
													"&content=" + encodeURIComponent(content) +
													"&published=" + published +
<?
if (defined('__TATTERTOOLS_NOTICE__')) {
?>
														"&category=-2"
<?
} else {
?>
														"&category=" + oForm.category.value +
														"&location=" + encodeURIComponent(locationValue) +
														"&tag=" + encodeURIComponent(tagValue) +
														"&acceptComment=" + (oForm.acceptComment.checked ? 1 : 0) +
														"&acceptTrackback=" + (oForm.acceptTrackback.checked ? 1 : 0)
<?
}
?>
												);
											}
											
											this.setEnclosure = function(fileName) {
												
											}
											
											this.save = function () {
												var data = this.getData(true);
												if (data == null)
													return false;
<?
if (defined('__TATTERTOOLS_POST__')) {
?>
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/add/");
<?
} else {
?>
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/update/<?=$entry['id']?>");
<?
}
?>
												request.message = "<?=_t('저장하고 있습니다.')?>";
												request.onSuccess = function () {
													entryManager.pageHolder.isHolding = function () {
														return false;
													}
													PM.removeRequest(this);
<?
if (isset($_GET['popupEditor'])) {
?>
													opener.location.href = opener.location.href;
													window.close();
<?
} else if (isset($_GET['returnURL'])) {
?>
													window.location = "<?=escapeJSInCData($_GET['returnURL'])?>";
<?
} else {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
													document.forms[0].action = "<?=$blogURL?>/owner/notice";
													document.forms[0].submit();
<?
	} else {
?>
													document.forms[0].action = "<?=$blogURL?>/owner/entry";
													document.forms[0].submit();
<?
	}
}
?>
												}
												request.onError = function () {
													PM.removeRequest(this);
													alert("<?=_t('저장하지 못했습니다.')?>");
												}
												PM.addRequest(request, "<?=_t('저장하고 있습니다.')?>");
												request.send(this.getData());
											}
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
											this.saveAuto = function () {
												if (this.timer == null)
													this.timer = window.setTimeout("entryManager.saveDraft()", 5000);
												else
													this.delay = true;
											}
											this.saveDraft = function () {
												var data = this.getData();
												if ((data == null) || (data == this.savedData))
													return;
												this.timer = null;
												if (this.delay) {
													this.delay = false;
													this.timer = window.setTimeout("entryManager.saveDraft()", 5000);
													return;
												}
												
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/draft/<?=$entry['id']?>");
												request.onSuccess = function () {
													PM.showMessage("<?=_t('자동으로 임시 저장되었습니다.')?>", "center", "bottom");
													entryManager.savedData = this.content;
													if (entryManager.savedData == entryManager.getData())
														entryManager.pageHolder.release();
												}
												request.send(data);
											}
<?
	}
}
?>
											this.preview = function () {
												var data = this.getData();
												if (data == null)
													return;
<?
if (defined('__TATTERTOOLS_NOTICE__')) {
?>
												return;
<?
} else {
?>
												if (data == this.savedData) {
													window.open("<?=$blogURL?>/owner/entry/preview/<?=$entry['id']?>", "previewEntry<?=$entry['id']?>");
													return;
												}
												
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/draft/<?=$entry['id']?>");
												request.async = false;
												request.message = "<?=_t('미리보기를 준비하고 있습니다.')?>";
												request.onSuccess = function () {
													entryManager.savedData = this.content;
													window.open("<?=$blogURL?>/owner/entry/preview/<?=$entry['id']?>", "previewEntry<?=$entry['id']?>");
												}
												request.onError = function () {
												}
												request.send(data);
<?
}
?>
											}
											this.savedData = this.getData();
										}
										var entryManager;
										window.addEventListener(
											"load",
											function (event) {
												entryManager = new EntryManager();
											},
											false
										);
									//]]>
								</script>
								
								<div id="part-editor" class="part">
<?
if (defined('__TATTERTOOLS_POST__')) {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
									<h2 class="caption"><span class="main-text"><?=_t('공지를 작성합니다')?></span></h2>
<?
	} else {
?>
									<h2 class="caption"><span class="main-text"><?=_t('글을 작성합니다')?></span></h2>
<?
	}
} else {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
									<h2 class="caption"><span class="main-text"><?=_t('선택한 공지를 수정합니다')?></span></h2>
<?
	} else {
?>
									<h2 class="caption"><span class="main-text"><?=_t('선택한 글을 수정합니다')?></span></h2>
<?
	}
}
?>
									
									<div id="editor" class="data-inbox">
										<div id="editor-section" class="section">
											<div id="title-container" class="container">
<?
if (defined('__TATTERTOOLS_KEYWORD__')) {
?>
												<label for="title"><span><?=_t('키워드')?></span><span class="divider"> | </span></label>
<?
} else {
?>
												<label for="title"><span><?=_t('제목')?></span><span class="divider"> | </span></label>
<?
}
?>
												<input type="text" id="title" class="text-input" name="title" value="<?=htmlspecialchars($entry['title'])?>" />
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
												<select name="category">
													<option value="0"><?=_t('전체')?></option>
<?
		foreach (getCategories($owner) as $category) {
?>
													<option value="<?=$category['id']?>"<?=($category['id'] == $entry['category'] ? ' selected="selected"' : '')?>><?=htmlspecialchars($category['name'])?></option>
<?
			foreach ($category['children'] as $child) {
?>
													<option value="<?=$child['id']?>"<?=($child['id'] == $entry['category'] ? ' selected="selected"' : '')?>>&nbsp;► <?=htmlspecialchars($child['name'])?></option>
<?
			}
		}
?>
												</select>
<?
	}
}
?>
											</div>
											
<?
printEntryEditorPalette();
?>
											<div id="editor-textbox" class="container">
												<textarea id="editWindow" name="content" cols="50" rows="20" onselect="savePosition(); editorChanged()" onclick="savePosition();editorChanged()" onkeyup="savePosition();editorChanged()"><?=htmlspecialchars($entry['content'])?></textarea>
												<script type="text/javascript" src="<?=$service['path']?>/script/editor.js"></script>
												<script type="text/javascript">
													//<![CDATA[
														var editor = new TTEditor();
														editor.initialize(document.getElementById("editWindow"), "<?=$service['path']?>/attach/<?=$owner?>/", "<?=true ? 'WYSIWYG' : 'TEXTAREA'?>", "<?=true ? 'BR' : 'P'?>");
													//]]>
												</script>
											</div>
											
											<hr class="hidden" />
											
											<div id="property-container" class="container">
<?
printEntryEditorProperty();
?>
											</div>

<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
		$view = fireEvent('AddPostEditorToolbox', '');
		if (!empty($view))
			echo '<div id="post-toolbox">', $view, '</div>';
?>						  
											<hr class="hidden" />
																			
											<div id="tag-location-container" class="container">
												<dl id="tag-line">
													<dt><span><?=_t('태그')?></span><span class="divider"> | </span></dt>
													<dd id="tag"></dd>
												</dl>
												
												<dl id="location-line">
													<dt><span><?=_t('지역')?></span><span class="divider"> | </span></dt>
													<dd id="location"></dd>
												</dl>
												
												<div class="clear"></div>
												
												<script type="text/javascript">
													//<![CDATA[
														try {
															var oLocationTag = new LocationTag(document.getElementById("location"), "<?=$blog['language']?>", <?=isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false'?>);
															oLocationTag.setInputClassName("text-input");
															oLocationTag.setValue("<?=addslashes($entry['location'])?>");	
														} catch (e) {
															document.getElementById("location").innerHTML = '<input type="text" class="text-input" name="location" value="<?=addslashes($entry['location'])?>" /><br /><?=_t('지역태그 스크립트를 사용할 수 없습니다. 슬래시(/)로 구분된 지역을 직접 입력해주세요 (예: /대한민국/서울/강남역)')?>';
															// TODO : 이부분(스크립트를 실행할 수 없는 환경일 때)은 직접 입력보다는 0.96 스타일의 팝업이 좋을 듯
														}
														
														try {
															var oTag = new Tag(document.getElementById("tag"), "<?=$blog['language']?>", <?=isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false'?>);
															oTag.setInputClassName("text-input");
<?
		$tags = array();
		if (!defined('__TATTERTOOLS_POST__')) {
			foreach (getTags($entry['id']) as $tag) {
				array_push($tags, $tag['name']);
				echo 'oTag.setValue("' . addslashes($tag['name']) . '");';
			}
		}
?>
														} catch(e) {
															document.getElementById("tag").innerHTML = '<input type="text" class="text-input" name="tag" value="<?=addslashes(str_replace('"', '&quot;', implode(', ', $tags)))?>" /><br /><?=_t('태그 입력 스크립트를 사용할 수 없습니다. 콤마(,)로 구분된 태그를 직접 입력해주세요 (예: 태터툴즈, BLOG, 테스트)')?>';
														}
													//]]>
												</script> 
											</div>
<?
	} else {
		$view = fireEvent('AddNoticeEditorToolbox', '');
		if (!empty($view))
			echo '<div id="notice-toolbox">', $view, '</div>';
	}
} else {
	$view = fireEvent('AddKeywordEditorToolbox', '');
	if (!empty($view))
		echo '<div id="keyword-toolbox">', $view, '</div>';
}
?>
										</div>
											
										<hr class="hidden" />
										
										<div id="upload-section" class="section">
											<div id="attachment-container" class="container">
<?
printEntryFileList(getAttachments($owner, $entry['id']), $entry['id']);
?>
											</div>
											
											<div id="insert-container" class="container">
												<ul>
													<li class="image-left"><a href="#void" onclick="linkImage1('1L')" title="<?=_t('선택한 파일을 글의 왼쪽에 정렬합니다.')?>"><span><?=_t('왼쪽 정렬')?></span></a></li>
													<li class="image-center"><a href="#void" onclick="linkImage1('1C')" title="<?=_t('선택한 파일을 글의 중앙에 정렬합니다.')?>"><span><?=_t('중앙 정렬')?></span></a></li>
													<li class="image-right"><a href="#void" onclick="linkImage1('1R')" title="<?=_t('선택한 파일을 글의 오른쪽에 정렬합니다.')?>"><span><?=_t('오른쪽 정렬')?></span></a></li>
													<li class="image-2center"><a href="#void" onclick="linkImage2()" title="<?=_t('선택한 두개의 파일을 글의 중앙에 정렬합니다.')?>"><span><?=_t('중앙 정렬(2 이미지)')?></span></a></li>
													<li class="image-3center"><a href="#void" onclick="linkImage3()" title="<?=_t('선택한 세개의 파일을 글의 중앙에 정렬합니다.')?>"><span><?=_t('중앙 정렬(3 이미지)')?></span></a></li>
													<li class="image-free"><a href="#void" onclick="linkImageFree()" title="<?=_t('선택한 파일을 글에 삽입합니다. 문단의 모양에 영향을 주지 않습니다.')?>"><span><?=_t('파일 삽입')?></span></a></li>
													<li class="image-imazing"><a href="#void" onclick="viewImazing()" title="<?=_t('이메이징(플래쉬 갤러리)을 삽입합니다.')?>"><span><?=_t('이메이징 삽입(플래쉬 갤러리)')?></span></a></ li>
													<li class="image-sequence"><a href="#void" onclick="viewGallery()" title="<?=_t('이미지 갤러리를 삽입합니다.')?>"><span><?=_t('갤러리 삽입')?></span></a></li>
													<li class="image-mp3"><a href="#void" onclick="viewJukebox()" title="<?=_t('쥬크박스를 삽입합니다.')?>"><span><?=_t('쥬크박스 삽입')?></span></a></li>
													<li class="image-podcast"><a href="#void" onclick="setEnclosure(document.getElementById('fileList').value)" title="<?=_t('팟캐스트로 지정합니다.')?>"><span><?=_t('팟캐스트 지정')?></span></a></li>								
												</ul>
												
												<div class="clear"></div>
											</div>
												
<?
printEntryFileUploadButton($entry['id']);
?>
										</div>
										
										<hr class="hidden" />
										
										<div id="power-section" class="section">
											<div id="power-container" class="container">
												<dl id="date-line" class="line">
													<dt><span class="text"><?=_t('등록일자')?></span><span class="divider"> | </span></dt>
													<dd>
<?
if (defined('__TATTERTOOLS_POST__')) {
?>
														<input type="radio" id="publishedUpdate" class="radio" name="published" value="1" checked="checked" /> <label for="publishedUpdate"><span><?=_t('갱신')?></span></label>
<?
} else {
?>
														<input type="radio" id="publishedNoChange" class="radio" name="published" value="0" <?=(!isset($entry['republish']) && !isset($entry['appointed']) ? 'checked="checked"' : '')?> /> <label for="publishedNoChange"><span><?=_t('유지')?></span> (<?=Timestamp::format5($entry['published'])?>)</label>
														<input type="radio" id="publishedUpdate" class="radio" name="published" value="1" <?=(isset($entry['republish']) ? 'checked="checked"' : '')?> /> <label for="publishedUpdate"><span><?=_t('갱신')?></span></label>
<?
}
?>
														<input type="radio" id="publishedPreserve" class="radio" name="published" value="2" <?=(isset($entry['appointed']) ? 'checked="checked"' : '')?> /> <label for="publishedPreserve" onclick="document.getElementById('appointed').select()"><span><?=_t('예약')?></span></label>
														<input type="text" id="appointed" class="text-input" name="appointed" value="<?=Timestamp::format5(isset($entry['appointed']) ? $entry['appointed'] : $entry['published'])?>" onfocus="document.forms[0].published[document.forms[0].published.length - 1].checked = true" />
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl id="statue-line" class="line">
													<dt><span class="text"><?=_t('공개여부')?></span><span class="divider"> | </span></dt>
													<dd>
														<input type="radio" class="radio" name="visibility" value="0"<?=(abs($entry['visibility']) == 0 ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('비공개')?></span></label>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
														<input type="radio" class="radio" name="visibility" value="1"<?=(abs($entry['visibility']) == 1 ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('보호')?></span></label>
<?
	}
}
?>
														<input type="radio" class="radio" name="visibility" value="2"<?=(abs($entry['visibility']) == 2 ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('공개')?></span></label>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
														<input type="radio" class="radio" name="visibility" value="3"<?=(abs($entry['visibility']) == 3 ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('발행')?></span></label>
<?
	}
}
?>
													</dd>
													<dd class="clear"></dd>
												</dl>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
												<dl id="power-line" class="line">
													<dt><span class="text"><?=_t('권한')?></span><span class="divider"> | </span></dt>
													<dd>
														<div class="comment-yes"><input type="checkbox" class="checkbox" name="acceptComment"<?=($entry['acceptComment'] ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('이 글에 댓글을 쓸 수 있습니다.')?></span></label></div>
													  	<div class="trackback-yes"><input type="checkbox" class="checkbox" name="acceptTrackback"<?=($entry['acceptTrackback'] ? ' checked="checked"' : '')?> /> <label for=""><span><?=_t('이 글에 트랙백을 보낼 수 있습니다.')?></span></label></div>
													</dd>
													<dd class="clear"></dd>
												</dl>
<?
	}
}
?>
												<div class="clear"></div>
											</div>
										</div>
									</div>
									
									<hr class="hidden" />
										
<?
if (isset($_GET['popupEditor'])) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
									<div class="button-box two-button">
										<a href="#void" class="save-button button" onclick="entryManager.save()"><span><?=_t('저장하기')?></span></a>
										<span class="hidden">|</span>
							        	<a href="#void" class="preview-button button" onclick="entryManager.preview()"><span><?=_t('미리보기')?></span></a>
										<div class="clear"></div>
									</div>
<?
	} else {
?>
									<div class="button-box one-button-box">
										<a href="#void" class="save-button button" onclick="entryManager.save()"><span><?=_t('저장하기')?></span></a>
										<div class="clear"></div>
									</div>
<?
	}
} else {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
									<div class="button-box three-button-box">
										<a href="#void" class="save-button button" onclick="entryManager.save()"><span><?=_t('저장하기')?></span></a>
										<span class="hidden">|</span>
							        	<a href="#void" class="preview-button button" onclick="entryManager.preview()"><span><?=_t('미리보기')?></span></a>
										<span class="hidden">|</span>
										<a href="#void" class="list-button button" onclick="document.forms[0].action='<?=$blogURL?>/owner/entry'; document.forms[0].submit()"><span><?=_t('목록으로')?></span></a>
										<div class="clear"></div>
									</div>
<?
	} else {
?>
									<div class="button-box two-button-box">
										<a href="#void" class="save-button button" onclick="entryManager.save()"><span><?=_t('저장하기')?></span></a>
										<span class="hidden">|</span>
										<a href="#void" class="list-button button" onclick="document.forms[0].action='<?=$blogURL?>/owner/notice'; document.forms[0].submit()"><span><?=_t('목록으로')?></span></a>
										<div class="clear"></div>
									</div>
<?
	}
}

if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
									<input type="hidden" name="categoryAtHome" value="<?=(isset($_POST['category']) ? $_POST['category'] : '0')?>" />
<?
}
?>
									<input type="hidden" name="withSearch" value="<?=(empty($_POST['search']) ? '' : 'on')?>" />
									<input type="hidden" name="search" value="<?=(isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '')?>" />
								</div>
<?
if (isset($_GET['popupEditor']))
	require ROOT . '/lib/piece/owner/footer8.php';
else
	require ROOT . '/lib/piece/owner/footer0.php';
?>