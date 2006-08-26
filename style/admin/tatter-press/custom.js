window.addEventListener("load", makeTempSideBar, false);

function makeTempSideBar() {
	// make sidebar
	if (document.getElementById("editor")) {
		tempSidebar = document.createElement("DIV");
		tempSidebar.id = "temp-sidebar";
		document.getElementById("editor").insertBefore(tempSidebar, document.getElementById("textarea-section"));
		
		document.getElementById("temp-sidebar").appendChild(document.getElementById("category-line"));
		
		tempDiv = document.getElementById("property-container").childNodes;
		for (i=0; i<tempDiv.length; i++) {
			if (tempDiv[i] != undefined && tempDiv[i].className == "entry-editor-property") {
				document.getElementById("temp-sidebar").appendChild(tempDiv[i]);
				i--;
			}
		}
		
		removeItselfById("property-section");
		
		if (document.getElementById("tag-location-container")) {
			tempDL = document.getElementById("tag-location-container").childNodes;
			for (i=0; i<tempDL.length; i++) {
				if (tempDL[i] != undefined && tempDL[i].tagName == "DL") {
					document.getElementById("temp-sidebar").appendChild(tempDL[i]);
					i--;
				}
			}
			document.getElementById("editor").removeChild(document.getElementById("taglocal-section"));
		}
		
		tempDL = document.getElementById("power-container").childNodes;
		count = tempDL.length;
		for (i=0; i<count; i++) {
			if (tempDL[i] != undefined && tempDL[i].tagName == "DL" && tempDL[i].id != "permalink-line") {
				document.getElementById("temp-sidebar").appendChild(tempDL[i]);
				i--;
			}
		}
		//document.getElementById("editor").removeChild(document.getElementById("power-section"));
	}
	
	// mod description
	if (document.getElementById("description-logout")) {
		tempLogout = document.getElementById("description-logout").innerHTML;
		tempLogout = tempLogout.replace(/<\/a>/i, '</a>]');
		document.getElementById("description-logout").innerHTML = tempLogout;
		
		tempBlog = document.getElementById("description-blog").innerHTML;
		tempBlog = tempBlog.replace(/<a /i, '[<a ');
		tempBlog = tempBlog.replace(/<\/a>/i, '</a>,');
		document.getElementById("description-blog").innerHTML = tempBlog;
	}
	
	if (document.getElementById("part-setting-filter")) {
		tempDiv = document.createElement("DIV");
		tempDiv.className = "clear";
		tempDiv.style.clear = "both";
		document.getElementById("part-setting-filter").appendChild(tempDiv);
	}
}

window.addEventListener("load", changeLoginStr, false);

function changeLoginStr() {
	tempArray = new Array();
	tempArray = document.getElementsBySelector("#field-box div.button-box a.login-button");
	for (i=0; i<tempArray.length; i++) {
		tempArray[i].innerHTML = '<span class="text">Login &raquo;</span>';
	}
}

window.addEventListener("load", insertDivClear, false);

function insertDivClear() {
	if (document.getElementById("part-trash-filter")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-trash-filter").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
	
	if (document.getElementById("part-statistics-visitor")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-statistics-visitor").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
	
	if (document.getElementById("part-setting-basic")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-setting-basic").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
	
	if (document.getElementById("part-editor")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-editor").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
}

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
				var elements = new Array();
				if (tagName == '*') {
						elements = getAllChildren(currentContext[h]);
				} else if (currentContext[h] != null) {
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

/* That revolting regular expression explained 
/^(\w+)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/
	\---/	\---/\-------------/		\-------/
		|			|				|							|
		|			|				|					The value
		|			|		~,|,^,$,* or =
		|	Attribute 
	Tag
*/