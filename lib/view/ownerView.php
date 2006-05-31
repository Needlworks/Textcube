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
											var skinContentWidth = <?php echo $contentWidth?>;
											var s_notSupportHTMLBlock = "<?php echo _t('위지윅 모드에서는 [HTML][/HTML] 블럭을 사용할 수 없습니다.')?>";
											var s_enterURL = "<?php echo _t('URL을 입력하세요.')?>";
											var s_unknownFileType = "<?php echo _t('알 수 없는 형식의 파일명입니다')?>";
											var s_enterObjectTag = "<?php echo _t('OBJECT 태그만 입력하세요')?>";
											var s_enterCorrectObjectTag = "<?php echo _t('틀린 OBJECT 태그입니다')?>";
	 
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
													document.frames[0].document.forms[0].action = blogURL + "/owner/entry/attach<?php echo ($entryId ? "/$entryId" : '')?>";
													document.frames[0].document.forms[0].attachment.click();
												} else {
													var attachHidden = document.getElementById('attachHiddenNest');
													attachHidden.contentDocument.forms[0].action = blogURL + "/owner/entry/attach<?php echo ($entryId ? "/$entryId" : '')?>";
													attachHidden.contentDocument.forms[0].attachment.click();
												}
											}
											
											function deleteAttachment() {
												var fileList = document.getElementById('fileList');		
												
												if (fileList.selectedIndex < 0) {
													alert("<?php echo _t('삭제할 파일을 선택해 주십시오.')?>");
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
													alert("<?php echo _t('파일을 삭제하지 못했습니다.')?> ::"+e.message);
												}

												var request = new HTTPRequest("POST", "<?php echo $blogURL?>/owner/entry/detach/multi<?php echo ($entryId ? "/$entryId" : '/0')?>");
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
													alert("<?php echo _t('파일을 삭제하지 못했습니다.')?>");
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
													document.getElementById('previewSelected').innerHTML = '<img style="width: '+width+'px; height: 94px" src="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" alt="" onerror="this.src=\'<?php echo $service['path']?>/image/spacer.gif\'" />';
													//setAttribute('src',"<?php echo $service['path']?>/attach/<?php echo $owner?>/"+  fileName);
													//document.getElementById('selectedImage').setAttribute('src',"<?php echo $service['path']?>/image/spacer.gif");
													return false;
												}
												
												if((new RegExp("\\.(mp3)$", "gi").exec(fileName))) {
													var str = getEmbedCode("<?php echo $service['path']?>/script/jukebox/flash/mini.swf?__TT__="+(Math.random()*1000),"100%","100%", "jukeBox0Flash","#FFFFFF", "sounds=<?php echo $service['path']?>/attach/<?php echo $owner?>/"+fileName, "false"); 
													writeCode(str, 'previewSelected');
													return false;
												}
												
												if((new RegExp("\\.(swf)$", "gi").exec(fileName))) {			
													
													code = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%"><param name="movie" value="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" /><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" />';
													code += '<!--[if !IE]> <--><object type="application/x-shockwave-flash" data="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" width="100%" height="100%"><param name="allowScriptAccess" value="sameDomain" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /></object><!--> <![endif]--></object>';
													
													writeCode(code,'previewSelected');
													return false;
												}
												
												if((new RegExp("\\.(mov)$", "gi").exec(fileName))) {			
													code = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="'+width+'" height="'+height+'"><param name="src" value="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" /><param name="controller" value="true"><param name="scale" value="Aspect">';
													code += '<!--[if !IE]> <--><object type="video/quicktime" data="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" width="'+width+'" height="'+height+'" showcontrols="true" TYPE="video/quicktime" scale="Aspect" nomenu="true"><param name="showcontrols" value="true"><param name="scale" value="ToFit"></object><!--> <![endif]--></object>';
													
													writeCode(code,'previewSelected');
													
													return false;
												}
												
											
												if((new RegExp("\\.(mp2|wma|mid|midi|mpg|wav)$", "gi").exec(fileName))) {
													code ='<object width="'+width+'" height="'+height+'" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" standby="Loading for you" type="application/x-oleobject" align="middle">';		
													code +='<param name="FileName" value="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'">';
													code +='<param name="ShowStatusBar" value="False">';
													code +='<param name="DefaultFrame" value="mainFrame">';
													code +='<param name="showControls" value="false">';
													code +='<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" src="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" align="middle" width="'+width+'" height="'+height+'" showControls="false" defaultframe="mainFrame" showstatusbar="false"></embed>';
													code +='</object>';
													
													writeCode(code,'previewSelected');
													
													return false;
												}
												
												
												
													
												code +='<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" src="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" align="middle" width="'+width+'" height="'+height+'" showControls="false" defaultframe="mainFrame" showstatusbar="false"></embed>';
												
												writeCode(code,'previewSelected');
													
												
												if((new RegExp("\\.(rm|ram)$", "gi").exec(fileName))) {		
												/*
													code = '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+width+'" height="'+height+'"><param name="src" value="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" /><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE">';
													code += '<!--[if !IE]> <--><object type="audio/x-pn-realaudio-plugin" data="<?php echo $service['path']?>/attach/<?php echo $owner?>/'+fileName+'" width="'+width+'" height="'+height+'" ><param name="CONTROLS" value="imagewindow"><param name="AUTOGOTOURL" value="FALSE"><param name="CONSOLE" value="radio"><param name="AUTOSTART" value="TRUE"></object><!--> <![endif]--></object>';			
												*/
												}
												
												if (code == undefined || code == '') {
													document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?php echo _t('미리보기')?></td></tr></table>";
													return true;
												}
												
														
												
												return false;
												} catch (e) {
													document.getElementById('previewSelected').innerHTML = "<table width=\"100%\" height=\"100%\"><tr><td valign=\"middle\" align=\"center\"><?php echo _t('미리보기')?></td></tr></table>";	
													return true;
												}
											}
											
											function linkImage1(align) {
												var oSelect = document.forms[0].fileList;
												if (oSelect.selectedIndex < 0) {
													alert("<?php echo _t('파일을 선택하십시오.')?>");
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
										                if(editor.isMediaFile(value[0])) {
													getObject("propertyInsertObject_type").value = "url";
													getObject("propertyInsertObject_url").value = "<?="$hostURL$blogURL"?>" + "/attachment/" + value[0];
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
															src = servicePath + "/image/spacer.gif";
															value[1] = editor.styleUnknown;
															attributes = "";				
														}

														switch(align)
														{
															case "1L":
																var prefix = "<img class=\"tatterImageLeft\" src=\"" + src + "\" " + value[1] + " longdesc=\"1L|" + value[0] + "|" + attributes + "|\" />";
																break;
															case "1C":
																var prefix = "<img class=\"tatterImageCenter\" src=\"" + src + "\" " + value[1] + " longdesc=\"1C|" + value[0] + "|" + attributes + "|\" />";
																break;
															case "1R":
																var prefix = "<img class=\"tatterImageRight\" src=\"" + src + "\" " + value[1] + " longdesc=\"1R|" + value[0] + "|" + attributes + "|\" />";
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
													alert("<?php echo _t('파일 리스트에서 이미지를 2개 선택해 주세요. (ctrl + 마우스 왼쪽 클릭)')?>");
													return false;
												}
												var imageinfo = prefix.split("^");
												try {
													if(editor.editMode == "WYSIWYG") {			
														var prefix = '<img class="tatterImageDual" src="' + servicePath + '/image/spacer.gif" width="200" height="100" longdesc="2C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '" />';
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
													alert("<?php echo _t('파일 리스트에서 이미지를 3개 선택해 주세요. (ctrl + 마우스 왼쪽 클릭)')?>");
													return false;
												}
												var imageinfo = prefix.split("^");
												try {
													if(editor.editMode == "WYSIWYG") {
														var prefix = '<img class="tatterImageTriple" src="' + servicePath + '/image/spacer.gif" width="300" height="100" longdesc="3C|' + editor.addQuot(imageinfo[1]) + '|' + editor.addQuot(imageinfo[2]) + '|' + editor.addQuot(imageinfo[3]) + '" />';
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
											iMazingProperties['skinPath'] = '<?php echo $service['path']?>/script/gallery/iMazing/';
											
											function viewImazing()
											{
												try
												{
													var oSelect = document.forms[0].fileList;
													if (oSelect.selectedIndex < 0) {
														alert("<?php echo _t('파일을 선택하십시오.')?>");
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
														alert("<?php echo _t('이미지 파일만 삽입 가능 합니다.')?>");
														return false;
													}
													fileList = fileList.substr(0,fileList.length-1);
													var Properties = '';
													for (var name in iMazingProperties) {
														Properties += name+'='+iMazingProperties[name]+' ';
													}

													try {
														if(editor.editMode == "WYSIWYG") {
															TTCommand("Raw", '<img class="tatterImazing" src="' + servicePath + '/image/spacer.gif" width="400" height="300" longdesc="iMazing|' + fileList + '|' + Properties + '|" />');
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
														alert("<?php echo _t('파일을 선택하십시오.')?>");
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
														alert("<?php echo _t('이미지 파일만 삽입 가능 합니다.')?>");
														return false;
													}
													fileList = fileList.substr(0,fileList.length-1);

													try {
														if(editor.editMode == "WYSIWYG") {
															TTCommand("Raw", '<img class="tatterGallery" src="' + servicePath + '/image/spacer.gif" width="400" height="300" longdesc="Gallery|' + fileList + '|width=&quot;400&quot; height=&quot;300&quot;" />');
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
														alert("<?php echo _t('파일을 선택하십시오.')?>");
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
														alert("<?php echo _t('MP3 파일만 삽입 가능 합니다.')?>");
														return false;
													}
													fileList = fileList.substr(0,fileList.length-1);

													try {
														if(editor.editMode == "WYSIWYG") {
															TTCommand("Raw", '<img class="tatterJukebox" src="' + servicePath + '/image/spacer.gif" width="200" height="30" longdesc="Jukebox|' + fileList + '|autoplay=0 visible=1|" />');
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
<?php
}

function printEntryFileList($attachments, $entryId) {
	global $owner, $service, $blogURL;
	if(empty($attachments) || (
	strpos($attachments[0]['name'] ,'.gif') === false &&
	strpos($attachments[0]['name'] ,'.jpg') === false &&
	strpos($attachments[0]['name'] ,'.png') === false)) {
		$fileName =  "{$service['path']}{$service['adminSkin']}/image/spacer.gif";
	} else {
		$fileName = "{$service['path']}/attach/$owner/{$attachments[0]['name']}";
	}
?>					
	
													<div id="previewSelected" style="width: 120px; height: 94px;"><span><?php echo _t('미리보기')?></span></div>
													
													<div id="attachManagerSelectNest">				
														<span id="attachManagerSelect">
															<select id="fileList" name="fileList" multiple="multiple" size="8" onchange="selectAttachment();" ondblclick="downloadAttachment()">
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
		if ( $attachment['enclosure'] == 1)  {	
			$enclosureFileName = $attachment['name'];
		} else {
			$prefix = '';
		}
		
		$value = htmlspecialchars(getAttachmentValue($attachment));
		$label = $prefix.htmlspecialchars(getPrettyAttachmentLabel($attachment));
		
		$initialFileListForFlash .= escapeJSInAttribute($value.'(_!'.$label.'!^|');
?>
																<option<?php echo $style?> value="<?php echo $value?>"><?php echo $label?></option>
<?php
	}
?>
															</select>
														</span>
													</div>
													
													<script type="text/javascript">
														//<![CDATA[
															function disablePageManager() {
																try {
																	pageHolding  = entryManager.pageHolder.isHolding;
																	entryManager.pageHolder.isHolding = function () {
																		return false;
																	}
																	STD.removeEventListener(window);					
																	window.removeEventListener("beforeunload", PageMaster.prototype._onBeforeUnload, false);					
																} catch(e) {
																	alert(e.message);
																}
															}
															window.onLoad = function() {
																disablePageManager()
															}
															function enablePageManager() {
																try {
																	entryManager.pageHolder.isHolding = pageHolding ;
																	STD.addEventListener(window);
																	window.addEventListener("beforeunload", PageMaster.prototype._onBeforeUnload, false);				
																} catch(e) {
																	alert(e.message);					
																}
																
															}
																			
															function stripLabelToValue(fileLabel) {
																var pos = fileLabel.lastIndexOf('(');
																return fileLabel.substring(0,pos-1);	
															}
														
															function refreshAttachFormSize () {				
																fileListObj = document.getElementById('fileList');
																fileListObj.setAttribute('size',Math.max(8,Math.min(fileListObj.length,30)));

															}
															
															function refreshAttachList() {
																var request = new HTTPRequest("POST", "<?php echo $blogURL?>/owner/entry/attachmulti/refresh<?php echo ($entryId ? "/$entryId" : '/0')?>");
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
																	oOption.innerHTML= fileName+' ('+Math.ceil((fileSize/1024))+'KB)  <?php echo _t('대기 중..')?>';
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
																	var request = new HTTPRequest("POST", "<?php echo $blogURL?>/owner/entry/size?owner=<?php echo $owner?>&parent=<?php echo $entryId?>");
																	request.onVerify = function () {
																		return true;
																	}
																	
																	request.onSuccess = function() {
																		var result = this.getText("/response/result");
																		document.getElementById('fileSize').innerHTML = result;
																	}
																	request.onError = function() {
																	}
																	//disablePageManager();
																	request.send();
																	
																} catch(e) {
																	alert(e.message);
																}
															}
															
															refreshAttachFormSize();
														//]]>
													</script>
<?php
	require_once ROOT.'/script/detectFlash.inc';
	$maxSize = min( return_bytes(ini_get('upload_max_filesize')) , return_bytes(ini_get('post_max_size')) );
?>	
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
																+ '<param name="movie" value="<?php echo $service['path']?>/script/uploader/uploader.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><param name="scale" value="noScale" /><param name="wmode" value="transparent" /><param name="FlashVars" value="path=<?php echo $blogURL?>&owner=<?php echo $owner?>&entryid=<?php echo $entryId?>&enclosure=<?php echo $enclosureFileName?>&maxSize=<?php echo $maxSize?>&sessionName=TSSESSION&sessionValue=<?php echo $_COOKIE['TSSESSION']?>" />'
																+ '<embed id="uploader2" src="<?php echo $service['path']?>/script/uploader/uploader.swf" flashvars="path=<?php echo $blogURL?>&owner=<?php echo $owner?>&entryid=<?php echo $entryId?>&enclosure=<?php echo $enclosureFileName?>&maxSize=<?php echo $maxSize?>&sessionName=TSSESSION&sessionValue=<?php echo $_COOKIE['TSSESSION']?>" width="1" height="1" align="middle" wmode="transparent" quality="high" class="color-ffffff" scale="noScale" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /><\/embed><\/object>';
														//]]>
													</script>
													
													<span id="uploaderNest">
														<script type="text/javascript">
															//<![CDATA[
																if (hasRightVersion && isWin) {
																	writeCode(uploaderStr);
																}
															//]]>
														</script>
													</span>
<?php
}

function printEntryFileUploadButton($entryId) {
	global $owner, $service;
?>
											<div id="fileUploadNest" class="container">
												<script type="text/javascript">
													//<![CDATA[
														if (getUploadObj()) {
															try {
																document.write('<a id="uploadBtn" class="upload-button button" href="#void" onclick="browser();"><span><?php echo _t('파일 업로드')?></span></a>');
																document.write('<a id="stopUploadBtn" class="stop-button button" href="#void" onclick="stopUpload();" style="display: none;"><span><?php echo _t('업로드 중지')?></span></a>');			
															} catch(e) {
																
															}								
														}
														
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
															
															var str = '<iframe src="'+uri+'" id="attachHiddenNest_'+(attachId)+'"  style="display:block; height:'+property[userAgent]['height']+'; width:'+property[userAgent]['width']+'" frameborder="no" scrolling="no"></iframe>'
															var div = document.createElement('div');
															div.innerHTML= str;									
															document.getElementById('fileUploadNest').appendChild(div);
															var div = document.getElementById('div');
															if(attachId) { 
																document.getElementById('attachHiddenNest_'+(attachId-1)+'').style.width = 0;
																document.getElementById('attachHiddenNest_'+(attachId-1)+'').style.height = 0;
															}
															
															attachId++;
														}
														
														//if(<?php echo !empty($service['flashuploader']) ? $service['flashuploader'] : 'false'?> ) { 
														 if(!getUploadObj()) {
															if(isIE) {
																makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId?>","ie");
															} else if(isMoz) {
																makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId?>","moz");
															} else {
																makeCrossDamainSubmit(blogURL + "/owner/entry/attach/<?php echo $entryId?>","etc");
															}
														}
													//]]>
												</script>
													
												<a id="deleteBtn" class="button" href="#void" onclick="deleteAttachment();"><span><?php echo _t('삭제하기')?></span></a>
												<div id="fileSize">
													<?php echo getAttachmentSizeLabel($owner, $entryId)?>
												</div>
												<div id="fileDownload" style="display: none;"></div>
												
												<div class="clear"></div>
											</div>
<?php
}

function printEntryEditorProperty() {
	global $service;
?>
													<div id="propertyInsertObject" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b><?php echo _t('오브젝트 삽입')?></b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyInsertObject_type"><span class="text"><?php echo _t('유형')?></span></label></dt>
															<dd>
																<select id="propertyInsertObject_type" style="width: 105px" onchange="getObject('propertyInsertObject_part_url').style.display=getObject('propertyInsertObject_part_raw').style.display='none';getObject('propertyInsertObject_part_' + this.value).style.display = 'block'">
																	<option value="url"><?php echo _t('주소 입력')?></option>
																	<option value="raw"><?php echo _t('코드 붙여넣기')?></option>
																</select>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyInsertObject_url"><span class="text"><?php echo _t('파일 주소')?></span></label></dt>
															<dd><input type="text" id="propertyInsertObject_url" class="text-input" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyInsertObject_chunk"><span class="text"><?php echo _t('코드')?></span></label></dt>
															<dd>
																<textarea id="propertyInsertObject_chunk" cols="30" rows="10"></textarea>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<input type="button" class="button-input" onclick="TTCommand('InsertObject')" value="<?php echo _t('삽입하기')?>" />
														<input type="button" class="button-input" onclick="TTCommand('HideObjectBlock')" value="<?php echo _t('취소하기')?>" />
													</div>
													<div id="propertyLink" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Link</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyLink_href"><span class="text"><?php echo _t('URL')?></span></label></dt>
															<dd><input type="text" id="propertyLink_href" class="text-input" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyLink_target"><span class="text"><?php echo _t('타겟')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyLink_target" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyLink_title"><span class="text"><?php echo _t('타이틀')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyLink_title" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>
													
													<div id="propertyImage1" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Image</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage1_width1"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage1_width1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage1_alt1"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage1_alt1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage1_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage1_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyImage2" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Image 1</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_width1"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_width1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_alt1"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_alt1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>

														<div class="head-line">
															<b>Image 2</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_width2"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_width2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_alt2"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_alt2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage2_caption2"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage2_caption2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyImage3" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Image 1</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_width1"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_width1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_alt1"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_alt1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>

														<div class="head-line">
															<b>Image 2</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_width2"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_width2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_alt2"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_alt2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_caption2"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_caption2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>Image 3</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_width3"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_width3" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_alt3"><span class="text"><?php echo _t('대체 텍스트')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_alt3" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyImage3_caption3"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyImage3_caption3" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyObject" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Object</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject_width"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject_width" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject_height"><span class="text"><?php echo _t('높이')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject_height" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>

														<dl class="line">
															<dt class="property-name"><label for="propertyObject_chunk"><span class="text"><?php echo _t('코드')?></span></label></dt>
															<dd><textarea id="propertyObject_chunk" cols="30" rows="10" onkeyup="editor.setProperty()"></textarea></dd>
															<dd class="clear"></dd>
														</dl>
													</div>
													<div id="propertyObject1" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Object 1</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject1_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject1_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject1_filename1"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject1_filename1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyObject2" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Object 1</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject2_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject2_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject2_filename1"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject2_filename1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>Object 2</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject2_caption2"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject2_caption2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject2_filename2"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject2_filename2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyObject3" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Object 1</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_caption1"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_caption1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_filename1"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_filename1" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>Object 2</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_caption2"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_caption2" onkeyup="editor.setProperty()" />
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_filename2"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_filename2" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>Object 3</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_caption3"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_caption3" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyObject3_filename3"><span class="text"><?php echo _t('파일명')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyObject3_filename3" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyiMazing" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>iMazing</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_width"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyiMazing_width" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_height"><span class="text"><?php echo _t('높이')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyiMazing_height" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_frame"><span class="text"><?php echo _t('테두리')?></span></label></dt>
															<dd>
																<select id="propertyiMazing_frame" onchange="editor.setProperty()">
																	<option value="net_imazing_frame_none"><?php echo _t('테두리 없음')?></option>
																</select>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_tran"><span class="text"><?php echo _t('장면전환효과')?></span></label></dt>
															<dd>
																<select id="propertyiMazing_tran" onchange="editor.setProperty()">
																	<option value="net_imazing_show_window_transition_none"><?php echo _t('효과없음')?></option>
																	<option value="net_imazing_show_window_transition_alpha"><?php echo _t('투명전환')?></option>
																	<option value="net_imazing_show_window_transition_contrast"><?php echo _t('플래쉬')?></option>
																	<option value="net_imazing_show_window_transition_sliding"><?php echo _t('슬라이딩')?></option>
																</select>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_nav"><span class="text"><?php echo _t('내비게이션')?></span></label></dt>
															<dd>
																<select id="propertyiMazing_nav" onchange="editor.setProperty()">
																	<option value="net_imazing_show_window_navigation_none"><?php echo _t('기본')?></option>
																	<option value="net_imazing_show_window_navigation_simple"><?php echo _t('심플')?></option>
																	<option value="net_imazing_show_window_navigation_sidebar"><?php echo _t('사이드바')?></option>
																</select>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><?php echo _t('슬라이드쇼 간격')?></dt>
															<dd><input type="text" class="text-input" id="propertyiMazing_sshow" onkeyup="editor.setProperty()" />
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><?php echo _t('화면당 이미지 수')?></dt>
															<dd><input type="text" class="text-input" id="propertyiMazing_page" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_align"><span class="text"><?php echo _t('정렬방법')?></span></label></dt>
															<dd>
																<select id="propertyiMazing_align" onchange="editor.setProperty()">
																	<option value="h"><?php echo _t('가로')?></option>
																	<option value="v"><?php echo _t('세로')?></option>
																</select>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyiMazing_caption"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyiMazing_caption" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>File</b>
														</div>
														<dl class="line">
															<dd>
																<select id="propertyiMazing_list" class="file-list" size="10" onchange="editor.listChanged('propertyiMazing_list')" onclick="editor.listChanged('propertyiMazing_list')"></select>
															</dd>
															<dd class="button-box">
																<a href="#void" class="up-button button" onclick="editor.moveUpFileList('propertyiMazing_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.')?>"><span class="text"><?php echo _t('위로')?></span></a>
																<a href="#void" class="dn-button button" onclick="editor.moveDownFileList('propertyiMazing_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.')?>"><span class="text"><?php echo _t('아래로')?></span></a>
																<div class="clear"></div>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<div id="propertyiMazing_preview" class="preview-box" style="display: none;"></div>
													</div>

													<div id="propertyGallery" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Gallery</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyGallery_width"><span class="text"><?php echo _t('최대너비')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyGallery_width" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyGallery_height"><span class="text"><?php echo _t('최대높이')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyGallery_height" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyGallery_caption"><span class="text"><?php echo _t('자막')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyGallery_caption" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>File</b>
														</div>
														<dl class="line">
															<dd>
																<select id="propertyGallery_list" class="file-list" size="10" onchange="editor.listChanged('propertyGallery_list')" onclick="editor.listChanged('propertyGallery_list')"></select>
															</dd>
															<dd class="button-box">
																<a href="#void" class="up-button button" onclick="editor.moveUpFileList('propertyGallery_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.')?>"><span class="text"><?php echo _t('위로')?></span></a>
																<a href="#void" class="dn-button button" onclick="editor.moveDownFileList('propertyGallery_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.')?>"><span class="text"><?php echo _t('아래로')?></span></a>
																<div class="clear"></div>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<div id="propertyGallery_preview" class="preview-box" style="display: none;"></div>
													</div>

													<div id="propertyJukebox" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Jukebox</b>
														</div>
														<dl class="line">
															<dt class="property-name"><span class="text"><?php echo _t('재생옵션')?></span></dt>
															<dd>
																<input type="text" class="text-input" id="propertyJukebox_autoplay" onkeyup="editor.setProperty()" />
																<label for="propertyJukebox_autoplay"><span class="text"><?php echo _t('자동재생')?></span></label>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><span class="text"><?php echo _t('화면표시')?></span></dt>
															<dd>
																<input type="text" class="text-input" id="propertyJukebox_visibility" onkeyup="editor.setProperty()" />
																<label for="propertyJukebox_visibility"><span class="text"><?php echo _t('플레이어보이기')?></span></label>
															</dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyJukebox_title"><span class="text"><?php echo _t('제목')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyJukebox_title" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														
														<div class="head-line">
															<b>File</b>
														</div>
														<dl class="line">
															<dd>
																<select id="propertyJukebox_list" class="file-list" size="10" onchange="editor.listChanged('propertyJukebox_list')" onclick="editor.listChanged('propertyJukebox_list')"></select>
															</dd>
															<dd class="button-box">
																<a href="#void" class="up-button button" onclick="editor.moveUpFileList('propertyJukebox_list')" title="<?php echo _t('선택한 항목을 위로 이동합니다.')?>"><span class="text"><?php echo _t('위로')?></span></a>
																<a href="#void" class="dn-button button" onclick="editor.moveDownFileList('propertyJukebox_list')" title="<?php echo _t('선택한 항목을 아래로 이동합니다.')?>"><span class="text"><?php echo _t('아래로')?></span></a>
																<div class="clear"></div>
															</dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyEmbed" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Embed</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyEmbed_width"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyEmbed_width" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyEmbed_height"><span class="text"><?php echo _t('높이')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyEmbed_height" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyEmbed_src"><span class="text">URL</span></label></dt>
															<dd><input type="text" class="text-input" id="propertyEmbed_src" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyFlash" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>Embed</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyFlash_width"><span class="text"><?php echo _t('폭')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyFlash_width" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyFlash_height"><span class="text"><?php echo _t('높이')?></span></label></dt>
															<dd><input type="text" class="text-input" id="propertyFlash_height" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyFlash_src"><span class="text">URL</span></label></dt>
															<dd><input type="text" class="text-input" id="propertyFlash_src" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>

													<div id="propertyMoreLess" class="entry-editor-property" style="display: none;">
														<div class="head-line">
															<b>More/Less</b>
														</div>
														<dl class="line">
															<dt class="property-name"><label for="propertyMoreLess_more"><span class="text">More Text</span></label></dt>
															<dd><input type="text" class="text-input" id="propertyMoreLess_more" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
														<dl class="line">
															<dt class="property-name"><label for="propertyMoreLess_less"><span class="text">Less Text</span></label></dt>
															<dd><input type="text" class="text-input" id="propertyMoreLess_less" onkeyup="editor.setProperty()" /></dd>
															<dd class="clear"></dd>
														</dl>
													</div>
<?php
}
function printEntryEditorPalette() {
	global $owner, $service;
?>
											<div id="editor-palette" class="container">
												<dl class="font-relatives">
													<dt class="title">
														<span class="text"><?php echo _t('폰트 설정')?></span>
													</dt>
													<dd class="command-box">
														<select id="fontFamilyChanger" onchange="editor.execCommand('fontname', false, this.value); this.selectedIndex=0;">
															<option><?php echo _t('글자체')?></option>
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
														<select id="fontSizeChanger" onchange="editor.execCommand('fontsize', false, this.value); this.selectedIndex=0;">
															<option><?php echo _t('크기')?></option>
															<option value="">=======</option>
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
														<span class="text"><?php echo _t('폰트 스타일')?></span>
													</dt>
													<dd class="command-box">
														<a id="indicatorBold" class="button" href="#void" onclick="TTCommand('Bold')" title="<?php echo _t('굵게')?>"><span class="text"><?php echo _t('굵게')?></span></a>
														<a id="indicatorItalic" class="button" href="#void" onclick="TTCommand('Italic')" title="<?php echo _t('기울임')?>"><span class="text"><?php echo _t('기울임')?></span></a>
														<a id="indicatorUnderline" class="button" href="#void" onclick="TTCommand('Underline')" title="<?php echo _t('밑줄')?>"><span class="text"><?php echo _t('밑줄')?></span></a>
														<a id="indicatorStrike" class="button" href="#void" onclick="TTCommand('StrikeThrough')" title="<?php echo _t('취소선')?>"><span class="text"><?php echo _t('취소선')?></span></a>
														<a id="indicatorColorPalette" class="button" href="#void" onclick="hideLayer('markPalette'); hideLayer('textBox'); toggleLayer('colorPalette');" title="<?php echo _t('글자색')?>"><span class="text"><?php echo _t('글자색')?></span></a>
														<span id="colorPalette" style="display: none;">
															<table cellspacing="3" cellpadding="0">
																<tr>
																	<td class="color-008000" onclick="insertColorTag('#008000')"><span class="text">#008000</span></td>
																	<td class="color-009966" onclick="insertColorTag('#009966')"><span class="text">#009966</span></td>
																	<td class="color-99CC66" onclick="insertColorTag('#99CC66')"><span class="text">#99CC66</span></td>
																	<td class="color-999966" onclick="insertColorTag('#999966')"><span class="text">#999966</span></td>
																	<td class="color-CC9900" onclick="insertColorTag('#CC9900')"><span class="text">#CC9900</span></td>
																	<td class="color-D41A01" onclick="insertColorTag('#D41A01')"><span class="text">#D41A01</span></td>
																	<td class="color-FF0000" onclick="insertColorTag('#FF0000')"><span class="text">#FF0000</span></td>
																	<td class="color-FF7635" onclick="insertColorTag('#FF7635')"><span class="text">#FF7635</span></td>
																	<td class="color-FF9900" onclick="insertColorTag('#FF9900')"><span class="text">#FF9900</span></td>
																	<td class="color-FF3399" onclick="insertColorTag('#FF3399')"><span class="text">#FF3399</span></td>
																	<td class="color-9B18C1" onclick="insertColorTag('#9B18C1')"><span class="text">#9B18C1</span></td>
																	<td class="color-993366" onclick="insertColorTag('#993366')"><span class="text">#993366</span></td>
																	<td class="color-666699" onclick="insertColorTag('#666699')"><span class="text">#666699</span></td>
																	<td class="color-0000FF" onclick="insertColorTag('#0000FF')"><span class="text">#0000FF</span></td>
																	<td class="color-177FCD" onclick="insertColorTag('#177FCD')"><span class="text">#177FCD</span></td>
																	<td class="color-006699" onclick="insertColorTag('#006699')"><span class="text">#006699</span></td>
																	<td class="color-003366" onclick="insertColorTag('#003366')"><span class="text">#003366</span></td>
																	<td class="color-333333" onclick="insertColorTag('#333333')"><span class="text">#333333</span></td>
																	<td class="color-000000" onclick="insertColorTag('#000000')"><span class="text">#000000</span></td>			
																	<td class="color-8E8E8E" onclick="insertColorTag('#8E8E8E')"><span class="text">#8E8E8E</span></td>
																	<td class="color-C1C1C1" onclick="insertColorTag('#C1C1C1')"><span class="text">#C1C1C1</span></td>
																</tr>
															</table>
														</span>
														<a id="indicatorMarkPalette" class="button" href="#void" onclick="hideLayer('colorPalette');hideLayer('textBox');toggleLayer('markPalette')" title="<?php echo _t('배경색')?>"><span class="text"><?php echo _t('배경색')?></span></a>
														<span id="markPalette" style="display: none;">
															<table cellspacing="3" cellpadding="0">
																<tr>
																	<td class="color-FFDAED" onclick="insertMarkTag('#202020', '#FFDAED')"><span class="text">#FFDAED</span></td>
																	<td class="color-C9EDFF" onclick="insertMarkTag('#202020', '#C9EDFF')"><span class="text">#C9EDFF</span></td>
																	<td class="color-D0FF9D" onclick="insertMarkTag('#202020', '#D0FF9D')"><span class="text">#D0FF9D</span></td>
																	<td class="color-FAFFA9" onclick="insertMarkTag('#202020', '#FAFFA9')"><span class="text">#FAFFA9</span></td>
																	<td class="color-E4E4E4" onclick="insertMarkTag('#202020', '#E4E4E4')"><span class="text">#E4E4E4</span></td>
																	<td class="color-FF0000" onclick="insertMarkTag('#FFFFFF', '#FF0000')"><span class="text">#FF0000</span></td>
																	<td class="color-0000FF" onclick="insertMarkTag('#FFFFFF', '#0000FF')"><span class="text">#0000FF</span></td>
																	<td class="color-009966" onclick="insertMarkTag('#FFFFFF', '#009966')"><span class="text">#009966</span></td>
																	<td class="color-670787" onclick="insertMarkTag('#FFFFFF', '#670787')"><span class="text">#670787</span></td>
																	<td class="color-333333" onclick="insertMarkTag('#FFFFFF', '#333333')"><span class="text">#333333</span></td>
																</tr>
															</table>
														</span>
														<a id="indicatorTextBox" class="button" href="#void" onclick="hideLayer('markPalette');hideLayer('colorPalette');toggleLayer('textBox')" title="<?php echo _t('텍스트 상자')?>"><span class="text"><?php echo _t('텍스트 상자')?></span></a>
														<span id="textBox" style="display: none;">
															<table cellspacing="3" cellpadding="0">
																<tr>
																	<td class="color-FFDAED" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FFDAED');"><span class="text">#FFDAED</span></td>
																	<td class="color-C9EDFF" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#C9EDFF');"><span class="text">#C9EDFF</span></td>
																	<td class="color-D0FF9D" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#D0FF9D');"><span class="text">#D0FF9D</span></td>
																	<td class="color-FAFFA9" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#FAFFA9');"><span class="text">#FAFFA9</span></td>
																	<td class="color-E4E4E4" onclick="hideLayer('textBox'); TTCommand('Box', 'padding:10px; background-color:#E4E4E4');"><span class="text">#E4E4E4</span></td>
																</tr>
															</table>
														</span>
														<a id="indicatorRemoveFormat" class="button" href="#void" onclick="TTCommand('RemoveFormat')" title="<?php echo _t('효과 제거')?>"><span class="text"><?php echo _t('효과 제거')?></span></a>
													</dd>
												</dl>
												<dl class="paragraph">
													<dt class="title">
														<span class="text"><?php echo _t('문단')?></span>
													</dt>
													<dd class="command-box">
														<a id="indicatorJustifyLeft" class="button" href="#void" onclick="TTCommand('JustifyLeft')" title="<?php echo _t('왼쪽 정렬')?>"><span class="text"><?php echo _t('왼쪽 정렬')?></span></a>
														<a id="indicatorJustifyCenter" class="button" href="#void" onclick="TTCommand('JustifyCenter')" title="<?php echo _t('가운데 정렬')?>"><span class="text"><?php echo _t('가운데 정렬')?></span></a>
														<a id="indicatorJustifyRight" class="button" href="#void" onclick="TTCommand('JustifyRight')" title="<?php echo _t('오른쪽 정렬')?>"><span class="text"><?php echo _t('오른쪽 정렬')?></span></a>
														<a id="indicatorUnorderedList" class="button" href="#void" onclick="TTCommand('InsertUnorderedList')" title="<?php echo _t('순서없는 리스트')?>"><span class="text"><?php echo _t('순서없는 리스트')?></span></a>
														<a id="indicatorOrderedList" class="button" href="#void" onclick="TTCommand('InsertOrderedList')" title="<?php echo _t('번호 매긴 리스트')?>"><span class="text"><?php echo _t('번호 매긴 리스트')?></span></a>
														<a id="indicatorOutdent" class="button" href="#void" onclick="TTCommand('Outdent')" title="<?php echo _t('내어쓰기')?>"><span class="text"><?php echo _t('내어쓰기')?></span></a>
														<a id="indicatorIndent" class="button" href="#void" onclick="TTCommand('Indent')" title="<?php echo _t('들여쓰기')?>"><span class="text"><?php echo _t('들여쓰기')?></span></a>
														<a id="indicatorBlockquote" class="button" href="#void" onclick="TTCommand('Blockquote')" title="<?php echo _t('인용구')?>"><span class="text"><?php echo _t('인용구')?></span></a>
													</dd>
												</dl>
												<dl class="special">
													<dt class="title">
														<span class="text"><?php echo _t('기타')?></span>
													</dt>
													<dd class="command-box">
														<a id="indicatorCodeBlock" class="button" href="#void" onclick="TTCommand('CodeBlock')" title="<?php echo _t('코드')?>"><span class="text"><?php echo _t('코드')?></span></a>
														<a id="indicatorHtmlBlock" class="button" href="#void" onclick="TTCommand('HtmlBlock')" title="<?php echo _t('HTML 코드 직접 쓰기')?>"><span class="text"><?php echo _t('HTML 코드 직접 쓰기')?></span></a>
														<a id="indicatorCreateLink" class="button" href="#void" onclick="TTCommand('CreateLink')" title="<?php echo _t('하이퍼링크')?>"><span class="text"><?php echo _t('하이퍼링크')?></span></a>
														<a id="indicatorMediaBlock" class="button" href="#void" onclick="TTCommand('ObjectBlock')" title="<?php echo _t('미디어 삽입')?>"><span class="text"><?php echo _t('미디어 삽입')?></span></a>
														<a id="indicatorMoreLessBlock" class="button" href="#void" onclick="TTCommand('MoreLessBlock')" title="<?php echo _t('More/Less')?>"><span class="text"><?php echo _t('More/Less')?></span></a>
													</dd>
												</dl>
												<dl class="mode">
													<dt class="title">
														<span class="text"><?php echo _t('편집 모드')?></span>
													</dt>
													<dd class="command-box">
														<a id="indicatorMode" class="button" href="#void" onclick="TTCommand('ToggleMode')" title="<?php echo _t('위지윅/텍스트 모드 변경')?>"><span class="text"><?php echo _t('위지윅/텍스트 모드 변경')?></span></a>
													</dd>
												</dl>
												
												<div class="clear"></div>
											</div>
<?php
}

function printContentLine() {
?>
<table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="36" height="3" class="color-0468AA"><img src="<?php echo $service['path']?>/image/spacer.gif" width="1" height="1" alt=""></td>
    <td width="*" class="color-4498CF"><img src="<?php echo $service['path']?>/image/spacer.gif" width="1" height="1" alt=""></td>
  </tr>
</table>
<?php
}

function printInputBlock() {
	global $service, $r_root_path;
?>
<table cellspacing="0" cellpadding="0" width="100%" style="margin:3 0 3 0">
  <tr>
    <td height="1" style="background-image:url('<?php echo $service['path']?>/image/dot_width2.gif')"></td>
  </tr>
</table>
<?php
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
		return "{$attachment['label']} ({$attachment['width']}×{$attachment['height']} / ".getSizeHumanReadable($attachment['size']).')';
	else if(strpos($attachment['mime'], 'audio') !== 0 && strpos($attachment['mime'], 'video') !== 0) {
		if ($attachment['downloads']>0)
			return "{$attachment['label']} (".getSizeHumanReadable($attachment['size']).' / '._t('다운로드').':'.$attachment['downloads'].')';		
	}
	return "{$attachment['label']} (".getSizeHumanReadable($attachment['size']).')';
}

?>
