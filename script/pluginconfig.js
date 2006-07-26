function Converter(data_Set ){
	this.data_Set=data_Set;
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
		} catch(e){}
	}	
	return this.xmlHeader + innerXml + this.xmlfooter;
};

Converter.prototype.makeDataSet = function( fieldset ){
	var ele ='';
	var fields = fieldset.getElementsByTagName("FIELD");
	for( var i =0; i < fields.length ; i++){
		if( undefined != fields[i].getAttribute('name') && undefined != fields[i].getAttribute('type')){
			var type = fields[i].getAttribute('type');
			ele += '<field   name = "' + fields[i].getAttribute('name') +'" type="' + type+ '" >';
			switch( type){
				case 'text':
					ele += '<![CDATA['+ this.getTextData( fields[i] )+']]>' ;break;
				case 'textarea':
					ele += '<![CDATA['+this.getTextareaData( fields[i] )+']]>';break;
				case 'radio':
					ele += this.getRadioData( fields[i] );break;
				case 'checkbox':
					ele += this.getCheckboxData( fields[i] );break;
				case 'select':
					ele += this.getSelectData( fields[i] );break;
				default:
					throw new error(0,"asdsd");
			}
			ele += '</field >\n';
		}
	}
	return ele;
};


Converter.prototype.getTextData = function ( field){
	var con = field.getElementsByTagName("input")[0];
	return con.value;
};
Converter.prototype.getTextareaData = function( field){
	var con = field.getElementsByTagName("textarea")[0];
	return con.value;
};
Converter.prototype.getRadioData = function( field){
	var cons = field.getElementsByTagName("input");
	var val="";
	for( var i= 0 ; i < cons.length ; i++){
		if( "radio" == cons[i].type && true == cons[i].checked ){
			val = cons[i].value;
			break;
		}
	}
	return val;
};
Converter.prototype.getCheckboxData = function( field){
	var cons = field.getElementsByTagName("input");
	var val="";
	for( var i= 0 ; i < cons.length ; i++){
		if( "checkbox" == cons[i].type && true == cons[i].checked ){
			val +=  "<vals>" + cons[i].value +"</vals>";
		}
	}
	return val;
};
Converter.prototype.getSelectData= function( field){
	var con = field.getElementsByTagName("select")[0];
	return con.value;
};