<?
define('ROOT', '../../..');
require ROOT . '/lib/include.php';
$entry = getEntry($owner, $suri['id']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title><?=_t('트랙백')?></title>
<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
<script type="text/javascript">
	function onclick_send(form) {
		trim_all(form);
		if (isNull(form.url,"<?=_t('전송할 주소를 입력하세요')?>")) return false;
		if (!confirm("<?=_t('지정하신 주소로 글을 보내시겠습니까?\t')?>")) return false;
		form.submit();
	}

	function onclick_delete(form, num) {
		trim_all(form);
		if (!confirm("<?=_t('지정하신 관련글(트랙백) 로그를 삭제하시겠습니까?\t')?>")) return false;
		form.mode.value = 'delete';
		form.exenum.value = num;
		form.submit();
	}
	
	function sendTrackback(id) {
		try {
			var trackbackField = document.getElementById('url');
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
			request.onSuccess = function() {
				showTrackbackSender(id);
				trackbackField.value ='';
				trackbackField.select();
			}
			request.onError = function() {
				alert("<?=_t('트랙백 전송에 실패하였습니다.')?>");
			}
			request.send();
		} catch(e) {
			alert(e.message);
		}
	}
	
	function showTrackbackSender(id) {
		var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/log/" + id);
		request.onSuccess = function() {
			resultRow = this.getText("/response/result").split('*');
			if (resultRow.length == 1) {
				var str ='';
			} else {
				var str='<table width="100%">';
				for (var i=0; i<resultRow.length-1 ; i++) {
					field = resultRow[i].split(',');
					str += '<tr id="trackbackLog_'+field[0]+'">\n';
					str += '	<td style="color:#333; font-size:11px; font-family:Verdana;">'+field[1]+'</td>\n'
					str += '	<td align="center" width="120" style="color:#333; font-size:11px; font-family:Verdana">'+field[2]+'</td>\n'
					str += '	<td class="pointerCursor" onclick="removeTrackbackLog('+field[0]+','+id+');"><img src="<?=$service['path']?>/image/owner/deleteX.gif" alt="<?=_t('삭제')?>"/></td>\n'
					str += '</tr>\n';
					str += '<tr height="1">\n';
					str += '<td colspan="3" bgcolor="#9DCAFB"></td>\n';
					str += '</tr>\n';
					
				}			
				str+='</table><br />';
			}
			document.getElementById('logs_'+id).innerHTML = str;
		}
		request.send();
	}
	
	function removeTrackbackLog(id,entry) {
		if(confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?\t')?>")) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/log/remove/" + id);
			request.onSuccess = function() {
				showTrackbackSender(entry);
			}
			request.onError = function() {
				alert('<?=_t('실패 했습니다')?>');
			}
			request.send();
		}
	}

	function resize() {
		window.resizeTo(document.body.clientWidth,document.body.clientHeight);
	}

	window.onload = function() {
		showTrackbackSender(<?=$suri['id']?>);
		//resize();
	}
</script>
</head>
<body style="margin:0; padding:0" bgcolor="#ffffff">
<form name="trackback" method="post" action="<?=$suri['url']?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr height="1">
    <td bgcolor="#6989AC"></td>
  </tr>
  <tr>
    <td bgcolor="#9DCAFB" height="3"></td>
  </tr>
  <tr>
    <td style="padding:10px;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td style="color:#5681B0; font-weight:bold; font-size:13px;"><img src="<?=$service['path']?>/image/icon_PopupTitle.gif" width="15" height="15" hspace="3" vspace="3" align="absmiddle" /> <?=_t('트랙백을 전송합니다')?></td>
        <td align="right" style="padding-bottom:5px;">
		<?
?>
				<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" />
		<?
?>		
		</td>
      </tr>
    </table>
      <table width="100%" border="0" cellspacing="1" bgcolor="#9DCAFB">
        <tr>
          <td bgcolor="#E0EFFF" style="padding: 10px">
		  <table border="0" cellpadding="2" cellspacing="0">
            <tr>
              <td height="24" style="text-align:right; color:#2A64A3; font-size:12px; padding: 2px"><?=_t('제목')?> : </td>
              <td style=" font-size:12px; padding: 2px"><?=htmlspecialchars($entry['title'])?></td>
              <td style=" font-size:12px; padding: 2px">&nbsp;</td>
            </tr>
            <tr>
              <td height="24" style="text-align:right; color:#2A64A3; font-size:12px; padding: 2px"><?=_t('주소입력')?> : </td>
              <td style=" font-size:12px; padding: 2px"><input type="text" id="url" style="border: 1px solid #9DCAFB; height:18px; width:360px;"/></td>
              <td style=" font-size:12px; padding: 2px"><input type="button" value="submit" style="border: 1px solid #6297D1; background-color:#83AFE0; color:#fff; width:50px; height:20px; font-size:10px; font-family:tahoma; font-weight:bold;" onclick="sendTrackback(<?=$suri['id']?>)"/></td>
            </tr>
          </table>

            <table width="100%" border="0" cellpadding="5" cellspacing="0" style="margin-top: 20px">
              <tr>
                <td bgcolor="#9DCAFB" height="2" colspan="3"></td>
              </tr>						
              <tr>
                <td bgcolor="#c9e3ff" style="color:#333; font-size:11px; font-family:Verdana; padding: 5px"><span style="width:65px; text-align:right; color:#2A64A3; font-size:12px;"> <strong><?=_t('전송로그')?></strong></span></td>
                <td align="center" bgcolor="#c9e3ff" style="color:#333; font-size:11px; font-family:Verdana; padding: 5px">&nbsp;</td>
                <td bgcolor="#c9e3ff">&nbsp;</td>
              </tr>						
              <tr height="1">
                <td colspan="3" bgcolor="#9DCAFB"></td>
              </tr>
			  <tr>
			  	<td colspan="3" id="logs_<?=$suri['id']?>"></td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	  <div style="text-align: center"><input onclick="window.close()" type="button" value="<?=_t('닫기')?>"  style="border: 1px solid #6297D1; background-color:#83AFE0; color:#fff; width:180px; height:20px; font-size:11px; font-family:tahoma; font-weight:bold; margin-top: 10px" /></div>
	  </td>
  </tr>
</table>
</form>
</body>
</html>
