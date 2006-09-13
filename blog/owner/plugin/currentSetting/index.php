<?php
define('ROOT', '../../../..');
$IV = array(	'GET' => array(	'Name' => array('string')	));
require ROOT . '/lib/includeForOwner.php';

if (false) { // For optimization process
	textTreat();
	textareaTreat();
	selectTreat();
	checkboxTreat();
	radioTreat();
}
$targetURL = $hostURL.preg_replace( '/(currentSetting)$/' , 'receiveConfig' , $folderURL );
$pluginName = $_GET['Name'];
$result =  handleConfig($pluginName);
if( is_null($result) )	respondNotFoundPage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $result['css'];?>" />
<script type="text/javascript" src="<?php echo $service['path'];?>/script/EAF.js"></script>
<script type="text/javascript" src="<?php echo $service['path'];?>/script/pluginconfig.js"> </script>
<script type="text/javascript" >//<![CDATA[
var fiednamelist = <?php echo $result['script'] ;?>;

var errorMessage ={
	"1": "<?php echo _t('데이터처리 오류 발생.');?>",
	"2": "<?php echo _t('잘못된 입력 입니다.');?>"
};
function saveConfig(plugin){
	var xmlcon= new Converter(document, fiednamelist) ; 
   	var xmlData = encodeURIComponent(xmlcon.getXMLData());
	var request = new HTTPRequest("POST" , "<?php echo $targetURL;;?>");
	PM.addRequest(request, "<?php echo _t('설정을 저장하고 있습니다.');?>");
	request.onSuccess = function () {
		PM.removeRequest(this);
		PM.showMessage("<?php echo _t('저장 완료');?>", "center", "bottom");
	};		
	request.onError = function () {
		PM.removeRequest(this);
		if( this.getText("/response/error") == "9" )
			alert(this.getText("/response/customError"));
		else if( undefined != errorMessage[ this.getText("/response/error") ] )
			alert( errorMessage[ this.getText("/response/error") ] );
		else if( undefined != this.getText("/response/error") )			
			alert("<?php echo _t('알 수 없는 에러입니다.');?>" );
		else 
			alert("<?php echo _t('데이터 처리 페이지를 찾을 수 없습니다.');?>");
	};
	request.onVerify = function() {
		return (this.getText("/response/error") == "0" );
	};			
	request.send("Name=" + encodeURIComponent(plugin) + "&DATA=" + xmlData);
	xmlcon = null;
	request = null;
}	
//]]></script>
<title><?php echo $pluginName;?> config</title>
</head>
<body>
	<h3 class="caption"><?php echo $pluginName;?> <?php echo _t('설정');?></h3>
	<div id='config_data'>
	<?php echo $result['code'];?>
	</div>
	<div class="submit">
		<input type='button' value='<?php echo _t('설정');?>' onclick='saveConfig("<?php echo $pluginName;?>");' />
		<input type='button' value='<?php echo _t('닫기');?>' onclick='self.close();' />
	</div>
</body>
</html>
