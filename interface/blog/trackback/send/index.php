<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_ADMINPANEL__',true);

require ROOT . '/library/preprocessor.php';
$entry = getEntry($blogid, $suri['id']);
if (is_null($entry)) {
	Respond::NotFoundPage();
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo _text('글걸기 시도');?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/popup-trackback.css" />
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $service['resourcepath'];?>/script/common2.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/EAF4.js"></script>
	<!-- script type="text/javascript" src="<?php echo $service['resourcepath'];?>/script/EAF4.js"></script -->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path'];?>";
			var blogURL = "<?php echo $blogURL;?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
			
			function onclick_send(form) {
				trim_all(form);
				if (isNull(form.url,"<?php echo _text('전송할 주소를 입력하세요');?>")) return false;
				if (!confirm("<?php echo _text('지정하신 주소로 글을 보내시겠습니까?');?>")) return false;
				form.submit();
			}

			function onclick_delete(form, num) {
				trim_all(form);
				if (!confirm("<?php echo _text('지정하신 걸린글 기록을 삭제하시겠습니까?');?>")) return false;
				form.mode.value = 'delete';
				form.exenum.value = num;
				form.submit();
			}
			
			function sendTrackback(id) {
				try {
					var trackbackField = document.getElementById('url');
					var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/trackback/send/" + id + "?url=" + encodeURIComponent(trackbackField.value));
					request.onSuccess = function() {
						showTrackbackSender(id);
						trackbackField.value ='';
						trackbackField.select();
					}
					request.onError = function() {
						alert("<?php echo _text('글을 걸 수 없었습니다.');?>");
					}
					request.send();
				} catch(e) {
					alert(e.message);
				}
			}
			
			function showTrackbackSender(id) {
				var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/trackback/log/" + id);
				request.onSuccess = function() {
					resultRow = this.getText("/response/result").split('*');
					if (resultRow.length == 1) {
						var str ='';
					} else {
						var str = '<table width="100%" cellpadding="5" cellspacing="0">\n';
						str += '	<thead>\n';
						str += '		<tr>\n';
						str += '			<th class="log"><span class="text"><?php echo _text('전송로그');?></span></th>\n';
						str += '			<th class="date"><span class="text"><?php echo _text('날짜');?></span></th>\n';
						str += '			<th class="delete"><span class="text"><?php echo _text('삭제');?></span></th>\n';
						str += '		</tr>\n';
						str += '	</thead>\n';
						
						for (var i=0; i<resultRow.length-1 ; i++) {
							field = resultRow[i].split(',');
							str += '	<tbody>\n';
							str += '		<tr id="trackbackLog_'+field[0]+'">\n';
							str += '			<td class="log">'+field[1]+'</td>\n'
							str += '			<td class="date">'+field[2]+'</td>\n'
							str += '			<td class="delete"><a class="delete-button button" href="#void" onclick="removeTrackbackLog('+field[0]+','+id+');"><span class="text"><?php echo _text('삭제');?></span></a></td>\n'
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
				if(confirm("<?php echo _text('선택된 걸린글을 지웁니다. 계속 하시겠습니까?');?>")) {
					var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/trackback/log/remove/" + id);
					request.onSuccess = function() {
						showTrackbackSender(entry);
					}
					request.onError = function() {
						alert('<?php echo _text('실패했습니다.');?>');
					}
					request.send();
				}
			}

			function resize() {
				window.resizeTo(document.body.clientWidth,document.body.clientHeight);
			}

			window.onload = function() {
				showTrackbackSender(<?php echo $suri['id'];?>);
				//resize();
			}
		//]]>
	</script>
</head>
<body>
	<form name="trackback" method="post" action="<?php echo $suri['url'];?>">
	
		<div id="trackback-box">
			<img src="<?php echo $service['path'] . $adminSkinSetting['skin'];?>/image/img_comment_popup_logo.gif" alt="<?php echo _text('텍스트큐브 로고');?>" />
			
			<div class="title"><span class="text"><?php echo _text('글걸기를 시도합니다');?></span></div>
	      	<div id="command-box">
	      		<dl class="title-line">
	      			<dt><span class="label"><?php echo _text('제목');?></span><span class="divider"> | </span></dt>
	      			<dd><?php echo htmlspecialchars($entry['title']);?></dd>
	      		</dl>
				<dl class="input-line">
					<dt><label for="url"><?php echo _text('주소입력');?></label><span class="divider"> | </span></dt>
					<dd>
						<input type="text" id="url" class="input-text" name="url" onkeydown="if (event.keyCode == 13) { sendTrackback(<?php echo $suri['id'];?>); return false;}" />
						<input type="button" class="input-button" name="Submit" value="<?php echo _text('전송');?>" onclick="sendTrackback(<?php echo $suri['id'];?>); return false;" />
					</dd>
				</dl>
				
				<div id="logs_<?php echo $suri['id'];?>"></div>
			</div>
			
			<div class="button-box">
				<input type="button" class="input-button" value="<?php echo _text('닫기');?>" onclick="window.close()" />
			</div>
		</div>
	</form>
</body>
</html>
