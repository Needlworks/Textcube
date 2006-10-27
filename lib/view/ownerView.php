<?php
function printOwnerEditorScript($entryId = false) {
	global $owner, $database, $skin, $hostURL, $blogURL, $service;
	
	$contentWidth = 500;
	
	if($skin = fetchQueryCell("SELECT skin FROM {$database['prefix']}SkinSettings WHERE owner = $owner")) {
		if($xml = @file_get_contents(ROOT."/skin/$skin/index.xml")) {
			$xmls = new XMLStruct();
			$xmls->open($xml, $service['encoding']);
			if($xmls->getValue('/skin/default/contentWidth')) {
				$contentWidth = $xmls->getValue('/skin/default/contentWidth');
			}
		}
	}
?>
<script type="text/javascript">
//<![CDATA[
	var entryId = <?php echo $entryId ? $entryId : 0;?>; 
	var skinContentWidth = <?php echo $contentWidth;?>;
	var s_enterURL = "<?php echo _t('URL을 입력하세요.');?>";
	var s_unknownFileType = "<?php echo _t('알 수 없는 형식의 파일명입니다.');?>";
	var s_enterObjectTag = "<?php echo _t('OBJECT 태그만 입력하세요.');?>";
	var s_enterCorrectObjectTag = "<?php echo _t('잘못된 OBJECT 태그입니다.');?>";
	var s_selectBoxArea = "<?php echo _t('박스로 둘러쌀 영역을 선택해주세요');?>";
	var s_selectLinkArea = "<?php echo _t('링크를 만들 영역을 선택해주세요');?>";

	window.addEventListener("scroll", function() { editor.setPropertyPosition(); }, false);

	function savePosition() {
		if (document.forms[0].content.createTextRange)
			document.forms[0].content.currentPos = document.selection.createRange().duplicate();
		return true;
	}
	
	function insertTag(prefix, postfix) {
		var oTextarea = document.forms[0].content;
		if(isSafari) 
			var selection = window.getSelection;
		else
			var selection = document.selection;
		
		if (selection) {			
			if (oTextarea.createTextRange && oTextarea.currentPos) {				
				oTextarea.currentPos.text = prefix + oTextarea.currentPos.text + postfix;
				oTextarea.focus();
				savePosition(oTextarea);
			}
			else
				oTextarea.value = oTextarea.value + prefix + postfix;
		}
		else if (oTextarea.selectionStart != null && oTextarea.selectionEnd != null) {
			var s1 = oTextarea.value.substring(0, oTextarea.selectionStart);
			var s2 = oTextarea.value.substring(oTextarea.selectionStart, oTextarea.selectionEnd);
			var s3 = oTextarea.value.substring(oTextarea.selectionEnd);
			oTextarea.value = s1 + prefix + s2 + postfix + s3;
		}
		else
			oTextarea.value += prefix + postfix;
			
		return true;	
	}
	
	function insertColorTag(col1) {
		hideLayer("colorPalette");
		TTCommand("Color", col1);
	}
	
	function insertMarkTag(col1) {
		hideLayer("markPalette");
		TTCommand("Mark", col1);
	}
	
	function linkImage1(align, caption) {
		var oSelect = document.forms[0].fileList;
		if (oSelect.selectedIndex < 0) {
			alert("<?php echo _t('파일을 선택하십시오.');?>");
			return false;
		}
		var value = oSelect.options[oSelect.selectedIndex].value.split("|");
		var result_w = new RegExp("width=['\"]?(\\d+)").exec(value[1]);
		var result_h = new RegExp("height=['\"]?(\\d+)").exec(value[1]);
		catption = (caption == undefined) ? "" : caption.replaceAll("|", "");
		if(result_w && result_h)
		{
			width = result_w[1];
			height = result_h[1];
			if(width > skinContentWidth)
			{
				height = parseInt(height * (skinContentWidth / width));
				value[1] = value[1].replace(new RegExp("width=['\"]?\\d+['\"]?", "gi"), 'width="' + skinContentWidth + '"');
				value[1] = value[1].replace(new RegExp("height=['\"]?\\d+['\"]?", "gi"), 'height="' + height + '"');
			}
		}
		if(editor.isMediaFile(value[0])) {
			getObject("propertyInsertObject_type").value = "url";
			getObject("propertyInsertObject_url").value = "<?php echo "$hostURL$blogURL";?>" + "/attachment/" + value[0];
			TTCommand("InsertObject");
			return;
		}
		try {
			if(editor.editMode == "WYSIWYG")
			{
				var src = editor.propertyFilePath + value[0];
				var attributes = editor.addQuot(value[1]);
				
				if(!(new RegExp("\.(jpe?g|gif|png|bmp)$", "i").test(value[0])))
				{
					src = servicePath + adminSkin + "/image/spacer.gif";
					value[1] = editor.styleUnknown;
					attributes = "";				
				}
				
				switch(align)
				{
					case "1L":
						var prefix = "<img class=\"tatterImageLeft\" src=\"" + src + "\" " + value[1] + " longdesc=\"1L|" + value[0] + "|" + attributes + "|" + catption + "\" />";
						break;
					case "1C":
						var prefix = "<img class=\"tatterImageCenter\" src=\"" + src + "\" " + value[1] + " longdesc=\"1C|" + value[0] + "|" + attributes + "|" + catption + "\" />";
						break;
					case "1R":
						var prefix = "<img class=\"tatterImageRight\" src=\"" + src + "\" " + value[1] + " longdesc=\"1R|" + value[0] + "|" + attributes + "|" + catption + "\" />";
						break;
				}
				TTCommand("Raw", prefix);
				return true;
			}
		} catch(e) { }
		insertTag('[##_' + align + '|' + value[0] + '|' + value[1] + '|' + caption + '_##]', "");
		return true;
	}
	
	function linkImage2() {
		var oSelect = document.forms[0].fileList;
		var count = 0;
		var prefix = '';
		for (var i = 0; i < oSelect.options.length; i++) {
			if (oSelect.options[i].selected == true) {
				var value = oSelect.options[i].value.split("|");
				var result_w = new RegExp("width=['\"]?(\\d+)").exec(value[1]);
				var result_h = new RegExp("height=['\"]?(\\d+)").exec(value[1]);
				if(result_w && result_h)
				{
					width = result_w[1];
					height = result_h[1];
					if(width > skinContentWidth / 2)
					{
						height = parseInt(height * (skinContentWidth / 2 / width));
						value[1] = value[1].replace(new RegExp("width=['\"]?\\d+['\"]?", "gi"), 'width="' + parseInt(skinContentWidth / 2) + '"');
						value[1] = value[1].replace(new RegExp("height=['\"]?\\d+['\"]?", "gi"), 'height="' + height + '"');
					}
				}
				prefix = prefix + '^' + value[0] + '|' + value[1] + '|';
				count ++;
			}
		}
		if (count != 2) {
			alert("<?php echo _t('파일 리스트에서 이미지를 2개 선택해 주십시오. (ctrl + 마우스 왼쪽 클릭)');?>");
			return false;
		}
		var imageinfo = prefix.split("^");
		try {
			if(editor.editMode == "WYSIWYG") {			
				var prefix = '<img class="tatterImageDual" src="' + servicePath + adminSkin + '/image/spacer.gif" width="200" height="100" longdesc="2C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '" />';
				TTCommand("Raw", prefix);
				return true;
			}
		} catch(e) { }
		insertTag('[##_2C|' + imageinfo[1] + '|' + imageinfo[2] + '_##]', '');
		return true;
	}
	
	function linkImage3() {
		var oSelect = document.forms[0].fileList;
		var count = 0;
		var prefix = '';
		for (var i = 0; i < oSelect.options.length; i++) {
			if (oSelect.options[i].selected == true) {
				var value = oSelect.options[i].value.split("|");
				var result_w = new RegExp("width=['\"]?(\\d+)").exec(value[1]);
				var result_h = new RegExp("height=['\"]?(\\d+)").exec(value[1]);
				if(result_w && result_h)
				{
					width = result_w[1];
					height = result_h[1];
					if(width > skinContentWidth / 3)
					{
						height = parseInt(height * (skinContentWidth / 3 / width));
						value[1] = value[1].replace(new RegExp("width=['\"]?\\d+['\"]?", "gi"), 'width="' + parseInt(skinContentWidth / 3) + '"');
						value[1] = value[1].replace(new RegExp("height=['\"]?\\d+['\"]?", "gi"), 'height="' + height + '"');
					}
				}
				prefix = prefix + '^' + value[0] + '|' + value[1] + '|';
				count++;
			}
		}
		if (count != 3) {
			alert("<?php echo _t('파일 리스트에서 이미지를 3개 선택해 주십시오. (ctrl + 마우스 왼쪽 클릭)');?>");
			return false;
		}
		var imageinfo = prefix.split("^");
		try {
			if(editor.editMode == "WYSIWYG") {
				var prefix = '<img class="tatterImageTriple" src="' + servicePath + adminSkin + '/image/spacer.gif" width="300" height="100" longdesc="3C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '|' + editor.addQuot(imageinfo[3]) + '" />';
				TTCommand("Raw", prefix);
				return true;
			}
		} catch(e) { }
		insertTag('[##_3C|' + imageinfo[1] + '|' + imageinfo[2] + '|' + imageinfo[3] + '_##]', '');
		return true;
	}
	
	function linkImageFree() {
		var isWYSIWYG = false;
		try {
			if(editor.editMode == "WYSIWYG")
				isWYSIWYG = true;
		} catch(e) { }		
		var oSelect = document.forms[0].fileList;
		var prefix = '';
		for (var i = 0; i < oSelect.options.length; i++) {
			if (oSelect.options[i].selected == true) {
				var value = oSelect.options[i].value.split("|");
				if(new RegExp("\\.(gif|jpe?g|png|bmp)$", "i").test(value[0])) {
					if(isWYSIWYG)
						prefix += '<img class="tatterImageFree" src="' + editor.propertyFilePath + value[0] + '" longdesc="[##_ATTACH_PATH_##]/' + value[0] + '" ' + value[1] + ' />';
					else
						prefix += '<img src="[##_ATTACH_PATH_##]/' + value[0] + '" ' + value[1] + ' />';
				}
			}
		}
		TTCommand("Raw", prefix);
		return true;
	}
	
	var iMazingProperties = new Array();
	iMazingProperties['width'] = 450;
	iMazingProperties['height'] = 350;
	iMazingProperties['frame'] = 'net_imazing_frame_none';
	iMazingProperties['transition'] = 'net_imazing_show_window_transition_alpha';
	iMazingProperties['navigation'] = 'net_imazing_show_window_navigation_simple';
	iMazingProperties['slideshowInterval'] = 10;
	iMazingProperties['page'] = 1;
	iMazingProperties['align'] = 'h';
	iMazingProperties['skinPath'] = '<?php echo $service['path'];?>/script/gallery/iMazing/';
	
	function viewImazing()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?php echo _t('파일을 선택하십시오.');?>");
				return false;
			}
			var value = oSelect.options[oSelect.selectedIndex].value.split("|");
			
			var fileList = '';
			for (var i = 0; i<oSelect.length; i++) {
				if (!oSelect.options[i].selected) continue;
				file = (oSelect[i].value.substr(oSelect[i].value,oSelect[i].value.indexOf('|')));				
				if(new RegExp("\\.jpe?g$", "gi").exec(file))
					fileList += file+'||';
			}
			if(fileList == '') {
				alert("<?php echo _t('이미지 파일만 삽입 가능합니다.');?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);
			var Properties = '';
			for (var name in iMazingProperties) {
				Properties += name+'='+iMazingProperties[name]+' ';
			}
			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterImazing" src="' + servicePath + adminSkin + '/image/spacer.gif" width="400" height="300" longdesc="iMazing|' + fileList + '|' + Properties + '|" />');
					return true;
				}
			} catch(e) { }
	
			insertTag('[##_iMazing|' + fileList + '|' + Properties +'|_##]', '');
			return true;
		} catch(e) {
			return false;
		}
	}
	
	function viewGallery()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?php echo _t('파일을 선택하십시오.');?>");
				return false;
			}
			var value = oSelect.options[oSelect.selectedIndex].value.split("|");
			
			var fileList = '';
			for (var i = 0; i<oSelect.length; i++) {
				if (!oSelect.options[i].selected) continue;
				file = (oSelect[i].value.substr(oSelect[i].value,oSelect[i].value.indexOf('|')));
				if(new RegExp("\\.(gif|jpe?g|png)$", "gi").exec(file))
					fileList += file+'||';
			}
			if(fileList == '') {
				alert("<?php echo _t('이미지 파일만 삽입 가능합니다.');?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);
			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterGallery" src="' + servicePath + adminSkin + '/image/spacer.gif" width="400" height="300" longdesc="Gallery|' + fileList + '|width=&quot;400&quot; height=&quot;300&quot;" />');
					return true;
				}
			} catch(e) { }
			insertTag('[##_Gallery|' + fileList + '|width="400" height="300"_##]', '');
			return true;
		} catch(e) {
			return false;
		}
	}	
	
	function viewJukebox()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?php echo _t('파일을 선택하십시오.');?>");
				return false;
			}
			var value = oSelect.options[oSelect.selectedIndex].value.split("|");
			
			var fileList = '';
			for (var i = 0; i<oSelect.length; i++) {
				if (!oSelect.options[i].selected) continue;
				file = (oSelect[i].value.substr(oSelect[i].value,oSelect[i].value.indexOf('|')));
				if(new RegExp("\\.mp3$", "gi").exec(file))
				{
					fileList += file + "|";
					if(result = new RegExp("(.*)\\.mp3", "gi").exec(oSelect.options[i].text))
						fileList += result[1].replaceAll("|","") + "|";
					else
						fileList += "|";
				}
			}
			if(fileList == '') {
				alert("<?php echo _t('MP3 파일만 삽입 가능합니다.');?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);
			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterJukebox" src="' + servicePath + adminSkin + '/image/spacer.gif" width="200" height="30" longdesc="Jukebox|' + fileList + '|autoplay=0 visible=1|" />');
					return true;
				}
			} catch(e) { }
			insertTag('[##_Jukebox|' + fileList + '|autoplay=0 visible=1|_##]', '');
			return true;
		} catch(e) {
			return false;
		}
	}	
//]]>
</script>
<?php
}

function printEntryFileList($attachments, $param) {
	global $owner, $service, $blogURL, $adminSkinSetting;
	if(empty($attachments) || (
	strpos($attachments[0]['name'] ,'.gif') === false &&
	strpos($attachments[0]['name'] ,'.jpg') === false &&
	strpos($attachments[0]['name'] ,'.png') === false)) {
		$fileName =  "{$service['path']}{$adminSkinSetting['skin']}/image/spacer.gif";
	} else {
		$fileName = "{$service['path']}/attach/$owner/{$attachments[0]['name']}";
	}
?>
											<div id="previewSelected" style="width: 120px; height: 90px;"><span class="text"><?php echo _t('미리보기');?></span></div>
											
											<div id="attachManagerSelectNest">				
												<span id="attachManagerSelect">
													<select id="fileList" name="fileList" multiple="multiple" size="8" onchange="selectAttachment();">
<?php 
	$initialFileListForFlash = '';
	$enclosureFileName = '';
	foreach ($attachments as $i => $attachment) {
		
		if (strpos ($attachment['mime'], 'application') !== false ) {
			$class = 'class="MimeApplication"';
		} else if (strpos ($attachment['mime'], 'audio') !== false ) {
			$class = 'class="MimeAudio"';
		} else if (strpos ($attachment['mime'], 'image') !== false ) {
			$class = 'class="MimeImage"';
		} else if (strpos ($attachment['mime'], 'message') !== false ) {
			$class = 'class="MimeMessage"';
		} else if (strpos ($attachment['mime'], 'model') !== false ) {
			$class = 'class="MimeModel"';
		} else if (strpos ($attachment['mime'], 'multipart') !== false ) {
			$class = 'class="MimeMultipart"';
		}  else if (strpos ($attachment['mime'], 'text') !== false ) {
			$class = 'class="MimeText"';
		}  else if (strpos ($attachment['mime'], 'video') !== false ) {
			$class = 'class="MimeVideo"';
		} else {
			$class = '';
		}
		if (!empty($attachment['enclosure']) && $attachment['enclosure'] == 1) {
			$style = 'style="background-color:#c6a6e7; color:#000000"';		
			$enclosureFileName = $attachment['name'];
		} else {
			$style = '';
			$prefix = '';
		}
		
		$value = htmlspecialchars(getAttachmentValue($attachment));
		$label = $prefix.htmlspecialchars(getPrettyAttachmentLabel($attachment));
		
		$initialFileListForFlash .= escapeJSInAttribute($value.'(_!'.$label.'!^|');
?>
		<option  <?php echo $style;?> value="<?php echo $value;?>"><?php echo $label;?></option>
<?php
	}
?>
													</select>
												</span>
											</div>
											
											<script type="text/javascript">
												//<![CDATA[
													function addAttachment() {
														if(isIE) {
															document.frames[0].document.forms[0].action = "<?php echo $param['singleUploadPath'];?>";
															document.frames[0].document.forms[0].attachment.click();
														} else {
															var attachHidden = document.getElementById('attachHiddenNest');
															attachHidden.contentDocument.forms[0].action = "<?php echo $param['singleUploadPath'];?>";
															attachHidden.contentDocument.forms[0].attachment.click();
														}
													}
													
													function deleteAttachment() {
														var fileList = document.getElementById('fileList');		
														
														if (fileList.selectedIndex < 0) {
															alert("<?php echo _t('삭제할 파일을 선택해 주십시오\t');?>");
															return false;
														}
														
														try {
															
															var targetStr = '';
															deleteFileList = new Array();
															for(var i=0; i<fileList.length; i++) {
																if(fileList[i].selected) {
																	var name = fileList[i].value.split("|")[0];
																	targetStr += name+'!^|';
																	deleteFileList.push(i);
																}
															}
														} catch(e) {
															alert("<?php echo _t('파일을 삭제하지 못했습니다');?> ::"+e.message);
														}
												
														var request = new HTTPRequest("POST", "<?php echo $param['deletePath'];?>");
														request.onVerify = function () { 
															return true 
														}
												
														request.onSuccess = function() {				
															for(var i=deleteFileList.length-1; i>=0; i--) {
																fileList.remove(deleteFileList[i]);	
															}
															
															if (fileList.options.length == 0)
																document.getElementById('previewSelected').innerHTML = '';
															else {
																fileList.selectedIndex = 0;
																selectAttachment();
															}
															refreshAttachFormSize();
															refreshFileSize();
														}
														
														request.onError = function() {
															alert("<?php echo _t('파일을 삭제하지 못했습니다');?>");
														}
														request.send("names="+targetStr);
													}

													function selectAttachment() {
														try {
														width = document.getElementById('previewSelected').clientWidth;
														height = document.getElementById('previewSelected').clientHeight;
														var code = '';
														var fileList = document.getElementById('fileList');
														if (fileList.selectedIndex < 0)
															return false;
														var fileName = fileList.value.split("|")[0];
														
														if((new RegExp("\\.(gif|jpe?g|png)$", "gi").exec(fileName))) {
															try {
																var width = new RegExp('width="(\\d+)').exec(fileList.value);
																width = width[1];
																var height = new RegExp('height="(\\d+)').exec(fileList.value);
																height = height[1];
																if(width > 120) {
																	height = 120 / width * height;
																	width = 120;
																}
																if(height > 90) {
																	width = 90 / height * width;
																	height = 90;
																}
																document.getElementById('previewSelected').innerHTML = '<img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'?randseed='+Math.random()+'" width="' + parseInt(width) + '" height="' + parseInt(height) + '" alt="" style="margin-top: ' + ((90-height)/2) + 'px" onerror="this.src=\'<?php echo $service['path'] . $adminSkinSetting['skin'];?>/image/spacer.gif\'"/>';																
															}
															catch(e) { }
															return false;
														}
														
														if((new RegExp("\\.(mp3)$", "gi").exec(fileName))) {
															var str = getEmbedCode("<?php echo $service['path'];?>/script/jukebox/flash/mini.swf","100%","100%", "jukeBox0Flash","#FFFFFF", "sounds=<?php echo $service['path'];?>/attach/<?php echo $owner;?>/"+fileName+"&autoplay=false", "false");
															writeCode(str, 'previewSelected');
															return false;
														}
														
														if((new RegExp("\\.(swf)$", "gi").exec(fileName))) {			
															
															code = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%"><param name="movie" value="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'"/><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF"/>';
															code += '<!--[if !IE]> <--><object type="application/x-shockwave-flash" data="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'" width="100%" height="100%"><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF"/><\/object><!--> <![endif]--><\/object>';
															
															writeCode(code,'previewSelected');
															return false;
														}
														
														if((new RegExp("\\.(mov)$", "gi").exec(fileName))) {			
															code = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="'+width+'" height="'+height+'"><param name="src" value="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'"/><param name="controller" value="true"><param name="autoplay" value="false"><param name="scale" value="Aspect">';
															code += '<!--[if !IE]> <--><object type="video/quicktime" data="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'" width="'+width+'" height="'+height+'" showcontrols="true" TYPE="video/quicktime" scale="Aspect" nomenu="true"><param name="showcontrols" value="true"><param name="autoplay" value="false"><param name="scale" value="ToFit"><\/object><!--> <![endif]--><\/object>';
															writeCode(code,'previewSelected');
															return false;
														}
													
														if((new RegExp("\\.(mp2|wma|mid|midi|mpg|wav|avi|mp4)$", "gi").exec(fileName))) {
															code ='<object width="'+width+'" height="'+height+'" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" standby="Loading for you" type="application/x-oleobject" align="middle">';		
															code +='<param name="FileName" value="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'">';
															code +='<param name="ShowStatusBar" value="False">';
															code +='<param name="DefaultFrame" value="mainFrame">';
															code +='<param name="autoplay" value="false">';
															code +='<param name="showControls" value="true">';
															code +='<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'" align="middle" width="'+width+'" height="'+height+'" showControls="true" defaultframe="mainFrame" showstatusbar="false" autoplay="false"><\/embed>';
															code +='<\/object>';
															
															writeCode(code,'previewSelected');
															
															return false;
														}
														
														if((new RegExp("\\.(rm|ram)$", "gi").exec(fileName))) {		
														/*
															code = '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+width+'" height="'+height+'"><param name="src" value="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'"/><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE">';
															code += '<!--[if !IE]> <--><object type="audio/x-pn-realaudio-plugin" data="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/'+fileName+'" width="'+width+'" height="'+height+'" ><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE"><\/object><!--> <![endif]--><\/object>';			
														*/
														}
														
														if (code == undefined || code == '') {
															document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?php echo _t('미리보기');?><\/td><\/tr><\/table>";
															return true;
														}
														
																
														
														return false;
														} catch (e) {
															document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?php echo _t('미리보기');?><\/td><\/tr><\/table>";	
															alert(e.message);
															return true;
														}
													}				

													function downloadAttachment() {
														try {
															var fileList = document.getElementById('fileList');
															if (fileList.selectedIndex < 0) {
																return false;
															}
															for(var i=0; fileList.length; i++) {
																if (fileList[i].selected) {
																	var fileName = fileList[i].value.split("|")[0];
																	if(STD.isIE) {
																		document.getElementById('fileDownload').innerHTML='<iframe style="display:none;" src="'+blogURL+'\/attachment\/'+fileName+'"><\/iframe>';
																		
																	} else {
																		window.location = blogURL+'/attachment/'+fileName;
																	}
																	break;
																}
															}
														} catch(e) {
															alert(e.message);
														}
													}

													function disablePageManager() {
														try {
															pageHolding = entryManager.pageHolder.isHolding;
															entryManager.pageHolder.isHolding = function () {
																return false;
															}
															STD.removeEventListener(window);					
															window.removeEventListener("beforeunload", PageMaster.prototype._onBeforeUnload, false);					
														} catch(e) {
														}
													}
													
													STD.addEventListener(window);
													window.addEventListener("load", disablePageManager, false);
													
													function enablePageManager() {
														try {
															entryManager.pageHolder.isHolding = pageHolding ;
															STD.addEventListener(window);
															window.addEventListener("beforeunload", PageMaster.prototype._onBeforeUnload, false);				
														} catch(e) {
														}	
													}
													
													function stripLabelToValue(fileLabel) {
														var pos = fileLabel.lastIndexOf('(');
														return fileLabel.substring(0,pos-1);	
													}
													
													function refreshAttachFormSize() {
														fileListObj = document.getElementById('fileList');
														fileListObj.setAttribute('size',Math.max(8,Math.min(fileListObj.length,30)));
													}
													
													function refreshAttachList() {
														var request = new HTTPRequest("POST", "<?php echo $param['refreshPath'];?>");
														request.onVerify = function () { 	
															return true 
														}
														request.onSuccess = function() {
															var fileListObj = document.getElementById("attachManagerSelect");
															fileListObj.innerHTML = this.getText();
															refreshAttachFormSize();
															//getUploadObj().setAttribute('width',1)
															//getUploadObj().setAttribute('height',1)
															
															if (isIE) {
																document.getElementById('uploadBtn').style.display  = 'block'			
																document.getElementById('stopUploadBtn').style.display  = 'none'			
															} else {
																document.getElementById('uploadBtn').disabled=false;					
															}
															
															document.getElementById('uploaderNest').innerHTML = uploaderStr
															refreshFileSize();						
															setTimeout("enablePageManager()", 2000);						
														}
														request.onError = function() {}
														request.send();					
													}
													
													function uploadProgress(target,loaded, total) {
														loaded = Number(loaded);
														total = Number(total);
														var fileListObj = document.getElementById("fileList");					
														for(var i=0; i<fileListObj.length; i++) {
															if (fileListObj[i].getAttribute("value") == target) {
																fileListObj[i].innerHTML = target+" "+(Math.ceil(100*loaded/total))+"%";
																break;
															}
														}
													}
													
													function uploadComplete(target,size) {
														loaded = Number(loaded);
														total = Number(total);
														var fileListObj = document.getElementById("fileList");
														for(var i=0; i<fileListObj.length; i++) {
															if (fileListObj[i].getAttribute("value") == target) {
																fileListObj[i].innerHTML = target+" "+(Math.ceil(100*loaded/total))+"%";
																break;
															}
														}				
													}
													
													function addFileList(list) {
														var fileListObj = document.getElementById("fileList");
														var listTemp = list.split("(_!");
														var fileLabel = listTemp[0];
														var fileValue = listTemp[1];
														var fileListObj = document.getElementById("fileList");
														for(var i=0; i<fileListObj.length; i++) {
															if (stripLabelToValue(fileLabel).indexOf(fileListObj[i].getAttribute("value")) != -1) {
																var oOption = document.createElement("option");
																oOption.innerHTML= fileLabel;
																oOption.setAttribute("value",fileValue);
																fileListObj.replaceChild(oOption,fileListObj[i]);
																break;
															}
														}
													}
													
													function newLoadItem(fileValue) {
														var fileListObj = document.getElementById("fileList");
														var fileListObj = document.getElementById("fileList");
														for(var i=0; i<fileListObj.length; i++) {
															if (fileValue.indexOf(fileListObj[i].getAttribute("value")) != -1) {
																fileListObj[i].style.backgroundColor="#C8DAF3";
																break;
															}
														}	
													}
													
													function setFileList() {
														try {
															list = getUploadObj().GetVariable("/:listStr");						
														} catch(e) {
															alert(e.message);
														}
														var fileListObj = document.getElementById("fileList");										
														var listTemp = list.split("!^|");					
														for(var i=0; i<listTemp.length; i++) {						
															temp = listTemp[i].split('(_!');
															var fileName = temp[0];
															var fileSize = temp[1];
															if(fileName == undefined || fileSize == undefined) 
																continue;							
															var oOption = document.createElement("option");
															oOption.innerHTML= fileName+' ('+Math.ceil((fileSize/1024))+'KB)  <?php echo _t('대기 중..');?>';
															oOption.setAttribute("value",fileName);
															oOption.style.backgroundColor="#A4C3F0";
															fileListObj.insertBefore(oOption,fileListObj[i]);
															if(i == 0) {
																newLoadItem(fileName);
															}
														}
														fileListObj.setAttribute('size',Math.max(8,Math.min(fileListObj.length,30)));
														getUploadObj().setAttribute('width',416)
														getUploadObj().setAttribute('height',25)
														//document.getElementById('uploadBtn').disabled=true;		
														if(isIE) {
															document.getElementById('uploadBtn').style.display  = 'none'			
															document.getElementById('stopUploadBtn').style.display  = 'block'			
														} else {
															document.getElementById('uploadBtn').disabled=true;		
														}
													}
													
													function selectFileList(value) {
														selectedFiles = value.split("!^|");
														var fileListObj = document.getElementById("fileList");
														for(var i=0; i<fileListObj.length; i++) {
															for(var j=0; j<selectedFiles.length; j++) {
																if (fileListObj[i].getAttribute("value") == selectedFiles[j]) {
																	fileListObj[i].setAttribute("selected","selected");
																	selectAttachment();
																	break;
																}
																
																fileListObj[i].setAttribute("selected","");							
															}
														}
														refreshAttachFormSize();
													}
													
													function disabledDeleteBtn() {
														if(document.getElementById('fileList').length>0) {
															document.getElementById('deleteBtn').disabled = false;
														} else {
															document.getElementById('deleteBtn').disabled = true;
														}
													}
													
													function removeUploadList(list) {
														selectedFiles = list.split("!^|");
														var fileListObj = document.getElementById("fileList");
														for(var j=0; j<selectedFiles.length; j++) {
															for(var i=0; i<fileListObj.length; i++) {						
																if(selectedFiles[j] == undefined) 
																	continue;
																if (fileListObj[i].getAttribute("value") == selectedFiles[j]) {								
																	fileListObj.remove(i);
																	break;
																}
															}
														}
														refreshAttachFormSize();
													}
													
													function browser() {
														disablePageManager();
														getUploadObj().SetVariable('/:openBrowser','true');
													}
													
													function stopUpload() {
														getUploadObj().SetVariable('/:stopUpload','true');
													}
													
													function refreshFileSize() {
														try {
															var request = new HTTPRequest("POST", "<?php echo $param['fileSizePath'];?>");
															request.onVerify = function () {
																return true;
															}
															
															request.onSuccess = function() {
																try {
																	var result = this.getText("/response/result");
																	document.getElementById('fileSize').innerHTML = result;
																} catch(e) {
																
																}
															}
															
															request.onError = function() {
															}
															//disablePageManager();
															request.send();
															
														} catch(e) {
															alert(e.message);
														}
													}
													function getUploadObj() {
														try {		
															var result;			
															if(isIE) 
																result = document.getElementById("uploader");
															else
																result = document.getElementById("uploader2");
															if (result == null)
																return false;
															else
																return result;
														} catch(e) {
															return false;
														}
													}
													refreshAttachFormSize();
												//]]>
											</script>
											
<?php
	require_once ROOT.'/script/detectFlash.inc';
	$maxSize = min( return_bytes(ini_get('upload_max_filesize')) , return_bytes(ini_get('post_max_size')) );
?>

											<div id="uploaderNest">
												<script type="text/javascript">
													//<![CDATA[
														var requiredMajorVersion = 8;
														var requiredMinorVersion = 0;
														var requiredRevision = 0;
														var jsVersion = 1.0;
														
														var hasRightVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
														uploaderStr = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="uploader"'
															+ 'width="0" height="0"'
															+ 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab">'
															+ '<param name="movie" value="<?php echo $service['path'];?>/script/uploader/uploader.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><param name="scale" value="noScale" /><param name="wmode" value="transparent" /><param name="FlashVars" value="uploadPath=<?php echo $param['uploadPath'];?>&labelingPath=<?php echo $param['labelingPath'];?>&maxSize=<?php echo $maxSize;?>&sessionName=TSSESSION&sessionValue=<?php echo $_COOKIE['TSSESSION'];?>" />'
															+ '<embed id="uploader2" src="<?php echo $service['path'];?>/script/uploader/uploader.swf" flashvars="uploadPath=<?php echo $param['uploadPath'];?>&labelingPath=<?php echo $param['labelingPath'];?>&maxSize=<?php echo $maxSize;?>&sessionName=TSSESSION&sessionValue=<?php echo $_COOKIE['TSSESSION'];?>" width="1" height="1" align="middle" wmode="transparent" quality="high" bgcolor="#ffffff" scale="noScale" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /><\/embed><\/object>';
														
														if (hasRightVersion) {
															if(<?php echo (isset($service['flashuploader']) && $service['flashuploader'] === false) ? 'false' : 'hasRightVersion';?>){ writeCode(uploaderStr); }
														}
													//]]>
												</script>
											</div>
<?php
}

function printEntryFileUploadButton($entryId) {
	global $owner, $service;
?>
										<div id="fileUploadNest" class="container">
											<script type="text/javascript">
												//<![CDATA[
													//var attachId = 0;
													function makeCrossDamainSubmit(uri,userAgent) {
														var property =new Array();
														property['ie'] = new Array();
														property['ie']['width'] = '225px';
														property['ie']['height'] = '25px';		
														
														property['moz'] = new Array();
														property['moz']['width'] = '215px';
														property['moz']['height'] = '22px';		
														
														property['etc'] = new Array();
														property['etc']['width'] = '240px';
														property['etc']['height'] = '22px';
														
																
														var str = '<iframe id="attachHiddenNest" src="' + uri + '" style="display: block; height: ' + property[userAgent]['height']+'; width: ' + property[userAgent]['width'] + ';" frameborder="no" scrolling="no"><\/iframe>';						
														document.getElementById('fileUploadNest').innerHTML = str + document.getElementById('fileUploadNest').innerHTML;
														/*if (document.getElementById('attachHiddenNest_' + (attachId - 1))) {
															document.getElementById('attachHiddenNest_' + (attachId - 1)).style.display = "none";
															document.getElementById('attachHiddenNest_' + (attachId - 1)).style.width = 0;
															document.getElementById('attachHiddenNest_' + (attachId - 1)).style.height = 0;
														}
														attachId++;*/
													}
													
													if (getUploadObj()) {
														try {
															document.write('<a id="uploadBtn" class="upload-button button" href="#void" onclick="browser(); return false"><span class="text"><?php echo _t('파일 업로드');?><\/span><\/a>');
															document.write('<a id="stopUploadBtn" class="stop-button button" href="#void" onclick="stopUpload(); return false" style="display: none;"><span class="text"><?php echo _t('업로드 중지');?><\/span><\/a>');			
														} catch(e) {
															
														}								
													} else {
														if(isIE) {
															makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId;?>","ie");
														} else if(isMoz) {
															makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId;?>","moz");
														} else {
															makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId;?>","etc");
														}
													}
												//]]>
											</script>
												
											<a id="deleteBtn" class="button" href="#void" onclick="deleteAttachment();"><span class="text"><?php echo _t('삭제하기');?></span></a>
											<div id="fileSize">
												<?php echo getAttachmentSizeLabel($owner, $entryId);?>
											</div>
											<div id="fileDownload" class="system-message" style="display: none;"></div>
										</div>
<?php
}

function printEntryEditorProperty($alt=NULL) {
	global $service;
	$fixPosition = getUserSetting('editorPropertyPositionFix', 0);
?>
											<script type="text/javascript">
												//<![CDATA[
													function checkResampling(flag, type, num) {
														resampleCheckbox = document.getElementById("propertyImage" + type + "_resample" + num);
														watermarkCheckbox = document.getElementById("propertyImage" + type + "_watermark" + num);
														
														if (flag == "resample" && resampleCheckbox.checked == false && watermarkCheckbox.checked == true)
															watermarkCheckbox.checked = false;
														else if (flag == "watermark" && watermarkCheckbox.checked == true && resampleCheckbox.checked == false)
															resampleCheckbox.checked = true;
													}
												//]]>
											</script>
												
											<div id="propertyHyperLink" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyHyperlink-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyHyperlink-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('하이퍼링크');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyInsertObject_url"><?php echo _t('URL');?></label></dt>
														<dd><input type="text" id="propertyHyperLink_url" class="input-text" onkeyup="editor.setProperty()"/></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyInsertObject_type"><?php echo _t('대상');?></label></dt>
														<dd>
															<select id="propertyHyperLink_target" style="width: 105px" >
																<option value="_blank"><?php echo _t('새창');?></option>
																<option value="_self"><?php echo _t('현재창');?></option>
																<option value=""><?php echo _t('사용 안함');?></option>
															</select>
														</dd>
													</dl>
												</div>
												<div class="button-box">
													<span class="insert-button button" onclick="TTCommand('ExcuteCreateLink')"><span class="text"><?php echo _t('적용하기');?></span></span>
													<span class="divider"> | </span>
													<span class="cancel-button button" onclick="TTCommand('CancelCreateLink')"><span class="text"><?php echo _t('취소하기');?></span></span>
												</div>
											</div>

											<div id="propertyInsertObject" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyInsertObject-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyInsertObject-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('오브젝트 삽입');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyInsertObject_type"><?php echo _t('유형');?></label></dt>
														<dd>
															<select id="propertyInsertObject_type" style="width: 105px" onchange="getObject('propertyInsertObject_part_url').style.display=getObject('propertyInsertObject_part_raw').style.display='none';getObject('propertyInsertObject_part_' + this.value).style.display = 'block'">
																<option value="url"><?php echo _t('주소입력');?></option>
																<option value="raw"><?php echo _t('코드 붙여넣기');?></option>
															</select>
														</dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyInsertObject_url"><?php echo _t('파일 주소');?></label></dt>
														<dd><input type="text" id="propertyInsertObject_url" class="input-text" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyInsertObject_chunk"><?php echo _t('코드');?></label></dt>
														<dd>
															<textarea id="propertyInsertObject_chunk" cols="30" rows="10"></textarea>
														</dd>
													</dl>
												</div>
												<div class="button-box">
													<span class="insert-button button" onclick="TTCommand('InsertObject')"><span class="text"><?php echo _t('삽입하기');?></span></span>
													<span class="divider"> | </span>
													<span class="cancel-button button" onclick="TTCommand('HideObjectBlock')"><span class="text"><?php echo _t('취소하기');?></span></span>
												</div>
											</div>
											
											<div id="propertyImage1" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyImage1-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyImage1-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Image');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyImage1_width1"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage1_width1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage1_alt1"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage1_alt1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage1_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage1_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage1_resample1" onclick="checkResampling('resample', 1, 1); editor.setProperty()" /> <label for="propertyImage1_resample1"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage1_watermark1" onclick="checkResampling('watermark', 1, 1); editor.setProperty()" /> <label for="propertyImage1_watermark1"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyImage2" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyImage2-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyImage2-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Image');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('첫번째 이미지');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_width1"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_width1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_alt1"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_alt1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage2_resample1" onclick="checkResampling('resample', 2, 1); editor.setProperty()" /> <label for="propertyImage2_resample1"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage2_watermark1" onclick="checkResampling('watermark', 2, 1); editor.setProperty()" /> <label for="propertyImage2_watermark1"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('두번째 이미지');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_width2"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_width2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_alt2"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_alt2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage2_resample2"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage2_caption2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage2_resample2" onclick="checkResampling('resample', 2, 2); editor.setProperty()" /> <label for="propertyImage2_resample2"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage2_watermark2" onclick="checkResampling('watermark', 2, 2); editor.setProperty()" /> <label for="propertyImage2_watermark2"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyImage3" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyImage3-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyImage3-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Image');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('첫번째 이미지');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_width1"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_width1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_alt1"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_alt1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage3_resample1" onclick="checkResampling('resample', 3, 1); editor.setProperty()" /> <label for="propertyImage3_resample1"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage3_watermark1" onclick="checkResampling('watermark', 3, 1); editor.setProperty()" /> <label for="propertyImage3_watermark1"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('두번째 이미지');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_width2"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_width2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_alt2"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_alt2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_caption2"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_caption2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage3_resample2" onclick="checkResampling('resample', 3, 2); editor.setProperty()" /> <label for="propertyImage3_resample2"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage3_watermark2" onclick="checkResampling('watermark', 3, 2); editor.setProperty()" /> <label for="propertyImage3_watermark2"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('세번째 이미지');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_width3"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_width3" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_alt3"><?php echo _t('대체 텍스트');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_alt3" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyImage3_caption3"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyImage3_caption3" value="<?php echo $alt;?>" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="resample-property-box line">
														<dd>
															<input type="checkbox" id="propertyImage3_resample3" onclick="checkResampling('resample', 3, 3); editor.setProperty()" /> <label for="propertyImage3_resample3"><?php echo _t('이미지에 리샘플링을 적용합니다.');?></label><br />
															<input type="checkbox" id="propertyImage3_watermark3" onclick="checkResampling('watermark', 3, 3); editor.setProperty()" /> <label for="propertyImage3_watermark3"><?php echo _t('이미지에 워터마크를 찍습니다.');?></label>
														</dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyObject" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyObject-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyObject-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Object');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyObject_width"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject_width" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject_height"><?php echo _t('높이');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject_height" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject_chunk"><?php echo _t('코드');?></label></dt>
														<dd><textarea id="propertyObject_chunk" cols="30" rows="10" onkeyup="editor.setProperty()"></textarea></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyObject1" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyObject1-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyObject1-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Object 1');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyObject1_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject1_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject1_filename1"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject1_filename1" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyObject2" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyObject2-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyObject2-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Object');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('첫번째 오브젝트');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject2_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject2_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject2_filename1"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject2_filename1" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('두번째 오브젝트');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject2_caption2"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject2_caption2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject2_filename2"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject2_filename2" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyObject3" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyObject3-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyObject3-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Object');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('첫번째 오브젝트');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_caption1"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_caption1" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_filename1"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_filename1" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('두번째 오브젝트');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_caption2"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_caption2" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_filename2"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_filename2" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('세번째 오브젝트');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_caption3"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_caption3" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyObject3_filename3"><?php echo _t('파일명');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyObject3_filename3" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyiMazing" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyiMazing-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyiMazing-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('iMazing');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('설정');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_width"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyiMazing_width" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_height"><?php echo _t('높이');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyiMazing_height" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_frame"><?php echo _t('테두리');?></label></dt>
														<dd>
															<select id="propertyiMazing_frame" onchange="editor.setProperty()">
																<option value="net_imazing_frame_none"><?php echo _t('테두리 없음');?></option>
															</select>
														</dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_tran"><?php echo _t('장면전환효과');?></label></dt>
														<dd>
															<select id="propertyiMazing_tran" onchange="editor.setProperty()">
																<option value="net_imazing_show_window_transition_none"><?php echo _t('효과없음');?></option>
																<option value="net_imazing_show_window_transition_alpha"><?php echo _t('투명전환');?></option>
																<option value="net_imazing_show_window_transition_contrast"><?php echo _t('플래쉬');?></option>
																<option value="net_imazing_show_window_transition_sliding"><?php echo _t('슬라이딩');?></option>
															</select>
														</dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_nav"><?php echo _t('내비게이션');?></label></dt>
														<dd>
															<select id="propertyiMazing_nav" onchange="editor.setProperty()">
																<option value="net_imazing_show_window_navigation_none"><?php echo _t('기본');?></option>
																<option value="net_imazing_show_window_navigation_simple"><?php echo _t('심플');?></option>
																<option value="net_imazing_show_window_navigation_sidebar"><?php echo _t('사이드바');?></option>
															</select>
														</dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><?php echo _t('슬라이드쇼 간격');?></dt>
														<dd><input type="text" class="input-text" id="propertyiMazing_sshow" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><?php echo _t('화면당 이미지 수');?></dt>
														<dd><input type="text" class="input-text" id="propertyiMazing_page" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_align"><?php echo _t('정렬방법');?></label></dt>
														<dd>
															<select id="propertyiMazing_align" onchange="editor.setProperty()">
																<option value="h"><?php echo _t('가로');?></option>
																<option value="v"><?php echo _t('세로');?></option>
															</select>
														</dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyiMazing_caption"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyiMazing_caption" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('파일');?></div>
													<dl class="file-list-line line">
														<dd>
															<select id="propertyiMazing_list" class="file-list" size="10" onchange="editor.listChanged('propertyiMazing_list')" onclick="editor.listChanged('propertyiMazing_list')"></select>
														</dd>
													</dl>
													<div class="button-box">
														<span class="up-button button" onclick="editor.moveUpFileList('propertyiMazing_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.');?>"><span class="text"><?php echo _t('위로');?></span></span>
														<span class="divider"> | </span>
														<span class="dn-button button" onclick="editor.moveDownFileList('propertyiMazing_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.');?>"><span class="text"><?php echo _t('아래로');?></span></span>
													</div>
													<div id="propertyiMazing_preview" class="preview-box" style="display: none;"></div>
												</div>
											</div>
											
											<div id="propertyGallery" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyGallery-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyGallery-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Gallery');?></h4>
												
												<div class="group">
													<div class="title"><?php echo _t('설정');?></div>
													<dl class="line">
														<dt class="property-name"><label for="propertyGallery_width"><?php echo _t('최대너비');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyGallery_width" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyGallery_height"><?php echo _t('최대높이');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyGallery_height" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyGallery_caption"><?php echo _t('자막');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyGallery_caption" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('파일');?></div>
													<dl class="file-list-line line">
														<dd>
															<select id="propertyGallery_list" class="file-list" size="10" onchange="editor.listChanged('propertyGallery_list')" onclick="editor.listChanged('propertyGallery_list')"></select>
														</dd>
													</dl>
													<div class="button-box">
														<span class="up-button button" onclick="editor.moveUpFileList('propertyGallery_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.');?>"><span class="text"><?php echo _t('위로');?></span></span>
														<span class="divider"> | </span>
														<span class="dn-button button" onclick="editor.moveDownFileList('propertyGallery_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.');?>"><span class="text"><?php echo _t('아래로');?></span></span>
													</div>
													<div id="propertyGallery_preview" class="preview-box" style="display: none;"></div>
												</div>
											</div>
											
											<div id="propertyJukebox" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyJukebox-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyJukebox-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Jukebox');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyJukebox_title"><?php echo _t('제목');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyJukebox_title" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyJukebox_autoplay"><?php echo _t('자동재생');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyJukebox_autoplay" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyJukebox_visibility"><?php echo _t('플레이어 보이기');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyJukebox_visibility" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
												
												<div class="group">
													<div class="title"><?php echo _t('파일');?></div>
													<dl class="line">
														<dd>
															<select id="propertyJukebox_list" class="file-list" size="10" onchange="editor.listChanged('propertyJukebox_list')" onclick="editor.listChanged('propertyJukebox_list')"></select>
														</dd>
													</dl>
													<div class="button-box">
														<span class="up-button button" onclick="editor.moveUpFileList('propertyJukebox_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.');?>"><span class="text"><?php echo _t('위로');?></span></span>
														<span class="divider"> | </span>
														<span class="dn-button button" onclick="editor.moveDownFileList('propertyJukebox_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.');?>"><span class="text"><?php echo _t('아래로');?></span></span>
													</div>
												</div>
											</div>
											
											<div id="propertyEmbed" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyEmbed-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyEmbed-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Embed');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyEmbed_width"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyEmbed_width" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyEmbed_height"><?php echo _t('높이');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyEmbed_height" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyEmbed_src"><acronym class="text" title="Uniform Resource Locator">URL</acronym></label></dt>
														<dd><input type="text" class="input-text" id="propertyEmbed_src" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyFlash" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyFlash-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyFlash-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('Embed');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyFlash_width"><?php echo _t('폭');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyFlash_width" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyFlash_height"><?php echo _t('높이');?></label></dt>
														<dd><input type="text" class="input-text" id="propertyFlash_height" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyFlash_src">URL</label></dt>
														<dd><input type="text" class="input-text" id="propertyFlash_src" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
											
											<div id="propertyMoreLess" class="entry-editor-property" style="display: none;">
												<div class="entry-editor-property-option">
													<input type="checkbox" class="checkbox" id="propertyMoreLess-fix-position" onclick="editor.setPropertyPosition(1)"<?=$fixPosition?' checked="checked"' : ''?>/>
													<label for="propertyMoreLess-fix-position"><?php echo _t('위치 고정');?></label>
												</div>
												<h4><?php echo _t('More/Less');?></h4>
												
												<div class="group">
													<dl class="line">
														<dt class="property-name"><label for="propertyMoreLess_more">More Text</label></dt>
														<dd><input type="text" class="input-text" id="propertyMoreLess_more" onkeyup="editor.setProperty()" /></dd>
													</dl>
													<dl class="line">
														<dt class="property-name"><label for="propertyMoreLess_less">Less Text</label></dt>
														<dd><input type="text" class="input-text" id="propertyMoreLess_less" onkeyup="editor.setProperty()" /></dd>
													</dl>
												</div>
											</div>
<?php
}
function printEntryEditorPalette() {
	global $owner, $service;
?>
										<div id="editor-palette" class="container">
											<dl class="font-relatives">
												<dt class="title">
													<span class="label"><?php echo _t('폰트 설정');?></span>
												</dt>
												<dd class="command-box">
													<select id="fontFamilyChanger" onchange="editor.execCommand('fontname', false, this.value); this.selectedIndex=0;">
														<option class="head-option"><?php echo _t('글자체');?></option>
<?php
	$fontSet = explode('|', _t('fontDisplayName:fontCode:fontFamily'));
	for($i=1; $i<count($fontSet); $i++) {
		$fontInfo = explode(':', $fontSet[$i]);
		if(count($fontInfo) == 3)
			echo "														<option style=\"font-family: '$fontInfo[1]';\" value=\"'$fontInfo[1]', '$fontInfo[2]'\">$fontInfo[0]</option>";
		}
?>
														<option style="font-family: 'Andale Mono';" value="'andale mono',times">Andale Mono</option>
														<option style="font-family: 'Arial';" value="arial,helvetica,sans-serif">Arial</option>
														<option style="font-family: 'Arial Black';" value="'arial black',avant garde">Arial Black</option>
														<option style="font-family: 'Book Antiqua';" value="'book antiqua',palatino">Book Antiqua</option>
														<option style="font-family: 'Comic Sans MS';" value="'comic sans ms',sand">Comic Sans MS</option>
														<option style="font-family: 'Courier New';" value="'courier new',courier,monospace">Courier New</option>
														<option style="font-family: 'Georgia';" value="georgia,'times new roman',times,serif">Georgia</option>
														<option style="font-family: 'Helvetica';" value="helvetica">Helvetica</option>
														<option style="font-family: 'Impact';" value="impact,chicago">Impact</option>
														<option style="font-family: 'Symbol';" value="symbol">Symbol</option>
														<option style="font-family: 'Tahoma';" value="tahoma,arial,helvetica,sans-serif">Tahoma</option>
														<option style="font-family: 'Terminal';" value="terminal,monaco">Terminal</option>
														<option style="font-family: 'Times New Roman';" value="'times new roman',times,serif">Times New Roman</option>
														<option style="font-family: 'Trebuchet MS';" value="'trebuchet ms',geneva">Trebuchet MS</option>
														<option style="font-family: 'Verdana';" value="verdana,arial,helvetica,sans-serif">Verdana</option>
														<option style="font-family: 'Webdings';" value="webdings">Webdings</option>
														<option style="font-family: 'Wingdings';" value="wingdings,'zapf dingbats'">Wingdings</option>
													</select>
													<select id="fontSizeChanger" onchange="editor.execCommand('fontsize', false, this.value); this.selectedIndex=0;">
														<option class="head-option"><?php echo _t('크기');?></option>
														<option value="1">1 (8 pt)</option>
														<option value="2">2 (10 pt)</option>
														<option value="3">3 (12 pt)</option>
														<option value="4">4 (14 pt)</option>
														<option value="5">5 (18 pt)</option>
														<option value="6">6 (24 pt)</option>
														<option value="7">7 (36 pt)</option>
													</select>
												</dd>
											</dl>
											<dl class="font-style">
												<dt class="title">
													<span class="label"><?php echo _t('폰트 스타일');?></span>
												</dt>
												<dd class="command-box">
													<a id="indicatorBold" class="inactive-class button" href="#void" onclick="TTCommand('Bold')" title="<?php echo _t('굵게');?>"><span class="text"><?php echo _t('굵게');?></span></a>
													<a id="indicatorItalic" class="inactive-class button" href="#void" onclick="TTCommand('Italic')" title="<?php echo _t('기울임');?>"><span class="text"><?php echo _t('기울임');?></span></a>
													<a id="indicatorUnderline" class="inactive-class button" href="#void" onclick="TTCommand('Underline')" title="<?php echo _t('밑줄');?>"><span class="text"><?php echo _t('밑줄');?></span></a>
													<a id="indicatorStrike" class="inactive-class button" href="#void" onclick="TTCommand('StrikeThrough')" title="<?php echo _t('취소선');?>"><span class="text"><?php echo _t('취소선');?></span></a>
													<a id="indicatorColorPalette" class="inactive-class button" href="#void" onclick="hideLayer('markPalette'); hideLayer('textBox'); toggleLayer('colorPalette'); changeButtonStatus(this, 'colorPalette');" title="<?php echo _t('글자색');?>"><span class="text"><?php echo _t('글자색');?></span></a>
													<div id="colorPalette" style="display: none;">
														<table cellspacing="0" cellpadding="0">
															<tr>
																<td><a href="#void" onclick="insertColorTag('#008000')"><span class="color-008000">#008000</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#009966')"><span class="color-009966">#009966</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#99CC66')"><span class="color-99CC66">#99CC66</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#999966')"><span class="color-999966">#999966</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#CC9900')"><span class="color-CC9900">#CC9900</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#D41A01')"><span class="color-D41A01">#D41A01</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#FF0000')"><span class="color-FF0000">#FF0000</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#FF7635')"><span class="color-FF7635">#FF7635</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#FF9900')"><span class="color-FF9900">#FF9900</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#FF3399')"><span class="color-FF3399">#FF3399</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#9B18C1')"><span class="color-9B18C1">#9B18C1</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#993366')"><span class="color-993366">#993366</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#666699')"><span class="color-666699">#666699</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#0000FF')"><span class="color-0000FF">#0000FF</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#177FCD')"><span class="color-177FCD">#177FCD</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#006699')"><span class="color-006699">#006699</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#003366')"><span class="color-003366">#003366</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#333333')"><span class="color-333333">#333333</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#000000')"><span class="color-000000">#000000</span></a></td>			
																<td><a href="#void" onclick="insertColorTag('#8E8E8E')"><span class="color-8E8E8E">#8E8E8E</span></a></td>
																<td><a href="#void" onclick="insertColorTag('#C1C1C1')"><span class="color-C1C1C1">#C1C1C1</span></a></td>
															</tr>
														</table>
													</div>
													<a id="indicatorMarkPalette" class="inactive-class button" href="#void" onclick="hideLayer('colorPalette');hideLayer('textBox');toggleLayer('markPalette'); changeButtonStatus(this, 'markPalette');" title="<?php echo _t('배경색');?>"><span class="text"><?php echo _t('배경색');?></span></a>
													<div id="markPalette" style="display: none;">
														<table cellspacing="0" cellpadding="0">
															<tr>
																<td><a href="#void" onclick="insertMarkTag('#008000')"><span class="color-008000">#008000</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#009966')"><span class="color-009966">#009966</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#99CC66')"><span class="color-99CC66">#99CC66</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#999966')"><span class="color-999966">#999966</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#CC9900')"><span class="color-CC9900">#CC9900</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#D41A01')"><span class="color-D41A01">#D41A01</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#FF0000')"><span class="color-FF0000">#FF0000</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#FF7635')"><span class="color-FF7635">#FF7635</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#FF9900')"><span class="color-FF9900">#FF9900</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#FF3399')"><span class="color-FF3399">#FF3399</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#9B18C1')"><span class="color-9B18C1">#9B18C1</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#993366')"><span class="color-993366">#993366</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#666699')"><span class="color-666699">#666699</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#0000FF')"><span class="color-0000FF">#0000FF</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#177FCD')"><span class="color-177FCD">#177FCD</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#006699')"><span class="color-006699">#006699</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#003366')"><span class="color-003366">#003366</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#333333')"><span class="color-333333">#333333</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#000000')"><span class="color-000000">#000000</span></a></td>			
																<td><a href="#void" onclick="insertMarkTag('#8E8E8E')"><span class="color-8E8E8E">#8E8E8E</span></a></td>
																<td><a href="#void" onclick="insertMarkTag('#C1C1C1')"><span class="color-C1C1C1">#C1C1C1</span></a></td>
															</tr>
														</table>
													</div>
													<a id="indicatorTextBox" class="inactive-class button" href="#void" onclick="hideLayer('markPalette');hideLayer('colorPalette');toggleLayer('textBox'); changeButtonStatus(this, 'textBox');" title="<?php echo _t('텍스트 상자');?>"><span class="text"><?php echo _t('텍스트 상자');?></span></a>
													<div id="textBox" style="display: none;">
														<table cellspacing="0" cellpadding="0">
															<tr>
																<td><a href="#void" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FFDAED');"><span class="color-FFDAED">#FFDAED</span></a></td>
																<td><a href="#void" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#C9EDFF');"><span class="color-C9EDFF">#C9EDFF</span></a></td>
																<td><a href="#void" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#D0FF9D');"><span class="color-D0FF9D">#D0FF9D</span></a></td>
																<td><a href="#void" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FAFFA9');"><span class="color-FAFFA9">#FAFFA9</span></a></td>
																<td><a href="#void" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#E4E4E4');"><span class="color-E4E4E4">#E4E4E4</span></a></td>
															</tr>
														</table>
													</div>
													<a id="indicatorRemoveFormat" class="inactive-class button" href="#void" onclick="TTCommand('RemoveFormat')" title="<?php echo _t('효과 제거');?>"><span class="text"><?php echo _t('효과 제거');?></span></a>
												</dd>
											</dl>
											<dl class="paragraph">
												<dt class="title">
													<span class="label"><?php echo _t('문단');?></span>
												</dt>
												<dd class="command-box">
													<a id="indicatorJustifyLeft" class="inactive-class button" href="#void" onclick="TTCommand('JustifyLeft')" title="<?php echo _t('왼쪽 정렬');?>"><span class="text"><?php echo _t('왼쪽 정렬');?></span></a>
													<a id="indicatorJustifyCenter" class="inactive-class button" href="#void" onclick="TTCommand('JustifyCenter')" title="<?php echo _t('가운데 정렬');?>"><span class="text"><?php echo _t('가운데 정렬');?></span></a>
													<a id="indicatorJustifyRight" class="inactive-class button" href="#void" onclick="TTCommand('JustifyRight')" title="<?php echo _t('오른쪽 정렬');?>"><span class="text"><?php echo _t('오른쪽 정렬');?></span></a>
													<a id="indicatorUnorderedList" class="inactive-class button" href="#void" onclick="TTCommand('InsertUnorderedList')" title="<?php echo _t('순서없는 리스트');?>"><span class="text"><?php echo _t('순서없는 리스트');?></span></a>
													<a id="indicatorOrderedList" class="inactive-class button" href="#void" onclick="TTCommand('InsertOrderedList')" title="<?php echo _t('번호 매긴 리스트');?>"><span class="text"><?php echo _t('번호 매긴 리스트');?></span></a>
													<a id="indicatorOutdent" class="inactive-class button" href="#void" onclick="TTCommand('Outdent')" title="<?php echo _t('내어쓰기');?>"><span class="text"><?php echo _t('내어쓰기');?></span></a>
													<a id="indicatorIndent" class="inactive-class button" href="#void" onclick="TTCommand('Indent')" title="<?php echo _t('들여쓰기');?>"><span class="text"><?php echo _t('들여쓰기');?></span></a>
													<a id="indicatorBlockquote" class="inactive-class button" href="#void" onclick="TTCommand('Blockquote')" title="<?php echo _t('인용구');?>"><span class="text"><?php echo _t('인용구');?></span></a>
												</dd>
											</dl>
											<dl class="special">
												<dt class="title">
													<span class="label"><?php echo _t('기타');?></span>
												</dt>
												<dd class="command-box">
													<a id="indicatorCreateLink" class="inactive-class button" href="#void" onclick="TTCommand('CreateLink')" title="<?php echo _t('하이퍼링크');?>"><span class="text"><?php echo _t('하이퍼링크');?></span></a>
													<a id="indicatorMediaBlock" class="inactive-class button" href="#void" onclick="TTCommand('ObjectBlock')" title="<?php echo _t('미디어 삽입');?>"><span class="text"><?php echo _t('미디어 삽입');?></span></a>
													<a id="indicatorMoreLessBlock" class="inactive-class button" href="#void" onclick="TTCommand('MoreLessBlock')" title="<?php echo _t('More/Less');?>"><span class="text"><?php echo _t('More/Less');?></span></a>
												</dd>
											</dl>
											<dl class="mode">
												<dt class="title">
													<span class="label"><?php echo _t('편집 환경');?></span>
												</dt>
												<dd class="command-box">
													<a id="indicatorMode" class="inactive-class button" href="#void" onclick="TTCommand('ToggleMode'); changeEditorMode();" title="<?php echo _t('클릭하시면 HTML 편집기로 변경합니다.');?>"><span class="text"><?php echo _t('WYSIWYG 편집기');?></span></a>
												</dd>
											</dl>
										</div>
<?php
}

function getAttachmentValue($attachment) {
	global $g_attachmentFolderPath;
	if (strpos($attachment['mime'], 'image') === 0) {
		if (getUserSetting("waterMarkDefault") == "yes")
			$classString = 'class="tt-watermark" ';
		else if (getUserSetting("resamplingDefault") == "yes")
			$classString = 'class="tt-resampling" ';
		else
			$classString = "";
		
		return "{$attachment['name']}|{$classString}width=\"{$attachment['width']}\" height=\"{$attachment['height']}\" alt=\"" . _text('사용자 삽입 이미지') . "\"";		
	} else {
		return "{$attachment['name']}|";
	}
}

function getPrettyAttachmentLabel($attachment) {
	if (strpos($attachment['mime'], 'image') === 0)
		return "{$attachment['label']} ({$attachment['width']}x{$attachment['height']} / ".getSizeHumanReadable($attachment['size']).')';
	else if(strpos($attachment['mime'], 'audio') !== 0 && strpos($attachment['mime'], 'video') !== 0) {
		if ($attachment['downloads']>0)
			return "{$attachment['label']} (".getSizeHumanReadable($attachment['size']).' / '._t('다운로드').':'.$attachment['downloads'].')';		
	}
	return "{$attachment['label']} (".getSizeHumanReadable($attachment['size']).')';
}
?>
