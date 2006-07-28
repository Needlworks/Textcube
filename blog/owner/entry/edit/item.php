<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'draft' => array('any', 'mandatory' => false),
		'popupEditor' => array('any', 'mandatory' => false),
		'returnURL' => array('string', 'mandatory' => false)
	),
	'POST' => array(
		'category' => array('int', 'default' => 0),
		'withSearch' => array(array('on'), 'mandatory' => false),
		'search' => array('string', 'default' => '')
	)
);
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
	if (isset($_GET['popupEditor']))
		require ROOT . '/lib/piece/owner/header8.php';
	else
		require ROOT . '/lib/piece/owner/header7.php';
} else {
	if (isset($_GET['popupEditor']))
		require ROOT . '/lib/piece/owner/header8.php';
	else
		require ROOT . '/lib/piece/owner/header0.php';
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
			alert("<?=_t('파일을 선택하십시오\t')?>");
			return false;
		}

		if(!(new RegExp("\.mp3$", "i").test(filename))) {
			alert("<?=_t('MP3만 사용할 수 있습니다')?>");
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
			alert("<?=_t('변경하지 못했습니다')?>");
		}
		PM.addRequest(request, "<?=_t('변경하고 있습니다')?>");
		request.send("fileName=" + encodeURIComponent(filename) + "&order=" + order);
	}
	
	function EntryManager() {
		this.savedData = null;
		this.pageHolder = new PageHolder(false, "<?=_t('아직 저장되지 않았습니다')?>");
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
				alert("<?=_t('제목을 입력해 주십시오')?>");
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
				alert("<?=_t('본문을 입력해 주십시오')?>");
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
						alert("<?=_t('등록 예약 시간이 올바르지 않습니다')?>");
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
			request.message = "<?=_t('저장하고 있습니다')?>";
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
				alert("<?=_t('저장하지 못했습니다')?>");
			}
			PM.addRequest(request, "<?=_t('저장하고 있습니다')?>");
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
				PM.showMessage("<?=_t('자동으로 임시 저장되었습니다')?>", "center", "bottom");
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
			request.message = "<?=_t('미리보기를 준비하고 있습니다')?>";
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
//]]>
</script>
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
<?
if (defined('__TATTERTOOLS_POST__')) {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
                      <td style="padding:3px 0px 0px 4px"><?=_t('공지를 작성합니다')?></td>
<?
	} else {
?>
                      <td style="padding:3px 0px 0px 4px"><?=_t('글을 작성합니다')?></td>
<?
	}
} else {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
                      <td style="padding:3px 0px 0px 4px"><?=_t('선택한 공지를 수정합니다')?></td>
<?
	} else {
?>
                      <td style="padding:3px 0px 0px 4px"><?=_t('선택한 글을 수정합니다')?></td>
<?
	}
}
?>
                    </tr>
                  </table>
                </td>
                <td align="right"></td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED; table-layout: fixed">
              <tr>
                <td style="background-color:#EBF2F8; padding: 5px 0px 5px 0px">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" style="table-layout: fixed">
				  <tr>
					<td style="padding: 16px 15px; background :#EBF2F8 ;">
					<table border="0" cellspacing="0" cellpadding="0" style="width: 901px; table-layout: fixed">
					<col width="74%"></col>
					<col width="*"></col>
					  <tr>
						<td style="padding: 7px; background:#BCD2E5"><table width="100%" border="0" cellspacing="0" cellpadding="2">
						  <tr>
<?
if (defined('__TATTERTOOLS_KEYWORD__')) {
?>
							<td><span style="padding-left:10px; font-weight:bold; color:#536576;"><?=_t('키워드')?></span> |</td>
<?
} else {
?>
							<td><span style="padding-left:10px; font-weight:bold; color:#536576;"><?=_t('제목')?></span> |</td>
<?
}
?>
							<td style="padding-left:7px"><input type="text" class="text2" name="title" style="width: 420px" value="<?=htmlspecialchars($entry['title'])?>"/></td>
							<td>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
							  <select name="category" style="width: 150px; border: 1px solid #92B5E8;">
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
							</td>
						  </tr>
						</table></td>
						<td rowspan="6" valign="top" style="padding-left: 10px; background:#EBF2F8">
						<?
printEntryEditorProperty();
?>
						</td>
					  </tr>
					  <tr>
						<td style="padding: 0px 0px 5px 5px; background:#BCD2E5 ;">
						<? printEntryEditorPalette(); ?>
					    <textarea class="text1" cols="" name="content" id="editWindow" rows="20" onselect="savePosition();editorChanged()" onclick="savePosition();editorChanged()" onkeyup="savePosition();editorChanged()" style="width: 645px; font-family: monospace; word-break: keep-all; line-height: 1.5; color: #222; border: 2px solid #7ac; height: 440px"><?=htmlspecialchars($entry['content'])?></textarea>
						<script type="text/javascript" src="<?=$service['path']?>/script/editor.js"></script>
						<script type="text/javascript">
							var editor = new TTEditor();
							editor.initialize(document.getElementById("editWindow"), "<?=$service['path']?>/attach/<?=$owner?>/", "<?=getUserSetting('editorMode', 1) == 1 ? 'WYSIWYG' : 'TEXTAREA'?>", "<?=true ? 'BR' : 'P'?>");
						</script>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
<?
		$view = fireEvent('AddPostEditorToolbox', '');
		if (!empty($view))
			echo '<div style="width:655px;">', $view, '</div>';
?>
						  
						  <table cellspacing="0" cellpadding="1" style="margin-top:3px;" width="100%">
							<tr>
							  <td style="width:5%" nowrap="nowrap"><span style="padding-left:10px; font-weight:bold; color:#536576;"><?=_t('태그')?></span> | &nbsp;&nbsp;</td>
							  <td style="padding-left: 7px; width:100%" colspan="2"><div id="tag"></div></td>							  
							</tr>
							<tr>
							  <td nowrap="nowrap"><span style="padding-left:10px; font-weight:bold; color:#536576;"><?=_t('지역')?></span> | &nbsp;&nbsp;</td>
							  <td style="padding-left: 7px" width="100%"><div id="location"></div></td>							  
							  <td style="padding-right: 10px" align="left" nowrap="nowrap"><a target="_blank" href="http://manual.tattertools.com/ko/wiki/%EC%82%AC%EC%9A%A9%ED%95%98%EA%B8%B0/%EC%A7%80%EC%97%AD%ED%83%9C%EA%B7%B8?action=show">[<?=_t('지역태그 란 ')?>?]</a></td>
							</tr>
						  </table>
						  
						  
						  <script type="text/javascript">
						  //<![CDATA[
							try {
								var oLocationTag = new LocationTag(document.getElementById("location"), "<?=$blog['language']?>", <?=isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false'?>);
								oLocationTag.setInputClassName("text2");
								oLocationTag.setValue("<?=addslashes($entry['location'])?>");
								
							} catch (e) {
								document.getElementById("location").innerHTML = '<input class="text1" type="text" name="location" style="width:520px" value="<?=addslashes($entry['location'])?>" /><br/><?=_t('지역태그 스크립트를 사용할 수 없습니다. 슬래시(/)로 구분된 지역을 직접 입력해주세요 (예: /대한민국/서울/강남역)')?>';
								// TODO : 이부분(스크립트를 실행할 수 없는 환경일때)은 직접 입력보다는 0.96 스타일의 팝업이 좋을 듯
							}

							try {
								var oTag = new Tag(document.getElementById("tag"), "<?=$blog['language']?>", <?=isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false'?>);
								oTag.setInputClassName("text2");
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
								document.getElementById("tag").innerHTML = '<input class="text1" type="text" name="tag" style="width:520px" value="<?=addslashes(str_replace('"', '&quot;', implode(', ', $tags)))?>" /><br/><?=_t('태그 입력 스크립트를 사용할 수 없습니다. 콤마(,)로 구분된 태그를 직접 입력해주세요 (예: 태터툴즈, BLOG, 테스트)')?>';
							}
						  //]]> 
						  </script> 
	<?
	} else {
		$view = fireEvent('AddNoticeEditorToolbox', '');
		if (!empty($view))
			echo '<div style="width:655px;">', $view, '</div>';
	}
} else {
	$view = fireEvent('AddKeywordEditorToolbox', '');
	if (!empty($view))
		echo '<div style="width:655px;">', $view, '</div>';
}
?>
						  </td>
						</tr>
					  <tr>
						<td style="background:#EBF2F8 ;" height="6" ></td>
						</tr>
					  <tr>
						<td style="padding:7px; background:#D0E5F1 ;">
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tr>
<?
$param = array(
		'uploadPath'=> "$blogURL/owner/entry/attachmulti/{$entry['id']}", 
		'singleUploadPath'=> "$blogURL/owner/entry/attach/{$entry['id']}", 
		'deletePath'=>"$blogURL/owner/entry/detach/multi/". ($entry['id'] ? $entry['id'] : '0') ,
		'labelingPath'=> "$blogURL/owner/entry/attachmulti/list/{$entry['id']}", 
		'refreshPath'=> "$blogURL/owner/entry/attachmulti/refresh/". ($entry['id'] ? $entry['id'] : '0') , 
		'fileSizePath'=> "$blogURL/owner/entry/size?parent={$entry['id']}");		
printEntryFileList(getAttachments($owner, $entry['id'], 'label'), $param);
?>
							
							<td width="110" align="center" valign="top">
							<table border="0" cellspacing="0" cellpadding="0">
							  <tr>
								<td width="33" height="28" align="center" valign="top"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignLeft.gif" width="27" height="25" onclick="linkImage1('1L')" alt="<?=_t('선택한 파일을 글의 왼쪽에 정렬합니다')?>" title="<?=_t('선택한 파일을 글의 왼쪽에 정렬합니다')?>"/></td>
								<td width="33" align="center" valign="top"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignCenter.gif" width="27" height="25" onclick="linkImage1('1C')" alt="<?=_t('선택한 파일을 글의 중앙에 정렬합니다')?>" title="<?=_t('선택한 파일을 글의 중앙에 정렬합니다')?>"/></td>
								<td width="33" align="center" valign="top"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignRight.gif" width="27" height="25" onclick="linkImage1('1R')" alt="<?=_t('선택한 파일을 글의 오른쪽에 정렬합니다')?>" title="<?=_t('선택한 파일을 글의 오른쪽에 정렬합니다')?>"/></td>
							  </tr>
							  <tr>
								<td colspan="3" background="<?=$service['path']?>/image/owner/edit/dotted_attach.gif"></td>
								</tr>
							  <tr>
								<td height="31" align="center"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlign2Center.gif" width="27" height="25" onclick="linkImage2()" alt="<?=_t('선택한 두개의 파일을 글의 중앙에 정렬합니다')?>" title="<?=_t('선택한 두개의 파일을 글의 중앙에 정렬합니다')?>"/></td>
								<td align="center"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlign3Center.gif" width="27" height="25" onclick="linkImage3()" alt="<?=_t('선택한 세개의 파일을 글의 중앙에 정렬합니다')?>" title="<?=_t('선택한 세개의 파일을 글의 중앙에 정렬합니다')?>"/></td>
								<td align="center"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignFree.gif" width="27" height="25" onclick="linkImageFree()" alt="<?=_t('선택한 파일을 글에 삽입합니다.\n문단의 모양에 영향을 주지 않습니다')?>" title="<?=_t('선택한 파일을 글에 삽입합니다.\n문단의 모양에 영향을 주지 않습니다')?>"/></td>
							  </tr>
							  <tr>
								<td colspan="3" background="<?=$service['path']?>/image/owner/edit/dotted_attach.gif"></td>
								</tr>
							  <tr>
								<td height="31" align="center" valign="middle"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignImazing.gif" width="27" height="25" onclick="viewImazing()" alt="<?=_t('이메이징을 삽입합니다 (플래쉬 갤러리)')?>" title="<?=_t('이메이징을 삽입합니다 (플래쉬 갤러리)')?>"/></td>
								<td align="center" valign="middle"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignSequence.gif" width="27" height="25" onclick="viewGallery()" alt="<?=_t('이미지 갤러리를 삽입합니다')?>" title="<?=_t('이미지 갤러리를 삽입합니다')?>"/></td>
								<td align="center" valign="middle"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachMP3.gif" width="27" height="25" onclick="viewJukebox()" alt="<?=_t('쥬크박스를 삽입합니다')?>" title="<?=_t('쥬크박스를 삽입합니다')?>"/></td>
							  </tr>
							  
							  <tr>
								<td colspan="3" background="<?=$service['path']?>/image/owner/edit/dotted_attach.gif"></td>								
							  </tr>
							  <tr>
								<td height="28" align="center" valign="bottom"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attachAlignPodcast.gif" width="27" height="25" onclick="setEnclosure(document.getElementById('fileList').value)" alt="<?=_t('팟캐스트로 지정합니다')?>" title="<?=_t('팟캐스트로 지정합니다')?>"/></td>								
							  </tr>
							</table></td>
						  </tr>
						  <tr>
							<td></td>
							<td><?
printEntryFileUploadButton($entry['id']);
?></td>
							<td align="right">&nbsp;</td>
						  </tr>
						</table>
						  </td>
					  </tr>
					  <tr>
						<td style="background:#EBF2F8 ;" height="6"></td>
						</tr>
					  <tr>
						<td style="padding:7px; background:#D0E5F1 ;"><table width="100%" border="0" cellspacing="0" cellpadding="5">
						  <tr>
							<td>
						  <table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
							  <td><table border="0" cellspacing="0" cellpadding="2">
								  <tr>
									<td width="80" height="22" align="right"><?=_t('등록일자')?> | </td>
									<td>
<?
if (defined('__TATTERTOOLS_POST__')) {
?>
										<input type="radio" name="published" value="1" checked="checked" />
<?
} else {
?>
										<input type="radio" name="published" value="0" <?=(!isset($entry['republish']) && !isset($entry['appointed']) ? 'checked="checked"' : '')?> />
										<?=_t('유지')?> (<?=Timestamp::format5($entry['published'])?>)
										<input type="radio" name="published" value="1" <?=(isset($entry['republish']) ? 'checked="checked"' : '')?> />
<?
}
?>
										<?=_t('갱신')?>
										<input type="radio" name="published" value="2" <?=(isset($entry['appointed']) ? 'checked="checked"' : '')?>/>
										<?=_t('예약')?>
										<input name="appointed" class="text2" type="text" style="width: 120px" value="<?=Timestamp::format5(isset($entry['appointed']) ? $entry['appointed'] : $entry['published'])?>" onfocus="document.forms[0].published[document.forms[0].published.length - 1].checked = true" />
									</td>
								  </tr>
								  <tr>
									<td height="22" align="right"><?=_t('공개여부')?> | </td>
									<td>
										<input type="radio" name="visibility" value="0"<?=(abs($entry['visibility']) == 0 ? ' checked="checked"' : '')?> />
										<?=_t('비공개')?>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
										<input type="radio" name="visibility" value="1"<?=(abs($entry['visibility']) == 1 ? ' checked="checked"' : '')?> />
										<?=_t('보호')?>
<?
	}
}
?>
										<input type="radio" name="visibility" value="2"<?=(abs($entry['visibility']) == 2 ? ' checked="checked"' : '')?> />
										<?=_t('공개')?>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
										<input type="radio" name="visibility" value="3"<?=(abs($entry['visibility']) == 3 ? ' checked="checked"' : '')?> />
										<?=_t('발행')?>
<?
	}
}
?>
									</td>
								  </tr>
							  </table></td>
							</tr>
<?
if (!defined('__TATTERTOOLS_KEYWORD__')) {
	if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
							<tr>
							  <td bgcolor="#BCD2E5"></td>
							</tr>
							<tr>
							  <td><table border="0" cellspacing="0" cellpadding="2">
								  <tr>
									<td width="80" height="22" align="right"><?=_t('권한')?> | </td>
									<td><input type="checkbox" name="acceptComment"<?=($entry['acceptComment'] ? ' checked="checked"' : '')?> />
										<?=_t('이 글에 댓글을 쓸 수 있습니다')?></td>
								  </tr>
								  <tr>
									<td height="22">&nbsp;</td>
									<td><input type="checkbox" name="acceptTrackback"<?=($entry['acceptTrackback'] ? ' checked="checked"' : '')?> />
										<?=_t('이 글에 트랙백을 보낼 수 있습니다')?></td>
								  </tr>
							  </table></td>
							</tr>
<?
	}
}
?>
						  </table>
						  </td>
						</tr>
					</table>
					</td>
				  </tr>
				</table>
                </td>
              </tr>
            </table>
			</td>
			</tr>
			</table>
            <div style="text-align: center">
              <table style="margin:15px auto 0px">
                <tr>
<?
if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
                  <td>
                    <table class="buttonTop" cellspacing="0" onclick="entryManager.preview()">
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="word-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('미리보기')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
                  </td>
<?
}
?>
                  <td>
                    <table class="buttonTop" cellspacing="0" onclick="entryManager.save()">
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="word-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
                  </td>
                  <td>
<?
if (!isset($_GET['popupEditor'])) {
	if (defined('__TATTERTOOLS_NOTICE__')) {
?>
                    <table class="buttonTop" cellspacing="0" onclick="document.forms[0].action='<?=$blogURL?>/owner/notice'; document.forms[0].submit()">
<?
	} else {
?>
                    <table class="buttonTop" cellspacing="0" onclick="document.forms[0].action='<?=$blogURL?>/owner/entry'; document.forms[0].submit()">
<?
	}
?>
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="word-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('목록으로')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
<?
}
?>
                  </td>
                </tr>
              </table>
            </div>
<?
if (!defined('__TATTERTOOLS_NOTICE__')) {
?>
	<input type="hidden" name="categoryAtHome" value="<?=(isset($_POST['category']) ? $_POST['category'] : '0')?>" />
	
<?
}
?>
	<input type="hidden" name="withSearch" value="<?=(empty($_POST['search']) ? '' : 'on')?>" />
	<input type="hidden" name="search" value="<?=(isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '')?>" />
<script type="text/javascript">
	entryManager = new EntryManager();
</script>
<?
if (isset($_GET['popupEditor']))
	require ROOT . '/lib/piece/owner/footer8.php';
else
	require ROOT . '/lib/piece/owner/footer.php';
?>