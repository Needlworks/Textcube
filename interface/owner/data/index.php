<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$backup = null;
if (file_exists(ROOT . "/cache/backup/$blogid.xml.gz"))
	$backup = filemtime(ROOT . "/cache/backup/$blogid.xml.gz");
else if (file_exists(ROOT . "/cache/backup/$blogid.xml"))
	$backup = filemtime(ROOT . "/cache/backup/$blogid.xml");
require ROOT . '/interface/common/owner/header.php';

if (isset($checkFunction)) unset($checkFunction);

?>
						<script type="text/javascript">
							//<![CDATA[
								function checkForceTextcubeVersion() {
									if (confirm("<?php echo _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
									else
										alert("<?php echo _t('점검 없이 이 기능을 사용할 수 없습니다.');?>");
								}
							//]]>
						</script>
<?php

if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
							//]]>
						</script>
<?php
	$checkFunction = 'checkForceTextcubeVersion();';
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION) {
?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
							//]]>
						</script>
<?php
	$checkFunction = 'checkForceTextcubeVersion();';
}

function forceCheckBlog($passFunction)
{
	global $checkFunction;
	
	if (!isset($checkFunction))
		return $passFunction;
	return $checkFunction;
}

if (false) forceCheckBlog('');

?>
						<script type="text/javascript">
							//<![CDATA[
								var dialog = null;
								
								function showDialog($name) {
									if (dialog)
										dialog.style.display = "none";
									dialog = document.getElementById($name + "Dialog");
									PM.showPanel(dialog);
								}
								
								function hideDialog() {
									if (dialog) {
										dialog.style.display = "none";
										dialog = null;
									}
								}
								
								function correctData() {
									document.getElementById("correctingIndicator").style.width = "0%";
									document.getElementById("correctingDataDialogTitle").innerHTML = '<?php echo _t('데이터를 교정하고 있습니다. 잠시만 기다려 주십시오.');?>';
									PM.showPanel("correctingDataDialog");
									document.getElementById("dataCorrector").submit();
								}

								function optimizeData() {
									document.getElementById("optimizingIndicator").style.width = "0%";
									document.getElementById("optimizingDataDialogTitle").innerHTML = '<?php echo _t('데이터베이스를 최적화하고 있습니다. 잠시만 기다려 주십시오.');?>';
									PM.showPanel("optimizingDataDialog");
									document.getElementById("dataOptimizer").submit();
								}
								
								function backupData() {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/data/backup?includeFileContents=" + document.getElementById("includeFileContents-yes").checked);
									PM.addRequest(request, "<?php echo _t('백업을 저장하고 있습니다.');?>");
									request.onSuccess = function () {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('백업이 저장되었습니다.');?>", "center", "bottom");
									}
									request.onError = function () {
										PM.removeRequest(this);
										alert("<?php echo _t('백업을 저장하지 못했습니다');?>");
									}
									request.send();
									hideDialog();
								}
								
								function exportData() {
									window.location.href = "<?php echo $blogURL;?>/owner/data/export?includeFileContents=" + document.getElementById("includeFileContents-yes").checked;
									hideDialog();
								}
								
								function downloadBackup() {
									window.location.href = "<?php echo $blogURL;?>/owner/data/download";
									hideDialog();
								}
								
								function importData() {
									if(confirm("<?php echo _t('일반적으로 복원시 기존의 데이터를 덮어씁니다. \n덮어쓰지 않고 백업파일을 추가하기 위해서는 백업파일을 열어 migrational=false 를 migrational=true로 바꾸셔야 합니다. 확인하신 후에 진행하시기 바랍니다.\n계속 하시겠습니까?');?>")!=1) return null;
									
									var dataImporter = document.getElementById("dataImporter");
									if (document.getElementById("importFromUploaded").checked) {
										if (!dataImporter.elements["backupPath"].value) {
											alert("<?php echo _t('백업파일을 선택하십시오.');?>");
											dataImporter.elements["backupPath"].focus();
											return false;
										}
										document.getElementById("progressText").innerHTML = "<?php echo _t('백업파일을 올리고 있습니다.');?>";
									} else if (document.getElementById("importFromWeb").checked) {
										if (!dataImporter.elements["backupURL"].value) {
											alert("<?php echo _t('백업파일 URL을 입력하십시오.');?>");
											dataImporter.elements["backupURL"].focus();
											return false;
										}
										document.getElementById("progressText").innerHTML = "<?php echo _t('백업파일을 가져오고 있습니다.');?>";
									} else {
										document.getElementById("progressText").innerHTML = "";
									}
									hideDialog();
									document.getElementById("progressIndicator").style.width = "0%";
									document.getElementById("progressDialogTitle").innerHTML = '<?php echo _t('데이터를 복원하고 있습니다. 잠시만 기다려 주십시오.');?>';
									document.getElementById("progressText").innerHTML = '<?php echo _t('백업파일을 올리고 있습니다.');?>';
									PM.showPanel("progressDialog");
									dataImporter.submit();
								}
								
								function removeData() {
									var removeAttachments = document.getElementById("removeAttachments-yes");
									var confirmativePassword = document.getElementById("confirmativePassword");
									if (confirmativePassword.value.length < 6) {
										alert("<?php echo _t('비밀번호를 입력해 주십시오.');?>");
										confirmativePassword.focus();
										return false;
									}
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/data/remove");
									PM.addRequest(request, "<?php echo _t('데이터를 삭제하고 있습니다.');?>");
									request.onSuccess = function () {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('데이터가 삭제되었습니다.');?>", "center", "bottom");
										confirmativePassword.value = '';
									}
									request.onError = function () {
										PM.removeRequest(this);
										alert("<?php echo _t('비밀번호가 일치하지 않습니다.');?>");
									}
									request.send("removeAttachments=" + (removeAttachments.checked ? "1" : "0") + "&confirmativePassword=" + encodeURIComponent(confirmativePassword.value));
									hideDialog();
								}
								
								//window.addEventListener("load", execLoadFunction, false);
								
								function execLoadFunction() {
									if (STD.isIE6) {
										var pluginIcons = document.getElementsByTagName('img');
										
										for (var i=0; i<pluginIcons.length; ++i) {
											var temp = pluginIcons[i].src;
											pluginIcons[i].setAttribute('src', "<?php echo $service['path'];?>/resources/image/spacer.gif");
											pluginIcons[i].style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' + temp + '", sizingMethod="scale")';
										}
									}
								}
							//]]>
						</script>
						
						<div id="part-data-correct" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('데이터를 교정합니다');?></span></h2>
							
							<div class="data-inbox main-explain-box">
								<div class="image" onclick="correctData(); return false">
									<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/dbCorrect.png" alt="<?php echo _t('데이터 교정 이미지');?>" />
								</div>
								<p class="explain">
									<?php echo _t('비정상적인 데이터를 교정합니다.<br />동적인 캐쉬 데이터는 재계산하여 저장합니다.');?>
								</p>
							</div>
							
							<form id="dataCorrector" method="get" action="<?php echo $blogURL;?>/owner/data/correct" target="blackhole"></form>
							
							<div id="correctingDataDialog" class="system-dialog" style="position: absolute; display: none; z-index: 110;">
								<h3 id="correctingDataDialogTitle"></h3>
								<div class="message-sub">
									<span id="correctingText"></span>
									<span id="correctingTextSub"></span>
								</div>
								<div id="correctingIndicator" class="progressBar" style="width: 0%; height: 18px; margin-top: 5px; background-color: #66DDFF;"></div>
							</div>
						</div>
						
						<hr class="hidden" />
<?php
if(Acl::check("group.creators")) {
?>
						<div id="part-data-optimize" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('데이터베이스를 최적화합니다');?></span></h2>
							
							<div class="data-inbox main-explain-box">
								<div class="image" onclick="optimizeData()">
									<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/dbOptimize.png" alt="<?php echo _t('데이터베이스 최적화 이미지');?>" />
								</div>
								<p class="explain">
									<?php echo _t('잦은 입출력으로 비효율적이 된 데이터베이스를 최적화 합니다.');?>
								</p>
							</div>
							
							<form id="dataOptimizer" method="get" action="<?php echo $blogURL;?>/owner/data/optimize" target="blackhole"></form>
							
							<div id="optimizingDataDialog" class="system-dialog" style="position: absolute; display: none; z-index: 110;">
								<h3 id="optimizingDataDialogTitle"></h3>
								<div class="message-sub">
									<span id="optimizingText"></span>
									<span id="optimizingTextSub"></span>
								</div>
								<div id="optimizingIndicator" class="progressBar" style="width: 0%; height: 18px; margin-top: 5px; background-color: #66DDFF;"></div>
							</div>
						</div>
						
						<hr class="hidden" />
<?php
}
?>
						<div id="part-data-backup" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('데이터를 백업합니다');?></span></h2>
							
							<div class="data-inbox main-explain-box">
								<div class="image" onclick="showDialog('DBExport')">
									<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/dbExport.png" alt="<?php echo _t('데이터 백업 이미지');?>" />
								</div>
								<p class="explain">
									<?php echo _t('현재의 모든 데이터를 TTXML형태의 백업파일로 보관합니다.<br />첨부파일을 포함시킬 수 있으며, 복원할 경우 자동으로 첨부파일이 처리됩니다.<br />백업파일은 서버에 저장하거나 다운받으실 수 있습니다.');?>
								</p>
							</div>
<?php
	if(file_exists(ROOT . "/cache/backup/$blogid.xml")) {
		$fileTime = Timestamp::format5(filectime(ROOT . "/cache/backup/$blogid.xml"));

?>
							<div class="notification-box">
								<p><?php echo _f('서버에 %1에 백업한 파일이 존재합니다.',$fileTime);?></p>
<?php
		$apikey = Setting::getUserSettingGlobal('APIKey',null,getUserId());
		if($apikey!=null) {
?>
								<p>
									 <?php echo _t('복원을 위하여 외부에서 백업 파일에 접근하려면 아래의 주소를 이용하세요.');?></p>
								<p class="url"><?php echo $defaultURL."/ttxml?loginid=".User::getEmail(getUserId())."&key=".$apikey;?></p>
<?php
		} else {
?>
								<p>
								 	<?php echo _t('설정-개인 정보에서 API key를 설정하시면 온라인 복원을 위한 주소를 만들 수 있습니다.');?>
								</p>
<?php
		}
?>
							</div>
<?php
	}
?>
							<form id="DBExportDialog" class="dialog" method="get" action="<?php echo $blogURL;?>/owner/data/backup" style="position: absolute; display: none; z-index: 100;">
								<h3><?php echo _t('데이터 백업을 시작합니다');?></h3>
								
								<div class="message-body">
									<p class="message">
										<span><?php echo _t('첨부파일을 포함하시겠습니까?');?></span>
									</p>
									<div class="selection">
										<div class="select-yes" title="<?php echo _t('첨부 파일이 포함된 백업파일을 사용하여 복원할 경우, 첨부 파일의 내용은 백업파일의 내용으로 다시 작성됩니다.');?>"><input type="radio" id="includeFileContents-yes" class="radio" name="includeFileContents" value="1" /><label for="includeFileContents-yes"><span class="text"><?php echo _t('첨부파일을 포함합니다.');?></span></label></div>
										<div class="select-no" title="<?php echo _t('첨부 파일이 포함되지 않는 백업파일을 사용하여 복원하여도 기존 첨부 파일을 삭제하거나 훼손시키지 않습니다.');?>"><input type="radio" id="includeFileContents-no" class="radio" name="includeFileContents" value="0" checked="checked" /><label for="includeFileContents-no"><span class="text"><?php echo _t('첨부파일을 포함하지 않습니다.');?></span></label></div>
									</div>
								</div>
								<div class="button-box">
<?php
if(defined('__TEXTCUBE_NO_FANCY_URL__')) {
?>
									<a class="server-button button" title="<?php echo _t('mod_rewrite를 사용하지 않는 경우 보안상 문제로 서버에 백업하기 기능을 지원하지 않습니다.');?>"><span class="text"><?php echo _t('서버에 저장');?></span></a>
									<span class="hidden">|</span>
<?php
} else {
?>
									<a class="server-button button" href="#void" onclick="backupData()" title="<?php echo _t('서버에 백업파일을 저장하여 복원에 사용할 수 있습니다.');?>"><span class="text"><?php echo _t('서버에 저장');?></span></a>
									<span class="hidden">|</span>
<?php
}
?>
									<a class="local-button button" href="#void" onclick="exportData()" title="<?php echo _t('현재 상태의 데이터를 백업하여 다운로드합니다. 서버에 저장된 백업파일은 갱신되지 않습니다.');?>"><span class="text"><?php echo _t('다운로드');?></span></a>
									<span class="hidden">|</span>
									<a class="close-button button" href="#void" onclick="hideDialog()" title="<?php echo _t('명령을 취소하고 이 대화상자를 닫습니다.');?>"><span class="text"><?php echo _t('취소하기');?></span></a>
	 							</div>
 							</form>
 						</div>
 						
 						<hr class="hidden" />
 						
						<div id="part-data-restore" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('데이터를 복원합니다');?></span></h2>
							
							<div class="data-inbox main-explain-box">
								<div class="image" onclick="<?php echo forceCheckBlog("showDialog('DBImport');"); ?> return false">
									<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/dbImport.png" alt="<?php echo _t('데이터 복원 이미지');?>" />
								</div>
								<p class="explain">
									<?php echo _t('백업파일을 읽어서 데이터를 복원합니다.<br />백업파일에 첨부파일이 포함되어 있으면 첨부파일도 자동으로 복원됩니다.<br />이전 버전으로부터의 데이터도 복원을 통해 가져올 수 있습니다.');?>
								</p>
							</div>
							<div id="DBImportDialog" class="dialog" style="position: absolute; display: none; z-index: 100;">
								<form id="dataImporter" method="post" action="<?php echo $blogURL;?>/owner/data/import" enctype="multipart/form-data" target="blackhole">
									<h3><?php echo _t('데이터 복원을 시작합니다');?></h3>
									
									<div class="message-body">
										<div class="explain">
											<?php echo _f('이 계정의 업로드 허용 용량은 <em>%1</em> 바이트로 백업파일의 크기가 이를 초과하는 경우 <acronym title="File Transfer Protocol">FTP</acronym> 등으로 원하시는 사이트에 업로드하신 후 이 파일의 웹 주소를 입력해서 진행하십시오. 이 경우, 보안을 위해 복원이 끝나면 반드시 그 백업파일을 웹 상에서 지우실 것을 권장합니다.', (Misc::getNumericValue(ini_get('post_max_size')) < Misc::getNumericValue(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize')));?>
										</div>
<?php
if ($backup) {
?>
										<p class="message">
											<?php echo _f('서버에 <em>%1</em>에 저장된 백업파일이 있습니다.', Timestamp::format5($backup));?>
										</p>
<?php
}
?>
										<div class="selection">
<?php
if ($backup) {
?>
											<div id="select-server" title="<?php echo _t('데이터 백업 기능을 통해 서버에 저장해 두었던 기존 파일을 이용해 데이터베이스를 복원합니다. 데이터 파일에 대해서는 위의 정보를 참고하십시오.');?>"><input type="radio" id="importFromServer" class="radio" name="importFrom" value="server" checked="checked" onclick="if (this.checked) {hideLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = true;}" /><label for="importFromServer"><?php echo _t('서버에 저장된 백업파일.');?></label></div>
<?php
}
?>
											<div id="select-upload" title="<?php echo _t('백업파일을 자신의 하드디스크로부터 직접 선택하여 데이터베이스를 복원합니다. 백업파일의 용량이 업로드 허용용량을 초과하지 않는지 주의하십시오.');?>"><input type="radio" id="importFromUploaded" class="radio" name="importFrom" value="uploaded"<?php echo ($backup ? '' : ' checked="checked"');?> onclick="if (this.checked) {showLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = false; document.getElementById('backupURL').disabled = true;}" /><label for="importFromUploaded"><?php echo _t('백업파일 올리기.');?></label></div>
											<div id="select-web" title="<?php echo _t('백업파일의 크기가 업로드 허용 용량을 초과하는 경우, FTP 등을 이용하여 계정의 홈페이지에 직접 업로드한 후 이 파일의 위치를 지정하여 데이터베이스를 복원할 수 있습니다.');?>"><input type="radio" id="importFromWeb" class="radio" name="importFrom" value="web" onclick="if (this.checked) {hideLayer('uploadBackup'); showLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = false;}" /><label for="importFromWeb"><?php echo _t('웹에서 백업파일 가져오기.');?></label></div>
											<div id="select-correct" title="<?php echo _t('백업파일에 비정상적인 글자가 포함된 경우 복원에 실패할 수 있습니다. 비정상적인 글자를 교정하여 복원이 가능하도록 합니다. 이를 사용할 경우 복원에 많은 시간이 소요될 수 있습니다.');?>"><input type="checkbox" id="correctData" class="checkbox" name="correctData" value="on" /><label for="correctData"><?php echo _t('백업파일에 포함된 비정상적인 글자를 교정합니다.');?></label></div>
										</div>
										<div id="uploadBackup"<?php echo ($backup ? 'class="hidden"' : NULL);?>>
											<label for="backupPath"><?php echo _t('백업파일 경로');?></label><span class="divider"> : </span><input type="file" id="backupPath" class="input-file" name="backupPath" <?php echo ($backup ? 'disabled="disabled"' : '');?> />
										</div>
										<div id="remoteBackup" class="hidden">
											<label for="backupURL"><?php echo _t('백업파일 <acronym title="Uniform Resource Locator">URL</acronym>');?></label><span class="divider"> : </span><input type="text" id="backupURL" class="input-text" name="backupURL" value="http://" disabled="disabled" onkeydown="if (event.keyCode == 13) { importData(); return false; }" />
										</div>
									</div>
									<div class="button-box">
										<a class="restore-button button" href="#void" onclick="importData()"><span class="text"><?php echo _t('복원하기');?></span></a>
										<span class="hidden">|</span>
										<a class="close-button button" href="#void" onclick="hideDialog()" title="<?php echo _t('명령을 취소하고 이 대화상자를 닫습니다.');?>"><span class="text"><?php echo _t('취소하기');?></span></a>
 									</div>
 								</form>
							</div>
							
							<div id="progressDialog" class="system-dialog" style="position: absolute; display: none; z-index: 100;">
								<h3 id="progressDialogTitle"></h3>
								<div class="message-sub">
									<p id="progressText"></p>
									<p id="progressTextSub"></p>
								</div>
								<div id="progressIndicator" class="progressBar" style="width: 10%; height: 18px; margin-top: 5px; background-color:#66DDFF;"></div>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-data-remove" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('데이터를 삭제합니다');?></span></h2>
							
							<div class="data-inbox main-explain-box">
								<div class="image" onclick="showDialog('DBRemove')">
									<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/dbClear.png" alt="<?php echo _t('데이터 삭제 이미지');?>" />
								</div>
								<p class="explain">
									<?php echo _t('텍스트큐브의 모든 데이터를 삭제합니다.<br />첨부파일의 삭제 여부를 선택하실 수 있습니다.<br />데이터의 복원은 백업파일을 통해서만 가능하므로 먼저 백업을 하시기 바랍니다.');?>
								</p>
							</div>
							
							<form id="DBRemoveDialog" class="dialog" method="get" action="<?php echo $blogURL;?>/owner/data/remove" style="position: absolute; display: none; z-index: 100;">
								<h3><?php echo _t('데이터 삭제를 시작합니다');?></h3>
								
								<div class="message-body">
<?php
if ($backup) {
?>
									<p class="explain">
										<?php echo _f('서버에 <em>%1</em>에 저장된 백업파일이 있습니다. 삭제후 복원에는 이 파일을 이용하실 수 있습니다.', Timestamp::format5($backup));?><br />
									</p>
<?php
}
?>
									<div class="message">
										<?php echo _t('첨부파일을 포함하여 삭제하시겠습니까?');?>
									</div>
									<div class="selection">
										<div class="select-yes"><input type="radio" id="removeAttachments-yes" class="radio" name="removeAttachments" value="1" /><label for="removeAttachments-yes"><?php echo _t('첨부파일을 포함합니다.');?></label></div>
										<div class="select-no"><input type="radio" id="removeAttachments-no" class="radio" name="removeAttachments" value="0" checked="checked" /><label for="removeAttachments-no"><?php echo _t('첨부파일을 포함하지 않습니다.');?></label></div>
									</div>
									<div id="admin-password">
										<label for="confirmativePassword"><?php echo _t('데이터를 삭제하시려면 관리자 비밀번호를 입력하십시오.');?></label>
										<input type="password" id="confirmativePassword" class="input-text" name="confirmativePassword" onkeydown="if (event.keyCode == 13) { removeData(); return false; }" />
									</div>
								</div>
								<div class="button-box">
									<a class="remove-button button" href="#void" onclick="removeData()"><span class="text"><?php echo _t('삭제하기');?></span></a>
									<span class="hidden">|</span>
									<a class="close-button button" href="#void" onclick="hideDialog()" title="<?php echo _t('명령을 취소하고 이 대화상자를 닫습니다.');?>"><span class="text"><?php echo _t('취소하기');?></span></a>
								</div>
							</form>
						</div>
						
			 			<iframe id="blackhole" name="blackhole" class="hidden"></iframe>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
