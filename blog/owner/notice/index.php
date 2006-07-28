<?
define('ROOT', '../../..');
$IV = array(
	'POST' => array(
		'withSearch' => array('any' ,'mandatory' => false),
		'search' => array('string' ,'mandatory' => false)		
	)
);
require ROOT . '/lib/includeForOwner.php';
$search = empty($_POST['withSearch']) ? '' : $_POST['search'];
list($entries, $paging) = getNoticesWithPaging($owner, $search, $suri['page'], 10);
require ROOT . '/lib/piece/owner/header7.php';
require ROOT . '/lib/piece/owner/contentMenu70.php';
?>
<script type="text/javascript">
//<![CDATA[
	function setEntryVisibility(entry, visibility) {
		if ((visibility < 0) || (visibility > 3))
			return false;
		var request = new HTTPRequest("<?=$blogURL?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
		switch (visibility) {
			case 0:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "inline");
				break;
			case 2:
				request.presetProperty(document.getElementById("entry" + entry + "privateOn").style, "display", "none");
				request.presetProperty(document.getElementById("entry" + entry + "privateOff").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOn").style, "display", "inline");
				request.presetProperty(document.getElementById("entry" + entry + "publicOff").style, "display", "none");
				break;
		}
		request.send();
	}
	function deleteEntry(id) { 
		if (!confirm("<?=_t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/delete/" + id);
		request.onSuccess = function () {
			document.forms[0].submit();
		}
		request.send();
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
	function searchEntry() {
		var oForm = document.forms[0];
		trimAll(oForm);
		if (!checkValue(oForm.search, "<?=_t('검색어를 입력해 주십시오')?>")) return false;
		oForm.page.value = "";
		oForm.withSearch.value = "on";
		oForm.submit();
	}
	function cancelSearch() {
		var oForm = document.forms[0];
		oForm.page.value = "";
		oForm.withSearch.value = "";
		oForm.submit();
	}
//]]>
</script>
            <input type="hidden" name="withSearch" value="<?=(empty($_POST['withSearch']) ? '' : 'on')?>" />
            <table cellspacing="0" style="width:100%; height:28px">
              <tr>
                <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                <td style="padding:3px 0px 0px 4px"><?=_t('등록된 공지 목록입니다')?></td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; margin-bottom:1px">
              <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')">
                <td width="20">
                  <input type="Checkbox" name="allChecked" onclick="checkAll(this.checked)" />
                </td>
                <td width="70" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?=_t('등록일자')?></td>
                <td width="40" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?=_t('상태')?></td>
                <td width="320" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?=_t('공지')?></td>
                <td></td>
              </tr>
<?
$more = false;
foreach ($entries as $entry) {
	if ($more) {
?>
              <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                <td height="1" colspan="5"></td>
              </tr>
<?
	} else
		$more = true;
?>
              <tr style="height:22px">
                <td>
				  <input type="Checkbox" name="entry" value="<?=$entry['id']?>" onclick="document.forms[0].allChecked.checked = false" />
                </td>
                <td style="padding:0px 7px 0px 7px; font-size:12px"><span style="font-size:11px;font-family:verdana"><?=Timestamp::format3($entry['published'])?></span></td>
                <td class="row">
                  <img id="entry<?=$entry['id']?>privateOn" style="display:<?=($entry['visibility'] <= 0 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/privateOn.gif" alt="<?=_t('비공개')?>" /><img id="entry<?=$entry['id']?>privateOff" style="cursor:pointer; display:<?=($entry['visibility'] <= 0 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/privateOff.gif" alt="<?=_t('현재 상태를 비공개로 전환합니다')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 0)" /> <img id="entry<?=$entry['id']?>publicOn" style="display:<?=($entry['visibility'] >= 2 ? 'inline' : 'none')?>" src="<?=$service['path']?>/image/owner/publicOn.gif" alt="<?=_t('공개')?>" /><img id="entry<?=$entry['id']?>publicOff" style="cursor:pointer; display:<?=($entry['visibility'] >= 2 ? 'none' : 'inline')?>" src="<?=$service['path']?>/image/owner/publicOff.gif" alt="<?=_t('현재 상태를 공개로 전환합니다')?>" onclick="setEntryVisibility(<?=$entry['id']?>, 2)" />
                </td>
                <td class="row"><a class="rowLink" style="text-decoration:none" onclick="document.forms[0].action='<?=$blogURL?>/owner/notice/edit/<?=$entry['id']?>'; document.forms[0].submit()"><?=htmlspecialchars($entry['title'])?></a></td>
                <td align="right" style="padding-top:2px"><a class="rowLink" onclick="deleteEntry(<?=$entry['id']?>)"><img src="<?=$service['path']?>/image/owner/delete.gif" alt="<?=_t('삭제')?>"/></a>&nbsp;&nbsp;</td>
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
                            <td style="padding:0px 7px 0px 10px; font-size:12px" ><?=_t('공지')?> | <?=_t('설명')?></td>
                            <td style="padding:0px 5px 0px 5px">
                              <input class="text1" type="text" name="search" value="<?=htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" style="width:70px" />
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