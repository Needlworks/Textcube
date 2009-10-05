/**
 * @requires Xquared.js
 * @requires Browser.js
 * @requires Editor.js
 * @requires plugin/Base.js
 * @requires ui/Control.js
 */
xq.plugin.CustomStylePlugin = xq.Class(xq.plugin.Base,
	/**
	 * @name xq.plugin.CustomStylePlugin
	 * @lends xq.plugin.CustomStylePlugin.prototype
	 * @extends xq.plugin.Base
	 * @constructor
	 */
	{
	onAfterLoad: function(xed) {
		xed.config.defaultToolbarButtonGroups.block.push(
			{className:"lineHeight", title:"Line Height", list: [
				{html:"50%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('50%')"},
				{html:"80%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('80%')"},
				{html:"100%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('100%')"},
				{html:"120%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('120%')"},
				{html:"150%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('150%')"},
				{html:"180%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('180%')"},
				{html:"200%25", style: {marginBottom: "3px"}, handler:"xed.handleLineHeight('200%')"}
			]}
		)
			
		xed.handleLineHeight = function(value){
			if(xed.rdom.hasSelection(true)) {
				var blocks = xed.rdom.getBlockElementsAtSelectionEdge(true, true);
				if(blocks.first() !== blocks.last()) {
					xed.rdom.applyLineHeights(blocks.first(), blocks.last(), value);
					
					var historyAdded = xed.editHistory.onCommand();
					xed._fireOnCurrentContentChanged(xed);
					
					return true;
				}
			}
			
			var affected = xed.rdom.applyLineHeight(value);
			
			if(affected && !xed.rdom.tree.isAtomic(xed.rdom.getCurrentElement())) {
				xed.rdom.placeCaretAtStartOf(affected);
				
				var historyAdded = xed.editHistory.onCommand();
				xed._fireOnCurrentContentChanged(this);
			}
			
			return true;
		}
		
		xed.rdom.applyLineHeight = function(value, element){
			element = element || xed.rdom.getCurrentBlockElement();
			
			var root = xed.rdom.getRoot();
			if(!element || element === root) return null;
		
			element.style.lineHeight = value;
			
			return element;
		}
		
		xed.rdom.applyLineHeights = function(from, to, value){
			var blocks = xed.rdom.getBlockElementsBetween(from, to);
			for (var i=0; i < blocks.length; i++) {
				if (xed.rdom.tree._blockContainerTags.indexOf(blocks[i].nodeName) === -1 && xed.rdom.tree._blockTags.indexOf(blocks[i].nodeName) !== -1) {
					xed.rdom.applyLineHeight(value, blocks[i]);
				}
			}
		}
	}
});