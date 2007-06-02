<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'FILES'=>array(
		'logo'=>array('file','mandatory'=>false)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
function addTeamPic($file){
	global $database, $owner, $_SESSION;
	if(empty($file['name'])||($file['error']!=0))
		return false;
		
	$attachment=array();
	$attachment['label']=Path::getBaseName($file['name']);
	$label=mysql_tt_escape_string($attachment['label']);
	$attachment['size']=$file['size'];
	$extension=Path::getExtension($attachment['label']);
	

	switch(strtolower($extension)){
		case '.exe':
		case '.php':
		case '.sh':
		case '.com':
		case '.bat':
		case '.cgi':
		case '.pl':
			return false;
			break;
	}

	// Create directory if not exists.
	$pcheck = ROOT. '/attach/1';
	$path = ROOT. '/attach/1/teamProfileImages';
	if(!is_dir($pcheck)){
		mkdir($pcheck);
		if(!is_dir($pcheck))
			return false;
		@chmod($pcheck,0777);
	}
	if(!is_dir($path)){
		mkdir($path);
		if(!is_dir($path))
			return false;
		@chmod($path,0777);
	}
	do {
		$attachment['name']=rand(1000000000,9999999999)."$extension";
		$attachment['path']="$path/{$attachment['name']}";
	} while(file_exists($attachment['path']));
	if($imageAttributes=@getimagesize($file['tmp_name'])){
		$attachment['mime']=$imageAttributes['mime'];
		$attachment['width']=$imageAttributes[0];
		$attachment['height']=$imageAttributes[1];
	}

	if(!move_uploaded_file($file['tmp_name'],$attachment['path']))
		return false;
	@chmod($attachment['path'],0666);

	$logoFilePath = DBQuery::queryCell("SELECT logo FROM {$database['prefix']}Teamblog WHERE teams='".$owner."' and userid='."$_SESSION['admin']."'");	
	$result = DBQuery::query("UPDATE {$database['prefix']}Teamblog SET `logo`='$attachment[name]' WHERE teams='$owner' AND userid='$_SESSION[admin]' ");
	if(!$result){
		@unlink($attachment['path']);
		return false;
	}
	
	if(!empty($logoFilePath))
		@unlink($path."/".$logoFilePath);
	if($attachment['height'] > 93){
		$height = 93;
		$width = $height * $attachment['width'] / $attachment['height'];
		$attachment['width'] = $width;
		$attachment['height'] = $height;
	}
	return $attachment;
}

function changeTeamPic($file){
	if(($label=addTeamPic($file))===false){
		return false;
	}
	return $label;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript">
//<![CDATA[
<?php
if(count($_FILES)==1){

		$file=array_pop($_FILES);
		if(($label = changeTeamPic($file))===false){
			print ('alert("'._t('변경하지 못했습니다.').'");');
		}else{
			$logo = $label['name'];
			$width = $label['width'];
			$height = $label['height'];

?>
		window.parent.document.getElementById('logo').src = "<?php echo empty($logo)?"{$service['path']}/image/spacer.gif":"{$service['path']}/attach/1/teamProfileImages/{$logo}"; ?>";
		window.parent.document.getElementById('logo').width = <?php echo $width ?>;
		window.parent.document.getElementById('logo').height = <?php echo $height ?>;
<?php } } ?>
//]]>
</script>
<style type="text/css">
<!--
body {
	background-color: #ffffff;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
</head>
<body>
	<form method="post" action="<?=$blogURL?>/owner/setting/teamblog/profileImage/index.php" enctype="multipart/form-data">
		<input type="file" name="logo" onchange="document.forms[0].submit()" width="50" />

	</form>
</body>
</html>
