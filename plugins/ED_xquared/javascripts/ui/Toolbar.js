/**
 * @requires XQuared.js
 * @requires Browser.js
 * @requires ui/Base.js
 */
xq.ui.Toolbar = xq.Class(/** @lends xq.ui.Toolbar.prototype */{
	/**
	 * Editor's toolbar.
	 *
     * @constructs
	 */
	initialize: function(xed, container, wrapper, buttonMap, buttonList, imagePath, structureAndStyleCollector) {
	
		xq.addToFinalizeQueue(this);
		
		this.xed = xed;
		
		if(typeof container === 'string') {
			container = xq.$(container);
		}
		if(container && container.nodeType !== 1) {
			throw "[container] is not an element";
		}
		
		this.wrapper = wrapper;
		this.doc = this.wrapper.ownerDocument;
		this.buttonMap = buttonMap;
		this.buttonList = buttonList;
		this.imagePath = imagePath;
		this.structureAndStyleCollector = structureAndStyleCollector;
		
		this.buttons = null;
		this.anchorsCache = [];
		this._scheduledUpdate = null;
		
		if(!container) {
			this.create();
			this._addStyleRules([
				{selector:".xquared div.toolbar", rule:"background-image: url(" + imagePath + "toolbarBg.gif)"},
				{selector:".xquared ul.buttons li", rule:"background-image: url(" + imagePath + "toolbarButtonBg.gif)"},
				{selector:".xquared ul.buttons li.xq_separator", rule:"background-image: url(" + imagePath + "toolbarSeparator.gif)"}
			]);
		} else {
			this.container = container;
			
			if(!this.dialogContainer)
			{
				// dialog container
				var dialogs = this.doc.createElement('div');
				dialogs.className = 'dialogs';
				this.dialogContainer = dialogs;
				this.wrapper.appendChild(dialogs);
			}
		}
		xed.addListener({
			onEditorInitialized: function() {
				xq.observe(xed.getDoc(), 'mousedown', this._closeAllLightweight.bindAsEventListener(this));
				xq.observe(document, 'mousedown', this._closeAllLightweight.bindAsEventListener(this));
			}.bind(this)
		});
	},
	
	finalize: function() {
		for(var i = 0; i < this.anchorsCache.length; i++) {
			// TODO remove dependency to Editor
			this.anchorsCache[i].xed = null;
			this.anchorsCache[i].handler = null;
			this.anchorsCache[i] = null;
		}
	
		this.toolbarAnchorsCache = null;
	},
	
	triggerUpdate: function() {
		if(this._scheduledUpdate) return;
		
		this._scheduledUpdate = window.setTimeout(
			function() {
				this._scheduledUpdate = null;
				var ss = this.structureAndStyleCollector();
				if(ss) this.update(ss);
			}.bind(this), 200
		);
	},
	
	/**
	 * Updates all buttons' status. Override this to customize status L&F. Don't call this function directly. Use triggerUpdate() to call it indirectly.
	 * 
	 * @param {Object} structure and style information. see xq.rdom.Base.collectStructureAndStyle()
	 */
	update: function(info) {
		if(!this.container) return;
		if(!this.buttons) {
			var classNames = [
				"emphasis", "strongEmphasis", "underline", "strike", "superscription", "subscription",
				"justifyLeft", "justifyCenter", "justifyRight", "justifyBoth",
				"unorderedList", "orderedList", "code",
				"paragraph", "heading1", "heading2", "heading3", "heading4", "heading5", "heading6"
			];
			
			this.buttons = {};
			
			for(var i = 0; i < classNames.length; i++) {
				var found = xq.getElementsByClassName(this.container, classNames[i]);
				var button = found && found.length > 0 ? found[0] : null;
				if(button) this.buttons[classNames[i]] = button;
			}
		}
		
		var buttons = this.buttons;
		this._updateButtonStatus('emphasis', info.em);
		this._updateButtonStatus('strongEmphasis', info.strong);
		this._updateButtonStatus('underline', info.underline);
		this._updateButtonStatus('strike', info.strike);
		this._updateButtonStatus('superscription', info.superscription);
		this._updateButtonStatus('subscription', info.subscription);
		
		this._updateButtonStatus('justifyLeft', info.justification === 'left');
		this._updateButtonStatus('justifyCenter', info.justification === 'center');
		this._updateButtonStatus('justifyRight', info.justification === 'right');
		this._updateButtonStatus('justifyBoth', info.justification === 'justify');
		
		this._updateButtonStatus('orderedList', info.list === 'OL');
		this._updateButtonStatus('unorderedList', info.list === 'UL');
		this._updateButtonStatus('code', info.list === 'CODE');
		
		this._updateButtonStatus('paragraph', info.block === 'P');
		this._updateButtonStatus('heading1', info.block === 'H1');
		this._updateButtonStatus('heading2', info.block === 'H2');
		this._updateButtonStatus('heading3', info.block === 'H3');
		this._updateButtonStatus('heading4', info.block === 'H4');
		this._updateButtonStatus('heading5', info.block === 'H5');
		this._updateButtonStatus('heading6', info.block === 'H6');
	},

	/**
	 * Enables all buttons
	 *
	 * @param {Array} [exceptions] array of string containing classnames to exclude
	 */
	enableButtons: function(exceptions) {
		if(!this.container) return;
		
		this._execForAllButtons(exceptions, function(li, exception) {
			li.firstChild.className = !exception ? '' : 'disabled';
		});

		// @WORKAROUND: Image icon disappears without following code:
		if(xq.Browser.isIE6) {
			this.container.style.display = 'none';
			setTimeout(function() {this.container.style.display = 'block';}.bind(this), 0);
		}
	},
	
	/**
	 * Disables all buttons
	 *
	 * @param {Array} [exceptions] array of string containing classnames to exclude
	 */
	disableButtons: function(exceptions) { 
		this._execForAllButtons(exceptions, function(li, exception) {
			li.firstChild.className = exception ? '' : 'disabled';
		});
	},
	
	/**
	 * Creates toolbar element
	 */
	create: function() {
		// outmost container
		this.container = this.doc.createElement('div');
		this.container.className = 'toolbar';
		
		// button container
		var buttons = this.doc.createElement('ul');
		buttons.className = 'buttons';
		this.container.appendChild(buttons);
		
		// dialog container
		var dialogs = this.doc.createElement('div');
		dialogs.className = 'dialogs';
		this.dialogContainer = dialogs;
		this.wrapper.appendChild(dialogs);
		
		if(this.buttonList.length !== 0)
		{
			var btnListLen = this.buttonList.length;
			for(var i = 0; i < btnListLen; i++)
			{
				if(this.buttonList[i] == "separator" )
				{
					continue;
				}
				
				var buttonConfig = this.buttonList[i];
				var li = this.doc.createElement('li');
				buttons.appendChild(li);
				li.className = buttonConfig.className;
				
				if(typeof this.buttonList[i-1] !== "undefined" && this.buttonList[i-1] == "separator" )
				{
					li.className += ' xq_separator';
				}
				
				var span = this.doc.createElement('span');
				li.appendChild(span);
				
				if(buttonConfig.list) {
					this._createDropdown(buttonConfig, span);
				} else {
					this._createButton(buttonConfig, span);
				}
			}
		}
		else if(this.buttonMap)
		{
			// Generate buttons from map and append it to button container
			for(var i = 0; i < this.buttonMap.length; i++) {
				for(var j = 0; j < this.buttonMap[i].length; j++) {
					var buttonConfig = this.buttonMap[i][j];
					var li = this.doc.createElement('li');
					buttons.appendChild(li);
					li.className = buttonConfig.className;
					
					var span = this.doc.createElement('span');
					li.appendChild(span);
					
					if(buttonConfig.list) {
						this._createDropdown(buttonConfig, span);
					} else {
						this._createButton(buttonConfig, span);
					}
					
					if(j === 0 && i !== 0) li.className += ' xq_separator';
				}
			}
		}
		
		this.wrapper.appendChild(this.container);
	},

	_createButton: function(buttonConfig, span) {
		var a = this.doc.createElement('a');
		span.appendChild(a);
		
		a.href = "#";
		a.title = buttonConfig.title;
		if (buttonConfig.handler){
			a.handler = buttonConfig.handler;
			xq.observe(a, 'click', this._clickHandler.bindAsEventListener(this));
		}
		
		this.anchorsCache.push(a);
		
		xq.observe(a, 'mousedown', xq.cancelHandler);

		var img = this.doc.createElement('img');
		a.appendChild(img);
		img.className = buttonConfig.className;
		img.src = this.imagePath + buttonConfig.className + '.gif';
		
		if(buttonConfig.title)
		{
			img.alt = buttonConfig.title;
		}
		else
		{
			img.alt = buttonConfig.className;
		}

		return a;
	},
	
	_createDropdown: function(buttonConfig, span) {
		// Create button
		var btn = this._createButton(buttonConfig, span);
		btn.items = buttonConfig.list;
		
		xq.observe(btn, 'click', this._openDropdownDialog.bindAsEventListener(this));
		
		// Create dialog
		var dialog = this.doc.createElement('DIV');
		dialog.id = buttonConfig.className + "Dialog";
		dialog.className = "xqFormDialog lightweight";
		dialog.style.display = 'none';
		
		var title = this.doc.createElement('H3');
		title.innerHTML = buttonConfig.title;
		dialog.appendChild(title);

		var dialogContent = this.doc.createElement('DIV');
		dialogContent.className = 'dialog-content';
		
		var ul = this.doc.createElement('UL');
		ul.className = "item-list";
		
		for (var i = 0; i < btn.items.length; i++) {
			var item = btn.items[i];
			var li = this.doc.createElement('LI');
			var anchor = this.doc.createElement('A');
			li.appendChild(anchor);
			if (item.html) {
				if (buttonConfig.className == 'emoticon') {
					var emoticon = this.doc.createElement('IMG');
					emoticon.src = this.xed.config.imagePathForEmoticon + item.html;
					emoticon.alt = item.html;
					anchor.appendChild(emoticon);				
				} else {
					anchor.innerHTML = decodeURIComponent(item.html);
				}
			}
			
			anchor.href = "#";
			anchor.handler = item.handler;
			
			for (attr in item.style){
				anchor.style[attr] = item.style[attr];
			}
			xq.observe(anchor, 'click', xq.cancelHandler);
			xq.observe(anchor, 'mouseup', this._closeAllLightweight.bindAsEventListener(this));
			xq.observe(anchor, 'mousedown', this._clickHandler.bindAsEventListener(this));
			
			ul.appendChild(li);
		}
		
		dialogContent.appendChild(ul);
		dialog.appendChild(dialogContent);
		this.dialogContainer.appendChild(dialog);
		span.appendChild(btn);
	},
	
	_openDropdownDialog: function(e){
		this._closeAllLightweight(e);
		
		var src = e.target || e.srcElement;
		this.xed.lastAnchor = src;
		var dialog = xq.$(src.className + "Dialog");
		
		if (dialog) {
			dialog.style.display = 'block';
			dialog.style.top = this.container.offsetTop + this.container.offsetHeight + 'px';
			dialog.style.left = this.container.offsetLeft + src.parentNode.offsetLeft + 'px';
		} 
		xq.stopEvent(e);
		return false;
	},
	
	_closeAllLightweight: function(e){
		if(e)
		{
			var src = e.target || e.srcElement;
			
			var linkDlg = xq.$('linkDialog');
			
			if( (linkDlg !== null && linkDlg.style.display !== 'none') || src.id.indexOf("extForeColor") !== -1 || src.className.indexOf("jscolor") !== -1)
			{
				return false;
			}
		}
		
		var dialogs = xq.getElementsByClassName(this.dialogContainer, 'lightweight');
		for (var i = 0; i < dialogs.length; i++){
			dialogs[i].style.display = "none";
		}
	},
	_clickHandler: function(e) {
		var src = e.target || e.srcElement;
		
		while(src.nodeName !== "A") src = src.parentNode;
		
		if(xq.hasClassName(src.parentNode, 'disabled') || xq.hasClassName(this.container, 'disabled')) {
			xq.stopEvent(e);
			return false;
		}
		
		var handler = src.handler;
		var xed = this.xed;
		xed.focus();
		if(typeof handler === "function") {
			handler(this);
		} else {
			eval(handler);
		}
		
		xq.stopEvent(e);
		return false;
	},

	_updateButtonStatus: function(className, selected) {
		var button = this.buttons[className];
		if(button) {
			var newClassName = selected ? 'selected' : '';
			var target = button.firstChild.firstChild;
			if(target.className !== newClassName) target.className = newClassName;
		}
	},
	
	_execForAllButtons: function(exceptions, exec) {
		if(!this.container) return;
		exceptions = exceptions || [];
		
		var lis = this.container.getElementsByTagName('LI');
		for(var i = 0; i < lis.length; i++) {
			var className = lis[i].className.split(" ").find(function(name) {return name !== 'xq_separator'});
			var exception = exceptions.indexOf(className) !== -1;
			exec(lis[i], exception);
		}
	},
	
	_addStyleRules: function(rules) {
		
		if(!this.dynamicStyle) {
			if(xq.Browser.isTrident) {
			    this.dynamicStyle = this.doc.createStyleSheet();
			} else {
	    		var style = this.doc.createElement('style');
	    		this.doc.body.appendChild(style);
		    	this.dynamicStyle = xq.$A(this.doc.styleSheets).last();
			}
		}
		
		for(var i = 0; i < rules.length; i++) {
			var rule = rules[i];
					
			if(xq.Browser.isTrident) {
				
				this.dynamicStyle.addRule(rules[i].selector, rules[i].rule);
			} else {
		    	this.dynamicStyle.insertRule(rules[i].selector + " {" + rules[i].rule + "}", this.dynamicStyle.cssRules.length);
	    	}
		}
	}
});