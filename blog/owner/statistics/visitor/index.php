<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$stats = getStatistics($owner);
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu40.php';
?>
<script type="text/javascript">
//<![CDATA[
	function setTotalStatistics() {
		if (confirm("<?=_t('방문자의 수를 초기화하면 방문객의 수가 0 이 됩니다. 정말 초기화합니까?\t')?>")) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/statistics/visitor/set/0");
			request.onSuccess = function() {
				//document.getElementById("total").innerHTML = 0;
				window.location = '<?=$blogURL?>/owner/statistics/visitor';
				return true;
			}
			request.onError = function() {
				alert("<?=_t('저장하지 못했습니다')?>");
				return false;
			}
			request.send();
		}
	}
	function addCommas(nStr)
	{
		nStr += '';
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
	

//]]>
</script>
           <table>
              <tr>
                <td valign="top" width="220">
                  <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
                    <tr>
                      <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px" align="center"><?=_t('현재까지의 방문자 수')?><br /><span id="total" style="padding-left:15px; color:#50B0C0; font-size:30px; line-height:30px; font-weight:bold; font-family:arial"><?=number_format($stats['total'])?></span>					  
                        <br />
						
							<table class="buttonTop" cellspacing="0" onclick="setTotalStatistics()">
								<tr>
									<td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
									<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('초기화')?></td>
									<td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
								</tr>
							</table>	
								
                      </td>					 
                    </tr>
                  </table>
                </td>
                <td width="8"></td>
                <td valign="top" width="220">
                  <table cellspacing="0" style="width:100%; margin-bottom:1px">
                    <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')">
                      <td style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('월별 방문자 수')?></td>
                    </tr>
                  </table>
                  <table width="100%">
<?
$more = false;
foreach (getMonthlyStatistics($owner) as $record) {
	if ($more) {
?>
                    <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                      <td height="1" colspan="2"></td>
                    </tr>
<?
	} else {
		$more = true;
		if (!isset($suri['id']))
			$suri['id'] = $record['date'];
	}
?>
                    <tr class="pointerCursor" onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'" onclick="location.href = '<?=$blogURL?>/owner/statistics/visitor/<?=$record['date']?>'">
                      <td width="75"><?=Timestamp::formatDate2(getTimeFromPeriod($record['date']))?></td>
                      <td><?=$record['visits']?></td>
                    </tr>
<?
}
?>
                  </table>
                  <table cellspacing="0" style="width:100%; border-bottom:solid 2px #00A6ED">
                    <tr>
                      <td></td>
                    </tr>
                  </table>
                </td>
                <td width="8"></td>
                <td valign="top" width="220">
                  <table cellspacing="0" style="width:100%; margin-bottom:1px">
                    <tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif')">
                      <td style="color:#FFFFFF; padding:2px 7px 0px 7px; font-size:13px; font-weight:bold"><?=_t('일별 방문자 수')?></td>
                    </tr>
                  </table>
<?
if (isset($suri['id'])) {
?>
                  <table width="100%">
<?
	$more = false;
	foreach (getDailyStatistics($suri['id']) as $record) {
		if ($more) {
?>
                    <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                      <td height="1" colspan="2"></td>
                    </tr>
<?
		} else
			$more = true;
?>
                    <tr>
                      <td width="100"><?=Timestamp::formatDate(getTimeFromPeriod($record['date']))?></td>
                      <td><?=$record['visits']?></td>
                    </tr>
<?
	}
?>
                  </table>
<?
}
?>
                  <table cellspacing="0" style="width:100%; border-bottom:solid 2px #00A6ED">
                    <tr>
                      <td></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>