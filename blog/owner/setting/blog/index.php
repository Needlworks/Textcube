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
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														title = document.forms[0].title.value;
													}
													request.onError = function() {
														alert("<?=_t('블로그 제목을 변경하지 못했습니다.')?>");
													}
													request.send();
												}
												if (document.forms[0].description.value != description) {
													var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/blog/description/");
													request.onSuccess = function() {
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														description = document.forms[0].description.value;
													}
													request.onError = function() {
														alert("<?=_t('블로그 설명을 변경하지 못했습니다.')?>");
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
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
													}
													request.onError = function() {
														alert("<?=_t('기본 블로그 도메인을 변경하지 못했습니다.')?>");
													}
													request.send();
												}
												if (document.forms[0].primaryDomain.value != primaryDomain) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary/?name=" + encodeURIComponent(document.forms[0].primaryDomain.value));
													request.onSuccess = function() {
														primaryDomain = document.forms[0].primaryDomain.value;
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
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
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
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
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														location = "http://<?=$service['domain']?><?=$service['path']?>/" + pathDomain + "/owner/setting/blog";
													}
													request.onError = function() {
														alert("<?=_t('블로그 주소를 변경하지 못했습니다.')?>");
													}
													request.send();
												}
<?
	}
?>
												if (location) {
													alert("<?=_t('변경된 기본 블로그 도메인으로 이동합니다.')?>");
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
											var publishEolinSyncOnRSS = "<?=$blog['publishEolinSyncOnRSS']?>";
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
															PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														}
														request.onError = function() {
															alert("<?=_t('글 주소 표기법을 변경할 수 없습니다.')?>");
														}
														request.send();
													}
												}
												if (document.forms[0].publishEolinSyncOnRSS[publishEolinSyncOnRSS].checked == true) {
													if (document.forms[0].publishEolinSyncOnRSS.value != publishEolinSyncOnRSS) {
														var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/publishRSS/" + (document.forms[0].publishEolinSyncOnRSS[0].checked ? 1 : 0));
														request.onSuccess = function() {
															publishEolinSyncOnRSS = document.forms[0].publishEolinSyncOnRSS[0].checked ? 1 : 0;
															PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
														}
														request.onError = function() {
															alert("<?=_t('RSS 공개 정도를 변경할 수 없습니다.')?>");
														}
														request.send();
													}
												}
												if (document.forms[0].entriesOnRSS.value != entriesOnRSS) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/entries/" + document.forms[0].entriesOnRSS.value);
													request.onSuccess = function() {
														entriesOnRSS = document.forms[0].entriesOnRSS.value;
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
													}
													request.onError = function() {
														alert("<?=_t('RSS 글 개수를 변경하지 못했습니다.')?>");
													}
													request.send();
												}
												if (document.forms[0].publishWholeOnRSS.value != publishWholeOnRSS) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/whole/" + document.forms[0].publishWholeOnRSS.value);
													request.onSuccess = function() {
														publishWholeOnRSS = document.forms[0].publishWholeOnRSS.value;
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
													}
													request.onError = function() {
														alert("<?=_t('RSS 공개 범위를 변경하지 못했습니다.')?>");
													}
													request.send();
												}
												isAllowCommentGuestbook = document.getElementById('allowCommentGuestbook').checked ? 1 : 0;
												//isAllowWriteGuestbook = document.getElementById('allowWriteGuestbook').checked ? 1 : 0;
												if ( isAllowCommentGuestbook != allowCommentGuestbook) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/guestbook/?comment="+isAllowCommentGuestbook+"&write=1");
													request.onSuccess = function() {
														allowCommentGuestbook = isAllowCommentGuestbook;
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
													}
													request.onError = function() {
														alert("<?=_t('실패 했습니다.')?>");
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
															PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
															var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.forms[0].timezone.value));
															request.onSuccess = function() {
																timezone = document.forms[0].timezone.value;
																window.location.href = "<?=$blogURL?>/owner/setting/blog";
															}
															request.onError = function() {
																alert("<?=_t('블로그 시간대를 변경할 수 없습니다.')?>");
																window.location.href = "<?=$blogURL?>/owner/setting/blog";
															}
															request.send();
														} else {
															window.location.href = "<?=$blogURL?>/owner/setting/blog";
														}
													}
													request.onError = function() {
														alert("<?=_t('블로그 언어를 변경할 수 없습니다.')?>");
													}
													request.send();
												}
												else if (document.forms[0].timezone.value != timezone) {
													var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.forms[0].timezone.value));
													request.onSuccess = function() {
														timezone = document.forms[0].timezone.value;
														PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
													}
													request.onError = function() {
														alert("<?=_t('블로그 시간대를 변경할 수 없습니다.')?>");
													}
													request.send();
												}
											}
										//]]>
									</script>
									
									<div id="part-setting-basic" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('블로그 기본 정보를 설정합니다')?></span></h2>
										
										<div class="data-inbox">
											<div id="common-section" class="section">
												<dl class="line">
													<dt><span><?=_t('블로그 제목')?></span><span class="divider"> | </span></dt>
													<dd><input type="text" class="text-input" name="title" value="<?=htmlspecialchars($blog['title'])?>" /></dd>
												</dl>
												<dl class="line">
													<dt><span><?=_t('블로그 설명')?></span><span class="divider"> | </span></dt>
													<dd><textarea name="description" rows="5"><?=htmlspecialchars($blog['description'])?></textarea></dd>
												</dl>
												<div class="button-box">
													<a class="save-button button" href="#void" onclick="setBlog()"><span><?=_t('저장하기')?></span></a>
												</div>
											</div>
<?
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
	if ($service['type'] == 'domain') {
?>
											<div id="multi-section" class="section">
												<dl class="line">
													<dt><span><?=_t('1차 블로그 주소')?></span><span class="divider"> | </span></dt>
													<dd><input type="radio" class="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? '' : 'checked="checked"')?> title="<?=_t('기본 도메인')?>" /></dd>
													<dd><span class="label">http://</span><input type="text" id="primaryDomain" class="text-input" name="primaryDomain" value="<?=escapeJSInAttribute($blog['name'])?>" /></dd>
													<dd><span class="label"><?=$urlRule[1]?></span></dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span><?=_t('2차 블로그 주소')?></span><span class="divider"> | </span></dt>
													<dd><input type="radio" class="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? 'checked="checked"' : '')?>title="<?=_t('기본 도메인')?>" /></dd>
													<dd><span class="label">http://</span><input type="text" id="secondaryDomain" class="text-input" name="secondaryDomain" value="<?=escapeJSInAttribute($blog['secondaryDomain'])?>" /></dd>
													<dd><span class="label"><?=$service['path']?></span></dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span><?=_t('블로그 주소')?></span><span class="divider"> | </span></dt>
													<dd><?=$urlRule[0]?></dd>
													<dd><input type="text" id="pathDomain" class="text-input" name="pathDomain" value="<?=escapeJSInAttribute($blog['name'])?>" /></dd>
													<dd class="clear"></dd>
												</dl>
<?
	}
?>
												<div class="button-box">
													<a class="save-button button" href="#void" onclick="setDomains()"><span><?=_t('저장하기')?></span></a>
												</div>
											</div>
<?
}
?>
										</div>
										
										<div class="clear"></div>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-setting-profile" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('프로필')?></span></h2>
										
										<div class="data-inbox">
											<div id="profle-section" class="section">
												<dl class="line">
													<dt><span><?=_t('사진')?></span><span class="divider"> | </span></dt>
													<dd><img id="logo" src="<?=(empty($blog['logo']) ? "{$service['path']}/style/default/image/spacer.gif" : "{$service['path']}/attach/$owner/{$blog['logo']}")?>" width="92" height="93" border="1" alt="<?=_t('블로그 메인 이미지')?>" /></dd>
													<dd><iframe src="<?=$blogURL?>/owner/setting/blog/logo" frameborder="0" scrolling="no" width="450" height="40"></iframe></dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span><?=_t('블로그 아이콘')?></span><span class="divider"> | </span></dt>
<?
if (file_exists(ROOT.'/index.gif')) {
?>
													<dd><img id="blogIcon" src="<?=$blogURL?>/index.gif" width="16" height="16" border="1" alt="<?=_t('블로그 아이콘')?>" /></dd>
<?
} else {
?>
													<dd><img id="blogIcon" src="<?=$service['path']?>/image/Tattertools.gif" width="16" height="16" border="1" alt="<?=_t('블로그 아이콘')?>" /></dd>
<?
}
?>
													<dd><iframe src="<?=$blogURL?>/owner/setting/blog/blogIcon" frameborder="0" scrolling="no" width="400" height="23"></iframe></dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span>Favicon</span><span class="divider"> | </span></dt>
													<dd>
														<script type="text/javascript">
															//<![CDATA[
																if(!isIE) {
																	document.write('<img id="favicon" src="<?=$blogURL?>/favicon.ico" border="1" alt="<?=_t('파비콘')?>" />');
																} else {
																	document.write('<a href="<?=$blogURL?>/favicon.ico" onclick="window.open(this.href); return false;"><span><?=_t('미리보기')?></span></a>');
																}
															//]]>
														</script>
													</dd>
													<dd>
														<iframe src="<?=$blogURL?>/owner/setting/blog/favicon" frameborder="0" scrolling="no" height="30"></iframe>
													</dd>
													<dd class="clear"></dd>
												</dl>
											</div>
										</div>
										
										<div class="clear"></div>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-setting-rss" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('블로그 공개 정책을 설정합니다')?></span></h2>
										
										<div class="data-inbox">
											<div id="rss-section" class="section">
												<dl id="post-address" class="line">
													<dt><span><?=_t('글 주소')?></span><span class="divider"> | </span></dt>
													<dd>
														<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="useSlogan1"><?=_t('문자를 사용합니다.')?> <samp><?=_f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다.)', getBlogURL())?></samp></label><br />
														<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? '' : ' checked="checked"')?> /> <label for="useSlogan0"><?=_t('숫자를 사용합니다.')?> <samp><?=_f('(예: %1/123)', getBlogURL())?></samp></label>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl id="open-type" class="line">
													<dt><span><?=_t('공개 정도')?></span><span class="divider"> | </span></dt>
													<dd>
														<input type="radio" id="publishEolinSyncOnRSS1" class="radio" name="publishEolinSyncOnRSS"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="publishEolinSyncOnRSS1"><?=_t('공개된 모든 글을 RSS로 내보냅니다.')?></label><br />
														<input type="radio" id="publishEolinSyncOnRSS0" class="radio" name="publishEolinSyncOnRSS"<?=($blog['publishEolinSyncOnRSS'] ? '' : ' checked="checked"')?> /> <label for="publishEolinSyncOnRSS0"><?=_t('이올린에 발행된 글만을 RSS로 내보냅니다.')?></label>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl id="post-count" class="line">
													<dt><span><?=_t('글 개수')?></span><span class="divider"> | </span></dt>
													<dd>
														<?=getArrayValue(explode('%1', _t('RSS파일의 블로그 글은 최신 %1 개로 갱신됩니다.')), 0)?>
														<select name="entriesOnRSS" style="width: 40px;">
<?
for ($i = 5; $i <= 30; $i += 5) {
?>
															<option value="<?=$i?>"<?=($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '')?>><?=$i?></option>
<?
}
?>
														</select><?=getArrayValue(explode('%1', _t('RSS파일의 블로그 글은 최신 %1 개로 갱신됩니다.')), 1)?>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl id="open-range" class="line">
													<dt><span><?=_t('공개 범위')?></span><span class="divider"> | </span></dt>
													<dd><?=getArrayValue(explode('%1', _t('RSS파일의 글 본문은 %1를 원칙으로 합니다.')), 0)?>
														<select name="publishWholeOnRSS" style="width: 75px;">
															<option value="1"<?=($blog['publishWholeOnRSS'] ? ' selected="selected"' : '')?>><?=_t('전체공개')?></option>
															<option value="0"<?=($blog['publishWholeOnRSS'] ? '' : ' selected="selected"')?>><?=_t('부분공개')?></option>
														</select><?=getArrayValue(explode('%1', _t('RSS파일의 글 본문은 %1 를 원칙으로 합니다.')), 1)?>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl id="guestbook-authority" class="line">
													<dt><span><?=_t('방명록 사용권한')?></span><span class="divider"> | </span></dt>
													<dd>
														<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value="" <?=$blog['allowWriteOnGuestbook'] == '1' ? 'checked = "selected"' : ""?> /> <label for="allowWriteGuestbook"><?=_t('손님이 글쓰기 허용')?></label-->
														<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?=$blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? ' checked="selected"' : ""?> /> <label for="allowCommentGuestbook"><?=_t('손님이 댓글쓰기 허용.')?></label>
													</dd>
													<dd class="clear"></dd>
												</dl>
												
												<div class="button-box">
													<a class="save-button button" href="#void" onclick="setRSS()"><span><?=_t('저장하기')?></span></a>
												</div>
											</div>
										</div>
										
										<div class="clear"></div>
									</div>
									
									<hr class="hidden" />
									
									<div id="part-setting-language" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('언어, 시간대를 설정합니다')?></span></h2>
										
										<div class="data-inbox">
											<div id="language-section" class="section">
												<dl class="line">
													<dt><span><?=_t('언어')?></span><span class="divider"> | </span></dt>
													<dd>
														<select name="language">
<?
foreach (Locale::getSupportedLocales() as $locale => $language) {
?>
															<option value="<?=$locale?>"<?=($locale == $blog['language'] ? ' selected="selected"' : '')?>><?=$language?></option>
<?
}
?>
														</select>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><?=_t('시간대')?> |</dt>
													<dd>
														<select name="timezone">
<?
foreach (Timezone::getList() as $timezone => $label) {
?>
															<option value="<?=$timezone?>"<?=($timezone == $blog['timezone'] ? ' selected="selected"' : '')?>><?=$label?></option>
<?
}
?>
														</select>
													</dd>
													<dd class="clear"></dd>
												</dl>
												
												<div class="button-box">
													<a class="save-button button" href="#void" onclick="setLocale()"><span><?=_t('저장하기')?></span></a>
												</div>
											</div>
										</div>
										
										<div class="clear"></div>
									</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>
