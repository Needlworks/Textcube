<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireComponent('Tattertools.Data.Filter');
$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$name = empty($_POST['name']) ? '' : $_POST['name'];
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
list($comments, $paging) = getCommentsWithPagingForOwner($owner, $categoryId, $name, $ip, $search, $suri['page'], $perPage);
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu01.php';
require ROOT . '/lib/piece/owner/contentMeta0Begin.php';
if (strlen($name) > 0) {
?>
                <td>
                  <table cellspacing="0">
                    <tr>
                      <td class="row"><?=_t('이름')?>:</td>
                      <td><?=htmlspecialchars($name)?></td>
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
	function deleteComment(id) {
		if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속하시겠습니까?\t')?>"))
			return;
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/comment/delete/" + id);
		request.onSuccess = function () {
			document.forms[0].submit();
		}
		request.send();
	}
	function deleteComments() {	
		if (!confirm("<?=_t('선택된 댓글을 삭제합니다. 계속하시겠습니까?\t')?>"))
			return false;
		var oElement;
		var targets = '';
		for (i = 0; document.forms[0].elements[i]; i ++) {
			oElement = document.forms[0].elements[i];
			if ((oElement.name == "entry") && oElement.checked) {
				targets += oElement.value +'~*_)';
			
			}
		}
		var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/entry/comment/delete/");
		request.onSuccess = function() {
			document.forms[0].submit();
		}
		request.send("targets=" + targets);
	}
	
	function checkAll(checked) {
		for (i = 0; document.forms[0].elements[i]; i ++)
			if (document.forms[0].elements[i].name == "entry")
				document.forms[0].elements[i].checked = checked;
	}
	
	function changeState(caller, value, no) {
		try {			
			var command 	= caller.getAttribute('command');
			var mode 		= caller.getAttribute('mode');
			var name 		= caller.getAttribute('name');
			var id 			= caller.getAttribute('id');
			var blockElement = document.getElementsByName(caller.name+'Block');
			var unblockElement = document.getElementsByName(caller.name+'Unblock');
			
			//if(caller.src.indexOf("Active.gif")!=-1) return;

			param  	=  '?value='	+ encodeURIComponent(value);
			param 	+= '&mode=' 	+ mode;
			param 	+= '&command=' 	+ command;
			param 	+= '&id=' 	+ no;

			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/filter/change/" + param);
			var iconList = document.getElementsByTagName("img");	
			for (var i = 0; i < iconList.length; i++) {
				icon = iconList[i];
				if(icon.getAttribute('mode') != mode) continue;
				if(icon.getAttribute('name').toLowerCase() != name.toLowerCase()) continue;
				if(icon.getAttribute('id').toLowerCase() != id.toLowerCase())
					request.presetProperty(icon.style, "display", "block");
				else
					request.presetProperty(icon.style, "display", "none");
			}
			request.send();
		} catch(e) {
			alert(e.message);
		}
	}
//]]>
</script>
            <input type="hidden" name="withSearch" value="" />
            <input type="hidden" name="name" value="" />
            <input type="hidden" name="ip" value="" />
						<table cellspacing="0" style="width:100%; margin-bottom:1px;table-layout:fixed">
                            <tr style="background-color:#00a6ed; height:24px; background-image: url('<?=$service['path']?>/image/owner/subTabCenter.gif');">
								<th width="20"><input type="checkbox" onclick="checkAll(this.checked);" /></th>
								<th class="rowHeader" align="left" width="70"><?=_t('등록일자')?></th>
								<th class="rowHeader" align="left" width="70" nowrap="nowrap"><?=_t('이름')?></th>
								<th class="rowHeader" align="left"><?=_t('내용')?></th>
								<th class="rowHeader" align="left" width="110">IP</th>
								<th class="rowHeader" align="left" width="15"></th>
                            </tr>
<?
$more = false;
foreach ($comments as $comment) {
	if ($more) {
?>
							<tr style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif');">
                              <td height="1" colspan="6"></td>
                            </tr>
<?
	}
	$more = true;
?>
							<tr style="height:22px" align="center">
								<td width="20">
								  <input type="checkbox" name="entry" value="<?=$comment['id']?>"/>
								</td>
								<td><?=Timestamp::formatDate($comment['written'])?></td>
								<td align="left" style="padding:0px 10px 0px 10px;">
<?
	$filter = new Filter();
	if (Filter::isFiltered('name', $comment['name']))
		$isNameFiltered = true;
	else
		$isNameFiltered = false;
?>
									<table>
										<tr>
											<td>
												
												<img
													name	="name<?=htmlspecialchars(strtolower($comment['name']))?>" 
													id		="name<?=htmlspecialchars(strtolower($comment['name']))?>block" 
													src		="<?=$service['path']?>/image/owner/blockActive.gif"
													align	="absmiddle"
													alt		=""
													command	="unblock"
													mode 	="name"
													style 	="cursor:pointer; <?=($isNameFiltered ? 'display:block' : 'display:none')?>"
													onclick	="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>')"
												/>
												<img
													name	="name<?=htmlspecialchars($comment['name'])?>" 
													id		="name<?=htmlspecialchars($comment['name'])?>unblock" 
													src		="<?=$service['path']?>/image/owner/unblockActive.gif"  	
													align	="absmiddle" 
													alt		=""
													command	="block"
													mode 	="name"
													style 	="cursor:pointer; <?=($isNameFiltered ? 'display:none' : 'display:block')?>"					
													onclick	="changeState(this,'<?=escapeJSInAttribute($comment['name'])?>', '<?=$filter->id?>')"
												/>
											</td>
											<td nowrap="nowrap">
												<a class="rowLink" onclick="document.forms[0].name.value='<?=escapeJSInAttribute($comment['name'])?>'; document.forms[0].submit()">
												<?=htmlspecialchars($comment['name'])?></a>
											</td>
										</tr>
									</table>
								</td>
								<td>
								  <table align="left" border="0">
									<tr align="left" >
<?
	if ($comment['parent']) {
?>
                      			<td align="right" valign="top" width="20"> <img src="<?=$service['path']?>/image/owner/icon_arrow_guest.gif" alt="" /> 								</td>
<?
	}
?>
								<td class="row" style="word-break: break-all">
									<p>
									<a class="rowLink" href="<?="$blogURL/{$comment['entry']}#comment{$comment['id']}"?>" target="_self"> 
										<strong><?=($comment['title'] == '' ? '' : $comment['title'])?>
<?
	if ($comment['title'] != '' && $comment['parent'] != '')
		echo ' | ';
?>
								<?=(empty($comment['parent']) ? '' : $comment['parentName'] . _t('님의 댓글에 대한 댓글'))?></strong>
								<?=((!empty($comment['title']) || !empty($comment['parent'])) ? '</br>' : '')?>
									</a>
								<?=htmlspecialchars($comment['comment'])?>
								</p>
							 	</td>
							</tr>
						</table>
							</td>
							<td align="left">
								<table>
									<tr>
										<td>
<?
	$filter = new Filter();
	if (Filter::isFiltered('ip', $comment['ip']))
		$isIpFiltered = true;
	else
		$isIpFiltered = false;
?>
											<img
												name	="name<?=urlencode($comment['ip'])?>" 
												id		="name<?=urlencode($comment['ip'])?>block" 
												src		="<?=$service['path']?>/image/owner/blockActive.gif"
												align	="absmiddle"
												alt		=""
												command	="unblock"
												mode 	="ip"
												style 	="cursor:pointer;<?=$isIpFiltered ? 'display:hand;' : 'display:none;'?>"
												onclick	="changeState(this,'<?=urlencode($comment['ip'])?>', '<?=$filter->id?>')"
											/>
											<img
												name	="name<?=urlencode($comment['ip'])?>" 
												id		="name<?=urlencode($comment['ip'])?>unblock" 
												src		="<?=$service['path']?>/image/owner/unblockActive.gif"  	
												align	="absmiddle" 
												alt		=""
												command	="block"
												mode 	="ip"
												style 	="cursor:pointer; <?=$isIpFiltered ? 'display:none;' : 'display:block;'?>"					
												onclick	="changeState(this,'<?=urlencode($comment['ip'])?>', '<?=$filter->id?>')"
											/>
										</td>
										<td>
											<a class="rowLink" onclick="document.forms[0].ip.value='<?=escapeJSInAttribute($comment['ip'])?>'; document.forms[0].submit()"><?=$comment['ip']?></a>
										</td>
									</tr>
								</table>
							</td>
							<td>
								<a class="rowLink" onclick="deleteComment(<?=$comment['id']?>)"><img src="<?=$service['path']?>/image/owner/delete.gif" alt="<?=_t('삭제')?>"/></a>
							</td>
				  </tr>
<?
}
?>               
                          </table>		 
                          <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
                            <tr>
                              <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;"><table cellspacing="0" width="100%">
                                  <tr>
                                    <td><table cellspacing="0">
                                        <tr>
                                          <td style="padding:0px 7px 0px 7px; font-size:12px;"><?=_t('선택한 글을')?></td>
                                          <td style="padding-left:3px;">
                                            <table class="buttonTop" cellspacing="0" onclick="deleteComments();">
                                              <tr>
                                                <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                                                <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('삭제')?></td>
                                                <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                                              </tr>
                                            </table>
                                           </td>
                                        </tr>
                                      </table></td>
                                    <td align="right" style="padding-right:5px;"></td>
                                  </tr>
                                </table>
                                <table style="width:100%; margin:7px 0px 5px 0px;">
                                  <tr>
                                    <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;"  /></td>
                                  </tr>
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
                                    <td align="right"><table cellspacing="0" style="margin-right:5px;">
                                        <tr>
                                          <!--<td style="padding:0px 7px 0px 10px; font-size:12px;" nowrap="nowrap"><?=_t('이름') . ' | ' . _t('홈페이지 이름') . ' | ' . _t('내용')?></td>-->
                                          <td style="padding:0px 5px 0px 5px;">
										  	<input type="text" name="search" value="<?=htmlspecialchars($search)?>" class="text1" style="width:70px" onkeydown="if (event.keyCode == '13') { document.forms[0].withSearch.value = 'on'; document.forms[0].submit(); }" />
										  </td>
                                          <td>
                                              <table class="buttonTop" cellspacing="0" onclick="document.forms[0].withSearch.value = 'on'; document.forms[0].submit();">
                                              <tr>
                                                <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                                                <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');" nowrap="nowrap"><?=_t('검색')?></td>
                                                <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                                              </tr>
                                            </table>
                                            </td>
                                          <td></td>
                                        </tr>
                                      </table></td>
                                  </tr>
                                </table></td>
                            </tr>
                          </table>
	
<?
require ROOT . '/lib/piece/owner/footer.php';
?>