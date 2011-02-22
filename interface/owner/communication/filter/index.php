<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'mode' => array( array('ip','content','url','name','whiteurl') ,'default'=>null),
		'contentValue' => array('string' , 'default' => null),
		'ipValue' => array('string' , 'default' => null),
		'urlValue' => array('url' , 'default' => null),
		'whiteurlValue' => array('url' , 'default' => null),		
		'nameValue' => array('string' , 'default' => null)
	),
	//'GET' => array(
	//	'history' => array( 'string' , 'default' => null )
	//)
);
require ROOT . '/library/preprocessor.php';
/*if (isset($_POST['ipValue'])) {
	$_POST['mode'] = "ip";
} else if (isset($_POST['urlValue'])) {
	$_POST['mode'] = "url";
} else if (isset($_POST['contentValue'])) {
	$_POST['mode'] = "content";
} else if (isset($_POST['nameValue'])) {
	$_POST['mode'] = "name";
} else if (isset($_POST['whiteurlValue'])) {
	$_POST['mode'] = "whiteurl";
}
if (!empty($_POST['mode'])) {
	$filter = new Filter();
	$filter->type = $_POST['mode'];
	$filter->pattern = $_POST[($_POST['mode'] . 'Value')];
	$filter->add();
	//$history = $_POST['mode'];
}
//if (!empty($_GET['history'])) {
//	$history = $_GET['history'];
//}*/
require ROOT . '/interface/common/owner/header.php';


function printFilterBox($mode, $title) {
	global $service;
	$filter = new Filter();
	$filtersList = array();
	if ($filter->open($mode, 'pattern')) {
		do {
			$filtersList[] = array(0 => $filter->id, 1 => $filter->pattern);
		} while ($filter->shift());
		$filter->close();
	}
?>
									<h3><?php echo $title;?></h3>
									
									<div class="filtering-words">
										<table cellpadding="0" cellspacing="0">
											<tbody id="filterbox-<?php echo $mode;?>"<?php echo (empty($filtersList) ? ' class="empty"' : '');?>>
<?php
	if ($filtersList) {
		$id = 0;
		$count = 0;
		foreach ($filtersList as $key => $value) {
			$entity = $value[1];
			
			$className = ($count % 2) == 1 ? 'even-line' : 'odd-line';
			$className .= ($id == sizeof($filtersList) - 1) ? ' last-line' : '';
?>
												<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
			<td class="content"><span title="<?php echo htmlspecialchars($entity);?>"><?php echo htmlspecialchars(UTF8::lessenAsEm($entity, 26));?></span></td>
													<td class="delete"><a class="delete-button button" href="#void" onclick="deleteFilter(parentNode.parentNode,'<?php echo $mode;?>', '<?php echo urlencode($entity);?>',<?php echo $value[0];?>); return false;" title="<?php echo _t('이 필터링을 제거합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
												</tr>
<?php
			$id++;
			$count++;
		}
	} else {
?>
												<tr <?php echo (empty($filtersList) ? 'id="explainbox-'.$mode.'" ' : '');?>class="odd-line inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
													<td class="empty"><?php echo _t('등록된 내용이 없습니다.');?></td>
												</tr>
<?php
	}
?>
											</tbody>
										</table>
									</div>
									
									<div class="input-field">
<input type="text" class="input-text" name="<?php echo $mode;?>Value" onkeyup="if(event.keyCode=='13') {add('filterbox-<?php echo $mode;?>','<?php echo $mode;?>'); return false;}" />
									</div>
									
									<div class="button-box">
										<input type="submit" class="add-button input-button" value="<?php echo _t('추가하기');?>" onclick="add('filterbox-<?php echo $mode;?>','<?php echo $mode;?>'); return false;" />
									</div>
<?php
}
?>
						<script type="text/javascript">
							//<![CDATA[
								
								function changeColor(caller, color) {
									var target 	= document.getElementById(field) ;
									target.style.backgroundColor=color;
								}
								
								function deleteFilter(caller, mode, value, id) {
									if (!confirm('<?php echo _t('선택된 목록을 필터링에서 제외합니다. 계속 하시겠습니까?');?>')) return false;
									var execute = 'close';
									
									param  = '?mode=' + mode;
									param += '&value=' + value;
									param += '&command=unblock';
									param += '&id=' + id;
									
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/filter/change/" + param);
									request.onSuccess = function() {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('필터를 삭제하였습니다.');?>", "center", "bottom");							
										var parent = caller.parentNode;
										parent.removeChild(caller);
										
										if(parent.rows.length == 0) {	
											var tr = document.createElement("tr");
											tr.className = "odd-line inactive-class";
											tr.setAttribute("onmouseover", "rolloverClass(this, 'over')");
											tr.setAttribute("onmouseout", "rolloverClass(this, 'out')");
											tr.id = "explainbox-"+mode;
											var td = document.createElement("td");
											td.className = "empty";
											td.appendChild(document.createTextNode("<?php echo _t('등록된 내용이 없습니다.');?>"));
											tr.appendChild(td);
											parent.appendChild(tr);
											parent.className = "empty";
										}
									}
									request.onError = function() {
										PM.removeRequest(this);
										PM.showErrorMessage("<?php echo _t('필터를 삭제하지 못했습니다.');?>", "center", "bottom");							
										alert("<?php echo _t('필터링을 삭제하지 못했습니다.');?>");
									}
									PM.addRequest(request, "<?php echo _t('삭제하고 있습니다.');?>");
									request.send();
								}

								function add(callerId,mode) {
									switch (mode) {
										case 'ip':
											target 	= document.getElementById('ipSection').ipValue;
											break;
										case 'url':
											target 	= document.getElementById('urlSection').urlValue;
											break;
										case 'content':
											target 	= document.getElementById('contentSection').contentValue;
											break;
										case 'name':
											target 	= document.getElementById('nameSection').nameValue;
											break;
										case 'whiteurl':
											target 	= document.getElementById('whiteurlSection').whiteurlValue;
											break;
									}

									if(target.value=="") {
										alert("<?php echo _t('내용을 입력해 주십시오.');?>");
										return false;
									}

									if((mode == 'url') || (mode == 'whiteurl')) {
										var reg = new RegExp('^http://', "gi");
										target.value = target.value.replace(reg,'');
									}
									
									if(mode == 'ip') {
										var valid = false;
										if (/\b[0-9.*]+\b/.test(target.value)) {
											var segment = target.value.split('.');
											if (segment.length == 4) {
												var reg = /[0-9]+/, wildcardStarted = false;
												for (var i = 0; i < 4; i++) {
													if (reg.test(segment[i])) {
														if (wildcardStarted || segment[i] > 255 || segment[i] < 0) {
															valid = false;
															break;
														}
													} else {
														if (segment[i] != '*') {
															valid = false;
															break;
														}
														wildcardStarted = true;
													}
													valid = true;
												}
											}
											if (!valid) {
												alert("<?php echo _t('잘못된 IP 주소입니다.');?>");
												return;
											}
										}
									}
									param  = '?mode=' + mode;
									param += '&value=' + target.value;
									
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/filter/change/" + param);
									request.onSuccess = function() {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('필터를 추가하였습니다.');?>", "center", "bottom");
										elementId = this.getText("/response/id");
										caller = document.getElementById(callerId);

										if((caller.rows.length == 1) && (caller.className == "empty")) {
// TODO : Case for EMPTY -> ADD -> DELETE -> ADD..
											caller.removeChild(document.getElementById("explainbox-"+mode));
											caller.className = "filter";
										}
										var tr = document.createElement("tr");
										tr.className = "odd-line inactive-class";
										tr.setAttribute("onmouseover", "rolloverClass(this, 'over')");
										tr.setAttribute("onmouseout", "rolloverClass(this, 'out')");
										var td = document.createElement("td");
										td.className = "content";
										td.appendChild(document.createTextNode(target.value));
										tr.appendChild(td);
										
										td = null;
										td = document.createElement("td");
										td.className = "delete";
										
										var deleteA = document.createElement("A");
										deleteA.className = "delete-button button";
										deleteA.setAttribute("href", "#void");
										deleteA.onclick = function() { deleteFilter(tr,mode,target.value,elementId); return false;};
										deleteA.setAttribute("title", "<?php echo _t('이 필터링을 제거합니다.');?>");
										
										deleteSpan = document.createElement("SPAN");
										deleteSpan.className = "text";
										deleteSpan.innerHTML = "<?php echo _t('삭제');?>";
										deleteA.appendChild(deleteSpan);
										
										td.appendChild(deleteA);
										
										tr.appendChild(td);
										caller.appendChild(tr);
									}
									request.onError = function() {
										PM.removeRequest(this);
										PM.showErrorMessage("<?php echo _t('필터를 추가하지 못했습니다.');?>", "center", "bottom");
										alert("<?php echo _t('예외를 추가하지 못했습니다.');?>");
									}
									PM.addRequest(request, "<?php echo _t('추가하고 있습니다.');?>");
									request.send();
									
								}
								
							//]]>
						</script>
						
						<div id="part-communication-filter" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('필터를 설정합니다');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('댓글, 걸린글, 리퍼러가 입력될 때 아래의 단어가 포함되어 있으면 알림창을 띄우고 휴지통으로 보냅니다.');?></p>
							</div>
							
							<div class="data-inbox">
								<form id="ipSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/communication/filter">
<?php echo printFilterBox('ip', _t('IP 필터링'));?>
								</form>
										
								<hr class="hidden" />
										
								<form id="urlSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/communication/filter">
<?php echo printFilterBox('url', _t('홈페이지 필터링'));?>
								</form>
								
								<hr class="hidden" />
								
								<form id="contentSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/communication/filter">
<?php echo printFilterBox('content', _t('본문 필터링'));?>
								</form>
								
								<hr class="hidden" />
								
								<form id="nameSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/communication/filter">
<?php echo printFilterBox('name', _t('이름 필터링'));?>
								</form>
							</div>
						</div>
						
						<div id="part-communication-whitelist" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('필터 예외 항목을 설정합니다');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('필터 처리시 예외로 처리할 항목입니다. 예외 처리 항목은 필터보다 우선적으로 처리되므로, 주의해서 추가해야 합니다.');?></p>
							</div>
							
							<div class="data-inbox">
								<form id="whiteurlSection" class="section" method="post" action="<?php echo $blogURL;?>/owner/communication/filter">
<?php echo printFilterBox('whiteurl', _t('예외 처리할 홈페이지'));?>
								</form>
								
								<hr class="hidden" />
							</div>
						</div>

<?php
require ROOT . '/interface/common/owner/footer.php';
?>
