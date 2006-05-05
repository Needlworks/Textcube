<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';
?>
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table width="100%">
                    <tr>
                      <td width="250" valign="top">
                        <table cellspacing="0" style="width:100%; height:28px">
                          <tr>
                            <td style="width:18px"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
                            <td style="padding:3px 0px 0px 4px"><?=_t('리퍼러 순위')?></td>
                          </tr>
                        </table>
                        <table cellspacing="0" width="100%" style="width:100%; border:solid #00A6ED; border-width:2px 0px 2px 0px">
<?
$more = false;
$i = 0;
foreach (getRefererStatistics($owner) as $record) {
	$i++;
	if ($more) {
?>
                          <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                            <td height="1" colspan="2"></td>
                          </tr>
<?
	} else
		$more = true;
?>
                          <tr onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding:2px" width="20" align="right"><?=$i?>.</td>
                            <td style="padding:2px" class="pointerCursor" onclick="window.open('http://<?=escapeJSInAttribute($record['host'])?>')"><?=htmlspecialchars($record['host'])?> (<?=$record['count']?>)</td>
                          </tr>
<?
}
?>
                        </table>
                      </td>
                      <td style="padding-left:10px">
                        <table cellspacing="0" style="width:100%; height:28px">
                          <tr>
                            <td style="width:18px"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
                            <td style="padding:3px 0px 0px 4px"><?=_t('리퍼러 로그')?></td>
                          </tr>
                        </table>
                        <table cellspacing="0" width="100%" style="width:100%; border:solid #00A6ED; border-width:2px 0px 2px 0px">
<?
$more = false;
$i = 0;
foreach (getRefererLogs() as $record) {
	$i++;
	if ($more) {
?>
                          <tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')">
                            <td height="1" colspan="3"></td>
                          </tr>
<?
	} else
		$more = true;
?>
                          <tr onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding:2px" width="75"><?=Timestamp::formatDate($record['referred'])?></td>
                            <td style="padding:2px" width="150" class="pointerCursor" onclick="window.open('http://<?=escapeJSInAttribute($record['host'])?>')"><?=htmlspecialchars($record['host'])?></td>
                            <td style="padding:2px" class="pointerCursor" title="<?=htmlspecialchars($record['url'], 40)?>" onclick="window.open('<?=escapeJSInAttribute($record['url'])?>')"><?=fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 40)), $record)?></td>
                          </tr>
<?
}
?>
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