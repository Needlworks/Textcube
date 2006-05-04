<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
publishEntries();
if (isset($_POST['categoryAtHome']))
	$_POST['category'] = $_POST['categoryAtHome'];
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = getPersonalization($owner, 'rowsPerPage');
if (empty($_POST['perPage'])) {
} else if (!empty($_POST['perPage']) && $perPage != $_POST['perPage']) {
	setPersonalization($owner, 'rowsPerPage', $_POST['perPage']);
	$perPage = $_POST['perPage'];
} else if (!empty($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
}
list($entries, $paging) = getEntriesWithPagingForOwner($owner, $categoryId, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu00.php';
require ROOT . '/lib/piece/owner/contentMeta0Begin.php';
require ROOT . '/lib/piece/owner/contentMeta0End.php';
?>
<script type="text/javascript">
//<![CDATA[
<?
if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION)) {
?>
	window.onload = function () {
		if (confirm("<?=_t("시스템 점검이 필요합니다. 지금 점검하시겠습니까?")?>"))
			window.location.href = "<?=$blogURL?>/checkup";
	}
<?
}
?>
	function setEntryVisibility(entry, visibility) {
		if ((visibility < 0) || (visibility > 3))
			return false;
		var request = new HTTPRequest("<?=$blogURL?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
		switch (visibility) {
			case 0:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "inline");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOff").style, "display", "inline");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "protectedSetting").style, "display", "none");
				break;
			case 1:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOff").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "inline");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOff").style, "display", "inline");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "protectedSetting").style, "display", "inline");
				break;
			case 2:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "none");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "protectedSetting").style, "display", "none");
				break;
			case 3:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "protectedOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "syndicatedOff").style, "display", "none");
<?
?>
				request.presetProperty(document.getElementById("entry" + entry + "protectedSetting").style, "display", "none");
				break;
		}
		request.send();
	}
	function deleteEntry(id) {
		if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;
		var request = new HTTPRequest("<?=$blogURL?>/owner/entry/delete/" + id);
		request.onSuccess = function () {
			document.forms[0].submit();
		}
		request.send();
	}
	function protectEntry(id) {
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/protect/" + id);
		request.onSuccess = function () {
			hideLayer("entry" + id + "Protection");
		}
		request.onError = function () {
			alert("<?=_t('보호글의 비밀번호를 변경하지 못 했습니다')?>");
		}
		request.send("password=" + encodeURIComponent(document.getElementById("entry" + id + "Password").value));
	}
	function checkAll(checked) {
		for (i = 0; document.forms[0].elements[i]; i ++)
			if (document.forms[0].elements[i].name == "entry")
				document.forms[0].elements[i].checked = checked;
	}
	function processBatch(mode) {
		var entries = '';
		switch (mode) {
			case 'classify':
				for (var i = 0; i < document.forms[0].elements.length; i++) {
					var oElement = document.forms[0].elements[i];
					if ((oElement.name == "entry") && oElement.checked)
						setEntryVisibility(oElement.value, 0);
				}
				break;
			case 'publish':
				for (var i = 0; i < document.forms[0].elements.length; i++) {
					var oElement = document.forms[0].elements[i];
					if ((oElement.name == "entry") && oElement.checked)
						setEntryVisibility(oElement.value, 2);
				}
				break;
			case 'delete':
				if (!confirm("<?=_t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?\t')?>"))
					return false;
				var targets = "";
				for (var i = 0; i < document.forms[0].elements.length; i++) {
					var oElement = document.forms[0].elements[i];
					if ((oElement.name == "entry") && oElement.checked)
						targets += oElement.value +'~*_)';
				}
				var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/delete/");
				request.onSuccess = function () {
					document.forms[0].submit();
				}
				request.send("targets="+targets);
				break;
		}
	}

	function removeTrackbackLog(id,entry) {
		if (confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?\t')?>")) {
			var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/remove/" + id);
			request.onSuccess = function () {
				printTrackbackLog(entry);
			}
			request.onError = function () {
				alert("<?=_t('트랙백을 삭제하는데 실패하였습니다.')?>");
			}
			request.send();
		}
	}
	
	function printTrackbackLog(id) {
		var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/log/" + id);
		request.onVerify = function () {
			var resultRow = this.getText("/response/result").split('*');
			if (resultRow.length == 1)
				var str ='';
			else {
				var str='<table border="0">';
				for (var i=0; i<resultRow.length-1 ; i++) {
					field = resultRow[i].split(',');
					str += '<tr id="trackbackLog_'+field[0]+'">\n';
					str += '	<td>'+field[1]+'</td>\n'
					str += '	<td align="center" width="140">'+field[2]+'</td>\n'
					str += '	<td class="rowLink pointerCursor" onclick="removeTrackbackLog('+field[0]+','+id+');"><img src="<?=$service['path']?>/image/owner/deleteX.gif" alt="<?=_t('삭제')?>"/></td>\n'
					str += '<tr>\n';
				}
				str += "</table>";
			}
			document.getElementById("logs_"+id).innerHTML = str;
			return true;
		}
		request.send();
	}
	
	function showTrackbackSender(id) {
		collapseAllTrackback(id);
		if (document.getElementById('trackbackSender_'+id).style.display == "block") {
			document.getElementById('trackbackSender_'+id).style.display = "none";
			return;
		}
		document.getElementById('trackbackSender_'+id).style.display = "block";
		document.getElementById('trackbackForm_'+id).select();
		printTrackbackLog(id);		
	}

	function sendTrackback(id) {
		var trackbackField = document.getElementById('trackbackForm_'+id);
		var request = new HTTPRequest("<?=$blogURL?>/owner/entry/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
		request.onSuccess = function () {
			document.getElementById('trackbackForm_'+id).value = "http://";
			printTrackbackLog(id);
		}
		request.onError = function () {
			alert("<?=_t('트랙백 전송에 실패하였습니다.')?>");
		}
		request.send();
	}

	function collapseAllTrackback(except) {
		var divs = document.getElementsByTagName('div');
		for (var i = 0; i < divs.length; i++) {
			if (divs[i].id.indexOf('trackbackSender_') != -1) {
				if (except != undefined) {
					if (divs[i].id == 'trackbackSender_'+except) 
						continue;
				}
				divs[i].style.display = 'none';
			}
		}
	}
//]]>
</script> 
            <input type="hidden" name="withSearch" value="" />
            <table cellspacing="0" border="0" style="width:100%; margin-bottom:1px; table-layout: fixed" id="list">
              <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')" >
                <td width="20">
                  <input type="checkbox" name="allChecked" onclick="checkAll(this.checked)" />
                </td>
                <td width="60" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('등록일자')?></td>			
                <td width="60" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('상태')?></td>
<?
?>	
			 	<td width="75" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('발행')?></td>
<?
?>				
                <td width="150" align="center" nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('분류')?></td>
                <td nowrap="nowrap" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('제목')?></td>
                <td width="30"></td>
                <td width="20"></td>
				<td width="20"></td>
              </tr>
<?
$more = false;
foreach ($entries as $entry) {
	if ($more) {
?>
              <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                <td height="1" colspan="9"></td>
              </tr>
<?
	} else
		$more = true;
?>
              <tr style="height:22px" onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
                <td>
				  <input type="checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.forms[0].allChecked.checked = false" />
                </td>
                <td style="padding:0px 7px 0px 7px; font-size:12px"><span class="rowDate"><?=Timestamp::formatDate($entry['published'])?></span></td>
                <td align="center" class="row"><img id="entry<?=$entry['id']?>privateOn" style="display:<?=($entry['visibility'] <= 0 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/privateOn.gif" alt="<?=_t('현재 비공개 상태입니다')?>" /><img id="entry<?=$entry['id']?>privateOff" style="cursor:pointer; display:<?=($entry['visibility'] <= 0 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/privateOff.gif" alt="<?=_t('현재 상태를 비공개로 전환합니다')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" /> <img id="entry<?=$entry['id']?>protectedOn" style="display:<?=($entry['visibility'] == 1 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/protectedOn.gif" alt="<?=_t('현재 보호 상태입니다')?>" /><img id="entry<?=$entry['id']?>protectedOff" style="cursor:pointer; display:<?=($entry['visibility'] == 1 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/protectedOff.gif" alt="<?=_t('현재 상태를 보호로 전환합니다')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 1)" /> <img id="entry<?=$entry['id']?>publicOn" style="display:<?=($entry['visibility'] >= 2 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/publicOn.gif" alt="<?=_t('현재 공개 상태입니다')?>" /><img id="entry<?=$entry['id']?>publicOff" style="cursor:pointer; display:<?=($entry['visibility'] >= 2 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/publicOff.gif" alt="<?=_t('현재 상태를 공개로 전환합니다')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" /></td>
<?
?>				
                <td align="center" class="row">
                  <img id="entry<?=$entry['id']?>syndicatedOn" style="cursor:pointer; display:<?=($entry['visibility'] == 3 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/syndicatedOn.gif" alt="<?=_t('발행')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" />
                  <img id="entry<?=$entry['id']?>syndicatedOff" style="cursor:pointer; display:<?=($entry['visibility'] == 3 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/syndicatedOff.gif" alt="<?=_t('발행하기')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 3)" />
				</td>
<?
?>				
                <td align="center" class="row"><a class="rowLink" onclick="document.forms[0].category.value='<?=$entry['category']?>'; document.forms[0].submit()"><?=htmlspecialchars($entry['categoryLabel'])?></a></td> 
                <td class="row"><?=($entry['draft'] ? ('<img src="' . $service['path'] . '/image/owner/hasTemp.gif" alt="' . _t('임시 저장본이 있습니다') . '" />') : '')?> <a class="rowLink" style="text-decoration:none" onclick="document.forms[0].action='<?=$blogURL?>/owner/entry/edit/<?=$entry['id']?>'<?=($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?\t') . "') ? '?draft' : '')") : '')?>; document.forms[0].submit()"><?=htmlspecialchars($entry['title'])?></a></td>
                <td style="padding-top:2px">
					<img id="entry<?=$entry['id']?>protectedSetting" src="<?=$service['path']?>/image/owner/protectedInvite.gif" alt="<?=_t('보호')?>" class="pointerCursor" style="display:<?=(abs($entry['visibility']) == 1 ? 'inline' : 'none')?>" onclick="toggleLayer('entry<?=$entry['id']?>Protection')" />

				</td>
                <td align="center" style="padding-top:2px">
					<img src="<?=$service['path']?>/image/owner/trackback.gif" alt="<?=_t('트랙백')?>" class="pointerCursor" onclick="showTrackbackSender(<?=$entry['id']?>,event)" />
				</td>
				<td align="right" style="padding-top:2px; width:35px;>
					<a class="rowlink" onclick="deleteEntry(<?=$entry['id']?>)"> <img src="<?=$service['path']?>/image/owner/delete.gif" alt="<?=_t('삭제')?>"/> </a> &nbsp; 
				</td>
              </tr>
				<tr>
                  <td colspan="9"  align="right">
                    <div id = "entry<?=$entry['id']?>Protection" style="display:none; padding:5px; background-color: #eee">
<table width="100%" border="0" cellspacing="0">
  <tr>
    <td align="right">
      <table border="0">
        <tr>
          <td align="right" style="padding:0px 3px 0px 10px; font-size:12px" nowrap="nowrap"><?=_t('비밀번호')?></td>
          <td>
            <input id="entry<?=$entry['id']?>Password" type="text" value="<?=$entry['password']?>" maxlength="16" style="border:1px #999 solid; background-color:#fff; height:18px; width:100px;" onkeydown="if (event.keyCode == 13) protectEntry(<?=$entry['id']?>)" />
          </td>
          <td>
            <input type="button" name="set" value="<?=_t('수정')?>" style="border:1px solid #666; background-color:#eee; height:20px;" onclick="protectEntry(<?=$entry['id']?>)" />
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="right"><img src="<?=$service['path']?>/image/owner/close.gif" style="cursor:pointer" onclick="hideLayer('entry<?=$entry['id']?>Protection')" /></td>
  </tr>
</table>
						</div>
                    <div id = "trackbackSender_<?=$entry['id']?>" style="display:none; padding:5px; background-color: #eee">
<table width="100%" border="0" cellspacing="0">
  <tr>
    <td align="right">
      <table border="0">
        <tr>
          <td align="right" style="padding:0px 3px 0px 10px; font-size:12px" nowrap="nowrap"><?=_t('트랙백 주소')?>: </td>
          <td>
            <input id="trackbackForm_<?=$entry['id']?>" type="text" name="trackbackURL" value="http://" style="border:1px #999 solid; background-color:#fff; height:18px; width:300px;" onkeydown="if (event.keyCode == 13) sendTrackback(<?=$entry['id']?>)" />
          </td>
          <td>
            <input type="button" name="send" value="<?=_t('전송')?>" style="border:1px solid #666; background-color:#eee; height:20px;" onclick="sendTrackback(<?=$entry['id']?>)" />
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#dddddd"></td>
  </tr>
  <tr>
    <td align="right">
      <span id="logs_<?=$entry['id']?>" style="padding:0px 20px 0px 0px"></span>
    </td>
  </tr>
  <tr>
    <td align="right"><img src="<?=$service['path']?>/image/owner/close.gif" style="cursor:pointer" onclick="collapseAllTrackback()" /></td>
  </tr>
</table>
						</div>
					</td>
				</tr>

<?
}
?>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
                  <table cellspacing="0" width="100%">
                    <tr>
                      <td>
                        <table cellspacing="0">
                          <tr>
                            <td style="padding:0px 7px 0px 7px; font-size:12px"><?=_t('선택한 글을')?></td>
                            <td>
                              <select onchange="processBatch(this.value); this.selectedIndex=0">
                                <option value="">---------------------------</option>
                                <option value="publish"><?=_t('공개로 변경합니다')?></option>
                                <option value="classify"><?=_t('비공개로 변경합니다')?></option>
                                <option value="delete"><?=_t('삭제합니다')?></option>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
					  
                    </tr>
                  </table>
                  <table style="width:100%; margin:7px 0px 5px 0px">
                    <tr>
                      <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px" /></td>
                    </tr>
                  </table>
                  <table cellspacing="0" width="100%">
                    <tr style="height:22px">
                      <td style="padding:0px 7px 0px 7px; font-size:12px" width="55"><?=_t('총')?> <?=$paging['total']?><?=_t('건')?></td>
                      <td style="padding:0px 7px 0px 7px; font-size:12px">
<?
$paging['url'] = 'javascript: document.forms[0].page.value=';
$paging['prefix'] = '';
$paging['postfix'] = '; document.forms[0].submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a class="pageLink" [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
					  </td>
                      <td align="right">
                        <table cellspacing="0" style="margin-right:5px">
                          <tr>
						  	<!--
                            <td style="padding:0px 7px 0px 10px; font-size:12px" >
								<?=_t('제목')?> | <?=_t('내용')?>
							</td>
							-->
                            <td style="padding:0px 5px 0px 5px">
                              <input type="text" name="search" value="<?=htmlspecialchars($search)?>" class="text1" style="width:70px" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
                            </td>
                            <td>
                              <table class="buttonTop" cellspacing="0" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();">
                                <tr>
                                  <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" /></td>
                                  <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('검색')?></td>
                                  <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" /></td>
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

<?
require ROOT . '/lib/piece/owner/footer.php';
?>