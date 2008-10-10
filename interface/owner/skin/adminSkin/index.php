<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
require ROOT . '/library/piece/owner/header.php';

?>
						<div id="part-skin-admin" class="part">
							<h2 class="caption"><span class="main-text"><?php echo setDetailPanel('admin_skin_setting','link',_t('관리 패널의 스킨을 설정합니다'));?></span></h2>
							<form id="admin-skin-form" class="data-inbox" method="post" action="<?php echo parseURL($blogURL.'/owner/skin/adminSkin/set');?>">
								<div class="main-explain-box">
									<p class="explain"><?php echo _t('관리 패널 스킨은 로그인한 후 보여지는 패널의 디자인을 다양하게 변경합니다. 관리 패널 스킨을 추가하기 위해서는 관리 패널 스킨을 내려받아 /style/admin 디렉토리에 설치하시면 됩니다.');?></p>
								</div>
								<fieldset class="container">
									<legend><?php echo _t('관리 패널의 스킨을 설정합니다');?></legend>

									<dl id="admin-skin-line" class="line">
										<dt><span class="label"><?php echo _t('패널 스킨');?></span></dt>
										<dd>
											<select id="adminSkin" name="adminSkin">
<?php
$currentAdminSkin = getBlogSetting("adminSkin", "whitedream");
$dir = dir(ROOT . '/style/admin/');
while ($tempAdminSkin = $dir->read()) {
	if (!preg_match('/^[a-zA-Z0-9 _-]+$/', $tempAdminSkin))
		continue;
	if (!is_dir(ROOT . '/style/admin/' . $tempAdminSkin))
		continue;
	if (!file_exists(ROOT . "/style/admin/$tempAdminSkin/index.xml"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/style/admin/$tempAdminSkin/index.xml"))) {
		continue;
	} else {
		$skinDir = trim($tempAdminSkin);
		$skinName = htmlspecialchars($xmls->getValue('/adminSkin/information/name[lang()]'));
?>
												<option value="<?php echo $skinDir;?>"<?php echo $currentAdminSkin==$skinDir ?' selected="selected"':'';?>><?php echo $skinName;?></option>
<?php
	}
}
?>
											</select>
										</dd>
										<dd>
											<p><label for="adminSkin"><?php echo _t('팀블로그의 관리 패널 스킨을 변경할 경우 다른 팀원들의 관리 패널 디자인도 함께 바뀝니다.');?></label></p>
										</dd>
									</dl>
								</fieldset>
								<div class="button-box">
									<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" />
								</div>
							</form>
						</div>
<?php 
if (isset($_GET['message'])) {
	$msg = escapeJSInCData($_GET['message']);
?>
	<script type="text/javascript">
		//<![CDATA[
			window.onload = function() { PM.showMessage("<?php echo $msg;?>", "center", "bottom"); }
		//]]>
	</script>
<?php
}

require ROOT . '/library/piece/owner/footer.php';
?>
