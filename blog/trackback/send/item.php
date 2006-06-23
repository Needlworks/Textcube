<?
define('ROOT', '../../..');
require ROOT . '/lib/include.php';
$entry = getEntry($owner, $suri['id']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?=_t('트랙백 전송')?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?=$service['path']?>";
			var blogURL = "<?=$blogURL?>";
			var adminSkin = "<?=$service['adminSkin']?>";
			
			function onclick_send(form) {
				trim_all(form);
				if (isNull(form.url,"<?=_t('전송할 주소를 입력하세요')?>")) return false;
				if (!confirm("<?=_t('지정하신 주소로 글을 보내시겠습니까?')?>")) return false;
				form.submit();
			}

			function onclick_delete(form, num) {
				trim_all(form);
				if (!confirm("<?=_t('지정하신 관련글(트랙백) 로그를 삭제하시겠습니까?')?>")) return false;
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
						var str = '<table width="100%" cellpadding="5" cellspacing="0">\n';
						str += '	<thead>\n';
						str += '		<tr>\n';
						str += '			<td class="log"><span class="text"><? echo _t('전송로그')?></span></td>\n';
						str += '			<td class="date"><span class="text"><? echo _t('날짜')?></span></td>\n';
						str += '			<td class="delete"><span class="text"><? echo _t('삭제')?></span></td>\n';
						str += '		</tr>\n';
						str += '	</thead>\n';
						
						for (var i=0; i<resultRow.length-1 ; i++) {
							field = resultRow[i].split(',');
							str += '	<tbody>\n';
							str += '		<tr id="trackbackLog_'+field[0]+'">\n';
							str += '			<td class="log">'+field[1]+'</td>\n'
							str += '			<td class="date">'+field[2]+'</td>\n'
							str += '			<td class="delete"><a class="delete-button button" href="#void" onclick="removeTrackbackLog('+field[0]+','+id+');"><span class="text"><?=_t('삭제')?></span></a></td>\n'
							str += '		</tr>\n';
							str += '	</tbody>\n';
						}			
						str += '</table>\n';
					}
					document.getElementById('logs_'+id).innerHTML = str;
				}
				request.send();
			}
			
			function removeTrackbackLog(id,entry) {
				if(confirm("<?=_t('선택된 트랙백을 삭제합니다. 계속하시겠습니까?')?>")) {
					var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/entry/trackback/log/remove/" + id);
					request.onSuccess = function() {
						showTrackbackSender(entry);
					}
					request.onError = function() {
						alert('<?=_t('실패 했습니다.')?>');
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
		//]]>
	</script>
	<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
	<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
</head>
<body>
	<form name="trackback" method="post" action="<?=$suri['url']?>">
	
		<div id="trackback-box">
			<img src="<?=$service['path']?>/image/logo_CommentPopup.gif" alt="태터툴즈 로고" />
			
			<div class="title"><span class="text"><?=_t('트랙백을 전송합니다')?></span></div>
	      	<div id="command-box">
	      		<dl class="title-line">
	      			<dt><span class="label"><?=_t('제목')?></span><span class="divider"> | </span></dt>
	      			<dd><?=htmlspecialchars($entry['title'])?></dd>
	      		</dl>
				<dl class="input-line">
					<dt><label for="url"><?=_t('주소입력')?></label><span class="divider"> | </span></dt>
					<dd>
						<input type="text" id="url" class="text-input" name="url" onkeydown="if (event.keyCode == 13) { sendTrackback(<?=$suri['id']?>); return false;}" />
						<input type="button" class="button-input" name="Submit" value="<?=_t('전송')?>" onclick="sendTrackback(<?=$suri['id']?>)" />				
					</dd>
				</dl>
				
				<div id="logs_<?=$suri['id']?>"></div>
			</div>
			
			<div class="button-box">
				<input type="button" class="button-input" value="<?=_t('닫기')?>" onclick="window.close()" />
			</div>
		</div>
	</form>
</body>
</html>
