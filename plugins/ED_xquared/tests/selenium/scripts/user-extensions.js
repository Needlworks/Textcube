// JSSpec specific functions
Selenium.prototype.doOpenSpec = function(specName) {
	return this.doOpen("../specs/" + specName + ".html");
}
Selenium.prototype.getSpecProgress = function() {
	return parseInt(this.page().findElement("progress").innerHTML);
};
Selenium.prototype.getSpecResult = function() {
	return this.page().findElement("title").className;
};



// Editor specific functions
Selenium.prototype.doSetEditMode = function(mode) {
	this.page().getCurrentWindow().xed.setEditMode(mode, true);
};
Selenium.prototype.doSetEditorConfig = function(name, value) {
	this.page().getCurrentWindow().xed.config[name] = value;
};
Selenium.prototype.doToggleSourceAndWysiwygMode = function() {
	this.page().getCurrentWindow().xed.toggleSourceAndWysiwygMode();
};
Selenium.prototype.getEditMode = function() {
	return this.page().getCurrentWindow().xed.getCurrentEditMode();
};
Selenium.prototype.doSetRawWysiwygContent = function(html) {
	this.page().getCurrentWindow().xed.getBody().innerHTML = html.replace(/\[/g, "<").replace(/\]/g, ">");
};
Selenium.prototype.getRawWysiwygContent = function() {
	return this.page().getCurrentWindow().xed.getBody().innerHTML.normalizeHtml().replace(/</g, "[").replace(/>/g, "]");
};
Selenium.prototype.doSetRawSourceContent = function(html) {
	this.page().getCurrentWindow().xed.sourceEditorTextarea.value = html.replace(/\[/g, "<").replace(/\]/g, ">");
};
Selenium.prototype.getRawSourceContent = function() {
	return this.page().getCurrentWindow().xed.sourceEditorTextarea.value.normalizeHtml().replace(/</g, "[").replace(/>/g, "]");
};
Selenium.prototype.doSelectElementById = function(id) {
	var xed = this.page().getCurrentWindow().xed;
	xed.focus();
	var element = xed.rdom.$(id);
	xed.rdom.selectElement(element);
};
Selenium.prototype.doExecute = function(commandStr) {
	eval("this.page().getCurrentWindow().xed." + commandStr);
};



// Generic functions
Selenium.prototype.getProperty = function(locator) {
    // Split into locator + attributeName
    var propertyPos = locator.lastIndexOf("@");
    var elementLocator = locator.slice(0, propertyPos);
    var propertyName = locator.slice(propertyPos + 1);

    // Find the element.
    var element = this.page().findElement(elementLocator);

    // Get the property value.
    var propertyValue = element[propertyName];

    return propertyValue ? propertyValue.toString() : null;
};
Selenium.prototype.doSetProperty = function(locator, value) {
	// Split into locator + attributeName
	var propertyPos = locator.lastIndexOf("@");
	var elementLocator = locator.slice(0, propertyPos);
	var propertyName = locator.slice(propertyPos + 1);
	
	// Find the element.
	var element = this.page().findElement(elementLocator);
	
	// Set the property value.
	element[propertyName] = value;
};
Selenium.prototype.doSetPropertyAsHtml = function(locator, value) {
	this.doSetProperty(locator, value.replace("[", "<").replace("]", ">"));
};
Selenium.prototype.getPropertyAsHtml = function(locator) {
	return this.getProperty(locator).normalizeHtml().replace(/</g, "[").replace(/>/g, "]");
};
Selenium.prototype.getStyleDisplay = function(locator) {
    var element = this.browserbot.findElement(locator);
    var display = this.findEffectiveStyleProperty(element, "display");
    return display;
};

String.prototype.normalizeHtml = function() {
	var html = this;
	
	// Uniformize quotation, turn tag names and attribute names into lower case
	html = html.replace(/<(\/?)(\w+)([^>]*?)>/img, function(str, closingMark, tagName, attrs) {
		var sortedAttrs = Selenium.sortHtmlAttrs(Selenium.correctHtmlAttrQuotation(attrs).toLowerCase())
		return "<" + closingMark + tagName.toLowerCase() + sortedAttrs + ">"
	});
	
	// validation self-closing tags
	html = html.replace(/<(br|hr|img)([^>]*?)>/mg, function(str, tag, attrs) {
		return "<" + tag + attrs + " />";
	});
	
	// append semi-colon at the end of style value
	html = html.replace(/style="(.*?)"/mg, function(str, styleStr) {
		styleStr = Selenium.sortStyleEntries(styleStr.strip()); // for Safari
		if(styleStr.charAt(styleStr.length - 1) != ';') styleStr += ";"
		
		return 'style="' + styleStr + '"'
	});
	
	// sort style entries
	
	// remove empty style attributes
	html = html.replace(/ style=";"/mg, "");
	
	// remove new-lines
	html = html.replace(/\r/mg, '');
	html = html.replace(/\n/mg, '');
		
	return html;
}
Selenium.correctHtmlAttrQuotation = function(html) {
	html = html.replace(/(\w+)=['"]([^'"]+)['"]/mg,function (str, name, value) {return name + '=' + '"' + value + '"';});
	html = html.replace(/(\w+)=([^ '"]+)/mg,function (str, name, value) {return name + '=' + '"' + value + '"';});
	html = html.replace(/'/mg, '"');
	
	return html;
}
Selenium.sortHtmlAttrs = function(html) {
	var attrs = [];
	html.replace(/((\w+)="[^"]+")/mg, function(str, matched) {
		attrs.push(matched);
	});
	return attrs.length == 0 ? "" : " " + attrs.sort().join(" ");
}
Selenium.sortStyleEntries = function(styleText) {
	var entries = styleText.split(/; /);
	return entries.sort().join("; ");
}
