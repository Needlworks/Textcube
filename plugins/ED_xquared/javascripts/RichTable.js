/**
 * @requires XQuared.js
 * @requires rdom/Base.js
 */
xq.RichTable = xq.Class(/** @lends xq.RichTable.prototype */{
	/**
	 * TODO: Add description
	 *
	 * @constructs
	 */
	initialize: function(rdom, table) {
		xq.addToFinalizeQueue(this);

		this.rdom = rdom;
		this.table = table;
	},
	collectCells: function(cell){
		var cells = [];
		var x = this.getXIndexOf(cell);
		var y = 0;
		while(true) {
			var cur = this.getCellAt(x, y);
			if(!cur) break;
			cells.push(cur);
			y++;
		}
		
		return cells
	},
	insertNewRowAt: function(tr, where) {
		var row = this.rdom.createElement("TR");
		var cells = tr.cells;
		for(var i = 0; i < cells.length; i++) {
			var cell = this.rdom.createElement(cells[i].nodeName);
			this.rdom.correctEmptyElement(cell);
			row.appendChild(cell);
		}
		return this.rdom.insertNodeAt(row, tr, where);
	},
	insertNewCellAt: function(cell, where) {
		var cells = this.collectCells(cell);
		
		// insert new cells
		for(var i = 0; i < cells.length; i++) {
			var cell = this.rdom.createElement(cells[i].nodeName);
			this.rdom.correctEmptyElement(cell);
			this.rdom.insertNodeAt(cell, cells[i], where);
		}
	},
	
	deleteTable: function(table) {
		return this.rdom.deleteNode(table);
	},
	deleteRow: function(tr) {
		return this.rdom.removeBlock(tr);
	},
	deleteCell: function(cell) {
		if(!cell.previousSibling && !cell.nextSibling) {
			this.rdom.deleteNode(this.table);
			return;
		}
		
		var cells = this.collectCells(cell);
		
		for(var i = 0; i < cells.length; i++) {
			this.rdom.deleteNode(cells[i]);
		}
	},
	getPreviousCellOf: function(cell) {
		if(cell.previousSibling) return cell.previousSibling;
		var adjRow = this.getPreviousRowOf(cell.parentNode);
		if(adjRow) return adjRow.lastChild;
		return null;
	},
	getNextCellOf: function(cell) {
		if(cell.nextSibling) return cell.nextSibling;
		var adjRow = this.getNextRowOf(cell.parentNode);
		if(adjRow) return adjRow.firstChild;
		return null;
	},
	getPreviousRowOf: function(row) {
		if(row.previousSibling) return row.previousSibling;
		var rowContainer = row.parentNode;
		if(rowContainer.previousSibling && rowContainer.previousSibling.lastChild) return rowContainer.previousSibling.lastChild;
		return null;
	},
	getNextRowOf: function(row) {
		if(row.nextSibling) return row.nextSibling;
		var rowContainer = row.parentNode;
		if(rowContainer.nextSibling && rowContainer.nextSibling.firstChild) return rowContainer.nextSibling.firstChild;
		return null;
	},
	getAboveCellOf: function(cell) {
		var row = this.getPreviousRowOf(cell.parentNode);
		if(!row) return null;
		
		var x = this.getXIndexOf(cell);
		return row.cells[x];
	},
	getBelowCellOf: function(cell) {
		var row = this.getNextRowOf(cell.parentNode);
		if(!row) return null;
		
		var x = this.getXIndexOf(cell);
		return row.cells[x];
	},
	getXIndexOf: function(cell) {
		var row = cell.parentNode;
		for(var i = 0; i < row.cells.length; i++) {
			if(row.cells[i] === cell) return i;
		}
		
		return -1;
	},
	getYIndexOf: function(cell) {
		var y = -1;
		
		// find y
		var group = row.parentNode;
		for(var i = 0; i <group.rows.length; i++) {
			if(group.rows[i] === row) {
				y = i;
				break;
			}
		}
		if(this.hasHeadingAtTop() && group.nodeName === "TBODY") y = y + 1;
		
		return y;
	},


	getTableProperty: function() {
		var prop = {
			width: this.table.style.width || null,
			height: this.table.style.height || null,
			textAlign: this.table.style.textAlign || null,
			borderColor: this.table.style.borderLeftColor || null,
			borderWidth: this.table.style.borderLeftWidth.replace(/ .*/, '').replace(/[^0-9]/g, '') || null,
			backgroundColor: this.table.style.backgroundColor || null
		};
		return prop;
	},
	setTableProperty: function(prop) {
		this._setTableProperty(this.table, prop);
	},
	getRowProperty: function(row) {
		var prop = {
			height: row.style.height || null,
			verticalAlign: row.style.verticalAlign || null,
			textAlign: row.style.textAlign || null,
			backgroundColor: row.style.backgroundColor || null
		};
		return prop;
	},
	setRowProperty: function(tr, prop) {
		this._setTableProperty(tr, prop);
	},
	getColumnProperty: function(cell) {
		var prop = {
			width: cell.style.width || null,
			verticalAlign: cell.style.verticalAlign || null,
			textAlign: cell.style.textAlign || null,
			backgroundColor: cell.style.backgroundColor || null
		};
		return prop;
	},
	setColumnProperty: function(cell, prop) {
		for (var i=0; i < cell.offsetParent.rows.length; i++) {
			this._setTableProperty(cell.offsetParent.rows[i].cells[cell.cellIndex], prop);
		}
	},

	_setTableProperty: function(el, prop) {
		for (propName in prop){
			if (prop[propName] != null){
				var value = typeof prop[propName] == 'string' ? prop[propName] : prop[propName].size + prop[propName].unit;
				
				if (propName == 'className') {
					el.className = prop.className || '';					
				} else if (propName != 'headerPositions'){
					var defaultPropName = (propName == 'width')? el.nodeName.toLowerCase() + 'Width' : propName;
					el.style[propName] = (xed.config.enableTableInlineStyle || (value.length != 0 && value != xq.RichTable.defaultPropertyValues[defaultPropName]))? value : '';
				}
			}
		}
	},

	
	/**
	 * TODO: Not used. Delete or not?
	 */
	getLocationOf: function(cell) {
		var x = this.getXIndexOf(cell);
		var y = this.getYIndexOf(cell);
		return {x:x, y:y};
	},
	getCellAt: function(col, row) {
		var row = this.getRowAt(row);
		return (row && row.cells.length > col) ? row.cells[col] : null;
	},
	getRowAt: function(index) {
		if(this.hasHeadingAtTop()) {
			return index === 0 ? this.table.tHead.rows[0] : this.table.tBodies[0].rows[index - 1];
		} else {
			var rows = this.table.tBodies[0].rows;
			return (rows.length > index) ? rows[index] : null;
		}
	},
	getDom: function() {
		return this.table;
	},
	hasHeadingAtTop: function() {
		return !!(this.table.tHead && this.table.tHead.rows[0]);
	},
	hasHeadingAtLeft: function() {
		return this.table.tBodies[0].rows[0].cells[0].nodeName === "TH";
	},
	correctEmptyCells: function() {
		var cells = xq.$A(this.table.getElementsByTagName("TH"));
		var tds = xq.$A(this.table.getElementsByTagName("TD"));
		for(var i = 0; i < tds.length; i++) {
			cells.push(tds[i]);
		}
		
		for(var i = 0; i < cells.length; i++) {
			if(this.rdom.isEmptyBlock(cells[i])) this.rdom.correctEmptyElement(cells[i])
		}
	}
});
xq.RichTable.defaultPropertyValues = {
	borderColor: '#000000',
	borderWidth: 1,
	backgroundColor: '#FFFFFF',
	tableWidth: '100%',
	columnWidth: 'auto',
	height: 'auto',
	textAlign: '',
	verticalAlign: 'top'
};
xq.RichTable.create = function(rdom, attrs) {
	if(["t", "tl", "lt"].indexOf(attrs.headerPositions) !== -1) var headingAtTop = true
	if(["l", "tl", "lt"].indexOf(attrs.headerPositions) !== -1) var headingAtLeft = true

	var sb = []
	sb.push('<table class="datatable2" style="width:100%;">')
	
	// thead
	if(headingAtTop) {
		sb.push('<thead><tr>')
		for(var i = 0; i < attrs.cols; i++) sb.push('<th></th>')
		sb.push('</tr></thead>')
		attrs.rows -= 1
	}
		
	// tbody
	sb.push('<tbody>')
	for(var i = 0; i < attrs.rows; i++) {
		sb.push('<tr>')
		
		for(var j = 0; j < attrs.cols; j++) {
			if(headingAtLeft && j === 0) {
				sb.push('<th></th>')
			} else {
				sb.push('<td></td>')
			}
		}
		
		sb.push('</tr>')
	}
	sb.push('</tbody>')
	
	sb.push('</table>')
	
	// create DOM element
	var container = rdom.createElement("div");
	container.innerHTML = sb.join("");
	
	// correct empty cells and return
	var rtable = new xq.RichTable(rdom, container.firstChild);
	rtable.correctEmptyCells();
	return rtable;
};
xq.RichTableController = {
	dialogType: null,
	initDialog: function(type, prop){
		// initial value
		xq.$("tableDialog").tableTypeField.value = '';
		this.changeType(xq.$("tableTypeDefaultValue"),'');
		
		xq.$("tableRowsField").value = "3";
		xq.$("tableColsField").value = "3";
		this.previewTable();
		
		var defaultValues = xq.RichTable.defaultPropertyValues;
		
		xq.getElementsByClassName(xq.$("tableDialog"), 'tableWidths')[0].selectedIndex = (type == 'new' || type == 'table')? 0 : 1
		xq.$("tableWidthValue").value = (type == 'new' || type == 'table')? defaultValues.tableWidth : defaultValues.columnWidth;
		xq.$("tableWidthValue").style.display = "none";
		xq.$("tableWidthValueUnit").value = "";
		
		xq.getElementsByClassName(xq.$("tableDialog"), 'tableHeights')[0].selectedIndex = 0;
		xq.$("tableHeightValue").value = defaultValues.height;
		xq.$("tableHeightValue").style.display = "none";
		xq.$("tableHeightValueUnit").value = "";
		
		xq.$("tableDialog").tableHorizontalAlign.selectedIndex = 0;
		xq.$("tableDialog").tableVerticalAlign.selectedIndex = 1;
		
		xq.$("tableDialog").tableBorderColor.parentNode.getElementsByTagName('A')[0].style.backgroundColor = defaultValues.borderColor;
		xq.$("tableDialog").tableBorderColor.value = defaultValues.borderColor;
		
		xq.$("tableDialog").tableBorderSize.value = defaultValues.borderWidth;
		
		xq.$("tableDialog").tableBackgroundColor.parentNode.getElementsByTagName('A')[0].style.backgroundColor = defaultValues.backgroundColor;
		xq.$("tableDialog").tableBackgroundColor.value = defaultValues.backgroundColor;

		if (prop) this.setDialog(prop);
	},
	setDialog: function(prop){
		if(typeof prop.width != 'undefined' || prop.width != null){
			xq.$("tableWidthValue").value = prop.width;
			if(prop.width.match(/(\%|px)/)) {
				xq.getElementsByClassName(xq.$("tableDialog"), 'tableWidths')[0].selectedIndex = (prop.width.indexOf('%') != -1)? 3:2
				xq.$("tableWidthValue").style.display = "inline";
				xq.$("tableWidthValueUnit").value = (prop.width.indexOf('%') != -1)? '%':'px';
			}
		}
		if(typeof prop.height != 'undefined' || prop.height != null){
			if(prop.height.indexOf('px') != -1) {
				xq.getElementsByClassName(xq.$("tableDialog"), 'tableHeights')[0].selectedIndex = 1
				xq.$("tableHeightValue").style.display = "inline";
				xq.$("tableHeightValue").value = prop.height;
				xq.$("tableHeightValueUnit").value = 'px';
			}
		}
		if(typeof prop.verticalAlign != 'undefined' || prop.verticalAlign != null){
			var optTextAlign = {
				'top':0,
				'middle':1,
				'bottom':2
			}
			xq.$("tableDialog").tableVerticalAlign.selectedIndex = optTextAlign[prop.verticalAlign];
		}
		if(typeof prop.textAlign != 'undefined' || prop.textAlign != null){
			var optVerticalAlign = {
				'left':0,
				'center':1,
				'right':2
			}
			xq.$("tableDialog").tableHorizontalAlign.selectedIndex = optVerticalAlign[prop.textAlign];
		}
		if(typeof prop.borderColor != 'undefined' || prop.borderColor != null){
			xq.$("tableDialog").tableBorderColor.parentNode.getElementsByTagName('A')[0].style.backgroundColor = prop.borderColor;
			xq.$("tableDialog").tableBorderColor.value = prop.borderColor;
		}
		if(typeof prop.borderWidth != 'undefined' || prop.borderWidth != null){
			xq.$("tableDialog").tableBorderSize.value = prop.borderWidth;
		}
		if(typeof prop.backgroundColor != 'undefined' || prop.backgroundColor != null){
			xq.$("tableDialog").tableBackgroundColor.parentNode.getElementsByTagName('A')[0].style.backgroundColor = prop.backgroundColor;
			xq.$("tableDialog").tableBackgroundColor.value = prop.backgroundColor;
		}
	},
	openDialog: function(type, element){
		var tableDialog = xq.$('tableDialog');
		if (tableDialog && tableDialog.style.display != 'none') this.lastTableDialog.close();
		var text = xed.rdom.getSelectionAsText() || '';
		var dialog = new xq.ui.FormDialog(
			xed,
			xq.ui_templates.basicTableDialog,
			function(dialog) {
				var isNewTable = type == 'new';
				
				//xq.$("tableDialogTitle").innerHTML = (isNewTable)? 'Insert Table' : 'Change ' + type;
				//xq.$("tableDialogSubmit").innerHTML = (isNewTable)? 'Insert' : 'Edit';
				var prop;
				if (type != 'new'){
					switch (type){
						case 'table':
							prop = xed.handleTableProperty();
						break;
						case 'row':
							prop = xed.handleRowProperty();
						break;
						case 'column':
							prop = xed.handleColumnProperty();
						break;
					}
				}
				
				xq.$("tableDialog").className += " " + type;
				xq.RichTableController.initDialog(type, prop);
			},
			function(data) {
				xed.focus();
				
				if(xq.Browser.isTrident) {
					var rng = xed.rdom.rng();
					rng.moveToBookmark(bm);
					rng.select();
				}
				if(!data) return;
				xq.RichTableController.submit();
			}
		);
		
		if(xq.Browser.isTrident) var bm = xed.rdom.rng().getBookmark();
		dialog.show({position: 'centerOfEditor'});
		this.dialogType = type;
		this.lastTableDialog = dialog;
		return true;

	},
	submit: function(){
		var type = this.dialogType;
		
		var prop = {};
		
		if (type == 'new') {
			if (!xq.$("tableDialog").tableCols.value.replace(/[^0-9]/g,'') || parseInt(xq.$("tableDialog").tableCols.value, 10) > 30) {
				alert(xed._('Please enter column value between 1 to 30.'));
				xq.$("tableDialog").tableCols.focus();
				return false;
			}
			if (!xq.$("tableDialog").tableRows.value.replace(/[^0-9]/g,'') || parseInt(xq.$("tableDialog").tableRows.value, 10) > 120) {
				alert(xed._('Please enter row value between 1 to 120.'));
				xq.$("tableDialog").tableRows.focus();
				return false;
			}
			
			prop['cols'] = xq.$("tableDialog").tableCols.value;
			prop['rows'] = xq.$("tableDialog").tableRows.value;
			prop['headerPositions'] = xq.$("tableDialog").tableType.value;
		}
		
		if (type != 'row') {
			if (xq.$("tableDialog").tableWidth.value > 0) {
				prop['width'] = {};
				prop['width'].size = parseInt(xq.$("tableDialog").tableWidth.value, 10);
				prop['width'].unit = xq.$("tableDialog").tableWidthUnit.value;
			} else {
				prop['width'] = xq.$("tableDialog").tableWidth.value;
			}
		}
		
		if (type != 'column') {
			if (xq.$("tableDialog").tableHeight.value > 0) {
				prop['height'] = {};
				prop['height'].size = parseInt(xq.$("tableDialog").tableHeight.value, 10);
				prop['height'].unit = xq.$("tableDialog").tableHeightUnit.value;
			} else {
				prop['height'] = xq.$("tableDialog").tableHeight.value;
			}
		}
		
		if (type == 'row' || type == 'column') {
			prop['verticalAlign'] = xq.$("tableDialog").tableVerticalAlign.value;
			prop['textAlign'] = xq.$("tableDialog").tableHorizontalAlign.value;
		}
		
		if (type == 'new' || type == 'table') {
			prop['borderColor'] = xq.$("tableDialog").tableBorderColor.value;
		}
		
		var currentTable = xed.rdom.getParentElementOf(xed.rdom.getCurrentBlockElement(), ["TABLE"]);
		
		prop['className'] = (currentTable)? currentTable.className : '';
		
		if (prop['className'].indexOf('dataTable2') == -1) {
			prop['className'] += (currentTable && currentTable.className)? ' datatable2':'datatable2';
		}
		
		if (type == 'new' || type == 'table') {
			prop['borderWidth'] = xq.$("tableDialog").tableBorderSize.value;
			
			if(xq.$("tableDialog").tableBorderSize.value < 1 && prop['className'].indexOf('zeroborder') == -1){
				prop['className'] += ' zeroborder';
			} else if (xq.$("tableDialog").tableBorderSize.value > 0){
				prop['className'] = prop['className'].replace('zeroborder','')
			}
		}
		
		prop['backgroundColor'] = xq.$("tableDialog").tableBackgroundColor.value;
		
		xed.focus();
		
		switch (type){
			case 'new':
			xed.handleTable(prop);
			break;
			case 'table':
			xed.handleTableProperty(prop);
			break;
			case 'row':
			xed.handleRowProperty(prop);
			break;
			case 'column':
			xed.handleColumnProperty(prop);
			break;
		}
		
		return false;
	},
	insertParagraph: function(where){
		var cur = xed.rdom.getCurrentBlockElement();
		if (!cur) return;
		var table = xed.rdom.getParentElementOf(cur, ["TABLE"]);
		if(!table) return true;
		
		var insert = xed.rdom.insertNodeAt(xed.rdom.makeEmptyParagraph(), table, where);
		xed.rdom.placeCaretAtStartOf(insert);
		xed.focus()
	},
	changeType: function(element, type){
		var anchors = element.parentNode.parentNode.getElementsByTagName('A');
		for(var i = 0; i < anchors.length; i++){
			anchors[i].className = ""
		}
		element.className = "selected"
		xq.$('tableTypeField').value = type; 
		return false;
	},
	changeSize: function(element){
		var targetElement = element.parentNode.parentNode.getElementsByTagName('INPUT')[0];
		if (element.className.indexOf('plus') != -1){
			targetElement.value++;
		} else if(targetElement.value > 1 || (targetElement.name == "tableBorderSize" && targetElement.value > 0)) {
			targetElement.value--;
		}
		this.previewTable();
	},
	previewTable: function(){
		var table = xq.$("previewTable").getElementsByTagName('TABLE')[0]
		if (table.tBodies.length > 0) table = table.tBodies[0];

		var row = parseInt(xq.$("tableRowsField").value, 10);
		var col = parseInt(xq.$("tableColsField").value, 10);
		
		if (row < 1) xq.$("tableRowsField").value = row = 1;
		if (row > 120) xq.$("tableRowsField").value = row = 120;
		if (col < 1) xq.$("tableColsField").value = col = 1;
		if (col > 30) xq.$("tableColsField").value = col = 30;
		
		row = Math.min(parseInt(xq.$("tableRowsField").value, 10), 20);
		
		var rowsValue = row - table.rows.length;
		var colsValue = col - table.rows[0].cells.length;
		
		for (var i = 0; i < Math.abs(rowsValue); i++){
			if (rowsValue > 0){
				table.appendChild(table.rows[0].cloneNode(true))
			} else {
				table.deleteRow(0)
			}
		}
		
		for (var j = 0; j < Math.abs(colsValue); j++){
			for (var k = 0; k < table.rows.length; k++){
				var tr = table.rows[k];
				if (colsValue > 0) {
					tr.insertCell(0);
				} else {
					tr.deleteCell(0);
				}
			}
		}
	},
	changeStyle: function(element){
		var target = (element.className.indexOf('Width') != -1)? 'Width':'Height';
		switch (element.value){
			case 'fullsize':
				xq.$("table" + target + "Value").value = "100";
				xq.$("table" + target + "ValueUnit").value = "%";
				xq.$("table" + target + "Value").style.display = "none";
				break;
			case 'content':
				xq.$("table" + target + "Value").value = "";
				xq.$("table" + target + "ValueUnit").value = "";
				xq.$("table" + target + "Value").style.display = "none";
				break;
			case 'pixel':
				xq.$("table" + target + "Value").value = "";
				xq.$("table" + target + "ValueUnit").value = "px";
				xq.$("table" + target + "Value").style.display = "inline";
				break;
			case 'percentage':
				xq.$("table" + target + "Value").value = "100";
				xq.$("table" + target + "ValueUnit").value = "%";
				xq.$("table" + target + "Value").style.display = "inline";
				break;
		}
	},
	showColorPicker: function(elem){
		xed.lastAnchor = elem;
		var dialog = xq.$('foregroundColorDialog');
		dialog.style.display = 'block';
		
		dialog.style.position = 'absolute'
		dialog.style.top = elem.offsetTop + xq.$('tableDialog').offsetTop + elem.offsetHeight + 2 + 'px';
		dialog.style.left = elem.offsetLeft + xq.$('tableDialog').offsetLeft + 'px';
	}
}