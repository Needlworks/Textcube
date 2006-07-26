function Converter(data_Set , obj_document ){
	this.data_Set=data_Set;
	this.doc= obj_document;
	this.xmlHeader = '<?xml version="1.0" encoding="utf-8"?><config>';
	this.xmlfooter = '</config>';
}

Converter.prototype.getXMLData = function(){
	if( undefined == this.data_Set ) return 'aa';
	var fieldsets = this.data_Set.getElementsByTagName( "FIELDSET");
	var innerXml ='';
	for( var i =0 ; i < fieldsets.length ; i++){
		try{			
			innerXml += '<fieldset name="' + fieldsets[i].getAttribute('name') + '" >\n'; 
			innerXml += this.makeDataSet( fieldsets[i] );
			innerXml += '</fieldset >\n'; 
		} catch(e){alert( e.description );}
	}	
	return this.xmlHeader + innerXml + this.xmlfooter;
};


Converter.prototype.getControls = function( name){
	return this.doc.getElementsByName(name);
};

Converter.prototype.makeDataSet = function( fieldset ){
	var ele ='';/* FMS_name == Fucking Micro$oft 's name */
	var fields = fieldset.getElementsByTagName("FIELD");
	for( var i =0; i < fields.length ; i++){
		if( undefined != fields[i].getAttribute('name') && undefined != fields[i].getAttribute('type')){
			var type = fields[i].getAttribute('type');
			var FMS_name = 'FMS_' + fieldset.getAttribute('name') + '_' +  fields[i].getAttribute('name') + '_control';
			ele += '<field   name = "' + fields[i].getAttribute('name') +'" type="' + type+ '" >';
			switch( type){
				case 'text':
				case 'textarea':
					ele += '<![CDATA['+this.getFirstConValue(  FMS_name )+']]>';break;
				case 'select':
					ele += this.getFirstConValue(  FMS_name );break;
				case 'radio':
					ele += this.getRadioData(  FMS_name);break;
				case 'checkbox':
					ele += this.getCheckboxData(  FMS_name);break;
				default:
					throw new error(0,"");
			}
			ele += '</field >\n';
		}
	}
	return ele;
};
Converter.prototype.getFirstConValue = function ( name){
	var cons = this.getControls(name);
	return cons[0].value;
};
Converter.prototype.getRadioData = function( name ){
	var cons = this.getControls(name);
	var val="";
	for( var i= 0 ; i < cons.length ; i++){
		if( "radio" == cons[i].type && true == cons[i].checked ){
			val = cons[i].value;
			break;
		}
	}
	return val;
};
Converter.prototype.getCheckboxData = function( name ){
	var cons = this.getControls(name);
	var val= "" ;
	for( var i= 0 ; i < cons.length ; i++){
		if( "checkbox" == cons[i].type && true == cons[i].checked ){
			val +=  "<vals>" + cons[i].value +"</vals>";
		}
	}
	val = val.length > 0 ? val: '<vals></vals>';
	return val;
};