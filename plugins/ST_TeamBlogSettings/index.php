<?php
function getTeamBlogInitConfigVal( &$data ){
	$postHeaderDefault = '<fieldset><legend>'._t('필자 소개').'</legend>';
	$postFooterDefault = '</fieldset>';
	$data['p1'] = !isset($data['p1'])?true:$data['p1'];
	$data['postHeader']	= !isset($data['postHeader'])?$postHeaderDefault:$data['postHeader'];
	$data['postFooter']	= !isset($data['postFooter'])?$postFooterDefault:$data['postFooter'];
	$data['imageSize']	= !isset($data['imageSize'])?80:$data['imageSize'];
	$data['imagePosition']	= !isset($data['imagePosition'])?'left':$data['imagePosition'];
	$data['lineColor']	= !isset($data['lineColor'])?'#e3e3e3':$data['lineColor'];
	$data['cssSelect']	= !isset($data['cssSelect'])?1:$data['cssSelect'];
}

function getTeamAuthorStyle($target, $mother){
	global $entry;
	$pool = DBModel::getInstance();
	$pool->reset("TeamUserSettings");
	$pool->setQualifier("blogid","eq",getBlogId());
	$pool->setQualifier("userid","eq",$entry['userid']);
	$row = $pool->getRow("style, image, profile");
	$authorStyle = '';
	if($row['style']){
		$style = explode("|", $row['style']);
		if($style[0]=="true"){
			$authorStyle = "font-weight:bold;";
		}
		if($style[1]=="true"){
			$authorStyle .= "font-style:italic;";
		}
		if($style[2]=="true"){
			$authorStyle .= "text-decoration:underline;";
		}
		if($style[3]){
			$authorStyle .= "color:{$style[3]};";
		}
		if($style[4]){
			$authorStyle .= "font-family:{$style[4]};";
		}
		if($style[5]){
			$authorStyle .= "font-size:{$style[5]}pt;";
		}
	}
	$target = "<span style=\"{$authorStyle}\">".$target."</span>";
	return $target;
}

function getTeamProfileView($target, $mother){
	global $entry, $entryView;
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	getTeamBlogInitConfigVal($data);
	if ($context->getProperty("suri.directive") != "/rss" && $context->getProperty("suri.directive") != "/sync" && $data['p1'] && empty($data['p2']) ) {
		$target .= getTeamProfile($entry['userid']);
	}
	if ($context->getProperty("suri.directive") != "/rss" && $context->getProperty("suri.directive") != "/sync" && $data['p1'] && !empty($data['p2']) ) {
		Utils_Misc::dress('TeamBlogProfileTag', getTeamProfile($entry['userid']), $entryView);
	}
	return $target;
}

function getTeamProfile($userid){
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	getTeamBlogInitConfigVal($data);
	$pool = DBModel::getInstance();
	$pool->init("TeamUserSettings");
	$pool->setQualifier("blogid","eq",getBlogId());
	$pool->setQualifier("userid","eq",$userid);
	$row = $pool->getRow("style, image, profile");
	$imageStyle = $imageTag = $html = '';
	if(!empty($row['image'])){
		$imageSrc = $context->getProperty("uri.service")."/attach/".getBlogId()."/team/".$row['image'];
		$imageTag = "<img src=\"".$imageSrc."\" alt=\"author image\" align=\"top\" />";
		$imageStyle = "style=\"width:".($data['imageSize']+6)."px; margin-right:10px;\"";
	}
	if(!empty($row['image']) || !empty($row['profile'])){
		$profile = nl2br(addLinkSense(htmlspecialchars($row['profile']), ' onclick="return openLinkInNewWindow(this)"'));
		$html  = "<div class=\"teamProfile\">";
		$html .= $data['postHeader'];
		$html .= "<div class=\"teamMain\">";
		$html .= "<div class=\"teamImage\" {$imageStyle}>".$imageTag."</div>";
		$html .= "<div class=\"teamDesc\">".$profile."</div>";
		$html .= "</div>";
		$html .= $data['postFooter'];
		$html .= "</div>";
	}
	return $html;
}

function getTeamBlogSettings() {
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	$pool = DBModel::getInstance();
	getTeamBlogInitConfigVal($data);
?>
	<script type="text/javascript" src="<?php echo $context->getProperty('plugin.uri');?>/plugin-main.js"></script>
<?php
	$pool->reset("Users");
	$pool->setQualifier("userid","eq",getUserId());
	$teamblog_user = $pool->getRow("name, loginid");
	$pool->reset("TeamUserSettings");
	$pool->setQualifier("userid","eq",getUserId());
	$pool->setQualifier("blogid","eq",getBlogId());
	$row = $pool->getRow("style, image, profile");
	if(!$row){
		$pool->reset("TeamUserSettings");
		$pool->setAttribute("blogid",getBlogId());
		$pool->setAttribute("userid",getUserId());
		$pool->setAttribute("style","",true);
		$pool->setAttribute("image","",true);
		$pool->setAttribute("profile","",true);
		$pool->setAttribute("updated",Timestamp::getUNIXtime());
		$pool->insert();
	}
	if($row['image']){
		$image = $context->getProperty("service.path")."/attach/".getBlogId()."/team/".$row['image'];
		$imageRemoveCheck = "";
	}else{
		$image = $context->getProperty("service.path")."/resources/image/spacer.gif";
		$imageRemoveCheck = " disabled ";
	}

	$authorStyle = "";
	$italicCheck = "";
	$underlineCheck = "";
	$boldCheck = "";
	$color = "";
	if($row['style']){
		$style = explode("|", $row['style']);
		if(isset($style[0]) && ($style[0]=="true")) {
			$authorStyle = "font-weight:bold;";
			$boldCheck = " checked ";
		}
		if(isset($style[1]) && ($style[1]=="true")) {
			$authorStyle .= "font-style:italic;";
			$italicCheck = " checked ";
		}
		if(isset($style[2]) && ($style[2]=="true")) {
			$authorStyle .= "text-decoration:underline;";
			$underlineCheck = " checked ";
		}
		if(isset($style[3]) && !empty($style[3])){
			$authorStyle .= "color:{$style[3]};";
			$color = $style[3];
		}
		if(isset($style[4]) && !empty($style[4])){
			$authorStyle .= "font-family:{$style[4]};";
			$family = explode(",",$style[4]);

		}
		if(isset($style[5]) && !empty($style[5])){
			$authorStyle .= "font-size:{$style[5]}pt;";
			$size = $style[5];

		}
	} else {
		$style = array();
	}
	$profile = "";
	if($row['profile']){
		$profile = $row['profile'];
	}
?>
	<div id="part-setting-teams" class="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('팀블로그 정보를 설정합니다');?></span></h2>

		<div class="data-inbox">
				<div  class="sectionGroup">
					<fieldset class="container">
						<legend><?php echo _t('필명 설정');?></legend>
						<dl id="team-style-line" class="line">
							<dt><label for="title"><?php echo _t('필명 스타일');?></label></dt>
							<dd>by <span id="nicknameStyle" style="<?php echo $authorStyle;?>"><?php echo htmlspecialchars($teamblog_user['name']);?></span></dd>
							<dd>
								<span><input <?php echo $boldCheck;?> type="checkbox" value="1" id="fontBold" onclick="styleExecCommand('fontBold', 'fontbold', 'bold');" /> <label for="fontBold"><b><?php echo _t('굵게'); ?></b></label></span>
								<span><input <?php echo $italicCheck;?> type="checkbox" id="fontItalic" onclick="styleExecCommand('fontItalic', 'fontitalic', 'italic');" /> <label for="fontItalic"><i><?php echo _t('기울임'); ?></i></label></span>
								<span><input <?php echo $underlineCheck;?> type="checkbox" id="fontUnderline" onclick="styleExecCommand('fontUnderline', 'fontunderline', 'underline');" /> <label for="fontUnderline"><u><?php echo _t('밑줄'); ?></u></label></span>
								<span id="fontStyleElement"></span>
								<script type="text/javascript">
										//<![CDATA[
											var colorCheck;
											var familyCheck;
											var sizeCheck;
											var html = ////
												'<input type="text" id="fontColor" class="input-text2" style="<?php echo empty($color) ? '' : 'color:$color'; ?>;" value="<?php echo $color;?>" />' +
												'<select id="fontColorList" style="width:80px;height:19px;" onchange="styleExecCommand(\'fontColor\', \'fontcolor\', this.value);">' +
													'<option class="head-option" value=""><?php echo _t('글자색'); ?><\/option>';
											for (var i = 0; i < colors.length; ++i) {
												<?php
													if(isset($style[3]) && !empty($style[3])) echo "colorCheck = (colors[i] == '".str_replace("#","",$color)."')?' selected ':'';";
												?>
												html += '<option style="color:#' + colors[i] + ';" value="#' + colors[i] + '" ' + colorCheck + '>#' + colors[i] + '<\/option>';
											}
											html += '<\/select>';

											html += ////
												'<select id="fontFamilyList" style="width:120px;height:19px;" onchange="styleExecCommand(\'\', \'fontname\', this.value); ">' +
													'<option class="head-option" value=""><?php echo _t('글자체'); ?><\/option>';
											var fontset = _t('fontDisplayName:fontCode:fontFamily').split('|');
											for (var i = 1; i < fontset.length; ++i) {
												var fontinfo = fontset[i].split(':');
												if (fontinfo.length != 3) continue;
												<?php
													if(isset($style[4]) && !empty($style[4])) echo "familyCheck = (fontinfo[1] == ".$family[0].")?' selected ':'';";
												?>
												html += '<option style="font-family: \'' + fontinfo[1] + '\';" value="\'' + fontinfo[1] + '\', \'' + fontinfo[2] + '\'"' + familyCheck + '>' + fontinfo[0] + '<\/option>';
											}
											for (var i = 0; i < defaultfonts.length; ++i) {
												var entry = defaultfonts[i];
												<?php
													if(isset($style[4]) && !empty($style[4])) echo "familyCheck = (entry[0] == ".$family[0].")?' selected ':'';";
												?>
												html += '<option style="font-family: \'' + entry[0] + '\';" value="\'' + entry.join("','") + '\'" ' + familyCheck + '>' + entry[0] + '<\/option>';
											}
											html += '<\/select>';

											html += ////
												'<select id="fontSizeList" style="width:50px;height:19px;" onchange="styleExecCommand(\'\', \'fontsize\', this.value); ">' +
													'<option class="head-option" value=""><?php echo _t('크기'); ?><\/option>';
											for (var i = 8; i < 16; ++i) {
												<?php
													if(isset($style[5]) && !empty($style[5])) echo "sizeCheck = (i == ".$size.")?' selected ':'';";
												?>
												html += '<option value="' + i + '" ' + sizeCheck + '>' + i + 'pt<\/option>';
											}
											html +=	'<\/select>';
											document.getElementById('fontStyleElement').innerHTML = html;
										//]]>
								</script>
							</dd>
						</dl>
						<dl>
							<dd class="button-box">
								<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setStyleSave(); return false;" />
							</dd>
						</dl>
						<dl id="team-image-line" class="line">
							<dt><label for="profile"><?php echo _t('프로필 사진');?></label></dt>
							<dd>
								<img id="teamImage" src="<?php echo $image;?>" width="80" height="80" border="1" alt="<?php echo _t('프로필 사진');?>" /><br />(Size : <?php echo $data['imageSize']?> x <?php echo $data['imageSize']?>)<br />
								<form id="file_upload_form" method="post" target="uploadTarget" enctype="multipart/form-data" action="<?php echo $blogURL;?>/plugin/teamFileUpload/">
									<input type="hidden" name="type" value="" />
									<input type="file" name="teamImageFile" id="teamImageFile" size="35" onchange="uploadImage(this.form, 'upload');" /> <a href="http://mypictr.com/?size=<?php echo $data['imageSize']?>x<?php echo $data['imageSize']?>" onclick="window.open(this.href); return false;"><font color="#527A98"><u>mypictr.com</u></font></a>에서 사진 편집.<br />
									<div class="tip"><?php echo _t('(찾아보기를 이용하여 사진을 선택하시면 바로 <b>변경</b> 저장됩니다)'); ?></div>
									<div class="tip">
										<input type="checkbox" name="imageRemove" id="imageRemove" onclick="uploadImage(this.form, 'delete');" <?php echo $imageRemoveCheck;?> />
										<label for="imageRemove"><?php echo _t('프로필 사진 초기화');?></label>
									</div>
									<iframe id="uploadTarget" name="uploadTarget" style="width:0;height:0;border:0px solid;"></iframe>
								</form>
						</dd>
						</dl>
						<dl id="team-profile-line" class="line">
							<dt><label for="profile"><?php echo _t('프로필 내용');?></label></dt>
							<dd>
								<textarea id="profile" name="profile" cols="15" rows="7"><?php echo htmlspecialchars($profile);?></textarea>
							</dd>
						</dl>
						<dl>
							<dd class="button-box">
								<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setProfileSave(); return false;" />
							</dd>
						</dl>
					</fieldset>
				</div>
		</div>
	</div>

	<div class="clear"></div>

<?php
}

function getTeamContentsSave($target){
	$pool = DBModel::getInstance();
	$flag = isset($_POST['flag']) ? $_POST['flag'] : '';
	$style = isset($_POST['fontstyle']) ? $_POST['fontstyle'] : '';
	$profile = isset($_POST['profile']) ? $_POST['profile'] : '';
	if(doesHaveOwnership() && doesHaveMembership()){
		if($flag == "style"){
			$pool->reset("TeamUserSettings");
			$pool->setAttribute("style",$style,true);
			$pool->setAttribute("updated",Timestamp::getUNIXtime());
			$pool->setQualifier("blogid","eq",getBlogId());
			$pool->setQualifier("userid","eq",getUserId());
			if($pool->update()) {
				Respond::ResultPage(0);
			}
		}else if($flag == "profile"){
			$profile = Utils_Unicode::lessenAsEncoding($profile, 65535);
			$pool->reset("TeamUserSettings");
			$pool->setAttribute("profile",$profile,true);
			$pool->setAttribute("updated",Timestamp::getUNIXtime());
			$pool->setQualifier("blogid","eq",getBlogId());
			$pool->setQualifier("userid","eq",getUserId());
			if($pool->update()) {
				Respond::ResultPage(0);
			}
		}
		Respond::ResultPage(-1);
	}
}

function getImageFileUpload($target){
	if(doesHaveOwnership() && doesHaveMembership()){
		$type = $_POST['type'];
		$file = $_FILES['teamImageFile'];
		$errcode = 0;
		if($type == "upload"){
			$fileExt=Path::getExtension($file['name']);
			if(($fileExt!='.gif')&&($fileExt!='.jpg')&&($fileExt!='.png')){
				$errmsg = _t('잘못된 파일 형식입니다. 다시 시도하세요');
				$errcode = 1;
			}else{
				$result = getAddAttachment($file);
				$errmsg = _t('새로운 프로필 사진을 저장 했습니다.');
			}
		}else if($type == "delete"){
			$pool->reset("TeamUserSettings");
			$pool->setQualifier("blogid","eq",getBlogId());
			$pool->setQualifier("userid","eq",getUserId());
			$tmpImage = $pool->getCell("image");
			if($tmpImage){
				$result = getDeleteAttachment();
				$errmsg = _t('등록된 프로필 사진을 삭제 하였습니다.');
			}else{
				$errmsg = _t('삭제할 파일이 없습니다. 다시 시도하세요');
				$errcode = 1;
			}
		}
	}

	$script  = '<script type="text/javascript">//<![CDATA'.CRLF;
	if($errcode != 1){
		$script .= '	window.parent.top.document.getElementById("teamImage").src = "'.$result.'";';
	}
	$script .= '	window.parent.top.PM.showMessage("'.$errmsg.'", "center", "bottom");';
	$script .= '//]]></script>';
	echo $script;
	exit;
}

function getAddAttachment($file){
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	Attachment::confirmFolder();
	if(empty($file['name'])||($file['error']!=0))
		return false;
	$attachment = array();
	$attachment['ext'] = Utils_Misc::getFileExtension(Path::getBaseName($file['name']));
	$path = __TEXTCUBE_ATTACH_DIR__."/".getBlogId()."/team";
	if(!is_dir($path)){
		mkdir($path);
		if(!is_dir($path))
			return false;
		@chmod($path,0777);
	}
	do{
		$attachment['name']=rand(1000000000,9999999999).".".$attachment['ext'];
		$attachment['path']="$path/{$attachment['name']}";
	}while(file_exists($attachment['path']));

	if(!move_uploaded_file($file['tmp_name'],$attachment['path']))
		return false;
	@chmod($attachment['path'],0666);
	$pool->reset("TeamUserSettings");
	$pool->setQualifier("blogid","eq",getBlogId());
	$pool->setQualifier("userid","eq",getUserId());
	$tmpImage = $pool->getCell("image");

	$pool->reset("TeamUserSettings");
	$pool->setAttribute("image",$attachment['name'],true);
	$pool->setAttribute("updated",Timestamp::getUNIXtime());
	$pool->setQualifier("blogid","eq",getBlogId());
	$pool->setQualifier("userid","eq",getUserId());
	if(!$pool->update()) {
		@unlink($attachment['path']);
		$result = $context->getProperty("uri.service")."/resources/image/spacer.gif";
	}else{
		$result = $context->getProperty("uri.service")."/attach/".getBlogId()."/team/".$attachment['name'];
	}
	if(!empty($tmpImage))
	@unlink($path."/".$tmpImage);
	return $result;
}

function getDeleteAttachment($filename){
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();

	$pool->reset("TeamUserSettings");
	$pool->setQualifier("blogid","eq",getBlogId());
	$pool->setQualifier("userid","eq",getUserId());
	$tmpImage = $pool->getCell("image");
	if($tmpImage){
		$pool->reset("TeamUserSettings");
		$pool->setAttribute("image","",true);
		$pool->setAttribute("updated",Timestamp::getUNIXtime());
		$pool->setQualifier("blogid","eq",getBlogId());
		$pool->setQualifier("userid","eq",getUserId());
		$pool->update();
		@unlink(__TEXTCUBE_ATTACH_DIR__."/".getBlogId()."/team/".$tmpImage);
	}
	$result = "{$serviceURL}/resources/image/spacer.gif";
	return $result;
}

function getTeamBlogStyle($target) {
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	getTeamBlogInitConfigVal($data);
	if($data['cssSelect'] == 1){
		$target .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$context->getProperty("uri.blog")."/plugin/teamBlogStyle/\" />".CRLF;
	}
	return $target;
}

function getTeamBlogStyleSet($target){
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	getTeamBlogInitConfigVal($data);
	$lineColor = (strpos($data['lineColor'], "#")===0)?$data['lineColor']:"#".$data['lineColor'];
	header('Content-type: text/css; charset=utf-8');
	echo '
		/* Team Blog Profile CSS Setting */
		.teamProfile		{ margin:10px 0px 0px 0px;font:8.5pt dotum, AppleGothic;}
		.teamProfile fieldset{ margin:0px; border:1px solid '.$lineColor.'; padding:0px 0px 6px 0px;}

		.teamProfile legend { font-weight:bold; margin:0 0 0 5px;}
		*html .teamProfile legend { margin:0; padding:0 !important;}
		*:first-child+html .teamProfile legend { margin:0; padding:0 !important;}

		.teamProfile .teamMain  {margin:0px 6px;}
		.teamProfile .teamImage {margin:5px 0px 0px 0px; float:'.$data['imagePosition'].';}
		.teamProfile .teamImage img {padding:2px; border:1px solid '.$lineColor.'; width:'.$data['imageSize'].'px; background-color:#fff;}
		.teamProfile .teamDesc  {margin:5px 0px 0px 0px; float:'.$data['imagePosition'].';}
	';
	flush();
}

function getTeamBlog_DataSet($DATA){
	$cfg = Setting::fetchConfigVal($DATA);
	return true;
}
?>
