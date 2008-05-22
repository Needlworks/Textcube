<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';

if(isset($_POST['search'])) {
	$search = $_POST['search'];
} else $search = null;

$skins = array();
$dirHandler = dir(ROOT . "/skin");
while ($file = $dirHandler->read()) {
	$skin = array();
	if ($file == '.' || $file == '..')
		continue;
	if (!file_exists(ROOT . "/skin/$file/skin.html"))
		continue;
	$preview = "";
	if (file_exists(ROOT . "/skin/$file/preview.jpg"))
		$preview = "{$service['path']}/skin/$file/preview.jpg";
	if (file_exists(ROOT . "/skin/$file/preview.gif"))
		$preview = "{$service['path']}/skin/$file/preview.gif";
	
	if (file_exists(ROOT . "/skin/$file/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$file/index.xml");
		$xmls = new XMLStruct();
		$xmls->open($xml, $service['encoding']);
		$skin['skinName']     = $xmls->getValue('/skin/information/name');
		$skin['version']  = $xmls->getValue('/skin/information/version');
		$skin['license']  = $xmls->getValue('/skin/information/license');
		$skin['maker']    = $xmls->getValue('/skin/author/name');
		$skin['homepage'] = $xmls->getValue('/skin/author/homepage');
		$skin['email']    = $xmls->getValue('/skin/author/email');
		$skin['description'] = $xmls->getValue('/skin/information/description');
	}

	if(!empty($search) && 
		(stristr($skin['skinName'],$search) === false) && 
		(stristr($skin['maker'],$search) === false) &&
		(stristr($skin['homepage'],$search) === false) &&
		(stristr($skin['email'],$search) === false) &&
		(stristr($skin['description'],$search) === false)) continue; // Search.

	$skin['name'] = $file;
	$skin['path'] = ROOT . "/skin/$file/";
	$skin['preview'] = $preview;
	
	array_push($skins, $skin);
}

function writeValue($value, $label, $className) {
?>
										<tr class="<?php echo $className;?>-line">
											<td class="name"><?php echo $label;?></td>
											<td class="explain"><?php echo nl2br(addLinkSense($value, ' onclick="window.open(this.href); return false;"'));?></td>
										</tr>
<?php
}
?>
						<script type="text/javascript">
							//<![CDATA[
								var isSkinModified = <?php echo ($skinSetting['skin'] == "customize/$blogid") ? 'true' : 'false';?>;
								
								function selectSkin(name) {
									if(isSkinModified) {
										if(!confirm("<?php echo _t('수정된 스킨을 사용중입니다. 새로운 스킨을 선택하면 수정된 스킨의 내용과 스킨에 적용된 출력 설정, 사이드바의 변경점은 모두 지워집니다.\n스킨을 적용하시겠습니까?');?>"))
											return;
									} else {
										if(!confirm("<?php echo _t('새로운 스킨을 선택하면 이전 스킨에 적용된 출력 설정들과 사이드바의 변경점은 모두 지워집니다.\n스킨을 적용하시겠습니까?');?>"))
											return;
									}
									try {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/change/");
										request.onSuccess = function() {
											isSkinModified = false;
											PM.showMessage("<?php echo _t('성공적으로 변경했습니다.');?>", "center", "bottom");
											document.getElementById('currentPreview').innerHTML = document.getElementById('preview_'+name).innerHTML;
											document.getElementById('currentInfo').innerHTML = document.getElementById('info_'+name).innerHTML;
											//document.getElementById('currentButton').innerHTML = document.getElementById('button_'+name).innerHTML;
											//window.location.href = "#currentSkinAnchor";
											//eleganceScroll('currentSkin',8);
											/*
											document.getElementById('currentSkinPreview').innerHTML = document.getElementById('preivew_'+name).innerHTML
											document.getElementById('currentSkinInfo').innerHTML = document.getElementById('info_'+name).innerHTML
											*/
										}
										request.onError = function() {
											msg = this.getText("/response/msg");
											if (this.getText("/response/msg") == null)
												msg = "<?php echo _t('올바른 스킨 디렉토리명이 아닙니다.\n디렉토리명에는 알파벳, 숫자, 언더바(_), 공백문자, 대쉬(-)만 사용하실 수 있습니다.');?>";
											alert(msg);
										}
										request.send("skinName=" + encodeURIComponent(name));
									} catch(e) {
										alert(e.message);
									}
								}
							//]]>
						</script>
						
						<div id="part-skin-current" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('현재 사용중인 스킨입니다');?></span></h2>
							
							<div class="data-inbox">
								<div id="currentSkin" class="section">
									<a id="currentSkinAnchor"></a>
									<div id="currentPreview" class="preview">
<?php
if (file_exists(ROOT."/skin/".$skinSetting['skin']."/preview.jpg")) {
?>
										<img src="<?php echo $service['path'];?>/skin/<?php echo $skinSetting['skin'];?>/preview.jpg" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
} else if (file_exists(ROOT."/skin/".$skinSetting['skin']."/preview.gif")) {
?>
										<img src="<?php echo $service['path'];?>/skin/<?php echo $skinSetting['skin'];?>/preview.gif" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
} else {
?>
										<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/noPreview.gif" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
}
?>
									</div>
									<div class="information">
<?php
if (file_exists(ROOT . "/skin/{$skinSetting['skin']}/index.xml")) {
?>
										<div id="currentInfo">
											<table cellspacing="0" cellpadding="0">
<?php
	$xml = file_get_contents(ROOT . "/skin/{$skinSetting['skin']}/index.xml");
	$xmls = new XMLStruct();
	$xmls->open($xml, $service['encoding']);
	writeValue('<span class="skin-name">' . $xmls->getValue('/skin/information/name') . '</span> <span class="version">ver.' . $xmls->getValue('/skin/information/version') . ($skinSetting['skin'] == "customize/$blogid" ? _t('(사용자 수정본)') : NULL) . '</span>', _t('제목'), "title");
	writeValue($xmls->getValue('/skin/information/license'), _t('저작권'), "license");
	writeValue($xmls->getValue('/skin/author/name'), _t('만든이'), "maker");
	writeValue($xmls->getValue('/skin/author/homepage'), _t('홈페이지'), "homepage");
	writeValue($xmls->getValue('/skin/author/email'), _t('e-mail'), "email");
	writeValue($xmls->getValue('/skin/information/description'), _t('설명'), "explain");
?>
											</table>
										</div>
										<div class="button-box">
											<!--span id="currentButton">
												<a class="preview-button button" href="<?php echo $blogURL;?>/owner/skin/preview/?skin=<?php echo $skinSetting['skin'];?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('미리보기');?></span></a>
												<span class="hidden">|</span>
												<a class="apply-button button" href="<?php echo $blogURL;?>/owner/skin/change/?skinName=<?php echo urlencode($skinSetting['skin']);?>" onclick="selectSkin('<?php echo $skinSetting['skin'];?>'); return false;"><span class="text"><?php echo _t('적용');?></span></a>
											</span-->
											<span class="hidden">|</span>
											<a class="edit-button button" href="<?php echo $blogURL;?>/owner/skin/edit"><span class="text"><?php echo _t('편집하기');?></span></a>
										</div>
<?php
} else {
?>
										<div id="currentInfo">
											<div id="customizedTable">
												<?php echo _t('선택하신 스킨이 존재하지 않습니다. 다른 스킨을 선택해 주시기 바랍니다.').CRLF;?>
											</div>
										</div>
										<div class="button-box">
											<!--span id="currentButton"></span-->
											<span class="hidden">|</span>
											<a class="edit-button button" href="<?php echo $blogURL;?>/owner/skin/edit"><span class="text"><?php echo _t('편집하기');?></span></a>
										</div>
<?php
}
?>
									</div>
								</div>
							</div>
						</div>
						
						<div id="currentSkinLoading" class="system-message" style="display: none;">
							<?php echo _t('로딩 중..');?>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-skin-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('사용가능한 스킨들의 목록입니다');?></span></h2>
							
							<form id="skin-search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/skin">
								<h2><?php echo _t('검색');?></h2>
								<div class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') {  document.getElementById('skin-search-form').submit();return false; }" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('skin-search-form').submit();return false;" />
								</div>
							</form>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('블로그에 적용하기 원하시는 스킨의 적용 버튼을 누르면 스킨이 블로그에 반영됩니다.');?></p>
							</div>
							
							<div class="data-inbox">
<?php
$count = 0;
for ($i = 0; $i < count($skins); $i++) {
	$skin = $skins[$i];
?>
								<div class="section">
									<div id="preview_<?php echo $skin['name'];?>" class="preview">
<?php
	if ($skin['preview'] == '') {
?>
										<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/noPreview.gif" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
	} else {
?>
										<img src="<?php echo $skin['preview'];?>" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
	}
?>
									</div>
									<div class="information">
										<div id="info_<?php echo $skin['name'];?>">
											<table cellspacing="0" cellpadding="0">
<?php
	if (isset($skin['skinName'])) {
		writeValue('<span class="skin-name">' . $skin['skinName'] . '</span> <span class="version">ver.' . $skin['version']. '</span>', _t('제목'), "title");
		writeValue($skin['license'], _t('저작권'), "license");
		writeValue($skin['maker'], _t('만든이'), "maker");
		writeValue($skin['homepage'], _t('홈페이지'), "homepage");
		writeValue($skin['email'], _t('e-mail'), "email");
		writeValue($skin['description'], _t('설명'), "explain");
	} else {
		writeValue($skin['name'], _t('제목'));
	}
?>
											</table>
										</div>
										<div id="button_<?php echo $skin['name'];?>" class="button-box">
											<a class="preview-button button" href="<?php echo $blogURL;?>/owner/skin/preview/?skin=<?php echo $skin['name'];?>" onclick="window.open(this.href, &quot;<?php echo $skin['name'];?>&quot;,&quot;location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0&quot;); return false;"><span><?php echo _t('미리보기');?></span></a>
											<span class="hidden">|</span>
											<a class="apply-button button" href="<?php echo $blogURL;?>/owner/skin/change/?skinName=<?php echo urlencode($skin['name']);?>" onclick="selectSkin('<?php echo $skin['name'];?>'); return false;"><span><?php echo _t('적용');?></span></a>
										</div>
									</div>
								</div>
<?php
}
?>
							</div>
							<hr class="hidden" />
						</div>
						<div id="part-skin-more" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('스킨을 구하려면');?></span></h2>
							
<?php
$linkString = '<a href="http://www.textcube.org/theme" onclick="window.open(this.href); return false;" title="' . _t('텍스트큐브 홈페이지의 스킨 업로드 게시판으로 연결합니다.') . '">' . _t('스킨 업로드 게시판'). '</a>';
$tempString = _f('텍스트큐브 홈페이지의 %1을 방문하시면 다양한 스킨을 다운로드 하실 수 있습니다. 일반적으로 스킨 파일을 텍스트큐브의 skin 디렉토리로 업로드하면 설치가 완료됩니다. 업로드가 완료된 스킨은 이 메뉴에서 적용 버튼을 눌러 사용하실 수 있습니다.', $linkString);
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo $tempString;?></p>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
