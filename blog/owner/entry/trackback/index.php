<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$site = empty($_POST['site']) ? '' : $_POST['site'];
$ip = empty($_POST['ip']) ? '' : $_POST['ip'];
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$page = getPersonalization($owner, 'rowsPerPage');
if (empty($_POST['perPage'])) {
	$perPage = $page;
} else if ($page != $_POST['perPage']) {
	setPersonalization($owner, 'rowsPerPage', $_POST['perPage']);
	$perPage = $_POST['perPage'];
} else {
	$perPage = $_POST['perPage'];
}
list($trackbacks, $paging) = getTrackbacksWithPagingForOwner($owner, $categoryId, $site, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu02.php';
require ROOT . '/lib/piece/owner/contentMeta0Begin.php';
if (strlen($site) > 0) {
?>
                <td>
                  <table cellspacing="0">
                    <tr>
                      <td class="row"><?=_t('사이트명')?>:</td>
                      <td><?=htmlspecialchars($site)?></td>
                    </tr>
                  </table>
                </td>
<?
}
if (strlen($ip) > 0) {
?>
                <td>
                  <table cellspacing="0">
                    <tr>
                      <td class="row">IP:</td>
                      <td><?=htmlspecialchars($ip)?></td>
                    </tr>
                  </table>
                </td>
<?
}
require ROOT . '/lib/piece/owner/contentMeta0End.php';
?>
<script type="text/javascript">
//<![CDATA[

	
	function changeState(caller,value) {
		try {
			var command 	= caller.getAttribute('command');
			var mode 		= caller.getAttribute('mode');
			var name 		= caller.getAttribute('name');
			var id 			= caller.getAttribute('id');
			var blockElement = document.getElementsByName(caller.name+'Block');
			var unblockElement = document.getElementsByName(caller.name+'Unblock');
		
			
			param  	=  '?value='	+ encodeURIComponent(value);		
			param 	+= '&mode=' 	+ mode;
			param 	+= '&command=' 	+ command;

			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/filter/change/" + param);				
			var iconList = document.getElementsByTagName("img");	
			for (var i = 0; i < iconList.length; i++) { 	
				icon = iconList[i];
				if(icon.getAttribute('mode') != mode) continue;
				if(icon.getAttribute('name') != name) continue;
				if(icon.getAttribute('id') != id) {
					request.presetProperty(icon.style, "display", "block");
				} else {
					request.presetProperty(icon.style, "display", "none");
				}
			}
			request.send();
		} catch(e) {
			alert(e.message);
		}
	}

	function deleteTrackback(id) {
		if (!confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/delete/" + id);
		request.onSuccess = function() {
			document.forms[0].submit();
		}
		request.send();
	}
	
	function deleteTrackbacks() {
		try {
			if (!confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?\t')?>"))
				return false;
			var oElement;
			var targets = '';
			for (i = 0; document.forms[0].elements[i]; i ++) {
				oElement = document.forms[0].elements[i];
				if ((oElement.name == "entry") && oElement.checked) {
					targets+=oElement.value+'~*_)';										
				}
			}
			var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/trackback/delete/");
			request.onSuccess = function() {
				document.forms[0].submit();
			}
			request.send("targets=" + targets);
		} catch(e) {
			alert(e.message);
		}
	}
	function checkAll(checked) {
		for (i = 0; document.forms[0].elements[i]; i ++)
			if (document.forms[0].elements[i].name == "entry")
				document.forms[0].elements[i].checked = checked;
	}
//]]>
</script>
            <input type="hidden" name="withSearch" value="" />
            <input type="hidden" name="site" value="" />
            <input type="hidden" name="ip" value="" />
	<table cellspacing="0" style="width:100%; margin-bottom:1px;table-layout: fixed">
		<tr style="background-color:#00A6ED; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif');">
		  <th width="20"><input type="Checkbox"  onclick="checkAll(this.checked);" /></th>
		  <th class="rowHeader" width="70"><?=_t('등록일자')?></th>
		  <th class="rowHeader" width="100"><?=_t('사이트명')?></th>
		  <th class="rowHeader" width="150"><?=_t('분류')?></th>
		  <th class="rowHeader" align="left"><?=_t('제목')?></th>
		  <th class="rowHeader" width="100">IP</th >
		  <th width="30"></th>
		</tr>
<?
$more = false;
foreach ($trackbacks as $trackback) {
	if ($more) {
?>
		<tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif');">
			<td height="1" colspan="7"></td>
		</tr>
<?
	} else
		$more = true;
	requireComponent('Tattertools.Data.Filter');
	$isFilterURL = Filter::isFiltered('url', $trackback['url']);
	$filteredURL = getURLForFilter($trackback['url']);
?>
		<tr style="height:22px;" onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
			<td width="20"><input type="Checkbox" name="entry" value="<?=$trackback['id']?>" /></td>
			<td style="padding:0px 7px 0px 7px; font-size:12px;"><span class="rowDate"><?=Timestamp::formatDate($trackback['written'])?></span></td>
			<td class="row">
				<table border="0">
					<tr>
						<td>
							<img
								name	="sitename<?=md5($filteredURL)?>" 
								id		="sitename<?=md5($filteredURL)?>Block" 
								src		="<?=$service['path']?>/image/owner/blockActive.gif"
								align	="absmiddle"
								alt		=""
								command	="unblock"
								mode 	="url"
								style 	="cursor:pointer; <?=$isFilterURL > 0 ? 'display:block;' : 'display:none;'?>"
								onclick	="changeState(this,'<?=$filteredURL?>')"
							/>
							<img
								name	="sitename<?=md5($filteredURL)?>" 
								id		="sitename<?=md5($filteredURL)?>Unblock" 
								src		="<?=$service['path']?>/image/owner/unblockActive.gif"  	
								align	="absmiddle" 
								alt		=""
								command	="block"
								mode 	="url"
								style 	="cursor:pointer; <?=$isFilterURL > 0 ? 'display:none;' : 'display:block;'?>"					
								onclick	="changeState(this,'<?=$filteredURL?>')"
							/>
						</td>
						<td>
							<a class="rowLink" onclick="document.forms[0].site.value='<?=escapeJSInAttribute($trackback['site'])?>'; document.forms[0].submit()"><?=htmlspecialchars($trackback['site'])?></a>
						</td>
					</tr>
				</table>
			</td>
			<td  align="center">
				<?=$trackback['categoryName']?>
			</td>
			<td class="row"><a class="rowLink" onclick="window.open('<?=$trackback['url']?>')"><?=htmlspecialchars($trackback['subject'])?></a></td>
			<td align="center">
				<a class="rowLink" onclick="document.forms[0].ip.value='<?=escapeJSInAttribute($trackback['ip'])?>'; document.forms[0].submit()"><?=$trackback['ip']?></a>
			</td>
			<td align="right"><a class="rowLink" onclick="deleteTrackback(<?=$trackback['id']?>)"><img src="<?=$service['path']?>/image/owner/delete.gif" alt="<?=_t('삭제')?>"/></a>  &nbsp; </td>
		</tr>
<?
}
?>                         
					</table>
					<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
						<tr>
								<td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;">
									<table cellspacing="0" width="100%">
									  <tr>
										<td><table cellspacing="0">
											<tr>
											  <td style="padding:0px 7px 0px 7px; font-size:12px;"><?=_t('선택한 글을')?></td>
											  <td style="padding-left:3px;">
												<table class="buttonTop" cellspacing="0" onclick="deleteTrackbacks();">
												  <tr>
													<td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
													<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('삭제')?></td>
													<td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
												  </tr>
												</table>
											   </td>
											</tr>
										  </table></td>
										
									  </tr>
									</table>
									<table style="width:100%; margin:7px 0px 5px 0px;">
									  <tr>
										<td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;"  /></td>
									  </tr>
									</table>
									<table cellspacing="0" width="100%">
									  <tr style="height:22px;">
										<td style="padding:0px 7px 0px 7px; font-size:12px;" width="55"><?=_t('총')?><?=$paging['total']?><?=_t('건')?></td>
										<td style="padding:0px 7px 0px 7px; font-size:12px;">
<?
$paging['url'] = 'javascript: document.forms[0].page.value=';
$paging['prefix'] = '';
$paging['postfix'] = '; document.forms[0].submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a class="pageLink" [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
										<td align="right">
											<table cellspacing="0" style="margin-right:5px;">
												<tr>
													<!--<td style="padding:0px 7px 0px 10px; font-size:12px;"><?=_t('이름') . ' | ' . _t('홈페이지 이름') . ' | ' . _t('내용')?></td>-->
													<td style="padding:0px 5px 0px 5px;">
													<input type="text" name="searchInput" value="<?=htmlspecialchars($search)?>" class="text1" style="width:70px" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
													</td>
													<td>
													  	<table class="buttonTop" cellspacing="0" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();">
															<tr>
																<td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
																<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('검색')?></td>
																<td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
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