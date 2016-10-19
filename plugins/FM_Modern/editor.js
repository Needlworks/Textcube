/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

var TTModernEditor = function() {
	// 현재 수정중인 property 관련 정보들
	this.propertyWindowId = "";
	this.propertyHeader = "";
	this.propertyFilename1 = "";
	this.propertyFilename2 = "";
	this.propertyFilename3 = "";
	this.propertyFilePath = "";
	this.propertyCurrentProportion1 = 0;
	this.propertyCurrentProportion2 = 0;
	this.propertyCurrentProportion3 = 0;
	this.propertyCurrentImage = "";
	this.propertyOffsetTop = null;

	this.propertyNames = ["propertyHyperLink", "propertyInsertObject", "propertyImage1", "propertyImage2", "propertyImage3", "propertyObject", "propertyObject1", "propertyObject2", "propertyiMazing", "propertyGallery", "propertyJukebox", "propertyEmbed", "propertyFlash", "propertyMoreLess"];

	// 커서가 있는곳의 스타일
	this.isBold = false;
	this.isItalic = false;
	this.isUnderline = false;
	this.isStrike = false;
	this.fontName = null;
	this.fontSize = null;

	// MORE/LESS 블럭을 선택했을때의 임시변수
	this.textMore = "";
	this.textLess = "";

	this.editMode = "TEXTAREA";
	this.styleUnknown = 'style="width: 90px; height: 30px; border: 2px outset #796; background-color: #efd; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + servicePath + '/resources/image/extension/unknown.gif\')"';

	this.buildFontMap();
}

TTModernEditor.prototype.buildFontMap = function() {
	var fontset = _t('fontDisplayName:fontCode:fontFamily').split('|');
	var defaultfonts = [
		['Andale Mono', 'times'],
		['Arial', 'helvetica', 'sans-serif'],
		['Arial Black', 'avant garde'],
		['Book Antiqua', 'palatino'],
		['Comic Sans MS', 'sand'],
		['Courier New', 'courier', 'monospace'],
		['Georgia', 'times new roman', 'times', 'serif'],
		['Helvetica'],
		['Impact', 'chicago'],
		['Symbol'],
		['Tahoma', 'arial', 'helvetica', 'sans-serif'],
		['Terminal', 'monaco'],
		['Times New Roman', 'times', 'serif'],
		['Trebuchet MS', 'geneva'],
		['Verdana', 'arial', 'helvetica', 'sans-serif'],
		['Webdings'],
		['Wingdings', 'zapf dingbats']
	];
	var fontlist = [], fontmap = {};

	for (var i = 1; i < fontset.length; ++i) {
		var fontinfo = fontset[i].split(':');
		if (fontinfo.length != 3) continue;
		var value = "'" + fontinfo[1] + "', " + fontinfo[2];
		fontlist.push([fontinfo[0], value]);
		fontmap[fontinfo[0]] = value;
		fontmap[fontinfo[1]] = value;
	}
	for (var i = 0; i < defaultfonts.length; ++i) {
		var entry = defaultfonts[i];
		var value = "'" + entry.join("','") + "'";
		fontlist.push([entry[0], value]);
		for (var j = 0; j < entry.length; ++j) fontmap[entry[j]] = value;
	}

	this.allFontList = fontlist;
	this.allFontMap = fontmap;
}

TTModernEditor.editors = {};

// 각종 환경 초기화
TTModernEditor.prototype.initialize = function(textarea) {
	// execCommand가 사용가능한 경우에만 위지윅을 쓸 수 있다. (지금은 Internet Explorer, Firefox, Safari 3만 지원한다)
	if(typeof(document.execCommand) == "undefined" || !(STD.isIE || STD.isFirefox || (STD.isWebkit && STD.engineVersion >= 419.3)))
		return;
	// Set editor mode for formatters.
	if(this.formatter == 'textile' || this.formatter == 'markdown') {
		this.editMode = 'TEXTAREA';
		this.restrictEditorMode = true;
	} else this.restrictEditorMode = false;
	for (var x in TTModernEditor.editors) return x; // ignore if there is any other instance
	var seed = (textarea.id || 'unknown').replace(new RegExp('[^a-z0-9_]', 'gi'), '');
	this.name = seed;
	for (var i = 0; TTModernEditor.editors[this.name]; ++i)
		this.name = seed + i;
	TTModernEditor.editors[this.name] = this;
	this.id = 'moderneditor-' + this.name + '-';

	// 마우스로 클릭했을때 클릭한 위치의 오브젝트의 인스턴스를 저장할 변수
	this.selectedElement = null;

	// 선택된 위치 상위노드에 a 태그가 있으면 여기에 저장된다
	this.selectedAnchorElement = null;

	// 포커스가 벗어나도 선택영역을 유지하기 위해 selection을 저장해둔다
	this.selection = null;

	// add bounding boxes around given textarea
	var div = document.createElement('div');
	div.id = 'moderneditor-textbox';
	div.className = 'container';
	//textarea.parentNode.insertBefore(this.getEditorPalette(), textarea);
	document.getElementById('formatbox-container').innerHTML = this.getEditorPalette(true);
	textarea.parentNode.insertBefore(div, textarea);
	textarea.parentNode.removeChild(textarea);
	div.appendChild(textarea);
	var hr = document.createElement('hr');
	hr.className = 'hidden';
	div.appendChild(hr);
	div.appendChild(this.getEditorProperty());

	// style given textarea
	textarea.className += ' moderneditor-textarea';
	// 원래 있던 TEXTAREA의 핸들을 저장해둔다
	this.textarea = textarea;
	if(this.editMode == "WYSIWYG")
		this.textarea.style.display = "none";

	// 디자인모드의 IFRAME을 생성한다
	this.iframe = document.createElement("iframe");
	this.iframe.id = "tatterVisualEditor";
	this.iframe.instance = this;
	this.iframe.className = "tatterVisualArea";
	this.iframe.setAttribute("border", "0");
	this.iframe.setAttribute("frameBorder", "0");
	this.iframe.setAttribute("marginWidth", "0");
	this.iframe.setAttribute("marginHeight", "0");
	this.iframe.setAttribute("leftMargin", "0");
	this.iframe.setAttribute("topMargin", "0");
	this.iframe.setAttribute("allowtransparency", "true");
	this.iframe.style.height = STD.isIE ? "448px" : "452px";
	this.iframe.style.margin = "0px auto";
	this.iframe.style.overflowY = "scroll";
	this.iframe.style.width = Math.min(skinContentWidth + (STD.isIE ? 56 : 64), 1050) + "px";

	// IFRAME을 감싸는 DIV
	//this.iframeWrapper = document.createElement("div");
	//this.iframeWrapper.id = "iframeWrapper";
	//this.iframeWrapper.appendChild(this.iframe);

	//textarea.parentNode.insertBefore(this.iframeWrapper, textarea);
	//textarea.parentNode.insertBefore(this.iframe, textarea);
	textarea.parentNode.insertBefore(this.iframe, textarea.nextSibling);

	// 자주 참조하는 핸들을 지정해둔다
	if (STD.isIE) {
		this.contentDocument = document.frames[this.iframe.id].document;
		this.contentWindow = this.contentDocument.parentWindow;
	} else {
		this.contentWindow = this.iframe.contentWindow;
		this.contentDocument = this.contentWindow.document;
	}

	// 디자인모드로 변경한다
	// Changing to browser desingmode.
	try { this.contentDocument.designMode = "on"; }
	catch(e) { return; }

	// IFRAME 안에 HTML을 작성한다
	// Put HTML code into IFRAME
	this.contentDocument.open("text/html", "replace");
	this.contentDocument.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">');
	this.contentDocument.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko"><head><meta http-equiv="content-type" content="text/html; charset=utf-8" />');
	this.contentDocument.write('<link rel="stylesheet" type="text/css" href="' + servicePath + editorCSS + '" />');
	this.contentDocument.write('<style type="text/css">');
	this.contentDocument.write('/*<![CDATA[*/');
	if(STD.isIE)
		this.contentDocument.write("body { padding: 10px; }");
	else
		this.contentDocument.write("html { padding: 10px; }");
	this.contentDocument.write('/*]]>*/');
	this.contentDocument.write("</style>");
	this.contentDocument.write("</head><body>");
	this.contentDocument.write(this.ttml2html());
	this.contentDocument.write("</body></html>");
	this.contentDocument.close();

	var _this = this; // make new scope closure

	// IFRAME 내에서 발생하는 이벤트 핸들러를 연결
	// Connect event handlers occurring in IFRAME
	STD.addEventListener(this.contentDocument);
	var eventHandler = function(event) { _this.eventHandler(event); };

	this.contentDocument.addEventListener("mousedown", eventHandler, false);
	this.contentDocument.addEventListener("mouseup", eventHandler, false);
	this.contentDocument.addEventListener("keydown", eventHandler, false);
	this.contentDocument.addEventListener("keypress", eventHandler, false);
	this.contentDocument.addEventListener("paste", eventHandler, false);
	this.contentDocument.addEventListener("keyup", eventHandler, false);

	this.lastSelectionRange = null;

	// editor height resize event
	var target = (this.editMode == "WYSIWYG" ? this.iframe : textarea);
	this.resizer = new TTEditorResizer(target, getObject('status-container'), [document, this.contentDocument]);
	this.resizer.onResizeBegin = function() {
		getObject('attachManagerSelectNest').style.visibility = 'hidden';
		getObject(_this.id + "propertyiMazing_list").style.visibility = "hidden";
		getObject(_this.id + "propertyGallery_list").style.visibility = "hidden";
		getObject(_this.id + "propertyJukebox_list").style.visibility = "hidden";
	};
	this.resizer.onResizeEnd = function() {
		getObject('attachManagerSelectNest').style.visibility = 'visible';
		getObject(_this.id + "propertyiMazing_list").style.visibility = "visible";
		getObject(_this.id + "propertyGallery_list").style.visibility = "visible";
		getObject(_this.id + "propertyJukebox_list").style.visibility = "visible";
	};
	this.resizer.initialize();

	// textarea event
	STD.addEventListener(textarea);
	var textareaEventHandler = function() { editorChanged(); savePosition(_this.textarea); return true; };
	this.textareaEventHandler_bounded = textareaEventHandler; // keep it to remove the handler later
	textarea.addEventListener("select", textareaEventHandler, false);
	textarea.addEventListener("click", textareaEventHandler, false);
	textarea.addEventListener("keyup", textareaEventHandler, false);

	var scrollEventHandler = function() { _this.setPropertyPosition(); return true; };
	this.scrollEventHandler_bounded = scrollEventHandler;
	window.addEventListener("scroll", scrollEventHandler, false);

	if(this.editMode == "TEXTAREA")
		this.iframe.style.display = "none";
	// 데이터 싱크 과정.
	// 가끔씩 Firefox에서 커서가 움직이지 않는 문제 수정
	if(!STD.isIE) setTimeout(function() { try { _this.contentDocument.designMode='on'; } catch (e) {} }, 100);
}

TTModernEditor.prototype.finalize = function() {
	return true;	// From 1.8, we do not need to finalize DOM
	// Codes below is legacy code.
	this.resizer.finalize();

	var textarea = this.textarea;
	textarea.removeEventListener("select", this.textareaEventHandler_bounded, false);
	textarea.removeEventListener("click", this.textareaEventHandler_bounded, false);
	textarea.removeEventListener("keyup", this.textareaEventHandler_bounded, false);

	window.addEventListener("scroll", this.scrollEventHandler_bounded, false);

	/*
	<div id="moderneditor-palette" />
	<div id="moderneditor-textbox">
		<iframe id="tatterVisualEditor" />
		<textarea class=".... moderneditor-textarea" style="display:none" />
		<hr />
		<div id="property-section" />
	</div>
	*/
	var outer = textarea.parentNode.parentNode;
	var textbox = getObject('moderneditor-textbox');
	outer.removeChild(getObject('moderneditor-palette'));
	textbox.removeChild(getObject('tatterVisualEditor'));
	textbox.removeChild(textarea.nextSibling); // expected to be <hr />
	textbox.removeChild(textarea);
	textbox.removeChild(getObject('property-section'));
	outer.insertBefore(textarea, textbox);
	outer.removeChild(textbox);

	textarea.style.display = '';
	textarea.className = textarea.className.replace(' moderneditor-textarea', '');

	delete TTModernEditor.editors[this.name];
}

TTModernEditor.prototype.syncContents = function() {
	if (this.editMode == "WYSIWYG") {
		this.textarea.value = this.html2ttml();
	} else if (this.editMode == "TEXTAREA") {
		this.contentDocument.body.innerHTML = this.ttml2html();
	}
}

TTModernEditor.prototype.syncTextarea = function() {
	this.correctContent();
	return this.syncContents();
}
// TTML로 작성된 파일을 HTML 뷰에 뿌려주기 위해 변환
// Convert TTML-format to HTML view
TTModernEditor.prototype.ttml2html = function() {
	var str = this.textarea.value;
	// Safari 3 / webkit에서 디자인모드에 자동으로 붙이는 주석 제거
	str = str.replaceAll('class="Apple-style-span"','');
	str = str.replaceAll('class="webkit-block-placeholder"','');

	// MORE/LESS 처리
	while(true) {
		var pos1 = str.indexOf("[#M_");

		if(pos1 > -1) {
			var pos2 = str.indexOf("_M#]", pos1);

			if(pos2 > -1) {
				var block = str.substring(pos1 + 4, pos2);

				while(true) {
					if(block.indexOf("[#M_") == -1)
						break;
					else
						block = block.substring(block.indexOf("[#M_") + 4, block.length);
				}

				var more = this.htmlspecialchars(block.substring(0, block.indexOf("|")));
				var remain = block.substring(block.indexOf("|") + 1, block.length);
				var less = this.htmlspecialchars(remain.substring(0, remain.indexOf("|")));
				remain = remain.substring(remain.indexOf("|"), remain.length);
				var body = remain.substring(remain.indexOf("|") + 1, remain.length);

				str = str.replaceAll("[#M_" + block + "_M#]", '<div class="tattermoreless" more="' + more + '" less="' + less + '">' + body + '</div>');
			}
			else
				break;
		}
		else
			break;
	}

	// 이미지 치환자 처리
	var regImage = new RegExp("\\[##_(([1-3][CLR])(\\|[^|]*?)+)_##\\]", "");
	while(result = regImage.exec(str)) {
		var search = result[0];

		var longDesc = ' longdesc="' + this.addQuot(this.htmlspecialchars(result[1])) + '" ';

		// Avoid the bug
		longDesc = longDesc.replaceAll("&lt;", "&amp;lt;");
		longDesc = longDesc.replaceAll("&gt;", "&amp;gt;");

		var attributes = result[1].split("|");
		var imageType = attributes[0];

		if(this.isImageFile(attributes[1])) {
			var imageName = this.propertyFilePath + attributes[1];
			var imageAttr = this.parseImageSize(attributes[2], "string");
		}
		else {
			var imageName = servicePath + adminSkin + "/image/spacer.gif";
			var imageAttr = this.styleUnknown;
		}

		switch(imageType) {
			case "1L":
				var replace = '<img class="tatterImageLeft" src="' + imageName + '" ' + imageAttr + longDesc + " />";
				break;
			case "1R":
				var replace = '<img class="tatterImageRight" src="' + imageName + '" ' + imageAttr + longDesc + " />";
				break;
			case "1C":
				var replace = '<img class="tatterImageCenter" src="' + imageName + '\" ' + imageAttr + longDesc + " />";
				break;
			case "2C":
				var replace = '<img class="tatterImageDual" src="' + servicePath + adminSkin + '/image/spacer.gif" width="200" height="100" ' + longDesc + " />";
				break;
			case "3C":
				var replace = '<img class="tatterImageTriple" src="' + servicePath + adminSkin + '/image/spacer.gif" width="300" height="100" ' + longDesc + " />";
		}

		str = str.replaceAll(search, replace);
	}

	// iMazing 처리
	var regImazing = new RegExp("\\[##_iMazing\\|(.*?)_##\\]", "");
	while(result = regImazing.exec(str)) {
		var search = result[0];

		var longDesc = ' longdesc="iMazing|' + this.addQuot(this.htmlspecialchars(result[1])) + '" ';

		// Avoid the bug
		longDesc = longDesc.replaceAll("&lt;", "&amp;lt;");
		longDesc = longDesc.replaceAll("&gt;", "&amp;gt;");

		var imageAttr = this.parseImageSize(result[1], "string");

		var replace = '<img class="tatterImazing" src="' + servicePath + adminSkin + '/image/spacer.gif" ' + imageAttr + longDesc + " />";

		str = str.replaceAll(search, replace);
	}


	// Gallery 처리
	var regGallery = new RegExp("\\[##_Gallery\\|(.*?)_##\\]", "");
	while(result = regGallery.exec(str)) {
		var search = result[0];

		var longDesc = ' longdesc="Gallery|' + this.addQuot(this.htmlspecialchars(result[1])) + '" ';

		// Avoid the bug
		longDesc = longDesc.replaceAll("&lt;", "&amp;lt;");
		longDesc = longDesc.replaceAll("&gt;", "&amp;gt;");

		var imageAttr = this.parseImageSize(result[1], "string");

		var replace = '<img class="tatterGallery" src="' + servicePath + adminSkin + '/image/spacer.gif" ' + imageAttr + longDesc + " />";

		str = str.replaceAll(search, replace);
	}

	// Jukebox 처리
	var regJukebox = new RegExp("\\[##_Jukebox\\|(.*?)_##\\]", "");
	while(result = regJukebox.exec(str)) {
		var search = result[0];

		var longDesc = ' longdesc="Jukebox|' + this.addQuot(this.htmlspecialchars(result[1])) + '" ';

		// Avoid the bug
		longDesc = longDesc.replaceAll("&lt;", "&amp;lt;");
		longDesc = longDesc.replaceAll("&gt;", "&amp;gt;");

		var replace = '<img class="tatterJukebox" src="' + servicePath + adminSkin + '/image/spacer.gif" width="200" height="25"' + longDesc + " />";

		str = str.replaceAll(search, replace);
	}

	// 단일 이미지 치환자 처리
	var regImage = new RegExp("src=[\"']?(\\[##_ATTACH_PATH_##\\][a-z.0-9/]*)", "i");
	while(result = regImage.exec(str))
		str = str.replaceAll(result[0], 'class="tatterImageFree" longdesc="' + result[1] + '" src="' + this.propertyFilePath.substring(0, this.propertyFilePath.length - 1) + result[1].replaceAll("[##_ATTACH_PATH_##]", ""));

	// Object manipulation
	var objects = getTagChunks(str, "object");
	if ( objects.length > 0 ) {
		for(i in objects) {
			str = str.replaceAll(objects[i], '<img class="tatterObject" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(objects[i], "string", "css") + ' longDesc="' + this.objectSerialize(objects[i]) + '" />');
		}
	}
	// Flash manipulation
	var regEmbed = new RegExp("<embed([^<]*?)application/x-shockwave-flash(.*?)></embed>", "i");
	while(result = regEmbed.exec(str)) {
	    var body = result[0];
	    str = str.replaceAll(body, '<img class="tatterFlash" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(body, "string", "css") + ' longDesc="' + this.parseAttribute(body, "src") + '"/>');
	}

	// Embed manipulation
	var regEmbed = new RegExp("<embed([^<]*?)></embed>", "i");
	while(result = regEmbed.exec(str)) {
	    var body = result[0];
	    str = str.replaceAll(body, '<img class="tatterEmbed" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(body, "string", "css") + ' longDesc="' + this.parseAttribute(body, "src") + '"/>');
	}
	return str;
}

// IFRAME에 작성된 HTML을 태터툴즈 텍스트 에디터에서 볼 수 있는 TTML로 전환
// Convert HTML on designmode IFRAME to TTML format.
TTModernEditor.prototype.html2ttml = function() {
	var str = this.contentDocument.body.innerHTML;
	if (STD.isWebkit) {
		// Workaround for Webkit's misbehaviour (All closing non-html '>' are not converted to the entity '&gt;')
		// NOTE: This solution can't process cases like ">>>>".
		str = str.replace(new RegExp("(&lt;[^<]*?)>", "gi"), "$1&gt;");
		str = str.replaceAll('<div><br /></div>','<br />');
	}

	// more/less handling
	str = this.morelessConvert(str);
	
	// iframe 임시 저장 (빈 태그 제거시 영향 받지 않도록)Store iframe tag.
	str = str.replace(new RegExp("<iframe(\\w+)[^>]*></iframe>", "gi"), "<iframe$1>IFRAME</iframe>");

	// 빈 줄을 BR 태그로 변환 convert empty line to BR tag.
	str = str.replace(new RegExp("<p[^>]*?>&nbsp;</p>", "gi"), "<br />");

	// 빈 태그 제거 Remove empty tags
	str = str.replace(new RegExp("<(\\w+)[^>]*></\\1>", "gi"), "");

	// 쓸모없는 &nbsp; 제거 Remove useless &nbsp; tag.
	str = str.replace(new RegExp("([^> ])&nbsp;([^ ])", "gi"), "$1 $2");

	// 비어있는 a 태그 제거 Remove blank anchor tag.
	var regEmptyAnchor = new RegExp("<a>(((?!<a>).)*?)</a>", "i");
	while(result = regEmptyAnchor.exec(str))
		str = str.replaceAll(result[0], result[1]);

	// iframe Revert.
	str = str.replace(new RegExp("<iframe(\\w+)[^>]*>IFRAME</iframe>", "gi"), "<iframe$1></iframe>");

	// 이미지 치환자 처리
	var regImage = new RegExp("<img[^>]*?class=[\"']?tatterImage[^>]*?>", "i");
	while(result = regImage.exec(str)) {
	    var body = result[0];
	    var replace = this.parseAttribute(result[0], "longdesc");
	    if(replace && replace.indexOf("[##_ATTACH_PATH_##]") == -1)
		str = str.replaceAll(body, "[##_" + this.removeQuot(replace).replace(new RegExp("&amp;", "gi"), "&") + "_##]");
		else {
			var align = this.parseAttribute(body, "align").toLowerCase();
			if(align == "left" || align == "right" || align == "center")
				str = str.replaceAll(body, '<img src="' + replace + '"' + this.parseImageSize(body, "string") + 'align="' + align + '"/>');
			else
				str = str.replaceAll(body, '<img src="' + replace + '"' + this.parseImageSize(body, "string") + "/>");
		}
	}

	// iMazing 처리
	var regImaging = new RegExp("<img[^>]*class=[\"']?tatterImazing[^>]*>", "i");
	while(result = regImaging.exec(str)) {
	    var body = result[0];
	    var size = this.parseImageSize(body, "array");
	    var longdesc = this.parseAttribute(result[0], "longdesc");
	    longdesc = this.removeQuot(longdesc);
	    longdesc = longdesc.replace(new RegExp("(width=[\"']?)\\d*", "i"), "$1" + size[0]);
	    longdesc = longdesc.replace(new RegExp("(height=[\"']?)\\d*", "i"), "$1" + size[1]);
	    str = str.replaceAll(body, "[##_" + longdesc.replace(new RegExp("&amp;", "gi"), "&") + "_##]");
	}

	// Gallery 처리
	var regGallery = new RegExp("<img[^>]*class=[\"']?tatterGallery[^>]*>", "i");
	while(result = regGallery.exec(str)) {
		var body = result[0];

		var size = this.parseImageSize(body, "array");

		var longdesc = this.parseAttribute(result[0], "longdesc");
		longdesc = this.removeQuot(longdesc);
		longdesc = longdesc.replace(new RegExp("(width=[\"']?)\\d*", "i"), "$1" + size[0]);
		longdesc = longdesc.replace(new RegExp("(height=[\"']?)\\d*", "i"), "$1" + size[1]);
		longdesc = longdesc.split("|");

		// TT 1.0 alpha ~ 1.0.1까지 쓰던 Gallery 치환자를 위한 코드 Legacy code for TT 1.0 alpha to 1.0.1
		if(longdesc.length % 2 == 1)
			longdesc.length--;

		var files = "";

		for(var i=1; i<longdesc.length-1; i++)
			files += longdesc[i].replace(new RegExp("&amp;", "gi"), "&") + "|";

		str = str.replaceAll(body, "[##_Gallery|" + files + this.unHtmlspecialchars(trim(longdesc[longdesc.length-1])) + "_##]");
	}

	// Jukebox 처리 Jukebox handling
	var regJukebox = new RegExp("<img[^>]*class=[\"']?tatterJukebox[^>]*>", "i");
	while(result = regJukebox.exec(str)) {
		var body = result[0];

		var size = this.parseImageSize(body, "array");

		var longdesc = this.parseAttribute(result[0], "longdesc");
		longdesc = this.removeQuot(longdesc);
		longdesc = longdesc.replace(new RegExp("(width=[\"']?)\\d*", "i"), "$1" + size[0]);
		longdesc = longdesc.replace(new RegExp("(height=[\"']?)\\d*", "i"), "$1" + size[1]);

		longdesc = longdesc.split("|");

		var files = "";

		for(var i=1; i<longdesc.length-2; i++)
			files += longdesc[i].replace(new RegExp("&amp;", "gi"), "&") + "|";

		str = str.replaceAll(body, "[##_Jukebox|" + files + this.unHtmlspecialchars(trim(longdesc[longdesc.length-2])) + "|_##]");
	}

	// Object 처리
	var regObject = new RegExp("<img[^>]*class=[\"']?tatterObject.*?>", "i");
	while(result = regObject.exec(str)) {
		var body = result[0];
		var object = this.objectUnSerialize(this.parseAttribute(body, "longdesc"));
		var widthString = new RegExp("width=[\"']?\\w+[\"']?","i").exec(object);
		var heightString = new RegExp("height=[\"']?\\w+[\"']?","i").exec(object);
		var size = this.parseImageSize(body, "array");
		if(widthString)
			object = object.replaceAll(widthString[0], 'width="' + size[0] + '"');
		if(heightString)
			object = object.replaceAll(heightString[0], 'height="' + size[1] + '"');
		str = str.replaceAll(body, object);
	}

	// Embed 처리
	var regEmbed = new RegExp("<img[^>]*class=[\"']?tatterEmbed.*?>", "i");
	while(result = regEmbed.exec(str)) {
		var body = result[0];
		str = str.replaceAll(body, "<embed autostart=\"0\" src=\"" + this.parseAttribute(body, "longdesc") + "\"" + this.parseImageSize(body, "string", "css") + "></embed>");
	}

	// Flash 처리
	var regFlash = new RegExp("<img[^>]*class=[\"']?tatterFlash.*?>", "i");
	while(result = regFlash.exec(str)) {
		var body = result[0];
		str = str.replaceAll(body, '<embed loop="true" menu="false" quality="high" ' + this.parseImageSize(body, "string") + ' type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" src="' + this.parseAttribute(body, "longdesc") + '"></embed>');
	}
	return str;
}

TTModernEditor.prototype.morelessConvert = function(string) {
	while(new RegExp("<div[^>]*?class=['\"]?tattermoreless[^>]*>", "i").test(string))
		string = this.morelessConvert_process(string);
	return string;
}

TTModernEditor.prototype.morelessConvert_process = function(string) {
	var result = "";
	var pos1 = pos2 = 0;
	var head = new RegExp("<div[^>]*?class=['\"]?tattermoreless[^>]*>", "i");
	var chunk = undefined;
	if((pos1 = string.indexOfCaseInsensitive(head, pos2)) > -1) {
		result += string.substring(0, pos1);
		do {
			if((pos2 = string.indexOfCaseInsensitive(new RegExp("</div>", "i"), Math.max(pos1, pos2))) == -1)
				return result + string.substring(pos1, string.length).replace(head,'');
			pos2 += 6;
			chunk = string.substring(pos1, pos2);
		} while(chunk != "" && chunk.count(new RegExp("<div[>\\s]", "gi")) != chunk.count(new RegExp("</div>", "gi")));
		var less = this.parseAttribute(chunk, "less").replaceAll("&amp;", "&");
		var more = this.parseAttribute(chunk, "more").replaceAll("&amp;", "&");
		chunk = chunk.replace(head, "[#M_" + more + "|" + less + "|");
		chunk = chunk.replace(new RegExp("</div>$", "i"), "_M#]");
		result += chunk;
	}
	return result + string.substring(pos2, string.length);
}

// 위지윅 모드에서 치환자 이미지를 클릭했을때 편집창 옆에 속성창을 보여준다
// true를 반환할 경우 해당 obj는 하나의 오브젝트(이미지 등)로 처리해야 함을 의미
// Show property window when click images at WYGIWYG mode.
TTModernEditor.prototype.showProperty = function(obj)
{
	this.selectedAnchorElement = null;
	this.selection = this.getSelectionRange();

	var attribute = obj.getAttribute("longdesc");

	getObject(this.id + "textBox").style.display = "none";
	getObject(this.id + "colorPalette").style.display = "none";
	getObject(this.id + "markPalette").style.display = "none";
	getObject(this.id + "propertyImage1").style.display = "none";
	getObject(this.id + "propertyImage2").style.display = "none";
	getObject(this.id + "propertyImage3").style.display = "none";
	getObject(this.id + "propertyObject").style.display = "none";
	getObject(this.id + "propertyObject1").style.display = "none";
	getObject(this.id + "propertyObject2").style.display = "none";
	getObject(this.id + "propertyObject3").style.display = "none";
	getObject(this.id + "propertyiMazing").style.display = "none";
	getObject(this.id + "propertyiMazing_preview").style.display = "none";
	getObject(this.id + "propertyGallery").style.display = "none";
	getObject(this.id + "propertyGallery_preview").style.display = "none";
	getObject(this.id + "propertyJukebox").style.display = "none";
	getObject(this.id + "propertyEmbed").style.display = "none";
	//getObject(this.id + "propertyFlash").style.display = "none";
	getObject(this.id + "propertyMoreLess").style.display = "none";
	this.changeButtonStatus(null, null);

	if(obj.className == "tatterObject") {
		this.propertyHeader = "tatterObject";
		this.propertyWindowId = this.id + "propertyObject";
		var size = this.parseImageSize(this.selectedElement, "array");
		getObject(this.id + "propertyObject_width").value = size[0];
		getObject(this.id + "propertyObject_height").value = size[1];
		getObject(this.id + "propertyObject_chunk").value = this.objectUnSerialize(attribute);
		getObject(this.id + "propertyInsertObject").style.display = "none";
		getObject(this.id + "propertyHyperLink").style.display = "none";
		getObject(this.id + "propertyObject").style.display = "block";
	}
	else if(obj.className == "tatterEmbed") {
		this.propertyHeader = "tatterEmbed";
		this.propertyWindowId = this.id + "propertyEmbed";
		var size = this.parseImageSize(this.selectedElement, "array");
		getObject(this.id + "propertyEmbed_width").value = size[0];
		getObject(this.id + "propertyEmbed_height").value = size[1];
		getObject(this.id + "propertyEmbed_src").value = attribute;
		getObject(this.id + "propertyEmbed").style.display = "block";
	}
	else if(obj.tagName && obj.tagName.toLowerCase() == "img" && attribute) {
		var values = attribute.split("|");

		if(values.length == 1)
			return false;

		this.propertyHeader = values[0];

		if(values[0] == "iMazing" || values[0] == "Gallery" || values[0] == "Jukebox") {
			var objectCount = 1;
			var objectType = values[0];
			var propertyWindowId = this.id + "property" + objectType;
		}
		else {
			var objectCount = values[0].charAt(0);
			var objectType = this.isImageFile(values[1]) ? "Image" : "Object";
			var propertyWindowId = this.id + "property" + objectType + objectCount;
		}

		this.propertyWindowId = propertyWindowId;

		if(objectType == "Image") {
			getObject(propertyWindowId + "_width1").value = trim(this.removeQuot(this.parseAttribute(values[2], "width")));
			getObject(propertyWindowId + "_alt1").value = trim(this.unHtmlspecialchars(this.removeQuot(this.parseAttribute(values[2], "alt"))));
			getObject(propertyWindowId + "_caption1").value = trim(this.unHtmlspecialchars(this.removeQuot(values[3])));

			this.propertyFilename1 = values[1];

			// 1번 이미지.
			if(objectCount == 1) {
				var size = this.parseImageSize(this.selectedElement, "array");

				if(this.propertyCurrentImage == this.selectedElement.getAttribute("src")) {
					var newWidth = size[0];
					var newHeight = parseInt(size[0] * this.propertyCurrentProportion1);
					this.propertyCurrentProportion1 = newHeight / newWidth;
					this.selectedElement.removeAttribute("width");
					this.selectedElement.removeAttribute("height");
					if (!isNaN(newWidth))
						this.selectedElement.style.width = newWidth + "px";
					if (!isNaN(newHeight))
						this.selectedElement.style.height = newHeight + "px";
				}
				else {
					this.propertyCurrentProportion1 = size[1] / size[0];
					this.propertyCurrentImage = this.selectedElement.getAttribute("src");
				}
			}
			else {
				var size = this.parseImageSize(values[2], "array");
				this.propertyCurrentProportion1 = size[1] / size[0];
				if(objectCount > 1) {
					var size = this.parseImageSize(values[5], "array");
					this.propertyCurrentProportion2 = size[1] / size[0];
				}
				if(objectCount > 2) {
					var size = this.parseImageSize(values[8], "array");
					this.propertyCurrentProportion3 = size[1] / size[0];
				}
			}

			// 2번 이미지.
			if(objectCount > 1) {
				getObject(propertyWindowId + "_width2").value = trim(this.removeQuot(this.parseAttribute(values[5], "width")));
				getObject(propertyWindowId + "_alt2").value = trim(this.unHtmlspecialchars(this.removeQuot(this.parseAttribute(values[5], "alt"))));
				getObject(propertyWindowId + "_caption2").value = trim(this.unHtmlspecialchars(this.removeQuot(values[6])));
			}

			this.propertyFilename2 = values[4];

			// 3번 이미지.
			if(objectCount > 2) {
				getObject(propertyWindowId + "_width3").value = trim(this.removeQuot(this.parseAttribute(values[8], "width")));
				getObject(propertyWindowId + "_alt3").value = trim(this.unHtmlspecialchars(this.removeQuot(this.parseAttribute(values[8], "alt"))));
				getObject(propertyWindowId + "_caption3").value = trim(this.unHtmlspecialchars(this.removeQuot(values[9])));
			}

			this.propertyFilename3 = values[7];
		}
		else if(objectType == "Object") {
			getObject(propertyWindowId + "_caption1").value = trim(this.unHtmlspecialchars(this.removeQuot(values[3])));
			getObject(propertyWindowId + "_filename1").value = this.getFilenameFromFilelist(values[1]);
			this.propertyFilename1 = values[1];
			if(objectCount > 1) {
				getObject(propertyWindowId + "_caption2").value = trim(this.unHtmlspecialchars(this.removeQuot(values[6])));
				getObject(propertyWindowId + "_filename2").value = this.getFilenameFromFilelist(values[4]);
				this.propertyFilename2 = values[4];
			}

			if(objectCount > 2) {
				getObject(propertyWindowId + "_caption3").value = trim(this.unHtmlspecialchars(this.removeQuot(values[9])));
				getObject(propertyWindowId + "_filename3").value = this.getFilenameFromFilelist(values[7]);
				this.propertyFilename3 = values[7];
			}
		}
		else if(objectType == "iMazing") {
			var size = this.parseImageSize(this.selectedElement, "array");
			var attributes = values[values.length-2];

			getObject(propertyWindowId + "_width").value = size[0];
			getObject(propertyWindowId + "_height").value = size[1];
			getObject(propertyWindowId + "_frame").value = this.parseAttribute(attributes, "frame");
			getObject(propertyWindowId + "_tran").value = this.parseAttribute(attributes, "transition");
			getObject(propertyWindowId + "_nav").value = this.parseAttribute(attributes, "navigation");
			getObject(propertyWindowId + "_sshow").value = this.parseAttribute(attributes, "slideshowInterval");
			getObject(propertyWindowId + "_page").value = this.parseAttribute(attributes, "page");
			getObject(propertyWindowId + "_align").value = this.parseAttribute(attributes, "align");
			getObject(propertyWindowId + "_caption").value = trim(this.unHtmlspecialchars(this.removeQuot(values[values.length-1])));

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2)
				list.options[list.length] = new Option(this.getFilenameFromFilelist(values[i]), values[i] + "|", false, false);
		}
		else if(objectType == "Gallery") {
			var size = this.parseImageSize(this.selectedElement, "array");
			var attributes = values[values.length-2];

			getObject(propertyWindowId + "_width").value = size[0];
			getObject(propertyWindowId + "_height").value = size[1];
			getObject(propertyWindowId + "_caption").value = "";

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2) {
				list.options[list.length] = new Option(this.getFilenameFromFilelist(values[i]), values[i] + "|" + this.unHtmlspecialchars(values[i+1]), false, false);
				if (i == 1) {
					list.selectedIndex = 0;
					this.listChanged('propertyGallery_list');
				}
			}
		}
		else if(objectType == "Jukebox") {
			getObject(propertyWindowId + "_autoplay").checked = this.parseAttribute(values[values.length-2], "autoplay") == 1;
			getObject(propertyWindowId + "_visibility").checked = this.parseAttribute(values[values.length-2], "visible") == 1;

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2)
				list.options[list.length] = new Option(this.getFilenameFromFilelist(values[i]), values[i] + "|" + this.unHtmlspecialchars(values[i+1]), false, false);
		}

		getObject(propertyWindowId).style.display = "block";
	} else {
		var node = obj;

		while(node.parentNode) {
			if(node.tagName && node.tagName.toLowerCase() == "div" && node.getAttribute("more") != null && node.getAttribute("less") != null) {
				var moreText = node.getAttribute("more");
				var lessText = node.getAttribute("less");
				getObject(this.id + "propertyInsertObject").style.display = "none";
				getObject(this.id + "propertyHyperLink").style.display = "none";
				getObject(this.id + "propertyMoreLess").style.display = "block";
				getObject(this.id + "propertyMoreLess_more").value = trim(this.unHtmlspecialchars(moreText));
				getObject(this.id + "propertyMoreLess_less").value = trim(this.unHtmlspecialchars(lessText));
				this.propertyWindowId = this.id + "propertyMoreLess";
				getObject(this.id + "propertyHyperLink").style.display = "none";
				this.setPropertyPosition();
				return false;
			} else if(node.tagName.toLowerCase() == "a" && node.href) {
				getObject(this.id + "propertyHyperLink").style.display = "block";
				getObject(this.id + "propertyHyperLink_url").value = node.href;
				getObject(this.id + "propertyHyperLink_target").value = node.target;
				if(getObject(this.id + "propertyHyperLink_target").selectedIndex == -1)
					getObject(this.id + "propertyHyperLink_target").value = "_self";
				this.selectedAnchorElement = node;
				this.propertyWindowId = this.id + "propertyHyperLink";
				getObject(this.id + "propertyMoreLess").style.display = "none";
				getObject(this.id + "propertyInsertObject").style.display = "none";
				getObject(this.id + "propertyObject").style.display = "none";
				this.setPropertyPosition();
				return false;
			}

			node = node.parentNode;
		}

		if(STD.isIE)
			var isEmpty = (this.getSelectionRange().htmlText == "");
		else
			var isEmpty = (this.getSelectionRange().startOffset == this.getSelectionRange().endOffset);

		if (this.selectedAnchorElement == null && isEmpty)
			getObject(this.id + "propertyHyperLink").style.display = "none";
		return false;
	}
	this.setPropertyPosition();
	return true;
}

// 속성창에서 수정된 내용을 반영
TTModernEditor.prototype.setProperty = function()
{
	var attribute = this.selectedElement.getAttribute("longdesc");

	if(this.selectedElement.className == "tatterObject" || this.selectedElement.className == "tatterEmbed" || this.selectedElement.className == "tatterFlash") {
		this.selectedElement.removeAttribute("width");
		this.selectedElement.removeAttribute("height");
		this.selectedElement.style.width = "auto";
		this.selectedElement.style.height = "auto";

		try {
			var width = parseInt(getObject(this.propertyWindowId + "_width").value);
			if(!isNaN(width) && width > 0 && width < 10000)
				this.selectedElement.style.width = width + "px";
			var height = parseInt(getObject(this.propertyWindowId + "_height").value);
			if(!isNaN(height) && height > 0 && height < 10000)
				this.selectedElement.style.height = height + "px";
		} catch(e) { }

		if(this.selectedElement.className == "tatterEmbed" || this.selectedElement.className == "tatterFlash")
			this.selectedElement.setAttribute("longDesc", getObject(this.propertyWindowId + "_src").value);
		else {
			this.selectedElement.setAttribute("longDesc", this.objectSerialize(getObject(this.propertyWindowId + "_chunk").value));
		}
	}
	else if(this.selectedElement.tagName && this.selectedElement.tagName.toLowerCase() == "img" && attribute) {
		if(this.propertyWindowId.indexOf(this.id + "propertyImage") == 0) {
			var objectCount = this.propertyWindowId.charAt(this.propertyWindowId.length-1);

			// 1L,1C,1R일 경우에는 수정된 속성의 크기로 실제 이미지 크기를 변경
			// 1번 이미지.
			if(objectCount == 1) {
				this.selectedElement.removeAttribute("width");
				this.selectedElement.removeAttribute("height");
				this.selectedElement.style.width = "auto";
				this.selectedElement.style.height = "auto";

				try {
					var value = parseInt(getObject(this.propertyWindowId + "_width1").value);
					if(!isNaN(value) && value > 0 && value < 10000) {
						var newWidth = value;
						var newHeight = parseInt(value * this.propertyCurrentProportion1);
						this.selectedElement.style.width = newWidth + "px";
						this.selectedElement.style.height = newHeight + "px";
					}
				} catch(e) { }
			}

			var imageSize = "";
			var imageAlt = "";
			var imageCaption = "";
			var imageResample = "";

			try {
				var value = parseInt(getObject(this.propertyWindowId + "_width1").value);
				if(!isNaN(value) && value > 0 && value < 10000)
					imageSize = 'width="' + value + '" height="' + parseInt(value * this.propertyCurrentProportion1) + '" ';
			} catch(e) { }
			try {
				if(this.isImageFile(this.propertyFilename1))
					imageAlt = 'alt="' + this.htmlspecialchars(getObject(this.propertyWindowId + "_alt1").value) + '"';
			} catch(e) { imageAlt = 'alt=""'; }
			try {
				imageCaption = this.htmlspecialchars(getObject(this.propertyWindowId + "_caption1").value);
			} catch(e) { imageCaption = ''; }

			var longdesc = this.propertyHeader + '|' + this.propertyFilename1 + '|' + imageSize + imageAlt + '|' + imageCaption;

			// 2번 이미지.
			if(objectCount > 1) {
				imageSize = "";
				imageAlt = "";
				imageCaption = "";

				try {
					var value = parseInt(getObject(this.propertyWindowId + "_width2").value);
					if(!isNaN(value) && value > 0 && value < 10000)
						imageSize = 'width="' + value + '" height="' + parseInt(value * this.propertyCurrentProportion2) + '" ';;
				} catch(e) { }
				try {
					if(this.isImageFile(this.propertyFilename2))
						imageAlt = 'alt="' + this.htmlspecialchars(getObject(this.propertyWindowId + "_alt2").value) + '"';
				} catch(e) { imageAlt = 'alt = ""'; }
				try {
					imageCaption = this.htmlspecialchars(getObject(this.propertyWindowId + "_caption2").value);
				} catch(e) { imageCaption = ''; }

				longdesc += '|' + this.propertyFilename2 + '|' + imageSize + imageAlt + '|' + imageCaption;
			}

			// 3번 이미지.
			if(objectCount > 2) {
				imageSize = "";
				imageAlt = "";
				imageCaption = "";

				try {
					var value = parseInt(getObject(this.propertyWindowId + "_width3").value);
					if(!isNaN(value) && value > 0 && value < 10000)
						imageSize = 'width="' + value + '" height="' + parseInt(value * this.propertyCurrentProportion3) + '" ';
				} catch(e) { }
				try {
					if(this.isImageFile(this.propertyFilename3))
						imageAlt = 'alt="' + this.htmlspecialchars(getObject(this.propertyWindowId + "_alt3").value) + '"';
				} catch(e) { imageAlt = 'alt = ""'; }
				try {
					imageCaption = this.htmlspecialchars(getObject(this.propertyWindowId + "_caption3").value);
				} catch(e) { imageCaption = ''; }

				longdesc += '|' + this.propertyFilename3 + '|' + imageSize + imageAlt + '|' + imageCaption;
			}

			this.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(this.propertyWindowId.indexOf(this.id + "propertyObject") == 0) {
			var objectCount = this.propertyWindowId.charAt(this.propertyWindowId.length-1);

			var longdesc = this.propertyHeader + '|' + this.propertyFilename1 + '||' + this.htmlspecialchars(getObject(this.propertyWindowId + "_caption1").value);

			if(objectCount > 1)
				longdesc += '|' + this.propertyFilename2 + '||' + this.htmlspecialchars(getObject(this.propertyWindowId + "_caption2").value);

			if(objectCount > 2)
				longdesc += '|' + this.propertyFilename3 + '||' + this.htmlspecialchars(getObject(this.propertyWindowId + "_caption3").value);

			this.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(this.propertyWindowId.indexOf(this.id + "propertyiMazing") == 0) {
			var list = getObject(this.id + "propertyiMazing_list");
			var longdesc = "iMazing|";

			for(var i=0; i<list.length; i++)
				longdesc += list[i].value.substring(0, list[i].value.indexOf("|")) + "||";

			this.selectedElement.removeAttribute("width");
			this.selectedElement.removeAttribute("height");
			this.selectedElement.style.width = "auto";
			this.selectedElement.style.height = "auto";

			var size = "";

			var width = parseInt(getObject(this.id + "propertyiMazing_width").value);
			if(!isNaN(width) && width > 0 && width < 10000) {
				this.selectedElement.style.width = width + "px";
				size = 'width="' + width + '" ';
			}

			var height = parseInt(getObject(this.id + "propertyiMazing_height").value);
			if(!isNaN(height) && height > 0 && height < 10000) {
				this.selectedElement.style.height = height + "px";
				size += 'height="' + height + '"';
			}

			if(isNaN(width) && isNaN(height)) {
				this.selectedElement.style.width = this.selectedElement.style.height = 100 + "px";
				size = 'width="100" height="100"';
			}

			longdesc += size;
			longdesc += ' frame="' + getObject(this.id + "propertyiMazing_frame").value + '"';
			longdesc += ' transition="' + getObject(this.id + "propertyiMazing_tran").value + '"';
			longdesc += ' navigation="' + getObject(this.id + "propertyiMazing_nav").value + '"';
			longdesc += ' slideshowInterval="' + getObject(this.id + "propertyiMazing_sshow").value + '"';
			longdesc += ' page="' + getObject(this.id + "propertyiMazing_page").value + '"';
			longdesc += ' align="' + getObject(this.id + "propertyiMazing_align").value + '"';
			longdesc += ' skinPath="' + servicePath + '/script/gallery/iMazing/"';
			longdesc += "|" + this.htmlspecialchars(getObject(this.id + "propertyiMazing_caption").value);

			this.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(this.propertyWindowId.indexOf(this.id + "propertyGallery") == 0) {
			var list = getObject(this.id + "propertyGallery_list");
			var longdesc = "Gallery|";

			if(list.selectedIndex != -1) {
				var caption = getObject(this.id + "propertyGallery_caption").value.replaceAll("|", "");
				var tmp = list[list.selectedIndex].value.split("|");
				list[list.selectedIndex].value = tmp[0] + "|" + caption;
			}

			for(var i=0; i<list.length; i++)
				longdesc += this.htmlspecialchars(list[i].value) + "|";

			this.selectedElement.removeAttribute("width");
			this.selectedElement.removeAttribute("height");
			this.selectedElement.style.width = "auto";
			this.selectedElement.style.height = "auto";

			var size = "";

			var width = parseInt(getObject(this.id + "propertyGallery_width").value);
			if(!isNaN(width) && width > 0 && width < 10000) {
				this.selectedElement.style.width = width + "px";
				size = 'width="' + width + '" ';
			}

			var height = parseInt(getObject(this.id + "propertyGallery_height").value);
			if(!isNaN(height) && height > 0 && height < 10000) {
				this.selectedElement.style.height = height + "px";
				size += 'height="' + height + '"';
			}

			if(isNaN(width) && isNaN(height)) {
				this.selectedElement.style.width = this.selectedElement.style.height = 100 + "px";
				size = 'width=100 height=100';
			}

			longdesc += trim(size) + "|";

			this.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(this.propertyWindowId.indexOf(this.id + "propertyJukebox") == 0) {
			var list = getObject(this.id + "propertyJukebox_list");
			var longdesc = "Jukebox|";

			if(list.selectedIndex != -1) {
				var title = getObject(this.id + "propertyJukebox_title").value.replaceAll("|", "");
				var tmp = list[list.selectedIndex].value.split("|");
				list[list.selectedIndex].value = tmp[0] + "|" + title;
			}

			for(var i=0; i<list.length; i++)
				longdesc += list[i].value + "|";

			longdesc += "autoplay=" + (getObject(this.id + "propertyJukebox_autoplay").checked ? 1 : 0);
			longdesc += " visible=" + (getObject(this.id + "propertyJukebox_visibility").checked ? 1 : 0);

			this.selectedElement.setAttribute("longDesc", longdesc + "|");
		}
	}
	else if(this.selectedElement.tagName && this.selectedElement.tagName.toLowerCase() == "div" && this.selectedElement.getAttribute("more") != null && this.selectedElement.getAttribute("less") != null) {
		this.selectedElement.setAttribute("more", this.htmlspecialchars(getObject(this.id + "propertyMoreLess_more").value));
		this.selectedElement.setAttribute("less", this.htmlspecialchars(getObject(this.id + "propertyMoreLess_less").value));
	}
}

TTModernEditor.prototype.command = function(command, value1, value2) {
	var isWYSIWYG = false;

	try {
		if(this.editMode == "WYSIWYG")
			isWYSIWYG = true;
	} catch(e) { }

	switch(command) {
		case "ToggleMode":
			try {
				this.toggleMode();
				this.trimContent();
			} catch(e) { }
			break;
		case "Bold":
			if(isWYSIWYG) {
				this.execCommand("Bold", false, null);
				this.activeButton();
			}
			else
				insertTag(this.textarea, "<strong>", "</strong>");
			break;
		case "Italic":
			if(isWYSIWYG) {
				this.execCommand("Italic", false, null);
				this.activeButton();
			}
			else
				insertTag(this.textarea, "<em>", "</em>");
			break;
		case "Underline":
			if(isWYSIWYG) {
				this.execCommand("Underline", false, null);
				this.activeButton();
			}
			else
				insertTag(this.textarea, "<ins>", "</ins>");
			break;
		case "StrikeThrough":
			if(isWYSIWYG) {
				this.execCommand("StrikeThrough", false, null);
				this.activeButton();
			}
			else
				insertTag(this.textarea, "<del>", "</del>");
			break;
		case "FontSize":
			if(value1.substring(0, 1) == "h") {
				if(STD.isIE) {
					if(this.getSelectionRange().htmlText == "")
						this.execCommand("FormatBlock", false, "<" + value1 + ">");
					else
						this.command("Raw", "<" + value1 + ">", "</" + value1 + ">");
				}
				else
					this.execCommand("FormatBlock", false, value1);
			}
			else
				this.execCommand("FontSize", false, value1);
			break;
		case "Color":
			if(isWYSIWYG)
				this.execCommand("ForeColor", false, value1);
			else
				this.command("Raw", '<span style="color: ' + value1 + '">', "</span>");
			break;
		case "Mark":
			if(isWYSIWYG) {
				if(STD.isIE) {
					this.execCommand("BackColor", false, value1);
				} else {
					this.execCommand("HiliteColor", false, value1);
				}
			} else {
				this.command("Raw", '<span style="background-color: ' + value1 + '">', "</span>");
			}
			break;
		case "RemoveFormat":
			if(isWYSIWYG) {
				if(STD.isIE) {
					if(this.getSelectionRange().htmlText != "") {
						if(this.getSelectionRange().parentElement().outerHTML == this.getSelectionRange().htmlText)
							this.getSelectionRange().parentElement().outerHTML = this.removeFormatting(this.getSelectionRange().htmlText);
						else
							this.getSelectionRange().pasteHTML(this.removeFormatting(this.getSelectionRange().htmlText));
					}
				}
				else {
					if(this.getSelectionRange().startOffset != this.getSelectionRange().endOffset) {
						var range = this.getSelectionRange();
						var dummyNode = document.createElement("div");
						dummyNode.appendChild(range.extractContents());
						range.insertNode(range.createContextualFragment(this.removeFormatting(dummyNode.innerHTML)));
					}
				}
			}
			break;
		case "JustifyLeft":
			blockAlign = "left";
		case "JustifyCenter":
			if(typeof blockAlign == "undefined")
				blockAlign = "center";
		case "JustifyRight":
			if(typeof blockAlign == "undefined")
				blockAlign = "right";
			if(isWYSIWYG) {
				if(STD.isIE) {
					if(this.selectedElement && this.selectedElement.tagName == "IMG") {
						switch(this.selectedElement.className) {
							default:
								this.execCommand("Justify" + blockAlign, false, null);
								break;
							case "":
							case "tatterImageFree":
								var img = this.selectedElement;
								img.removeAttribute("align");
								if(blockAlign == "center")
									this.execCommand("Justify" + blockAlign, false, null);
								else {
									img.setAttribute("align", blockAlign);
									var container = this.selectedElement.parentNode;
									if(container && (container.tagName == "P" || container.tagName == "DIV") && container.childNodes.length == 1 && container.parentNode)
										container.parentNode.replaceChild(img, container);
								}
						}
					}
					else if(this.getSelectionRange().htmlText) {
						var div = document.createElement("div");
						div.innerHTML = this.getSelectionRange().htmlText;
						if(div.childNodes.length == 1 && (div.childNodes[0].tagName == "P" || div.childNodes[0].tagName == "DIV")) {
							div.childNodes[0].style.textAlign = blockAlign;
							this.getSelectionRange().parentElement().outerHTML = div.innerHTML;
						}
						else {
							var parent = this.getSelectionRange().parentElement();
							if(parent && parent.tagName != "BODY" && parent.innerHTML == this.getSelectionRange().htmlText)
								parent.style.textAlign = blockAlign;
							else {
								for(var i=0; i<parent.childNodes.length; i++) {
									if(parent.childNodes[i].tagName == "P" || parent.childNodes[i].tagName == "DIV") {
										parent.childNodes[i].removeAttribute("align");
										parent.childNodes[i].style.textAlign = "";
									}
								}
								this.getSelectionRange().pasteHTML('<div style="text-align: ' + blockAlign + '">' + this.getSelectionRange().htmlText + "</div>");
							}
						}
					}
					else {
						var container = this.getSelectionRange().parentElement();
						if(container && (container.tagName == "P" || container.tagName == "DIV") && container.childNodes.length == 1)
							container.style.textAlign = blockAlign;
						else {
							// TODO : FF처럼 현재 커서있는 줄을 정렬
							delete blockAlign;
							return;
						}
					}
				}
				else
					this.execCommand("Justify" + blockAlign, false, null);
			}
			else
				insertTag(this.textarea, '<div style="text-align: ' + blockAlign + '">', "</div>");
			delete blockAlign;
			this.trimContent();
			break;
		case "InsertUnorderedList":
			if(isWYSIWYG) {
				if(STD.isIE)
					var isEmpty = this.getSelectionRange().htmlText == "";
				else
					var isEmpty = this.getSelectionRange().startOffset == this.getSelectionRange().endOffset;

				if(isEmpty)
					this.execCommand("InsertUnorderedList", false, null);
				else {
					try { var node = this.activeButton(this.getSelectionRange().parentElement()); }
					catch(e) {
						try { var node = this.activeButton(this.getSelectionRange().commonAncestorContainer.parentNode); }
						catch(e) { }
					}

					if(node && new RegExp("^[UO]L$", "i").test(node.tagName)) {
						if(STD.isIE)
							;
						else
							;
					}
					else {
						if(STD.isIE) {
							this.getSelectionRange().pasteHTML("<ul>\n<li>" + this.getSelectionRange().htmlText.replace(new RegExp("<br />", "gi"), "</li>\n<li>") + "</li>\n</ul>");
						}
						else {
							var range = this.getSelectionRange();
							var dummyNode = document.createElement("div");
							dummyNode.appendChild(range.extractContents());
							var html = dummyNode.innerHTML.replace(new RegExp("<br \/>", "gi"), "<\/li><li>");
							range.insertNode(range.createContextualFragment("<ul><li>" + html + "<\/li><\/ul>"));
						}
					}
				}
				this.trimContent();
			}
			else
				insertTag(this.textarea, "<ul><li>", "</li></ul>");
			break;
		case "InsertOrderedList":
			if(isWYSIWYG) {
				if(STD.isIE)
					var isEmpty = this.getSelectionRange().htmlText == "";
				else
					var isEmpty = this.getSelectionRange().startOffset == this.getSelectionRange().endOffset;

				if(isEmpty)
					this.execCommand("InsertOrderedList", false, null);
				else {
					try { var node = this.activeButton(this.getSelectionRange().parentElement()); }
					catch(e) {
						try { var node = this.activeButton(this.getSelectionRange().commonAncestorContainer.parentNode); }
						catch(e) { }
					}

					if(node && new RegExp("^[UO]L$", "").test(node.tagName)) {
						if(STD.isIE)
							;
						else
							;
					}
					else {
						if(STD.isIE) {
							this.getSelectionRange().pasteHTML("<ol><li>" + this.getSelectionRange().htmlText.replace(new RegExp("<br />", "gi"), "</li><li>") + "</li></ol>");
						}
						else {
							var range = this.getSelectionRange();
							var dummyNode = document.createElement("div");
							dummyNode.appendChild(range.extractContents());
							var html = dummyNode.innerHTML.replace(new RegExp("<br />", "gi"), "<\/li><li>");
							range.insertNode(range.createContextualFragment("<ol><li>" + html + "<\/li><\/ol>"));
						}
					}
				}
				this.trimContent();
			}
			else
				insertTag(this.textarea, "<ol><li>", "</li></ol>");
			break;
		case "Indent":
			if(isWYSIWYG) {
				this.execCommand("Indent", false, null);
				this.trimContent();
			}
			break;
		case "Outdent":
			if(isWYSIWYG) {
				this.execCommand("Outdent", false, null);
				this.trimContent();
			}
			break;
		case "Blockquote":
			this.command("Raw", "<blockquote>", "</blockquote>");
			this.trimContent();
			break;
		case "Box":
			if(isWYSIWYG && !STD.isIE) {
				if(this.selection == null || this.selection.startOffset == this.selection.endOffset) {
					alert(s_selectBoxArea);
					return;
				}
			}
			this.command("Raw", '<div style="' + value1 + '">', "</div>");
			this.trimContent();
			break;
		case "CreateLink":
			if(!isWYSIWYG) {
				this.command("Raw", '<a href="">', "</a>");
				return;
			}
			if(STD.isIE) {
				if(this.selection == null || this.selection.htmlText == "") {
					alert(s_selectLinkArea);
					return;
				}
			}
			else {
				if(this.selection == null || this.selection.startOffset == this.selection.endOffset) {
					alert(s_selectLinkArea);
					return;
				}
			}
			this.propertyWindowId = this.id + "propertyHyperLink";
			getObject(this.id + "propertyMoreLess").style.display = "none";
			getObject(this.id + "propertyInsertObject").style.display = "none";
			getObject(this.id + "propertyObject").style.display = "none";
			getObject(this.id + "propertyHyperLink").style.display = "block";
			getObject(this.id + "propertyHyperLink_url").value = "";
			getObject(this.id + "propertyHyperLink_target").selectedIndex = 0;
			break;
		case "ExcuteCreateLink":
			var url = getObject(this.id + "propertyHyperLink_url").value.trim();
			var target = getObject(this.id + "propertyHyperLink_target").value;
			if(url == "") {
				alert(s_enterURL);
				return;
			}
			if(this.selectedAnchorElement) {
				this.selectedAnchorElement.href = url;
				if(target == "_self")
					this.selectedAnchorElement.removeAttribute("target");
				else
					this.selectedAnchorElement.target = target;
				getObject(this.id + "propertyHyperLink").style.display = "none";
				if(STD.isIE)
					this.selection.select();
			}
			else {
				if(STD.isIE) {
					if(!this.selection.htmlText || this.selection.htmlText == "") {
						alert(s_selectLinkArea);
						return;
					}
				}
				else {
					if(this.selection.startOffset == this.selection.endOffset) {
						alert(s_selectLinkArea);
						return;
					}
				}
				var link = '<a href="' + url + '"';
				if(target != "_self")
					link += ' target="' + target + '"';
				link += ">";
				if(STD.isIE) {
					this.selection.select();
					this.selection.pasteHTML(link + this.selection.htmlText + "</a>");
				}
				else
					this.command("Raw", link, "</a>");
				getObject(this.id + "propertyHyperLink").style.display = "none";
			}
			break;
		case "CancelCreateLink":
			if(this.selectedAnchorElement) {
				var a = this.selectedAnchorElement;
				if(STD.isIE)
					a.outerHTML = a.innerHTML;
				else {
					var range = this.getSelectionRange();
					var newChild = range.createContextualFragment(a.innerHTML);
					a.parentNode.replaceChild(newChild, a);
				}
			}
			getObject(this.id + "propertyHyperLink").style.display = "none";
			break;
		case "ObjectBlock":
			this.propertyWindowId = this.id + "propertyInsertObject";
			getObject(this.id + "propertyMoreLess").style.display = "none";
			getObject(this.id + "propertyHyperLink").style.display = "none";
			getObject(this.id + "propertyObject").style.display = "none";
			getObject(this.id + "propertyInsertObject").style.display = "block";
			getObject(this.id + "propertyInsertObject_url").value = "";
			getObject(this.id + "propertyInsertObject_chunk").value = "";
			break;
		case "HideObjectBlock":
			getObject(this.id + "propertyInsertObject").style.display = "none";
			break;
		case "InsertObject":
			if(getObject(this.id + "propertyInsertObject_type").value == "url") {
				var url = getObject(this.id + "propertyInsertObject_url").value.trim();
				if(url == "") {
					alert(s_enterURL);
					return;
				}
				var ext = new RegExp("\\.(\\w+)(?:$|\\?)").exec(url);
				ext = (ext && ext.length == 2) ? ext[1].toLowerCase() : "";
				var code = "";
				if(ext == "swf" || ext == "") {
					code = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="400" height="300">' +
							'<param name="wmode" value="transparent"/>' +
							'<param name="movie" value="' + url + '"/>' +
							'<!--[if !IE]> <-->' +
							'<object type="application/x-shockwave-flash" transparent="yes" data="' + url + '" width="400" height="300">' +
							'<p><a href="' + url + '">[Flash] ' + url + '</a></p>' +
							'<\/object>' +
							'<!--> <![endif]-->' +
							'<\/object>';
				}
				else {
					var type = null;

					switch(ext) {
						case "mp3": type = "audio/mpeg"; break;
						case "mid": type = "audio/x-ms-mid"; break;
						case "wav": type = "audio/x-ms-wav"; break;
						case "wax": type = "audio/x-ms-wax"; break;
						case "wma": type = "audio/x-ms-wma"; break;
						case "avi": type = "video/x-msvideo"; break;
						case "asf":
						case "asx": type = "video/x-ms-asf"; break;
						case "mov": type = "video/quicktime"; break;
						case "mpg":
						case "mpeg": type = "video/x-ms-mpeg"; break;
						case "wmv": type = "video/x-ms-wmv"; break;
						case "mp4": type = "video/mp4"; break;
						case "mkv": type = "video/x-matroska"; break;
						case "wm": type = "video/x-ms-wm"; break;
						case "wvx": type = "video/x-ms-wvx"; break;
					}

					if(type === null) {
						alert(s_unknownFileType);
						return;
					}
					else if(type == "video/quicktime") {
								code = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="320" height="260">' +
								'<param name="src" value="' + url + '"/>' +
								'<param name="controller" value="true"/>' +
								'<param name="autoplay" value="false"/>' +
								'<!--[if !IE]>-->' +
								'<object type="video/quicktime" data="' + url + '" width="320" height="260">' +
								'<param name="autoplay" value="false"/>' +
								'<param name="controller" value="true"/>' +
								'</object>' +
								'<!--<![endif]-->' +
								'</object>';
					}
					else {
						code = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95">' +
								'<param name="Filename" value="' + url + '"/>' +
								'<param name="AutoStart" value="false"/>' +
								'<!--[if !IE]> <-->' +
								'<object type="' + type + '" data="' + url + '" width="320" height="' + (type == "audio/mpeg" ? "20" : "240") + '">' +
								'<param name="AutoStart" value="0"/>' +
								'<embed pluginspage="http://www.microsoft.com/Windows/Downloads/Contents/Products/MediaPlayer/" src="' + url + '" width="320" height="' + (type == "audio/mpeg" ? "20" : "240") + '" type="application/x-mplayer2" autostart="0"></embed>' +
								'</object>' +
								'<!--> <![endif]-->' +
								'</object>';
					}
				}
			}
			else {
				var code = getObject(this.id + "propertyInsertObject_chunk").value.trim();
				if(!(new RegExp("^<object(?:.|\\s)*</object>$", "i").test(code))) {
					alert(s_enterObjectTag);
					return;
				}
				lowercasedCode = code.toLowerCase();
				if(lowercasedCode.count("<object") == 0 || lowercasedCode.count("<object") != lowercasedCode.count("</object>")) {
					alert(s_enterCorrectObjectTag);
					return;
				}
			}

			if(isWYSIWYG) {
				this.command("Raw", '<img class="tatterObject" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(code, "string", "css") + ' longDesc="' + this.objectSerialize(code) + '" />', "");
			} else
				insertTag(this.textarea, code,"");
			getObject(this.id + "propertyInsertObject").style.display = "none";
			break;
		case "MoreLessBlock":
			if(isWYSIWYG) {
				this.command("Raw", '<div class="tattermoreless" more=" more.. " less=" less.. ">&nbsp;', "</div>");
				this.trimContent();
			}
			else
				insertTag(this.textarea, "[#M_ more.. | less.. | ", "_M#]");
			break;
		case "Raw":
			value2 = (typeof value2 == "undefined") ? "" : value2;
			if(isWYSIWYG) {
				if(STD.isIE) {
					this.contentWindow.focus();
					var range = this.getSelectionRange();
					if(range.pasteHTML)
						range.pasteHTML(value1 + range.htmlText + value2);
					else if(this.selectedElement) {
						this.selectedElement.insertAdjacentHTML("beforeBegin", value1);
						this.selectedElement.insertAdjacentHTML("afterEnd", value2);
					}
				} else {
					var focus = this.contentWindow.getSelection().focusNode;
					if(focus && focus.tagName == "HTML") {
						var range = this.contentDocument.createRange();
						range.setStart(this.contentDocument.body,0);
						range.setEnd(this.contentDocument.body,0);
						var dummyNode = document.createElement("div");
						var node = range.extractContents();
						if (node != null) dummyNode.appendChild(node);
						range.insertNode(range.createContextualFragment(value1 + dummyNode.innerHTML + value2));
					} else {
						var range = this.getSelectionRange() || this.lastSelectionRange;
						var dummyNode = document.createElement("div");
						var node = range ? range.extractContents() : null;
						if (node != null) dummyNode.appendChild(node);
						range.insertNode(range.createContextualFragment(value1 + dummyNode.innerHTML + value2));
					}
				}
			} else {
				insertTag(this.textarea, value1, value2);
			}
	}
    this.changeButtonStatus(null, null);
	try { this.contentDocument.body.focus(); } catch(e) { }
}

// IFRAME 내에서 발생하는 이벤트를 처리할 함수
TTModernEditor.prototype.eventHandler = function(event) {
	var isFunctionalKeyPressed = event.altKey || event.ctrlKey || event.shiftKey;

	if(STD.isIE) {
		event = this.contentWindow.event;
		event.target = event.srcElement;
	}

	// safari 3 workaround: collect the last selection range for object insertion
	if (STD.isWebkit && STD.engineVersion >= 419.3) {
		var range = this.getSelectionRange();
		if (range) this.lastSelectionRange = range;
	}

	// 마우스를 클릭했을땐 이벤트가 발생한 오브젝트 핸들을 selectedElement 변수에 저장해둔다
	if(event.type == "mousedown") {
		this.selectedElement = event.target;
		this.activeButton(event.target);
	}
	else if(event.type != "mouseup")
		this.activeButton();

	if(this.selectedElement == null)
		return;

	switch(event.type) {
		case "mouseup":
			var longdesc = this.selectedElement.getAttribute("longdesc");

			if(new RegExp("^1[CLR]", "").exec(longdesc)) {
				var size = this.parseImageSize(this.selectedElement, "array");
				longdesc = longdesc.replace(new RegExp("(width=[\"']?)\\d*", "i"), "$1" + size[0]);
				longdesc = longdesc.replace(new RegExp("(height=[\"']?)\\d*", "i"), "$1" + parseInt(size[0] * this.propertyCurrentProportion1));
				this.selectedElement.setAttribute("longDesc", longdesc);
			}
			break;
		case "keypress":
			var range = this.getSelectionRange();
			if(event.keyCode == 13) {
				if(this.newLineToParagraph) {
					if(STD.isFirefox && !event.shiftKey) {
						// TODO : test<br /> -> <p>test</p>
					} else if(STD.isWebkit && !event.shiftKey) {
						// TODO : test or <div>test</div> -> <p>test</p>
					}
				} else {
					if(STD.isIE && range.parentElement && range.parentElement().tagName != "LI") {
						// TODO : <p>test</p> -> <br />
						event.returnValue = false;
						event.cancelBubble = true;
						range.pasteHTML("<br />");
						range.collapse(false);
						range.select();
						return false;
					} else if(STD.isWebkit && !event.shiftKey) {
						// TODO : test or <div>test</div> -> test<br />
					}
				}
			}
	}
	editorChanged();

	// 이벤트가 발생하면 showProperty 함수에서 TTML 치환자인지 아닌지 판단해, TTML 치환자일 경우에 속성을 수정할 수 있는 창을 띄워주게 된다
	if(this.selectedElement && !isFunctionalKeyPressed) {
		if (this.showProperty(this.selectedElement) && (STD.isWebkit && STD.engineVersion >= 419.3)) {
			// safari 3 workaround: put current element in the selection
			var range = this.contentDocument.createRange();
			range.selectNode(this.selectedElement);
			this.contentWindow.getSelection().removeAllRanges();
			this.contentWindow.getSelection().addRange(range);
		}
	}
}

// execCommand 후 불필요하게 삽입된 여백등을 제거해준다
// Excluse useless blanks after execCommand.
TTModernEditor.prototype.trimContent = function() {
	var html = this.contentDocument.body.innerHTML;
	html = html.replace(new RegExp("<p>\\s*(<br\\s/?)+", "gi"), "<p>");
	html = html.replace(new RegExp("(<br\\s/?>)+\\s*</p>", "gi"), "</p>");
	html = html.replace(new RegExp("<p></p>", "gi"), "");
	html = html.replace(new RegExp("<li>\\s*<p>", "gi"), "<li>");
	html = html.replace(new RegExp("</p>\\s*</li>", "gi"), "</li>");
	this.contentDocument.body.innerHTML = html;
}

// correct HTML tags generated by browser designmode to XHTML compatible
TTModernEditor.prototype.correctContent = function() {
	var isWYSIWYG = false;
	try {
		if(this.editMode == "WYSIWYG")
			isWYSIWYG = true;
	} catch(e) { }

	if(isWYSIWYG) {
		var html = this.contentDocument.body.innerHTML;
	} else {
		var html = this.textarea.value;
	}
	// Webkit-specific correction
	html = html.replaceAll('<br>', '<br />');
	if(STD.isWebkit) {
		html = html.replaceAll('class="Apple-style-span"','');
		html = html.replaceAll('class="webkit-block-placeholder"','');
		html = html.replaceAll('br class="webkit-block-placeholder"','br /');
		html = html.replaceAll('<div><br /></div>','<br />');
		if(this.newLineToParagraph) {
			html = html.replace(new RegExp("<div>(.*?)</div>", "gi"), "<p>$1</p>");
		} else {
			html = html.replace(new RegExp("<div>(.*?)</div>", "gi"), "<br />$1");
		}
	}
	//html = html.replaceAll('<br>', '<br />');
	var dmodeExprs = new Array("font-weight: bold;",
		"font-style: italic;",
		"text-decoration: underline;",
		"text-decoration: line-through;");
	var xhtmlExprs = new Array("strong",
		"em",
		"ins",
		"del");
	for(var i in dmodeExprs) {
		var regTag = new RegExp('<span style="'+dmodeExprs[i]+'">((?:.|\\s)*?)</span>', "gi");
		while(result = regTag.exec(html))
			html = html.replaceAll(result[0], "<"+xhtmlExprs[i]+">"+result[1]+"</"+xhtmlExprs[i]+">");
	}

	// Make tags strict.
	html = html.replace(new RegExp("<b>(.*?)</b>", "gi"), "<strong>$1</strong>");
	html = html.replace(new RegExp("<i([^>]*?)>(.*?)</i>", "gi"), "<em$1>$2</em>");
	html = html.replace(new RegExp("<u([^>]*?)>(.*?)</u>", "gi"), "<ins$1>$2</ins>");
	html = html.replace(new RegExp("<strike([^>]*?)>(.*?)</strike>", "gi"), "<del$1>$2</del>");
	html = html.replace(new RegExp("<(img|br|hr)(\\s+[^>]*[^>/]|)>", "gi"), "<$1$2 />");
	// delete blanks
	html = html.replace(new RegExp("(<(p|div|li|blockquote)(|\\s+[^>]+)>)\\s*(<br\\s*/?>)+", "gi"), "$1");////
	html = html.replace(new RegExp("(<br\\s*/?>)+\\s*(</(p|div|li|blockquote)(|\\s+[^>]+))", "gi"), "$2");
	html = html.replace(new RegExp("<p>\\s*</p>", "gi"), "");
	html = html.replace(new RegExp("<li>\\s*<p>", "gi"), "<li>");
	html = html.replace(new RegExp("</p>\\s*</li>", "gi"), "</li>");
	if(isWYSIWYG) {
		this.contentDocument.body.innerHTML = html;
	} else {
		this.textarea.value = html;
	}
}


// HTML 문자열 또는 오브젝트에서 오브젝트 크기를 추출
TTModernEditor.prototype.parseImageSize = function(target, type, mode) {
	var width = 0;
	var height = 0;

	if(typeof(target) == "object") {
		if(target.style.width && target.style.height) {
			width = parseInt(target.style.width);
			height = parseInt(target.style.height);
		}
		else {
			width = target.width;
			height = target.height;
		}
	}
	else {
		target = target.replace(new RegExp('longdesc=".*?"', "gi"), "");
		target = target.replace(new RegExp("longdesc='.*?'", "gi"), "");

		var regStyleWidth = new RegExp("width:\\s*(\\d+)", "gi");
		var regStyleHeight = new RegExp("height:\\s*(\\d+)", "gi");
		var regWidth = new RegExp("width=[\"']?(\\d+)", "gi");
		var regHeight = new RegExp("height=[\"']?(\\d+)", "gi");

		var sizeWidth, sizeHeight;

		if(sizeWidth = regStyleWidth.exec(target))
			width = sizeWidth[1];
		else if(sizeWidth = regWidth.exec(target))
			width = sizeWidth[1];

		if(sizeHeight = regStyleHeight.exec(target))
			height = sizeHeight[1];
		else if(sizeHeight = regHeight.exec(target))
			height = sizeHeight[1];
	}

	if(type == "array")
		return new Array(width, height);
	else if(mode == "css") {
		var size = ' style="';
		if(width > 0)
			size += 'width: ' + width + 'px;';
		if(height > 0)
			size += 'height: ' + height + 'px;';
		return size + '"';
	}
	else {
		var size = ' ';
		if(width > 0)
			size += 'width="' + width + '" ';
		if(height > 0)
			size += 'height="' + height + '" ';
		return size;
	}
}

// 상위 태그를 검사해서 툴바에 눌림 표시
TTModernEditor.prototype.activeButton = function(node) {
	if(typeof(node) == "undefined") {
		try {
			node = this.activeButton(this.getSelectionRange().parentElement());
		} catch(e) {
			try {
				node = this.activeButton(this.getSelectionRange().commonAncestorContainer.parentNode);
			} catch(e) {
				return;
			}
		}
	}

	this.isBold = false;
	this.isItalic = false;
	this.isUnderline = false;
	this.isStrike = false;
	this.fontName = null;
	this.fontSize = null;

	while(typeof(node) != "undefined" && node.tagName && node.tagName.toLowerCase() != "body") {
		switch(node.tagName.toLowerCase()) {
			case "strong":
			case "b":
				this.isBold = true;
				break;
			case "em":
			case "i":
				this.isItalic = true;
				break;
			case "u":
			case "ins":
				this.isUnderline = true;
				break;
			case "del":
			case "strike":
				this.isStrike = true;
				break;
			case "font":
				if (this.fontName == null && node.face)
					this.fontName = node.face;
				if (this.fontSize == null && node.size)
					this.fontSize = node.size;
				break;
			case "h1":
			case "h2":
			case "h3":
			case "h4":
			case "h5":
			case "h6":
				if (this.fontSize == null)
					this.fontSize = node.tagName.toLowerCase();
				break;
			default:
				if(node.style.fontWeight.toLowerCase() == "bold")
					this.isBold = true;
				if(node.style.fontStyle.toLowerCase() == "italic")
					this.isItalic = true;
				if(node.style.textDecoration.toLowerCase() == "underline")
					this.isUnderline = true;
				if(node.style.textDecoration.toLowerCase() == "line-through")
					this.isStrike = true;
				if (this.fontName == null && node.style.fontFamily)
					this.fontName = node.style.fontFamily;
		}
		node = node.parentNode;
	}

	// parse fontName and map to appropriate value in the font list
	if (this.fontName != null) {
		var fontnamelist = this.fontName.split(',');
		var realfont = null;
		for (var i = 0; i < fontnamelist.length; ++i) {
			var name = fontnamelist[i].replace(new RegExp("^[\\s\"']*|[\\s\"']*$", "g"), "");
			if (typeof this.allFontMap[name] != 'undefined') {
				realfont = this.allFontMap[name];
				break;
			}
		}
		this.fontName = realfont;
	}

	if (this.isBold) {
		getObject(this.id + "indicatorBold").className = getObject(this.id + "indicatorBold").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject(this.id + "indicatorBold").className.match('inactive')) {
			getObject(this.id + "indicatorBold").className = getObject(this.id + "indicatorBold").className.replace("active-class", "inactive-class");
		}
	}
	if (this.isItalic) {
		getObject(this.id + "indicatorItalic").className = getObject(this.id + "indicatorItalic").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject(this.id + "indicatorItalic").className.match('inactive')) {
			getObject(this.id + "indicatorItalic").className = getObject(this.id + "indicatorItalic").className.replace("active-class", "inactive-class");
		}
	}
	if (this.isUnderline) {
		getObject(this.id + "indicatorUnderline").className = getObject(this.id + "indicatorUnderline").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject(this.id + "indicatorUnderline").className.match('inactive')) {
			getObject(this.id + "indicatorUnderline").className = getObject(this.id + "indicatorUnderline").className.replace("active-class", "inactive-class");
		}
	}
	if (this.isStrike) {
		getObject(this.id + "indicatorStrike").className = getObject(this.id + "indicatorStrike").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject(this.id + "indicatorStrike").className.match('inactive')) {
			getObject(this.id + "indicatorStrike").className = getObject(this.id + "indicatorStrike").className.replace("active-class", "inactive-class");
		}
	}
	if (this.fontName != null) {
		getObject(this.id + "fontFamilyChanger").value = this.fontName;
	} else {
		getObject(this.id + "fontFamilyChanger").value = '';
	}
	if (this.fontSize != null) {
		getObject(this.id + "fontSizeChanger").value = this.fontSize;
	} else {
		getObject(this.id + "fontSizeChanger").value = '';
	}
}

TTModernEditor.prototype.getFilenameFromFilelist = function(name) {
	var fileList = getObject("TCfilelist");

	for(var i=0; i<fileList.length; i++)
		if(fileList.options[i].value.indexOf(name) == 0)
			return fileList.options[i].text.substring(0, fileList.options[i].text.lastIndexOf("(") - 1);

	return name;
}

TTModernEditor.prototype.listChanged = function(id) {
	if(id == "propertyGallery_list") {
		var list = getObject(this.id + "propertyGallery_list");
		if(list.selectedIndex > -1) {
			var values = list[list.selectedIndex].value.split("|");
			getObject(this.id + "propertyGallery_preview").style.display = "block";
			getObject(this.id + "propertyGallery_preview").innerHTML = '<img src="' + this.propertyFilePath + values[0] + '" width="198" />';
			getObject(this.id + "propertyGallery_captionLine").style.display = "block";
			getObject(this.id + "propertyGallery_caption").value = values[1];
		}
	}
	else if(id == "propertyiMazing_list") {
		var list = getObject(this.id + "propertyiMazing_list");
		if(list.selectedIndex > -1) {
			var values = list[list.selectedIndex].value.split("|");
			getObject(this.id + "propertyiMazing_preview").style.display = "block";
			getObject(this.id + "propertyiMazing_preview").innerHTML = '<img src="' + this.propertyFilePath + values[0] + '" width="198" />';
		}
	}
	else if(id == "propertyJukebox_list") {
		var list = getObject(this.id + "propertyJukebox_list");
		if(list.selectedIndex > -1) {
			var values = list[list.selectedIndex].value.split("|");
			getObject(this.id + "propertyJukebox_title").value = values[1];
		}
	}
}

TTModernEditor.prototype.moveUpFileList = function(id)
{
	var list = getObject(id);

	if(list && list.selectedIndex > 0) {
		var value = list[list.selectedIndex-1].value;
		var text = list[list.selectedIndex-1].text;

		list[list.selectedIndex-1].value = list[list.selectedIndex].value;
		list[list.selectedIndex-1].text = list[list.selectedIndex].text;
		list[list.selectedIndex].value = value;
		list[list.selectedIndex].text = text;
		list.selectedIndex--;
		this.setProperty();
		this.listChanged(id);
	}
}

TTModernEditor.prototype.moveDownFileList = function(id)
{
	var list = getObject(id);

	if(list && list.selectedIndex < list.length - 1) {
		var value = list[list.selectedIndex+1].value;
		var text = list[list.selectedIndex+1].text;

		list[list.selectedIndex+1].value = list[list.selectedIndex].value;
		list[list.selectedIndex+1].text = list[list.selectedIndex].text;
		list[list.selectedIndex].value = value;
		list[list.selectedIndex].text = text;
		list.selectedIndex++;
		this.setProperty();
		this.listChanged(id);
	}
}

// WYSIWYG <-> TEXTAREA 전환
TTModernEditor.prototype.toggleMode = function() {
	if(this.editMode == "WYSIWYG") {
		this.syncContents();
		this.iframe.style.display = "none";
		this.textarea.style.display = "block";
		this.editMode = "TEXTAREA";
		this.correctContent();
		this.textarea.focus();
		this.resizer.target = this.textarea;
	}
	else {
		this.iframe.style.display = "block";
		this.textarea.style.display = "none";
		this.syncContents();
		try { this.contentDocument.designMode = "on"; }
		catch(e) {
			this.iframe.style.display = "none";
			this.textarea.style.display = "block";
			return;
		}
		this.editMode = "WYSIWYG";
		this.correctContent();
		try { this.contentDocument.body.focus(); } catch(e) { }
		this.resizer.target = this.iframe;
	}
}

// 위지윅 모드에서의 selection을 리턴한다
TTModernEditor.prototype.getSelectionRange = function() {
	if (STD.isWebkit) this.contentWindow.focus();
	return STD.isIE ? this.contentDocument.selection.createRange() : this.contentWindow.getSelection().getRangeAt(0);
}

// object 태그를 "" 안에 넣을 수 있도록 변형
TTModernEditor.prototype.objectSerialize = function(str) {
	str = str.replace(new RegExp("<br\\s*/?>", "gi"), "");
	str = str.replace(new RegExp("\r?\n", "g"), "");
	str = str.replace(new RegExp("<", "g"), "__LT__");
	str = str.replace(new RegExp(">", "g"), "__GT__");
	str = str.replace(new RegExp('"', "g"), "__QUOT__");
	return str;
}

TTModernEditor.prototype.objectUnSerialize = function(str) {
	str = str.replaceAll("__QUOT__", '"');
	str = str.replaceAll("__GT__", ">");
	str = str.replaceAll("__LT__", "<");
	return str;
}

// HTML 문자열에서 attribute="value" 추출
TTModernEditor.prototype.parseAttribute = function(str, name) {
	var regAttribute1 = new RegExp("(^|\\W)" + name + '="([^"]*)"', "gi");
	var regAttribute2 = new RegExp("(^|\\W)" + name + "='([^']*)'", "gi");
	var regAttribute3 = new RegExp("(^|\\W)" + name + "=([^\\s>]*)", "gi");

	if(result = regAttribute1.exec(str)) {
		return result[2];
	} else if(result = regAttribute2.exec(str)) {
		return result[2];
	} else if(result = regAttribute3.exec(str)) {
		return result[2];
	} else {
		return "";
	}
}

// 직접 execCommand 명령을 내릴 수 있게 해줌
TTModernEditor.prototype.execCommand = function(cmd, userInterface, value) {
	if(this.editMode == "WYSIWYG")
		this.contentDocument.execCommand(cmd, userInterface, value);
}

// 파일명으로 이미지파일인지 판단
TTModernEditor.prototype.isImageFile = function(filename) {
	return new RegExp("\\.(jpe?g|gif|png|bmp)$", "gi").exec(filename);
}

// 파일명으로 미디어/플래시파일인지 판단
TTModernEditor.prototype.isMediaFile = function(filename) {
	return new RegExp("\\.(swf|mid|mp3|wav|wax|wma|avi|asf|asx|mov|mpe?g|wmv|wm|wvx)$", "gi").exec(filename);
}

// " -> &quot; / ' -> &#39;
TTModernEditor.prototype.addQuot = function(str) {
	return str.replace(new RegExp('"', "g"), "&quot;").replace(new RegExp("'", "g"), "&#39;");
}

// &quot; -> " / &#39; -> '
TTModernEditor.prototype.removeQuot = function(str) {
	return str.replace(new RegExp("&quot;", "gi"), '"').replace(new RegExp("&#39;", "g"), "'");
}

// Convert HTML entities
TTModernEditor.prototype.htmlspecialchars = function(str) {
	return this.addQuot(str.replace(new RegExp("&", "g"), "&amp;").replace(new RegExp("<", "g"), "&lt;").replace(new RegExp(">", "g"), "&gt;"));
}

// Convert HTML entities Reverse
TTModernEditor.prototype.unHtmlspecialchars = function(str) {
	return this.removeQuot(str.replace(new RegExp("&amp;", "gi"), "&").replace(new RegExp("&lt;", "gi"), "<").replace(new RegExp("&gt;", "gi"), ">"));
}

// 줄바꿈 문자를 BR 태그로
TTModernEditor.prototype.nl2br = function(str) {
	return str.replace(new RegExp("\r\n", "gi"), "<br />").replace(new RegExp("\r", "gi"), "<br />").replace(new RegExp("\n", "gi"), "<br />");
}

// 스타일 속성, 태그 제거
TTModernEditor.prototype.removeFormatting = function(str) {
	var styleTags = new Array("b", "strong", "i", "em", "u", "ins", "strike", "del", "font", "div");
	for(var i in styleTags) {
		var regTag = new RegExp("</?" + styleTags[i] + "(?:>| [^>]*>)", "i");
		while(result = regTag.exec(str))
			str = str.replaceAll(result[0], "");
	}
	str = str.replace(new RegExp('\\s*style="[^"]*"', "gi"), "");
	var styleContainers = new Array("span", "div");
	for(var i in styleContainers) {
		var regTag = new RegExp("<span\\s*?>((?:.|\\s)*?)</span>", "i");
		while(result = regTag.exec(str))
			str = str.replace(result[0], result[1]);
	}
	return str;
}

TTModernEditor.prototype.setPropertyPosition = function(flag) {
	if(win = document.getElementById(this.propertyWindowId)) {
		var isFixed = document.getElementById(this.propertyWindowId + "-fix-position").checked;
		if(flag) {
			if(isFixed)
				setUserSetting("editorPropertyPositionFix", 1);
			else
				setUserSetting("editorPropertyPositionFix", 0);
			for(var i in this.propertyNames)
				document.getElementById(this.id + this.propertyNames[i] + "-fix-position").checked = isFixed;
		}
		if(isFixed)
			win.style.top = "9px";
		else {
			if(this.propertyOffsetTop === null)
				this.propertyOffsetTop = getOffsetTop(win);
			if(this.scrollTop === null)
				this.scrollTop = STD.getScrollTop();
			scrollHeight = STD.getScrollTop() - this.scrollTop;
			if(STD.getScrollTop() > this.propertyOffsetTop - 15) {
				if(win.offsetHeight > getWindowCleintHeight()) {
					if(scrollHeight > 0) { // scroll down
						win.style.top = Math.max(9, Math.min(3000, STD.getScrollTop() + getWindowCleintHeight() - this.propertyOffsetTop - win.offsetHeight)) + "px";
					}
					else { // scroll up
						win.style.top = Math.max(9, Math.min(3000, STD.getScrollTop() + getWindowCleintHeight() - this.propertyOffsetTop - win.offsetHeight)) + "px";
					}
				}
				else
					win.style.top = Math.min(3000, 24 + STD.getScrollTop() - this.propertyOffsetTop) + "px";
			}
			else
				win.style.top = "9px";
			this.scrollTop = STD.getScrollTop();
		}
	}
}

////////////////////////////////////////////////////////////////////////////////

// moved from library/view/ownerView.php, printOwnerEditorScript()

var s_enterURL = _t('URL을 입력하세요.');
var s_unknownFileType = _t('알 수 없는 형식의 파일명입니다.');
var s_enterObjectTag = _t('OBJECT 태그만 입력하세요.');
var s_enterCorrectObjectTag = _t('잘못된 OBJECT 태그입니다.');
var s_selectBoxArea = _t('박스로 둘러쌀 영역을 선택해주세요');
var s_selectLinkArea = _t('링크를 만들 영역을 선택해주세요');

TTModernEditor.prototype.insertColorTag = function(col1) {
	hideLayer(this.id + "colorPalette");
	this.command("Color", col1);
}

TTModernEditor.prototype.insertMarkTag = function(col1) {
	hideLayer(this.id + "markPalette");
	this.command("Mark", col1);
}

TTModernEditor.prototype.addObject = function(data) {
	var objects = data.objects;

	switch (data.mode) {
	case 'Image1L': case 'Image1C': case 'Image1R':
		if (this.isMediaFile(objects[0][0])) {
			getObject(this.id + "propertyInsertObject_type").value = "url";
			getObject(this.id + "propertyInsertObject_url").value = blogURL + "/attachment/" + objects[0][0];
			this.command("InsertObject");
			return true;
		}
		// *fall through*

	case 'Image2C': case 'Image3C':
		try {
			if (this.editMode == "WYSIWYG") {
				var src = servicePath + adminSkin + "/image/spacer.gif";
				var moreattrs = '';
				var longdesc;
				if (data.mode == 'Image1L' || data.mode == 'Image1C' || data.mode == 'Image1R') {
					if (new RegExp("\.(jpe?g|gif|png|bmp|webm|svg)$", "i").test(objects[0][0])) {
						src = this.propertyFilePath + objects[0][0];
						moreattrs = objects[0][1];
					} else {
						objects[0][1] = '';
						moreattrs = this.styleUnknown;
					}
					longdesc = data.mode.substr(5) + '|' + objects[0][0] + '|' + objects[0][1] + '|' + objects[0][2].replaceAll("|", "");
				} else {
					moreattrs = 'width="' + (parseInt(data.mode.substr(5)) * 100) + '" height="100"';
					longdesc = data.mode.substr(5);
					for (var i = 0; objects[i]; ++i) {
						longdesc += '|' + objects[i][0] + '|' + objects[i][1] + '|' + objects[i][2];
					}
				}

				var className = {Image1L: 'tatterImageLeft', Image1C: 'tatterImageCenter', Image1R: 'tatterImageRight',
				                 Image2C: 'tatterImageDual', Image3C: 'tatterImageTriple'}[data.mode];
				var prefix = '<img class="' + className + '" src="' + src + '" ' + moreattrs + ' longdesc="' + this.addQuot(longdesc) + '" />';
				this.command("Raw", prefix);
				return true;
			}
		} catch(e) { }

		var code = data.mode.substr(5);
		for (var i = 0; objects[i]; ++i) {
			code += '|' + objects[i][0] + '|' + objects[i][1] + '|' + objects[i][2];
		}
		insertTag(this.textarea, '[##_' + code + '_##]', "");
		return true;

	case 'ImageFree':
		var prefix = '';
		var isWYSIWYG = false;
		try {
			isWYSIWYG = (this.editMode == 'WYSIWYG');
		} catch (e) {}
		for (var i = 0; objects[i]; ++i) {
			if (isWYSIWYG) {
				prefix += '<img class="tatterImageFree" src="' + this.propertyFilePath + objects[i][0] + '" longdesc="[##_ATTACH_PATH_##]/' + objects[i][0] + '" ' + objects[i][1] + ' />';
			} else {
				prefix += '<img src="[##_ATTACH_PATH_##]/' + objects[i][0] + '" ' + objects[i][1] + ' />';
			}
		}
		this.command("Raw", prefix);
		return true;

	case 'Imazing': case 'Gallery': case 'Jukebox':
		var code = (data.mode == 'Imazing' ? 'iMazing' : data.mode);
		for (var i = 0; objects[i]; ++i) {
			code += '|' + objects[i][0] + '|' + objects[i][1];
		}
		switch (data.mode) {
		case 'Imazing': code += '|' + data.properties + '|'; break;
		case 'Gallery': code += '|width="400" height="300"'; break;
		case 'Jukebox': code += '|autoplay=0 visible=1|'; break;
		}

		try {
			if (this.editMode == "WYSIWYG") {
				var className = 'tatter' + data.mode;
				var widthheight = (data.mode == 'Jukebox' ? 'width="200" height="30"' : 'width="400" height="300"');
				this.command("Raw", '<img class="' + className + '" src="' + servicePath + adminSkin + '/image/spacer.gif" ' + widthheight + ' longdesc="' + code + '" />');
				return true;
			}
		} catch(e) { }
		insertTag(this.textarea, '[##_' + code + '_##]', '');
		return true;
	}

	return false;
}

////////////////////////////////////////////////////////////////////////////////

// moved from library/view/ownerView.php, printEntryEditorPalette()

TTModernEditor.prototype.getEditorPalette = function(htmlonly) {
	var colors = ['008000', '009966', '99CC66', '999966', 'CC9900', 'D41A01',
	              'FF0000', 'FF7635', 'FF9900', 'FF3399', '9B18C1', '993366',
	              '666699', '0000FF', '177FCD', '006699', '003366', '333333',
	              '000000', '8E8E8E', 'C1C1C1', 'FFFFFF', 'FFDAED', 'C9EDFF',
	              'D0FF9D', 'FAFFA9', 'E4E4E4'];
	var boxcolors = ['FFDAED', 'C9EDFF', 'D0FF9D', 'FAFFA9', 'E4E4E4'];

	var html = ////
		'<dl class="font-relatives">' +
			'<dt class="title">' +
				'<span class="label">' + _t('폰트 설정') + '</span>' +
			'</dt>' +
			'<dd class="command-box">' +
				'<select id="__ID__fontFamilyChanger" class="moderneditor-fontFamilyChanger" onchange="__EDITOR__.execCommand(\'fontname\', false, this.value); this.selectedIndex=0;">' +
					'<option class="head-option" value="">' + _t('글자체') + '</option>';
	var fontset = _t('fontDisplayName:fontCode:fontFamily').split('|');
	for (var i = 0; i < this.allFontList.length; ++i) {
		var entry = this.allFontList[i];
		html += '<option style="font-family: ' + entry[1] + ';" value="' + entry[1] + '">' + entry[0] + '</option>';
	}
	html += ////
				'</select>' +
				'<select id="__ID__fontSizeChanger" class="moderneditor-fontSizeChanger" onchange="__EDITOR__.command(\'FontSize\', this.value); this.selectedIndex=0;">' +
					'<option class="head-option" value="">' + _t('속성') + '</option>' +
					'<optgroup class="size" label="' + _t('크기') + '">' +
						'<option value="1">1 (8 pt)</option>' +
						'<option value="2">2 (10 pt)</option>' +
						'<option value="3">3 (12 pt)</option>' +
						'<option value="4">4 (14 pt)</option>' +
						'<option value="5">5 (18 pt)</option>' +
						'<option value="6">6 (24 pt)</option>' +
						'<option value="7">7 (36 pt)</option>' +
					'</optgroup>' +
					'<optgroup class="header" label="' + _t('제목') + '">' +
						'<option value="h3">h3</option>' +
						'<option value="h4">h4</option>' +
						'<option value="h5">h5</option>' +
						'<option value="h6">h6</option>' +
					'</optgroup>' +
				'</select>' +
			'</dd>' +
		'</dl>' +
		'<dl class="font-style">' +
			'<dt class="title">' +
				'<span class="label">' + _t('폰트 스타일') + '</span>' +
			'</dt>' +
			'<dd class="command-box">' +
				'<a id="__ID__indicatorBold" class="inactive-class button moderneditor-indicatorBold" href="#void" onclick="__EDITOR__.command(\'Bold\'); return false" title="' + _t('굵게') + '"><span class="text">' + _t('굵게') + '</span></a>' +
				'<a id="__ID__indicatorItalic" class="inactive-class button moderneditor-indicatorItalic" href="#void" onclick="__EDITOR__.command(\'Italic\'); return false" title="' + _t('기울임') + '"><span class="text">' + _t('기울임') + '</span></a>' +
				'<a id="__ID__indicatorUnderline" class="inactive-class button moderneditor-indicatorUnderline" href="#void" onclick="__EDITOR__.command(\'Underline\'); return false" title="' + _t('밑줄') + '"><span class="text">' + _t('밑줄') + '</span></a>' +
				'<a id="__ID__indicatorStrike" class="inactive-class button moderneditor-indicatorStrike" href="#void" onclick="__EDITOR__.command(\'StrikeThrough\'); return false" title="' + _t('취소선') + '"><span class="text">' + _t('취소선') + '</span></a>' +
				'<a id="__ID__indicatorColorPalette" class="inactive-class button moderneditor-indicatorColorPalette" href="#void" onclick="hideLayer(\'__ID__markPalette\'); hideLayer(\'__ID__textBox\'); toggleLayer(\'__ID__colorPalette\'); __EDITOR__.changeButtonStatus(this, \'colorPalette\'); return false" title="' + _t('글자색') + '"><span class="text">' + _t('글자색') + '</span></a>' +
				'<div id="__ID__colorPalette" class="moderneditor-colorPalette" style="display: none;">' +
					'<table cellspacing="0" cellpadding="0">' +
						'<tr>';
	for (var i = 0; i < colors.length; ++i)
		html += '<td><a href="#void" onclick="__EDITOR__.insertColorTag(\'#' + colors[i] + '\'); return false"><span class="color-' + colors[i] + '">#' + colors[i] + '</span></a></td>';
	html += ////
						'</tr>' +
					'</table>' +
				'</div>' +
				'<a id="__ID__indicatorMarkPalette" class="inactive-class button moderneditor-indicatorMarkPalette" href="#void" onclick="hideLayer(\'__ID__colorPalette\');hideLayer(\'__ID__textBox\');toggleLayer(\'__ID__markPalette\'); __EDITOR__.changeButtonStatus(this, \'markPalette\'); return false" title="' + _t('배경색') + '"><span class="text">' + _t('배경색') + '</span></a>' +
				'<div id="__ID__markPalette" class="moderneditor-markPalette" style="display: none;">' +
					'<table cellspacing="0" cellpadding="0">' +
						'<tr>';
	for (var i = 0; i < colors.length; ++i)
		html += '<td><a href="#void" onclick="__EDITOR__.insertMarkTag(\'#' + colors[i] + '\'); return false"><span class="color-' + colors[i] + '">#' + colors[i] + '</span></a></td>';
	html += ////
						'</tr>' +
					'</table>' +
				'</div>' +
				'<a id="__ID__indicatorTextBox" class="inactive-class button moderneditor-indicatorTextBox" href="#void" onclick="hideLayer(\'__ID__markPalette\');hideLayer(\'__ID__colorPalette\');toggleLayer(\'__ID__textBox\'); __EDITOR__.changeButtonStatus(this, \'textBox\'); return false" title="' + _t('텍스트 상자') + '"><span class="text">' + _t('텍스트 상자') + '</span></a>' +
				'<div id="__ID__textBox" class="moderneditor-textBox" style="display: none;">' +
					'<table cellspacing="0" cellpadding="0">' +
						'<tr>';
	for (var i = 0; i < boxcolors.length; ++i)
		html += '<td><a href="#void" onclick="hideLayer(\'__ID__textBox\'); __EDITOR__.command(\'Box\', \'padding:10px; background-color:#' + boxcolors[i] + '\'); return false"><span class="color-' + boxcolors[i] + '">#' + boxcolors[i] + '</span></a></td>';
	html += ////
						'</tr>' +
					'</table>' +
				'</div>' +
				'<a id="__ID__indicatorRemoveFormat" class="inactive-class button moderneditor-indicatorRemoveFormat" href="#void" onclick="__EDITOR__.command(\'RemoveFormat\'); return false;" title="' + _t('효과 제거') + '"><span class="text">' + _t('효과 제거') + '</span></a>' +
			'</dd>' +
		'</dl>' +
		'<dl class="paragraph">' +
			'<dt class="title">' +
				'<span class="label">' + _t('문단') + '</span>' +
			'</dt>' +
			'<dd class="command-box">' +
				'<a id="__ID__indicatorJustifyLeft" class="inactive-class button moderneditor-indicatorJustifyLeft" href="#void" onclick="__EDITOR__.command(\'JustifyLeft\'); return false" title="' + _t('왼쪽 정렬') + '"><span class="text">' + _t('왼쪽 정렬') + '</span></a>' +
				'<a id="__ID__indicatorJustifyCenter" class="inactive-class button moderneditor-indicatorJustifyCenter" href="#void" onclick="__EDITOR__.command(\'JustifyCenter\'); return false" title="' + _t('가운데 정렬') + '"><span class="text">' + _t('가운데 정렬') + '</span></a>' +
				'<a id="__ID__indicatorJustifyRight" class="inactive-class button moderneditor-indicatorJustifyRight" href="#void" onclick="__EDITOR__.command(\'JustifyRight\'); return false" title="' + _t('오른쪽 정렬') + '"><span class="text">' + _t('오른쪽 정렬') + '</span></a>' +
				'<a id="__ID__indicatorUnorderedList" class="inactive-class button moderneditor-indicatorUnorderedList" href="#void" onclick="__EDITOR__.command(\'InsertUnorderedList\'); return false" title="' + _t('순서없는 리스트') + '"><span class="text">' + _t('순서없는 리스트') + '</span></a>' +
				'<a id="__ID__indicatorOrderedList" class="inactive-class button moderneditor-indicatorOrderedList" href="#void" onclick="__EDITOR__.command(\'InsertOrderedList\'); return false" title="' + _t('번호 매긴 리스트') + '"><span class="text">' + _t('번호 매긴 리스트') + '</span></a>' +
				'<a id="__ID__indicatorOutdent" class="inactive-class button moderneditor-indicatorOutdent" href="#void" onclick="__EDITOR__.command(\'Outdent\'); return false" title="' + _t('내어쓰기') + '"><span class="text">' + _t('내어쓰기') + '</span></a>' +
				'<a id="__ID__indicatorIndent" class="inactive-class button moderneditor-indicatorIndent" href="#void" onclick="__EDITOR__.command(\'Indent\'); return false" title="' + _t('들여쓰기') + '"><span class="text">' + _t('들여쓰기') + '</span></a>' +
				'<a id="__ID__indicatorBlockquote" class="inactive-class button moderneditor-indicatorBlockquote" href="#void" onclick="__EDITOR__.command(\'Blockquote\'); return false" title="' + _t('인용구') + '"><span class="text">' + _t('인용구') + '</span></a>' +
			'</dd>' +
		'</dl>' +
		'<dl class="special">' +
			'<dt class="title">' +
				'<span class="label">' + _t('기타') + '</span>' +
			'</dt>' +
			'<dd class="command-box">' +
				'<a id="__ID__indicatorCreateLink" class="inactive-class button moderneditor-indicatorCreateLink" href="#void" onclick="__EDITOR__.command(\'CreateLink\'); return false" title="' + _t('하이퍼링크') + '"><span class="text">' + _t('하이퍼링크') + '</span></a>' +
				'<a id="__ID__indicatorMediaBlock" class="inactive-class button moderneditor-indicatorMediaBlock" href="#void" onclick="__EDITOR__.command(\'ObjectBlock\'); return false" title="' + _t('미디어 삽입') + '"><span class="text">' + _t('미디어 삽입') + '</span></a>' +
				'<a id="__ID__indicatorMoreLessBlock" class="inactive-class button moderneditor-indicatorMoreLessBlock" href="#void" onclick="__EDITOR__.command(\'MoreLessBlock\'); return false" title="' + _t('More/Less') + '"><span class="text">' + _t('More/Less') + '</span></a>' +
			'</dd>' +
		'</dl>';
	if(this.restrictEditorMode != true) {
		html += ////
		'<dl class="mode">' +
			'<dt class="title">' +
				'<span class="label">' + _t('편집 환경') + '</span>' +
			'</dt>' +
			'<dd class="command-box">' +
				'<a id="__ID__indicatorMode" class="inactive-class button moderneditor-indicatorMode" href="#void" onclick="__EDITOR__.command(\'ToggleMode\'); __EDITOR__.changeEditorMode(); return false" title="' + _t('클릭하시면 HTML 편집기로 변경합니다.') + '"><span class="text">' + _t('WYSIWYG 편집기') + '</span></a>' +
			'</dd>' +
		'</dl>';
	}
	html = html.replace(new RegExp('__EDITOR__', 'g'), 'TTModernEditor.editors.' + this.name);
	html = html.replace(new RegExp('__ID__', 'g'), this.id);

	if(htmlonly == true) {
		return '<div id="moderneditor-palette">'+html+'</div>';
	}
	var div = document.createElement('div');
	div.id = 'moderneditor-palette';
	div.className = 'container';
	div.innerHTML = html;
	return div;
}

////////////////////////////////////////////////////////////////////////////////

TTModernEditor.prototype.getEditorProperty = function(/*$alt*/) {
	//$fixPosition = getUserSetting('editorPropertyPositionFix', 0);
	var fixPosition = this.fixPosition, hasGD = this.hasGD;

	// hyperlink
	var html = ////
		'<div id="__ID__propertyHyperLink" class="entry-editor-property property-window-HyperLink" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyHyperLink-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyHyperLink-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('하이퍼링크') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyInsertObject_url">' + _t('URL') + '</label></dt>' +
					'<dd><input type="text" id="__ID__propertyHyperLink_url" class="input-text" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyInsertObject_type">' + _t('대상') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyHyperLink_target" style="width: 105px" >' +
							'<option value="_blank">' + _t('새창') + '</option>' +
							'<option value="_self">' + _t('현재창') + '</option>' +
							'<option value="">' + _t('사용 안함') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
			'</div>' +
			'<div class="button-box">' +
				'<span class="insert-button button" onclick="__EDITOR__.command(\'ExcuteCreateLink\'); return false"><span class="text">' + _t('적용하기') + '</span></span>' +
				'<span class="divider"> | </span>' +
				'<span class="cancel-button button" onclick="__EDITOR__.command(\'CancelCreateLink\'); return false"><span class="text">' + _t('취소하기') + '</span></span>' +
			'</div>' +
		'</div>';

	// object
	html += ////
		'<div id="__ID__propertyInsertObject" class="entry-editor-property property-window-InsertObject" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyInsertObject-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyInsertObject-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('오브젝트 삽입') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyInsertObject_type">' + _t('유형') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyInsertObject_type" style="width: 105px" onchange="getObject(\'__ID__propertyInsertObject_part_url\').style.display=getObject(\'__ID__propertyInsertObject_part_raw\').style.display=\'none\';getObject(\'__ID__propertyInsertObject_part_\' + this.value).style.display = \'block\'">' +
							'<option value="url">' + _t('주소입력') + '</option>' +
							'<option value="raw">' + _t('코드 붙여넣기') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
				'<dl id="__ID__propertyInsertObject_part_url" class="line">' +
					'<dt class="property-name"><label for="__ID__propertyInsertObject_url">' + _t('파일 주소') + '</label></dt>' +
					'<dd><input type="text" id="__ID__propertyInsertObject_url" class="input-text" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl id="__ID__propertyInsertObject_part_raw" class="line" style="display: none">' +
					'<dt class="property-name"><label for="__ID__propertyInsertObject_chunk">' + _t('코드') + '</label></dt>' +
					'<dd>' +
						'<textarea id="__ID__propertyInsertObject_chunk" cols="30" rows="10"></textarea>' +
					'</dd>' +
				'</dl>' +
			'</div>' +
			'<div class="button-box">' +
				'<span class="insert-button button" onclick="__EDITOR__.command(\'InsertObject\'); return false"><span class="text">' + _t('삽입하기') + '</span></span>' +
				'<span class="divider"> | </span>' +
				'<span class="cancel-button button" onclick="__EDITOR__.command(\'HideObjectBlock\'); return false"><span class="text">' + _t('취소하기') + '</span></span>' +
			'</div>' +
		'</div>';

	// one image
	html += ////
		'<div id="__ID__propertyImage1" class="entry-editor-property property-window-Image1" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyImage1-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyImage1-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Image') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage1_width1">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage1_width1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage1_alt1">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage1_alt1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage1_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage1_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// two images
	html += ////
		'<div id="__ID__propertyImage2" class="entry-editor-property property-window-Image2" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyImage2-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyImage2-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Image') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('첫번째 이미지') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_width1">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage2_width1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_alt1">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage2_alt1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage2_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('두번째 이미지') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_width2">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage2_width2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_alt2">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage2_alt2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage2_caption2">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage2_caption2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// three images
	html += ////
		'<div id="__ID__propertyImage3" class="entry-editor-property property-window-Image3" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyImage3-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyImage3-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Image') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('첫번째 이미지') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_width1">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_width1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_alt1">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_alt1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage3_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('두번째 이미지') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_width2">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_width2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_alt2">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_alt2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_caption2">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage3_caption2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('세번째 이미지') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_width3">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_width3" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_alt3">' + _t('대체 텍스트') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyImage3_alt3" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyImage3_caption3">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyImage3_caption3" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// object
	html += ////
		'<div id="__ID__propertyObject" class="entry-editor-property property-window-Object" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyObject-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyObject-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Object') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject_width">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject_width" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject_height">' + _t('높이') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject_height" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject_chunk">' + _t('코드') + '</label></dt>' +
					'<dd><textarea id="__ID__propertyObject_chunk" class="propertyObject_chunk" cols="30" rows="10" onkeyup="__EDITOR__.setProperty()"></textarea></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// one video
	html += ////
		'<div id="__ID__propertyObject1" class="entry-editor-property property-window-Object1" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyObject1-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyObject1-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Object 1') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject1_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject1_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject1_filename1">' + _t('파일명(수정불가)') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject1_filename1" readonly="readonly" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// two videoes
	html += ////
		'<div id="__ID__propertyObject2" class="entry-editor-property property-window-Object2" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyObject2-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyObject2-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Object') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('첫번째 오브젝트') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject2_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject2_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject2_filename1">' + _t('파일명') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject2_filename1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('두번째 오브젝트') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject2_caption2">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject2_caption2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject2_filename2">' + _t('파일명') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject2_filename2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// three videoes
	html += ////
		'<div id="__ID__propertyObject3" class="entry-editor-property property-window-Object3" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyObject3-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyObject3-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Object') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('첫번째 오브젝트') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_caption1">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject3_caption1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_filename1">' + _t('파일명') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject3_filename1" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('두번째 오브젝트') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_caption2">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject3_caption2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_filename2">' + _t('파일명') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject3_filename2" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('세번째 오브젝트') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_caption3">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyObject3_caption3" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyObject3_filename3">' + _t('파일명') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyObject3_filename3" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// iMazing
	html += ////
		'<div id="__ID__propertyiMazing" class="entry-editor-property property-window-iMazing" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyiMazing-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyiMazing-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('iMazing') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('설정') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_width">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyiMazing_width" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_height">' + _t('높이') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyiMazing_height" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_frame">' + _t('테두리') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyiMazing_frame" onchange="__EDITOR__.setProperty()">' +
							'<option value="net_imazing_frame_none">' + _t('테두리 없음') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_tran">' + _t('장면전환효과') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyiMazing_tran" onchange="__EDITOR__.setProperty()">' +
							'<option value="net_imazing_show_window_transition_none">' + _t('효과없음') + '</option>' +
							'<option value="net_imazing_show_window_transition_alpha">' + _t('투명전환') + '</option>' +
							'<option value="net_imazing_show_window_transition_contrast">' + _t('플래쉬') + '</option>' +
							'<option value="net_imazing_show_window_transition_sliding">' + _t('슬라이딩') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_nav">' + _t('내비게이션') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyiMazing_nav" onchange="__EDITOR__.setProperty()">' +
							'<option value="net_imazing_show_window_navigation_none">' + _t('기본') + '</option>' +
							'<option value="net_imazing_show_window_navigation_simple">' + _t('심플') + '</option>' +
							'<option value="net_imazing_show_window_navigation_sidebar">' + _t('사이드바') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name">' + _t('슬라이드쇼 간격') + '</dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyiMazing_sshow" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name">' + _t('화면당 이미지 수') + '</dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyiMazing_page" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_align">' + _t('정렬방법') + '</label></dt>' +
					'<dd>' +
						'<select id="__ID__propertyiMazing_align" onchange="__EDITOR__.setProperty()">' +
							'<option value="h">' + _t('가로') + '</option>' +
							'<option value="v">' + _t('세로') + '</option>' +
						'</select>' +
					'</dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyiMazing_caption">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyiMazing_caption" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('파일') + '</div>' +
				'<dl class="file-list-line line">' +
					'<dd>' +
						'<select id="__ID__propertyiMazing_list" class="file-list" size="10" onchange="__EDITOR__.listChanged(\'propertyiMazing_list\')" onclick="__EDITOR__.listChanged(\'propertyiMazing_list\')"></select>' +
					'</dd>' +
				'</dl>' +
				'<div class="button-box">' +
					'<span class="up-button button" onclick="__EDITOR__.moveUpFileList(\'__ID__propertyiMazing_list\'); return false" title="' + _t('선택한 항목을 위로 이동합니다.') + '"><span class="text">' + _t('위로') + '</span></span>' +
					'<span class="divider"> | </span>' +
					'<span class="dn-button button" onclick="__EDITOR__.moveDownFileList(\'__ID__propertyiMazing_list\'); return false" title="' + _t('선택한 항목을 아래로 이동합니다.') + '"><span class="text">' + _t('아래로') + '</span></span>' +
				'</div>' +
				'<div id="__ID__propertyiMazing_preview" class="preview-box" style="display: none;"></div>' +
			'</div>' +
		'</div>';

	// gallery
	html += ////
		'<div id="__ID__propertyGallery" class="entry-editor-property property-window-Gallery" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyGallery-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyGallery-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Gallery') + '</h4>' +
			'<div class="group">' +
				'<div class="title">' + _t('설정') + '</div>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyGallery_width">' + _t('최대너비') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyGallery_width" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyGallery_height">' + _t('최대높이') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyGallery_height" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl id="__ID__propertyGallery_captionLine" class="line" style="display: none;">' +
					'<dt class="property-name"><label for="__ID__propertyGallery_caption">' + _t('자막') + '</label></dt>' +
					'<dd><textarea class="input-text" id="__ID__propertyGallery_caption" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);"></textarea></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('파일') + '</div>' +
				'<dl class="file-list-line line">' +
					'<dd>' +
						'<select id="__ID__propertyGallery_list" class="file-list" size="10" onchange="__EDITOR__.listChanged(\'propertyGallery_list\')" onclick="__EDITOR__.listChanged(\'propertyGallery_list\'); return false"></select>' +
					'</dd>' +
				'</dl>' +
				'<div class="button-box">' +
					'<span class="up-button button" onclick="__EDITOR__.moveUpFileList(\'__ID__propertyGallery_list\')" title="' + _t('선택한 항목을 위로 이동합니다.') + '"><span class="text">' + _t('위로') + '</span></span>' +
					'<span class="divider"> | </span>' +
					'<span class="dn-button button" onclick="__EDITOR__.moveDownFileList(\'__ID__propertyGallery_list\')" title="' + _t('선택한 항목을 아래로 이동합니다.') + '"><span class="text">' + _t('아래로') + '</span></span>' +
				'</div>' +
				'<div id="__ID__propertyGallery_preview" class="preview-box" style="display: none;"></div>' +
			'</div>' +
		'</div>';

	// jukebox
	html += ////
		'<div id="__ID__propertyJukebox" class="entry-editor-property property-window-Jukebox" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyJukebox-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyJukebox-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Jukebox') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyJukebox_title">' + _t('제목') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyJukebox_title" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dd><input type="checkbox" id="__ID__propertyJukebox_autoplay" onclick="__EDITOR__.setProperty()" /> <label for="__ID__propertyJukebox_autoplay">' + _t('자동재생') + '</label></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dd><input type="checkbox" id="__ID__propertyJukebox_visibility" onclick="__EDITOR__.setProperty()" /> <label for="__ID__propertyJukebox_visibility">' + _t('플레이어 보이기') + '</label></dd>' +
				'</dl>' +
			'</div>' +
			'<div class="group">' +
				'<div class="title">' + _t('파일') + '</div>' +
				'<dl class="file-list-line line">' +
					'<dd>' +
						'<select id="__ID__propertyJukebox_list" class="file-list" size="10" onchange="__EDITOR__.listChanged(\'propertyJukebox_list\')" onclick="__EDITOR__.listChanged(\'propertyJukebox_list\')"></select>' +
					'</dd>' +
				'</dl>' +
				'<div class="button-box">' +
					'<span class="up-button button" onclick="__EDITOR__.moveUpFileList(\'__ID__propertyJukebox_list\')" title="' + _t('선택한 항목을 위로 이동합니다.') + '"><span class="text">' + _t('위로') + '</span></span>' +
					'<span class="divider"> | </span>' +
					'<span class="dn-button button" onclick="__EDITOR__.moveDownFileList(\'__ID__propertyJukebox_list\')" title="' + _t('선택한 항목을 아래로 이동합니다.') + '"><span class="text">' + _t('아래로') + '</span></span>' +
				'</div>' +
			'</div>' +
		'</div>';

	// embedded things
	html += ////
		'<div id="__ID__propertyEmbed" class="entry-editor-property property-window-Embed" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyEmbed-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyEmbed-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Embed') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyEmbed_width">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyEmbed_width" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyEmbed_height">' + _t('높이') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyEmbed_height" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyEmbed_src"><acronym class="text" title="Uniform Resource Locator">URL</acronym></label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyEmbed_src" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// flash object
	html += ////
		'<div id="__ID__propertyFlash" class="entry-editor-property property-window-Flash" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyFlash-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyFlash-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('Embed') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyFlash_width">' + _t('폭') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyFlash_width" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyFlash_height">' + _t('높이') + '</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyFlash_height" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyFlash_src">URL</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyFlash_src" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	// more/less
	html += ////
		'<div id="__ID__propertyMoreLess" class="entry-editor-property property-window-MoreLess" style="display: none;">' +
            '<div class="entry-editor-property-control">' +
                '<a onclick="getObject(this).parentNode.parentNode.style.display=\'none\'; return true;"' + '><span class="text">' + _t('닫기') + '</span></a>' +
            '</div>' +
			'<div class="entry-editor-property-option">' +
				'<input type="checkbox" class="checkbox" id="__ID__propertyMoreLess-fix-position" onclick="__EDITOR__.setPropertyPosition(1); return true;"' + (fixPosition ? ' checked="checked"' : '') + '/>' +
				'<label for="__ID__propertyMoreLess-fix-position">' + _t('위치 고정') + '</label>' +
			'</div>' +
			'<h4>' + _t('More/Less') + '</h4>' +
			'<div class="group">' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyMoreLess_more">More Text</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyMoreLess_more" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
				'<dl class="line">' +
					'<dt class="property-name"><label for="__ID__propertyMoreLess_less">Less Text</label></dt>' +
					'<dd><input type="text" class="input-text" id="__ID__propertyMoreLess_less" onkeyup="__EDITOR__.setProperty()" onkeypress="return preventEnter(event);" /></dd>' +
				'</dl>' +
			'</div>' +
		'</div>';

	html = html.replace(new RegExp('__EDITOR__', 'g'), 'TTModernEditor.editors.' + this.name);
	html = html.replace(new RegExp('__ID__', 'g'), this.id);

	var div = document.createElement('div');
	div.id = 'property-section';
	div.className = 'section';
	div.innerHTML = html;
	return div;
}

////////////////////////////////////////////////////////////////////////////////

// moved from /blog/owner/entry/edit/item.php

TTModernEditor.prototype.changeEditorMode = function() {
	editWindow = document.getElementById("editWindow");
	var indicatorMode = document.getElementById(this.id + "indicatorMode");

	if (editWindow.style.display == "block" || editWindow.style.display == "inline") {
		if (document.getElementById("visualEditorWindow")) {
			indicatorMode.className = indicatorMode.className.replace("inactive-class", "active-class");
			indicatorMode.innerHTML = '<span class="text">' + _t('HTML 모드') + '</span>';
			indicatorMode.setAttribute("title", _t('클릭하시면 WYSIWYG 모드로 변경합니다.'));
		} else {
			indicatorMode.className = indicatorMode.className.replace("inactive-class", "active-class");
			indicatorMode.innerHTML = '<span class="text">' + _t('HTML 모드') + '</span>';
			indicatorMode.removeAttribute("title");
		}
	} else {
		indicatorMode.className = indicatorMode.className.replace("active-class", "inactive-class");
		indicatorMode.setAttribute("title", _t('클릭하시면 HTML 모드로 변경합니다.'));
		indicatorMode.innerHTML = '<span class="text">' + _t('WYSIWYG 모드') + '</span>';
	}
}

TTModernEditor.prototype.changeButtonStatus = function(obj, palette) {
	if (!document.getElementById(this.id + 'indicatorColorPalette').className.match('inactive-class')) {
		document.getElementById(this.id + 'indicatorColorPalette').className = document.getElementById(this.id + 'indicatorColorPalette').className.replace('active-class', 'inactive-class');
	}
	if (!document.getElementById(this.id + 'indicatorMarkPalette').className.match('inactive-class')) {
		document.getElementById(this.id + 'indicatorMarkPalette').className = document.getElementById(this.id + 'indicatorMarkPalette').className.replace('active-class', 'inactive-class');
	}
	if (!document.getElementById(this.id + 'indicatorTextBox').className.match('inactive-class')) {
		document.getElementById(this.id + 'indicatorTextBox').className = document.getElementById(this.id + 'indicatorTextBox').className.replace('active-class', 'inactive-class');
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
