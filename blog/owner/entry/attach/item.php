<?
define('ROOT', '../../../..');
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'fileName' => array('filename')
		),
		'FILES' => array(
			'attachment' => array('file')
		)
	);
}
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
<script type="text/javascript">
//<![CDATA[
oSelect = window.parent.document.getElementById('fileList');
function addAttachOption(value) {
	window.parent.makeCrossDamainSubmit("<?=$blogURL?>/owner/entry/attach/<?=$suri['id']?>","ie");
	if (isWin) {
		var fileName = value.substring(value.lastIndexOf('\\')+1);
	} else {
		var fileName = value.substring(value.lastIndexOf('/')+1);
	}
	
	var oSelect = window.parent.document.getElementById('fileList');	
	var oOption = window.parent.document.createElement("option");
	oOption.innerHTML= fileName+ "  <?=_t('업로드 중..')?>"; 
	oOption.value=fileName;
	oSelect.appendChild(oOption);
	oSelect.setAttribute('size',Math.max(8,Math.min(oSelect.length,30)));
	document.getElementById('fileNameInput').setAttribute('value',fileName);
}

function checkUploadMode(oEvent) {
	try {
		if(isIE) 
			uploader = window.parent.document.getElementById("uploader");
		else 
			uploader = window.parent.document.getElementById("uploader2");
	} catch(e) {		
		uploader = null;	
	}
	
	
	if (uploader!=null) {
		uploader.SetVariable('/:openBroswer','true');
		
			
	}
}
<?
if (count($_FILES) == 1) {
	$file = array_pop($_FILES);
	if (getAttachmentByLabel($owner, $suri['id'], Path::getBaseName($file['name']))) {
		print ('alert("' . _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.') . '");');
?>

		for( i=0; i<oSelect.options.length; i++) {
			if(oSelect.options[i].value == "<?=escapeJSInCData($_POST['fileName'])?>") {
				oSelect.remove(i);
			}
		}
<?
	} else if (($attachment = addAttachment($owner, $suri['id'], $file)) === false) {
		print ('alert("' . _t('첨부하지 못했습니다.') . '");');
?>

		for( i=0; i<oSelect.options.length; i++) {
			if(oSelect.options[i].value == "<?=escapeJSInCData($_POST['fileName'])?>") {
				oSelect.remove(i);
			}
		}
<?
	} else {
?>
	
		var oOption = window.parent.document.createElement("option");	
		oOption.innerHTML= "<?=escapeJSInCData(getPrettyAttachmentLabel($attachment))?>";
		oOption.value = "<?=escapeJSInCData(getAttachmentValue($attachment))?>";
		try {
<?
		if (!empty($attachment)) {
?>
			for( i=0; i<oSelect.options.length; i++) {
				//alert(oSelect.options[i].value+"   "+ "<?=escapeJSInCData($attachment['label'])?>");
				if(oSelect.options[i].value == "<?=escapeJSInCData($attachment['label'])?>") {
					oSelect.remove(i);
				}
			}
<?
		}
?>
		oSelect.appendChild(oOption);
		//oSelect.selectedIndex = oSelect.options.length - 1;
		//window.parent.document.getElementById("selectedImage").src = "<?=(strncmp($attachment['mime'], 'image/', 6) == 0 ? "{$service['path']}/attach/$owner/{$attachment['name']}" : "{$service['path']}/image/spacer.gif")?>";
			window.parent.refreshFileSize();
		} catch(e) {
		alert('['+e.message+']');
	}
	
<?
	}
}
?>
//]]>
</script>
<style type="text/css">
<!--
body {
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #D0E5F1;
}
-->
</style></head>
<body>
<form method="post" action="" enctype="multipart/form-data" style="position:absolute; left:0px">
<script type="text/javascript">
//<![CDATA[
	try {
		if(isIE) {
			uploader = window.parent.document.getElementById("uploader");
		} else {
			uploader = window.parent.document.getElementById("uploader2");
		}
	} catch(e) {		
		
	}
	
	if (uploader!=null) {
		document.write('<input type="file" name="attachment" onClick="uploader.SetVariable(\'/:openBroswer\',\'true\');return false;" onchange="addAttachOption(this.value);document.forms[0].submit()" style="font-size: 9pt; height:24px"/>');
	} else {
		document.write('<input type="file" name="attachment"  onchange="addAttachOption(this.value);document.forms[0].submit()" style="font-size: 9pt; height:24px"/>');
	}
//]]>	
</script>
  <input type="hidden" id="fileNameInput" name="fileName" value=""/>
</form>
</body>
</html>
