var TTEditor = function() {
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

	// 커서가 있는곳의 스타일
	this.isBold = false;
	this.isItalic = false;
	this.isUnderline = false;
	this.isStrike = false;

	// MORE/LESS 블럭을 선택했을때의 임시변수
	this.textMore = "";
	this.textLess = "";

	this.editMode = "TEXTAREA";
	this.styleUnknown = 'style="width: 90px; height: 30px; border: 2px outset #796; background-color: #efd; background-image: url(\'' + servicePath + '/image/extension/unknown.gif\')"';
}

// 각종 환경 초기화
TTEditor.prototype.initialize = function(textarea, imageFilePath, mode, newLine) {
	// execCommand가 사용가능한 경우에만 위지윅을 쓸 수 있다. (지금은 Internet Explorer, Firefox만 지원한다)
	if(typeof(document.execCommand) == "undefined" || !(STD.isIE || STD.isFirefox))
		return;

	// 위지윅모드로 시작
	this.editMode = mode;

	this.propertyFilePath = imageFilePath;
	this.newLineToParagraph = (newLine == "P");

	// 마우스로 클릭했을때 클릭한 위치의 오브젝트의 인스턴스를 저장할 변수
	this.selectedElement = null;
	
	// 선택된 위치 상위노드에 a 태그가 있으면 여기에 저장된다
	this.selectedAnchorElement = null;	

	// 포커스가 벗어나도 선택영역을 유지하기 위해 selection을 저장해둔다
	this.selection = null;

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
	this.iframe.style.width = Math.min(skinContentWidth + (STD.isIE ? 36 : 39), 650) + "px";

	// IFRAME을 감싸는 DIV
	//this.iframeWrapper = document.createElement("div");
	//this.iframeWrapper.id = "iframeWrapper";
	//this.iframeWrapper.appendChild(this.iframe);

	//textarea.parentNode.insertBefore(this.iframeWrapper, textarea);
	textarea.parentNode.insertBefore(this.iframe, textarea);
	
	// 자주 참조하는 핸들을 지정해둔다
	this.contentWindow = this.iframe.contentWindow;
	this.contentDocument = this.contentWindow.document;

	// 디자인모드로 변경한다
	try { this.contentDocument.designMode = "on"; }
	catch(e) { return; }

	// IFRAME 안에 HTML을 작성한다
	this.contentDocument.open("text/html", "replace");
	this.contentDocument.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">');
	this.contentDocument.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko"><head><meta http-equiv="content-type" content="text/html; charset=utf-8" />');
	this.contentDocument.write('<link rel="stylesheet" type="text/css" href="' + servicePath + editorCSS + '" />');
	this.contentDocument.write('<style type="text/css">')
	if(STD.isIE)
		this.contentDocument.write("html { padding: 10px 0px 10px; } body { padding: 10px; }");
	else
		this.contentDocument.write("html { padding: 0px 10px; }");
	this.contentDocument.write("</style>");
	this.contentDocument.write("</head><body>");
	this.contentDocument.write(this.ttml2html());
	this.contentDocument.write("</body></html>");
	this.contentDocument.close();

	// IFRAME 내에서 발생하는 이벤트 핸들러를 연결
	STD.addEventListener(this.contentDocument);

	this.contentDocument.addEventListener("mousedown", this.eventHandler, false);
	this.contentDocument.addEventListener("mouseup", this.eventHandler, false);
	this.contentDocument.addEventListener("keydown", this.eventHandler, false);
	this.contentDocument.addEventListener("keypress", this.eventHandler, false);
	this.contentDocument.addEventListener("paste", this.eventHandler, false);
	this.contentDocument.addEventListener("keyup", this.eventHandler, false);
	
	// editor height resize event
	STD.addEventListener(document);
	document.addEventListener("mousemove", docEventHandler, false);
	document.addEventListener("mousedown", docEventHandler, false);
	document.addEventListener("mouseup", docEventHandler, false);
	document.addEventListener("selectstart", docEventHandler, false);
	editor.contentDocument.addEventListener("mousemove", docEventHandler, false);
	editor.contentDocument.addEventListener("mousedown", docEventHandler, false);
	editor.contentDocument.addEventListener("mouseup", docEventHandler, false);
	
	if(this.editMode == "TEXTAREA")
		this.iframe.style.display = "none";
	
	// 가끔씩 Firefox에서 커서가 움직이지 않는 문제 수정
	setTimeout("try{editor.contentDocument.designMode='on'}catch(e){}", 100);
}

// editor height resize event
function docEventHandler(event) {
	var attachManagerSelectNest = getObject('attachManagerSelectNest');

	if(STD.isIE) event.target = event.srcElement;

	switch(event.type) {
		case "mousemove":
			if(editor.rowResizeDown) {
				var targetOffset = (editor.editMode == "WYSIWYG") ? editor.iframe : editor.textarea;
				var pageY = parseInt(event.clientY);

				if(event.target.tagName != "BODY" && event.target.tagName != "HTML") {
					pageY += document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
					pageY -= getOffsetTop(targetOffset);
				}

				try { targetOffset.style.height = pageY + 'px'; } catch(e) {}
			} else if(event.target == getObject('status-container')) {
				editor.rowResize = true;
			} else {
				editor.rowResize = false;
			}
			break;
		case "mousedown":
			editor.rowResizeDown = false;

			if(editor.rowResize) {
				attachManagerSelectNest.style.visibility = 'hidden';
				editor.rowResizeDown = true;
			}
			break;
		case "mouseup":
			editor.rowResizeDown = false;
			attachManagerSelectNest.style.visibility = 'visible';
			break;
		case "selectstart":
			return editor.rowResizeDown ? false : true;
	}
}

// TTML로 작성된 파일을 HTML 뷰에 뿌려주기 위해 변환
TTEditor.prototype.ttml2html = function() {
	var str = this.textarea.value;

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

				var more = block.substring(0, block.indexOf("|"));
				var remain = block.substring(block.indexOf("|") + 1, block.length);
				var less = remain.substring(0, remain.indexOf("|"));
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

	// Object 처리
	var objects = getTagChunks(str, "object");
	for(i in objects) {
		str = str.replaceAll(objects[i], '<img class="tatterObject" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(objects[i], "string", "css") + ' longDesc="' + this.objectSerialize(objects[i]) + '" />');
	}

	// Flash 처리
	var regEmbed = new RegExp("<embed([^<]*?)application/x-shockwave-flash(.*?)></embed>", "i");
	while(result = regEmbed.exec(str)) {
	    var body = result[0];
	    str = str.replaceAll(body, '<img class="tatterFlash" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(body, "string", "css") + ' longDesc="' + this.parseAttribute(body, "src") + '"/>');
	}

	// Embed 처리
	var regEmbed = new RegExp("<embed([^<]*?)></embed>", "i");
	while(result = regEmbed.exec(str)) {
	    var body = result[0];
	    str = str.replaceAll(body, '<img class="tatterEmbed" src="' + servicePath + adminSkin + '/image/spacer.gif"' + this.parseImageSize(body, "string", "css") + ' longDesc="' + this.parseAttribute(body, "src") + '"/>');
	}

	return str;
}

// IFRAME에 작성된 HTML을 태터툴즈 텍스트 에디터에서 볼 수 있는 TTML로 전환
TTEditor.prototype.html2ttml = function() {
	var obj = this.contentDocument.body.cloneNode(true);

	// MORE/LESS 처리
	while(true) {
		var divs = obj.getElementsByTagName("div");

		if(divs.length > 0) {
			var exist = false;

			for(var i=0; i<divs.length; i++) {
				if(divs[i].className == "tattermoreless") {
					exist = true;
					divs[i].className = "removeme";
					divs[i].innerHTML = "[#M_" + divs[i].getAttribute("more") + "|" + divs[i].getAttribute("less") + "|" + divs[i].innerHTML + "_M#]";
				}

				if(exist)
					break;
			}

			if(!exist)
				break;
		}
		else
			break;
	}

	var str = obj.innerHTML;

	// 빈줄을 br 태그로
	str = str.replace(new RegExp("<p[^>]*?>&nbsp;</p>", "gi"), "<br />");
	
	// <br> 을 <br />로
	str = str.replaceAll("<br>", "<br />");
	str = str.replaceAll("<BR>", "<br />");

	// 빈 태그 제거
	str = str.replace(new RegExp("<(\\w+)[^>]*></\\1>", "gi"), "");

	// 쓸모없는 &nbsp; 제거
	str = str.replace(new RegExp("([^ ])&nbsp;([^ ])", "gi"), "$1 $2");

	// 비어있는 a 태그 제거
	var regEmptyAnchor = new RegExp("<a>(((?!<a>).)*?)</a>", "i");
	while(result = regEmptyAnchor.exec(str))
		str = str.replaceAll(result[0], result[1]);

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

		// TT 1.0 alpha ~ 1.0.1까지 쓰던 Gallery 치환자를 위한 코드 
		if(longdesc.length % 2 == 1) 
			longdesc.length--; 

		var files = ""; 
 
		for(var i=1; i<longdesc.length-1; i++) 
			files += longdesc[i].replace(new RegExp("&amp;", "gi"), "&") + "|"; 

		str = str.replaceAll(body, "[##_Gallery|" + files + this.unHtmlspecialchars(trim(longdesc[longdesc.length-1])) + "_##]"); 
	}

	// Jukebox 처리
	var regJukebox = new RegExp("<img[^>]*class=[\"']?tatterJukebox[^>]*>", "gi");
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

	// MORE/LESS 처리 후 남은 태그 제거
	str = str.replace(new RegExp("<div.*?class=[\"']?removeme.*?\\[#M_", "gi"), "[#M_");
	str = str.replace(new RegExp("_M#\\]</div>", "gi"), "_M#]");

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
	if(!strictXHTML)
		return str;

	// <b> -> <strong>, <i> -> <em>, <u> -> <ins>, <strike> -> <del>
	str = str.replace(new RegExp("<b([^>]*?)>(.*?)</b>", "gi"), "<strong$1>$2</strong>");
	str = str.replace(new RegExp("<i([^>]*?)>(.*?)</i>", "gi"), "<em$1>$2</em>");
	str = str.replace(new RegExp("<u([^>]*?)>(.*?)</u>", "gi"), "<ins$1>$2</ins>");
	str = str.replace(new RegExp("<strike([^>]*?)>(.*?)</strike>", "gi"), "<del$1>$2</del>");


	var regTag = new RegExp("<([^\\s>]+)\\s*([^>]*)(/?)>", "g");

	while(result = regTag.exec(str)) {
		var tagBody = result[0];
		var tagStart = "<" + result[1];
		var tagFinish = result[3] + ">";

		if(tagStart.indexOf("<!--") == 0)
			continue;

		var attributeString = result[2];

		var regAttribute = new RegExp("(\\s*[^=]*)=((?:\"[^\"]+\")|(?:'[^']+')|(?:[^\\s]+))", "g");

		var attributes = new Array();

		while(result = regAttribute.exec(attributeString))
			attributes.push(new Array(result[1].trim(), result[2].replace(new RegExp("['\"](.*)['\"]", "g"), "$1").trim()));

		var sb = new StringBuffer();

		for(var i in attributes) {
			if(trim(attributes[i][0].toLowerCase()) == "style") {
				var regStyle = new RegExp("([\\w-]+): ([^;]*)", "gi");
				var sbStyle = new StringBuffer();

				while(result = regStyle.exec(attributes[i][1]))
					sbStyle.append(result[1].toLowerCase() + ": " + result[2] + "; ");

				sb.append(" style=\"" + sbStyle.toString().replace(new RegExp("(.*); $", "g"), "$1") + "\"");
			}
			else
				sb.append(" " + attributes[i][0].toLowerCase() + "=\"" + attributes[i][1] + "\"");
		}

		var tagAttributes = sb.toString();

		switch(tagStart.toLowerCase()) {
			case "<img":
			case "<br":
			case "<hr":
				tagFinish = (tagFinish == ">") ? " />" : tagFinish;
		}

		if(tagStart.toLowerCase() == "<img" && tagAttributes.indexOf("alt=") == -1)
			tagFinish = ' alt=""' + tagFinish;

		str = str.replaceAll(tagBody, tagStart.toLowerCase() + tagAttributes + tagFinish);
	}

	return str;
}

// 위지윅 모드에서 치환자 이미지를 클릭했을때 편집창 옆에 속성창을 보여준다
TTEditor.prototype.showProperty = function(obj)
{
	editor.selectedAnchorElement = null;
	editor.selection = editor.getSelectionRange();

	var attribute = obj.getAttribute("longdesc");

	getObject("textBox").style.display = "none";
	getObject("colorPalette").style.display = "none"; 
	getObject("markPalette").style.display = "none"; 
	getObject("propertyImage1").style.display = "none";
	getObject("propertyImage2").style.display = "none";
	getObject("propertyImage3").style.display = "none";
	getObject("propertyObject").style.display = "none";
	getObject("propertyObject1").style.display = "none";
	getObject("propertyObject2").style.display = "none";
	getObject("propertyObject3").style.display = "none";
	getObject("propertyiMazing").style.display = "none";
	getObject("propertyiMazing_preview").style.display = "none";
	getObject("propertyGallery").style.display = "none";
	getObject("propertyGallery_preview").style.display = "none";
	getObject("propertyJukebox").style.display = "none";
	getObject("propertyEmbed").style.display = "none";
	//getObject("propertyFlash").style.display = "none";
	getObject("propertyMoreLess").style.display = "none";
	changeButtonStatus(null, null);

	if(obj.className == "tatterObject") {
		editor.propertyHeader = "tatterObject";
		editor.propertyWindowId = "propertyObject";
		var size = editor.parseImageSize(editor.selectedElement, "array");
		getObject("propertyObject_width").value = size[0];
		getObject("propertyObject_height").value = size[1];
		getObject("propertyObject_chunk").value = this.objectUnSerialize(attribute);
		getObject("propertyObject").style.display = "block";
	}
	else if(obj.className == "tatterEmbed") {
		editor.propertyHeader = "tatterEmbed";
		editor.propertyWindowId = "propertyEmbed";
		var size = editor.parseImageSize(editor.selectedElement, "array");
		getObject("propertyEmbed_width").value = size[0];
		getObject("propertyEmbed_height").value = size[1];
		getObject("propertyEmbed_src").value = attribute;
		getObject("propertyEmbed").style.display = "block";
	}
	/*else if(obj.className == "tatterFlash") {
		editor.propertyHeader = "tatterFlash";
		editor.propertyWindowId = "propertyFlash";
		var size = editor.parseImageSize(editor.selectedElement, "array");
		getObject("propertyFlash_width").value = size[0];
		getObject("propertyFlash_height").value = size[1];
		getObject("propertyFlash_src").value = attribute;
		getObject("propertyFlash").style.display = "block";
	}*/
	else if(obj.tagName && obj.tagName.toLowerCase() == "img" && attribute) {
		var values = attribute.split("|");

		if(values.length == 1)
			return;

		editor.propertyHeader = values[0];

		if(values[0] == "iMazing" || values[0] == "Gallery" || values[0] == "Jukebox") {
			var objectCount = 1;
			var objectType = values[0];
			var propertyWindowId= "property" + objectType;
		}
		else {
			var objectCount = values[0].charAt(0);
			var objectType = editor.isImageFile(values[1]) ? "Image" : "Object";
			var propertyWindowId = "property" + objectType + objectCount;
		}

		editor.propertyWindowId = propertyWindowId;

		if(objectType == "Image") {
			getObject(propertyWindowId + "_width1").value = trim(editor.removeQuot(editor.parseAttribute(values[2], "width")));
			getObject(propertyWindowId + "_alt1").value = trim(editor.unHtmlspecialchars(editor.removeQuot(editor.parseAttribute(values[2], "alt"))));
			getObject(propertyWindowId + "_caption1").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[3])));
			if (editor.parseAttribute(values[2], "class").match("tt-resampling")) {
				getObject(propertyWindowId + "_resample1").checked = true;
			} else if (editor.parseAttribute(values[2], "class").match("tt-watermark")) {
				getObject(propertyWindowId + "_watermark1").checked = true;
				getObject(propertyWindowId + "_resample1").checked = true;
			}
			
			editor.propertyFilename1 = values[1];
			
			// 1번 이미지.
			if(objectCount == 1) {
				var size = editor.parseImageSize(editor.selectedElement, "array");

				if(editor.propertyCurrentImage == editor.selectedElement.getAttribute("src")) {
					var newWidth = size[0];
					var newHeight = parseInt(size[0] * editor.propertyCurrentProportion1);
					editor.propertyCurrentProportion1 = newHeight / newWidth;
					editor.selectedElement.removeAttribute("width");
					editor.selectedElement.removeAttribute("height");
					if (!isNaN(newWidth))
						editor.selectedElement.style.width = newWidth + "px";
					if (!isNaN(newHeight))
						editor.selectedElement.style.height = newHeight + "px";
				}
				else {
					editor.propertyCurrentProportion1 = size[1] / size[0];
					editor.propertyCurrentImage = editor.selectedElement.getAttribute("src");
				}
			}
			else {
				var size = editor.parseImageSize(values[2], "array");
				editor.propertyCurrentProportion1 = size[1] / size[0];
				if(objectCount > 1) {
					var size = editor.parseImageSize(values[5], "array");
					editor.propertyCurrentProportion2 = size[1] / size[0];
				}
				if(objectCount > 2) {
					var size = editor.parseImageSize(values[8], "array");
					editor.propertyCurrentProportion3 = size[1] / size[0];
				}
			}
			
			// 2번 이미지.
			if(objectCount > 1) {
				getObject(propertyWindowId + "_width2").value = trim(editor.removeQuot(editor.parseAttribute(values[5], "width")));
				getObject(propertyWindowId + "_alt2").value = trim(editor.unHtmlspecialchars(editor.removeQuot(editor.parseAttribute(values[5], "alt"))));
				getObject(propertyWindowId + "_caption2").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[6])));
				if (editor.parseAttribute(values[5], "class").match("tt-resampling")) {
					getObject(propertyWindowId + "_resample2").checked = true;
				} else if (editor.parseAttribute(values[5], "class").match("tt-watermark")) {
					getObject(propertyWindowId + "_watermark2").checked = true;
					getObject(propertyWindowId + "_resample2").checked = true;
				}
			}

			editor.propertyFilename2 = values[4];
			
			// 3번 이미지.
			if(objectCount > 2) {
				getObject(propertyWindowId + "_width3").value = trim(editor.removeQuot(editor.parseAttribute(values[8], "width")));
				getObject(propertyWindowId + "_alt3").value = trim(editor.unHtmlspecialchars(editor.removeQuot(editor.parseAttribute(values[8], "alt"))));
				getObject(propertyWindowId + "_caption3").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[9])));
				if (editor.parseAttribute(values[8], "class").match("tt-resampling")) {
					getObject(propertyWindowId + "_resample3").checked = true;
				} else if (editor.parseAttribute(values[8], "class").match("tt-watermark")) {
					getObject(propertyWindowId + "_watermark3").checked = true;
					getObject(propertyWindowId + "_resample3").checked = true;
				}
			}

			editor.propertyFilename3 = values[7];
		}
		else if(objectType == "Object") {
			getObject(propertyWindowId + "_caption1").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[3])));
			getObject(propertyWindowId + "_filename1").value = editor.getFilenameFromFilelist(values[1]);
			editor.propertyFilename1 = values[1];
			if(objectCount > 1) {
				getObject(propertyWindowId + "_caption2").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[6])));
				getObject(propertyWindowId + "_filename2").value = editor.getFilenameFromFilelist(values[4]);
				editor.propertyFilename2 = values[4];
			}

			if(objectCount > 2) {
				getObject(propertyWindowId + "_caption3").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[9])));
				getObject(propertyWindowId + "_filename3").value = editor.getFilenameFromFilelist(values[7]);
				editor.propertyFilename3 = values[7];
			}
		}
		else if(objectType == "iMazing") {
			var size = editor.parseImageSize(editor.selectedElement, "array");
			var attributes = values[values.length-2];

			getObject(propertyWindowId + "_width").value = size[0];
			getObject(propertyWindowId + "_height").value = size[1];
			getObject(propertyWindowId + "_frame").value = editor.parseAttribute(attributes, "frame");
			getObject(propertyWindowId + "_tran").value = editor.parseAttribute(attributes, "transition");
			getObject(propertyWindowId + "_nav").value = editor.parseAttribute(attributes, "navigation");
			getObject(propertyWindowId + "_sshow").value = editor.parseAttribute(attributes, "slideshowInterval");
			getObject(propertyWindowId + "_page").value = editor.parseAttribute(attributes, "page");
			getObject(propertyWindowId + "_align").value = editor.parseAttribute(attributes, "align");
			getObject(propertyWindowId + "_caption").value = trim(editor.unHtmlspecialchars(editor.removeQuot(values[values.length-1])));

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2)
				list.options[list.length] = new Option(editor.getFilenameFromFilelist(values[i]), values[i] + "|", false, false);
		}
		else if(objectType == "Gallery") {
			var size = editor.parseImageSize(editor.selectedElement, "array");
			var attributes = values[values.length-2];

			getObject(propertyWindowId + "_width").value = size[0];
			getObject(propertyWindowId + "_height").value = size[1];
			getObject(propertyWindowId + "_caption").value = "";

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2)
				list.options[list.length] = new Option(editor.getFilenameFromFilelist(values[i]), values[i] + "|" + editor.unHtmlspecialchars(values[i+1]), false, false);
		}
		else if(objectType == "Jukebox") {
			getObject(propertyWindowId + "_autoplay").checked = editor.parseAttribute(values[values.length-2], "autoplay") == 1;
			getObject(propertyWindowId + "_visibility").checked = editor.parseAttribute(values[values.length-2], "visible") == 1;

			var list = getObject(propertyWindowId + "_list");

			list.innerHTML = "";

			for(var i=1; i<values.length-2; i+=2)
				list.options[list.length] = new Option(editor.getFilenameFromFilelist(values[i]), values[i] + "|" + editor.unHtmlspecialchars(values[i+1]), false, false);
		}

		getObject(propertyWindowId).style.display = "block";
	} else {
		var node = obj;

		while(node.parentNode) {
			if(node.tagName && node.tagName.toLowerCase() == "div" && node.getAttribute("more") != null && node.getAttribute("less") != null) {
				var moreText = node.getAttribute("more");
				var lessText = node.getAttribute("less");
				getObject("propertyMoreLess").style.display = "block";
				getObject("propertyMoreLess_more").value = trim(editor.unHtmlspecialchars(moreText));
				getObject("propertyMoreLess_less").value = trim(editor.unHtmlspecialchars(lessText));
				editor.propertyWindowId = "propertyMoreLess";
				getObject("propertyHyperLink").style.display = "none";
				
				return;
			} else if(node.tagName.toLowerCase() == "a" && node.href) {
				getObject("propertyHyperLink").style.display = "block";
				getObject("propertyHyperLink_url").value = node.href;
				getObject("propertyHyperLink_target").value = node.target;
				if(getObject("propertyHyperLink_target").selectedIndex == -1)
					getObject("propertyHyperLink_target").value = "_self";
				editor.selectedAnchorElement = node;
				editor.propertyWindowId = "propertyHyperLink";
				getObject("propertyMoreLess").style.display = "none";
				
				return;
			}

			node = node.parentNode;
		}
		
		if(STD.isIE)
			var isEmpty = (editor.getSelectionRange().htmlText == "");
		else
			var isEmpty = (editor.getSelectionRange().startOffset == editor.getSelectionRange().endOffset);
		
		if (editor.selectedAnchorElement == null && isEmpty)
			getObject("propertyHyperLink").style.display = "none";
	}
}

// 속성창에서 수정된 내용을 반영
TTEditor.prototype.setProperty = function()
{
	var attribute = editor.selectedElement.getAttribute("longdesc");

	if(editor.selectedElement.className == "tatterObject" || editor.selectedElement.className == "tatterEmbed" || editor.selectedElement.className == "tatterFlash") {
		editor.selectedElement.removeAttribute("width");
		editor.selectedElement.removeAttribute("height");
		editor.selectedElement.style.width = "auto";
		editor.selectedElement.style.height = "auto";

		try {
			var width = parseInt(getObject(editor.propertyWindowId + "_width").value);
			if(!isNaN(width) && width > 0 && width < 10000)
				editor.selectedElement.style.width = width + "px";
			var height = parseInt(getObject(editor.propertyWindowId + "_height").value);
			if(!isNaN(height) && height > 0 && height < 10000)
				editor.selectedElement.style.height = height + "px";
		} catch(e) { }

		if(editor.selectedElement.className == "tatterEmbed" || editor.selectedElement.className == "tatterFlash")
			editor.selectedElement.setAttribute("longDesc", getObject(editor.propertyWindowId + "_src").value);
		else {
			editor.selectedElement.setAttribute("longDesc", editor.objectSerialize(getObject(editor.propertyWindowId + "_chunk").value));
		}
	}
	else if(editor.selectedElement.tagName && editor.selectedElement.tagName.toLowerCase() == "img" && attribute) {
		if(editor.propertyWindowId.indexOf("propertyImage") == 0) {
			var objectCount = editor.propertyWindowId.charAt(editor.propertyWindowId.length-1);

			// 1L,1C,1R일 경우에는 수정된 속성의 크기로 실제 이미지 크기를 변경
			// 1번 이미지.
			if(objectCount == 1) {
				editor.selectedElement.removeAttribute("width");
				editor.selectedElement.removeAttribute("height");
				editor.selectedElement.style.width = "auto";
				editor.selectedElement.style.height = "auto";

				try {
					var value = parseInt(getObject(editor.propertyWindowId + "_width1").value);
					if(!isNaN(value) && value > 0 && value < 10000) {
						var newWidth = value;
						var newHeight = parseInt(value * editor.propertyCurrentProportion1);
						editor.selectedElement.style.width = newWidth + "px";
						editor.selectedElement.style.height = newHeight + "px";
					}
				} catch(e) { }
			}

			var imageSize = "";
			var imageAlt = "";
			var imageCaption = "";
			var imageResample = "";
			
			try {
				var value = parseInt(getObject(editor.propertyWindowId + "_width1").value);
				if(!isNaN(value) && value > 0 && value < 10000)
					imageSize = 'width="' + value + '" height="' + parseInt(value * editor.propertyCurrentProportion1) + '" ';
			} catch(e) { }
			try {
				if(editor.isImageFile(editor.propertyFilename1))
					imageAlt = 'alt="' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_alt1").value) + '"';
			} catch(e) { imageAlt = 'alt=""'; }
			try {
				imageCaption = editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption1").value);
			} catch(e) { imageCaption = ''; }
			try {
				if (getObject(editor.propertyWindowId + "_watermark1").checked == true) {
					imageResample = 'class="tt-watermark" ';
					getObject(editor.propertyWindowId + "_resample1").checked = true;
				} else if (getObject(editor.propertyWindowId + "_resample1").checked == true) {
					imageResample = 'class="tt-resampling" ';
				} else {
					imageResample = '';
					getObject(editor.propertyWindowId + "_watermark1").checked = false;
					getObject(editor.propertyWindowId + "_resample1").checked = false;
				}
			} catch(e) { imageResample = ''; }
			
			var longdesc = editor.propertyHeader + '|' + editor.propertyFilename1 + '|' + imageSize + imageResample + imageAlt + '|' + imageCaption;
			
			// 2번 이미지.
			if(objectCount > 1) {
				imageSize = "";
				imageAlt = "";
				imageCaption = "";
				imageResample = "";
				
				try {
					var value = parseInt(getObject(editor.propertyWindowId + "_width2").value);
					if(!isNaN(value) && value > 0 && value < 10000)
						imageSize = 'width="' + value + '" height="' + parseInt(value * editor.propertyCurrentProportion2) + '" ';;
				} catch(e) { }
				try {
					if(editor.isImageFile(editor.propertyFilename2))
						imageAlt = 'alt="' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_alt2").value) + '"';
				} catch(e) { imageAlt = 'alt = ""'; }
				try {
					imageCaption = editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption2").value);
				} catch(e) { imageCaption = ''; }
				try {
					if (getObject(editor.propertyWindowId + "_watermark2").checked == true) {
						imageResample = 'class="tt-watermark" ';
						getObject(editor.propertyWindowId + "_resample2").checked = true;
					} else if (getObject(editor.propertyWindowId + "_resample2").checked == true) {
						imageResample = 'class="tt-resampling" ';
					} else {
						imageResample = '';
						getObject(editor.propertyWindowId + "_watermark2").checked = false;
						getObject(editor.propertyWindowId + "_resample2").checked = false;
					}
				} catch(e) { imageResample = ''; }
				
				longdesc += '|' + editor.propertyFilename2 + '|' + imageSize + imageResample + imageAlt + '|' + imageCaption;
			}
			
			// 3번 이미지.
			if(objectCount > 2) {
				imageSize = "";
				imageAlt = "";
				imageCaption = "";
				imageResample = "";
				
				try {
					var value = parseInt(getObject(editor.propertyWindowId + "_width3").value);
					if(!isNaN(value) && value > 0 && value < 10000)
						imageSize = 'width="' + value + '" height="' + parseInt(value * editor.propertyCurrentProportion3) + '" ';
				} catch(e) { }
				try {
					if(editor.isImageFile(editor.propertyFilename3))
						imageAlt = 'alt="' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_alt3").value) + '"';
				} catch(e) { imageAlt = 'alt = ""'; }
				try {
					imageCaption = editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption3").value);
				} catch(e) { imageCaption = ''; }
				try {
					if (getObject(editor.propertyWindowId + "_watermark3").checked == true) {
						imageResample = 'class="tt-watermark" ';
						getObject(editor.propertyWindowId + "_resample3").checked = true;
					} else if (getObject(editor.propertyWindowId + "_resample3").checked == true) {
						imageResample = 'class="tt-resampling" ';
					} else {
						imageResample = '';
						getObject(editor.propertyWindowId + "_watermark3").checked = false;
						getObject(editor.propertyWindowId + "_resample3").checked = false;
					}
				} catch(e) { imageResample = ''; }
				
				longdesc += '|' + editor.propertyFilename3 + '|' + imageSize + imageResample + imageAlt + '|' + imageCaption;
			}

			editor.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(editor.propertyWindowId.indexOf("propertyObject") == 0) {
			var objectCount = editor.propertyWindowId.charAt(editor.propertyWindowId.length-1);

			var longdesc = editor.propertyHeader + '|' + editor.propertyFilename1 + '||' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption1").value);

			if(objectCount > 1)
				longdesc += '|' + editor.propertyFilename2 + '||' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption2").value);

			if(objectCount > 2)
				longdesc += '|' + editor.propertyFilename3 + '||' + editor.htmlspecialchars(getObject(editor.propertyWindowId + "_caption3").value);

			editor.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(editor.propertyWindowId.indexOf("propertyiMazing") == 0) {
			var list = getObject("propertyiMazing_list");
			var longdesc = "iMazing|";

			for(var i=0; i<list.length; i++)
				longdesc += list[i].value.substring(0, list[i].value.indexOf("|")) + "||";

			editor.selectedElement.removeAttribute("width");
			editor.selectedElement.removeAttribute("height");
			editor.selectedElement.style.width = "auto";
			editor.selectedElement.style.height = "auto";

			var size = "";

			var width = parseInt(getObject("propertyiMazing_width").value);
			if(!isNaN(width) && width > 0 && width < 10000) {
				editor.selectedElement.style.width = width + "px";
				size = 'width="' + width + '" ';
			}

			var height = parseInt(getObject("propertyiMazing_height").value);
			if(!isNaN(height) && height > 0 && height < 10000) {
				editor.selectedElement.style.height = height + "px";
				size += 'height="' + height + '"';
			}

			if(isNaN(width) && isNaN(height)) {
				editor.selectedElement.style.width = editor.selectedElement.style.height = 100 + "px";
				size = 'width="100" height="100"';
			}

			longdesc += size;
			longdesc += ' frame="' + getObject("propertyiMazing_frame").value + '"';
			longdesc += ' transition="' + getObject("propertyiMazing_tran").value + '"';
			longdesc += ' navigation="' + getObject("propertyiMazing_nav").value + '"';
			longdesc += ' slideshowInterval="' + getObject("propertyiMazing_sshow").value + '"';
			longdesc += ' page="' + getObject("propertyiMazing_page").value + '"';
			longdesc += ' align="' + getObject("propertyiMazing_align").value + '"';
			longdesc += ' skinPath="' + servicePath + '/script/gallery/iMazing/"';
			longdesc += "|" + editor.htmlspecialchars(getObject("propertyiMazing_caption").value);

			editor.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(editor.propertyWindowId.indexOf("propertyGallery") == 0) {
			var list = getObject("propertyGallery_list");
			var longdesc = "Gallery|";

			if(list.selectedIndex != -1) {
				var caption = getObject("propertyGallery_caption").value.replaceAll("|", "");
				var tmp = list[list.selectedIndex].value.split("|");
				list[list.selectedIndex].value = tmp[0] + "|" + caption;
			}

			for(var i=0; i<list.length; i++)
				longdesc += editor.htmlspecialchars(list[i].value) + "|";

			editor.selectedElement.removeAttribute("width");
			editor.selectedElement.removeAttribute("height");
			editor.selectedElement.style.width = "auto";
			editor.selectedElement.style.height = "auto";

			var size = "";

			var width = parseInt(getObject("propertyGallery_width").value);
			if(!isNaN(width) && width > 0 && width < 10000) {
				editor.selectedElement.style.width = width + "px";
				size = 'width="' + width + '" ';
			}

			var height = parseInt(getObject("propertyGallery_height").value);
			if(!isNaN(height) && height > 0 && height < 10000) {
				editor.selectedElement.style.height = height + "px";
				size += 'height="' + height + '"';
			}

			if(isNaN(width) && isNaN(height)) {
				editor.selectedElement.style.width = editor.selectedElement.style.height = 100 + "px";
				size = 'width=100 height=100';
			}

			longdesc += trim(size) + "|";

			editor.selectedElement.setAttribute("longDesc", longdesc);
		}
		else if(editor.propertyWindowId.indexOf("propertyJukebox") == 0) {
			var list = getObject("propertyJukebox_list");
			var longdesc = "Jukebox|";

			if(list.selectedIndex != -1) {
				var title = getObject("propertyJukebox_title").value.replaceAll("|", "");
				var tmp = list[list.selectedIndex].value.split("|");
				list[list.selectedIndex].value = tmp[0] + "|" + title;
			}

			for(var i=0; i<list.length; i++)
				longdesc += list[i].value + "|";

			longdesc += "autoplay=" + (getObject("propertyJukebox_autoplay").checked ? 1 : 0);
			longdesc += " visible=" + (getObject("propertyJukebox_visibility").checked ? 1 : 0);

			editor.selectedElement.setAttribute("longDesc", longdesc + "|");
		}
	}
	else if(editor.selectedElement.tagName && editor.selectedElement.tagName.toLowerCase() == "div" && editor.selectedElement.getAttribute("more") != null && editor.selectedElement.getAttribute("less") != null) {
		editor.selectedElement.setAttribute("more", editor.htmlspecialchars(getObject("propertyMoreLess_more").value));
		editor.selectedElement.setAttribute("less", editor.htmlspecialchars(getObject("propertyMoreLess_less").value));
	}
}

function TTCommand(command, value1, value2) {
	var isWYSIWYG = false;

	try {
		if(editor.editMode == "WYSIWYG")
			isWYSIWYG = true;
	} catch(e) { }

	switch(command) {
		case "ToggleMode":
			try {
				editor.toggleMode();
				editor.trimContent();
			} catch(e) { }
			break;
		case "Bold":
			if(isWYSIWYG) {
				editor.execCommand("Bold", false, null);
				editor.activeButton();
			}
			else
				insertTag("<strong>", "</strong>");
			break;
		case "Italic":
			if(isWYSIWYG) {
				editor.execCommand("Italic", false, null);
				editor.activeButton();
			}
			else
				insertTag("<em>", "</em>");
			break;
		case "Underline":
			if(isWYSIWYG) {
				editor.execCommand("Underline", false, null);
				editor.activeButton();
			}
			else
				insertTag("<ins>", "</ins>");
			break;
		case "StrikeThrough":
			if(isWYSIWYG) {
				editor.execCommand("StrikeThrough", false, null);
				editor.activeButton();
			}
			else
				insertTag("<del>", "</del>");
			break;
		case "Color":
			if(isWYSIWYG)
				editor.execCommand("ForeColor", false, value1);
			else
				TTCommand("Raw", '<span style="color: ' + value1 + '">', "</span>");
			break;
		case "Mark":
			if(isWYSIWYG) {
				if(STD.isIE) {
					editor.execCommand("BackColor", false, value1);
				} else {
					editor.execCommand("HiliteColor", false, value1);
				}
			} else {
				TTCommand("Raw", '<span style="background-color: ' + value1 + '">', "</span>");
			}
			break;
		case "RemoveFormat":
			if(isWYSIWYG) {
				if(STD.isIE) {
					if(editor.getSelectionRange().htmlText != "")
						editor.getSelectionRange().pasteHTML(editor.removeFormatting(editor.getSelectionRange().htmlText));
				}
				else {
					if(editor.getSelectionRange().startOffset != editor.getSelectionRange().endOffset) {
						var range = editor.getSelectionRange();
						var dummyNode = document.createElement("div");
						dummyNode.appendChild(range.extractContents());
						range.insertNode(range.createContextualFragment(editor.removeFormatting(dummyNode.innerHTML)));
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
					if(editor.selectedElement && editor.selectedElement.tagName == "IMG") {
						switch(editor.selectedElement.className) {
							default:
								editor.execCommand("Justify" + blockAlign, false, null);
								break;
							case "":
							case "tatterImageFree":
								var img = editor.selectedElement;
								img.removeAttribute("align");
								if(blockAlign == "center")
									editor.execCommand("Justify" + blockAlign, false, null);
								else {
									img.setAttribute("align", blockAlign);
									var container = editor.selectedElement.parentNode;
									if(container && (container.tagName == "P" || container.tagName == "DIV") && container.childNodes.length == 1 && container.parentNode)
										container.parentNode.replaceChild(img, container);
								}
						}
					}
					else if(editor.getSelectionRange().htmlText) {
						var div = document.createElement("div");
						div.innerHTML = editor.getSelectionRange().htmlText;
						if(div.childNodes.length == 1 && (div.childNodes[0].tagName == "P" || div.childNodes[0].tagName == "DIV")) {
							div.childNodes[0].style.textAlign = blockAlign;
							editor.getSelectionRange().parentElement().outerHTML = div.innerHTML;
						}
						else {
							var parent = editor.getSelectionRange().parentElement();
							if(parent && parent.tagName != "BODY" && parent.innerHTML == editor.getSelectionRange().htmlText)
								parent.style.textAlign = blockAlign;
							else {
								for(var i=0; i<parent.childNodes.length; i++) {
									if(parent.childNodes[i].tagName == "P" || parent.childNodes[i].tagName == "DIV") {
										parent.childNodes[i].removeAttribute("align");
										parent.childNodes[i].style.textAlign = "";
									}
								}
								editor.getSelectionRange().pasteHTML('<div style="text-align: ' + blockAlign + '">' + editor.getSelectionRange().htmlText + "</div>");
							}
						}
					}
					else {
						var container = editor.getSelectionRange().parentElement();
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
					editor.execCommand("Justify" + blockAlign, false, null);
			}
			else
				insertTag('<div style="text-align: ' + blockAlign + '">', "</div>");
			delete blockAlign;
			editor.trimContent();
			break;
		case "InsertUnorderedList":
			if(isWYSIWYG) {
				if(STD.isIE)
					var isEmpty = editor.getSelectionRange().htmlText == "";
				else
					var isEmpty = editor.getSelectionRange().startOffset == editor.getSelectionRange().endOffset;

				if(isEmpty)
					editor.execCommand("InsertUnorderedList", false, null);
				else {
					try { var node = editor.activeButton(editor.getSelectionRange().parentElement()); }
					catch(e) {
						try { var node = editor.activeButton(editor.getSelectionRange().commonAncestorContainer.parentNode); }
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
							editor.getSelectionRange().pasteHTML("<ul><li>" + editor.getSelectionRange().htmlText.replace(new RegExp("<br />", "gi"), "</li><li>") + "</li></ul>");
						}
						else {
							var range = editor.getSelectionRange();
							var dummyNode = document.createElement("div");
							dummyNode.appendChild(range.extractContents());
							var html = dummyNode.innerHTML.replace(new RegExp("<br \/>", "gi"), "<\/li><li>");
							range.insertNode(range.createContextualFragment("<ul><li>" + html + "<\/li><\/ul>"));
						}
					}
				}
				editor.trimContent();
			}
			else
				insertTag("<ul><li>", "</li></ul>");
			break;
		case "InsertOrderedList":
			if(isWYSIWYG) {
				if(STD.isIE)
					var isEmpty = editor.getSelectionRange().htmlText == "";
				else
					var isEmpty = editor.getSelectionRange().startOffset == editor.getSelectionRange().endOffset;

				if(isEmpty)
					editor.execCommand("InsertOrderedList", false, null);
				else {
					try { var node = editor.activeButton(editor.getSelectionRange().parentElement()); }
					catch(e) {
						try { var node = editor.activeButton(editor.getSelectionRange().commonAncestorContainer.parentNode); }
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
							editor.getSelectionRange().pasteHTML("<ol><li>" + editor.getSelectionRange().htmlText.replace(new RegExp("<br />", "gi"), "</li><li>") + "</li></ol>");
						}
						else {
							var range = editor.getSelectionRange();
							var dummyNode = document.createElement("div");
							dummyNode.appendChild(range.extractContents());
							var html = dummyNode.innerHTML.replace(new RegExp("<br />", "gi"), "<\/li><li>");
							range.insertNode(range.createContextualFragment("<ol><li>" + html + "<\/li><\/ol>"));
						}
					}
				}
				editor.trimContent();
			}
			else
				insertTag("<ol><li>", "</li></ol>");
			break;
		case "Indent":
			if(isWYSIWYG) {
				editor.execCommand("Indent", false, null);
				editor.trimContent();
			}
			break;
		case "Outdent":
			if(isWYSIWYG) {
				editor.execCommand("Outdent", false, null);
				editor.trimContent();
			}
			break;
		case "Blockquote":
			TTCommand("Raw", "<blockquote>", "</blockquote>");
			editor.trimContent();
			break;
		case "Box":
			if(isWYSIWYG && !STD.isIE) {
				if(editor.selection == null || editor.selection.startOffset == editor.selection.endOffset) {
					alert(s_selectBoxArea);
					return;
				}
			}
			TTCommand("Raw", '<div style="' + value1 + '">', "</div>");
			editor.trimContent();
			break;
		case "CreateLink":
			if(!isWYSIWYG) {
				TTCommand("Raw", '<a href="">', "</a>");
				return;
			}
			if(STD.isIE) {
				if(editor.selection == null || editor.selection.htmlText == "") {
					alert(s_selectLinkArea);
					return;
				}
			}
			else {
				if(editor.selection == null || editor.selection.startOffset == editor.selection.endOffset) {
					alert(s_selectLinkArea);
					return;
				}
			}
			getObject("propertyMoreLess").style.display = "none";
			getObject("propertyHyperLink").style.display = "block";
			getObject("propertyHyperLink_url").value = "";
			getObject("propertyHyperLink_target").selectedIndex = 0;
			break;
		case "ExcuteCreateLink":
			var url = getObject("propertyHyperLink_url").value.trim();
			var target = getObject("propertyHyperLink_target").value;
			if(url == "") {
				alert(s_enterURL);
				return;
			}
			if(editor.selectedAnchorElement) {
				editor.selectedAnchorElement.href = url;
				if(target == "_self")
					editor.selectedAnchorElement.removeAttribute("target");
				else
					editor.selectedAnchorElement.target = target;
				getObject("propertyHyperLink").style.display = "none";
			}
			else {
				if(STD.isIE) {
					if(!editor.selection.htmlText || editor.selection.htmlText == "") {
						alert(s_selectLinkArea);
						return;
					}
				}
				else {
					if(editor.selection.startOffset == editor.selection.endOffset) {
						alert(s_selectLinkArea);
						return;
					}
				}
				var link = '<a href="' + url + '"';
				if(target != "_self")
					link += ' target="' + target + '"';
				link += ">";
				if(STD.isIE) {
				    editor.selection.select();
					editor.selection.pasteHTML(link + editor.selection.htmlText + "</a>");
				}
				else
					TTCommand("Raw", link, "</a>");
				getObject("propertyHyperLink").style.display = "none";
			}
			break;
		case "CancelCreateLink":
			if(editor.selectedAnchorElement) {
				var a = editor.selectedAnchorElement;
				if(STD.isIE)
					a.outerHTML = a.innerHTML;
				else {
					var range = editor.getSelectionRange();
					var newChild = range.createContextualFragment(a.innerHTML);
					a.parentNode.replaceChild(newChild, a);
				}
			}
			getObject("propertyHyperLink").style.display = "none";
			break;
		case "ObjectBlock":
			getObject("propertyInsertObject").style.display = "block";
			break;
		case "HideObjectBlock":
			getObject("propertyInsertObject").style.display = "none";
			break;
		case "InsertObject":
			if(getObject("propertyInsertObject_type").value == "url") {
				var url = getObject("propertyInsertObject_url").value;
				var ext = new RegExp("\\.(\\w+)(?:$|\\?)").exec(url);
				ext = (ext && ext.length == 2) ? ext[1].toLowerCase() : "";
				var code = "";
				if(ext == "swf"  || ext == "") {
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
						case "mid": type = "audio/x-ms-mid"; break;
						case "mp3": type = "audio/x-ms-mp3"; break;
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
						case "wm": type = "video/x-ms-wm"; break;
						case "wvx": type = "video/x-ms-wvx"; break;
					}

					if(type)
						code = '<object width="320" height="280" src="' + url + '" type="' + type + '">' +
							'<param name="FileName" value="' + url + '" />' +
							'<param name="AutoStart" value="0" />' +
							'</object>';
					else {
						alert(s_unknownFileType);
						return;
					}
				}
			}
			else {
				var code = getObject("propertyInsertObject_chunk").value.trim();
				if(!(new RegExp("^<object(?:.|\\s)*</object>$", "i").test(code))) {
					alert(s_enterObjectTag);
					return;
				}
				if(code.count("<object") == 0 || code.count("<object") != code.count("</object>")) {
					alert(s_enterCorrectObjectTag);
					return;
				}
			}

			if(isWYSIWYG)
				TTCommand("Raw", '<img class="tatterObject" src="' + servicePath + adminSkin + '/image/spacer.gif"' + editor.parseImageSize(code, "string", "css") + ' longDesc="' + editor.objectSerialize(code) + '" />', "");
			else
				insertTag(code,"");
			getObject("propertyInsertObject").style.display = "none";
			break;
		case "MoreLessBlock":
			if(isWYSIWYG) {
				TTCommand("Raw", '<div class="tattermoreless" more=" more.. " less=" less.. ">&nbsp;', "</div>");
				editor.trimContent();
			}
			else
				insertTag("[#M_ more.. | less.. | ", "_M#]");
			break;
		case "Raw":
			value2 = (typeof value2 == "undefined") ? "" : value2;
			if(isWYSIWYG) {
				if(STD.isIE) {
					editor.contentWindow.focus();
					var range = editor.getSelectionRange();
					if(range.pasteHTML)
						range.pasteHTML(value1 + range.htmlText + value2);
					else if(editor.selectedElement) {
						editor.selectedElement.insertAdjacentHTML("beforeBegin", value1);
						editor.selectedElement.insertAdjacentHTML("afterEnd", value2);
					}
				}
				else {
					if(editor.contentWindow.getSelection().focusNode.tagName == "HTML") {
						var range = editor.contentDocument.createRange();
						range.setStart(editor.contentDocument.body,0);
						range.setEnd(editor.contentDocument.body,0);
						var dummyNode = document.createElement("div");
						dummyNode.appendChild(range.extractContents());
						range.insertNode(range.createContextualFragment(value1 + dummyNode.innerHTML + value2));
					}
					else {
						var range = editor.getSelectionRange();
						var dummyNode = document.createElement("div");
						dummyNode.appendChild(range.extractContents());
						range.insertNode(range.createContextualFragment(value1 + dummyNode.innerHTML + value2));
					}
				}
			} else
				insertTag(value1, value2);
	}
    changeButtonStatus(null, null);
	try { editor.contentDocument.body.focus(); } catch(e) { }
}

// IFRAME 내에서 발생하는 이벤트를 처리할 함수
TTEditor.prototype.eventHandler = function(event) {
	var isFunctionalKeyPressed = event.altKey || event.ctrlKey || event.shiftKey;

	if(STD.isIE) {
		event = editor.contentWindow.event;
		event.target = event.srcElement;
	}

	// 마우스를 클릭했을땐 이벤트가 발생한 오브젝트 핸들을 selectedElement 변수에 저장해둔다
	if(event.type == "mousedown") {
		editor.selectedElement = event.target;
		editor.activeButton(event.target);
	}
	else if(event.type != "mouseup")
		editor.activeButton();

	if(editor.selectedElement == null)
		return;

	switch(event.type) {
		case "mouseup":
			var longdesc = editor.selectedElement.getAttribute("longdesc");

			if(new RegExp("^1[CLR]", "").exec(longdesc)) {
				var size = editor.parseImageSize(editor.selectedElement, "array");
				longdesc = longdesc.replace(new RegExp("(width=[\"']?)\\d*", "i"), "$1" + size[0]);
				longdesc = longdesc.replace(new RegExp("(height=[\"']?)\\d*", "i"), "$1" + parseInt(size[0] * editor.propertyCurrentProportion1));
				editor.selectedElement.setAttribute("longDesc", longdesc);
			}
			break;
		case "keypress":
			var range = editor.getSelectionRange();

			if(event.keyCode == 13) {
				if(editor.newLineToParagraph) {
					if(STD.isFirefox && !event.shiftKey) {
						// TODO : put a p tag
					}
				}
				else {
					if(STD.isIE && range.parentElement().tagName != "LI") {
						event.returnValue = false;
						event.cancelBubble = true;
						range.pasteHTML("<br />");
						range.collapse(false);
						range.select();
						return false;
					}
				}
			}
	}
	editorChanged();

	// 이벤트가 발생하면 showProperty 함수에서 태터툴즈 치환자인지 아닌지 판단해, 태터툴즈 치환자일 경우에 속성을 수정할 수 있는 창을 띄워주게 된다
	if(editor.selectedElement && !isFunctionalKeyPressed)
		editor.showProperty(editor.selectedElement);
}

// execCommand 후 불필요하게 삽입된 여백등을 제거해준다
TTEditor.prototype.trimContent = function() {
	var html = this.contentDocument.body.innerHTML;
	html = html.replace(new RegExp("<p>\\s*(<br />)+", "gi"), "<p>");
	html = html.replace(new RegExp("(<br />)+\\s*</p>", "gi"), "</p>");
	html = html.replace(new RegExp("<p></p>", "gi"), "");
	html = html.replace(new RegExp("<li>\\s*<p>", "gi"), "<li>");
	html = html.replace(new RegExp("</p>\\s*</li>", "gi"), "</li>");
	this.contentDocument.body.innerHTML = html;
}

// HTML 문자열 또는 오브젝트에서 오브젝트 크기를 추출
TTEditor.prototype.parseImageSize = function(target, type, mode) {
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
TTEditor.prototype.activeButton = function(node) {
	if(typeof(node) == "undefined") {
		try {
			node = editor.activeButton(editor.getSelectionRange().parentElement());
		} catch(e) {
			try {
				node = editor.activeButton(editor.getSelectionRange().commonAncestorContainer.parentNode);
			} catch(e) {
				return;
			}
		}
	}

	editor.isBold = false;
	editor.isItalic = false;
	editor.isUnderline = false;
	editor.isStrike = false;

	while(typeof(node) != "undefined" && node.tagName && node.tagName.toLowerCase() != "body") {
		switch(node.tagName.toLowerCase()) {
			case "strong":
			case "b":
				editor.isBold = true;
				break;
			case "em":
			case "i":
				editor.isItalic = true;
				break;
			case "u":
			case "ins":
				editor.isUnderline = true;
				break;
			case "del":
			case "strike":
				editor.isStrike = true;
				break;
			default:
				if(node.style.fontWeight.toLowerCase() == "bold")
					editor.isBold = true;
				else if(node.style.fontStyle.toLowerCase() == "italic")
					editor.isItalic = true;
				else if(node.style.textDecoration.toLowerCase() == "underline")
					editor.isUnderline = true;
				else if(node.style.textDecoration.toLowerCase() == "line-through")
					editor.isStrike = true;
		}
		node = node.parentNode;
	}

	if (editor.isBold) {
		getObject("indicatorBold").className = getObject("indicatorBold").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject("indicatorBold").className.match('inactive')) {
			getObject("indicatorBold").className = getObject("indicatorBold").className.replace("active-class", "inactive-class");
		}
	}
	if (editor.isItalic) {
		getObject("indicatorItalic").className = getObject("indicatorItalic").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject("indicatorItalic").className.match('inactive')) {
			getObject("indicatorItalic").className = getObject("indicatorItalic").className.replace("active-class", "inactive-class");
		}
	}
	if (editor.isUnderline) {
		getObject("indicatorUnderline").className = getObject("indicatorUnderline").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject("indicatorUnderline").className.match('inactive')) {
			getObject("indicatorUnderline").className = getObject("indicatorUnderline").className.replace("active-class", "inactive-class");
		}
	}
	if (editor.isStrike) {
		getObject("indicatorStrike").className = getObject("indicatorStrike").className.replace("inactive-class", "active-class");
	} else {
		if (!getObject("indicatorStrike").className.match('inactive')) {
			getObject("indicatorStrike").className = getObject("indicatorStrike").className.replace("active-class", "inactive-class");
		}
	}
}

TTEditor.prototype.getFilenameFromFilelist = function(name) {
	var fileList = getObject("fileList");

	for(var i=0; i<fileList.length; i++)
		if(fileList.options[i].value.indexOf(name) == 0)
			return fileList.options[i].text.substring(0, fileList.options[i].text.lastIndexOf("(") - 1);

	return name;
}

TTEditor.prototype.listChanged = function(id) {
	if(id == "propertyGallery_list") {
		var list = getObject("propertyGallery_list");
		var values = list[list.selectedIndex].value.split("|");
		getObject("propertyGallery_preview").style.display = "block";
		getObject("propertyGallery_preview").innerHTML = '<img src="' + this.propertyFilePath + values[0] + '" width="198" />';
		getObject("propertyGallery_caption").value = values[1];
	}
	else if(id == "propertyiMazing_list") {
		var list = getObject("propertyiMazing_list");
		var values = list[list.selectedIndex].value.split("|");
		getObject("propertyiMazing_preview").style.display = "block";
		getObject("propertyiMazing_preview").innerHTML = '<img src="' + this.propertyFilePath + values[0] + '" width="198" />';
	}
	else if(id == "propertyJukebox_list") {
		var list = getObject("propertyJukebox_list");
		var values = list[list.selectedIndex].value.split("|");
		getObject("propertyJukebox_title").value = values[1];
	}
}

TTEditor.prototype.moveUpFileList = function(id)
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
		editor.setProperty();
		editor.listChanged(id);
	}
}

TTEditor.prototype.moveDownFileList = function(id)
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
		editor.setProperty();
		editor.listChanged(id);
	}
}

// WYSIWYG <-> TEXTAREA 전환
TTEditor.prototype.toggleMode = function() {
	if(this.editMode == "WYSIWYG") {
		this.iframe.style.display = "none";
		this.textarea.style.display = "block";
		this.editMode = "TEXTAREA";
		this.textarea.value = this.html2ttml();
		this.textarea.focus();
	}
	else {
		this.iframe.style.display = "block";
		this.textarea.style.display = "none";
		try { this.contentDocument.designMode = "on"; }
		catch(e) {
			this.iframe.style.display = "none";
			this.textarea.style.display = "block";
			return;
		}
		this.editMode = "WYSIWYG";
		this.contentDocument.body.innerHTML = this.ttml2html();
		try { this.contentDocument.body.focus(); } catch(e) { }
	}
}

// 위지윅 모드에서의 selection을 리턴한다
TTEditor.prototype.getSelectionRange = function() {
	return STD.isIE ? this.contentDocument.selection.createRange() : this.contentWindow.getSelection().getRangeAt(0);
}

// object 태그를 "" 안에 넣을 수 있도록 변형
TTEditor.prototype.objectSerialize = function(str) {
	str = str.replace(new RegExp("<br\\s*/?>", "gi"), "");
	str = str.replace(new RegExp("\r?\n", "g"), "");
	str = str.replace(new RegExp("<", "g"), "__LT__");
	str = str.replace(new RegExp(">", "g"), "__GT__");
	str = str.replace(new RegExp('"', "g"), "__QUOT__");
	return str;
}

TTEditor.prototype.objectUnSerialize = function(str) {
	str = str.replaceAll("__QUOT__", '"');
	str = str.replaceAll("__GT__", ">");
	str = str.replaceAll("__LT__", "<");
	return str;
}

// HTML 문자열에서 attribute="value" 추출
TTEditor.prototype.parseAttribute = function(str, name) {
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
TTEditor.prototype.execCommand = function(cmd, userInterface, value) {
	if(editor.editMode == "WYSIWYG")
		this.contentDocument.execCommand(cmd, userInterface, value);
}

// 파일명으로 이미지파일인지 판단
TTEditor.prototype.isImageFile = function(filename) {
	return new RegExp("\\.(jpe?g|gif|png|bmp)$", "gi").exec(filename);
}

// 파일명으로 미디어/플래시파일인지 판단
TTEditor.prototype.isMediaFile = function(filename) {
	return new RegExp("\\.(swf|mid|mp3|wav|wax|wma|avi|asf|asx|mov|mpe?g|wmv|wm|wvx)$", "gi").exec(filename);
}

// " -> &quot; / ' -> &#39;
TTEditor.prototype.addQuot = function(str) {
	return str.replace(new RegExp('"', "g"), "&quot;").replace(new RegExp("'", "g"), "&#39;");
}

// &quot; -> " / &#39; -> '
TTEditor.prototype.removeQuot = function(str) {
	return str.replace(new RegExp("&quot;", "gi"), '"').replace(new RegExp("&#39;", "g"), "'");
}

// Convert HTML entities
TTEditor.prototype.htmlspecialchars = function(str) {
	return this.addQuot(str.replace(new RegExp("&", "g"), "&amp;").replace(new RegExp("<", "g"), "&lt;").replace(new RegExp(">", "g"), "&gt;"));
}

// Convert HTML entities Reverse
TTEditor.prototype.unHtmlspecialchars = function(str) {
	return this.removeQuot(str.replace(new RegExp("&amp;", "gi"), "&").replace(new RegExp("&lt;", "gi"), "<").replace(new RegExp("&gt;", "gi"), ">"));
}

// 줄바꿈 문자를 BR 태그로
TTEditor.prototype.nl2br = function(str) {
	return str.replace(new RegExp("\r\n", "gi"), "<br />").replace(new RegExp("\r", "gi"), "<br />").replace(new RegExp("\n", "gi"), "<br />");
}

// 스타일 속성, 태그 제거
TTEditor.prototype.removeFormatting = function(str) {
	var styleTags = new Array("b", "strong", "i", "em", "u", "ins", "strike", "del", "font");
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

var editorChanged = function () {
	if ((entryManager != undefined) && (entryManager.saveAuto != undefined))
		entryManager.saveAuto();
}
