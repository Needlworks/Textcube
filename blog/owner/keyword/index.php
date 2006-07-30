<?php
define('ROOT', '../../..');
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
$IV = array(
	'GET' => array(
		'page' => array('int', 1, 'default' => 1)
	),
	'POST' => array(
		'withSearch' => array(array('on'), 'default' => null),
		'search' => array('string', 'mandatory' => false),
		'withSearch' => array('any' , 'default' => null)
	)
);
require ROOT . '/lib/includeForOwner.php';
respondNotFoundPage();
$search = empty($_POST['withSearch']) ? '' : $_POST['search'];
list($entries, $paging) = getKeywordsWithPaging($owner, $search, $suri['page'], 10);
require ROOT . '/lib/piece/owner/header1.php';
require ROOT . '/lib/piece/owner/contentMenu10.php';
?>
<script type="text/javascript">
//<![CDATA[
	function setEntryVisibility(entry, visibility) {
		if ((visibility < 0) || (visibility > 3))
			return false;
		var request = new HTTPRequest("<?php echo $blogURL?>/owner/entry/visibility/" + entry + "?visibility=" + visibility);
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
		if (!confirm("<?php echo _t('이 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
			return;
		var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/entry/delete/" + id);
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
				if (!confirm("<?php echo _t('선택된 글 및 이미지 파일을 완전히 삭제합니다. 계속 하시겠습니까?')?>"))
					return false;
				var targets = "";
				for (var i = 0; i < document.forms[0].elements.length; i++) {
					var oElement = document.forms[0].elements[i];
					if ((oElement.name == "entry") && oElement.checked)
						targets += oElement.value +'~*_)';
				}
				var request = new HTTPRequest("POST", "<?php echo $blogURL?>/owner/entry/delete/");
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
		if (!checkValue(oForm.search, "<?php echo _t('검색어를 입력해 주십시오.')?>")) return false;
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
            <input type="hidden" name="withSearch" value="<?php echo (empty($_POST['withSearch']) ? '' : 'on')?>" />
            <table cellspacing="0" style="width:100%; height:28px">
              <tr>
                <td style="width:18px"><img src="<?php echo $blogURL?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                <td style="padding:3px 0px 0px 4px"><?php echo _t('등록된 키워드 목록입니다.')?></td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; margin-bottom:1px">
              <tr style="background-color:#00A6ED; height:24px; background-image: url('<?php echo $blogURL?>/image/owner/subTabCenter.gif')">
                <td width="20">
                  <input type="Checkbox" name="allChecked" onclick="checkAll(this.checked)" />
                </td>
                <td width="70" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?php echo _t('등록일자')?></td>
                <td width="40" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?php echo _t('상태')?></td>
                <td width="320" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?php echo _t('키워드')?></td>
                <td></td>
              </tr>
<?php
$more = false;
foreach ($entries as $entry) {
	if ($more) {
?>
              <tr style="background-image:url('<?php echo $blogURL?>/image/owner/dotHorizontalStyle1.gif')">
                <td height="1" colspan="5"></td>
              </tr>
<?php
	} else
		$more = true;
?>
              <tr style="height:22px">
                <td>
				  <input type="Checkbox" name="entry" value="<?php echo $entry['id']?>" onclick="document.forms[0].allChecked.checked = false" />
                </td>
                <td style="padding:0px 7px 0px 7px; font-size:12px"><span style="font-size:11px;font-family:verdana"><?php echo Timestamp::format3($entry['published'])?></span></td>
                <td class="row">
                  <img id="entry<?php echo $entry['id']?>privateOn" style="display:<?php echo ($entry['visibility'] <= 0 ? 'inline' : 'none')?>" src="<?php echo $blogURL?>/image/owner/privateOn.gif" alt="<?php echo _t('비공개')?>" /><img id="entry<?php echo $entry['id']?>privateOff" style="cursor:pointer; display:<?php echo ($entry['visibility'] <= 0 ? 'none' : 'inline')?>" src="<?php echo $blogURL?>/image/owner/privateOff.gif" alt="<?php echo _t('현재 상태를 비공개로 전환합니다.')?>" onclick="setEntryVisibility(<?php echo $entry['id']?>, 0)" /> <img id="entry<?php echo $entry['id']?>publicOn" style="display:<?php echo ($entry['visibility'] >= 2 ? 'inline' : 'none')?>" src="<?php echo $blogURL?>/image/owner/publicOn.gif" alt="<?php echo _t('공개')?>" /><img id="entry<?php echo $entry['id']?>publicOff" style="cursor:pointer; display:<?php echo ($entry['visibility'] >= 2 ? 'none' : 'inline')?>" src="<?php echo $blogURL?>/image/owner/publicOff.gif" alt="<?php echo _t('현재 상태를 공개로 전환합니다.')?>" onclick="setEntryVisibility(<?php echo $entry['id']?>, 2)" />
                </td>
                <td class="row"><a class="rowLink" style="text-decoration:none" onclick="window.location.href='<?php echo $blogURL?>/owner/keyword/edit/<?php echo $entry['id']?>'"><?php echo htmlspecialchars($entry['title'])?></a></td>
                <td align="right" style="padding-top:2px"><a class="rowLink" onclick="deleteEntry(<?php echo $entry['id']?>)"><img src="<?php echo $blogURL?>/image/owner/delete.gif" alt="<?php echo _t('삭제')?>" />&nbsp;&nbsp;</td>
              </tr>
<?php
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
                            <td style="padding:0px 7px 0px 7px; font-size:12px"><?php echo _t('선택한 글을')?></td>
                            <td>
                              <select onchange="processBatch(this.value); this.selectedIndex=0">
                                <option value="">---------------------------</option>
                                <option value="publish"><?php echo _t('공개로 변경합니다.')?></option>
                                <option value="classify"><?php echo _t('비공개로 변경합니다.')?></option>
                                <option value="delete"><?php echo _t('삭제합니다.')?></option>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <table style="width:100%; margin:7px 0px 5px 0px">
                    <tr>
                      <td style="background-image:url('<?php echo $blogURL?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?php echo $blogURL?>/image/owner/spacer.gif" style="width:1px; height:1px" /></td>
                    </tr>
                  </table>
                  <table cellspacing="0" width="100%">
                    <tr style="height:22px">
                      <td style="padding:0px 7px 0px 7px; font-size:12px" width="55"><?php echo _f('총 %1건', empty($paging['total']) ? "0" : $paging['total'])?></td>
                      <td style="padding:0px 7px 0px 7px; font-size:12px">
<?php
$paging['url'] = 'document.forms[0].page.value=';
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
                            <td style="padding:0px 7px 0px 10px; font-size:12px" ><?php echo _t('키워드')?> | <?php echo _t('설명')?></td>
                            <td style="padding:0px 5px 0px 5px">
                              <input class="text1" type="text" name="search" value="<?php echo htmlspecialchars($search)?>" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" style="width:70px" />
                            </td>
                            <td>
                              <table class="buttonTop" cellspacing="0" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();">
                                <tr>
                                  <td><img alt="" width="4" height="24" src="<?php echo $blogURL?>/image/owner/buttonLeft.gif" /></td>
                                  <td class="buttonCenter" style="work-break:keep-all;background-image:url('<?php echo $blogURL?>/image/owner/buttonCenter.gif')"><?php echo _t('검색')?></td>
                                  <td><img alt="" width="5" height="24" src="<?php echo $blogURL?>/image/owner/buttonRight.gif" /></td>
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
<?php
require ROOT . '/lib/piece/owner/footer0.php';
?>
