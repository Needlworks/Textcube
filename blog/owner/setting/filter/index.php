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
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu52.php';

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
<table cellspacing="1" width="100%" >
	<tr >
	  <td valign="top" width="200" > <?=$title?>
		<div style="margin:2px 0 2px 0;padding: 5px 5px 5px 5px;color: #000000; background-color:#FCFCFC; border-width:1px; border-color:#A0A0C0; border-style:solid;">
		  <table width="100%" cellpadding="0" cellspacing="0" border="0" > 
<?
	if ($filtersList) {
		$id = 0;
		foreach ($filtersList as $key => $value) {
			$entity = $value[1];
?>
				<tr  onmouseover="this.style.backgroundColor='#EEEEEE'" onmouseout="this.style.backgroundColor='white'">
					<td width="*">
<?
			$filteredName = utf8Lessen($entity, 30);
			if (strlen($filteredName) < strlen($entity))
				$filteredName = '<span title = "' . $entity . '">' . $filteredName . '</span>';
			echo $filteredName;
?>																					
	</td>
	
	<td align="left" width="13"><a class="rowLink" onclick="deleteFilter(parentNode.parentNode,'<?=$mode?>', '<?=urlencode($entity)?>',<?=$value[0]?>);"><img src="<?=$service['path']?>/image/owner/delete.gif" align="absmiddle"/></a></td>
</tr>
<?
			$id++;
		}
	} else {
?>
	<tr><td width="*"><?=_t('등록된 내용이 없습니다')?></td></tr>
	<?
	}
?>
	</table>
  </div>
	<table width="100%" align="right" cellpadding="0" cellspacing="0" border="0" >
		<tr>
			<td><input style="width:97%" name="<?=$mode?>Value" type="text" id="<?=$mode?>" onkeyup="if(event.keyCode=='13') {add('<?=$mode?>')}"/></td>
		</tr>
		<tr>
			<td > 
				<table class="buttonTop" cellspacing="0" onclick="add('<?=$mode?>')" align="center">
					<tr>
					  <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
					  <td 
						class="buttonTop" 
						style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('추가하기')?></td>
					  <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
					</tr>
				</table> 
			</td>
		</tr>	
			
		</tr>
  </table>
</td>
</tr>
</table>
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
		if (!confirm('<?=_t('선택된 목록을 필터링에서 제외합니다. 계속하시겠습니까?\t')?>')) return false;
		var execute = 'close';
		
		param  = '?mode=' 	+ mode;
		param += '&value=' 	+ value;
		param += '&command=unblock';
		param += '&id='+id;
		

		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/filter/change/" + param);
		request.onSuccess = function() {
			var parent = caller.parentNode;
			parent.removeChild(caller);
			if(parent.childNodes.length == 0) {	
				var tr = document.createElement("tr");
				var td = document.createElement("td");
				td.appendChild(document.createTextNode("<?=_t('등록된 내용이 없습니다')?>"));
				tr.appendChild(td);
				parent.appendChild(tr);
			}
		}
		request.send();
	}
	function add(mode) {
	
		var target 	= document.getElementById(mode) ;
		
		if(target.value=="") {
			alert("<?=_t('내용을 입력해 주세요')?>");
			return false;
		}
		
		if(mode == 'ip') {
			reg = /\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/;
			if(!reg.test(target.value)) {
				alert("<?=_t('잘못된 IP주소 입니다')?>");
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
<input type="hidden" name="mode" id="mode" />
<table cellspacing="0" style="width:100%; background-color:#FFFFFF;">
  <tr>
		<td valign="top" style="height:50px; padding:5px 15px 15px 15px;">
			<table cellspacing="0" width="100%">
				<tr>
					<td>
						<table cellspacing="0" style="width:100%; height:28px;">
							<tr>
							<td style="width:18px;"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
							<td style="padding:3px 0px 0px 4px;"><?=_t('필터링을 설정합니다. 댓글, 트랙백, 리퍼러가 입력될 때 아래의 단어가 포함되어 있으면 알림창을 띄우거나 무시합니다')?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			
			<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
				<tr>
					<td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;">
						<table cellspacing="0" style="margin-top:5px;" width="100%" >
							<tr valign="top">
								<td width="8"></td>
								<td valign="top">									
									<?=printFilterBox('ip', _t('IP 필터링'))?>
								</td>
								<td width="8"></td>
								<td valign="top">
									<?=printFilterBox('url', _t('홈페이지 필터링'))?>
								</td>
								<td width="8"></td>
							
								<td valign="top">
									<?=printFilterBox('content', _t('본문 필터링'))?>
								</td>
								<td width="8"></td>
								<td valign="top">	
									<?=printFilterBox('name', _t('이름 필터링'))?>
								</td>
								<td width="8"></td>
							</tr>
							<tr>
								<td>
									<table style="width:100%; margin:7px 0px 5px 0px;">
										<tr>
											<td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle1.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
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
<table cellspacing="0" style="width:100%;">
  <tr>
	<td style="width:7px; height:7px;"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftBottom.gif" /></td>
	<td style="background-color:#FFFFFF;"><img alt="" width="1" height="1" src="<?=$service['path']?>/image/owner/spacer.gif" /></td>
	<td style="width:7px; height:7px;"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightBottom.gif" /></td>
  </tr>
</table>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>