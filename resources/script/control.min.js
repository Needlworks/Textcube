//Suggestion 용 함수들 from locationTag
function ctlUserSuggestFunction_WatchInputBox(id)
{
	try
	{
		var instance = document.getElementById(id).instance;
		if(instance.input.value != instance.typingText)
		{
			instance.typingText = instance.input.value;
   			instance.requestSuggestion();
		}
	}
	catch(e) { }
}

// 서버에서 보내오는 내용을 실행하는 함수
function ctlUserSuggestFunction_showSuggestion()
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
	// TODO : leftover
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
		instance.hideSuggestion();
		return false;
	}

	instance.suggestion.innerHTML = htmlText.toString();
	instance.suggestion.style.display = "block";
	instance.isSuggestionShown = true;

	// 이전에 추가했던 suggestion 노드가 있으면 삭제한다
	try { document.body.removeChild(instance.suggestion) } catch(e) { };
	document.body.appendChild(instance.suggestion);
}

function ctlUserSuggest(container, disable)
{
	this.name = "Textcube Control Panel User Suggestion Object";
	this.copyright = "Tatter Network Foundation";

	this.allowSuggestion = (typeof(disable) == "undefined") ? false : !disable;

	this.isFocused = false;
	this.isSettingValue = false;	// setValue가 짧은 시간에 여러번 실행될때 Safari가 죽어버리는 문제 해결

	this.instance = this;	// requestSuggestion() 함수에서 참조한다
	this.cursor = 0;		// 비동기로 전송되는 스크립트의 짝을 맞추기 위한 커서

	this.inputClassName = "";

	this.isTyping = false;			// input box에 포커스가 있는지 여부
	this.isSuggestionShown = false;	// suggest window가 보여지고 있는지의 여부

	this.typingText = "";			// tcTagFunction_WatchInputBox에서 input box의 값을 감시하기 위한 변수

	this.container = container;		// tag list가 들어갈 container
	this.container.instance = this;

	// suggestion window
	this.suggestion = document.createElement("ul");
	this.suggestion.instance = this;
	this.suggestion.selectedIndex = 0;
	this.suggestion.className = "ctlUserSuggest";
	this.suggestion.style.margin = "0px";
	this.suggestion.style.padding = "0px";
	this.suggestion.style.listStyleType = "none";
	this.suggestion.style.position = "absolute";
	this.suggestion.style.display = "none";
	this.suggestion.style.zIndex = "999";

	this.input = document.createElement("input");
	this.input.instance = this;
	this.input.setAttribute("autocomplete", "off");
	this.input.onblur = function() {
		this.instance.isTyping = false;
		this.instance.typingText = "";
		this.instance.setValue(this.value);
		this.instance.isFocused = false;
	};
	this.input.onfocus = function() {
		if(this.instance.isFocused)
			return;
		else
			this.instance.isFocused = true;

		this.instance.isTyping = true;
		this.instance.typingText = this.value;
		this.instance.requestSuggestion();
	};
	this.input.onkeydown = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    case 13:	// Enter
			    instance.setValue(this.value, false);
			    break;
		    case 9 :    // tab
			    instance.setValue(this.value, false);
			    return event.keyCode;
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
	};

	this.input.onkeypress = function(event) { return preventEnter(event); };
	
	this.input.onkeyup = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    default:
			    return event.keyCode;
	    }
        		
		event.returnValue = false;
		event.cancelBubble = true;

		try { event.preventDefault(); } catch(e) { }

		return false;
	};
	
	// 10ms마다 input box의 값이 변했는지 체크
	setInterval("ctlUserSuggestFunction_WatchInputBox('" + this.container.id + "')", 10);

	this.container.appendChild(this.input);
}

// 입력받은 값으로 리스트를 세팅한다
ctlUserSuggest.prototype.setValue = function(str, focusOnInput)
{
	if(this.isSettingValue)
		return;
	else
		this.isSettingValue = true;
	this.hideSuggestion();
	var input = this.input;
	this.input.value = str;
	this.typingText = str;	
	this.isSettingValue = false;
};

// input box로 포커스를 이동시킨다
ctlUserSuggest.prototype.focusOnInput = function()
{
	this.input.focus();
	this.input.select();

	// 가끔씩 IE에서 포커스가 안가는 문제
	try { setTimeout("document.getElementById('" + this.container.id + "').instance.input.focus()", 1); } catch(e) { }
};

ctlUserSuggest.prototype.getValue = function()
{
	return this.input.value;
};

// suggestion window의 항목을 클릭하면 값을 세팅한다
ctlUserSuggest.prototype.suggestionMouseClick = function(obj)
{
	this.setValue(obj.innerHTML.replace(new RegExp("<\/?em>", "gi"), "").replaceAll("&amp;", "&").match("^[^ ]*"), true);
	this.hideSuggestion();
};

// suggestion window를 숨긴다
ctlUserSuggest.prototype.hideSuggestion = function()
{
	this.isSuggestionShown = false;
	this.suggestion.style.display = "none";
	this.suggestion.selectedIndex = 0;
};

// suggestion window 커서를 위로 이동
ctlUserSuggest.prototype.moveUp = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex--;

		if(this.suggestion.selectedIndex < 1)
			this.suggestion.selectedIndex = this.suggestion.childNodes.length;

		this.highlightRow();
	}
};


// suggestion window 커서를 아래로 이동
ctlUserSuggest.prototype.moveDown = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex++;

		if(this.suggestion.selectedIndex > this.suggestion.childNodes.length)
			this.suggestion.selectedIndex = 1;

		this.highlightRow();
	}
};

// 이동 후에 현재 열의 style class를 변경한다
ctlUserSuggest.prototype.highlightRow = function()
{
	// suggest window가 보이지 않는 상태거나 전송받은 내용이 없으면 제낌
	if(this.isSuggestionShown && this.suggestion.childNodes[0].className != "disabled")
	{
		for(var i=0; i<this.suggestion.childNodes.length; i++)
			this.suggestion.childNodes[i].className = (i == this.suggestion.selectedIndex - 1) ? "hover" : "";

		// 선택된 열의 값을 input box에 채운다
		this.input.value = this.typingText = this.suggestion.childNodes[this.suggestion.selectedIndex-1].innerHTML.replace(new RegExp("<\/?em>", "gi"), "").unhtmlspecialchars().match("^[^ ]*");
	}
};

ctlUserSuggest.prototype.requestSuggestion = function()
{
	var instance = this.instance;

	if(!instance.allowSuggestion)
		return;

	instance.isTyping = true;
	instance.cursor++;

	debug("Request " + instance.cursor);

	var script = document.createElement("script");
	script.setAttribute("src", blogURL + "/control/action/user/suggest/?id=" + instance.container.getAttribute("id") + "&cursor=" + instance.cursor + "&input=" + encodeURIComponent(instance.input.value) + (STD.isSafari ? "&encode=1" : ""));
	document.body.appendChild(script);
};


// cross browser event
ctlUserSuggest.prototype.adjustEventCompatibility = function(event)
{
	if(navigator.appName == "Microsoft Internet Explorer")
	{
		event = window.event;
		event.target = event.srcElement;
	}

	return event;
};

// 이하 잡 유틸들

function getOffsetTop(obj)
{ return obj ? obj.offsetTop + getOffsetTop(obj.offsetParent) : 0; }

function getOffsetLeft(obj)
{ return obj ? obj.offsetLeft + getOffsetLeft(obj.offsetParent) : 0; }

var StringBuffer = function()
{ this.buffer = new Array(); };

StringBuffer.prototype.append=function(str)
{ this.buffer[this.buffer.length] = str; };

StringBuffer.prototype.toString = function()
{ return this.buffer.join(""); };

if(!String.prototype.trim) {
	String.prototype.trim = function()
	{ return this.replace(new RegExp("(^\\s*)|(\\s*$)", "g"), ""); };
}

if(!String.prototype.htmlspecialchars) {
	String.prototype.htmlspecialchars = function()
	{ return this.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll("<", "&gt;"); };
}

if(!String.prototype.unhtmlspecialchars) {
	String.prototype.unhtmlspecialchars = function()
	{ return this.replaceAll("&amp;", "&").replaceAll("&lt;", "<").replaceAll("&gt;", ">"); };
}

var x=0;

function debug(s){try{document.getElementById("debug").innerHTML=++x+")"+s+"<br />"+document.getElementById("debug").innerHTML}catch(e){}}

// 여기까지 Suggestion용 Function

function sendUserAddInfo(name,email) {
	var request = new HTTPRequest(blogURL + "/control/action/user/add/?name=" + name + "&email=" + email);
	request.onSuccess = function() {
		PM.showMessage(_t('새로운 사용자가 추가되었습니다.'), "right", "top");
		ctlRefresh();
	};
	request.onError = function() {
		msg = this.getText("/response/result");
		alert(_t('사용자를 추가하지 못했습니다.') + "\r\nError : " + msg);
	};
	request.send();
}

function sendBlogAddInfo(owner,identify) {
	var request = new HTTPRequest(blogURL + "/control/action/blog/add/?owner="+owner+"&identify="+identify);
	request.onSuccess = function() {
		PM.showMessage(_t('새로운 블로그가 추가되었습니다.'), "right", "top");
		ctlRefresh();
	};
	request.onError = function() {
		msg = this.getText("/response/result");
		alert(_t('블로그를 추가하지 못했습니다.') + "\r\nError : " + msg);
	};
	request.send();
}

function cleanUser(uid) {
	if (!confirm(_t('모든 글과 블로그가 관리자의 소유로 옮겨집니다.') + '\t\n\n' + _t('되돌릴 수 없습니다.') + '\t\n\n' + _t('계속 진행하시겠습니까?'))) return false;
	var request = new HTTPRequest(blogURL + "/control/action/user/delete/?userid="+uid);
	request.onSuccess = function() {
		PM.removeRequest(this);
		window.location.href = '../';
	};
	request.onError = function() {
		PM.removeRequest(this);
		msg = this.getText("/response/result");
		alert(_t('사용자 삭제에 실패하였습니다.') + "\r\nError : " + msg);
	};
	PM.addRequest(request, _t("사용자 삭제중"));
	request.send();
}

function ctlRefresh() {
	window.location.reload();
}
