<?
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'perPage' => array('int', 1, 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';

$page = 20; //getPersonalization($owner, 'rowsPerPage');
if (empty($_POST['perPage'])) {
	$perPage = $page;
} else if ($page != $_POST['perPage']) {
	//setPersonalization($owner, 'rowsPerPage', $_POST['perPage']);
	$perPage = $_POST['perPage'];
} else {
	$perPage = $_POST['perPage'];
}

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
                          <tr>
                            <td style="padding:2px" width="20" align="right"><?=$i?>.</td>
                            <td style="padding:2px"><a href="http://<?=escapeJSInAttribute($record['host'])?>" target="_blank"><?=htmlspecialchars($record['host'])?></a> (<?=$record['count']?>)</td>
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
							<td align="right">
							  <table cellspacing="0">
								<tr>
								  <td class="row"><?=getArrayValue(explode('%1', _t('한 페이지에 목록 %1건 표시')), 0)?></td>
								  <td>
								  
									<select name="perPage" onchange="document.forms[0].page.value=1; document.forms[0].submit()">					
<?
for ($i = 10; $i <= 100; $i += 5) {
	if ($i == $perPage) {
?>
			                           <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?
	} else {
?>
						               <option value="<?=$i?>"><?=$i?></option>
<?
	}
}
?>
			                        </select>
								  </td>
								  <td class="row"><?=getArrayValue(explode('%1', _t('한 페이지에 목록 %1건 표시')), 1)?></td>
								</tr>
							  </table>
							</td>
                          </tr>
                        </table>
                        <table cellspacing="0" width="100%" style="width:100%; border:solid #00A6ED; border-width:2px 0px 2px 0px">
<?
$more = false;
$i = 0;
list($refereres, $paging) = getRefererLogsWithPage($suri['page'], $perPage);
foreach ($refereres as $record) {
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
                          <tr>
                            <td style="padding:2px" width="75"><?=Timestamp::formatDate($record['referred'])?></td>
                            <td style="padding:2px" title="<?=htmlspecialchars($record['url'])?>" style="word-break: break-all"><a href="<?=escapeJSInAttribute($record['url'])?>" target="_blank"><?=fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
                          </tr>
<?
}
?>
                        </table>
                        <table cellspacing="0" width="100%">
							<tr style="height:22px;">
								<td style="padding:0px 7px 0px 7px; font-size:12px;" width="55"><?=_t('총')?> <?=$paging['total']?><?=_t('건')?></td>
                                <td style="padding:0px 7px 0px 7px; font-size:12px;">
<?
$paging['url'] = 'javascript: document.forms[0].page.value=';
$paging['prefix'] = '';
$paging['postfix'] = '; document.forms[0].submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a class="pageLink" [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
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