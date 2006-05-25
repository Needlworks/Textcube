<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireComponent('Tattertools.Data.Filter');
if (!empty($_POST['mode']) && !empty($_POST[($_POST['mode'] . 'Value')])) {
	$filter = new Filter();
	$filter->type = $_POST['mode'];
	$filter->pattern = $_POST[($_POST['mode'] . 'Value')];
	$filter->add();
	$history = $_POST['mode'];
}
if (!empty($_GET['history'])) {
	$history = $_GET['history'];
}
require ROOT . '/lib/piece/owner/header9.php';
require ROOT . '/lib/piece/owner/contentMenu92.php';

function printFilterBox($mode, $title) {
	global $service;
	$filter = new Filter();
	$filtersList = array();
	if ($filter->open($mode)) {
		do {
			$filtersList[] = array(0 => $filter->id, 1 => $filter->pattern);
		} while ($filter->shift());
		$filter->close();
	}
?>
												<div class="title"><span><?=$title?></span></div>
												
												<table cellpadding="0" cellspacing="0"> 
<?
	if ($filtersList) {
		$id = 0;
		foreach ($filtersList as $key => $value) {
			$entity = $value[1];
?>
													<tr class="inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
														<td class="content"><span title="<?=escapeJSInAttribute($entity)?>"><?=UTF8::lessenAsEm($entity, 30)?></span></td>
														<td class="delete"><a class="delete-button button" href="#void" onclick="deleteFilter(parentNode.parentNode,'<?=$mode?>', '<?=urlencode($entity)?>',<?=$value[0]?>);" title="<?=_t('이 필터링을 제거합니다.')?>"><span><?=_t('삭제')?></span></a></td>
													</tr>
<?
			$id++;
		}
	} else {
?>
													<tr class="inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
														<td><?=_t('등록된 내용이 없습니다.')?></td>
													</tr>
<?
	}
?>
												</table>
												
												<div class="input-field">
													<input type="text" id="<?=$mode?>" class="text-input" name="<?=$mode?>Value" onkeyup="if(event.keyCode=='13') {add('<?=$mode?>')}" />
												</div>
												
												<div class="button-box">
													<a class="add-button button" href="#void" onclick="add('<?=$mode?>')"><span><?=_t('추가하기')?></span></a>
												</div>
<?
}
?>
									<script type="text/javascript">
										//<![CDATA[
											function edit(caller, table ,column,  field) {			
												/*
												var target 	= document.getElementById(field) ;
												var oldValue = target.value;
												if(caller.innerHTML == "<?=_t('완료')?>")
												{
													
												}
												 
												caller.innerHTML = "<?=_t('완료')?>";
												
												target.select();
												
												target.onkeydown = function() {
													if (event.keyCode == '13') {
														target.blur();
													}
												}
												
												target.onblur=function() {
													param = '?table=' 	+ table;
													param += '&column=' + column;
													param += '&newValue=' 	+ encodeURI(target.value);
													param += '&oldValue=' 	+ encodeURI(oldValue);
													
														
													if(	target.value != oldValue) {
														var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/filter/modify/" + param);
														request.onSuccess = function() {
															caller.innerHTML = "<?=_t('수정')?>";
															this.onblur = '';
															this.onkeydown = '';
														}
														request.send();
												
														return false;
													} 
												}
												*/
											}
											
											
											function changeColor(caller, color) {
												var target 	= document.getElementById(field) ;
												target.style.backgroundColor=color;
											}
											
											function deleteFilter(caller, mode, value, id) {
												if (!confirm('<?=_t('선택된 목록을 필터링에서 제외합니다. 계속하시겠습니까?')?>')) return false;
												var execute = 'close';
												
												param  = '?mode=' 	+ mode;
												param += '&value=' 	+ value;
												param += '&command=unblock';
												param += '&id='+id;
												

												var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/trash/filter/change/" + param);
												request.onSuccess = function() {
													var parent = caller.parentNode;
													parent.removeChild(caller);
													if(parent.childNodes.length == 0) {	
														var tr = document.createElement("tr");
														var td = document.createElement("td");
														td.appendChild(document.createTextNode("<?=_t('등록된 내용이 없습니다.')?>"));
														tr.appendChild(td);
														parent.appendChild(tr);
													}
												}
												request.send();
											}
											function add(mode) {
											
												var target 	= document.getElementById(mode) ;
												
												if(target.value=="") {
													alert("<?=_t('내용을 입력해 주세요.')?>");
													return false;
												}
												
												if(mode == 'ip') {
													reg = /\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/;
													if(!reg.test(target.value)) {
														alert("<?=_t('잘못된 IP주소 입니다.')?>");
														return;
													};
												}
												document.getElementById('mode').value = mode;
												document.forms[0].submit();
											}

											/* deprecate
											function add(target, table, column, field) {
												
												
												var target = document.getElementById(target);

												try {
													
												
													tr = document.createElement("tr");
													tr.className ="rowLink";
													td 			= document.createElement("td");
													input 		= document.createElement("input");
													
													input.id    = "filterById3";
													input.type	= "text";
													input.style.width = "70px";
													input.style.borderStyle = 'none';
													input.style.borderColor = '#FFFFFF';
													input.value = field.value;
													
													td.appendChild(input);		
													tr.appendChild(td);
															
													td = document.createElement("td");
													
													
													td.className = "rowLink";
													
													td.border=1;
													
													a = document.createElement("div");
													
													
													text = document.createTextNode("<?=_t('수정')?>");		
													a.appendChild(text);		
													
													td.appendChild(a);
													tr.appendChild(td);
													
													td = document.createElement("td");
													text = document.createTextNode("<?=_t('삭제')?>");
													td.appendChild(text);		
													tr.appendChild(td);
													
													target.appendChild(tr);
													field.value = "";
													field.select();
												
													alert(target.innerHTML);
												}catch (e) {
													alert(e.message+"  "+e.name);
												}
											}
											*/
<?
if (!@is_null($history)) {
?>
											this.onload =  function()
											{
												var target = document.getElementById('<?=$history?>');
												target.select();
											}
<?
}
?>
										//]]>
									</script>
									
									<input type="hidden" id="mode" name="mode" />
									
									<div id="part-trash-filter" class="part">
										<h2 class="caption" title="댓글, 트랙백, 리퍼러가 입력될 때 아래의 단어가 포함되어 있으면 알림창을 띄우거나 무시합니다."><span class="main-text"><?=_t('필터링을 설정합니다')?></span></h2>
										
										<div class="data-inbox">
											<div id="ip-section" class="section">
<?=printFilterBox('ip', _t('IP 필터링'))?>
											</div>
											
											<hr class="hidden" />
											
											<div id="homepage-section" class="section">
<?=printFilterBox('url', _t('홈페이지 필터링'))?>
											</div>
											
											<hr class="hidden" />
											
											<div id="content-section" class="section">
<?=printFilterBox('content', _t('본문 필터링'))?>
											</div>
											
											<hr class="hidden" />
											
											<div id="name-section" class="section">
<?=printFilterBox('name', _t('이름 필터링'))?>
											</div>
										</div>
									</div>
									
									<div class="clear"></div>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>
