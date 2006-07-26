<?php
define('ROOT', '../../../../..');
$IV = array(	'GET' => array(	'Name' => array('string')	));
require ROOT . '/lib/includeForOwner.php';
$pluginName = $_GET['Name'];
$result =  handleConfig($pluginName);
if( is_null($result) )	respondNotFoundPage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo $service['path']?>/style/configStyle.css" />
<script type="text/javascript" src="<?php echo $service['path']?>/script/EAF.js"></script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/pluginconfig.js"> </script>
<script type="text/javascript" >//<![CDATA[
var errorMessage ={
	"1": "<?php echo _t('디비에서')?>",
	"2": "<?php echo _t('플러그인 제작자 검증에서')?>"
}
function saveConfig(plugin){
	if( !document ) return false;
	var dataSet = document.getElementById('config_data');
	var xmlcon= new Converter( dataSet) ;
	var xmlData = encodeURIComponent(xmlcon.getXMLData());
	var request = new HTTPRequest("POST" , "<?php echo $blogURL?>/owner/setting/plugins/recieveConfig");
	PM.addRequest(request, "<?php echo _t('설정을 저장중 입니다.')?>");
	request.onSuccess = function () {
		PM.removeRequest(this);
		PM.showMessage("<?php echo _t('저장 완료')?>", "center", "bottom");
	};		
	request.onError = function () {
		PM.removeRequest(this);
		if( this.getText("/response/error") == "9" )
			alert(this.getText("/response/customError"));
		else
			alert( errorMessage[ this.getText("/response/error") ] );
	};
	request.onVerify = function() {
		return (this.getText("/response/error") == "0" );
	};			
	request.send("Name=" + encodeURIComponent(plugin) + "&DATA=" + xmlData);
	xmlcon = null;
	request = null;
}	
//]]></script>
<title><?php echo $pluginName?> config</title>
</head>
<body>
<h3><?php echo $pluginName?> CONFIG</h3>
<div id='config_data'><?php echo $result?></div>
<div width='100%' align='center'>
	<input type='button' value='<?php echo _t('설정')?>' onclick='saveConfig("<?php echo $pluginName?>");'>
</div>
</body>
</html>
