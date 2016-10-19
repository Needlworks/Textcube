/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function Converter(obj_document , treat_control_list ){
	this.doc= obj_document;
	this.targets = treat_control_list;
	this.xmlHeader = '<' + '?xml version="1.0" encoding="utf-8"?><config>';
	this.xmlfooter = '</config>';
}
Converter.prototype.getXMLData = function(){
	var innerXml ='';
	for( var i =0 ; i < this.targets.length ; i++){
		try{			
			innerXml += this.makeDataSet( this.targets[i] );
		} catch(e){}
	}	
	return this.xmlHeader + innerXml + this.xmlfooter;
};
Converter.prototype.makeDataSet = function( fieldName ){
	var ele ='';
	var con = this.getControl( fieldName);
	var type = this.getType(con); 
	ele += '<field   name = "' + fieldName +'" type="' + type+ '" >';
	switch( type){
		case 'select':
		case 'SELECT':
		case 'text':
			ele += '<![CDATA['+con.value+']]>';break;		
		case 'textarea':
		case 'TEXTAREA':
			ele += '<![CDATA['+con.value +']]>';break;
		case 'radio':
			ele += this.getRadioData(  con );break;
		case 'checkbox':	
			ele += this.getCheckBoxData( con); break;			
		default:
			throw new Error(0,'알수 없는 타입');
	}
	ele += '</field >\n';
	return ele;
};
Converter.prototype.getCheckBoxData = function ( con){
	return true == con.checked ? con.value: '';
};
Converter.prototype.getRadioData = function( cons ){
	var val="";
	for( var i= 0 ; i < cons.length ; i++){
		if( "radio" == cons[i].type && true == cons[i].checked ){
			val = cons[i].value;
			break;
		}
	}
	return val;
};
Converter.prototype.getControl = function( name){

	if( undefined == this.doc.getElementById(name)) return this.doc.getElementsByName(name);
	if( 'radio' == this.doc.getElementById(name).type ) return this.doc.getElementsByName(name);	
	return this.doc.getElementById(name);
};
Converter.prototype.getType = function( objList ){
	if( undefined == objList ) throw new Error( 0 , 'objList 가 정의 안됨');
	if( undefined != objList.length && 'INPUT' == objList[0].tagName)	return ( objList[0].getAttribute( 'type' ) ) ;
	var tagName = objList.tagName ;
	var type = objList.getAttribute( 'type' );
	if( 'SELECT' == tagName || 'TEXTAREA' == tagName  ) return tagName;
	if( undefined != type  ) return type;
	throw new Error( 0 , 'type에 접근 안됨');
}
