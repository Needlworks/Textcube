/* ***************************************************************************** *\
	관리자 화면 제작시 모든 자바스크립트를 제어하기 위한 기본 오브젝트입니다.
	이 오브젝트의 초기함수배열에 자신의 함수를 등록함으로써 자바스크립트를
	추가하실 수 있습니다.
	
	이 오브젝트 소스의 원본 : Jay G님의 http://www.jayg.org
\* ***************************************************************************** */

// Basic Object ----------------------------------------------------------------- /
var baseObjectByTnF = new Object();
var baseObjectByTnF_init_funcs = new Array();
var baseObjectByTnF_finish_funcs = new Array();

baseObjectByTnF =
{
	init: function()
	{
		for (i=0; i<baseObjectByTnF_init_funcs.length; i++)
		{
			baseObjectByTnF_init_funcs[i]();
		}
	},
	
	finish: function()
	{
		for (i=0; i<baseObjectByTnF_finish_funcs.length; i++)
		{
			baseObjectByTnF_fini_funcs[i]();
		}
		
			if (document.all && window.attachEvent)
			{
				elProps = ['data', 'onmouseover', 'onmouseout', 'onclick'];
				all = document.all;
				
				for (i=0, el; el=all[i]; i++)
				{
					for (j=0, elProp; elProp=elProps[j]; j++)
					{
						el[elProp] = null;
					}
				}
			}
		}
	}
};

Event.observe(window, 'load', baseObjectByTnF.init);
Event.observe(window, 'unload', baseObjectByTnF.finish);

baseObjectByTnF_init_funcs.push(function() { baseObjectByTnF.browserDetector.detect(); });
//baseObjectByTnF_finish_funcs.push(function() { baseObjectByTnF.widgets.styleSwitcher.finish(); });

// Browser Detector ------------------------------------------------------------- /
baseObjectByTnF.browserDetector =
{
	browser : null,
	version : null,
	os      : null,
	agent   : navigator.userAgent.toLowerCase(),
	place   : null,
	str     : null,
	css     : false,

	detect: function()
	{
		if (this.checkIt('konqueror'))
		{
			this.browser = 'Konqueror';
			this.os      = 'Linux';
		}
		else if (this.checkIt('safari'))
		{
			this.browser = 'Safari';
		}
		else if (this.checkIt('omniweb'))
		{
			this.browser = 'OmniWeb';
		}
		else if (this.checkIt('opera'))
		{
			this.browser = 'Opera';
		}
		else if (this.checkIt('webtv'))
		{
			this.browser = 'WebTV';
		}
		else if (this.checkIt('icab'))
		{
			this.browser = 'iCab';
		}
		else if (this.checkIt('msie'))
		{
			this.browser = 'Internet Explorer';
		}
		else if (this.checkIt('firefox'))
		{
			this.browser = 'Firefox';
		}
		else if (this.checkIt('netscape'))
		{
			this.browser = 'Netscape';
		}
		else if (!this.checkIt('compatible'))
		{
			var rev_offset = this.agent.indexOf('rv:') + 1;

			this.browser = 'Mozilla';
			this.version = this.agent.substring(rev_offset, rev_offset + 3);
		}
		else
		{
			this.browser = 'Unknown';
		}

		if (!this.version)
		{
			this.version = this.agent.charAt(this.place + this.str.length);
		}

		if (!this.os)
		{
			if      (this.checkIt('linux')) this.os = "Linux";
			else if (this.checkIt('x11'))   this.os = "Unix";
			else if (this.checkIt('mac'))   this.os = "Mac"
			else if (this.checkIt('win'))   this.os = "Windows"
			else                            this.os = "Unknown";
		}
	},
	
	checkIt: function(string)
	{
		this.place = this.agent.indexOf(string) + 1;
		this.str = string;

		return this.place;
	}
};

/* Cookie ******************************************************************/
baseObjectByTnF.cookie =
{ 
	create: function(name, value, days)
	{
		expires = '';
		if ('undefined' != typeof (days))
		{
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			var expires = '; expires='+date.toGMTString();
		}

		document.cookie = name+'='+value+expires+'; path=/';
	},
		
	read: function(name)
	{
		var name   = name+'=';
		var values = document.cookie.split(';');
			
		for (var i = 0; i < values.length; i++)
		{
			var value = values[i];
		
			while (' ' == value.charAt(0))
			{
				value = value.substring(1, value.length);
			}

			if (0 == value.indexOf(name))
			{
				return value.substring(name.length, value.length);
			}
		}
		
		return null;
	} 
};

/* document.getElementsBySelector(selector)
	 - returns an array of element objects from the current document
		 matching the CSS selector. Selectors can contain element names, 
		 class names and ids and can be nested. For example:

			 elements = document.getElementsBySelect('div#main p a.external')

		 Will return an array of all 'a' elements with 'external' in their 
		 class attribute that are contained inside 'p' elements that are 
		 contained inside the 'div' element which has id="main"

	 New in version 0.4: Support for CSS2 and CSS3 attribute selectors:
	 See http://www.w3.org/TR/css3-selectors/#attribute-selectors

	 Version 0.4 - Simon Willison, March 25th 2003
	 -- Works in Phoenix 0.5, Mozilla 1.3, Opera 7, Internet Explorer 6, Internet Explorer 5 on Windows
	 -- Opera 7 fails 
*/

function getAllChildren(e) {
	// Returns all children of element. Workaround required for IE5/Windows. Ugh.
	return e.all ? e.all : e.getElementsByTagName('*');
}

document.getElementsBySelector = function(selector) {
	// Attempt to fail gracefully in lesser browsers
	if (!document.getElementsByTagName) {
		return new Array();
	}
	// Split selector in to tokens
	var tokens = selector.split(' ');
	var currentContext = new Array(document);
	for (var i = 0; i < tokens.length; i++) {
		token = tokens[i].replace(/^\s+/,'').replace(/\s+$/,'');;
		if (token.indexOf('#') > -1) {
			// Token is an ID selector
			var bits = token.split('#');
			var tagName = bits[0];
			var id = bits[1];
			var element = document.getElementById(id);
			if (tagName && element.nodeName.toLowerCase() != tagName) {
				// tag with that ID not found, return false
				return new Array();
			}
			// Set currentContext to contain just this element
			currentContext = new Array(element);
			continue; // Skip to next token
		}
		if (token.indexOf('.') > -1) {
			// Token contains a class selector
			var bits = token.split('.');
			var tagName = bits[0];
			var className = bits[1];
			if (!tagName) {
				tagName = '*';
			}
			// Get elements matching tag, filter them for class selector
			var found = new Array;
			var foundCount = 0;
			for (var h = 0; h < currentContext.length; h++) {
				var elements;
				if (tagName == '*') {
						elements = getAllChildren(currentContext[h]);
				} else {
						elements = currentContext[h].getElementsByTagName(tagName);
				}
				for (var j = 0; j < elements.length; j++) {
					found[foundCount++] = elements[j];
				}
			}
			currentContext = new Array;
			var currentContextIndex = 0;
			for (var k = 0; k < found.length; k++) {
				if (found[k].className && found[k].className.match(new RegExp('\\b'+className+'\\b'))) {
					currentContext[currentContextIndex++] = found[k];
				}
			}
			continue; // Skip to next token
		}
		// Code to deal with attribute selectors
		if (token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {
			var tagName = RegExp.$1;
			var attrName = RegExp.$2;
			var attrOperator = RegExp.$3;
			var attrValue = RegExp.$4;
			if (!tagName) {
				tagName = '*';
			}
			// Grab all of the tagName elements within current context
			var found = new Array;
			var foundCount = 0;
			for (var h = 0; h < currentContext.length; h++) {
				var elements;
				if (tagName == '*') {
						elements = getAllChildren(currentContext[h]);
				} else {
						elements = currentContext[h].getElementsByTagName(tagName);
				}
				for (var j = 0; j < elements.length; j++) {
					found[foundCount++] = elements[j];
				}
			}
			currentContext = new Array;
			var currentContextIndex = 0;
			var checkFunction; // This function will be used to filter the elements
			switch (attrOperator) {
				case '=': // Equality
					checkFunction = function(e) { return (e.getAttribute(attrName) == attrValue); };
					break;
				case '~': // Match one of space seperated words 
					checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('\\b'+attrValue+'\\b'))); };
					break;
				case '|': // Match start with value followed by optional hyphen
					checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('^'+attrValue+'-?'))); };
					break;
				case '^': // Match starts with value
					checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) == 0); };
					break;
				case '$': // Match ends with value - fails with "Warning" in Opera 7
					checkFunction = function(e) { return (e.getAttribute(attrName).lastIndexOf(attrValue) == e.getAttribute(attrName).length - attrValue.length); };
					break;
				case '*': // Match ends with value
					checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) > -1); };
					break;
				default :
					// Just test for existence of attribute
					checkFunction = function(e) { return e.getAttribute(attrName); };
			}
			currentContext = new Array;
			var currentContextIndex = 0;
			for (var k = 0; k < found.length; k++) {
				if (checkFunction(found[k])) {
					currentContext[currentContextIndex++] = found[k];
				}
			}
			// alert('Attribute Selector: '+tagName+' '+attrName+' '+attrOperator+' '+attrValue);
			continue; // Skip to next token
		}
		// If we get here, token is JUST an element (not a class or ID selector)
		tagName = token;
		var found = new Array;
		var foundCount = 0;
		for (var h = 0; h < currentContext.length; h++) {
			var elements = currentContext[h].getElementsByTagName(tagName);
			for (var j = 0; j < elements.length; j++) {
				found[foundCount++] = elements[j];
			}
		}
		currentContext = found;
	}
	return currentContext;
}