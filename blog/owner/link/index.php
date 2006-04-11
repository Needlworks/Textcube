<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$links = getLinks($owner);
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu20.php';
?>
<script type="text/javascript">
//<![CDATA[
	function deleteLink(id) {
		if (!confirm("<?=_t('링크를 삭제하시겠습니까?\t')?>"))
			return;

		var request = new HTTPRequest("GET", blogURL + "/owner/link/delete/" + id);
		request.onSuccess = function () {
			PM.removeRequest(this);
			PM.showMessage("<?=_t('링크가 삭제되었습니다')?>", "center", "bottom");
			var node1 = document.getElementById("link" + id + "1");
			var node2 = document.getElementById("link" + id + "2");
			if(node1)
				node1.parentNode.removeChild(node1);
			else {
				node1 = node2.nextSibling;
				if(node1)
					node1.parentNode.removeChild(node1);
			}
			node2.parentNode.removeChild(node2);
		}
		request.onError= function () {
			PM.removeRequest(this);
			switch(parseInt(this.getText("/response/error")))
			{
				default:
					alert("<?=_t('알 수 없는 에러가 발생했습니다')?>");
			}
		}
		PM.addRequest(request, "<?=_t('링크를 삭제하고 있습니다')?>");
		request.send();
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
                      <td style="width:18px"><img width="18" height="18" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('링크 목록입니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; margin-bottom:1px; table-layout: fixed" border="0">
              <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')">
                <td width="300" style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?=_t('홈페이지 이름')?></td>
				<td style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px;	font-weight:bold"><?=_t('사이트주소')?></td>
                <td width="20"></td>
                <td width="20"></td>
              </tr>
			  
<?
$more = false;
?>	
<?
foreach ($links as $link) {
	if ($more) {
?> 
			<tr id="link<?=$link['id']?>1" style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                <td height="1" colspan="4"></td>
            </tr>
<?
	} else
		$more = true;
?>

			<tr id="link<?=$link['id']?>2" style="height:22px">
                <td class="row"><a class="rowLink" href="<?=$blogURL?>/owner/link/edit/<?=$link['id']?>"><?=htmlspecialchars($link['name'])?></a></td>
				<td class="row"><a class="rowLink" href="<?=htmlspecialchars($link['url'])?>" target="_blank"><?=htmlspecialchars($link['url'])?></a></td>
                <td class="row" align="right"><a class="rowLink" href="<?=$blogURL?>/owner/link/edit/<?=$link['id']?>"><img src="<?=$service['path']?>/image/owner/modify.gif" alt="<?=_t('수정')?>"/></a></td>
                <td class="row" align="right"><a class="rowLink" onclick="deleteLink(<?=$link['id']?>)"><img src="<?=$service['path']?>/image/owner/delete.gif" alt="<?=_t('삭제')?>"/></a></td>
            </tr>
<?
}
?>
                  <table cellspacing="0" style="width:100%; border-bottom:solid 2px #00A6ED">
                    <tr>
                      <td></td>
                    </tr>
                  </table>

			</table>
          </td>
        </tr>
      </table>
      <!-- } Content Body -->
      <table cellspacing="0" style="width:100%">
        <tr>
          <td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftBottom.gif" /></td>
          <td style="background-color:#FFFFFF"><img alt="" width="1" height="1" src="<?=$service['path']?>/image/owner/spacer.gif" /></td>
          <td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightBottom.gif" /></td>
        </tr>
      </table>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>