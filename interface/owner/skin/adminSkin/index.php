<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

if(isset($_POST['search'])) {
	$search = $_POST['search'];
} else $search = null;

// get the list type.
$listType = Setting::getBlogSetting('skinViewType', 'iconview',true);

$currentAdminSkin = Setting::getBlogSetting("adminSkin", "canon", true);

$skins = array();

function writeValue($value, $label, $className) {
?>
										<dl class="<?php echo $className;?>-line">
											<dt class="name"><?php echo $label;?></dt>
											<dd class="explain"><?php echo nl2br(addLinkSense($value, ' onclick="window.open(this.href); return false;"'));?></dd>
										</dl>
<?php
}

foreach(new DirectoryIterator(ROOT.'/skin/admin/') as $skinFile) {

	if(!$skinFile->isDir()) continue;
	if(strpos($skinFile->getFilename(),'.') === 0) continue;
	if($skinFile->getFilename() == 'mobile') continue;
	if (!file_exists($skinFile->getPathname()."/index.xml")) continue;
	
	$skin = array();

	$preview = "";
	if (file_exists($skinFile->getPathname()."/preview.jpg"))
		$preview = "{$service['path']}/skin/admin/".$skinFile->getFilename()."/preview.jpg";
	if (file_exists($skinFile->getPathname()."/preview.gif"))
		$preview = "{$service['path']}/skin/admin/".$skinFile->getFilename()."/preview.gif";
	
	if (file_exists($skinFile->getPathname()."/index.xml")) {
		$xml = file_get_contents($skinFile->getPathname()."/index.xml");
		$xmls = new XMLStruct();
		$xmls->open($xml, $service['encoding']);
		$skin['skinName'] = $xmls->getValue('/adminSkin/information/name');
		$skin['version']  = $xmls->getValue('/adminSkin/information/version');
		$skin['license']  = $xmls->getValue('/adminSkin/information/license');
		$skin['maker']    = $xmls->getValue('/adminSkin/author/name');
		$skin['homepage'] = $xmls->getValue('/adminSkin/author/homepage');
		$skin['email']    = $xmls->getValue('/adminSkin/author/email');
		$skin['description'] = $xmls->getValue('/adminSkin/information/description');
	}

	if(!empty($search) && 
		(stristr($skin['skinName'],$search) === false) && 
		(stristr($skin['maker'],$search) === false) &&
		(stristr($skin['homepage'],$search) === false) &&
		(stristr($skin['email'],$search) === false) &&
		(stristr($skin['description'],$search) === false)) continue; // Search.

	$skin['name'] = $skinFile->getFilename();
	$skin['path'] = $skinFile->getPathname();
	$skin['preview'] = $preview;

	array_push($skins, $skin);
}

?>
						<script type="text/javascript">
							//<![CDATA[
								function selectSkin(name) {
									try {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/adminSkin/set/");
										request.onSuccess = function() {
											isSkinModified = false;
											PM.showMessage("<?php echo _t('성공적으로 변경했습니다.');?>", "center", "bottom");
											document.getElementById('currentPreview').innerHTML = document.getElementById('preview_'+name).innerHTML;
											document.getElementById('currentInfo').innerHTML = document.getElementById('info_'+name).innerHTML;
											if(confirm("<?php echo _t('변경한 관리 패널 스킨으로 현재 페이지를 다시 불러오시겠습니까?');?>")) {
												window.location.reload();
											} else {
												return;
											}
										}
										request.onError = function() {
											msg = this.getText("/response/msg");
											if (this.getText("/response/msg") == null)
												msg = "<?php echo _t('올바른 스킨 디렉토리명이 아닙니다.\n디렉토리명에는 알파벳, 숫자, 언더바(_), 공백문자, 대쉬(-)만 사용하실 수 있습니다.');?>";
											alert(msg);
										}
										request.send("adminSkin=" + encodeURIComponent(name));
									} catch(e) {
										alert(e.message);
									}
								}
								function changeList(obj) {	
									if(document.getElementById('list-view').checked == true) {
										viewtype = 'listview';
									} else {
										viewtype = 'iconview';
									}
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/skin/saveScope");

									request.onSuccess = function() {
										document.getElementById('search-form').submit();
									}
									
									request.onError = function() {
										alert("<?php echo _t('선택하신 조건을 적용할 수 없었습니다.');?>");
									}
									
									request.send("viewtype=" + viewtype);
								}
							//]]>
						</script>

						<div id="part-skin-current" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('현재 사용중인 스킨');?></span></h2>
							
							<div class="data-inbox">
								<div id="currentSkin" class="section">
									<a id="currentSkinAnchor"></a>
									<div id="currentPreview" class="preview">
<?php
if (file_exists(ROOT."/skin/admin/".$currentAdminSkin."/preview.jpg")) {
?>
										<img src="<?php echo $service['path'];?>/skin/admin/<?php echo $currentAdminSkin;?>/preview.jpg" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
<?php
} else if (file_exists(ROOT."/skin/admin/".$currentAdminSkin."/preview.gif")) {
?>
										<img src="<?php echo $service['path'];?>/skin/admin/<?php echo $currentAdminSkin;?>/preview.gif" width="150" height="150" alt="<?php echo _t('스킨 미리보기');?>" />
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
if (file_exists(ROOT . "/skin/admin/{$currentAdminSkin}/index.xml")) {
?>
										<div id="currentInfo">
<?php
	$xml = file_get_contents(ROOT . "/skin/admin/{$currentAdminSkin}/index.xml");
	$xmls = new XMLStruct();
	$xmls->open($xml, $service['encoding']);
	writeValue('<span class="skin-name">' . $xmls->getValue('/adminSkin/information/name') . '</span> <span class="version">ver.' . $xmls->getValue('/adminSkin/information/version') . '</span>', _t('제목'), "title");
	writeValue($xmls->getValue('/adminSkin/information/license'), _t('저작권'), "license");
	writeValue($xmls->getValue('/adminSkin/author/name'), _t('만든이'), "maker");
	writeValue($xmls->getValue('/adminSkin/author/homepage'), _t('홈페이지'), "homepage");
	writeValue($xmls->getValue('/adminSkin/author/email'), _t('e-mail'), "email");
	writeValue($xmls->getValue('/adminSkin/information/description'), _t('설명'), "explain");
?>
										</div>
<?php
} else {
?>
										<div id="currentInfo">
											<div id="customizedTable">
												<?php echo _t('선택하신 스킨이 존재하지 않습니다. 다른 스킨을 선택해 주시기 바랍니다.').CRLF;?>
											</div>
										</div>
<?php
}
?>
									</div>
								</div>
							</div>
						</div>
						
						<div id="currentSkinLoading" class="system-message" style="display: none;">
							<?php echo _t('불러오는 중..');?>
						</div>

						<hr class="hidden" />
						
						<div id="part-skin-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('사용 가능한 스킨 목록');?></span></h2>
							<form id="search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/skin/adminSkin">
								
								<h2><?php echo _t('검색');?></h2>
								<div id="search-box" class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') {  document.getElementById('search-form').submit();return false; }" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').submit();return false;" />
								</div>
							</form>
							<form id="skin-search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/skin">
								<dl id="viewmode-box" class="line">
									<dt class="hidden"><?php echo _t('출력 설정');?></dt>
									<dd id="viewmode-line-align">
										<input type="radio" class="radio" id="list-view" name="viewType" value="listview" onclick="changeList(this);return false;"<?php echo $listType == 'listview' ? ' checked="checked"' : '';?> /><label for="list-view"><?php echo _t('리스트 보기');?></label>
										<input type="radio" class="radio" id="icon-view" name="viewType" value="iconview" onclick="changeList(this);return false;"<?php echo $listType == 'iconview' ? ' checked="checked"' : '';?> /><label for="icon-view"><?php echo _t('아이콘 보기');?></label>
									</dd>
								</dl>								
							</form>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('적용하기 원하는 스킨의 적용 버튼을 누르면 스킨이 현재 관리 패널에 반영됩니다.');?></p>
							</div>
							
							<div id="<?php echo $listType;?>-box" class="data-inbox">
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
<?php
	if (isset($skin['skinName'])) {
		writeValue('<span class="skin-name">' . $skin['skinName'] . '</span> <span class="version">v.' . $skin['version']. '</span>', _t('제목'), "title");
		writeValue($skin['license'], _t('저작권'), "license");
		writeValue($skin['maker'], _t('만든이'), "maker");
		writeValue($skin['homepage'], _t('홈페이지'), "homepage");
		writeValue($skin['email'], _t('e-mail'), "email");
		writeValue($skin['description'], _t('설명'), "explain");
	} else {
		writeValue($skin['name'], _t('제목'));
	}
?>
										</div>
										<div id="button_<?php echo $skin['name'];?>" class="button-box">
											<a class="apply-button button" href="<?php echo $blogURL;?>/owner/skin/adminSkin/set/?adminSkin=<?php echo urlencode($skin['name']);?>" onclick="selectSkin('<?php echo $skin['name'];?>'); return false;"><span><?php echo _t('적용');?></span></a>
										</div>
									</div>
								</div>
<?php
	if((($i+1) % 4) == 0) echo CRLF.TAB.TAB.TAB.TAB.TAB.TAB.'<hr class="hidden list-divider" />'.CRLF;
}
?>
							</div>
							<hr class="hidden clear" />
						</div>
						<hr class="hidden" />

						<div id="part-skin-more" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('스킨을 구하려면');?></span></h2>
							
<?php
$linkString = '<a href="http://www.textcube.org/theme" onclick="window.open(this.href); return false;" title="' . _t('텍스트큐브 홈페이지의 스킨 업로드 게시판으로 연결합니다.') . '">' . _t('스킨 업로드 게시판'). '</a>';
$tempString = _t('관리 패널 스킨은 로그인한 후 보여지는 패널의 디자인을 다양하게 변경합니다.').' '._t('관리 패널 스킨을 추가하기 위해서는 관리 패널 스킨을 내려받아 /skin/admin 디렉토리에 설치하시면 됩니다.');
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo $tempString;?></p>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
