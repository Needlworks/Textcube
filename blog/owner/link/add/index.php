<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu21.php';
?>
<script type="text/javascript">
//<![CDATA[
	function getSiteInfo() {
		if(document.forms[0].rss.value == '') {
			alert("<?=_t('RSS 주소를 입력해 주세요')?>\t");
			return false;		
		}

		if(document.forms[0].rss.value.indexOf("http://")==-1) {
			uri = 'http://'+document.forms[0].rss.value;
		} else {
			uri = document.forms[0].rss.value;
		}
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/link/site/?rss=" + uri);
		request.onSuccess = function () {
			PM.removeRequest(this);
			document.forms[0].name.value = this.getText("/response/name");
			document.forms[0].url.value = this.getText("/response/url");
			return true;
		}
		request.onError = function () {
			PM.removeRequest(this);
			alert("<?=_t('RSS를 읽어올 수 없습니다')?>");
			return false;
		}
		PM.addRequest(request, "<?=_t('RSS를 읽어오고 있습니다')?>");
		request.send();
	}

	function addLink() {
		var oForm = document.forms[0];
		trimAll(oForm);
		if (!checkValue(oForm.name, "<?=_t('이름을 입력해 주십시오')?>\t")) return false;
		if (!checkValue(oForm.url, "<?=_t('주소를 입력해 주세요')?>\t")) return false;

		var request = new HTTPRequest("POST", blogURL + "/owner/link/add/exec/");
		request.onSuccess = function () {
			PM.removeRequest(this);
			window.location = blogURL + "/owner/link";
		}
		request.onError= function () {
			PM.removeRequest(this);
			switch(parseInt(this.getText("/response/error")))
			{
				case 1:
					alert("<?=_t('이미 존재하는 주소입니다')?>");
					break;
				default:
					alert("<?=_t('알 수 없는 에러가 발생했습니다')?>");
			}
		}
		PM.addRequest(request, "<?=_t('링크를 추가하고 있습니다')?>");
		request.send("name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) + "&rss=" + encodeURIComponent(oForm.rss.value));
	}	
//]]>
</script>
      <table cellspacing="0" style="width:100%; background-color:#FFFFFF">
        <tr>
          <td valign="top" style="height:50px; padding:5px 15px 15px 15px">
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('링크 정보를 설정합니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
					<table cellspacing="0">
						<tr>
						  <td class="entryEditTableLeftCell"><?=_t('RSS 주소')?> |</td>
						  <td>
							<input type="text" class="text1" name="rss" style="width:300px"/>
						  </td>
						  <td>
							<table class="buttonTop" cellspacing="0" onclick="getSiteInfo(); return false">
							  <tr>
								<td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
								<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('정보가져오기')?></td>
								<td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
							  </tr>
							</table>
						  </td>
						</tr>
					  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('홈페이지 제목')?> |</td>
                      <td>
                        <input name="name" type="text" class="text1" id="name" style="width:300px" value="" />
                      </td>
                    </tr>
                  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('홈페이지 주소')?> |</td>
                      <td>
                        <input name="url" type="text" class="text1" id="url" style="width:300px" value="" />
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <div align="center">
              <table style="margin-top:10px">
                <tr>
                  <td>
                    <table class="buttonTop" cellspacing="0" onclick="addLink()">
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('추가하기')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
                  </td>
                  <td>
                    <table class="buttonTop" cellspacing="0" onclick="window.location.href='<?=$blogURL?>/owner/link'">
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('취소하기')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
<?
require ROOT . '/lib/piece/owner/footer.php';
?> 