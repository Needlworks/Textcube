/**
 * @requires XQuared.js
 * @requires validator/W3.js
 */
xq.validator.Webkit = xq.Class(xq.validator.W3,
	/**
	 * @name xq.validator.Webkit
	 * @lends xq.validator.Webkit.prototype
	 * @extends xq.validator.W3
	 * @constructor
	 */
	{
	validateDom: function(element) {
		var rdom = xq.rdom.Base.createInstance();
		rdom.setRoot(element);
		this.removeDangerousElements(element);
		rdom.removePlaceHoldersAndEmptyNodes(element);
		this.validateAppleStyleTags(element);
	},
	
	validateString: function(html) {
		try {
			html = this.addNbspToEmptyBlocks(html);
			html = this.performFullValidation(html);
			html = this.insertNewlineBetweenBlockElements(html);
		} catch(ignored) {}
		
		return html;
	},
	
	invalidateDom: function(element) {
		this.invalidateAppleStyleTags(element);
	},
	
	invalidateString: function(html) {
		html = this.replaceTag(html, "strong", "b");
		html = this.replaceTag(html, "em", "i");
		html = this.removeComments(html);
		html = this.replaceNbspToBr(html);
		return html;
	},
	
	validateAppleStyleTags: function(element) {
		try {
			var rdom = xq.rdom.Base.createInstance();
			rdom.setRoot(element);
			
			var nodes = xq.getElementsByClassName(rdom.getRoot(), "Apple-style-span");
			var holder = [];
			
			for(var i = 0; i < nodes.length; i++) {
				var node = nodes[i];
				if(node.style.fontStyle === "italic") {
					// span -> em
					node = rdom.replaceTag("em", node);
					node.style.fontStyle = "";
					holder.push({node:node});
				} else if(node.style.fontWeight === "bold") {
					// span -> strong
					node = rdom.replaceTag("strong", node);
					node.style.fontWeight = "";
					holder.push({node:node});
				} else if(node.style.textDecoration === "underline") {
					// span -> em.underline
					node = rdom.replaceTag("em", node);
					node.style.textDecoration = "";
					holder.push({node:node, className: 'underline'});
				} else if(node.style.textDecoration === "line-through") {
					// span -> span.strike
					node.style.textDecoration = "";
					holder.push({node:node, className: 'strike'});
				} else if(node.style.verticalAlign === "super") {
					// span -> sup
					node = rdom.replaceTag("sup", node);
					node.style.verticalAlign = "";
					holder.push({node:node});
				} else if(node.style.verticalAlign === "sub") {
					// span -> sup
					node = rdom.replaceTag("sub", node);
					node.style.verticalAlign = "";
					holder.push({node:node});
				} else if(node.style.fontFamily) {
					// span -> span font-family
					holder.push({node:node});
				}
			}
			
			for (var j = 0; j < holder.length; j++){
				if (holder[j].className) {
					holder[j].node.className = holder[j].className;
				} else {
					holder[j].node.removeAttribute("class");
				}
			}
			
		} catch(e){
		}
	},
	
	invalidateAppleStyleTags: function(element) {
		var rdom = xq.rdom.Base.createInstance();
		rdom.setRoot(element);
		
		var len;
		// span.strike -> span, span... -> span
		var spans = rdom.getRoot().getElementsByTagName("span");
		for(var i = 0; i < spans.length; i++) {
			var node = spans[i];
			if(node.className == "strike") {
				node.className = "Apple-style-span";
				node.style.textDecoration = "line-through";
			} else if(node.style.fontFamily) {
				node.className = "Apple-style-span";
			}
			// TODO: bg/fg/font-size
		}

		// em -> span, em.underline -> span
		var ems = rdom.getRoot().getElementsByTagName("em");
		len = ems.length;
		for(var i = 0; i < len; i++) {
			var node = ems[0];
			node = rdom.replaceTag("span", node);
			if(node.className === "underline") {
				node.className = "Apple-style-span";
				node.style.textDecoration = "underline";
			} else {
				node.className = "Apple-style-span";
				node.style.fontStyle = "italic";
			}
		}
		
		// strong -> span
		var strongs = rdom.getRoot().getElementsByTagName("strong");
		len = strongs.length;
		
		for(var i = 0; i < len; i++) {
			var node = strongs[0];
			node = rdom.replaceTag("span", node);
			node.className = "Apple-style-span";
			node.style.fontWeight = "bold";
		}
		
		// sup -> span
		var sups = rdom.getRoot().getElementsByTagName("sup");
		len = sups.length;
		
		for(var i = 0; i < len; i++) {
			var node = sups[0];
			node = rdom.replaceTag("span", node);
			node.className = "Apple-style-span";
			node.style.verticalAlign = "super";
		}
		
		// sub -> span
		var subs = rdom.getRoot().getElementsByTagName("sub");
		len = subs.length;
		
		for(var i = 0; i < len; i++) {
			var node = subs[0];
			node = rdom.replaceTag("span", node);
			node.className = "Apple-style-span";
			node.style.verticalAlign = "sub";
		}
	}
});