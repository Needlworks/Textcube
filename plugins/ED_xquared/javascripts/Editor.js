/**
 * @requires XQuared.js
 * @requires Browser.js
 * @requires Timer.js
 * @requires rdom/Factory.js
 * @requires validator/Factory.js
 * @requires EditHistory.js
 * @requires plugin/Base.js
 * @requires RichTable.js
 * @requires ui/Control.js
 * @requires ui/Toolbar.js
 * @requires ui/_templates.js
 * @requires Shortcut.js
 */
xq.Editor = xq.Class(/** @lends xq.Editor.prototype */{
	/**
	 * Initialize editor but it doesn't automatically start designMode. setEditMode should be called after initialization.
	 *
     * @constructs
	 * @param {Object} contentElement TEXTAREA to be replaced with editable area, or DOM ID string for TEXTAREA.
	 * @param {Object} toolbarContainer HTML element which contains toolbar icons, or DOM ID string.
	 */
	 initialize: function(contentElement, toolbarContainer) {
		xq.addToFinalizeQueue(this);

		if(typeof contentElement === 'string'){
			contentElement = xq.$(contentElement);
		}
		
		if(!contentElement) {
			throw "[contentElement] is null";
		}
		
		if(contentElement.nodeName !== 'TEXTAREA') {
			throw "[contentElement] is not a TEXTAREA";
		}
			
		 xq.asEventSource(this, "Editor", ["StartInitialization", "Initialized", "ElementChanged", "BeforeEvent", "AfterEvent", "CurrentContentChanged", "StaticContentChanged", "CurrentEditModeChanged"]);
		 
		/**
		 * Editor's configuration.
		 * @type object
		 */
		this.config = {};
		
		/**
		 * Show confirm dialog when user close browser
		 * @type boolean
		 */
		this.config.enablePreventExit = false;
		
		this.config.PreventExitMessage = "Document is not empty. If you want to leave, click 'Ok' button.";
		
		/**
		 * Automatically gives initial focus.
		 * @type boolean
		 */
		this.config.autoFocusOnInit = false;
		
		/**
		 * set language for l10n
		 * @type string
		 */
		this.config.lang = 'en';
		
		if(xq.Browser.language)
		{
			this.config.lang = xq.Browser.language.substr(0, 2); 
		}
		
		/**
		 * Makes links clickable.
		 * @type boolean
		 */
		this.config.enableLinkClick = false;
		
		/**
		 * Changes mouse cursor to pointer when the cursor is on a link.
		 * @type boolean
		 */
		this.config.changeCursorOnLink = false;
		
		/**
		 * Generates default toolbar if there's no toolbar provided.
		 * @type boolean
		 */
		this.config.generateDefaultToolbar = true;
		
		this._generateDefaultToolbar();

		/**
		 * Image path for default toolbar.
		 * @type String
		 */
		this.config.imagePathForDefaultToolbar = '../images/toolbar/';
		
		/**
		 * Image path for content.
		 * @type String
		 */
		this.config.imagePathForContent = '../images/content/';
		
		/**
		 * Image path for dialog.
		 * @type String
		 */
		this.config.imagePathForDialog = '../images/dialogs/';
		
		/**
		 * Image path for emoticon.
		 * @type String
		 */
		this.config.imagePathForEmoticon = '../images/dialogs/emoticon/';
		
		/**
		 * Widget Container path.
		 * @type String
		 */
		this.config.widgetContainerPath = 'widget_container.html';
		
		/**
		 * Array of URL containig CSS for WYSIWYG area.
		 * @type Array
		 */
		this.config.contentCssList = ['../stylesheets/xq_contents.css'];
		
		/**
		 * Array of URL containig JS for WYSIWYG area.
		 * @type Array
		 */
		this.config.contentJsList = [];
		 
		/**
		 * URL Validation mode. One or "relative", "host_relative", "absolute",
		 * "browser_default"
		 * @type String
		 */
		this.config.urlValidationMode = 'absolute';
		
		/**
		 * Turns off validation in source editor.<br />
		 * Note that the validation will be performed regardless of this value
		 * when you switching edit mode.
		 * @type boolean
		 */
		this.config.noValidationInSourceEditMode = false;
		
		/**
		 * Automatically hooks onsubmit event.
		 * @type boolean
		 */
		this.config.automaticallyHookSubmitEvent = true;
		
		/**
		 * Set of whitelist(tag name and attributes) for use in validator
		 * @type Object
		 */
		this.config.whitelist = xq.predefinedWhitelist;
		
		/**
		 * Specifies a value of ID attribute for WYSIWYG document's body
		 * @type String
		 */
		this.config.bodyId = "";
		
		/**
		 * Specifies a value of CLASS attribute for WYSIWYG document's body
		 * @type String
		 */
		this.config.bodyClass = "xed";
		
		/**
		 * Plugins
		 * @type Object
		 */
		this.config.plugins = {};
		
		/**
		 * Shortcuts
		 * @type Object
		 */
		this.config.shortcuts = {};
		
		/**
		 * Autocorrections
		 * @type Object
		 */
		this.config.autocorrections = {};
		
		/**
		 * Autocompletions
		 * @type Object
		 */
		this.config.autocompletions = {};
		
		/**
		 * Template processors
		 * @type Object
		 */
		this.config.templateProcessors = {};
		
		/**
		 * Context menu handlers
		 * @type Object
		 */
		this.config.contextMenuHandlers = {};
		
		/**
		 * Original content element
		 * @type Element
		 */
		this.contentElement = contentElement;
		
		/**
		 * Owner document of content element
		 * @type Document
		 */
		this.doc = this.contentElement.ownerDocument;
		
		/**
		 * Body of content element
		 * @type Element
		 */
		this.body = this.doc.body;
		
		/**
		 * False or 'source' means source editing mode, true or 'wysiwyg' means
		 * WYSIWYG editing mode.
		 * @type Object
		 */
		this.currentEditMode = '';

		/**
		 * Timer
		 * @type xq.Timer
		 */
		this.timer = new xq.Timer(100);
		
		/**
		 * Base instance
		 * @type xq.rdom.Base
		 */
		this.rdom = xq.rdom.Base.createInstance();
		
		/**
		 * Base instance
		 * 
		 * @type xq.validator.Base
		 */
		this.validator = null;
		
		/**
		 * Outmost wrapper div
		 * @type Element
		 */
		this.outmostWrapper = null;
		
		/**
		 * Source editor container
		 * @type Element
		 */
		this.sourceEditorDiv = null;
		
		/**
		 * Source editor textarea
		 * @type Element
		 */
		this.sourceEditorTextarea = null;
		
		/**
		 * WYSIWYG editor container
		 * @type Element
		 */
		this.wysiwygEditorDiv = null;
		
		/**
		 * Outer frame
		 * @type IFrame
		 */
		this.outerFrame = null;
		
		/**
		 * Design mode iframe
		 * @type IFrame
		 */
		this.editorFrame = null;
		
		this.toolbarContainer = toolbarContainer;
		
		/**
		 * Toolbar container
		 * @type Element
		 */
		this.toolbar = null;
		
		/**
		 * Undo/redo manager
		 * @type xq.EditHistory
		 */
		this.editHistory = null;
		
		/**
		 * Context menu container
		 * @type Element
		 */
		this.contextMenuContainer = null;
		
		/**
		 * Context menu items
		 * @type Array
		 */
		this.contextMenuItems = null;
		
		/**
		 * Platform dependent key event type
		 * @type String
		 */
		this.platformDepedentKeyEventType = (xq.Browser.isMac && xq.Browser.isGecko ? "keypress" : "keydown");
		
		this.addShortcuts(this.getDefaultShortcuts());
		
		this.addListener({
			onEditorCurrentContentChanged: function(xed) {
				var curFocusElement = xed.rdom.getCurrentElement();
				if(!curFocusElement || curFocusElement.ownerDocument !== xed.rdom.getDoc()) {
					return;
				}
				
				if(xed.lastFocusElement !== curFocusElement) {
					if(!xed.rdom.tree.isBlockOnlyContainer(xed.lastFocusElement) && xed.rdom.tree.isBlock(xed.lastFocusElement)) {
						xed.rdom.removeTrailingWhitespace(xed.lastFocusElement);
					}
					xed._fireOnElementChanged(xed, xed.lastFocusElement, curFocusElement);
					xed.lastFocusElement = curFocusElement;
				}
				
				xed.toolbar.triggerUpdate();
			}
		});
	
		if(!this.PreventExit)
		{
			this.PreventExit = {};
		}
		
		// add PreventExit handler	
		xq.observe(window, "beforeunload", function(e)
		{
			if(xed.config.enablePreventExit === false) return;
			
			var content = xed.getCurrentContent().stripTags();
	
			if(content !== '&nbsp;' && content !== xed.PreventExit.defaultContent)
			{
				xq.stopEvent(e, xed.config.PreventExitMessage);
			}
		});
	
	},
	
	finalize: function() {
		for(var key in this.config.plugins) this.config.plugins[key].unload();
	},
	
	/**
	 * Generate default toolbar buttons, groups and map
	 * 
	 * @type Object
	 */
	_generateDefaultToolbar: function()
	{
		this.config.defaultToolbarButtonList = [];
		
		this.config.defaultToolbarButtons = {};
		
		this.config.defaultToolbarButtons.foregroundColor =
		{className:"foregroundColor", title:this._("Foreground color"), list:[
			{style: {backgroundColor:"#ffd8d8",border: "1px solid #e5d2c4"}, handler:"xed.handleColorPicker('#ffd8d8')"},
			{style: {backgroundColor:"#ffead9",border: "1px solid #e4d1c3"}, handler:"xed.handleColorPicker('#ffead9')"},
			{style: {backgroundColor:"#fef2dc",border: "1px solid #e5dac6"}, handler:"xed.handleColorPicker('#fef2dc')"},
			{style: {backgroundColor:"#fff5da",border: "1px solid #e5ddc6"}, handler:"xed.handleColorPicker('#fff5da')"},
			{style: {backgroundColor:"#eefed9",border: "1px solid #d5e4c5"}, handler:"xed.handleColorPicker('#eefed9')"},
			{style: {backgroundColor:"#dafeda",border: "1px solid #c2e4c3"}, handler:"xed.handleColorPicker('#dafeda')"},
			{style: {backgroundColor:"#d8ffff",border: "1px solid #c2e6e6"}, handler:"xed.handleColorPicker('#d8ffff')"},
			{style: {backgroundColor:"#d9f7ff",border: "1px solid #c2dfe7"}, handler:"xed.handleColorPicker('#d9f7ff')"},
			{style: {backgroundColor:"#d5ebff",border: "1px solid #bed3e6"}, handler:"xed.handleColorPicker('#d5ebff')"},
			{style: {backgroundColor:"#eed8ff",border: "1px solid #d6c3e3"}, handler:"xed.handleColorPicker('#eed8ff')"},
			{style: {backgroundColor:"#fed8ff",border: "1px solid #e5c1e5"}, handler:"xed.handleColorPicker('#fed8ff')"},
			{style: {backgroundColor:"#ffffff",border: "1px solid #e5e5e5"}, handler:"xed.handleColorPicker('#ffffff')"},

			{style: {backgroundColor:"#fe8c8c",border: "1px solid #e77f80"}, handler:"xed.handleColorPicker('#fe8c8c')"},
			{style: {backgroundColor:"#feba8d",border: "1px solid #e7a67c"}, handler:"xed.handleColorPicker('#feba8d')"},
			{style: {backgroundColor:"#ffe88b",border: "1px solid #e5d07d"}, handler:"xed.handleColorPicker('#ffe88b')"},
			{style: {backgroundColor:"#ffff8d",border: "1px solid #e6e47d"}, handler:"xed.handleColorPicker('#ffff8d')"},
			{style: {backgroundColor:"#d0fc8d",border: "1px solid #bbe17e"}, handler:"xed.handleColorPicker('#d0fc8d')"},
			{style: {backgroundColor:"#8efb8e",border: "1px solid #7ee280"}, handler:"xed.handleColorPicker('#8efb8e')"},
			{style: {backgroundColor:"#8bffff",border: "1px solid #7ee6e5"}, handler:"xed.handleColorPicker('#8bffff')"},
			{style: {backgroundColor:"#8ce8ff",border: "1px solid #7fcfe6"}, handler:"xed.handleColorPicker('#8ce8ff')"},
			{style: {backgroundColor:"#8b8cff",border: "1px solid #7d7fe6"}, handler:"xed.handleColorPicker('#8b8cff')"},
			{style: {backgroundColor:"#d18cff",border: "1px solid #bc7de5"}, handler:"xed.handleColorPicker('#d18cff')"},
			{style: {backgroundColor:"#ff8bfe",border: "1px solid #e47fe5"}, handler:"xed.handleColorPicker('#ff8bfe')"},
			{style: {backgroundColor:"#cccccc",border: "1px solid #aeaeae"}, handler:"xed.handleColorPicker('#cccccc')"},

			{style: {backgroundColor:"#ff0103",border: "1px solid #e40001"}, handler:"xed.handleColorPicker('#ff0103')"},
			{style: {backgroundColor:"#ff6600",border: "1px solid #e85c00"}, handler:"xed.handleColorPicker('#ff6600')"},
			{style: {backgroundColor:"#ffcc01",border: "1px solid #e4b600"}, handler:"xed.handleColorPicker('#ffcc01')"},
			{style: {backgroundColor:"#ffff01",border: "1px solid #e5e400"}, handler:"xed.handleColorPicker('#ffff01')"},
			{style: {backgroundColor:"#96f908",border: "1px solid #86e004"}, handler:"xed.handleColorPicker('#96f908')"},
			{style: {backgroundColor:"#07f905",border: "1px solid #03e005"}, handler:"xed.handleColorPicker('#07f905')"},
			{style: {backgroundColor:"#02feff",border: "1px solid #00e4e3"}, handler:"xed.handleColorPicker('#02feff')"},
			{style: {backgroundColor:"#00ccff",border: "1px solid #00b8e4"}, handler:"xed.handleColorPicker('#00ccff')"},
			{style: {backgroundColor:"#0100fe",border: "1px solid #0000e6"}, handler:"xed.handleColorPicker('#0100fe')"},
			{style: {backgroundColor:"#9801ff",border: "1px solid #8900e6"}, handler:"xed.handleColorPicker('#9801ff')"},
			{style: {backgroundColor:"#fc01fe",border: "1px solid #e700e6"}, handler:"xed.handleColorPicker('#fc01fe')"},
			{style: {backgroundColor:"#999999",border: "1px solid #808080"}, handler:"xed.handleColorPicker('#999999')"},

			{style: {backgroundColor:"#990002",border: "1px solid #890101"}, handler:"xed.handleColorPicker('#990002')"},
			{style: {backgroundColor:"#b65006",border: "1px solid #ad6d00"}, handler:"xed.handleColorPicker('#b65006')"},
			{style: {backgroundColor:"#bf7900",border: "1px solid #ac6e01"}, handler:"xed.handleColorPicker('#bf7900')"},
			{style: {backgroundColor:"#cca500",border: "1px solid #b89200"}, handler:"xed.handleColorPicker('#cca500')"},
			{style: {backgroundColor:"#5a9603",border: "1px solid #518604"}, handler:"xed.handleColorPicker('#5a9603')"},
			{style: {backgroundColor:"#059502",border: "1px solid #048504"}, handler:"xed.handleColorPicker('#059502')"},
			{style: {backgroundColor:"#009997",border: "1px solid #008886"}, handler:"xed.handleColorPicker('#009997')"},
			{style: {backgroundColor:"#007998",border: "1px solid #006d89"}, handler:"xed.handleColorPicker('#007998')"},
			{style: {backgroundColor:"#095392",border: "1px solid #084a84"}, handler:"xed.handleColorPicker('#095392')"},
			{style: {backgroundColor:"#6a19a4",border: "1px solid #601693"}, handler:"xed.handleColorPicker('#6a19a4')"},
			{style: {backgroundColor:"#98019a",border: "1px solid #8a008b"}, handler:"xed.handleColorPicker('#98019a')"},
			{style: {backgroundColor:"#666666",border: "1px solid #555555"}, handler:"xed.handleColorPicker('#666666')"},

			{style: {backgroundColor:"#590100",border: "1px solid #510000"}, handler:"xed.handleColorPicker('#590100')"},
			{style: {backgroundColor:"#773505",border: "1px solid #714901"}, handler:"xed.handleColorPicker('#773505')"},
			{style: {backgroundColor:"#7f5000",border: "1px solid #734901"}, handler:"xed.handleColorPicker('#7f5000')"},
			{style: {backgroundColor:"#927300",border: "1px solid #836600"}, handler:"xed.handleColorPicker('#927300')"},
			{style: {backgroundColor:"#365802",border: "1px solid #304f03"}, handler:"xed.handleColorPicker('#365802')"},
			{style: {backgroundColor:"#035902",border: "1px solid #025102"}, handler:"xed.handleColorPicker('#035902')"},
			{style: {backgroundColor:"#01595a",border: "1px solid #00504f"}, handler:"xed.handleColorPicker('#01595a')"},
			{style: {backgroundColor:"#00485b",border: "1px solid #004252"}, handler:"xed.handleColorPicker('#00485b')"},
			{style: {backgroundColor:"#083765",border: "1px solid #06315b"}, handler:"xed.handleColorPicker('#083765')"},
			{style: {backgroundColor:"#370159",border: "1px solid #300151"}, handler:"xed.handleColorPicker('#370159')"},
			{style: {backgroundColor:"#59005a",border: "1px solid #520052"}, handler:"xed.handleColorPicker('#59005a')"},
			{style: {backgroundColor:"#000000",border: "1px solid #000000"}, handler:"xed.handleColorPicker('#000000')"}
		]};
		
		this.config.defaultToolbarButtons.backgroundColor = 		
		{className:"backgroundColor", title:this._("Background color"), list:[
			{style: {backgroundColor:"#FFF700"}, handler:"xed.handleBackgroundColor('#FFF700')"},
			{style: {backgroundColor:"#AEFF66"}, handler:"xed.handleBackgroundColor('#AEFF66')"},
			{style: {backgroundColor:"#FFCC66"}, handler:"xed.handleBackgroundColor('#FFCC66')"},
			{style: {backgroundColor:"#DCB0FB"}, handler:"xed.handleBackgroundColor('#DCB0FB')"},
			{style: {backgroundColor:"#B0EEFB"}, handler:"xed.handleBackgroundColor('#B0EEFB')"},
			{style: {backgroundColor:"#FBBDB0"}, handler:"xed.handleBackgroundColor('#FBBDB0')"},
			{style: {backgroundColor:"#FFFFFF"}, handler:"xed.handleBackgroundColor('#FFFFFF')"}
		]};
		
		this.config.defaultToolbarButtons.fontFace = 
			{className:"fontFace", title:this._("Font face"), list:[
	            {html:"Arial", style: {fontFamily: "Arial"}, handler:"xed.handleFontFace('Arial')"},
	            {html:"Comic Sans MS", style: {fontFamily: "Comic Sans MS"}, handler:"xed.handleFontFace('Comic Sans MS')"},
	            {html:"Courier New", style: {fontFamily: "Courier New"}, handler:"xed.handleFontFace('Courier New')"},
	            {html:"Georgia", style: {fontFamily: "Georgia"}, handler:"xed.handleFontFace('Georgia')"},
	            {html:"Tahoma", style: {fontFamily: "Tahoma"}, handler:"xed.handleFontFace('Tahoma')"},
	            {html:"Times", style: {fontFamily: "Times"}, handler:"xed.handleFontFace('Times')"},
	            {html:"Trebuchte MS", style: {fontFamily: "Trebuchte MS"}, handler:"xed.handleFontFace('Trebuchte MS')"},
	            {html:"Verdana", style: {fontFamily: "Verdana"}, handler:"xed.handleFontFace('Verdana')"}
            ]};

		this.config.defaultToolbarButtons.fontSize = 
		{className:"fontSize", title:this._("Font size"), list:[
            {html:"Lorem ipsum dolor (8pt)", style: {fontSize: "8pt", marginBottom: "3px"}, handler:"xed.handleFontSize('1')"},
            {html:"Lorem ipsum dolor (10pt)", style: {fontSize: "10pt", marginBottom: "3px"}, handler:"xed.handleFontSize('2')"},
            {html:"Lorem ipsum dolor (12pt)", style: {fontSize: "12pt", marginBottom: "6px"}, handler:"xed.handleFontSize('3')"},
            {html:"Lorem ipsum dolor (14pt)", style: {fontSize: "14pt", marginBottom: "10px"}, handler:"xed.handleFontSize('4')"},
            {html:"Lorem ipsum dolor (18pt)", style: {fontSize: "18pt", marginBottom: "16px"}, handler:"xed.handleFontSize('5')"},
            {html:"Lorem ipsum dolor (24pt)", style: {fontSize: "24pt", marginBottom: "6px"}, handler:"xed.handleFontSize('6')"}
		]};
		
		// link
		this.config.defaultToolbarButtons.link = {className:"link", title:this._("Link"), handler:"xed.handleLink()"};
		this.config.defaultToolbarButtons.removeLink = {className:"removeLink", title:this._("Remove link"), handler:"xed.handleRemoveLink()"};
		
		// style
		this.config.defaultToolbarButtons.strongEmphasis = {className:"strongEmphasis", title:this._("Strong emphasis"), handler:"xed.handleStrongEmphasis()"};
		this.config.defaultToolbarButtons.emphasis = {className:"emphasis", title:this._("Emphasis"), handler:"xed.handleEmphasis()"};
		this.config.defaultToolbarButtons.underline = {className:"underline", title:this._("Underline"), handler:"xed.handleUnderline()"};
		this.config.defaultToolbarButtons.strike = {className:"strike", title:this._("Strike"), handler:"xed.handleStrike()"};
		this.config.defaultToolbarButtons.superscription = {className:"superscription", title:this._("Superscription"), handler:"xed.handleSuperscription()"};
		this.config.defaultToolbarButtons.subscription = {className:"subscription", title:this._("Subscription"), handler:"xed.handleSubscription()"};
		this.config.defaultToolbarButtons.removeFormat = {className:"removeFormat", title:this._("Remove format"), handler:"xed.handleRemoveFormat()"};
		
		// justification
		this.config.defaultToolbarButtons.justifyLeft = {className:"justifyLeft", title:this._("Justify left"), handler:"xed.handleJustify('left')"};
		this.config.defaultToolbarButtons.justifyCenter = {className:"justifyCenter", title:this._("Justify center"), handler:"xed.handleJustify('center')"};
		this.config.defaultToolbarButtons.justifyRight = {className:"justifyRight", title:this._("Justify right"), handler:"xed.handleJustify('right')"};
		this.config.defaultToolbarButtons.justifyBoth = {className:"justifyBoth", title:this._("Justify both"), handler:"xed.handleJustify('both')"};

		// indentation
		this.config.defaultToolbarButtons.indent = {className:"indent", title:this._("Indent"), handler:"xed.handleIndent()"};
		this.config.defaultToolbarButtons.outdent = {className:"outdent", title:this._("Outdent"), handler:"xed.handleOutdent()"};
		
		// block
		this.config.defaultToolbarButtons.paragraph = {className:"paragraph", title:this._("Paragraph"), handler:"xed.handleApplyBlock('P')"};
		this.config.defaultToolbarButtons.heading1 = {className:"heading1", title:this._("Heading"), list:[
			{html:"Heading1", style: {fontSize: "2.845em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H1')"},
			{html:"Heading2", style: {fontSize: "2.46em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H2')"},
			{html:"Heading3", style: {fontSize: "2.153em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H3')"},
			{html:"Heading4", style: {fontSize: "1.922em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H4')"},
			{html:"Heading5", style: {fontSize: "1.461em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H5')"},
			{html:"Heading6", style: {fontSize: "1.23em", marginBottom: "3px"}, handler:"xed.handleApplyBlock('H6')"}
		]};
		
		this.config.defaultToolbarButtons.blockquote = {className:"blockquote", title:this._("Blockquote"), handler:"xed.handleApplyBlock('BLOCKQUOTE')"};
		this.config.defaultToolbarButtons.code = {className:"code", title:this._("Code"), handler:"xed.handleList('OL', 'code')"};
		this.config.defaultToolbarButtons.division = {className:"division", title:this._("Div"), handler:"xed.handleApplyBlock('DIV')"};
		this.config.defaultToolbarButtons.unorderedList = {className:"unorderedList", title:this._("Unordered list"), handler:"xed.handleList('UL')"};
		this.config.defaultToolbarButtons.orderedList = {className:"orderedList", title:this._("Ordered list"), handler:"xed.handleList('OL')"};

		this.config.defaultToolbarButtons.table =  {className:"table", title:this._("Table"), handler:"xed.handleTable()"};
		this.config.defaultToolbarButtons.separator =  {className:"separator", title:this._("Separator"), handler:"xed.handleSeparator()"};
		this.config.defaultToolbarButtons.character =  {className:"character", title:this._("Character"), list: [
            {html:"%E3%80%81", handler:"xed.handleCharacter('%E3%80%81')"},
            {html:"%E3%80%82", handler:"xed.handleCharacter('%E3%80%82')"},
            {html:"%C2%B7", handler:"xed.handleCharacter('%C2%B7')"},
			{html:"%E2%80%A5", handler:"xed.handleCharacter('%E2%80%A5')"},
			{html:"%E2%80%A6", handler:"xed.handleCharacter('%E2%80%A6')"},
			{html:"%C2%A8", handler:"xed.handleCharacter('%C2%A8')"},
			{html:"%E3%80%83", handler:"xed.handleCharacter('%E3%80%83')"},
			{html:"%E2%80%95", handler:"xed.handleCharacter('%E2%80%95')"},
			{html:"%E2%88%A5", handler:"xed.handleCharacter('%E2%88%A5')"},
			{html:"%EF%BC%BC", handler:"xed.handleCharacter('%EF%BC%BC')"},
			{html:"%E2%88%BC", handler:"xed.handleCharacter('%E2%88%BC')"},
			{html:"%E2%80%98", handler:"xed.handleCharacter('%E2%80%98')"},
			{html:"%E2%80%99", handler:"xed.handleCharacter('%E2%80%99')"},
			{html:"%E2%80%9C", handler:"xed.handleCharacter('%E2%80%9C')"},
			{html:"%E2%80%9D", handler:"xed.handleCharacter('%E2%80%9D')"},
			{html:"%E3%80%94", handler:"xed.handleCharacter('%E3%80%94')"},
			{html:"%E3%80%95", handler:"xed.handleCharacter('%E3%80%95')"},
			{html:"%E3%80%88", handler:"xed.handleCharacter('%E3%80%88')"},
			{html:"%E3%80%89", handler:"xed.handleCharacter('%E3%80%89')"},
			{html:"%E3%80%8A", handler:"xed.handleCharacter('%E3%80%8A')"},
			{html:"%E3%80%8B", handler:"xed.handleCharacter('%E3%80%8B')"},
			{html:"%E3%80%8C", handler:"xed.handleCharacter('%E3%80%8C')"},
			{html:"%E3%80%8D", handler:"xed.handleCharacter('%E3%80%8D')"},
			{html:"%E3%80%8E", handler:"xed.handleCharacter('%E3%80%8E')"},
			{html:"%E3%80%8F", handler:"xed.handleCharacter('%E3%80%8F')"},
			{html:"%E3%80%90", handler:"xed.handleCharacter('%E3%80%90')"},
			{html:"%E3%80%91", handler:"xed.handleCharacter('%E3%80%91')"},
			{html:"%C2%B1", handler:"xed.handleCharacter('%C2%B1')"},
			{html:"%C3%97", handler:"xed.handleCharacter('%C3%97')"},
			{html:"%C3%B7", handler:"xed.handleCharacter('%C3%B7')"},
			{html:"%E2%89%A0", handler:"xed.handleCharacter('%E2%89%A0')"},
			{html:"%E2%89%A4", handler:"xed.handleCharacter('%E2%89%A4')"},
			{html:"%E2%89%A5", handler:"xed.handleCharacter('%E2%89%A5')"},
			{html:"%E2%88%9E", handler:"xed.handleCharacter('%E2%88%9E')"},
			{html:"%E2%88%B4", handler:"xed.handleCharacter('%E2%88%B4')"},
			{html:"%C2%B0", handler:"xed.handleCharacter('%C2%B0')"},
			{html:"%E2%80%B2", handler:"xed.handleCharacter('%E2%80%B2')"},
			{html:"%E2%80%B3", handler:"xed.handleCharacter('%E2%80%B3')"},
			{html:"%E2%84%83", handler:"xed.handleCharacter('%E2%84%83')"},
			{html:"%E2%84%AB", handler:"xed.handleCharacter('%E2%84%AB')"},
			{html:"%EF%BF%A0", handler:"xed.handleCharacter('%EF%BF%A0')"},
			{html:"%EF%BF%A1", handler:"xed.handleCharacter('%EF%BF%A1')"},
			{html:"%EF%BF%A5", handler:"xed.handleCharacter('%EF%BF%A5')"},
			{html:"%E2%99%82", handler:"xed.handleCharacter('%E2%99%82')"},
			{html:"%E2%99%80", handler:"xed.handleCharacter('%E2%99%80')"},
			{html:"%E2%88%A0", handler:"xed.handleCharacter('%E2%88%A0')"},
			{html:"%E2%8A%A5", handler:"xed.handleCharacter('%E2%8A%A5')"},
			{html:"%E2%8C%92", handler:"xed.handleCharacter('%E2%8C%92')"},
			{html:"%E2%88%82", handler:"xed.handleCharacter('%E2%88%82')"},
			{html:"%E2%88%87", handler:"xed.handleCharacter('%E2%88%87')"},
			{html:"%E2%89%A1", handler:"xed.handleCharacter('%E2%89%A1')"},
			{html:"%E2%89%92", handler:"xed.handleCharacter('%E2%89%92')"},
			{html:"%C2%A7", handler:"xed.handleCharacter('%C2%A7')"},
			{html:"%E2%80%BB", handler:"xed.handleCharacter('%E2%80%BB')"},
			{html:"%E2%98%86", handler:"xed.handleCharacter('%E2%98%86')"},
			{html:"%E2%98%85", handler:"xed.handleCharacter('%E2%98%85')"},
			{html:"%E2%97%8B", handler:"xed.handleCharacter('%E2%97%8B')"},
			{html:"%E2%97%8F", handler:"xed.handleCharacter('%E2%97%8F')"},
			{html:"%E2%97%8E", handler:"xed.handleCharacter('%E2%97%8E')"},
			{html:"%E2%97%87", handler:"xed.handleCharacter('%E2%97%87')"},
			{html:"%E2%97%86", handler:"xed.handleCharacter('%E2%97%86')"},
			{html:"%E2%96%A1", handler:"xed.handleCharacter('%E2%96%A1')"},
			{html:"%E2%96%A0", handler:"xed.handleCharacter('%E2%96%A0')"},
			{html:"%E2%96%B3", handler:"xed.handleCharacter('%E2%96%B3')"},
			{html:"%E2%96%B2", handler:"xed.handleCharacter('%E2%96%B2')"},
			{html:"%E2%96%BD", handler:"xed.handleCharacter('%E2%96%BD')"},
			{html:"%E2%96%BC", handler:"xed.handleCharacter('%E2%96%BC')"},
			{html:"%E2%86%92", handler:"xed.handleCharacter('%E2%86%92')"},
			{html:"%E2%86%90", handler:"xed.handleCharacter('%E2%86%90')"},
			{html:"%E2%86%91", handler:"xed.handleCharacter('%E2%86%91')"},
			{html:"%E2%86%93", handler:"xed.handleCharacter('%E2%86%93')"},
			{html:"%E2%86%94", handler:"xed.handleCharacter('%E2%86%94')"},
			{html:"%E3%80%93", handler:"xed.handleCharacter('%E3%80%93')"},
			{html:"%E2%89%AA", handler:"xed.handleCharacter('%E2%89%AA')"},
			{html:"%E2%89%AB", handler:"xed.handleCharacter('%E2%89%AB')"},
			{html:"%E2%88%9A", handler:"xed.handleCharacter('%E2%88%9A')"},
			{html:"%E2%88%BD", handler:"xed.handleCharacter('%E2%88%BD')"},
			{html:"%E2%88%9D", handler:"xed.handleCharacter('%E2%88%9D')"},
			{html:"%E2%88%B5", handler:"xed.handleCharacter('%E2%88%B5')"},
			{html:"%E2%88%AB", handler:"xed.handleCharacter('%E2%88%AB')"},
			{html:"%E2%88%AC", handler:"xed.handleCharacter('%E2%88%AC')"},
			{html:"%E2%88%88", handler:"xed.handleCharacter('%E2%88%88')"},
			{html:"%E2%88%8B", handler:"xed.handleCharacter('%E2%88%8B')"},
			{html:"%E2%8A%86", handler:"xed.handleCharacter('%E2%8A%86')"},
			{html:"%E2%8A%87", handler:"xed.handleCharacter('%E2%8A%87')"},
			{html:"%E2%8A%82", handler:"xed.handleCharacter('%E2%8A%82')"},
			{html:"%E2%8A%83", handler:"xed.handleCharacter('%E2%8A%83')"},
			{html:"%E2%88%AA", handler:"xed.handleCharacter('%E2%88%AA')"},
			{html:"%E2%88%A9", handler:"xed.handleCharacter('%E2%88%A9')"},
			{html:"%E2%88%A7", handler:"xed.handleCharacter('%E2%88%A7')"},
			{html:"%E2%88%A8", handler:"xed.handleCharacter('%E2%88%A8')"},
			{html:"%EF%BF%A2", handler:"xed.handleCharacter('%EF%BF%A2')"},
			{html:"%E2%87%92", handler:"xed.handleCharacter('%E2%87%92')"},
			{html:"%E2%87%94", handler:"xed.handleCharacter('%E2%87%94')"},
			{html:"%E2%88%80", handler:"xed.handleCharacter('%E2%88%80')"},
			{html:"%E2%88%83", handler:"xed.handleCharacter('%E2%88%83')"},
			{html:"%EF%BD%9E", handler:"xed.handleCharacter('%EF%BD%9E')"},
			{html:"%CB%87", handler:"xed.handleCharacter('%CB%87')"},
			{html:"%CB%98", handler:"xed.handleCharacter('%CB%98')"},
			{html:"%C2%B8", handler:"xed.handleCharacter('%C2%B8')"},
			{html:"%CB%9B", handler:"xed.handleCharacter('%CB%9B')"},
			{html:"%C2%A1", handler:"xed.handleCharacter('%C2%A1')"},
			{html:"%C2%BF", handler:"xed.handleCharacter('%C2%BF')"},
			{html:"%CB%90", handler:"xed.handleCharacter('%CB%90')"},
			{html:"%E2%88%AE", handler:"xed.handleCharacter('%E2%88%AE')"},
			{html:"%E2%88%91", handler:"xed.handleCharacter('%E2%88%91')"},
			{html:"%E2%88%8F", handler:"xed.handleCharacter('%E2%88%8F')"},
			{html:"%C2%A4", handler:"xed.handleCharacter('%C2%A4')"},
			{html:"%E2%84%89", handler:"xed.handleCharacter('%E2%84%89')"},
			{html:"%E2%80%B0", handler:"xed.handleCharacter('%E2%80%B0')"},
			{html:"%E2%97%81", handler:"xed.handleCharacter('%E2%97%81')"},
			{html:"%E2%97%80", handler:"xed.handleCharacter('%E2%97%80')"},
			{html:"%E2%96%B7", handler:"xed.handleCharacter('%E2%96%B7')"},
			{html:"%E2%96%B6", handler:"xed.handleCharacter('%E2%96%B6')"},
			{html:"%E2%99%A4", handler:"xed.handleCharacter('%E2%99%A4')"},
			{html:"%E2%99%A0", handler:"xed.handleCharacter('%E2%99%A0')"},
			{html:"%E2%99%A1", handler:"xed.handleCharacter('%E2%99%A1')"},
			{html:"%E2%99%A5", handler:"xed.handleCharacter('%E2%99%A5')"},
			{html:"%E2%99%A7", handler:"xed.handleCharacter('%E2%99%A7')"},
			{html:"%E2%99%A3", handler:"xed.handleCharacter('%E2%99%A3')"},
			{html:"%E2%8A%99", handler:"xed.handleCharacter('%E2%8A%99')"},
			{html:"%E2%97%88", handler:"xed.handleCharacter('%E2%97%88')"},
			{html:"%E2%96%A3", handler:"xed.handleCharacter('%E2%96%A3')"},
			{html:"%E2%97%90", handler:"xed.handleCharacter('%E2%97%90')"},
			{html:"%E2%97%91", handler:"xed.handleCharacter('%E2%97%91')"},
			{html:"%E2%96%92", handler:"xed.handleCharacter('%E2%96%92')"},
			{html:"%E2%96%A4", handler:"xed.handleCharacter('%E2%96%A4')"},
			{html:"%E2%96%A5", handler:"xed.handleCharacter('%E2%96%A5')"},
			{html:"%E2%96%A8", handler:"xed.handleCharacter('%E2%96%A8')"},
			{html:"%E2%96%A7", handler:"xed.handleCharacter('%E2%96%A7')"},
			{html:"%E2%96%A6", handler:"xed.handleCharacter('%E2%96%A6')"},
			{html:"%E2%96%A9", handler:"xed.handleCharacter('%E2%96%A9')"},
			{html:"%E2%99%A8", handler:"xed.handleCharacter('%E2%99%A8')"},
			{html:"%E2%98%8F", handler:"xed.handleCharacter('%E2%98%8F')"},
			{html:"%E2%98%8E", handler:"xed.handleCharacter('%E2%98%8E')"},
			{html:"%E2%98%9C", handler:"xed.handleCharacter('%E2%98%9C')"},
			{html:"%E2%98%9E", handler:"xed.handleCharacter('%E2%98%9E')"},
			{html:"%C2%B6", handler:"xed.handleCharacter('%C2%B6')"},
			{html:"%E2%80%A0", handler:"xed.handleCharacter('%E2%80%A0')"},
			{html:"%E2%80%A1", handler:"xed.handleCharacter('%E2%80%A1')"},
			{html:"%E2%86%95", handler:"xed.handleCharacter('%E2%86%95')"},
			{html:"%E2%86%97", handler:"xed.handleCharacter('%E2%86%97')"},
			{html:"%E2%86%99", handler:"xed.handleCharacter('%E2%86%99')"},
			{html:"%E2%86%96", handler:"xed.handleCharacter('%E2%86%96')"},
			{html:"%E2%86%98", handler:"xed.handleCharacter('%E2%86%98')"},
			{html:"%E2%99%AD", handler:"xed.handleCharacter('%E2%99%AD')"},
			{html:"%E2%99%A9", handler:"xed.handleCharacter('%E2%99%A9')"},
			{html:"%E2%99%AA", handler:"xed.handleCharacter('%E2%99%AA')"},
			{html:"%E2%99%AC", handler:"xed.handleCharacter('%E2%99%AC')"},
			{html:"%E3%89%BF", handler:"xed.handleCharacter('%E3%89%BF')"},
			{html:"%E3%88%9C", handler:"xed.handleCharacter('%E3%88%9C')"},
			{html:"%E2%84%96", handler:"xed.handleCharacter('%E2%84%96')"},
			{html:"%E3%8F%87", handler:"xed.handleCharacter('%E3%8F%87')"},
			{html:"%E2%84%A2", handler:"xed.handleCharacter('%E2%84%A2')"},
			{html:"%E3%8F%82", handler:"xed.handleCharacter('%E3%8F%82')"},
			{html:"%E3%8F%98", handler:"xed.handleCharacter('%E3%8F%98')"},
			{html:"%E2%84%A1", handler:"xed.handleCharacter('%E2%84%A1')"}
		]};
		
		this.config.defaultToolbarButtons.emoticon = {className:"emoticon", title:this._("Emoticon"), list: [
            {html:"num1.gif", handler:"xed.handleEmoticon('num1.gif')"},
            {html:"num2.gif", handler:"xed.handleEmoticon('num2.gif')"},
            {html:"num3.gif", handler:"xed.handleEmoticon('num3.gif')"},
            {html:"num4.gif", handler:"xed.handleEmoticon('num4.gif')"},
            {html:"num5.gif", handler:"xed.handleEmoticon('num5.gif')"},
            {html:"question.gif", handler:"xed.handleEmoticon('question.gif')"},
            {html:"disk.gif", handler:"xed.handleEmoticon('disk.gif')"},
            {html:"play.gif", handler:"xed.handleEmoticon('play.gif')"},
            {html:"flag1.gif", handler:"xed.handleEmoticon('flag1.gif')"},
            {html:"flag2.gif", handler:"xed.handleEmoticon('flag2.gif')"},
            {html:"flag3.gif", handler:"xed.handleEmoticon('flag3.gif')"},
            {html:"flag4.gif", handler:"xed.handleEmoticon('flag4.gif')"},
            {html:"arrow_left.gif", handler:"xed.handleEmoticon('arrow_left.gif')"},
            {html:"arrow_right.gif", handler:"xed.handleEmoticon('arrow_right.gif')"},
            {html:"arrow_up.gif", handler:"xed.handleEmoticon('arrow_up.gif')"},
            {html:"arrow_down.gif", handler:"xed.handleEmoticon('arrow_down.gif')"},
            {html:"step1.gif", handler:"xed.handleEmoticon('step1.gif')"},
            {html:"step2.gif", handler:"xed.handleEmoticon('step2.gif')"},
            {html:"step3.gif", handler:"xed.handleEmoticon('step3.gif')"},
            {html:"note.gif", handler:"xed.handleEmoticon('note.gif')"},
            {html:"heart.gif", handler:"xed.handleEmoticon('heart.gif')"},
            {html:"good.gif", handler:"xed.handleEmoticon('good.gif')"},
            {html:"bad.gif", handler:"xed.handleEmoticon('bad.gif')"}
		]};
		
		this.config.defaultToolbarButtons.html = {className:"html", title:this._("Edit source"), handler:"xed.toggleSourceAndWysiwygMode()" };
		
		this.config.defaultToolbarButtons.undo = {className:"undo", title:this._("Undo"), handler:"xed.handleUndo()" };
		this.config.defaultToolbarButtons.redo = {className:"redo", title:this._("Redo"), handler:"xed.handleRedo()" };

		this.config.defaultToolbarButtonGroups = {
			"color": [
			          this.config.defaultToolbarButtons.foregroundColor,
			          this.config.defaultToolbarButtons.backgroundColor
 			],
 			"font": [
 					this.config.defaultToolbarButtons.fontFace,
 					this.config.defaultToolbarButtons.fontSize
			],
			"link": [
				this.config.defaultToolbarButtons.link,
				this.config.defaultToolbarButtons.removeLink
			],
			"style": [
		  		this.config.defaultToolbarButtons.strongEmphasis,
				this.config.defaultToolbarButtons.emphasis,
				this.config.defaultToolbarButtons.underline,
				this.config.defaultToolbarButtons.strike,
				this.config.defaultToolbarButtons.superscription,
				this.config.defaultToolbarButtons.subscription,
				this.config.defaultToolbarButtons.removeFormat
			],
			"justification": [
          		this.config.defaultToolbarButtons.justifyLeft,
        		this.config.defaultToolbarButtons.justifyCenter,
        		this.config.defaultToolbarButtons.justifyRight,
        		this.config.defaultToolbarButtons.justifyBoth
			],
			"indentation": [
        		this.config.defaultToolbarButtons.indent,
        		this.config.defaultToolbarButtons.outdent
  			],
  			"block": [
				this.config.defaultToolbarButtons.blockquote,
				this.config.defaultToolbarButtons.code,
				this.config.defaultToolbarButtons.division,
				this.config.defaultToolbarButtons.unorderedList,
				this.config.defaultToolbarButtons.orderedList
  			],
  			"insert": [
  			    this.config.defaultToolbarButtons.table,
  				this.config.defaultToolbarButtons.separator,
  				this.config.defaultToolbarButtons.character,
  				this.config.defaultToolbarButtons.emoticon
  			],
  			"html": [
  				this.config.defaultToolbarButtons.html
	        ],
	        "undo": [
  				this.config.defaultToolbarButtons.undo,
  				this.config.defaultToolbarButtons.redo
  			]
		};
		
		/**
		 * Button map for default toolbar
		 * 
		 * @type Object
		 */
		this.config.defaultToolbarButtonMap = [
		    this.config.defaultToolbarButtonGroups.font,
		    this.config.defaultToolbarButtonGroups.color,
		    this.config.defaultToolbarButtonGroups.style,
		    this.config.defaultToolbarButtonGroups.justification,
		    this.config.defaultToolbarButtonGroups.indentation,
		    this.config.defaultToolbarButtonGroups.block,
		    this.config.defaultToolbarButtonGroups.link,
		    this.config.defaultToolbarButtonGroups.insert,
		    this.config.defaultToolbarButtonGroups.html,
		    this.config.defaultToolbarButtonGroups.undo
		];
	},
	
	/////////////////////////////////////////////
	// Configuration Management
	
	getDefaultShortcuts: function() {
		if(xq.Browser.isMac) {
			// Mac FF & Safari
			return [
				{event:"Ctrl+Shift+SPACE", handler:"this.handleAutocompletion(); stop = true;"},
				{event:"SPACE", handler:"this.handleSpace()"},
				{event:"ENTER", handler:"this.handleEnter(false, false)"},
				{event:"Ctrl+ENTER", handler:"this.handleEnter(true, false)"},
				{event:"Ctrl+Shift+ENTER", handler:"this.handleEnter(true, true)"},
				{event:"TAB", handler:"this.handleTab()"},
				{event:"Shift+TAB", handler:"this.handleShiftTab()"},
				{event:"DELETE", handler:"this.handleDelete()"},
				{event:"BACKSPACE", handler:"this.handleBackspace()"},
				
				{event:"Ctrl+B", handler:"this.handleStrongEmphasis()"},
				{event:"Meta+B", handler:"this.handleStrongEmphasis()"},
				{event:"Ctrl+I", handler:"this.handleEmphasis()"},
				{event:"Meta+I", handler:"this.handleEmphasis()"},
				{event:"Ctrl+U", handler:"this.handleUnderline()"},
				{event:"Meta+U", handler:"this.handleUnderline()"},
				{event:"Ctrl+K", handler:"this.handleStrike()"},
				{event:"Meta+K", handler:"this.handleStrike()"},
				{event:"Meta+Z", handler:"this.handleUndo()"},
				{event:"Meta+Shift+Z", handler:"this.handleRedo()"},
				{event:"Meta+Y", handler:"this.handleRedo()"}
			];
		} else if(xq.Browser.isUbuntu) {
			//  Ubunto FF
			return [
				{event:"Ctrl+SPACE", handler:"this.handleAutocompletion(); stop = true;"},
				{event:"SPACE", handler:"this.handleSpace()"},
				{event:"ENTER", handler:"this.handleEnter(false, false)"},
				{event:"Ctrl+ENTER", handler:"this.handleEnter(true, false)"},
				{event:"Ctrl+Shift+ENTER", handler:"this.handleEnter(true, true)"},
				{event:"TAB", handler:"this.handleTab()"},
				{event:"Shift+TAB", handler:"this.handleShiftTab()"},
				{event:"DELETE", handler:"this.handleDelete()"},
				{event:"BACKSPACE", handler:"this.handleBackspace()"},
			
				{event:"Ctrl+B", handler:"this.handleStrongEmphasis()"},
				{event:"Ctrl+I", handler:"this.handleEmphasis()"},
				{event:"Ctrl+U", handler:"this.handleUnderline()"},
				{event:"Ctrl+K", handler:"this.handleStrike()"},
				{event:"Ctrl+Z", handler:"this.handleUndo()"},
				{event:"Ctrl+Shift+Z", handler:"this.handleRedo()"},
				{event:"Ctrl+Y", handler:"this.handleRedo()"}
			];
		} else {
			// Win IE & FF
			return [
				{event:"Ctrl+SPACE", handler:"this.handleAutocompletion(); stop = true;"},
				{event:"SPACE", handler:"this.handleSpace()"},
				{event:"ENTER", handler:"this.handleEnter(false, false)"},
				{event:"Ctrl+ENTER", handler:"this.handleEnter(true, false)"},
				{event:"Ctrl+Shift+ENTER", handler:"this.handleEnter(true, true)"},
				{event:"TAB", handler:"this.handleTab()"},
				{event:"Shift+TAB", handler:"this.handleShiftTab()"},
				{event:"DELETE", handler:"this.handleDelete()"},
				{event:"BACKSPACE", handler:"this.handleBackspace()"},
			
				{event:"Ctrl+B", handler:"this.handleStrongEmphasis()"},
				{event:"Ctrl+I", handler:"this.handleEmphasis()"},
				{event:"Ctrl+U", handler:"this.handleUnderline()"},
				{event:"Ctrl+K", handler:"this.handleStrike()"},
				{event:"Ctrl+Z", handler:"this.handleUndo()"},
				{event:"Ctrl+Shift+Z", handler:"this.handleRedo()"},
				{event:"Ctrl+Y", handler:"this.handleRedo()"}
			];
		}
	},
	
	/**
	 * Adds or replaces plugin.
	 *
	 * @param {String} id unique identifier
	 */
	addPlugin: function(id) {
		// already added?
		if(this.config.plugins[id]) return;
		
		// else
		var clazz = xq.plugin[id + "Plugin"];
		if(!clazz) throw "Unknown plugin id: [" + id + "]";
		
		var plugin = new clazz();
		this.config.plugins[id] = plugin;
		plugin.load(this);
	},

	/**
	 * Adds several plugins at once.
	 *
	 * @param {Array} list of plugin ids.
	 */
	addPlugins: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addPlugin(list[i]);
		}
	},
	
	/**
	 * Returns plugin matches with given identifier.
	 *
	 * @param {String} id unique identifier
	 */
	getPlugin: function(id) {return this.config.plugins[id];},

	/**
	 * Returns entire plugins
	 */
	getPlugins: function() {return this.config.plugins;},
	
	/**
	 * Remove plugin matches with given identifier.
	 *
	 * @param {String} id unique identifier
	 */
	removePlugin: function(id) {
		var plugin = this.config.shortcuts[id];
		if(plugin) {
			plugin.unload();
		}
		
		delete this.config.shortcuts[id];
	},
	
	
	
	/**
	 * Adds or replaces keyboard shortcut.
	 *
	 * @param {String} shortcut keymap expression like "CTRL+Space"
	 * @param {Object} handler string or function to be evaluated or called
	 */
	addShortcut: function(shortcut, handler) {
		this.config.shortcuts[shortcut] = {"event":new xq.Shortcut(shortcut), "handler":handler};
	},
	
	/**
	 * Adds several keyboard shortcuts at once.
	 *
	 * @param {Array} list of shortcuts. each element should have following structure: {event:"keymap expression", handler:handler}
	 */
	addShortcuts: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addShortcut(list[i].event, list[i].handler);
		}
	},

	/**
	 * Returns keyboard shortcut matches with given keymap expression.
	 *
	 * @param {String} shortcut keymap expression like "CTRL+Space"
	 */
	getShortcut: function(shortcut) {return this.config.shortcuts[shortcut];},

	/**
	 * Returns entire keyboard shortcuts' map
	 */
	getShortcuts: function() {return this.config.shortcuts;},
	
	/**
	 * Remove keyboard shortcut matches with given keymap expression.
	 *
	 * @param {String} shortcut keymap expression like "CTRL+Space"
	 */
	removeShortcut: function(shortcut) {delete this.config.shortcuts[shortcut];},
	
	/**
	 * Adds or replaces autocorrection handler.
	 *
	 * @param {String} id unique identifier
	 * @param {Object} criteria regex pattern or function to be used as a criterion for match
	 * @param {Object} handler string or function to be evaluated or called when criteria met
	 */
	addAutocorrection: function(id, criteria, handler) {
		if(criteria.exec) {
			var pattern = criteria;
			criteria = function(text) {return text.match(pattern)};
		}
		this.config.autocorrections[id] = {"criteria":criteria, "handler":handler};
	},
	
	/**
	 * Adds several autocorrection handlers at once.
	 *
	 * @param {Array} list of autocorrection. each element should have following structure: {id:"identifier", criteria:criteria, handler:handler}
	 */
	addAutocorrections: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addAutocorrection(list[i].id, list[i].criteria, list[i].handler);
		}
	},
	
	/**
	 * Returns autocorrection handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	getAutocorrection: function(id) {return this.config.autocorrection[id];},
	
	/**
	 * Returns entire autocorrections' map
	 */
	getAutocorrections: function() {return this.config.autocorrections;},
	
	/**
	 * Removes autocorrection handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	removeAutocorrection: function(id) {delete this.config.autocorrections[id];},
	
	/**
	 * Adds or replaces autocompletion handler.
	 *
	 * @param {String} id unique identifier
	 * @param {Object} criteria regex pattern or function to be used as a criterion for match
	 * @param {Object} handler string or function to be evaluated or called when criteria met
	 */
	addAutocompletion: function(id, criteria, handler) {
		if(criteria.exec) {
			var pattern = criteria;
			criteria = function(text) {
				var m = pattern.exec(text);
				return m ? m.index : -1;
			};
		}
		this.config.autocompletions[id] = {"criteria":criteria, "handler":handler};
	},
	
	/**
	 * Adds several autocompletion handlers at once.
	 *
	 * @param {Array} list of autocompletion. each element should have following structure: {id:"identifier", criteria:criteria, handler:handler}
	 */
	addAutocompletions: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addAutocompletion(list[i].id, list[i].criteria, list[i].handler);
		}
	},
	
	/**
	 * Returns autocompletion handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	getAutocompletion: function(id) {return this.config.autocompletions[id];},
	
	/**
	 * Returns entire autocompletions' map
	 */
	getAutocompletions: function() {return this.config.autocompletions;},
	
	/**
	 * Removes autocompletion handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	removeAutocompletion: function(id) {delete this.config.autocompletions[id];},
	
	/**
	 * Adds or replaces template processor.
	 *
	 * @param {String} id unique identifier
	 * @param {Object} handler string or function to be evaluated or called when template inserted
	 */
	addTemplateProcessor: function(id, handler) {
		this.config.templateProcessors[id] = {"handler":handler};
	},
	
	/**
	 * Adds several template processors at once.
	 *
	 * @param {Array} list of template processors. Each element should have following structure: {id:"identifier", handler:handler}
	 */
	addTemplateProcessors: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addTemplateProcessor(list[i].id, list[i].handler);
		}
	},
	
	/**
	 * Returns template processor matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	getTemplateProcessor: function(id) {return this.config.templateProcessors[id];},

	/**
	 * Returns entire template processors' map
	 */
	getTemplateProcessors: function() {return this.config.templateProcessors;},

	/**
	 * Removes template processor matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	removeTemplateProcessor: function(id) {delete this.config.templateProcessors[id];},



	/**
	 * Adds or replaces context menu handler.
	 *
	 * @param {String} id unique identifier
	 * @param {Object} handler string or function to be evaluated or called when onContextMenu occured
	 */
	addContextMenuHandler: function(id, handler) {
		this.config.contextMenuHandlers[id] = {"handler":handler};
	},
	
	/**
	 * Adds several context menu handlers at once.
	 *
	 * @param {Array} list of handlers. Each element should have following structure: {id:"identifier", handler:handler}
	 */
	addContextMenuHandlers: function(list) {
		for(var i = 0; i < list.length; i++) {
			this.addContextMenuHandler(list[i].id, list[i].handler);
		}
	},
	
	/**
	 * Returns context menu handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	getContextMenuHandler: function(id) {return this.config.contextMenuHandlers[id];},

	/**
	 * Returns entire context menu handlers' map
	 */
	getContextMenuHandlers: function() {return this.config.contextMenuHandlers;},

	/**
	 * Removes context menu handler matches with given id
	 *
	 * @param {String} id unique identifier
	 */
	removeContextMenuHandler: function(id) {delete this.config.contextMenuHandlers[id];},
	
	
	
	/**
	 * Sets width of editor.
	 *
	 * @param {String} w Valid CSS value for style.width. For example, "100%", "200px".
	 */
	setWidth: function(w) {
		this.outmostWrapper.style.width = w;
	},
	
	
	
	/**
	 * Sets height of editor.
	 *
	 * @param {String} h Valid CSS value for style.height. For example, "100%", "200px".
	 */
	setHeight: function(h) {
		this.wysiwygEditorDiv.style.height = h;
		this.sourceEditorDiv.style.height = h;
	},
	
	
	
	/////////////////////////////////////////////
	// Edit mode management
	
	/**
	 * Returns current edit mode - wysiwyg, source
	 */
	getCurrentEditMode: function() {
		return this.currentEditMode;
	},
	
	/**
	 * Toggle edit mode between source and wysiwyg 
	 */
	toggleSourceAndWysiwygMode: function() {
		var mode = this.getCurrentEditMode();
		this.setEditMode(mode === 'wysiwyg' ? 'source' : 'wysiwyg');
	},
	
	/**
	 * Switches between WYSIWYG/Source mode.
	 *
	 * @param {String} mode 'wysiwyg' means WYSIWYG editing mode, and 'source' means source editing mode.
	 */
	setEditMode: function(mode) {
		if(typeof mode !== 'string') throw "[mode] is not a string."
		if(['wysiwyg', 'source'].indexOf(mode) === -1) throw "Illegal [mode] value: '" + mode + "'. Use 'wysiwyg' or 'source'";
		if(this.currentEditMode === mode) return;
		
		// create editor frame if there's no editor frame.  
		var editorCreated = !!this.outmostWrapper;
		if(!editorCreated) {
			// create validator
			this.validator = xq.validator.Base.createInstance(
				this.doc.location.href,
				this.config.urlValidationMode,
				this.config.whitelist
			);

			this._fireOnStartInitialization(this);

			this._createEditorFrame(mode);
			var temp = window.setInterval(function() {
				// wait for loading
				if(this.getBody()) {
					window.clearInterval(temp);
	
					// @WORKAROUND: it is needed to fix IE6 horizontal scrollbar problem
					if(xq.Browser.isIE6) {
						this.rdom.getDoc().documentElement.style.overflowY='auto';
						this.rdom.getDoc().documentElement.style.overflowX='hidden';
					}
					
					this.setEditMode(mode);
					this.PreventExit.defaultContent = this.getCurrentContent().stripTags();
					
					if(this.config.autoFocusOnInit) this.focus();
					
					this.timer.start();
					this._fireOnInitialized(this);
				}


			}.bind(this), 10);
			
			return;
		}
		
		// switch mode
		if(mode === 'wysiwyg') {
			this._setEditModeToWysiwyg();
		} else { // mode === 'source'
			this._setEditModeToSource();
		}
		
		// fire event
		var oldEditMode = this.currentEditMode;
		this.currentEditMode = mode;
		
		this._fireOnCurrentEditModeChanged(this, oldEditMode, this.currentEditMode);
	},
	
	_setEditModeToWysiwyg: function() {
		// Turn off static content and source editor
		this.contentElement.style.display = "none";
		this.sourceEditorDiv.style.display = "none";
		
		// Update contents
		if(this.currentEditMode === 'source') {
			// get html from source editor
			var html = this.getSourceContent(true);
			
			// invalidate it and load it into wysiwyg editor
			var invalidHtml = this.validator.invalidate(html);
			invalidHtml = this.removeUnnecessarySpaces(invalidHtml);
			if(invalidHtml.isBlank()) {
				this.rdom.clearRoot();
			} else {
				this.rdom.getRoot().innerHTML = invalidHtml;
				this.rdom.wrapAllInlineOrTextNodesAs("P", this.rdom.getRoot(), true);
			}
		} else {
			// invalidate static html and load it into wysiwyg editor
			var invalidHtml = this.validator.invalidate(this.getStaticContent());
			invalidHtml = this.removeUnnecessarySpaces(invalidHtml);
			if(invalidHtml.isBlank()) {
				this.rdom.clearRoot();
			} else {
				this.rdom.getRoot().innerHTML = invalidHtml;
				this.rdom.wrapAllInlineOrTextNodesAs("P", this.rdom.getRoot(), true);
			}
		}
		
		// Turn on wysiwyg editor
		this.wysiwygEditorDiv.style.display = "block";
		this.outmostWrapper.style.display = "block";
		
		// Without this, xq.rdom.Base.focus() doesn't work correctly.
		if(xq.Browser.isGecko) this.rdom.placeCaretAtStartOf(this.rdom.getRoot());
		
		if(this.toolbar) this.toolbar.enableButtons();
	},
	
	_setEditModeToSource: function() {
		// Update contents
		var validHtml = null;
		if(this.currentEditMode === 'wysiwyg') {
			validHtml = this.getWysiwygContent();
		} else {
			validHtml = this.getStaticContent();
		}
		this.sourceEditorTextarea.value = validHtml

		// Turn off static content and wysiwyg editor
		this.contentElement.style.display = "none";
		this.wysiwygEditorDiv.style.display = "none";

		// Turn on source editor
		this.sourceEditorDiv.style.display = "block";
		this.outmostWrapper.style.display = "block";
		if(this.toolbar) this.toolbar.disableButtons(['html']);
	},
	
	/**
	 * Load CSS into WYSIWYG mode document
	 *
	 * @param {string} path URL
	 */
	loadStylesheet: function(path) {
		var head = this.getDoc().getElementsByTagName("HEAD")[0];
		var link = this.getDoc().createElement("LINK");
		link.rel = "Stylesheet";
		link.type = "text/css";
		link.href = path;
		head.appendChild(link);
	},
	
	/**
	 * Sets editor's dynamic content from static content
	 */
	loadCurrentContentFromStaticContent: function() {
		if(this.getCurrentEditMode() == 'wysiwyg') {
			// update WYSIWYG editor
			var html = this.validator.invalidate(this.getStaticContent());
			html = this.removeUnnecessarySpaces(html);
			
			if(html.isBlank()) {
				this.rdom.clearRoot();
			} else {
				this.rdom.getRoot().innerHTML = html;
				this.rdom.wrapAllInlineOrTextNodesAs("P", this.rdom.getRoot(), true);
			}
		} else { // 'source'
			this.sourceEditorTextarea.value = this.getStaticContent();
		}
		
		this._fireOnCurrentContentChanged(this);
	},

	/**
	 * Removes unnecessary spaces, tabs and new lines.
	 * 
	 * @param {String} html HTML string.
	 * @returns {String} Modified HTML string.
	 */
	removeUnnecessarySpaces: function(html) {
		var blocks = this.rdom.tree.getBlockTags().join("|");
		var regex = new RegExp("\\s*<(/?)(" + blocks + ")>\\s*", "img");
		return html.replace(regex, '<$1$2>');
	},
	
	/**
	 * Gets editor's dynamic content from current editor(source or WYSIWYG)
	 * 
	 * @return {Object} HTML String
	 */
	getCurrentContent: function() {
		if(this.getCurrentEditMode() === 'source') {
			return this.getSourceContent(this.config.noValidationInSourceEditMode);
		} else {
			return this.getWysiwygContent();
		}
	},
	
	/**
	 * Gets editor's dynamic content from WYSIWYG editor
	 * 
	 * @return {Object} HTML String
	 */
	getWysiwygContent: function() {
		return this.validator.validate(this.rdom.getRoot());
	},
	
	/**
	 * Gets editor's dynamic content from source editor
	 * 
	 * @return {Object} HTML String
	 */
	getSourceContent: function(noValidation) {
		var raw = this.sourceEditorTextarea.value;
		if(noValidation) return raw;
		
		var tempDiv = document.createElement('div');
		tempDiv.innerHTML = this.removeUnnecessarySpaces(raw);
		
		var rdom = xq.rdom.Base.createInstance();
		rdom.wrapAllInlineOrTextNodesAs("P", tempDiv, true);
		
		return this.validator.validate(tempDiv, true);
	},
	
	/**
	 * Sets editor's original content
	 *
	 * @param {Object} content HTML String
	 */
	setStaticContent: function(content) {
		this.contentElement.value = content;
		this._fireOnStaticContentChanged(this, content);
	},
	
	/**
	 * Gets editor's original content
	 *
	 * @return {Object} HTML String
	 */
	getStaticContent: function() {
		return this.contentElement.value;
	},
	
	/**
	 * Gets editor's original content as (newely created) DOM node
	 *
	 * @return {Element} DIV element
	 */
	getStaticContentAsDOM: function() {
		var div = this.doc.createElement('DIV');
		div.innerHTML = this.contentElement.value;
		return div;
	},
	
	/**
	 * Gives focus to editor
	 */
	focus: function() {
		if(this.getCurrentEditMode() === 'wysiwyg') {
			this.rdom.focus();
			if(this.toolbar) this.toolbar.triggerUpdate();
		} else if(this.getCurrentEditMode() === 'source') {
			this.sourceEditorTextarea.focus();
		}
	},
	
	getWysiwygEditorDiv: function() {
		return this.wysiwygEditorDiv;
	},
	
	getSourceEditorDiv: function() {
		return this.sourceEditorDiv;
	},
	
	/**
	 * Returns outer iframe object
	 */
	getOuterFrame: function() {
		return this.outerFrame;
	},
	
	/**
	 * Returns outer iframe document
	 */
	getOuterDoc: function() {
		return this.outerFrame.contentWindow.document;
	},
	
	/**
	 * Returns designmode iframe object
	 */
	getFrame: function() {
		return this.editorFrame;
	},
	
	/**
	 * Returns designmode window object
	 */
	getWin: function() {
		return this.rdom.getWin();
	},
	
	/**
	 * Returns designmode document object
	 */
	getDoc: function() {
		return this.rdom.getDoc();
	},
	
	/**
	 * Returns designmode body object
	 */
	getBody: function() {
		return this.rdom.getRoot();
	},
	
	/**
	 * Returns outmost wrapper element
	 */
	getOutmostWrapper: function() {
		return this.outmostWrapper;
	},
	
	_createIFrame: function(doc, width, height) {
		var frame = doc.createElement("iframe");
		
		// IE displays warning when a protocol is HTTPS, because IE6 treats IFRAME
		// without SRC attribute as insecure.
		// if(xq.Browser.isIE) frame.src = 'javascript:""';
		
		frame.style.width = width || "100%";
		frame.style.height = height || "100%";
		frame.setAttribute("frameBorder", "0");
		frame.setAttribute("marginWidth", "0");
		frame.setAttribute("marginHeight", "0");
		frame.setAttribute("allowTransparency", "auto");
		return frame;
	},

	_createDoc: function(frame, head, cssList, jsList, bodyId, bodyClass, body) {
		var sb = [];
		if(!xq.Browser.isTrident) {
			// @WORKAROUND: IE6/7 has caret movement and scrolling problem if I include following DTD.
			sb.push('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">');
		}
		sb.push('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">');
		sb.push('<head>');
		sb.push('<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />');
		if(head) sb.push(head);

		if(cssList) for(var i = 0; i < cssList.length; i++) {
			sb.push('<link rel="Stylesheet" type="text/css" href="' + cssList[i] + '" />');
		}
		
		if(jsList) for(var i = 0; i < jsList.length; i++) {
			sb.push('<script type="text/javascript" src="' + jsList[i] + '"></script>');
		}
		
		sb.push('</head>');
		sb.push('<body ' + (bodyClass ? 'class="' + bodyClass + '"' : '') + ' ' + (bodyId ? 'id="' + bodyId + '"' : '') + '>');
		if(body) sb.push(body);
		sb.push('</body>');
		sb.push('</html>');
		
		var doc = frame.contentWindow.document;
		doc.open();
		doc.write(sb.join(""));
		doc.close();
		return doc;
	},

	_createEditorFrame: function(mode) {
		// turn off static content
		this.contentElement.style.display = "none";
		
		// create outer DIV
		this.outmostWrapper = this.doc.createElement('div');
		this.outmostWrapper.className = "xquared";
		this.outmostWrapper.style.position = "relative";
		this.contentElement.parentNode.insertBefore(this.outmostWrapper, this.contentElement);
		
		// create toolbar
		
		if(this.toolbarContainer || this.config.generateDefaultToolbar || this.config.defaultToolbarButtonList.length > 0) {
			this.toolbar = new xq.ui.Toolbar(
				this,
				this.toolbarContainer,
				this.outmostWrapper,
				this.config.defaultToolbarButtonMap,
				this.config.defaultToolbarButtonList,
				this.config.imagePathForDefaultToolbar,
				function() {
					var element = this.getCurrentEditMode() === 'wysiwyg' ? this.lastFocusElement : null;
					return element && element.nodeName != "BODY" ? this.rdom.collectStructureAndStyle(element) : null;
				}.bind(this)
			);
		}
		
		// create source editor div
		this.sourceEditorDiv = this.doc.createElement('div');
		this.sourceEditorDiv.className = "editor source_editor"; //TODO: remove editor
		this.sourceEditorDiv.style.display = "none";
		this.outmostWrapper.appendChild(this.sourceEditorDiv);
		
		// create TEXTAREA for source editor
		this.sourceEditorTextarea = this.doc.createElement('textarea');
		this.sourceEditorDiv.appendChild(this.sourceEditorTextarea);
		
		// create WYSIWYG editor div
		this.wysiwygEditorDiv = this.doc.createElement('div');
		this.wysiwygEditorDiv.className = "editor wysiwyg_editor"; //TODO: remove editor
		this.outmostWrapper.appendChild(this.wysiwygEditorDiv);
		
		// create outer iframe for WYSIWYG editor
		this.outerFrame = this._createIFrame(document);
		this.wysiwygEditorDiv.appendChild(this.outerFrame);
		var outerDoc = this._createDoc(
			this.outerFrame,
			'<style type="text/css">html, body {margin:0px; padding:0px; background-color: transparent; width: 100%; height: 100%; overflow: hidden;}</style>'
		);

		// create designmode iframe for WYSIWYG editor
		this.editorFrame = this._createIFrame(outerDoc);
		
		outerDoc.body.appendChild(this.editorFrame);
		var editorDoc = this._createDoc(
			this.editorFrame,
			'<style type="text/css">html, body {margin:0px; padding:0px; background-color: transparent;}</style>' +
			(!xq.Browser.isTrident ? '<base href="./" />' : '') + // @WORKAROUND: it is needed to force href of pasted content to be an absolute url
			(this.config.changeCursorOnLink ? '<style>.xed a {cursor: pointer !important;}</style>' : ''),
			this.config.contentCssList,
			this.config.contentJsList,
			this.config.bodyId,
			this.config.bodyClass,
			''
		);
		this.rdom.setWin(this.editorFrame.contentWindow);
		this.editHistory = new xq.EditHistory(this.rdom);
		
		// turn on designmode
		if(xq.Browser.isIE){
			this.getBody().contentEditable = true;
		} else {
			this.rdom.getDoc().designMode = "On";
		}
		if(xq.Browser.isFF3) {
			this.rdom.getDoc().designMode = "Off";
			this.rdom.getDoc().designMode = "On";
		}
		
		// turn off Firefox's table editing feature
		if(xq.Browser.isGecko) {
			try {this.rdom.getDoc().execCommand("enableInlineTableEditing", false, "false")} catch(ignored) {}
		}
		
		// register event handlers
		this._registerEventHandlers();
		
		// hook onsubmit of form
		if(this.config.automaticallyHookSubmitEvent && this.contentElement.form) {
			var original = this.contentElement.form.onsubmit;
			this.contentElement.form.onsubmit = function() {
				xed.config.enablePreventExit === false;
				this.contentElement.value = this.getCurrentContent();
				return original ? original.bind(this.contentElement.form)() : true;
			}.bind(this);
		}
	},
	
	
	
	/////////////////////////////////////////////
	// Event Management
	
	_registerEventHandlers: function() {
		var events = [this.platformDepedentKeyEventType, 'click', 'keyup', 'mouseup', 'contextmenu'];
		
		if(xq.Browser.isTrident && this.config.changeCursorOnLink) events.push('mousemove');
		
		var handler = this._handleEvent.bindAsEventListener(this);
		for(var i = 0; i < events.length; i++) {
			xq.observe(this.getDoc(), events[i], handler);
		}
		
		if(xq.Browser.isGecko) {
			xq.observe(this.getDoc(), "focus", handler);
			xq.observe(this.getDoc(), "blur", handler);
			xq.observe(this.getDoc(), "scroll", handler);
			xq.observe(this.getDoc(), "dragdrop", handler);
		} else {
			xq.observe(this.getWin(), "focus", handler);
			xq.observe(this.getWin(), "blur", handler);
			xq.observe(this.getWin(), "scroll", handler);
		}
	},
	
	_dummyLink: null,
	_makeDummyLink: function(anchor, e){
		// Trident only
		var dummyLink = this.getOuterDoc().createElement("A");
		dummyLink.href = anchor.href;
		dummyLink.target = '_top';
		dummyLink.className = anchor.className;
		dummyLink.title = anchor.title;
		
		var image = this.getOuterDoc().createElement("IMG");
		image.src = this.config.imagePathForContent + 'blank.gif';
		image.style.width = image.style.height = '100%';
		image.style.border = 'none';
		
		dummyLink.appendChild(image);
		
		this.getOuterDoc().body.appendChild(dummyLink);
		this._dummyLink = dummyLink;
		
		dummyLink.style.top = (e.clientY - 5) + 'px';
		dummyLink.style.left = (e.clientX - 5) + 'px';
		dummyLink.onfocus = function(){
			this.blur();	
			return false;
		}		
				
		xq.observe(dummyLink, "click", this._handleEvent.bindAsEventListener(this));
		
		dummyLink.style.position = 'absolute';
		dummyLink.style.display = 'block';
		dummyLink.style.width = '10px';
		dummyLink.style.height = '10px';
		dummyLink.style.zIndex = '4';
	},
	
	_handleEvent: function(e) {
		if (!this._fireOnBeforeEvent) return;
		this._fireOnBeforeEvent(this, e);
		if(e.stopProcess) {
			xq.stopEvent(e);
			return false;
		}
		
		// Trident only
		if(e.type === 'mousemove') {
			if(!this.config.changeCursorOnLink) return true;
			
			var link = this.rdom.getParentElementOf(e.srcElement, ["A"]);
			if (this._dummyLink && this._dummyLink == link) return true;
			
			if (this._dummyLink) {
				this.getOuterDoc().body.removeChild(this._dummyLink);
				this._dummyLink = null;
			}
			if(!!link && !this.rdom.hasSelection()){
				this._makeDummyLink(link, e);
			}
			return true;
		}
		
		var stop = false;
		var modifiedByCorrection = false;
		if(e.type === this.platformDepedentKeyEventType) {
			var undoPerformed = false;
			modifiedByCorrection = this.rdom.correctParagraph();
			for(var key in this.config.shortcuts) {
				if(!this.config.shortcuts[key].event.matches(e)) continue;
				
				var handler = this.config.shortcuts[key].handler;
				var xed = this;
				stop = (typeof handler === "function") ? handler(this) : eval(handler);
				if(key === "undo") undoPerformed = true;
			}
		} else if(e.type === 'click' && e.button === 0 && this.config.enableLinkClick) {
			var a = this.rdom.getParentElementOf(e.target || e.srcElement, ["A"]);
			if(a) stop = this.handleClick(e, a);
		} else if(["keyup", "mouseup"].indexOf(e.type) !== -1) {
			modifiedByCorrection = this.rdom.correctParagraph();
		} else if(["contextmenu"].indexOf(e.type) !== -1) {
			this._handleContextMenu(e);
		} else if("focus" == e.type) {
			this.rdom.focused = true;
		} else if("blur" == e.type) {
			this.rdom.focused = false;
		}
		
		if(stop) xq.stopEvent(e);
		
		this._fireOnCurrentContentChanged(this);
		this._fireOnAfterEvent(this, e);
		
		if(!undoPerformed && !modifiedByCorrection && e.type != 'scroll') this.editHistory.onEvent(e);
		
		return !stop;
	},

	/**
	 * TODO: remove dup with handleAutocompletion
	 */
	handleAutocorrection: function() {
		var block = this.rdom.getCurrentBlockElement();
		var text = this.rdom.getInnerText(block).unescapeHTML();
		
		var acs = this.config.autocorrections;
		var performed = false;
		
		var stop = false;
		for(var key in acs) {
			var ac = acs[key];
			
			if(ac.criteria(text)) {
				try {
					this.editHistory.onCommand();
					this.editHistory.disable();
					if(typeof ac.handler === "String") {
						var xed = this;
						var rdom = this.rdom;
						eval(ac.handler);
					} else {
						stop = ac.handler(this, this.rdom, block, text);
					}
					this.editHistory.enable();
				} catch(ignored) {}
				
				block = this.rdom.getCurrentBlockElement();
				text = this.rdom.getInnerText(block);
				
				performed = true;
				if(stop) break;
			}
		}
		
		return stop;
	},
	
	/**
	 * TODO: remove dup with handleAutocorrection
	 */
	handleAutocompletion: function() {
		var acs = this.config.autocompletions;
		if(xq.isEmptyHash(acs)) return;
		
		if(this.rdom.hasSelection()) {
			var text = this.rdom.getSelectionAsText();
			this.rdom.deleteSelection();
			var wrapper = this.rdom.insertNode(this.rdom.createElement("SPAN"));
			this.rdom.insertTextAt(text, wrapper, "start");
			
			var marker = this.rdom.pushMarker();
			
			var filtered = [];
			for(var key in acs) {
				filtered.push([key, acs[key].criteria(text)]);
			}
			filtered = filtered.findAll(function(elem) {
				return elem[1] !== -1;
			});
			
			if(filtered.length === 0) {
				this.rdom.popMarker(true);
				return;
			}
			
			var minIndex = 0;
			var min = filtered[0][1];
			for(var i = 0; i < filtered.length; i++) {
				if(filtered[i][1] < min) {
					minIndex = i;
					min = filtered[i][1];
				}
			}
			
			var ac = acs[filtered[minIndex][0]];
			
			this.editHistory.disable();
			this.rdom.selectElement(wrapper);
		} else {
			var marker = this.rdom.pushMarker();

			var filtered = [];
			for(var key in acs) {
				filtered.push([key, this.rdom.testSmartWrap(marker, acs[key].criteria).textIndex]);
			}
			filtered = filtered.findAll(function(elem) {
				return elem[1] !== -1;
			});
			
			if(filtered.length === 0) {
				this.rdom.popMarker(true);
				return;
			}
			
			var minIndex = 0;
			var min = filtered[0][1];
			for(var i = 0; i < filtered.length; i++) {
				if(filtered[i][1] < min) {
					minIndex = i;
					min = filtered[i][1];
				}
			}
			
			var ac = acs[filtered[minIndex][0]];
			
			this.editHistory.disable();
			
			var wrapper = this.rdom.smartWrap(marker, "SPAN", ac.criteria);
			// editor lost a caret on Safari
			if (xq.Browser.isWebkit){
				this.rdom.popMarker(true);
				this.rdom.pushMarker();
			}
		}
		var block = this.rdom.getCurrentBlockElement();
		var text = this.rdom.getInnerText(wrapper).unescapeHTML();
		
		try {
			// call handler
			if(typeof ac.handler === "String") {
				var xed = this;
				var rdom = this.rdom;
				eval(ac.handler);
			} else {
				ac.handler(this, this.rdom, block, wrapper, text);
			}
		} catch(ignored) {}
		
		try {
			this.rdom.unwrapElement(wrapper);
		} catch(ignored) {}
		
		if(this.rdom.isEmptyBlock(block)) this.rdom.correctEmptyElement(block);
		
		this.editHistory.enable();
		this.editHistory.onCommand();
		
		this.rdom.popMarker(true);
	},

	/**
	 * Handles click event
	 *
	 * @param {Event} e click event
	 * @param {Element} target target element(usually has A tag)
	 */
	handleClick: function(e, target) {
		var href = decodeURI(target.href);
		var isNewWindow = target.className.indexOf('newWindow') != -1;
		if (isNewWindow) {
			window.open(href, "_blank");
			return true;
		}
		
		if(!xq.Browser.isTrident) {
			if(!e.ctrlKey && !e.shiftKey && e.button !== 1) {
				window.location.href = href;
				return true;
			}
		} else {
			if(e.shiftKey) {
				window.open(href, "_blank");
			} else {
				window.location.href = href;
			}
			return true;
		}
		
		return false;
	},
	lastLinkDialog: null,
	/**
	 * Show link dialog
	 *
	 * TODO: should support modify/unlink
	 * TODO: Add selenium test
	 */
	handleLink: function() {
		var linkDialog = xq.$('linkDialog');
		if (linkDialog && linkDialog.style.display != 'none') this.lastLinkDialog.close();
		
		var text = this.rdom.getSelectionAsText() || '';
		var dialog = new xq.ui.FormDialog(
			this,
			xq.ui_templates.basicLinkDialog,
			function(dialog) {
				setTimeout(function(){
					if(text) {
						dialog.form.text.value = text;
						dialog.form.url.focus();
						dialog.form.url.select();
					} else {
						dialog.form.text.focus();
					}
					
				}, 0);
			},
			function(data) {
				this.focus();
				
				if(xq.Browser.isTrident) {
					var rng = this.rdom.rng();
					rng.moveToBookmark(bm);
					rng.select();
				}
				
				if(!data) return;
					
				var urlRegex = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
				if( !urlRegex.test(data.url) )
				{
					alert( this._("Unknown URL pattern"));
					dialog.form.url.focus();
					return;
				}
				
				if (data.newWindow) var className = 'newWindow';
				
				this.handleInsertLink(false, data.url, data.text, data.text, className);
				
				dialog.close();
			}.bind(this)
		);
		
		if(xq.Browser.isTrident) var bm = this.rdom.rng().getBookmark();
		
		dialog.show({position: 'centerOfEditor', mode: 'lightweight', notSelfClose: true, dialogId:'linkDialog'});
		this.lastLinkDialog = dialog;
		return true;
	},
	
	/**
	 * Inserts link or apply link into selected area
	 * @TODO Add selenium test
	 * 
	 * @param {boolean} autoSelection if set true and there's no selection, automatically select word to link(if possible)
	 * @param {String} url url
	 * @param {String} title title of link
	 * @param {String} text text of link. If there's a selection(manually or automatically), it will be replaced with this text
	 *
	 * @returns {Element} created element
	 */
	handleInsertLink: function(autoSelection, url, title, text, className) {
		if(autoSelection && !this.rdom.hasSelection()) {
			var marker = this.rdom.pushMarker();
			var a = this.rdom.smartWrap(marker, "A", function(text) {
				var index = text.lastIndexOf(" ");
				return index === -1 ? index : index + 1;
			});
							
			a.href = url;
			a.title = title;
			if (className) a.className = className;
			
			if(text) {
				a.innerHTML = ""
				a.appendChild(this.rdom.createTextNode(text));
			} else if(!a.hasChildNodes()) {
				this.rdom.deleteNode(a);
			}
			this.rdom.popMarker(true);
		} else {
			text = text || (this.rdom.hasSelection() ? this.rdom.getSelectionAsText() : null);
			if(!text) return;
			
			this.rdom.deleteSelection();
			
			var a = this.rdom.createElement('A');
			a.href = url;
			a.title = title;
			if (className) a.className = className;
			
			a.appendChild(this.rdom.createTextNode(text));
			this.rdom.insertNode(a);
		}
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * @TODO Add selenium test
	 */
	handleSpace: function() {
		// If it has selection, perform default action.
		if(this.rdom.hasSelection()) return false;
		
		// Trident performs URL replacing automatically
		if(!xq.Browser.isTrident) {
			this.replaceUrlToLink();
		}
		
		return false;
	},
	
	/**
	 * Called when enter key pressed.
	 * @TODO Add selenium test
	 *
	 * @param {boolean} skipAutocorrection if set true, skips autocorrection
	 * @param {boolean} forceInsertParagraph if set true, inserts paragraph
	 */
	handleEnter: function(skipAutocorrection, forceInsertParagraph) {
		// If it has selection, perform default action.
		if(this.rdom.hasSelection()) return false;
		
		// @WORKAROUND:
		// If caret is in HR, default action should be performed and
		// this._handleEvent() will correct broken HTML
		if(xq.Browser.isTrident && this.rdom.tree.isBlockOnlyContainer(this.rdom.getCurrentElement()) && this.rdom.recentHR) {
			this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(), this.rdom.recentHR, "before");
			this.rdom.recentHR = null;
			return true;
		}
		
		// Perform autocorrection
		if(!skipAutocorrection && this.handleAutocorrection()) return true;
		
		var block = this.rdom.getCurrentBlockElement();
		var info = this.rdom.collectStructureAndStyle(block);
		
		// Perform URL replacing. Trident performs URL replacing automatically
		if(!xq.Browser.isTrident) {
			this.replaceUrlToLink();
		}
		
		var atEmptyBlock = this.rdom.isCaretAtEmptyBlock();
		var atStart = atEmptyBlock || this.rdom.isCaretAtBlockStart();
		var atEnd = atEmptyBlock || (!atStart && this.rdom.isCaretAtBlockEnd());
		var atEdge = atEmptyBlock || atStart || atEnd;
		
		if(!atEdge) {
			var marker = this.rdom.pushMarker();
			
			if(this.rdom.isFirstLiWithNestedList(block) && !forceInsertParagraph) {
				var parent = block.parentNode;
				this.rdom.unwrapElement(block);
				block = parent;
			} else if(block.nodeName !== "LI" && this.rdom.tree.isBlockContainer(block)) {
				block = this.rdom.wrapAllInlineOrTextNodesAs("P", block, true).first();
			}
			this.rdom.splitElementUpto(marker, block);
			
			this.rdom.popMarker(true);
		} else if(atEmptyBlock) {
			this._handleEnterAtEmptyBlock();

			if(!xq.Browser.isWebkit) {
				if(info.fontSize && info.fontSize !== "2") this.handleFontSize(info.fontSize);
				if(info.fontName) this.handleFontFace(info.fontName);
			}
		} else {
			this._handleEnterAtEdge(atStart, forceInsertParagraph);
			
			if(!xq.Browser.isWebkit) {
				if(info.fontSize && info.fontSize !== "2") this.handleFontSize(info.fontSize);
				if(info.fontName) this.handleFontFace(info.fontName);
				if(info.foregroundColor) this.handleForegroundColor(info.foregroundColor);
			}
		}
		
		return true;
	},
	
	/**
	 * Moves current block upward or downward
	 *
	 * @param {boolean} up moves current block upward
	 */
	handleMoveBlock: function(up) {
		var block = this.rdom.moveBlock(this.rdom.getCurrentBlockElement(), up);
		if(block) {
			this.rdom.selectElement(block, false);
			if(this.rdom.isEmptyBlock(block)) this.rdom.collapseSelection(true);
			
			if(!this.isElementVisible(block)) block.scrollIntoView(false);
			
			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
		}
		return true;
	},
	
	/**
	 * Called when tab key pressed
	 * @TODO: Add selenium test
	 */
	handleTab: function() {
		var hasSelection = this.rdom.hasSelection();
		var table = this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(), ["TABLE"]);
		var li = this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(), ["LI"]);
		
		if(hasSelection) {
			this.handleIndent();
		} else if (table && !li) {
			this.handleMoveToNextCell();
		} else if (this.rdom.isCaretAtBlockStart()) {
			this.handleIndent();
		} else {
			this.handleInsertTab();
		}

		return true;
	},
	
	/**
	 * Called when shift+tab key pressed
	 * @TODO: Add selenium test
	 */
	handleShiftTab: function() {
		var hasSelection = this.rdom.hasSelection();
		var table = this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(), ["TABLE"]);
		var li = this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(), ["LI"]);
		
		if(hasSelection) {
			this.handleOutdent();
		} else if (table && !li) {
			this.handleMoveToPreviousCell();
		} else {
			this.handleOutdent();
		}
		
		return true;
	},
	
	/**
	 * Inserts three non-breaking spaces
	 * @TODO: Add selenium test
	 */
	handleInsertTab: function() {
		this.rdom.insertHtml('&nbsp;');
		this.rdom.insertHtml('&nbsp;');
		this.rdom.insertHtml('&nbsp;');
		
		return true;
	},
	
	/**
	 * Called when delete key pressed
	 * @TODO: Add selenium test
	 */
	handleDelete: function() {
		var block = this.rdom.getCurrentBlockElement();
		
		if(this.rdom.hasSelection()) return false;
		if (this.rdom.isEmptyBlock(block) && this.rdom.isCaretAtBlockEnd() && !block.nextSibling) {
			if (this.rdom.tree.isListContainer(block.parentNode))
				return false;
			return true;
		}
		if(this.rdom.isCaretAtBlockEnd()) return this._handleMerge(true);
		if(!xq.Browser.isFF) return false;
		
		var element = this.rdom.getCurrentElement();
		for(var i = 0; i < element.childNodes.length; i++){
			var node = element.childNodes[i]
			if (node.nodeName == '#text' && !node.nodeValue.length) xed.rdom.deleteNode(node);
		}
		return false;
	},
	
	/**
	 * Called when backspace key pressed
	 * @TODO: Add selenium test
	 */
	handleBackspace: function() {
		if(this.rdom.hasSelection() || !this.rdom.isCaretAtBlockStart()) return false;
		return this._handleMerge(false);
	},
	
	_handleMerge: function(withNext) {
		var block = this.rdom.getCurrentBlockElement();
		
		if(this.rdom.isEmptyBlock(block) && !this.rdom.tree.isBlockContainer(block.nextSibling) && withNext) {
			var blockToMove = this.rdom.removeBlock(block);
			this.rdom.placeCaretAtStartOf(blockToMove);
			if(!this.isElementVisible(blockToMove)) blockToMove.scrollIntoView(false);

			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
			
			return true;
		} else {
			// save caret position;
			var marker = this.rdom.pushMarker();

			// perform merge
			var merged = this.rdom.mergeElement(block, withNext, withNext);
			if(!merged && !withNext) this.rdom.extractOutElementFromParent(block);
			
			// restore caret position
			this.rdom.popMarker(true);
			if(merged) this.rdom.correctEmptyElement(merged);
			
			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
			return !!merged;
		}
	},
	
	/**
	 * (in table) Moves caret to the next cell
	 * @TODO: Add selenium test
	 */
	handleMoveToNextCell: function() {
		this._handleMoveToCell("next");
	},

	/**
	 * (in table) Moves caret to the previous cell
	 * @TODO: Add selenium test
	 */
	handleMoveToPreviousCell: function() {
		this._handleMoveToCell("prev");
	},

	/**
	 * (in table) Moves caret to the above cell
	 * @TODO: Add selenium test
	 */
	handleMoveToAboveCell: function() {
		this._handleMoveToCell("above");
	},

	/**
	 * (in table) Moves caret to the below cell
	 * @TODO: Add selenium test
	 */
	handleMoveToBelowCell: function() {
		this._handleMoveToCell("below");
	},

	_handleMoveToCell: function(dir) {
		var block = this.rdom.getCurrentBlockElement();
		var cell = this.rdom.getParentElementOf(block, ["TD", "TH"]);
		var table = this.rdom.getParentElementOf(cell, ["TABLE"]);
		var rtable = new xq.RichTable(this.rdom, table);
		var target = null;
		
		if(["next", "prev"].indexOf(dir) !== -1) {
			var toNext = dir === "next";
			target = toNext ? rtable.getNextCellOf(cell) : rtable.getPreviousCellOf(cell);
		} else {
			var toBelow = dir === "below";
			target = toBelow ? rtable.getBelowCellOf(cell) : rtable.getAboveCellOf(cell);
		}

		if(!target) {
			var finder = function(node) {return ['TD', 'TH'].indexOf(node.nodeName) === -1 && this.tree.isBlock(node) && !this.tree.hasBlocks(node);}.bind(this.rdom);
			var exitCondition = function(node) {return this.tree.isBlock(node) && !this.tree.isDescendantOf(this.getRoot(), node)}.bind(this.rdom);
			
			target = (toNext || toBelow) ? 
				this.rdom.tree.findForward(cell, finder, exitCondition) :
				this.rdom.tree.findBackward(table, finder, exitCondition);
		}
		
		if(target) this.rdom.placeCaretAtStartOf(target);
	},
	
	/**
	 * Applies STRONG tag
	 * @TODO: Add selenium test
	 */
	handleStrongEmphasis: function() {
		this.rdom.applyStrongEmphasis();

		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Applies EM tag
	 * @TODO: Add selenium test
	 */
	handleEmphasis: function() {
		this.rdom.applyEmphasis();
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Applies EM.underline tag
	 * @TODO: Add selenium test
	 */
	handleUnderline: function() {
		this.rdom.applyUnderline();
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Applies SPAN.strike tag
	 * @TODO: Add selenium test
	 */
	handleStrike: function() {
		this.rdom.applyStrike();

		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);

		return true;
	},
	
	/**
	 * Removes all style
	 * @TODO: Add selenium test
	 */
	handleRemoveFormat: function() {
		this.rdom.applyRemoveFormat();

		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);

		return true;
	},
	
	/**
	 * Remove link
	 * @TODO: Add selenium test
	 */
	handleRemoveLink: function() {
		this.rdom.applyRemoveLink();
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Inserts table
	 * @TODO: Add selenium test
	 *
	 * @param {object} [attrs] Attributes of tag. If not provided, it does not modify current attribute, and if empty string is provided, attribute will be removed.
	 */
	handleTable: function(attrs) {
		if (attrs){
			var cur = this.rdom.getCurrentBlockElement();
			if (!cur)
				cur=this.getBody();
			if(this.rdom.getParentElementOf(cur, ["TABLE"])) return true;
			
			var rtable = xq.RichTable.create(this.rdom, attrs);
			rtable.setTableProperty(attrs);
			
			if(this.rdom.tree.isBlockContainer(cur)) {
				var wrappers = this.rdom.wrapAllInlineOrTextNodesAs("P", cur, true);
				cur = wrappers.last();
			}
			var tableDom = this.rdom.insertNodeAt(rtable.getDom(), cur, "after");
			this.rdom.placeCaretAtStartOf(rtable.getCellAt(0, 0));
			
			if(this.rdom.isEmptyBlock(cur)) this.rdom.deleteNode(cur, true);
			
			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
			
		} else {
			xq.RichTableController.openDialog('new');
		}
		
		return true;
	},
	handleColorPicker: function(color){
		var anchor = this.lastAnchor;
		if (anchor.className.indexOf('foreground') != -1){
			xed.handleForegroundColor(color);
		} else if(anchor.className.indexOf('tableDialog') != -1) {
			var anchors = anchor.parentNode.parentNode.getElementsByTagName('A');
			for (var i = 0; i < anchors.length; i++){
				if(anchors[i].id.indexOf('ColorBoard') != -1) anchors[i].style.backgroundColor = color;
			}
			
			var inputs = anchor.parentNode.parentNode.getElementsByTagName('INPUT');
			for (var j = 0; j < inputs.length; j++){
				if(inputs[j].id.indexOf('ColorCode') != -1) inputs[j].value = color;
			}
		}
	},
	handleInsertNewRowAt: function(where) {
		var cur = this.rdom.getCurrentBlockElement();
		var tr = this.rdom.getParentElementOf(cur, ["TR"]);
		if(!tr) return true;
		
		var table = this.rdom.getParentElementOf(tr, ["TABLE"]);
		var rtable = new xq.RichTable(this.rdom, table);
		var row = rtable.insertNewRowAt(tr, where);
		
		this.rdom.placeCaretAtStartOf(row.cells[0]);
		return true;
	},
	
	/**
	 * @TODO: Add selenium test
	 */
	handleInsertNewColumnAt: function(where) {
		var cur = this.rdom.getCurrentBlockElement();
		var td = this.rdom.getParentElementOf(cur, ["TD"], true);
		if(!td) return true;
		
		var table = this.rdom.getParentElementOf(td, ["TABLE"]);
		var rtable = new xq.RichTable(this.rdom, table);
		rtable.insertNewCellAt(td, where);
		
		this.rdom.placeCaretAtStartOf(cur);
		return true;
	},
	
	/**
	 * @TODO: Add selenium test
	 */
	handleDeleteTable: function() {
		var cur = this.rdom.getCurrentBlockElement();
		var table = this.rdom.getParentElementOf(cur, ["TABLE"]);
		if(!table) return true;

		var rtable = new xq.RichTable(this.rdom, table);
		var blockToMove = rtable.deleteTable(table);
		
		this.rdom.placeCaretAtStartOf(blockToMove);
		return true;
	},
	
	/**
	 * @TODO: Add selenium test
	 */
	handleDeleteRow: function() {
		var cur = this.rdom.getCurrentBlockElement();
		var tr = this.rdom.getParentElementOf(cur, ["TR"]);
		if(!tr) return true;

		var table = this.rdom.getParentElementOf(tr, ["TABLE"]);
		var rtable = new xq.RichTable(this.rdom, table);
		var blockToMove = rtable.deleteRow(tr);
		
		this.rdom.placeCaretAtStartOf(blockToMove);
		return true;
	},
	
	/**
	 * @TODO: Add selenium test
	 */
	handleDeleteColumn: function() {
		var cur = this.rdom.getCurrentBlockElement();
		var td = this.rdom.getParentElementOf(cur, ["TD"], true);
		if(!td) return true;

		var table = this.rdom.getParentElementOf(td, ["TABLE"]);
		var rtable = new xq.RichTable(this.rdom, table);
		rtable.deleteCell(td);

		//this.rdom.placeCaretAtStartOf(table);
		return true;
	},
	
	_tablePropFormSize: function(form, prop, type) {
		form[type+'Unit'].value=prop[type].unit || '100%';
		form[type].value=prop[type].size || '';
		form[type].style.display=(form[type+'Unit'].value == 'auto')?'none':'';
	},

	handleTableProperty: function(prop) {
		var cur = this.rdom.getCurrentBlockElement();
		var el = this.rdom.getParentElementOf(cur, ["TABLE"], true);
		if(!el) return true;

		var rtable = new xq.RichTable(this.rdom, el);

		if (prop) {
			rtable.setTableProperty(prop);
		}else{
			var prop = rtable.getTableProperty();
		}
		
		return prop;
	},
	handleRowProperty: function(prop) {
		var cur = this.rdom.getCurrentBlockElement();
		var el = this.rdom.getParentElementOf(cur, ["TR"], true);
		if(!el) return true;

		var rtable = new xq.RichTable(this.rdom, el.offsetParent);

		if (prop) {
			rtable.setRowProperty(el, prop);
		}else{
			var prop = rtable.getRowProperty(el);
		}
		
		return prop;
	},
	handleColumnProperty: function(prop) {
		var cur = this.rdom.getCurrentBlockElement();
		var el = this.rdom.getParentElementOf(cur, ["TD"], true);
		if(!el) return true;

		var rtable = new xq.RichTable(this.rdom, el.offsetParent);

		if (prop) {
			rtable.setColumnProperty(el, prop);
		}else{
			var prop = rtable.getColumnProperty(el);
		}
		
		return prop;
	},



	/**
	 * Performs block indentation
	 * @TODO: Add selenium test
	 */
	handleIndent: function() {
		if(this.rdom.hasSelection(true)) {
			var blocks = this.rdom.getBlockElementsAtSelectionEdge(true, true);
			if(blocks.first() !== blocks.last()) {
				var affected = this.rdom.indentElements(blocks.first(), blocks.last());
				this.rdom.selectBlocksBetween(affected.first(), affected.last());
				
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
				
				return true;
			}
		}
		
		var block = this.rdom.getCurrentBlockElement();
		var affected = this.rdom.indentElement(block);
		if(affected && !this.rdom.tree.isAtomic(this.rdom.getCurrentElement())) {
			this.rdom.placeCaretAtStartOf(affected);
			
			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
		}
		
		return true;
	},
	
	/**
	 * Performs block outdentation
	 * @TODO: Add selenium test
	 */
	handleOutdent: function() {
		if(this.rdom.hasSelection(true)) {
			var blocks = this.rdom.getBlockElementsAtSelectionEdge(true, true);
			if(blocks.first() !== blocks.last()) {
				var affected = this.rdom.outdentElements(blocks.first(), blocks.last());
				this.rdom.selectBlocksBetween(affected.first(), affected.last());
				
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
				
				return true;
			}
		}
		
		var block = this.rdom.getCurrentBlockElement();
		var affected = this.rdom.outdentElement(block);
		
		if(affected && !this.rdom.tree.isAtomic(this.rdom.getCurrentElement())) {
			this.rdom.placeCaretAtStartOf(affected);

			var historyAdded = this.editHistory.onCommand();
			this._fireOnCurrentContentChanged(this);
		}
		
		return true;
	},
	
	/**
	 * Applies list.
	 * @TODO: Add selenium test
	 *
	 * @param {String} type "UL" or "OL"
	 * @param {String} CSS class name
	 */
	handleList: function(type, className) {
		if(this.rdom.hasSelection(true)) {
			var blocks = this.rdom.getBlockElementsAtSelectionEdge(true, true);
			if(blocks.first() !== blocks.last()) {
				blocks = this.rdom.applyLists(blocks.first(), blocks.last(), type, className);
			} else {
				blocks[0] = blocks[1] = this.rdom.applyList(blocks.first(), type, className);
			}
			this.rdom.selectBlocksBetween(blocks.first(), blocks.last());
		} else {
			var block = this.rdom.applyList(this.rdom.getCurrentBlockElement(), type, className);
			this.rdom.placeCaretAtStartOf(block);
		}
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Applies justification
	 * @TODO: Add selenium test
	 *
	 * @param {String} dir "left", "center", "right" or "both"
	 */
	handleJustify: function(dir) {
		if(this.rdom.hasSelection(true)) {
			var blocks = this.rdom.getSelectedBlockElements();
    		
    		var dir = (dir === "left" || dir === "both") && (blocks[0].style.textAlign === "left" || blocks[0].style.textAlign === "") ? "both" : dir;
			this.rdom.justifyBlocks(blocks, dir);
			this.rdom.selectBlocksBetween(blocks.first(), blocks.last());
		} else {
    		var block = this.rdom.getCurrentBlockElement();
    		var dir = (dir === "left" || dir === "both") && (block.style.textAlign === "left" || block.style.textAlign === "") ? "both" : dir;
			this.rdom.justifyBlock(block, dir);
		}
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Removes current block element
	 * @TODO: Add selenium test
	 */
	handleRemoveBlock: function() {
		var block = this.rdom.getCurrentBlockElement();
		var blockToMove = this.rdom.removeBlock(block);
		this.rdom.placeCaretAtStartOf(blockToMove);
		if(!this.isElementVisible(blockToMove)) blockToMove.scrollIntoView(false);
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Applies background color
	 * @TODO: Add selenium test
	 *
	 * @param {String} color CSS color string
	 */
	handleBackgroundColor: function(color) {
		if(color) {
			this.rdom.applyBackgroundColor(color);

			if(this.rdom.hasSelection()) {
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
			}
		} else {
			var dialog = new xq.ui.FormDialog(
				this,
				xq.ui_templates.basicColorPickerDialog,
				function(dialog) {},
				function(data) {
					this.focus();
					
					if(xq.Browser.isTrident) {
						var rng = this.rdom.rng();
						rng.moveToBookmark(bm);
						rng.select();
					}
					
					if(!data) return;
					
					this.handleBackgroundColor(data.color);
				}.bind(this)
			);
			
			if(xq.Browser.isTrident) var bm = this.rdom.rng().getBookmark();
			
			dialog.show({position: 'centerOfEditor'});
		}
		return true;
	},
	
	/**
	 * Applies foreground color
	 * @TODO: Add selenium test
	 *
	 * @param {String} color CSS color string
	 */
	handleForegroundColor: function(color) {
		if(color) {
			this.rdom.applyForegroundColor(color);
			
			if(this.rdom.hasSelection()) {
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
			}
		} else {
			var dialog = new xq.ui.FormDialog(
				this,
				xq.ui_templates.basicColorPickerDialog,
				function(dialog) {},
				function(data) {
					this.focus();
					
					if(xq.Browser.isTrident) {
						var rng = this.rdom.rng();
						rng.moveToBookmark(bm);
						rng.select();
					}
					
					if(!data) return;
					
					this.handleForegroundColor(data.color);
				}.bind(this)
			);
			
			if(xq.Browser.isTrident) var bm = this.rdom.rng().getBookmark();
			
			dialog.show({position: 'centerOfEditor'});
		}
		return true;
	},

	/**
	 * Applies font face
	 * @TODO: Add selenium test
	 *
	 * @param {String} face font face
	 */
	handleFontFace: function(face) {
		if(face) {
			this.rdom.applyFontFace(face);

			if(this.rdom.hasSelection()) {
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
			}
		} else {
			//TODO: popup font dialog
		}
		return true;
	},
	
	/**
	 * Applies font size
	 *
	 * @param {Number} font size (1 to 6)
	 */
	handleFontSize: function(size) {
		if(size) {
			this.rdom.applyFontSize(size);

			if(this.rdom.hasSelection()) {
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
			}
		} else {
			//TODO: popup font dialog
		}
		return true;
	},

	/**
	 * Applies superscription
	 * @TODO: Add selenium test
	 */	
	handleSuperscription: function() {
		this.rdom.applySuperscription();

		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);

		return true;
	},
	
	/**
	 * Applies subscription
	 * @TODO: Add selenium test
	 */	
	handleSubscription: function() {
		this.rdom.applySubscription();

		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);

		return true;
	},

	/**
	 * Change or wrap current block(or selected blocks)'s tag
	 * @TODO: Add selenium test
	 * 
	 * @param {String} [tagName] Name of tag. If not provided, it does not modify current tag name
	 * @param {String} [className] Class name of tag. If not provided, it does not modify current class name, and if empty string is provided, class attribute will be removed.  
	 * @param {object} [attrs] Attributes of tag. If not provided, it does not modify current attribute, and if empty string is provided, attribute will be removed.  
	 */	
	handleApplyBlock: function(tagName, className, attrs) {
		if(!tagName && !className) return true;
		
		// if current selection contains multi-blocks
		if(this.rdom.hasSelection()) {
			var blocks = this.rdom.getBlockElementsAtSelectionEdge(true, true);
			if(blocks.first() !== blocks.last()) {
				var applied = this.rdom.applyTagIntoElements(tagName, blocks.first(), blocks.last(), className);
				this.rdom.selectBlocksBetween(applied.first(), applied.last());
				
				var historyAdded = this.editHistory.onCommand();
				this._fireOnCurrentContentChanged(this);
				
				return true;
			}
		}
		
		// else
		var block = this.rdom.getCurrentBlockElement();
		this.rdom.pushMarker();
		var applied =
			this.rdom.applyTagIntoElement(tagName, block, className) ||
			block;
		
		if(attrs) this.rdom.setAttributes(applied, attrs);
		
		this.rdom.popMarker(true);
		
		if(this.rdom.isEmptyBlock(applied)) {
			this.rdom.correctEmptyElement(applied);
			this.rdom.placeCaretAtStartOf(applied);
		}
		
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Inserts separator (HR)
	 * @TODO: Add selenium test
	 */
	handleSeparator: function() {
		this.rdom.collapseSelection();
		
		var curBlock = this.rdom.getCurrentBlockElement();
		var atStart = this.rdom.isCaretAtBlockStart();
		if(this.rdom.tree.isBlockContainer(curBlock)) curBlock = this.rdom.wrapAllInlineOrTextNodesAs("P", curBlock, true)[0];
		
		this.rdom.insertNodeAt(this.rdom.createElement("HR"), curBlock, atStart ? "before" : "after");
		this.rdom.placeCaretAtStartOf(curBlock);

		// add undo history
		var historyAdded = this.editHistory.onCommand();
		this._fireOnCurrentContentChanged(this);
		
		return true;
	},
	
	/**
	 * Performs UNDO
	 * @TODO: Add selenium test
	 */
	handleUndo: function() {
		var performed = this.editHistory.undo();
		this._fireOnCurrentContentChanged(this);
		
		var curBlock = this.rdom.getCurrentBlockElement();
		if(!xq.Browser.isTrident && curBlock && !this.isElementVisible(curBlock)) {
			curBlock.scrollIntoView(false);
		}
		return true;
	},
	
	/**
	 * Performs REDO
	 * @TODO: Add selenium test
	 */
	handleRedo: function() {
		var performed = this.editHistory.redo();
		this._fireOnCurrentContentChanged(this);
		
		var curBlock = this.rdom.getCurrentBlockElement();
		if(!xq.Browser.isTrident && curBlock && !this.isElementVisible(curBlock)) {
			curBlock.scrollIntoView(false);
		}
		return true;
	},
	
	handleEmoticon: function(fileName){
		var img = this.getDoc().createElement('IMG');
		img.src = this.config.imagePathForEmoticon + fileName;
		img.alt = fileName;
		this.rdom.insertNode(img);
	},
	
	handleCharacter: function(chr){
		this.rdom.insertHtml( decodeURIComponent(chr));
	},
	
	_handleContextMenu: function(e) {
		if (xq.Browser.isWebkit) {
			if (e.metaKey || xq.isLeftClick(e)) return false;
		} else if (e.shiftKey || e.ctrlKey || e.altKey) {
			return false;
		}
		
		var point = xq.getEventPoint(e);
		var x = point.x;
		var y = point.y;

		var pos = xq.getCumulativeOffset(this.wysiwygEditorDiv);
		x += pos.left;
		y += pos.top;
		this._contextMenuTargetElement = e.target || e.srcElement;
		
		if (!xq.Browser.isTrident) {
			var doc = this.getDoc();
			var body = this.getBody();
			
			x -= doc.documentElement.scrollLeft;
			y -= doc.documentElement.scrollTop;
			
			x -= body.scrollLeft;
			y -= body.scrollTop;
		}
		
		for(var cmh in this.config.contextMenuHandlers) {
			var stop = this.config.contextMenuHandlers[cmh].handler(this, this._contextMenuTargetElement, x, y);
			if(stop) {
				xq.stopEvent(e);
				return true;
			}
		}
		
		return false;
	},
	
	showContextMenu: function(menuItems, x, y) {
		if (!menuItems || menuItems.length <= 0) return;
		
		if (!this.contextMenuContainer) {
			this.contextMenuContainer = this.doc.createElement('UL');
			this.contextMenuContainer.className = 'xqContextMenu';
			this.contextMenuContainer.style.display='none';
			
			xq.observe(this.doc, 'click', this._contextMenuClicked.bindAsEventListener(this));
			xq.observe(this.rdom.getDoc(), 'click', this.hideContextMenu.bindAsEventListener(this));
			
			this.body.appendChild(this.contextMenuContainer);
		} else {
			while (this.contextMenuContainer.childNodes.length > 0)
				this.contextMenuContainer.removeChild(this.contextMenuContainer.childNodes[0]);
		}
		
		for (var i=0; i < menuItems.length; i++) {
			menuItems[i]._node = this._addContextMenuItem(menuItems[i]);
		}

		this.contextMenuContainer.style.display='block';
		this.contextMenuContainer.style.left = Math.min(Math.max(this.doc.body.scrollWidth, this.doc.documentElement.clientWidth) - this.contextMenuContainer.offsetWidth, x) + 'px';
		this.contextMenuContainer.style.top = Math.min(Math.max(this.doc.body.scrollHeight, this.doc.documentElement.clientHeight) - this.contextMenuContainer.offsetHeight, y) + 'px';

		this.contextMenuItems = menuItems;
	},
	
	hideContextMenu: function() {
		if (this.contextMenuContainer)
			this.contextMenuContainer.style.display='none';
	},
	
	_addContextMenuItem: function(item) {
		if (!this.contextMenuContainer) throw "No conext menu container exists";
		
		var node = this.doc.createElement('LI');
		if (item.disabled) node.className += ' disabled'; 
		
		if (item.title === '----') {
			node.innerHTML = '&nbsp;';
			node.className = 'separator';
		} else {
			if(item.handler) {
				node.innerHTML = '<a href="#" onclick="return false;">'+(item.title.toString().escapeHTML())+'</a>';
			} else {
				node.innerHTML = (item.title.toString().escapeHTML());
			}
		}
		
		if(item.className) node.className = item.className;
		
		this.contextMenuContainer.appendChild(node);
		
		return node;
	},
	
	_contextMenuClicked: function(e) {
		this.hideContextMenu();
		
		if (!this.contextMenuContainer) return;
		
		var node = e.srcElement || e.target;
		while(node && node.nodeName !== "LI") {
			node = node.parentNode;
		}
		if (!node || !this.rdom.tree.isDescendantOf(this.contextMenuContainer, node)) return;

		for (var i=0; i < this.contextMenuItems.length; i++) {
			if (this.contextMenuItems[i]._node === node) {
				var handler = this.contextMenuItems[i].handler;
				if (!this.contextMenuItems[i].disabled && handler) {
					var xed = this;
					var element = this._contextMenuTargetElement;
					if(typeof handler === "function") {
						handler(xed, element);
					} else {
						eval(handler);
					}
				}
				break;
			}
		}
	},
	
	/**
	 * Inserts HTML template
	 * @TODO: Add selenium test
	 *
	 * @param {String} html Template string. It should have single root element
	 * @returns {Element} inserted element
	 */
	insertTemplate: function(html) {
		return this.rdom.insertHtml(this._processTemplate(html));
	},
	
	/**
	 * Places given HTML template nearby target.
	 * @TODO: Add selenium test
	 *
	 * @param {String} html Template string. It should have single root element
	 * @param {Node} target Target node.
	 * @param {String} where Possible values: "before", "start", "end", "after"
	 *
	 * @returns {Element} Inserted element.
	 */
	insertTemplateAt: function(html, target, where) {
		return this.rdom.insertHtmlAt(this._processTemplate(html), target, where);
	},
	
	_processTemplate: function(html) {
		// apply template processors
		var tps = this.getTemplateProcessors();
		for(var key in tps) {
			var value = tps[key];
			html = value.handler(html);
		}
		
		// remove all whitespace characters between block tags
		return this.removeUnnecessarySpaces(html);
	},
	
	
	
	/** @private */
	_handleEnterAtEmptyBlock: function() {
		var block = this.rdom.getCurrentBlockElement();
		if(this.rdom.tree.isTableCell(block) && this.rdom.isFirstBlockOfBody(block)) {
			block = this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(), this.rdom.getRoot(), "start");
		} else {
			block = 
				this.rdom.outdentElement(block) ||
				this.rdom.extractOutElementFromParent(block) ||
				this.rdom.replaceTag("P", block) ||
				this.rdom.insertNewBlockAround(block);
		}
		
		this.rdom.placeCaretAtStartOf(block);
		if(!xq.Browser.isTrident && !this.isElementVisible(block)) block.scrollIntoView(false);
	},
	
	/** @private */
	_handleEnterAtEdge: function(atStart, forceInsertParagraph) {
		var block = this.rdom.getCurrentBlockElement();
		var blockToPlaceCaret;
		
		if(atStart && this.rdom.isFirstBlockOfBody(block)) {
			blockToPlaceCaret = this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(), this.rdom.getRoot(), "start");
		} else {
			if(this.rdom.tree.isTableCell(block)) forceInsertParagraph = true;
			var newBlock = this.rdom.insertNewBlockAround(block, atStart, forceInsertParagraph ? "P" : null);
			blockToPlaceCaret = !atStart ? newBlock : newBlock.nextSibling;
		}
		
		this.rdom.placeCaretAtStartOf(blockToPlaceCaret);
		if(!xq.Browser.isTrident && !this.isElementVisible(blockToPlaceCaret)) blockToPlaceCaret.scrollIntoView(false);
	},
	
	isElementVisible: function(element){
		var doc = this.rdom.getDoc();
		var currentParentNode = this.rdom.getParentBlockElementOf(element);
		var topLimit = doc.documentElement.scrollTop || doc.body.scrollTop;
		var bottomLimit = topLimit + this.outerFrame.offsetHeight - currentParentNode.offsetHeight;
		if (topLimit < currentParentNode.offsetTop && bottomLimit > currentParentNode.offsetTop) return true;
	},
	
	/**
	 * Replace URL text nearby caret into a link
	 * @TODO: Add selenium test
	 */
	replaceUrlToLink: function() {
		// If there's link nearby caret, nothing happens
		if(this.rdom.getParentElementOf(this.rdom.getCurrentElement(), ["A"])) return;
		
		var marker = this.rdom.pushMarker();
		var criteria = function(text) {
			var m = /(http|https|ftp|mailto)\:\/\/[^\s]+$/.exec(text);
			return m ? m.index : -1;
		};
		
		var test = this.rdom.testSmartWrap(marker, criteria);
		if(test.textIndex !== -1) {
			var a = this.rdom.smartWrap(marker, "A", criteria);
			a.href = test.text;
		}
		this.rdom.getCurrentElement().normalize();
		this.rdom.popMarker(true);
	},

	_: function(msg) {
		if (xq._messages && xq._messages[this.config.lang] && typeof xq._messages[this.config.lang][msg] != 'undefined')
		{
			msg=xq._messages[this.config.lang][msg];
		}

		if (arguments.length > 1) {
			for (var i=1; i < arguments.length; i++)
				msg=msg.replace('$'+i, arguments[i]);
		}
		return msg;
	}
});