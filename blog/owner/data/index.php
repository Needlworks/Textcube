<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$backup = null;
if (file_exists(ROOT . "/cache/backup/$owner.xml.gz"))
	$backup = filemtime(ROOT . "/cache/backup/$owner.xml.gz");
else if (file_exists(ROOT . "/cache/backup/$owner.xml"))
	$backup = filemtime(ROOT . "/cache/backup/$owner.xml");
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu54.php';
?>
<script type="text/javascript">
//<![CDATA[
	var dialog = null
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
		var dataCorrector = document.getElementById("dataCorrector");
		document.getElementById("correctingIndicator").style.width = "0%";
		PM.showPanel("correctingDataDialog");
		dataCorrector.submit();
	}
	function backupData() {
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/data/backup?includeFileContents=" + document.getElementById("includeFileContents").checked);
		PM.addRequest(request, "<?=_t('백업을 저장하고 있습니다...')?>");
		request.onSuccess = function () {
			PM.removeRequest(this);
			PM.showMessage("<?=_t('백업이 저장되었습니다.')?>", "center", "bottom");
		}
		request.onError = function () {
			PM.removeRequest(this);
			alert("<?=_t('백업을 저장하지 못했습니다.')?>");
		}
		request.send();
		hideDialog();
	}
	function exportData() {
		window.location.href = "<?=$blogURL?>/owner/data/export?includeFileContents=" + document.getElementById("includeFileContents").checked;
		hideDialog();
	}
	function downloadBackup() {
		window.location.href = "<?=$blogURL?>/owner/data/download";
		hideDialog();
	}
	function importData() {
		var dataImporter = document.getElementById("dataImporter");
		if (document.getElementById("importFromUploaded").checked) {
			if (!dataImporter.elements["backupPath"].value) {
				alert("<?=_t('백업파일을 선택하십시오.')?>");
				dataImporter.elements["backupPath"].focus();
				return false;
			}
			document.getElementById("progressText").innerHTML = "<?=_t('백업파일을 올리고 있습니다')?>";
		} else if (document.getElementById("importFromWeb").checked) {
			if (!dataImporter.elements["backupURL"].value) {
				alert("<?=_t('백업파일 URL을 입력하십시오.')?>");
				dataImporter.elements["backupURL"].focus();
				return false;
			}
			document.getElementById("progressText").innerHTML = "<?=_t('백업파일을 가져오고 있습니다')?>";
		} else {
			document.getElementById("progressText").innerHTML = "";
		}
		hideDialog();
		document.getElementById("progressIndicator").style.width = "0%";
		PM.showPanel("progressDialog");
		dataImporter.submit();
	}
	function removeData() {
		var removeAttachments = document.getElementById("removeAttachments");
		var confirmativePassword = document.getElementById("confirmativePassword");
		if (confirmativePassword.value.length < 6) {
			alert("<?=_t('비밀번호를 입력하십시오.')?>");
			confirmativePassword.focus();
			return false;
		}
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/data/remove");
		PM.addRequest(request, "<?=_t('데이터를 삭제하고 있습니다...')?>");
		request.onSuccess = function () {
			PM.removeRequest(this);
			PM.showMessage("<?=_t('데이터가 삭제되었습니다.')?>", "center", "bottom");
		}
		request.onError = function () {
			PM.removeRequest(this);
			alert("<?=_t('비밀번호가 일치하지 않습니다.')?>");
		}
		request.send("removeAttachments=" + (removeAttachments.checked ? "1" : "0") + "&confirmativePassword=" + encodeURIComponent(confirmativePassword.value));
		hideDialog();
	}
//]]>
</script>   
<table cellspacing="0" width="100%">
  <tr>
    <td>
      <table cellspacing="0" style="width:100%; height:28px">
        <tr>
          <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
          <td style="padding:3px 0px 0px 4px"><?=_t('데이터 교정')?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
  <tr style="background-color:#EBF2F8">
    <td valign="top" style="padding:10px 5px 10px 5px">
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:5px;">
        <tr>
          <td style="width:100px; padding:10px;">
            <table style="width:100px; height:60px; border:1px solid #536576; padding:10px; background-color:#fff;">
              <tr>
                <td onclick="correctData()" style="cursor:pointer; text-align:center; background-color:#fff; font:10px verdana;"><img src="<?=$service['path']?>/style/image/dbCorrect.gif" style="margin-bottom:5px;" alt="" /><br />
                  <?=_t('CORRECT')?></td>
              </tr>
            </table>
          </td>
          <td style="line-height:18px; color:#536576; padding:10px;"><?=_t('비정상적인 데이터를 교정합니다.<br />동적인 캐쉬 데이터는 재계산하여 저장합니다.<br />')?></td>
        </tr>
      </table>
	  <form id="dataCorrector" name="dataCorrector" method="get" action="<?=$blogURL?>/owner/data/correct" target="_blackhole">
	  </form>
	  <div id="correctingDataDialog" style="width:550px; position:absolute; display:none; z-index:10;">
      <table style="width:550px; border:5px solid #BCD2E5; margin-left:10px; background-color:#fff;">
        <tr>
          <td style="padding:10px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="font-size:14px; font-weight:bold; color:#333; padding:0 0 10px 5px;"><?=_t('데이터를 교정하고 있습니다. 잠시만 기다려 주십시오...')?></td>
              </tr>
              <tr>
                <td style="padding:2px 5px 10px 5px; color:#666;">
                  <table style="width:100%; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;">
					    <div style="display:block; width:100%; height:18px; background-color:#eee;">
					      <div style="display:block; position:absolute; width:465px; text-align:center; padding-top:2px"><span id="correctingText"></span><span id="correctingTextSub"></span></div>
						  <div id="correctingIndicator" style="display:block; width:0%; height:18px; background-color:#66DDFF;"></div>
						</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
	  </div>
    </td>
  </tr>
</table>
<br />
<br />
<table cellspacing="0" width="100%">
  <tr>
    <td>
      <table cellspacing="0" style="width:100%; height:28px">
        <tr>
          <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
          <td style="padding:3px 0px 0px 4px"><?=_t('데이터 백업')?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
  <tr style="background-color:#EBF2F8">
    <td valign="top" style="padding:10px 5px 10px 5px">
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:5px;">
        <tr>
          <td style="width:100px; padding:10px;">
            <table style="width:100px; height:60px; border:1px solid #536576; padding:10px; background-color:#fff;">
              <tr>
                <td onclick="showDialog('DBExport')" style="cursor:pointer; text-align:center; background-color:#fff; font:10px verdana;"><img src="<?=$service['path']?>/style/image/dbExport.gif" style="margin-bottom:5px;" alt="" /><br />
                  <?=_t('EXPORT')?></td>
              </tr>
            </table>
          </td>
          <td style="line-height:18px; color:#536576; padding:10px;"><?=_t('현재의 모든 데이터를 백업파일로 보관합니다.<br />첨부파일을 포함시킬 수 있으며, 복원할 경우 자동으로 첨부파일이 처리됩니다.<br />백업파일은 서버에 저장하거나 다운받으실 수 있습니다.')?></td>
        </tr>
      </table>
	  <div id="DBExportDialog" style="width:550px; position:absolute; display:none; z-index:10;">
      <table style="width:550px; border:5px solid #BCD2E5; margin-left:10px; background-color:#fff;">
        <tr>
          <td style="padding:10px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="right"><img src="<?=$service['path']?>/style/image/buttonClose.gif" width="13" height="13" border="0" class="pointerCursor" onclick="hideDialog()" alt="<?=_t('닫기')?>" title="<?=_t('닫기')?>" /></td>
              </tr>
              <tr>
                <td style="font-size:14px; font-weight:bold; color:#333; padding:0 0 10px 5px;"><?=_t('데이터 백업을 시작합니다.')?></td>
              </tr>
              <tr>
                <td style="height:1px; padding:0; background:url(<?=$service['path']?>/style/image/lineDotted.gif);"></td>
              </tr>
              <tr>
                <td style="padding-top:10px; color:#333;">&nbsp; * <?=_t('첨부파일을 포함하시겠습니까?')?></td>
              </tr>
              <tr>
                <td style="padding:2px 5px 10px 5px; color:#666;">
                  <table style="width:100%; background-color:#eee; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;"><span style="padding:20px 5px 10px 5px; color:#666;">
                        <span title="<?=_t('첨부 파일이 포함된 백업파일을 사용하여 복원할 경우, 첨부 파일의 내용은 백업파일의 내용으로 다시 작성됩니다.')?>"><input id="includeFileContents" type="radio" name="includeFileContents" value="1" />
                        <?=_t('첨부파일을 포함합니다.')?></span>   &nbsp;  &nbsp;
                        <span title="<?=_t('첨부 파일이 포함되지 않는 백업파일을 사용하여 복원하여도 기존 첨부 파일을 삭제하거나 훼손시키지 않습니다.')?>"><input type="radio" name="includeFileContents" value="0" checked="checked" />
                        <?=_t('첨부파일을 포함하지 않습니다.')?></span></span></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" style="padding:10px 0px;">
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center" style="padding:10px;">
                        <table cellpadding="0" cellspacing="0" style="background-color:#A6C5F0; padding:0; margin:0; border:0;">
                          <tr>
                            <td><img src="<?=$service['path']?>/style/image/maskRoundLeft.gif" width="6" height="34" alt="" /></td>
                            <td onclick="backupData()" style="cursor:pointer; font-size:14px; color:#336; font-weight:bold; padding:0 10px; width:120px;" title="<?=_t('서버에 백업파일을 저장하여 복원에 사용할 수 있습니다.')?>"><span style="background:url(<?=$service['path']?>/style/image/maskDownloadArrow.gif) no-repeat 0 50%; padding-left:25px;"><?=_t('서버에 저장')?></span></td>
                            <td align="right"><img src="<?=$service['path']?>/style/image/maskRoundRight.gif" width="6" height="34" alt="" /></td>
                          </tr>
                        </table>
                      </td>
                      <td align="center" style="padding:10px;">
                        <table cellpadding="0" cellspacing="0" style="background-color:#A6C5F0; padding:0; margin:0; border:0;">
                          <tr>
                            <td><img src="<?=$service['path']?>/style/image/maskRoundLeft.gif" width="6" height="34" alt="" /></td>
                            <td onclick="exportData()" style="cursor:pointer; font-size:14px; color:#336; font-weight:bold; padding:0 10px; width:120px;" title="<?=_t('현재 상태의 데이터를 백업하여 다운로드합니다. 서버에 저장된 백업파일은 갱신되지 않습니다.')?>"><span style="background:url(<?=$service['path']?>/style/image/maskDownloadArrow.gif) no-repeat 0 50%; padding-left:25px;"><?=_t('다운로드')?></span></td>
                            <td align="right"><img src="<?=$service['path']?>/style/image/maskRoundRight.gif" width="6" height="34" alt="" /></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
	  </div>
    </td>
  </tr>
</table>
<br />
<br />
<table cellspacing="0" width="100%">
  <tr>
    <td>
      <table cellspacing="0" style="width:100%; height:28px">
        <tr>
          <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
          <td style="padding:3px 0px 0px 4px"><?=_t('데이터 복원')?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
  <tr style="background-color:#EBF2F8">
    <td valign="top" style="padding:10px 5px 20px 5px">
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:5px;">
        <tr>
          <td style="width:100px; padding:10px;">
            <table style="width:100px; height:60px; border:1px solid #536576; padding:10px; background-color:#fff;">
              <tr>
                <td onclick="showDialog('DBImport')" style="cursor:pointer; text-align:center; background-color:#fff; font:10px verdana;"><img src="<?=$service['path']?>/style/image/dbImport.gif" style="margin-bottom:5px;" alt="" /><br />
                  <?=_t('IMPORT')?></td>
              </tr>
            </table>
          </td>
          <td style="line-height:18px; color:#536576; padding:10px;"><?=_t('백업파일을 읽어서 데이터를 복원합니다.<br />백업파일에 첨부파일이 포함되어 있으면 첨부파일도 자동으로 복원됩니다.<br />마이그레이션 데이터도 복원을 통해 가져올 수 있습니다.')?></td>
        </tr>
      </table>
	  <div id="DBImportDialog" style="width:550px; position:absolute; display:none; z-index:10;">
	  <form id="dataImporter" name="dataImporter" method="post" action="<?=$blogURL?>/owner/data/import" enctype="multipart/form-data" target="_blackhole">
      <table style="width:550px; border:5px solid #BCD2E5; background-color:#fff;">
        <tr>
          <td style="padding:10px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="right"><img src="<?=$service['path']?>/style/image/buttonClose.gif" width="13" height="13" border="0" class="pointerCursor" onclick="hideDialog()" alt="<?=_t('닫기')?>" title="<?=_t('닫기')?>" /></td>
              </tr>
              <tr>
                <td style="font-size:14px; font-weight:bold; color:#333; padding:0 0 10px 5px;"><?=_t('데이터 복원을 시작합니다.')?></td>
              </tr>
              <tr>
                <td style="height:1px; padding:0; background:url(<?=$service['path']?>/style/image/lineDotted.gif);"></td>
              </tr>
              <tr>
                <td style="padding:10px 5px; color:#666;"><p><?=_f('이 계정의 업로드 허용 용량은 <strong style="color:red">%1</strong> 바이트로 백업파일이 이를 초과하는 경우<br />FTP등으로 아무 곳에나 업로드를 하신 후 웹 주소를 입력해서 진행하십시오.<br />이 경우 복원이 끝나면 반드시 그 백업파일을 웹 상에서 지우시기 바랍니다.', (getNumericValue(ini_get('post_max_size')) < getNumericValue(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize')))?></p>
                </td>
              </tr>
<?
if ($backup) {
?>
              <tr>
                <td style="padding:10px 5px; color:#333;">&nbsp; * <?=_f('서버에 <em>%1</em>경에 저장된 백업파일이 있습니다.', Timestamp::format5($backup))?></td>
              </tr>
<?
}
?>
              <tr>
                <td style="height:1px; padding:0; background:url(<?=$service['path']?>/style/image/lineDotted.gif);"></td>
              </tr>
              <tr>
                <td style="padding:20px 5px 10px 5px; color:#333;" title="<?=_t('백업파일에 비정상적인 글자가 포함된 경우 복원에 실패할 수 있습니다. 비정상적인 글자를 교정하여 복원이 가능하도록 합니다. 이를 사용할 경우 복원에 많은 시간이 소요될 수 있습니다.')?>"><input id="correctData" type="checkbox" name="correctData" value="on" /> <label for="correctData"><?=_t('백업파일에 포함된 비정상적인 글자를 교정합니다.')?></label></td>
              </tr>
              <tr>
                <td style="padding:5px 5px 10px 5px; color:#666;">
<?
if ($backup) {
?>
                  <span><input type="radio" name="importFrom" value="server" checked="checked" onclick="if (this.checked) {hideLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = true;}" /><?=_t('서버에 저장된 백업파일')?></span>
                  &nbsp;  &nbsp;
<?
}
?>
                  <span><input id="importFromUploaded" type="radio" name="importFrom" value="uploaded" <?=($backup ? '' : 'checked="checked"')?> onclick="if (this.checked) {showLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = false; document.getElementById('backupURL').disabled = true;}" /><?=_t('백업파일 올리기')?></span>
                  &nbsp;  &nbsp;
                  <span><input id="importFromWeb" type="radio" name="importFrom" value="web" onclick="if (this.checked) {hideLayer('uploadBackup'); showLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = false;}" /><?=_t('웹에서 백업파일 가져오기')?></span>
                </td>
              </tr>
              <tr>
                <td>
                  <table id="uploadBackup" style="display: <?=($backup ? 'none' : 'block')?>; width:100%; background-color:#eee; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;"><label for="backupPath"><?=_t('백업파일 경로')?></label> :
                        <input id="backupPath" type="file" name="backupPath" style="border:1px solid #999; background-color:#fff; width:384px;" <?=($backup ? 'disabled="disabled"' : '')?> />
                      </td>
                    </tr>
                  </table>
                  <table id="remoteBackup" style="display:none; width:100%; background-color:#eee; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;"><label for="backupURL"><?=_t('백업파일 URL')?></label> :
                        <input id="backupURL" type="text" name="backupURL" style="border:1px solid #999; background-color:#fff; width:380px;" value="http://" disabled="disabled" onkeydown="if (event.keyCode == 13) { importData(); return false; }" />
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" style="padding:10px 0;">
                  <table cellpadding="0" cellspacing="0" style="background-color:#A6C5F0; padding:0; margin:0; border:0;">
                    <tr>
                      <td><img src="<?=$service['path']?>/style/image/maskRoundLeft.gif" width="6" height="34" alt="" /></td>
                      <td onclick="importData()" style="cursor:pointer; font-size:14px; color:#336; font-weight:bold; padding:0 10px;"><span style="background:url(<?=$service['path']?>/style/image/maskDownloadArrow.gif) no-repeat 0 50%; padding-left:25px;"><?=_t('데이터 복원하기')?></span> </td>
                      <td align="right"><img src="<?=$service['path']?>/style/image/maskRoundRight.gif" width="6" height="34" alt="" /></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
	  </form>
	  </div>
	  <div id="progressDialog" style="width:550px; position:absolute; display:none; z-index:10;">
      <table style="width:550px; border:5px solid #BCD2E5; margin-left:10px; background-color:#fff;">
        <tr>
          <td style="padding:10px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="font-size:14px; font-weight:bold; color:#333; padding:0 0 10px 5px;"><?=_t('데이터를 복원하고 있습니다. 잠시만 기다려 주십시오...')?></td>
              </tr>
              <tr>
                <td style="padding:2px 5px 10px 5px; color:#666;">
                  <table style="width:100%; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;">
					    <div style="display:block; width:100%; height:18px; background-color:#eee;">
					      <div style="display:block; position:absolute; width:465px; text-align:center; padding-top:2px"><span id="progressText"><?=_t('백업파일을 올리고 있습니다')?></span><span id="progressTextSub"></span></div>
						  <div id="progressIndicator" style="display:block; width:0%; height:18px; background-color:#66DDFF;"></div>
						</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
	  </div>
      <iframe id="_blackhole" name="_blackhole" style="display:none"></iframe>
    </td>
  </tr>
</table>
<br />
<br />
<table cellspacing="0" width="100%">
  <tr>
    <td>
      <table cellspacing="0" style="width:100%; height:28px">
        <tr>
          <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
          <td style="padding:3px 0px 0px 4px"><?=_t('데이터 삭제')?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
  <tr style="background-color:#EBF2F8">
    <td valign="top" style="padding:10px 5px 10px 5px">
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:5px;">
        <tr>
          <td style="width:100px; padding:10px;">
            <table style="width:100px; height:60px; border:1px solid #536576; padding:10px; background-color:#fff;">
              <tr>
                <td onclick="showDialog('DBRemove')" style="cursor:pointer; text-align:center; background-color:#fff; font:10px verdana;"><img src="<?=$service['path']?>/style/image/dbClear.gif" style="margin-bottom:5px;" alt="" /><br />
                  <?=_t('REMOVE')?></td>
              </tr>
            </table>
          </td>
          <td style="line-height:18px; color:#536576; padding:10px;"><?=_t('태터툴즈의 모든 데이터를 삭제합니다.<br />첨부파일의 삭제 여부를 선택하실 수 있습니다.<br />데이터의 복원은 백업파일로만 가능하므로 먼저 백업을 하시기 바랍니다.')?></td>
        </tr>
      </table>
	  <div id="DBRemoveDialog" style="width:550px; position:absolute; display:none; z-index:10;">
      <table style="width:550px; border:5px solid #BCD2E5; margin-left:10px; background-color:#fff;">
        <tr>
          <td style="padding:10px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="right"><img src="<?=$service['path']?>/style/image/buttonClose.gif" width="13" height="13" border="0" class="pointerCursor" onclick="hideDialog()" alt="<?=_t('닫기')?>" title="<?=_t('닫기')?>" /></td>
              </tr>
              <tr>
                <td style="font-size:14px; font-weight:bold; color:#333; padding:0 0 10px 5px;"><?=_t('데이터 삭제를 시작합니다.')?></td>
              </tr>
              <tr>
                <td style="height:1px; padding:0; background:url(<?=$service['path']?>/style/image/lineDotted.gif);"></td>
              </tr>
<?
if ($backup) {
?>
              <tr>
                <td style="padding-top:10px; color:#333;">&nbsp; * <?=_f('서버에 <em>%1</em>경에 저장된 백업파일이 있습니다.', Timestamp::format5($backup))?></td>
              </tr>
<?
}
?>
              <tr>
                <td style="padding-top:10px; color:#333;">&nbsp; * <?=_t('첨부파일을 포함하여 삭제하시겠습니까?')?></td>
              </tr>
              <tr>
                <td style="padding:2px 5px 10px 5px; color:#666;">
                  <table style="width:100%; background-color:#eee; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:15px;"><span style="padding:20px 5px 10px 5px; color:#666;">
                        <input id="removeAttachments" type="radio" name="removeAttachments" value="1" />
                        <?=_t('첨부파일을 포함합니다')?>   &nbsp;  &nbsp;
                        <input type="radio" name="removeAttachments" value="0" checked="checked" />
                        <?=_t('첨부파일을 포함하지 않습니다')?></span></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding-top:10px; color:#333;">&nbsp; * <?=_t('데이터를 삭제하시려면 관리자 비밀번호를 입력하십시오.')?></td>
              </tr>
              <tr>
                <td style="padding:2px 5px 10px 5px; color:#666;">
                  <table style="width:100%; background-color:#eee; margin:0 5px;">
                    <tr>
                      <td style="color:#666; padding:10px;">
                        <table width="100%" border="0" cellspacing="0">
                          <tr>
                            <td align="center">
                              <input id="confirmativePassword" type="password" name="confirmativePassword" style="border:1px solid #999; background-color:#fff; width:200px;" onkeydown="if (event.keyCode == 13) { removeData(); return false; }" />
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" style="padding:10px 0;">
                  <table cellpadding="0" cellspacing="0" style="background-color:#A6C5F0; padding:0; margin:0; border:0;">
                    <tr>
                      <td><img src="<?=$service['path']?>/style/image/maskRoundLeft.gif" width="6" height="34" alt="" /></td>
                      <td onclick="removeData()" style="cursor:pointer; font-size:14px; color:#336; font-weight:bold; padding:0 10px;"><span style="background:url(<?=$service['path']?>/style/image/maskDownloadArrow.gif) no-repeat 0 50%; padding-left:25px;"><?=_t('데이터 삭제하기')?></span> </td>
                      <td align="right"><img src="<?=$service['path']?>/style/image/maskRoundRight.gif" width="6" height="34" alt="" /></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
	  </div>
    </td>
  </tr>
</table>
<form method="get" action="">
<?
require ROOT . '/lib/piece/owner/footer.php';
?>