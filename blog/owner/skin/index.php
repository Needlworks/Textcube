<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu30.php';
$skins = array();
$dirHandler = dir(ROOT . "/skin");
while ($file = $dirHandler->read()) {
	if ($file == '.' || $file == '..')
		continue;
	if (!file_exists(ROOT . "/skin/$file/skin.html"))
		continue;
	$preview = "";
	if (file_exists(ROOT . "/skin/$file/preview.jpg"))
		$preview = "{$service['path']}/skin/$file/preview.jpg";
	if (file_exists(ROOT . "/skin/$file/preview.gif"))
		$preview = "{$service['path']}/skin/$file/preview.gif";
	array_push($skins, array('name' => $file, 'path' => ROOT . "/skin/$file/", 'preview' => $preview));
}

function writeValue($value, $label) {
?>
	<tr>
		<td style="color:#516575; text-align:right; width:90px;"><?=$label?> &nbsp;:&nbsp; </td>
		<td style="color:#516575;"><?=nl2br(addLinkSense($value, ' target="_blank"'))?> </td>
	</tr>
	<tr>
		<td colspan="2" height="1" bgcolor="#bdd4ec"></td>
	</tr>
<?
}
?>
<script type="text/javascript">
	//<![CDATA[
		var isSkinModified = <?=($skinSetting['skin'] == "customize/$owner") ? 'true' : 'false'?>;

		function selectSkin(name) {
			if(isSkinModified) {
				if(!confirm("<?=_t('수정된 스킨을 사용중입니다. 새로운 스킨을 선택하면 수정된 스킨의 내용은 모두 지워집니다.\n스킨을 적용하시겠습니까?')?>"))
					return;
			}

			try {
			var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/skin/change/");
			request.onSuccess = function() {
				isSkinModified = false;
				PM.showMessage("<?=_t('성공적으로 변경했습니다')?>", "center", "bottom");
				document.getElementById('currentPreview').innerHTML = document.getElementById('preview_'+name).innerHTML;
				document.getElementById('currentInfo').innerHTML = document.getElementById('info_'+name).innerHTML;
				window.location.href = "#currentSkinAnchor";
				//eleganceScroll('currentSkin',8);
				/*
				document.getElementById('currentSkinPreview').innerHTML = document.getElementById('preivew_'+name).innerHTML
				document.getElementById('currentSkinInfo').innerHTML = document.getElementById('info_'+name).innerHTML
				*/
			} 
			request.onError = function() {
				alert(result['msg']);
			}
			request.send("skinName=" + encodeURIComponent(name));
			} catch(e) {
				alert(e.message);
			}
		}

	//]]>
</script>

	<table cellspacing="0" style="width:100%; height:28px;">
		<tr>
			<td style="width:18px;"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
			<td style="padding:3px 0px 0px 4px;"><?=_t('현재 사용하고 있는 스킨')?></td>
		</tr>
	</table>
	<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED; background-color:#EBF2F8">
		<tr>
			<td style="background-color:#EBF2F8; padding:10px 5px 10px 25px;">
				<span id="currentSkin" style="display:none">
					<a name = "currentSkinAnchor" />
					<table>
						<tr>
							<td valign="top">
								<table border="0" cellpadding="2" cellspacing="1" bgcolor="#9a9a9a">
									<tr>
										<td bgcolor="#ffffff">
											<span id="currentPreview">
												
											</span>
										</td> 
									</tr>
								</table>
							</td>
							<td style="padding:5px 0px 0px 5px;" valign="top">
								<table>
									<tr>
										<td colspan="2">
											<span id="currentInfo">
											
											</span>									
										</td>
									</tr>
<?
?>
									<tr>
										<td style="color:#516575;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="Submit2" value="<?=_t('편집하기')?>" style="border:1px solid #666; background-color:#eee; padding-top:4px;" onclick="window.location='<?=$blogURL?>/owner/skin/edit'">		
										</td>
									</tr>
<?
?>									
								</table>
							</td>
						</tr>
					</table>	
				</span>
				<span id="currentSkinLoading">
					now loading....
				</span>
				
			<tr>
				<td height="5">
				</td>
			</tr>
	</table>
	
<br />
<br />
<table cellspacing="0" style="width:100%; height:28px;">
    <tr>
        <td style="width:18px;"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
        <td style="padding:3px 0px 0px 4px;"><?=_t('스킨을 변경하시려면 아래 목록에서 마음에 드는 스킨을 골라 사용하기 버튼을 클릭해 주세요')?></td>
    </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
    <tr>
        <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;">
			<table width="100%" >
                <?
$count = 0;
for ($i = 0; $i < count($skins); $i++) {
	$skin = $skins[$i];
	echo '<tr valign="top">';
?>
		<td width="50%" style="padding:20px 0 5px 20px;"  >
			
				<table  width="100%">
					<tr>
						<td valign="top">
							<table border="0" cellpadding="2" cellspacing="1" bgcolor="#9a9a9a">
								<tr>
									<td bgcolor="#ffffff">
										<span id="preview_<?=$skin['name']?>">
	<?
	if ($skin['preview'] == '') {
?>		
		<img alt="" src="<?=$service['path']?>/image/owner/noPreview.gif" style="width:150px; height:150px;"/>
	<?
	} else {
?>	
		<img alt="" src="<?=$skin['preview']?>" style="width:150px; height:150px;"/>
	<?
	}
?>
										</span>
									</td> 
								</tr>
							</table>
						</td>
						<td width="20">
						
						</td>
						<td style="padding:5px 0px 0px 5px;" valign="top" width="100%">
							<table>
								<tr>					
									<td>
										<span id="info_<?=$skin['name']?>">															
											<table width="600">
<?
	if (file_exists(ROOT . "/skin/{$skin['name']}/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/{$skin['name']}/index.xml");
		$xmls = new XMLStruct();
		$xmls->open($xml, $service['encoding']);
		writeValue('<b>' . $xmls->getValue('/skin/information/name') . '</b>&nbsp;&nbsp;&nbsp;ver.' . $xmls->getValue('/skin/information/version'), _t('제목'));
		writeValue($xmls->getValue('/skin/information/license'), _t('저작권'));
		writeValue($xmls->getValue('/skin/author/name'), _t('만든이'));
		writeValue($xmls->getValue('/skin/author/homepage'), _t('홈페이지'));
		writeValue($xmls->getValue('/skin/author/email'), _t('E-mail'));
		writeValue($xmls->getValue('/skin/information/description'), _t('설명'));
	} else {
		writeValue($skin['name'], _t('제목'));
	}
?>											
											</table>
										</span>
									</td>
								</tr>
								<tr>
									<td style="color:#516575;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="Submit2" value="<?=_t('미리보기')?>" style="border:1px solid #666; background-color:#eee; padding-top:4px;" onclick="location.href='<?=$blogURL?>/owner/skin/preview/?skin=<?=$skin['name']?>'">
										<input type="button" name="Submit22" value="<?=_t('적용')?>" style="border:1px solid #666; background-color:#eee; padding-top:4px;" onclick="selectSkin('<?=$skin['name']?>');">
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>			
			</span>
		</td>
			
		</tr>
		
		<tr>
			<td height="20"></td>
		</tr>
		
        <?
}
?>
</table>
</td>
</tr>
</table>
	
	
</td>
</tr>
</table>
<script type="text/javascript">
	//<![CDATA[
	try {
		document.getElementById('currentPreview').innerHTML = document.getElementById('preview_<?=$skinSetting['skin']?>').innerHTML;
		document.getElementById('currentInfo').innerHTML = document.getElementById('info_<?=$skinSetting['skin']?>').innerHTML;
		document.getElementById('currentSkin').style.display = "block";
		document.getElementById('currentSkinLoading').style.display = "none";
	} catch(e) {
		document.getElementById('currentPreview').innerHTML ='<img alt="" src="<?=$service['path']?>/image/owner/noPreview.gif" style="width:150px; height:150px;"/>';
		document.getElementById('currentInfo').innerHTML = "<?=_t('수정된 스킨입니다')?>";
		document.getElementById('currentSkin').style.display = "block";
		document.getElementById('currentSkinLoading').style.display = "none";
	}

	//]]>
</script>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>
