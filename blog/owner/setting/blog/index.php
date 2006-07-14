<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu50.php';
?>
<script type="text/javascript">
//<![CDATA[
	var title = "<?=escapeJSInCData($blog['title'])?>";
	var description = "<?=escapeJSInCData(trim($blog['description']))?>";
	function setBlog() {
		if (document.forms[0].title.value != title) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/title?title=" + encodeURIComponent(document.forms[0].title.value));
			request.onSuccess = function() {
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				title = document.forms[0].title.value;
			}
			request.onError = function() {
				alert("<?=_t('블로그 제목을 변경하지 못했습니다')?>");
			}
			request.send();
		}
		if (document.forms[0].description.value != description) {
			var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/blog/description/");
			request.onSuccess = function() {
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				description = document.forms[0].description.value;
			}
			request.onError = function() {
				alert("<?=_t('블로그 설명을 변경하지 못했습니다')?>");
			}
			request.send("description=" + encodeURIComponent(document.forms[0].description.value));
		}
	}

<?
if ($service['type'] != 'single') {
	if ($service['type'] == 'domain') {
?>
	var primaryDomain = "<?=escapeJSInCData($blog['name'])?>";
	var secondaryDomain = "<?=escapeJSInCData($blog['secondaryDomain'])?>";
	var defaultDomain = "<?=escapeJSInCData($blog['defaultDomain'])?>";
<?
	} else if ($service['type'] == 'path') {
?>
	var pathDomain = "<?=escapeJSInCData($blog['name'])?>";
<?
	}
?>
	function setDomains() {
<?
	if ($service['type'] == 'domain') {
?>
		if ((document.forms[0].primaryDomain.value != primaryDomain) && (!checkBlogName(document.forms[0].primaryDomain.value))) {
			alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
			document.forms[0].primaryDomain.focus();
			return;
		}
		if ((document.forms[0].secondaryDomain.value != secondaryDomain) && (!checkDomainName(document.forms[0].secondaryDomain.value))) {
			alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
			document.forms[0].secondaryDomain.focus();
			return;
		}
<?
	} else if ($service['type'] == 'path') {
?>
		if ((document.forms[0].pathDomain.value != pathDomain) && (!checkBlogName(document.forms[0].pathDomain.value))) {
			alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
			document.forms[0].pathDomain.focus();
			return;
		}
<?
	}
?>
		var location = null;
<?
	if ($service['type'] == 'domain') {
?>
		if (document.forms[0].defaultDomain[defaultDomain].checked == false) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/default/" + (document.forms[0].defaultDomain[1].checked ? 1 : 0));
			request.onSuccess = function() {
				defaultDomain = document.forms[0].defaultDomain[1].checked ? 1 : 0;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('기본 블로그 도메인을 변경하지 못했습니다')?>");
			}
			request.send();
		}
		if (document.forms[0].primaryDomain.value != primaryDomain) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary/?name=" + encodeURIComponent(document.forms[0].primaryDomain.value));
			request.onSuccess = function() {
				primaryDomain = document.forms[0].primaryDomain.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				if (document.forms[0].defaultDomain[0].checked)
					location = "http://" + primaryDomain + ".<?=$service['domain']?><?=$blogURL?>/owner/setting/blog";
			}
			request.onError = function() {
				alert("<?=_t('1차 블로그 도메인을 변경하지 못했습니다')?>");
			}
			request.send();
		}
		if (document.forms[0].secondaryDomain.value != secondaryDomain) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/secondary?domain=" + encodeURIComponent(document.forms[0].secondaryDomain.value));
			request.onSuccess = function() {
				secondaryDomain = document.forms[0].secondaryDomain.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				if (document.forms[0].defaultDomain[1].checked)
					location = "http://" + secondaryDomain + "<?=$blogURL?>/owner/setting/blog";
			}
			request.onError = function() {
				alert("<?=_t('1차 블로그 도메인을 변경하지 못했습니다')?>");
			}
			request.send();
		}
<?
	} else if ($service['type'] == 'path') {
?>
		if (document.forms[0].pathDomain.value != pathDomain) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary?name=" + encodeURIComponent(document.forms[0].pathDomain.value));
			request.onSuccess = function() {
				pathDomain = document.forms[0].pathDomain.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				location = "http://<?=$service['domain']?><?=$service['path']?>/" + pathDomain + "/owner/setting/blog";
			}
			request.onError = function() {
				alert("<?=_t('블로그 주소를 변경하지 못했습니다')?>");
			}
			request.send();
		}
<?
	}
?>
		if (location) {
			alert("<?=_t('변경된 기본 블로그 도메인으로 이동합니다')?>");
			window.location.href = location;
		}
	}
<?
}
?>
	function changeLogo() {
		document.frames[0].document.forms[0].logo.click();
	}

	var useSlogan = "<?=$blog['useSlogan']?>";
	var entriesOnRSS = "<?=$blog['entriesOnRSS']?>";
	var publishWholeOnRSS = "<?=$blog['publishWholeOnRSS']?>";
	var allowCommentGuestbook = <?=$blog['allowWriteDoubleCommentOnGuestbook']?>;
	var allowWriteGuestbook = <?=$blog['allowWriteOnGuestbook']?>;
	
	function setRSS() {
		if (document.forms[0].useSlogan[useSlogan].checked == true) {
			if (document.forms[0].useSlogan.value != useSlogan) {
				var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/slogan/" + (document.forms[0].useSlogan[0].checked ? 1 : 0));
				request.onSuccess = function() {
					useSlogan = document.forms[0].useSlogan[0].checked ? 1 : 0;
					PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				}
				request.onError = function() {
					alert("<?=_t('글 주소 표기법을 변경할 수 없습니다')?>");
				}
				request.send();
			}
		}
		if (document.forms[0].entriesOnRSS.value != entriesOnRSS) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/entries/" + document.forms[0].entriesOnRSS.value);
			request.onSuccess = function() {
				entriesOnRSS = document.forms[0].entriesOnRSS.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('RSS 글 개수를 변경하지 못했습니다')?>");
			}
			request.send();
		}
		if (document.forms[0].publishWholeOnRSS.value != publishWholeOnRSS) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/whole/" + document.forms[0].publishWholeOnRSS.value);
			request.onSuccess = function() {
				publishWholeOnRSS = document.forms[0].publishWholeOnRSS.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('RSS 공개 범위를 변경하지 못했습니다')?>");
			}
			request.send();
		}
		isAllowCommentGuestbook = document.getElementById('allowCommentGuestbook').checked ? 1 : 0;	
		//isAllowWriteGuestbook = document.getElementById('allowWriteGuestbook').checked ? 1 : 0;	
		
		if ( isAllowCommentGuestbook != allowCommentGuestbook) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/guestbook/?comment="+isAllowCommentGuestbook+"&write=1");
			request.onSuccess = function() {
				allowCommentGuestbook = isAllowCommentGuestbook;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('실패 했습니다')?>");
			}
			request.send();
		}
	}

	var language = "<?=$blog['language']?>";
	var timezone = "<?=$blog['timezone']?>";
	function setLocale() {
		if (document.forms[0].language.value != language) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/language?language=" + encodeURIComponent(document.forms[0].language.value));
			request.onSuccess = function() {
				language = document.forms[0].language.value;
				if (document.forms[0].timezone.value != timezone) {
					PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
					var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.forms[0].timezone.value));
					request.onSuccess = function() {
						timezone = document.forms[0].timezone.value;
						window.location.href = "<?=$blogURL?>/owner/setting/blog";
					}
					request.onError = function() {
						alert("<?=_t('블로그 시간대를 변경할 수 없습니다')?>");
						window.location.href = "<?=$blogURL?>/owner/setting/blog";
					}
					request.send();
				} else {
					window.location.href = "<?=$blogURL?>/owner/setting/blog";
				}
			}
			request.onError = function() {
				alert("<?=_t('블로그 언어를 변경할 수 없습니다')?>");
			}
			request.send();
		}
		else if (document.forms[0].timezone.value != timezone) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.forms[0].timezone.value));
			request.onSuccess = function() {
				timezone = document.forms[0].timezone.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('블로그 시간대를 변경할 수 없습니다')?>");
			}
			request.send();
		}
	}

	var editorMode = "<?=getUserSetting('editorMode', 1)?>";
	var strictXHTML = "<?=getUserSetting('strictXHTML', 0)?>";
	function setEditor() {
		if (document.forms[0].editorMode.value != editorMode || document.forms[0].strictXHTML.value != strictXHTML) {
			var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/editor/?editorMode=" + document.forms[0].editorMode.value + "&strictXHTML=" + document.forms[0].strictXHTML.value);
			request.onSuccess = function() {
				editorMode = document.forms[0].editorMode.value;
				strictXHTML = document.forms[0].strictXHTML.value;
				PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
			}
			request.onError = function() {
				alert("<?=_t('에디터 설정을 변경할 수 없습니다')?>");
			}
			request.send();
		}
	}
//]]>
</script>   
			
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('블로그 기본 정보를 설정합니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr style="background-color:#EBF2F8">
                <td width="350" valign="top" style="padding:10px 5px 10px 5px">
                  <table>
                    <tr>
                      <td valign="middle">
                        <table cellspacing="0">
                          <tr>
                            <td class="entryEditTableLeftCell"><?=_t('블로그 제목')?> |</td>
                            <td>
                              <input type="text" class="text1" style="width:200px" name="title" value="<?=htmlspecialchars($blog['title'])?>" />
                            </td>
                          </tr>
                          <tr>
                            <td class="entryEditTableLeftCell"><?=_t('블로그 설명')?> |</td>
                            <td>
                              <textarea class="text1" style="width:202px" name="description" rows="5"><?=htmlspecialchars($blog['description'])?></textarea>
                            </td>
                          </tr>
                          <tr>
                            <td class="entryEditTableLeftCell"></td>
                            <td>
                              <table class="buttonTop" cellspacing="0" onclick="setBlog()">
                                <tr>
                                  <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                                  <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                                  <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
<?
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
?>
                <td valign="top" style="padding:10px 5px 10px 5px">
                  <table>
                    <tr>
                      <td>
                        <table cellspacing="0">
<?
	if ($service['type'] == 'domain') {
?>
                          <tr>
                            <td class="entryEditTableLeftCell"><?=_t('1차 블로그 주소')?> |</td>
                            <td style="padding-right:5px">
                              <input type="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? '' : 'checked="checked"')?> title="<?=_t('기본 도메인')?>" />
							</td>
							<td>http://</td>
							<td style="padding-left:5px">
                              <input type="text" class="text1" name="primaryDomain" value="<?=escapeJSInAttribute($blog['name'])?>" />
                            </td>
                            <td style="padding-left:5px"><?=$urlRule[1]?></td>
                          </tr>
                          <tr>
                            <td class="entryEditTableLeftCell"><?=_t('2차 블로그 주소')?> |</td>
                            <td style="padding-right:5px">
								
                              <input type="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? 'checked="checked"' : '')?>title="<?=_t('기본 도메인')?>" />
							</td>
							<td>http://</td>
							<td style="padding-left:5px">
                              <input type="text" class="text1" name="secondaryDomain" value="<?=escapeJSInAttribute($blog['secondaryDomain'])?>" />
                            </td>
                            <td style="padding-left:5px"><?=$service['path']?></td>
                          </tr> 
<?
	} else {
?>
                          <tr>
                            <td class="entryEditTableLeftCell"><?=_t('블로그 주소')?> |</td>
                            <td style="padding-right:5px"><?=$urlRule[0]?></td>
							<td>
                              <input type="text" class="text1" name="pathDomain" value="<?=escapeJSInAttribute($blog['name'])?>" />
                            </td>
                          </tr>
<?
	}
?>
                          <tr>
                            <td class="entryEditTableLeftCell"></td>
                            <td colspan="3">
                              <table class="buttonTop" cellspacing="0" onclick="setDomains()">
                                <tr>
                                  <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                                  <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                                  <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
<?
}
?>
                <td>&nbsp;</td>
              </tr>
            </table>
            <br />
            <br />
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('프로필')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED; background-color:#EBF2F8;">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
                  <table cellspacing="0" border="0">
				  
				  
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('사진')?> |</td>
                      <td><img id="logo" width="92" height="93" style="border-style:solid; border-width:1px; border-color:#404040" src="<?=(empty($blog['logo']) ? "{$service['path']}/image/spacer.gif" : "{$service['path']}/attach/$owner/{$blog['logo']}")?>" alt="" /></td>           
					           
   					<td valign="middle">
						<iframe src="<?=$blogURL?>/owner/setting/blog/logo" style="margin:opx; padding:0px;display:block; border-color:#FFFFFF\" frameborder="0" scrolling="no" width="400" height="30"></iframe>
					</td>
					</tr> 
<?
?>
					<tr>
						<td>&nbsp;
						
						</td>
					</tr>
					
					 <tr>
						<td class="entryEditTableLeftCell"><?=_t('블로그 아이콘')?> |</td>
						<td align="center"><img id="blogIcon" width="16" height="16" style="border-style:solid; border-width:1px; border-color:#404040" src="<?=$blogURL?>/index.gif" alt="" onerror="this.src='<?=$service['path']?>/image/Tattertools.gif'"/></td>
						<td valign="middle">
						<iframe src="<?=$blogURL?>/owner/setting/blog/blogIcon" style="margin:opx; padding:0px;display:block; border-color:#FFFFFF\" frameborder="0" scrolling="no"  height="30"></iframe>
						</td>
                    </tr>
					
					<tr>
						<td>&nbsp;
						
						</td>
					</tr>
					
					<tr>
						<td class="entryEditTableLeftCell">Favicon |</td>
						<td align="center">
							<script type="text/javascript">
								if(!isIE) {									
									document.write('<img id="favicon" width="16" height="16" style="border-style:solid; border-width:1px; border-color:#404040" src="<?="$blogURL/favicon.ico"?>" alt="" />');
								} else {
									document.write('<a href="<?="$blogURL/favicon.ico"?>" target="_blank"><?=_t('미리보기')?></a>');
								}
							</script>
						</td>
						<td valign="middle">
							<iframe src="<?=$blogURL?>/owner/setting/blog/favicon" style="margin:opx; padding:0px;display:block; border-color:#FFFFFF\" frameborder="0" scrolling="no"  height="30"></iframe>
						</td>
                    </tr>
<?
?>					
					
                  </table>
                </td>
              </tr>
           </table>
            <br />
            <br />
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('블로그 공개 정책을 설정합니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('글 주소')?> |</td>
                      <td>
                        <table>
                          <tr>
                            <td><input type="radio" id="useSlogan1" name="useSlogan"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /><?=_t('문자를 사용합니다')?> <samp><?=_f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다)', getBlogURL())?></samp></td>
                          </tr>
                          <tr>
                            <td><input type="radio" id="useSlogan0" name="useSlogan"<?=($blog['useSlogan'] ? '' : ' checked="checked"')?> /><?=_t('숫자를 사용합니다')?> <samp><?=_f('(예: %1/123)', getBlogURL())?></samp></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('글 개수')?> |</td>
                      <td>
                        <table>
                          <tr>
                            <td><?=getArrayValue(explode('%1', _t('RSS파일의 블로그 글은 최신&nbsp;%1개로 갱신됩니다')), 0)?></td>
                            <td>
                              <select name="entriesOnRSS">
<?
for ($i = 5; $i <= 30; $i += 5) {
?>
                                <option value="<?=$i?>"<?=($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '')?>><?=$i?></option>
<?
}
?>
                              </select>
                            </td>
                            <td><?=getArrayValue(explode('%1', _t('RSS파일의 블로그 글은 최신&nbsp;%1개로 갱신됩니다')), 1)?></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('공개 범위')?> |</td>
                      <td>
                        <table>
                          <tr>
                            <td><?=getArrayValue(explode('%1', _t('RSS파일의 글 본문은&nbsp;%1를 원칙으로 합니다')), 0)?></td>
                            <td>
                              <select name="publishWholeOnRSS">
                                <option value="1"<?=($blog['publishWholeOnRSS'] ? ' selected="selected"' : '')?>><?=_t('전체공개')?></option>
                                <option value="0"<?=($blog['publishWholeOnRSS'] ? '' : ' selected="selected"')?>><?=_t('부분공개')?></option>
                              </select>
                            </td>
                            <td><?=getArrayValue(explode('%1', _t('RSS파일의 글 본문은&nbsp;%1를 원칙으로 합니다')), 1)?></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
				  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('방명록 사용권한')?> |</td>
                      <td>
                        <table>
						 <!--
						  <tr>
                            <td>
								<input id="allowWriteGuestbook" type="checkbox" value="" <?=$blog['allowWriteOnGuestbook'] == '1' ? 'checked = "selected"' : ""?>/> <label for="allowWriteGuestbook"><?=_t('손님이 글쓰기 허용')?> </label>
                            </td>
                          </tr>
						 -->
                          <tr>
                            <td>
                             	 <input id="allowCommentGuestbook" type="checkbox" value="" <?=$blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? 'checked = "selected"' : ""?>/> <label for="allowCommentGuestbook"><?=_t('손님이 댓글쓰기 허용')?> </label>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <div style="padding-left:132px">
                    <table class="buttonTop" cellspacing="0" onclick="setRSS()">
                      <tr>
                        <td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                        <td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>
            <br />
            <br />
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('언어, 시간대를 설정합니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('언어')?> |</td>
                      <td>
                        <select name="language">
<?
foreach (Locale::getSupportedLocales() as $locale => $language) {
?>
                          <option value="<?=$locale?>"<?=($locale == $blog['language'] ? ' selected="selected"' : '')?>><?=$language?></option>						  
<?
}
?>
                        </select>
                      </td>
                    </tr>
                  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('시간대')?> |</td>
                      <td>
                        <select name="timezone">
<?
foreach (Timezone::getList() as $timezone) {
?>
                          <option value="<?=$timezone?>"<?=($timezone == $blog['timezone'] ? ' selected="selected"' : '')?>><?=_t($timezone)?></option>
<?
}
?>
                        </select>
                      </td>
                    </tr>
                  </table>
                  <div style="padding-left:132px">
                    <table class="buttonTop" cellspacing="0" onclick="setLocale()">
                      <tr>
                        <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                        <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>
            <br />
            <br />
            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" alt="" /></td>
                      <td style="padding:3px 0px 0px 4px"><?=_t('글 작성 환경을 설정합니다')?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
              <tr>
                <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px">
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('기본 작성 모드')?> |</td>
                      <td>
					  	<?
							$editorMode = getUserSetting('editorMode', 1);
						?>
                        <select name="editorMode">
							<option value="1"<?=$editorMode==1?' selected':''?>><?=_t('위지윅 모드')?></option>
							<option value="2"<?=$editorMode==2?' selected':''?>><?=_t('HTML 직접 편집')?></option>
                        </select>
                      </td>
                    </tr>
                  </table>
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('XHTML 준수')?> |</td>
                      <td>
					  	<?
							$strictXHTML = getUserSetting('strictXHTML', 0);
						?>
                        <select name="strictXHTML">
							<option value="0"<?=$strictXHTML==0?' selected':''?>><?=_t('처리하지 않음')?></option>
							<option value="1"<?=$strictXHTML==1?' selected':''?>><?=_t('올바른 XHTML 코드로 다듬어 출력')?></option>
                        </select>
                      </td>
                    </tr>
                  </table>
                  <div style="padding-left:132px">
                    <table class="buttonTop" cellspacing="0" onclick="setEditor()">
                      <tr>
                        <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('저장하기')?></td>
                        <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>
<?
require ROOT . '/lib/piece/owner/footer.php';
?>