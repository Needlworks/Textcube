/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// 입력창을 10ms마다 체크하면서 값이 변했으면 request를 보낸다.
// 파이어폭스에서는 한글을 입력할때 keydown 이벤트가 발생하지 않기 때문에
// 값이 변하는지 계속 보고있어야 한다.
function eolinLocationTagFunction_WatchInputBox(id)
{
	try
	{
		var instance = document.getElementById(id).instance;
		if (instance.input.value.charAt(instance.input.value.length - 1) == '/') {
            if (input.value.length == 1) input.value = '';
		    else instance.setValue(instance.input.value, true);
		    return;
		}
		if(instance.input.value != instance.typingText)
		{
			instance.typingText = instance.input.value;
   			instance.requestSuggestion();
		}
	}
	catch(e) { }
}

// 서버에서 보내오는 필터로 로컬 location suggestion
function eolinLocationFunction_showLocalSuggestion(id, cursor, filter)
{
	// Container의 ID를 통해 instance를 가져온다
	try { var instance = document.getElementById(id).instance; }
	catch(e) { return; }

	// 보내온 cursor와 현재 cursor가 같지 않으면 필요없는 데이터이므로 버린다
	// 텍스트 박스를 벗어난 후에 도착한 데이터도 버린다
	if(instance.cursor != cursor || !instance.isTyping)
		return;

	var input = instance.input;

	// 편집중인 내용이 빈 상태면 suggestion 윈도우 감추고 리턴
	/*if(input.value.trim() == "")
	{
		instance.hideSuggestion();
		return;
	}*/

	var xmlhttp = createHttp();

	if(xmlhttp)
	{
		xmlhttp.open("GET", blogURL + "/locationSuggest/?id=" + id + "&cursor=" + cursor + "&filter=" + (STD.isSafari ? filter : encodeURIComponent(filter)), true);
		xmlhttp.onreadystatechange = function()
		{
			if(xmlhttp.readyState == 4)
			{
				try {
					var doc = xmlhttp.responseXML;
					var root = doc.getElementsByTagName("response")[0];
					var id = root.getAttribute("id");
					var cursor = root.getAttribute("cursor");

					var instance = document.getElementById(id).instance;

					if(instance.cursor == cursor)
					{
						var locations = new Array();

						var locationItems = root.getElementsByTagName("location");
						if(!instance.allowEolinSuggestion && locationItems.length == 0) {
							instance.hideSuggestion();
							return;
						}

						for(var i=0; i<locationItems.length; i++) {
							value = locationItems[i].lastChild.nodeValue.split('/');
							for (var j = 0; j != -1; j = filter.indexOf('/', j + 1)) {
								if (j == 0) {
									continue;
								}
								value.shift();
							}
							locations[locations.length] = value.join('/');
						}

						// 중복될 항목들을 미리 제거
						for(var i=0; i<locations.length; i++)
						{
							for(var j=0; j<instance.suggestion.childNodes.length; j++)
							{
								if(locations[i] == instance.suggestion.childNodes[j].innerHTML.replace(new RegExp("<\/?em>", "gi"), ""))
								{
									instance.suggestion.removeChild(instance.suggestion.childNodes[j]);
									break;
								}
							}
						}

						var htmlText = new StringBuffer();

						for(var i=0; i<locations.length; i++)
						{
							htmlText.append("<li onmouseover=\"this.className='hover'\" onmouseout=\"this.className=''\" onmousedown=\"this.parentNode.instance.suggestionMouseClick(this)\" style=\"background-color: #ccb\"><em>");
							htmlText.append(locations[i].substring(0, input.value.length).htmlspecialchars().replaceAll("&amp;", "&"));
							htmlText.append("</em>");
							htmlText.append(locations[i].substring(input.value.length).htmlspecialchars().replaceAll("&amp;", "&"));
							htmlText.append("</li>");
						}

						if(instance.allowEolinSuggestion)
							htmlText.append(instance.suggestion.innerHTML);
							
						instance.suggestion.innerHTML = htmlText.toString();

						if(!instance.allowEolinSuggestion || instance.input.value.trim() == "") {
							instance.suggestion.style.left = getOffsetLeft(input) + "px";
							instance.suggestion.style.top = getOffsetTop(input) + input.offsetHeight + "px";
							instance.suggestion.style.display = "block";
							instance.isSuggestionShown = true;

							try {
								document.getElementById("previewSelected").style.visibility = "hidden";
								document.getElementById("TCfilelist").style.visibility = "hidden";
							} catch(e) { }
							try { document.body.removeChild(instance.suggestion) } catch(e) { };
							document.body.appendChild(instance.suggestion);
						}

						while(instance.suggestion.childNodes.length > 10)
							instance.suggestion.removeChild(instance.suggestion.childNodes[instance.suggestion.childNodes.length-1]);
					}
				} catch(e) { }
			}
		}
		xmlhttp.send(null);
		delete xmlhttp;
	}
}

// 서버에서 보내오는 내용을 실행하는 함수
function eolinLocationTagFunction_showSuggestion()
{
	// Container의 ID를 통해 instance를 가져온다
	try { var instance = document.getElementById(arguments[0]).instance; }
	catch(e) { return; }

	debug("<span style=\"color: red\">Received " + instance.cursor + "</span>");

	// 보내온 cursor와 현재 cursor가 같지 않으면 필요없는 데이터이므로 버린다
	// 텍스트 박스를 벗어난 후에 도착한 데이터도 버린다
	if(instance.cursor != arguments[1] || !instance.isTyping)
		return;

	// input box의 위치를 구해서 suggestion window의 위치를 결정한다
	// TODO : suggestion window가 표시되면서 스크롤바가 생기면 위치를 다시 잡아줘야 한다
	instance.suggestion.style.left = getOffsetLeft(instance.input) + "px";
	instance.suggestion.style.top = getOffsetTop(instance.input) + instance.input.offsetHeight + "px";

	// suggestion window의 깜빡임을 방지하기 위해 dom을 이용한 삭제/삽입 대신 innerHTML을 이용한다
	var htmlText = new StringBuffer();

	// 전송된 결과가 있을때
	if(arguments[2] == 0)
	{
		for(var i=3; i<arguments.length; i++)
		{
			arguments[i] = arguments[i].replaceAll("&quot;", '"');
			if(STD.isSafari)
				arguments[i] = decodeURIComponent(arguments[i]);
			htmlText.append("<li onmouseover=\"this.className='hover'\" onmouseout=\"this.className=''\" onmousedown=\"this.parentNode.instance.suggestionMouseClick(this)\">");
			htmlText.append(arguments[i].replace(new RegExp("(" + instance.input.value + ")", "gi"), "<em>$1</em>"));
			htmlText.append("</li>");
		}
	}
	else
	{
		var message1 = arguments[3];
		var message2 = arguments[4];
		var message3 = arguments[5];

		if(STD.isSafari) {
			message1 = decodeURIComponent(message1);
			message2 = decodeURIComponent(message2);
			message3 = decodeURIComponent(message3);
		}

		if(instance.locationList.childNodes.length == 1)
			htmlText.append("<li class=\"disabled\"><em>" + instance.input.value + "</em> - " + message1 + "<br />" + message2 + "</li>");
		else if(instance.input.value.trim() == "")
			htmlText.append("<li class=\"disabled\">" + message3 + "</li>");
		else
			htmlText.append("<li class=\"disabled\"><em>" + instance.input.value + "</em> - " + message1 + "</li>");
	}

	/* TODO : temporary code */
/*	try {
		document.getElementById("previewSelected").style.visibility = "hidden";
		document.getElementById("TCfilelist").style.visibility = "hidden";
	} catch(e) { }*/

	instance.suggestion.innerHTML = htmlText.toString();
	instance.suggestion.style.display = "block";
	instance.isSuggestionShown = true;

	// 이전에 추가했던 suggestion 노드가 있으면 삭제한다
	try { document.body.removeChild(instance.suggestion) } catch(e) { };
	document.body.appendChild(instance.suggestion);
}

function LocationTag(container, language, disable)
{
	this.name = "Eolin Location Tag Object";
	this.copyright = "Tatter & Company";

	this.allowEolinSuggestion = (typeof(disable) == "undefined") ? false : !disable;

	this.isFocused = false;
	this.isSettingValue = false;	// setValue가 짧은 시간에 여러번 실행될때 Safari가 죽어버리는 문제 해결

	this.instance = this;	// requestSuggestion() 함수에서 참조한다
	this.cursor = 0;		// 비동기로 전송되는 스크립트의 짝을 맞추기 위한 커서

	this.inputClassName = "";

	this.language = "ko";
	if(typeof language != "undefined")
		this.language = language;

	this.isTyping = false;			// input box에 포커스가 있는지 여부
	this.isSuggestionShown = false;	// suggest window가 보여지고 있는지의 여부

	this.typingText = "";			// eolinTagFunction_WatchInputBox에서 input box의 값을 감시하기 위한 변수

	this.container = container;		// tag list가 들어갈 container
	this.container.instance = this;

	// suggestion window
	this.suggestion = document.createElement("ul");
	this.suggestion.instance = this;
	this.suggestion.selectedIndex = 0;
	this.suggestion.className = "eolinSuggest";
	this.suggestion.style.margin = "0px";
	this.suggestion.style.padding = "0px";
	this.suggestion.style.listStyleType = "none";
	this.suggestion.style.position = "absolute";
	this.suggestion.style.display = "none";
	this.suggestion.style.zIndex = "999";

	this.input = document.createElement("input");
	this.input.instance = this;
	this.input.className = this.inputClassName;
	this.input.setAttribute("autocomplete", "off");
	this.input.onblur = function() {
		this.instance.isTyping = false;
		this.instance.typingText = "";
		this.instance.setValue(this.value);
		this.instance.isFocused = false;
	}
	this.input.onfocus = function() {
		if(this.instance.isFocused)
			return;
		else
			this.instance.isFocused = true;

		this.instance.isTyping = true;
		this.instance.typingText = this.value;
		this.instance.requestSuggestion();
	}
	this.input.onkeydown = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    case 8:		// BackSpace
			    if(this.value == "")
				    instance.moveBack();
			    else
				    return event.keyCode;
			    break;
		    case 13:	// Enter
			    instance.setValue(this.value, true);
			    break;
			case 191:   // slash
				if(!event.shiftKey)
					instance.setValue(this.value, true);
				else
					return event.keyCode;
				break;
		    case 9 :    // tab
		        if (this.value.trim() == "") return event.keyCode;
			    instance.setValue(this.value, true);
			    break;
		    case 27:	// ESC
			    instance.hideSuggestion();
			    break;
		    case 38:	// Key Up
			    instance.moveUp();
			    break;
		    case 40:	// Key Down
			    instance.moveDown();
			    break;
		    default:
			    return event.keyCode;
	    }
        		
		event.returnValue = false;
		event.cancelBubble = true;

		try { event.preventDefault(); } catch(e) { }

		return false;
	}

	this.input.onkeypress = function(event) { return preventEnter(event); };
	
	this.input.onkeyup = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    case 191:   // slash
			    instance.setValue(this.value, true);
			    break;
		    default:
			    return event.keyCode;
	    }
        		
		event.returnValue = false;
		event.cancelBubble = true;

		try { event.preventDefault(); } catch(e) { }

		return false;
	}
	
	// 10ms마다 input box의 값이 변했는지 체크
	setInterval("eolinLocationTagFunction_WatchInputBox('" + this.container.id + "')", 10);

	// location list
	this.locationList = document.createElement("ul");
	this.locationList.instance = this;

	var listItem = document.createElement("li");
	listItem.className = "lastChild";
	listItem.appendChild(this.input);

	this.locationList.appendChild(listItem);

	this.container.appendChild(this.locationList);
}

// 지역태그 string을 리턴 (예: /대한민국/서울/강남역)
LocationTag.prototype.getValues = function()
{
	var locations = new Array();

	for(var i=0; i<this.locationList.childNodes.length-1; i++)
		locations[i] = this.locationList.childNodes[i].innerHTML.trim().unhtmlspecialchars();

	return "/" + locations.join("/");
}

// 입력받은 값으로 리스트를 세팅한다
LocationTag.prototype.setValue = function(str, focusOnInput)
{
	if(this.isSettingValue)
		return;
	else
		this.isSettingValue = true;

	this.hideSuggestion();

	var locations = this.stringToLocation(str);

	var input = this.input;

	this.input.parentNode.parentNode.removeChild(this.input.parentNode);

	for(var i=0; i<locations.length; i++)
	{
		var listItem = document.createElement("li");
		listItem.onclick = this.locationListMouseClick;
		listItem.appendChild(document.createTextNode(locations[i]));

		this.locationList.appendChild(listItem);
	}

	var listItem = document.createElement("li");
	listItem.appendChild(this.input);

	this.locationList.appendChild(listItem);

	this.locationList.lastChild.className = "lastChild";
	
	this.input.value = "";
	this.typingText = "";

	if(focusOnInput)
	{
		this.focusOnInput();

		if(navigator.userAgent.indexOf("Gecko") != -1)
			this.requestSuggestion();
	}

	this.isSettingValue = false;
}

// input box로 포커스를 이동시킨다
LocationTag.prototype.focusOnInput = function()
{
	this.input.focus();
	this.input.select();

	// 가끔씩 IE에서 포커스가 안가는 문제
	try { setTimeout("document.getElementById('" + this.container.id + "').instance.input.focus()", 1); } catch(e) { }
}

LocationTag.prototype.setInputClassName = function(str)
{
	this.inputClassName = str;
	this.input.className = str;
}

// suggestion window의 항목을 클릭하면 값을 세팅한다
LocationTag.prototype.suggestionMouseClick = function(obj)
{
	this.setValue(obj.innerHTML.replace(new RegExp("<\/?em>", "gi"), "").replaceAll("&amp;", "&"), true);
	this.hideSuggestion();
}

// location list를 마우스로 클릭하면 뒤쪽의 값을 모두 지우고 현재 위치에 input box를 놓는다
LocationTag.prototype.locationListMouseClick = function(event)
{
	var instance = this.parentNode.instance;

	var input = instance.input;
	input.value = this.innerHTML.unhtmlspecialchars();

	while(this.nextSibling)
		this.parentNode.removeChild(this.nextSibling);

	this.typingText = "";
	this.innerHTML = "";
	this.onclick = null;
	this.appendChild(input);

	instance.focusOnInput();
}

// suggestion window를 숨긴다
LocationTag.prototype.hideSuggestion = function()
{
	this.isSuggestionShown = false;
	this.suggestion.style.display = "none";
	this.suggestion.selectedIndex = 0;

	/* TODO : temporary code */
/*	try {	// 원래는 파일 업로드 상자의 z-index 의 IE 해석 오류때문에 추가되어 있는 부분인데, 태그가 파일 업로드 아래로 내려오면서 불필요해졌음.
		document.getElementById("previewSelected").style.visibility = "visible";
		document.getElementById("TCfilelist").style.visibility = "visible";
	} catch(e) { }*/
}

// suggestion window 커서를 위로 이동
LocationTag.prototype.moveUp = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex--;

		if(this.suggestion.selectedIndex < 1)
			this.suggestion.selectedIndex = this.suggestion.childNodes.length;

		this.highlightRow();
	}
}

// location list의 이전 항목으로 이동
LocationTag.prototype.moveBack = function()
{
	var prevNode = this.input.parentNode.previousSibling;

	if(this.locationList.childNodes.length > 1 && prevNode)
	{
		this.hideSuggestion();

		var text = prevNode.innerHTML.unhtmlspecialchars();

		prevNode.parentNode.removeChild(prevNode);

		this.locationList.lastChild.className = "lastChild";
		this.input.value = text;
	}
}

// suggestion window 커서를 아래로 이동
LocationTag.prototype.moveDown = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex++;

		if(this.suggestion.selectedIndex > this.suggestion.childNodes.length)
			this.suggestion.selectedIndex = 1;

		this.highlightRow();
	}
}

// 이동 후에 현재 열의 style class를 변경한다
LocationTag.prototype.highlightRow = function()
{
	// suggest window가 보이지 않는 상태거나 전송받은 내용이 없으면 제낌
	if(this.isSuggestionShown && this.suggestion.childNodes[0].className != "disabled")
	{
		for(var i=0; i<this.suggestion.childNodes.length; i++)
			this.suggestion.childNodes[i].className = (i == this.suggestion.selectedIndex - 1) ? "hover" : "";

		// 선택된 열의 값을 input box에 채운다
		this.input.value = this.typingText = this.suggestion.childNodes[this.suggestion.selectedIndex-1].innerHTML.replace(new RegExp("<\/?em>", "gi"), "").unhtmlspecialchars();
	}
}

// 입력중인 input box의 값까지 붙여서 전체 경로를 리턴한다
LocationTag.prototype.getPath = function()
{
	var path = this.getValues();

	return path + ((path == "/") ? "" : "/") + this.input.value;
}

// script의 src를 변경해 서버로부터 tag 리스트를 전송받는다
LocationTag.prototype.requestSuggestion = function()
{
	var instance = this.instance;

	if(!instance.allowEolinSuggestion || (instance.input.value.trim() == "")) {
		eolinLocationFunction_showLocalSuggestion(instance.container.getAttribute("id"), instance.cursor, this.getPath());
		return;
	}

	instance.isTyping = true;
	instance.cursor++;

	debug("Request " + instance.cursor);

	var script = document.createElement("script");
//	script.setAttribute("id", "eolinLocationScript");
	script.setAttribute("src", "http://suggest.eolin.com/location/script/?id=" + instance.container.getAttribute("id") + "&cursor=" + instance.cursor + "&language=" + instance.language + "&path=" + encodeURIComponent(instance.getPath()) + (STD.isSafari ? "&encode=1" : ""));
//	if(document.getElementById("eolinLocationScript"))
//		document.body.removeChild(document.getElementById("eolinLocationScript"));
	document.body.appendChild(script);
}

// 입력받은 string을 /로 잘라 배열로 리턴
LocationTag.prototype.stringToLocation = function(str)
{
	try
	{
		var ret = new Array();

		var locations = str.split("/");

		for(var i=0; i<locations.length; i++)
		{
			if(locations[i].trim() != "")
				ret[ret.length] = locations[i].trim();
		}

		return ret;
	}
	catch(e)
	{ return new Array(); }
}

// cross browser event
LocationTag.prototype.adjustEventCompatibility = function(event)
{
	if(navigator.appName == "Microsoft Internet Explorer")
	{
		event = window.event;
		event.target = event.srcElement;
	}

	return event;
}

// 이하 잡 유틸들

function getOffsetTop(obj)
{ return obj ? obj.offsetTop + getOffsetTop(obj.offsetParent) : 0; }

function getOffsetLeft(obj)
{ return obj ? obj.offsetLeft + getOffsetLeft(obj.offsetParent) : 0; }

var StringBuffer = function()
{ this.buffer = new Array(); }

StringBuffer.prototype.append=function(str)
{ this.buffer[this.buffer.length] = str; }

StringBuffer.prototype.toString = function()
{ return this.buffer.join(""); }

if(!String.prototype.trim) {
	String.prototype.trim = function()
	{ return this.replace(new RegExp("(^\\s*)|(\\s*$)", "g"), ""); }
}

if(!String.prototype.htmlspecialchars) {
	String.prototype.htmlspecialchars = function()
	{ return this.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll("<", "&gt;"); }
}

if(!String.prototype.unhtmlspecialchars) {
	String.prototype.unhtmlspecialchars = function()
	{ return this.replaceAll("&amp;", "&").replaceAll("&lt;", "<").replaceAll("&gt;", ">"); }
}

var x=0;
function debug(s){try{document.getElementById("debug").innerHTML=++x+")"+s+"<br />"+document.getElementById("debug").innerHTML}catch(e){}}
