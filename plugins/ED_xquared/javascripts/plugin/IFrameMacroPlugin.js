/**
 * @requires XQuared.js
 * @requires Browser.js
 * @requires Editor.js
 * @requires plugin/Base.js
 * @requires ui/Control.js
 * @requires macro/Factory.js
 * @requires macro/IFrameMacro.js
 */
xq.plugin.IFrameMacroPlugin = xq.Class(xq.plugin.Base,
	/**
	 * @name xq.plugin.IFrameMacroPlugin
	 * @lends xq.plugin.IFrameMacroPlugin.prototype
	 * @extends xq.plugin.Base
	 * @constructor
	 */
	{
	onAfterLoad: function(xed) {
		xed.config.macroIds.push("IFrame");
		xed.config.defaultToolbarButtonGroups.insert.push(
			{className:"iframe", title:"IFrame", handler:"xed.handleIFrame()"}
		)
		
		xed.handleInsertIFrame = function(data) {
			this.focus();
			
			var macro = this.macroFactory.createMacroFromDefinition({id:"IFrame", params:data});
			if(macro) {
				var placeHolder = macro.createPlaceHolderHtml();
				this.rdom.insertHtml(placeHolder);
			} else {
				alert(xed._("Unknown error"));
			}
		}
		
		xed.handleIFrame = function() {
			var dialog = new xq.ui.FormDialog(
					this,
					xq.ui_templates.basicIFrameDialog,
					function(dialog) {},
					function(data) {
						this.focus();
					
						if(xq.Browser.isTrident) {
							var rng = this.rdom.rng();
							rng.moveToBookmark(bm);
							rng.select();
						}
						
						// cancel?
						if(!data) return;
						
						this.handleInsertIFrame(data);
					}.bind(this)
			);
			
			if(xq.Browser.isTrident) var bm = this.rdom.rng().getBookmark();
			dialog.show({position: 'centerOfEditor'});
			
			return true;
		}
	}
});