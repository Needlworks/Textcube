<?

function printOwnerEditorScript($entryId = false) {
	global $owner, $database, $skin, $blogURL, $service;
	$contentWidth = 500;
	if ($skin = fetchQueryCell("SELECT skin FROM {$database['prefix']}SkinSettings WHERE owner = $owner")) {
		if ($xml = @file_get_contents(ROOT . "/skin/$skin/index.xml")) {
			$xmls = new XMLStruct();
			$xmls->open($xml, $service['encoding']);
			if ($xmls->getValue('/skin/default/contentWidth')) {
				$contentWidth = $xmls->getValue('/skin/default/contentWidth');
			}
		}
	}
?>
<script type="text/javascript">
//<![CDATA[
	var skinContentWidth = <?=$contentWidth?>;
	var s_notSupportHTMLBlock = "<?=_t('위지윅 모드에서는 [HTML][/HTML] 블럭을 사용할 수 없습니다.')?>";
	var s_enterURL = "<?=_t('URL을 입력하세요')?>";

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
	function insertMarkTag(col1, col2) {
		hideLayer("markPalette");
		TTCommand("Mark", col1, col2);
	}

	function addAttachment() {
		if(isIE) {
			document.frames[0].document.forms[0].action = blogURL + "/owner/entry/attach<?=($entryId ? "/$entryId" : '')?>";
			document.frames[0].document.forms[0].attachment.click();
		} else {
			var attachHidden = document.getElementById('attachHiddenNest');
			attachHidden.contentDocument.forms[0].action = blogURL + "/owner/entry/attach<?=($entryId ? "/$entryId" : '')?>";
			attachHidden.contentDocument.forms[0].attachment.click();
		}
	}
	
	function deleteAttachment() {
		var fileList = document.getElementById('fileList');		
		if (fileList.selectedIndex < 0) {
			alert("<?=_t('삭제할 파일을 선택해 주십시오\t')?>");
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
			alert("<?=_t('파일을 삭제하지 못했습니다')?> ::"+e.message);
		}

		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/detach/multi<?=($entryId ? "/$entryId" : '/0')?>");
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
		}
		request.onError = function() {
			alert("<?=_t('파일을 삭제하지 못했습니다')?>");
		}
		request.send("names="+targetStr);
	}
	
	function downloadAttachment() {
		var fileList = document.getElementById('fileList');
		if (fileList.selectedIndex < 0) {
			return false;
		}
		for(var i=0; fileList.length; i++) {
			if (fileList[i].selected) {
				var fileName = fileList[i].value.split("|")[0];
				if(STD.isIE) {
					document.getElementById('fileDownload').innerHTML='<iframe style="display:none;" src="'+blogURL+'/attachment/'+fileName+'"></iframe>';
				} else {
					window.location = blogURL+'/attachment/'+fileName;
				}
				break;
			}
		}
	}
	
	function selectAttachment() {
		try {
		width = document.getElementById('previewSelected').clientWidth;
		height = document.getElementById('previewSelected').clientHeight;
		var code = '';
		if (document.forms[0].fileList.selectedIndex < 0)
			return false;
		var fileName = document.forms[0].fileList.value.split("|")[0];
		
		if((new RegExp("\\.(gif|jpe?g|png)$", "gi").exec(fileName))) {
			document.getElementById('previewSelected').innerHTML = '<img style="width: '+width+'px; height: 94px" src="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" alt="" onerror="this.src=\'<?=$service['path']?>/image/spacer.gif\'"/>';
			//setAttribute('src',"<?=$service['path']?>/attach/<?=$owner?>/"+  fileName);
			//document.getElementById('selectedImage').setAttribute('src',"<?=$service['path']?>/image/spacer.gif");
			return false;
		}
		
		if((new RegExp("\\.(mp3)$", "gi").exec(fileName))) {
			var str = getEmbedCode("<?=$service['path']?>/script/jukebox/flash/mini.swf?__TT__="+(Math.random()*1000),"100%","100%", "jukeBox0Flash","#FFFFFF", "sounds=<?=$service['path']?>/attach/<?=$owner?>/"+fileName, "false"); 
			writeCode(str, 'previewSelected');
			return false;
		}
		
		if((new RegExp("\\.(swf)$", "gi").exec(fileName))) {			
			
			code = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%"><param name="movie" value="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'"/><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF"/>';
			code += '<!--[if !IE]> <--><object type="application/x-shockwave-flash" data="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" width="100%" height="100%"><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF"/></object><!--> <![endif]--></object>';
			
			writeCode(code,'previewSelected');
			return false;
		}
		
		if((new RegExp("\\.(mov)$", "gi").exec(fileName))) {			
			code = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="'+width+'" height="'+height+'"><param name="src" value="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'"/><param name="controller" value="true"><param name="scale" value="Aspect">';
			code += '<!--[if !IE]> <--><object type="video/quicktime" data="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" width="'+width+'" height="'+height+'" showcontrols="true" TYPE="video/quicktime" scale="Aspect" nomenu="true"><param name="showcontrols" value="true"><param name="scale" value="ToFit"></object><!--> <![endif]--></object>';
			
			writeCode(code,'previewSelected');
			
			return false;
		}
		
	
		if((new RegExp("\\.(mp2|wma|mid|midi|mpg|wav)$", "gi").exec(fileName))) {
			code ='<object width="'+width+'" height="'+height+'" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" standby="Loading for you" type="application/x-oleobject" align="middle">';		
			code +='<param name="FileName" value="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'">';
			code +='<param name="ShowStatusBar" value="False">';
			code +='<param name="DefaultFrame" value="mainFrame">';
			code +='<param name="showControls" value="false">';
			code +='<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" src="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" align="middle" width="'+width+'" height="'+height+'" showControls="false" defaultframe="mainFrame" showstatusbar="false"></embed>';
			code +='</object>';
			
			writeCode(code,'previewSelected');
			
			return false;
		}
		
		
		
			
		code +='<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" src="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" align="middle" width="'+width+'" height="'+height+'" showControls="false" defaultframe="mainFrame" showstatusbar="false"></embed>';
		
		writeCode(code,'previewSelected');
			
		
		if((new RegExp("\\.(rm|ram)$", "gi").exec(fileName))) {		
		/*
			code = '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+width+'" height="'+height+'"><param name="src" value="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'"/><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE">';
			code += '<!--[if !IE]> <--><object type="audio/x-pn-realaudio-plugin" data="<?=$service['path']?>/attach/<?=$owner?>/'+fileName+'" width="'+width+'" height="'+height+'" ><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE"></object><!--> <![endif]--></object>';			
		*/
		}
		
		if (code == undefined || code == '') {
			document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?=_t('미리보기')?></td></tr></table>";
			return true;
		}
		
				
		
		return false;
		} catch (e) {
			document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?=_t('미리보기')?></td></tr></table>";	
			return true;
		}
	}
	
	function linkImage1(align) {
		var oSelect = document.forms[0].fileList;
		if (oSelect.selectedIndex < 0) {
			alert("<?=_t('파일을 선택하십시오\t')?>");
			return false;
		}
		var value = oSelect.options[oSelect.selectedIndex].value.split("|");
		var result_w = new RegExp("width=['\"]?(\\d+)").exec(value[1]);
		var result_h = new RegExp("height=['\"]?(\\d+)").exec(value[1]);
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
		try {
			if(editor.editMode == "WYSIWYG")
			{
				var src = editor.propertyFilePath + value[0];
				var attributes = editor.addQuot(value[1]);

				if(!(new RegExp("\.(jpe?g|gif|png|bmp)$", "i").test(value[0])))
				{
					src = servicePath + "/image/spacer.gif";
					value[1] = editor.styleUnknown;
					attributes = "";				
				}

				switch(align)
				{
					case "1L":
						var prefix = "<img class=\"tatterImageLeft\" src=\"" + src + "\" " + value[1] + " longdesc=\"1L|" + value[0] + "|" + attributes + "|\"/>";
						break;
					case "1C":
						var prefix = "<img class=\"tatterImageCenter\" src=\"" + src + "\" " + value[1] + " longdesc=\"1C|" + value[0] + "|" + attributes + "|\"/>";
						break;
					case "1R":
						var prefix = "<img class=\"tatterImageRight\" src=\"" + src + "\" " + value[1] + " longdesc=\"1R|" + value[0] + "|" + attributes + "|\"/>";
						break;
				}
				TTCommand("Raw", prefix);
				return true;
			}
		} catch(e) { }

		insertTag('[##_' + align + '|' + value[0] + '|' + value[1] + '|_##]', "");
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
			alert("<?=_t('파일 리스트에서 이미지를 2개 선택해 주세요 (ctrl + 마우스 왼쪽 클릭)')?>");
			return false;
		}
		var imageinfo = prefix.split("^");
		try {
			if(editor.editMode == "WYSIWYG") {			
				var prefix = '<img class="tatterImageDual" src="' + servicePath + '/image/spacer.gif" width="200" height="100" longdesc="2C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '"/>';
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
			alert("<?=_t('파일 리스트에서 이미지를 3개 선택해 주세요 (ctrl + 마우스 왼쪽 클릭)')?>");
			return false;
		}
		var imageinfo = prefix.split("^");
		try {
			if(editor.editMode == "WYSIWYG") {
				var prefix = '<img class="tatterImageTriple" src="' + servicePath + '/image/spacer.gif" width="300" height="100" longdesc="3C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '|' + editor.addQuot(imageinfo[3]) + '"/>';
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
						prefix += '<img class="tatterImageFree" src="' + editor.propertyFilePath + value[0] + '" longdesc="[##_ATTACH_PATH_##]/' + value[0] + '" ' + value[1] + '/>';
					else
						prefix += '<img src="[##_ATTACH_PATH_##]/' + value[0] + '" ' + value[1] + '/>';
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
	iMazingProperties['skinPath'] = '<?=$service['path']?>/script/gallery/iMazing/';
	
	function viewImazing()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?=_t('파일을 선택하십시오\t')?>");
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
				alert("<?=_t('이미지 파일만 삽입 가능 합니다')?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);
			var Properties = '';
			for (var name in iMazingProperties) {
				Properties += name+'='+iMazingProperties[name]+' ';
			}

			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterImazing" src="' + servicePath + '/image/spacer.gif" width="400" height="300" longdesc="iMazing|' + fileList + '|' + Properties + '|"/>');
					return true;
				}
			} catch(e) { }
	
			insertTag('[##_iMazing|' + fileList + '|' + Properties +'|_##]', '');
			return true;
		}
		catch(e)
		{
			return false;
		}
	}
	
	function viewGallery()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?=_t('파일을 선택하십시오\t')?>");
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
				alert("<?=_t('이미지 파일만 삽입 가능 합니다')?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);

			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterGallery" src="' + servicePath + '/image/spacer.gif" width="400" height="300" longdesc="Gallery|' + fileList + '|width=&quot;400&quot; height=&quot;300&quot;"/>');
					return true;
				}
			} catch(e) { }

			insertTag('[##_Gallery|' + fileList + '|width="400" height="300"_##]', '');
			return true;
		}
		catch(e)
		{
			return false;
		}
	}

	function viewJukebox()
	{
		try
		{
			var oSelect = document.forms[0].fileList;
			if (oSelect.selectedIndex < 0) {
				alert("<?=_t('파일을 선택하십시오\t')?>");
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
				alert("<?=_t('MP3 파일만 삽입 가능 합니다')?>");
				return false;
			}
			fileList = fileList.substr(0,fileList.length-1);

			try {
				if(editor.editMode == "WYSIWYG") {
					TTCommand("Raw", '<img class="tatterJukebox" src="' + servicePath + '/image/spacer.gif" width="200" height="30" longdesc="Jukebox|' + fileList + '|autoplay=0 visible=1|"/>');
					return true;
				}
			} catch(e) { }

			insertTag('[##_Jukebox|' + fileList + '|autoplay=0 visible=1|_##]', '');
			return true;
		}
		catch(e) 
		{
			return false;
		}
	}	
//]]>
</script>
<?
}

function printEntryFileList($attachments, $entryId) {
	global $owner, $service, $blogURL;
	if(empty($attachments) || (
	strpos($attachments[0]['name'] ,'.gif') === false &&
	strpos($attachments[0]['name'] ,'.jpg') === false &&
	strpos($attachments[0]['name'] ,'.png') === false)) {
		$fileName = "{$service['path']}/image/spacer.gif";
	} else {
		$fileName = "{$service['path']}/attach/$owner/{$attachments[0]['name']}";
	}
?>				  
	<td width="130" valign="top" align="center">
		<div id="previewSelected" style="width:120px; height:90px; background:#FFFFFF"><table width="100%" height="100%"><tr><td valign="middle" align="center"><?=_t('미리보기')?></td></tr></table></div>
	</td>
	<td valign="top" align="center" width="415">	
		<table>
			<tr>
				<td>				
			<span id="attachManagerSelect">
		    <select size="8" name="fileList" id="fileList" multiple="multiple" style="width:415px;" onchange="selectAttachment();" ondblclick="downloadAttachment()">
                <?
	$initialFileListForFlash = '';
	$enclosureFileName = '';
	foreach ($attachments as $i => $attachment) {
		if (strpos($attachment['mime'], 'application') !== false) {
			$class = 'class="MimeApplication"';
		} else if (strpos($attachment['mime'], 'audio') !== false) {
			$class = 'class="MimeAudio"';
		} else if (strpos($attachment['mime'], 'image') !== false) {
			$class = 'class="MimeImage"';
		} else if (strpos($attachment['mime'], 'message') !== false) {
			$class = 'class="MimeMessage"';
		} else if (strpos($attachment['mime'], 'model') !== false) {
			$class = 'class="MimeModel"';
		} else if (strpos($attachment['mime'], 'multipart') !== false) {
			$class = 'class="MimeMultipart"';
		} else if (strpos($attachment['mime'], 'text') !== false) {
			$class = 'class="MimeText"';
		} else if (strpos($attachment['mime'], 'video') !== false) {
			$class = 'class="MimeVideo"';
		} else {
			$class = '';
		}
		if ($attachment['enclosure'] == 1) {
			$style = 'style="background-color:#c6a6e7; color:#000000"';
			$enclosureFileName = $attachment['name'];
		} else {
			$style = '';
			$prefix = '';
		}
		$value = htmlspecialchars(getAttachmentValue($attachment));
		$label = $prefix . htmlspecialchars(getPrettyAttachmentLabel($attachment));
		$initialFileListForFlash .= escapeJSInAttribute($value . '(_!' . $label . '!^|');
?>
		        <option  <?=$style?> value="<?=$value?>">
	            <?=$label?>
	            </option>
                <?
	}
?>
	        </select>
			</span>
				</td>
			</tr>
			<tr>
				<td>			
		    <script type="text/javascript">
				function stripLabelToValue(fileLabel) {
					var pos = fileLabel.lastIndexOf('(');
					return fileLabel.substring(0,pos-1);	
				}
			
				function refreshAttachFormSize () {
					fileListObj = document.getElementById('fileList');
					fileListObj.setAttribute('size',Math.max(8,Math.min(fileListObj.length,30)));
				}
				
				function refreshAttachList() {
					var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/attachmulti/refresh<?=($entryId ? "/$entryId" : '/0')?>");
					request.onVerify = function () { return true }
					request.onSuccess = function() {
						var fileListObj = document.getElementById("attachManagerSelect");
						fileListObj.innerHTML = this.getText();
						refreshAttachFormSize();
						getUploadObj().setAttribute('width',1)
						getUploadObj().setAttribute('height',1)
						document.getElementById('uploadBtn').disabled=false;						
					}
					request.onError = function() {
					}
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

				
				function setFileList() {
					var uploaderObj = document.getElementById("uploader");
					try {
						list = getUploadObj().GetVariable("/:listStr");						
					} catch(e) {
						
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
						oOption.innerHTML= fileName+' ('+Math.ceil((fileSize/1024))+'KB)  <?=_t('대기 중..')?>';
						oOption.setAttribute("value",fileName);
						oOption.style.backgroundColor="#A4C3F0";

						fileListObj.insertBefore(oOption,fileListObj[i]);
					}
					fileListObj.setAttribute('size',Math.max(8,Math.min(fileListObj.length,30)));
					getUploadObj().setAttribute('width',414)
					getUploadObj().setAttribute('height',25)
					document.getElementById('uploadBtn').disabled=true;					
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
					if(document.getElementById('fileList').length>0)
						document.getElementById('deleteBtn').disabled = false;
					else
						document.getElementById('deleteBtn').disabled = true;
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
					getUploadObj().SetVariable('/:openBroswer','true');
				}
				
				refreshAttachFormSize();
			</script>
			<script language="JavaScript" type="text/javascript">
			var requiredMajorVersion = 8;
			var requiredMinorVersion = 0;
			var requiredRevision = 0;
			var jsVersion = 1.0;
			</script>			
<?
	require_once ROOT . '/script/detectFlash.inc';
	$maxSize = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
?>	
		<script language="JavaScript" type="text/javascript">
		<!-- 
		var hasRightVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
		if(hasRightVersion && isWin) {  
		//if(<?=!empty($service['flashuploader']) ? $service['flashuploader'] : 'false'?> ) {  
			var oeTags = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="uploader"'
			+ 'width="0" height="0"'
			+ 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab">'
			+ '<param name="movie" value="<?=$service['path']?>/script/uploader/uploader.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><param name="scale" value="noScale" /><param name="wmode" value="transparent" /><param name="FlashVars" value="path=<?=$blogURL?>&owner=<?=$owner?>&entryid=<?=$entryId?>&enclosure=<?=$enclosureFileName?>&maxSize=<?=$maxSize?>&sessionName=TSSESSION&sessionValue=<?=$_COOKIE['TSSESSION']?>" />'
			+ '<embed id="uploader2" src="<?=$service['path']?>/script/uploader/uploader.swf" flashvars="path=<?=$blogURL?>&owner=<?=$owner?>&entryid=<?=$entryId?>&enclosure=<?=$enclosureFileName?>&maxSize=<?=$maxSize?>&sessionName=TSSESSION&sessionValue=<?=$_COOKIE['TSSESSION']?>" width="1" height="1" align="middle" wmode="transparent" quality="high" bgcolor="#ffffff" scale="noScale" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /><\/embed><\/object>';
			writeCode(oeTags);   // embed the flash movie 
			//document.getElementById('uploadProgressBar').style.display="none";

		  } else {  // flash is too old or we can't detect the plugin
			var alternateContent = '';
			document.write(alternateContent);  // insert non-flash content			
		  }
		// -->
		</script>
	        </td>
			</tr>
		</table>
	</td>			
	<?
}

function printEntryFileUploadButton($entryId) {
	global $owner, $service;
	?>
	<table  cellsapcing="0" cellpadding="0">
		<tr>
			<td>
				<table cellsapcing="0" cellpadding="0">
					<tr>
						<td>
							<table>
								<tr id="fileUploadNest" >
									<script>
										if(getUploadObj()) {		
											try{
												document.write('<td><input id="uploadBtn" type="button" class="button" value="<?=_t('파일 업로드')?>" onclick="browser();" style="margin-top: 1px"/></td>');			
											} catch(e) {
												
											}								
										}
									</script>
								</tr>
							</table>
						</td>
	<script type="text/javascript">	
		attachId = 0;
		function makeCrossDamainSubmit(uri,userAgent) {
		
			var property =new Array();
			property['ie'] = new Array();
			property['ie']['width'] = '225px';
			property['ie']['height'] = '25px';		
			
			property['moz'] = new Array();
			property['moz']['width'] = '215px';
			property['moz']['height'] = '25px';		
			
			property['etc'] = new Array();
			property['etc']['width'] = '240px';
			property['etc']['height'] = '22px';
			
			var str='<iframe src="'+uri+'" id="attachHiddenNest_'+(attachId)+'"  style="display:block; height:'+property[userAgent]['height']+'; width:'+property[userAgent]['width']+'" frameborder="no" scrolling="no"></iframe>'
			var td = document.createElement('td');
			td.innerHTML= str;									

			document.getElementById('fileUploadNest').appendChild(td);
			var td = document.getElementById('td');

			if(attachId) { 
				document.getElementById('attachHiddenNest_'+(attachId-1)+'').style.width = 0;
				document.getElementById('attachHiddenNest_'+(attachId-1)+'').style.height = 0;
			}
			
			attachId++;
			
		}
		
		//if(<?=!empty($service['flashuploader']) ? $service['flashuploader'] : 'false'?> ) { 
		 if(!getUploadObj()) {
			if(isIE) {
				makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?=$entryId?>","ie");
			} else if(isMoz) {
				makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?=$entryId?>","moz");
			} else {
				makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?=$entryId?>","etc");
			}
		}
	  </script>						
					</tr>
				</table>	
			</td>			
			<td><span id="fileDownload" style="display: none"></span></td>
			<td>
		  		<input type="button" class="button" id="deleteBtn" value="<?=_t('삭제하기')?>" onclick="deleteAttachment();" style="margin-top: 1px" />
			</td>
	  </tr>
</table>
	<?
}

function printEntryEditorProperty() {
	global $service;
?>
<table id="propertyImage1" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage1_width1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage1_alt1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage1_caption1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyImage2" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image 1</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_width1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_alt1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_caption1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image 2</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_width2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_alt2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage2_caption2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyImage3" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image 1</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_width1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_alt1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_caption1" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image 2</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_width2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_alt2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_caption2" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Image 3</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="50%" />
		<col width="50%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_width3" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('대체 텍스트')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_alt3" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyImage3_caption3" style="width: 88px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyObject1" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 1</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject1_caption1" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject1_filename1"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyObject2" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 1</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject2_caption1" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject2_filename1"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 2</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject2_caption2" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject2_filename2"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyObject3" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 1</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject3_caption1" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject3_filename1"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 2</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject3_caption2" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject3_filename2"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Object 3</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyObject3_caption3" style="width: 126px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('파일명')?></td>
		  <td align="left" style="padding-right:1px;" id="propertyObject3_filename3"></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyiMazing" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">iMazing</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="55%" />
		<col width="45%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyiMazing_width" style="width: 78px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('높이')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyiMazing_height" style="width: 78px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr> 
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('테두리')?></td>
		  <td align="right">
		  <select id="propertyiMazing_frame" style="width: 100%; border: 1px solid #92B5E8;" onchange="editor.setProperty()">
			<option value="net_imazing_frame_none"><?=_t('테두리 없음')?></option>
		  </select></td>
		</tr>
		
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('장면전환효과')?></td>
		  <td align="right">
		  <select id="propertyiMazing_tran" style="width: 100%; border: 1px solid #92B5E8;" onchange="editor.setProperty()">
			<option value="net_imazing_show_window_transition_none"><?=_t('효과없음')?></option>
			<option value="net_imazing_show_window_transition_alpha"><?=_t('투명전환')?></option>
			<option value="net_imazing_show_window_transition_contrast"><?=_t('플래쉬')?></option>
			<option value="net_imazing_show_window_transition_sliding"><?=_t('슬라이딩')?></option>
		  </select></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('내비게이션')?></td>
		  <td align="right">
		  <select id="propertyiMazing_nav" style="width: 100%; border: 1px solid #92B5E8;" onchange="editor.setProperty()">
			<option value="net_imazing_show_window_navigation_none"><?=_t('기본')?></option>
			<option value="net_imazing_show_window_navigation_simple"><?=_t('심플')?></option>
			<option value="net_imazing_show_window_navigation_sidebar"><?=_t('사이드바')?></option>
		  </select></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('슬라이드쇼 간격')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyiMazing_sshow" style="width: 78px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('화면당 이미지 수')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyiMazing_page" style="width: 78px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('정렬방법')?></td>
		  <td align="right">
		  <select id="propertyiMazing_align" style="width: 100%; border: 1px solid #92B5E8;" onchange="editor.setProperty()">
			<option value="h"><?=_t('가로')?></option>
			<option value="v"><?=_t('세로')?></option>
		  </select></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyiMazing_caption" style="width: 78px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	  <span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">File</span>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:10px; margin-bottom:10px;">
		<tr>
		  <td><table width="198" height="150" border="0" cellpadding="0" cellspacing="1">
			<tr>
			  <td bgcolor="#ffffff">
			  	<select id="propertyiMazing_list" size="10" style="width: 198px" onchange="editor.listChanged('propertyiMazing_list')" onclick="editor.listChanged('propertyiMazing_list')">
				</select>
			  </td>
			</tr>
		  </table>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" align="right"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveUp.gif" width="40" height="14" alt="<?=_t('위로')?>" onclick="editor.moveUpFileList('propertyiMazing_list')"/> <img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveDown.gif" width="50" height="14" alt="<?=_t('아래로')?>" onclick="editor.moveDownFileList('propertyiMazing_list')"/></td>
			  </tr>
			</table></td>
		</tr>
	  </table>
	  <div id="propertyiMazing_preview" style="width: 198px; text-align: center; overflow: hidden; margin-bottom: 10px; display: none"></div>
    </td>
  </tr>
</table>

<table id="propertyGallery" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Gallery</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('최대너비')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyGallery_width" style="width: 128px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('최대높이')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyGallery_height" style="width: 128px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
						<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('자막')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyGallery_caption" style="width: 128px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	  <span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">File</span>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:10px; margin-bottom:10px;">
		<tr>
		  <td><table width="198" height="150" border="0" cellpadding="0" cellspacing="1">
			<tr>
			  <td bgcolor="#ffffff">
			  	<select id="propertyGallery_list" size="10" style="width: 198px" onchange="editor.listChanged('propertyGallery_list')" onclick="editor.listChanged('propertyGallery_list')">
				</select>
			  </td>
			</tr>
		  </table>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" align="right"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveUp.gif" width="40" height="14" alt="<?=_t('위로')?>" onclick="editor.moveUpFileList('propertyGallery_list')"/> <img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveDown.gif" width="50" height="14" alt="<?=_t('아래로')?>" onclick="editor.moveDownFileList('propertyGallery_list')"/></td>
			  </tr>
			</table></td>
		</tr>
	  </table>
	  <div id="propertyGallery_preview" style="width: 198px; text-align: center; overflow: hidden; margin-bottom: 10px; display: none"></div>
    </td>	
  </tr>
</table>

<table id="propertyJukebox" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Jukebox</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="30%" />
		<col width="70%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('재생옵션')?></td>
		  <td align="left" style="padding-right:1px;"><input type="checkbox" id="propertyJukebox_autoplay" onclick="editor.setProperty()"/> <label for="propertyJukebox_autoplay"><?=_t('자동재생')?></label></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('화면표시')?></td>
		  <td align="left" style="padding-right:1px;"><input type="checkbox" id="propertyJukebox_visibility" onclick="editor.setProperty()"/> <label for="propertyJukebox_visibility"><?=_t('플레이어보이기')?></label></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('제목')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyJukebox_title" style="width: 128px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
	  <span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">File</span>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:10px; margin-bottom:10px;">
		<tr>
		  <td><table width="198" height="150" border="0" cellpadding="0" cellspacing="1">
			<tr>
			  <td bgcolor="#ffffff">
			  	<select id="propertyJukebox_list" size="10" style="width: 198px" onchange="editor.listChanged('propertyJukebox_list')" onclick="editor.listChanged('propertyJukebox_list')">
				</select>
			  </td>
			</tr>
		  </table>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" align="right"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveUp.gif" width="40" height="14" alt="<?=_t('위로')?>" onclick="editor.moveUpFileList('propertyJukebox_list')"/> <img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/attach_MoveDown.gif" width="50" height="14" alt="<?=_t('아래로')?>" onclick="editor.moveDownFileList('propertyJukebox_list')"/></td>
			  </tr>
			</table></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyEmbed" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Embed</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="35%" />
		<col width="65%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyEmbed_width" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('높이')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyEmbed_height" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr> 
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;">URL</td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyEmbed_src" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyFlash" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">Flash</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="35%" />
		<col width="65%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('폭')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyFlash_width" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;"><?=_t('높이')?></td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyFlash_height" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr> 
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;">URL</td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyFlash_src" style="width: 117px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>

<table id="propertyMoreLess" width="220" border="0" cellpadding="0" cellspacing="1" style="display: none; border: 1px solid #999">
  <tr>
	<td width="220" style="padding:10px 10px 0px 10px; background-color: #fff">
	<span style="font-family:tahoma; font-size:10px; color:#000; font-weight:bold;">More/Less</span>	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="margin-top:10px; margin-bottom:10px; table-layout: fixed">
	  	<col width="35%" />
		<col width="65%" />
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;">More Text</td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyMoreLess_more" style="width: 118px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
		<tr>
		  <td style="font-size:11px; letter-spacing:-1; color:#666;">Less Text</td>
		  <td align="right" style="padding-right:1px;"><input type="text" class="text2" id="propertyMoreLess_less" style="width: 118px" onkeyup="editor.setProperty()"/></td>
		</tr>
		<tr>
		  <td height="1" background="<?=$service['path']?>/image/owner/edit/dotted_layer.gif"></td>
		  <td></td>
		</tr>
	  </table>
    </td>
  </tr>
</table>
<?
}

function printEntryEditorPalette() {
	global $owner, $service;
	$p_box1_style = 'padding:10; background-color:#F0F0F0;';
	$p_box2_style = 'padding:10; background-color:#DEEFFF;';
	$p_box3_style = 'padding:10; background-color:#D6F7E0;';
	$p_box4_style = 'padding:10; background-color:#FFE6E6;';
?>
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td>
		<select id="fontFamilyChanger" tabindex="100" onchange="editor.execCommand('fontname', false, this.value);this.selectedIndex=0" style="width: 100px">
		<option><?=_t('글자체')?></option>
		<option value="">=========</option>
		<option value="andale mono,times">Andale Mono</option>
		<option value="arial,helvetica,sans-serif">Arial</option>
		<option value="arial black,avant garde">Arial Black</option>
		<option value="book antiqua,palatino">Book Antiqua</option>
		<option value="comic sans ms,sand">Comic Sans MS</option>
		<option value="courier new,courier,monospace">Courier New</option>
		<option value="georgia,times new roman,times,serif">Georgia</option>
		<option value="helvetica">Helvetica</option>
		<option value="impact,chicago">Impact</option>
		<option value="symbol">Symbol</option>
		<option value="tahoma,arial,helvetica,sans-serif">Tahoma</option>
		<option value="terminal,monaco">Terminal</option>
		<option value="times new roman,times,serif">Times New Roman</option>
		<option value="trebuchet ms,geneva">Trebuchet MS</option>
		<option value="verdana,arial,helvetica,sans-serif">Verdana</option>
		<option value="webdings">Webdings</option>
		<option value="wingdings,zapf dingbats">Wingdings</option>
		</select>
		<select id="fontSizeChanger" tabindex="101" onchange="editor.execCommand('fontsize', false, this.value);this.selectedIndex=0" style="width: 50px">
		<option><?=_t('크기')?></option>
		<option value="">=======</option>
		<option value="1">1 (8 pt)</option>
		<option value="2">2 (10 pt)</option>
		<option value="3">3 (12 pt)</option>
		<option value="4">4 (14 pt)</option>
		<option value="5">5 (18 pt)</option>
		<option value="6">6 (24 pt)</option>
		<option value="7">7 (36 pt)</option>
		</select>
	</td>
	<td><img src="<?=$service['path']?>/image/owner/edit/dotted_vertical.gif" width="5" height="24" /></td>							
	<td><table border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setBold.gif" id="indicatorBold" width="20" height="20" border="0" onclick="TTCommand('Bold')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setBold_over.gif'" onmouseout="if(!editor.isBold) this.src='<?=$service['path']?>/image/owner/edit/setBold.gif'" alt="<?=_t('굵게')?>" title="<?=_t('굵게')?>"/></td>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setItalic.gif" width="20" height="20" border="0" id="indicatorItalic" onclick="TTCommand('Italic')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setItalic_over.gif'" onmouseout="if(!editor.isItalic) this.src='<?=$service['path']?>/image/owner/edit/setItalic.gif'" alt="<?=_t('기울임')?>" title="<?=_t('기울임')?>"/></td>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setUnderLine.gif" width="20" height="20" border="0" id="indicatorUnderline" onclick="TTCommand('Underline')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setUnderLine_over.gif'" onmouseout="if(!editor.isUnderline) this.src='<?=$service['path']?>/image/owner/edit/setUnderLine.gif'" alt="<?=_t('밑줄')?>" title="<?=_t('밑줄')?>"/></td>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setLineThrough.gif" width="20" height="20" border="0" id="indicatorStrike" onclick="TTCommand('StrikeThrough')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setLineThrough_over.gif'" onmouseout="if(!editor.isStrike) this.src='<?=$service['path']?>/image/owner/edit/setLineThrough.gif'" alt="<?=_t('취소선')?>" title="<?=_t('취소선')?>"/></td>
		<td><a href="#" tabindex="106" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image6','','<?=$service['path']?>/image/owner/edit/setColor_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setColor.gif" name="Image6" width="20" height="20" border="0" id="Image6" onclick="hideLayer('markPalette');hideLayer('textBox');toggleLayer('colorPalette')" alt="<?=_t('글자색')?>" title="<?=_t('글자색')?>"/></a></td>
		<td><a href="#" tabindex="107" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image7','','<?=$service['path']?>/image/owner/edit/setMark_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMark.gif" name="Image7" width="20" height="20" border="0" id="Image7" onclick="hideLayer('colorPalette');hideLayer('textBox');toggleLayer('markPalette')" alt="<?=_t('배경색')?>" title="<?=_t('배경색')?>"/></a></td>
		<td><a href="#" tabindex="108" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image8','','<?=$service['path']?>/image/owner/edit/textBox_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/textBox.gif" name="Image8" width="20" height="20" border="0" id="Image8" onclick="hideLayer('markPalette');hideLayer('colorPalette');toggleLayer('textBox')" alt="<?=_t('텍스트 상자')?>" title="<?=_t('텍스트 상자')?>"/></a></td>
		<td><a href="#" tabindex="109" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image9','','<?=$service['path']?>/image/owner/edit/set_remove_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_remove.gif" name="Image9" width="20" height="20" border="0" id="Image9" onclick="TTCommand('RemoveFormat')" alt="<?=_t('효과 제거')?>" title="<?=_t('효과 제거')?>"/></a></td>
	  </tr>
	</table>
	</td>
	<td><img src="<?=$service['path']?>/image/owner/edit/dotted_vertical.gif" width="5" height="24" /></td>
	<td><table border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setAlignLeft.gif" width="20" height="20" onclick="TTCommand('JustifyLeft')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setAlignLeft_over.gif'" onmouseout="this.src='<?=$service['path']?>/image/owner/edit/setAlignLeft.gif'" alt="<?=_t('왼쪽 정렬')?>" title="<?=_t('왼쪽 정렬')?>"/></td>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setAlignCenter.gif" width="20" height="20" onclick="TTCommand('JustifyCenter')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setAlignCenter_over.gif'" onmouseout="this.src='<?=$service['path']?>/image/owner/edit/setAlignCenter.gif'" alt="<?=_t('가운데 정렬')?>" title="<?=_t('가운데 정렬')?>"/></td>
		<td><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setAlignRight.gif" width="20" height="20" onclick="TTCommand('JustifyRight')" onmouseover="this.src='<?=$service['path']?>/image/owner/edit/setAlignRight_over.gif'" onmouseout="this.src='<?=$service['path']?>/image/owner/edit/setAlignRight.gif'" alt="<?=_t('오른쪽 정렬')?>" title="<?=_t('오른쪽 정렬')?>"/></td>
		<td><a href="#" tabindex="113" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image15','','<?=$service['path']?>/image/owner/edit/set_ul_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_ul.gif" name="Image15" width="20" height="20" border="0" id="Image15" onclick="TTCommand('InsertUnorderedList')" alt="<?=_t('순서없는 리스트')?>" title="<?=_t('순서없는 리스트')?>"/></a></td>
		<td><a href="#" tabindex="114" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image16','','<?=$service['path']?>/image/owner/edit/set_ol_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_ol.gif" name="Image16" width="20" height="20" border="0" id="Image16" onclick="TTCommand('InsertOrderedList')" alt="<?=_t('번호 매긴 리스트')?>" title="<?=_t('번호 매긴 리스트')?>"/></a></td>
		<td><a href="#" tabindex="115" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image17','','<?=$service['path']?>/image/owner/edit/set_outdent_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_outdent.gif" name="Image17" width="20" height="20" border="0" id="Image17" onclick="TTCommand('Outdent')" alt="<?=_t('내어쓰기')?>" title="<?=_t('내어쓰기')?>"/></a></td>
		<td><a href="#" tabindex="116" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image18','','<?=$service['path']?>/image/owner/edit/set_indent_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_indent.gif" name="Image18" width="20" height="20" border="0" id="Image18" onclick="TTCommand('Indent')" alt="<?=_t('들여쓰기')?>" title="<?=_t('들여쓰기')?>"/></a></td>
		<td><a href="#" tabindex="117" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image19','','<?=$service['path']?>/image/owner/edit/set_blockquote_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_blockquote.gif" name="Image19" width="20" height="20" border="0" id="Image19" onclick="TTCommand('Blockquote')" alt="<?=_t('인용구')?>" title="<?=_t('인용구')?>"/></a></td>
	  </tr>
	</table></td>
	<td><img src="<?=$service['path']?>/image/owner/edit/dotted_vertical.gif" width="5" height="24" /></td>
	<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><a href="#" tabindex="118" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image20','','<?=$service['path']?>/image/owner/edit/setCode_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setCode.gif" name="Image20" width="20" height="20" border="0" id="Image20" onclick="TTCommand('CodeBlock')" alt="<?=_t('코드')?>" title="<?=_t('코드')?>"/></a></td>
		<td><a href="#" tabindex="119" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image21','','<?=$service['path']?>/image/owner/edit/setHtml_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setHtml.gif" name="Image21" width="20" height="20" border="0" id="Image21" onclick="TTCommand('HtmlBlock')" alt="<?=_t('HTML 코드 직접 쓰기')?>" title="<?=_t('HTML 코드 직접 쓰기')?>"/></a></td>
		<td><a href="#" tabindex="120" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image22','','<?=$service['path']?>/image/owner/edit/setLink_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setLink.gif" name="Image22" width="20" height="20" border="0" id="Image22" onclick="TTCommand('CreateLink')" alt="<?=_t('하이퍼링크')?>" title="<?=_t('하이퍼링크')?>"/></a></td>
		<td><a href="#" tabindex="121" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image23','','<?=$service['path']?>/image/owner/edit/setEmbed_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setEmbed.gif" name="Image23" width="20" height="20" border="0" id="Image23" onclick="TTCommand('MediaBlock')" alt="<?=_t('미디어 삽입')?>" title="<?=_t('미디어 삽입')?>"/></a></td>
		<td><a href="#" tabindex="122" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image24','','<?=$service['path']?>/image/owner/edit/setFlash_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setFlash.gif" name="Image24" width="20" height="20" border="0" id="Image24" onclick="TTCommand('FlashBlock')" alt="<?=_t('플래시 삽입')?>" title="<?=_t('플래시 삽입')?>"/></a></td>
		<td><a href="#" tabindex="123" onclick="return false" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image25','','<?=$service['path']?>/image/owner/edit/setMoreLess_over.gif',1)"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMoreLess.gif" name="Image25" width="20" height="20" border="0" id="Image25" onclick="TTCommand('MoreLessBlock')" alt="<?=_t('More/Less')?>" title="<?=_t('More/Less')?>"/></a></td>
	  </tr>
	</table></td>
	<td>&nbsp;</td>
	<td valign="bottom"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/set_switch.gif" width="42" height="11" onclick="TTCommand('ToggleMode')" vspace="3" alt="<?=_t('위지윅/텍스트 모드 변경')?>" title="<?=_t('위지윅/텍스트 모드 변경')?>"/></td>
  </tr>
</table>
	<div id="colorPalette" style="display:none;margin:4px 0px 4px 10px">
	  <table bgcolor="#FFFFFF" cellspacing="3" style="border-style:solid;border-width:1;border-color:#A0A0A0">
		<tr>
		  <td bgcolor="#008000"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#008000')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#009966"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#009966')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#99CC66"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#99CC66')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#999966"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#999966')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#CC9900"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#CC9900')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#D41A01"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#D41A01')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FF0000"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#FF0000')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FF7635"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#FF7635')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FF9900"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#FF9900')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FF3399"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#FF3399')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#9B18C1"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#9B18C1')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#993366"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#993366')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#666699"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#666699')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#0000FF"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#0000FF')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#177FCD"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#177FCD')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#006699"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#006699')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#003366"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#003366')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#333333"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#333333')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#8E8E8E"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#8E8E8E')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#C1C1C1"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/spacer.gif" onclick="insertColorTag('#C1C1C1')" width="16" height="16" alt="" /></td>
		</tr>
	  </table>
	</div>
	<div id="markPalette" style="display:none;margin:4px 0px 4px 140px">
	  <table bgcolor="#FFFFFF" cellspacing="3" style="border-style:solid;border-width:1;border-color:#A0A0A0">
		<tr>
		  <td bgcolor="#FFDAED"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="insertMarkTag('#202020', '#FFDAED')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#C9EDFF"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="insertMarkTag('#202020', '#C9EDFF')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#D0FF9D"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="insertMarkTag('#202020', '#D0FF9D')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FAFFA9"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="insertMarkTag('#202020', '#FAFFA9')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#E4E4E4"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="insertMarkTag('#202020', '#E4E4E4')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FF0000"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewWhite.gif" onclick="insertMarkTag('#FFFFFF', '#FF0000')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#0000FF"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewWhite.gif" onclick="insertMarkTag('#FFFFFF', '#0000FF')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#009966"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewWhite.gif" onclick="insertMarkTag('#FFFFFF', '#009966')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#670787"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewWhite.gif" onclick="insertMarkTag('#FFFFFF', '#670787')" width="16" height="16" alt="" /></td>
		  <td bgcolor="#333333"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewWhite.gif" onclick="insertMarkTag('#FFFFFF', '#333333')" width="16" height="16" alt="" /></td>
		</tr>
	  </table>
	</div>
	<div id="textBox" style="display:none;margin:4px 0px 4px 200px">
	  <table bgcolor="#FFFFFF" cellspacing="3" style="border-style:solid;border-width:1;border-color:#A0A0A0">
		<tr>
		  <td bgcolor="#FFDAED"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FFDAED');" width="16" height="16" alt="" /></td>
		  <td bgcolor="#C9EDFF"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#C9EDFF');" width="16" height="16" alt="" /></td>
		  <td bgcolor="#D0FF9D"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#D0FF9D');" width="16" height="16" alt="" /></td>
		  <td bgcolor="#FAFFA9"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FAFFA9');" width="16" height="16" alt="" /></td>
		  <td bgcolor="#E4E4E4"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/edit/setMarkPreviewBlack.gif" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#E4E4E4');" width="16" height="16" alt="" /></td>
		</tr>
	  </table>
	</div>
<?
}

function printContentLine() {
?>
<table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="36" height="3" bgcolor="#0468AA"><img src="<?=$service['path']?>/image/spacer.gif" width="1" height="1" alt=""></td>
    <td width="*" bgcolor="#4498CF"><img src="<?=$service['path']?>/image/spacer.gif" width="1" height="1" alt=""></td>
  </tr>
</table>
<?
}

function printInputBlock() {
	global $service, $r_root_path;
?>
<table cellspacing="0" cellpadding="0" width="100%" style="margin:3 0 3 0">
  <tr>
    <td height="1" style="background-image:url('<?=$service['path']?>/image/dot_width2.gif')"></td>
  </tr>
</table>
<?
}

function getAttachmentValue($attachment) {
	global $g_attachmentFolderPath;
	if (strpos($attachment['mime'], 'image') === 0)
		return "{$attachment['name']}|width=\"{$attachment['width']}\" height=\"{$attachment['height']}\" alt=\"\"";
	else
		return "{$attachment['name']}|";
}

function getPrettyAttachmentLabel($attachment) {
	if (strpos($attachment['mime'], 'image') === 0)
				return "{$attachment['label']} ({$attachment['width']}x{$attachment['height']} / ".getSizeHumanReadable($attachment['size']).')';
	else if(strpos($attachment['mime'], 'audio') !== 0 && strpos($attachment['mime'], 'video') !== 0) {
		if ($attachment['downloads']>0)
			return "{$attachment['label']} (".getSizeHumanReadable($attachment['size']).' / '._t('다운로드').':'.$attachment['downloads'].')';		
	}
		return "{$attachment['label']} (" . getSizeHumanReadable($attachment['size']) . ')';
}
?>