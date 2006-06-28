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
									if (document.getElementById('common-section').title.value != title) {
										var request = new HTTPRequest("GET", "<?=$service['path']?>/owner/setting/blog/title?title=" + encodeURIComponent(document.getElementById('common-section').title.value));
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											title = document.getElementById('common-section').title.value;
										}
										request.onError = function() {
											alert("<?=_t('블로그 제목을 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									if (document.getElementById('common-section').description.value != description) {
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/blog/description/");
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											description = document.getElementById('common-section').description.value;
										}
										request.onError = function() {
											alert("<?=_t('블로그 설명을 변경하지 못했습니다.')?>");
										}
										request.send("description=" + encodeURIComponent(document.getElementById('common-section').description.value));
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
									if ((document.getElementById('multi-section').primaryDomain.value != primaryDomain) && (!checkBlogName(document.getElementById('multi-section').primaryDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-section').primaryDomain.focus();
										return;
									}
									if ((document.getElementById('multi-section').secondaryDomain.value != secondaryDomain) && (!checkDomainName(document.getElementById('multi-section').secondaryDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.forms[0].secondaryDomain.focus();
										return;
									}
<?
	} else if ($service['type'] == 'path') {
?>
									if ((document.getElementById('multi-section').pathDomain.value != pathDomain) && (!checkBlogName(document.getElementById('multi-section').pathDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-section').pathDomain.focus();
										return;
									}
<?
	}
?>
									var location = null;
<?
	if ($service['type'] == 'domain') {
?>
									if (document.getElementById('multi-section').defaultDomain[defaultDomain].checked == false) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/default/" + (document.getElementById('multi-section').defaultDomain[1].checked ? 1 : 0));
										request.onSuccess = function() {
											defaultDomain = document.getElementById('multi-section').defaultDomain[1].checked ? 1 : 0;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?=_t('기본 블로그 도메인을 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									if (document.getElementById('multi-section').primaryDomain.value != primaryDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary/?name=" + encodeURIComponent(document.getElementById('multi-section').primaryDomain.value));
										request.onSuccess = function() {
											primaryDomain = document.getElementById('multi-section').primaryDomain.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											if (document.getElementById('multi-section').defaultDomain[0].checked)
												location = "http://" + primaryDomain + ".<?=$service['domain']?><?=$blogURL?>/owner/setting/blog";
										}
										request.onError = function() {
											alert("<?=_t('1차 블로그 도메인을 변경하지 못했습니다')?>");
										}
										request.send();
									}
									if (document.getElementById('multi-section').secondaryDomain.value != secondaryDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/secondary?domain=" + encodeURIComponent(document.getElementById('multi-section').secondaryDomain.value));
										request.onSuccess = function() {
											secondaryDomain = document.getElementById('multi-section').secondaryDomain.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											if (document.getElementById('multi-section').defaultDomain[1].checked)
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
									if (document.getElementById('multi-section').pathDomain.value != pathDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary?name=" + encodeURIComponent(document.getElementById('multi-section').pathDomain.value));
										request.onSuccess = function() {
											pathDomain = document.getElementById('multi-section').pathDomain.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											location = "http://<?=$service['domain']?><?=$blogURL?>/" + pathDomain + "/owner/setting/blog";
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
								//var allowWriteGuestbook = <?=$blog['allowWriteOnGuestbook']?>;
								function setRSS() {
									if (document.getElementById('rss-form').useSlogan[useSlogan].checked == true) {
										if (document.getElementById('rss-form').useSlogan.value != useSlogan) {
											var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/slogan/" + (document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0));
											request.onSuccess = function() {
												useSlogan = document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0;
												PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											}
											request.onError = function() {
												alert("<?=_t('글 주소 표기법을 변경할 수 없습니다.')?>");
											}
											request.send();
										}
									}
									
									var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/publishRSS/" + (document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0));
									request.onSuccess = function() {
										publishEolinSyncOnRSS = document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0;
										PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?=_t('RSS 공개 정도를 변경할 수 없습니다.')?>");
									}
									request.send();
									
									if (document.getElementById('rss-form').entriesOnRSS.value != entriesOnRSS) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/entries/" + document.getElementById('rss-form').entriesOnRSS.value);
										request.onSuccess = function() {
											entriesOnRSS = document.getElementById('rss-form').entriesOnRSS.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?=_t('RSS 글 개수를 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									
									if (document.getElementById('rss-form').publishWholeOnRSS.value != publishWholeOnRSS) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/rss/whole/" + document.getElementById('rss-form').publishWholeOnRSS.value);
										request.onSuccess = function() {
											publishWholeOnRSS = document.getElementById('rss-form').publishWholeOnRSS.value;
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
											alert("<?=_t('실패했습니다.')?>");
										}
										request.send();
									}
								}
								
								var language = "<?=$blog['language']?>";
								var timezone = "<?=$blog['timezone']?>";
								
								function setLocale() {
									if (document.getElementById('language-form').language.value != language) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/language?language=" + encodeURIComponent(document.getElementById('language-form').language.value));
										request.onSuccess = function() {
											language = document.getElementById('language-form').language.value;
											if (document.getElementById('language-form').timezone.value != timezone) {
												PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
												var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.getElementById('language-form').timezone.value));
												request.onSuccess = function() {
													timezone = document.getElementById('language-form').timezone.value;
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
									else if (document.getElementById('language-form').timezone.value != timezone) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.getElementById('language-form').timezone.value));
										request.onSuccess = function() {
											timezone = document.getElementById('language-form').timezone.value;
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
								<form id="common-section" class="section" method="post" action="<?=$service['path']?>/owner/setting/blog/title">
									<div class="grouping">
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="blog-title-line" class="line">
											<dt><label for="title"><?=_t('블로그 제목')?></label></dt>
											<dd><input type="text" id="title" class="text-input" name="title" value="<?=htmlspecialchars($blog['title'])?>" /></dd>
										</dl>
										<dl id="blog-description-line" class="line">
											<dt><label for="description"><?=_t('블로그 설명')?></label></dt>
											<dd><textarea id="description" name="description" cols="15" rows="5"><?=htmlspecialchars($blog['description'])?></textarea></dd>
										</dl>
										<div class="button-box">
											<a class="save-button button" href="#void" onclick="setBlog()"><span class="text"><?=_t('저장하기')?></span></a>
										</div>
									</div>
								</form>
<?
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
?>
								<form id="multi-section" class="section" method="post" action="<?=$service['path']?>/owner/setting/blog/domain">
									<div class="grouping">
										<input type="hidden" name="javascript" value="disabled" />
									
<?
	if ($service['type'] == 'domain') {
?>
										<dl id="first-blog-address-line" class="line">
											<dt><label for="primaryDomain"><?=_t('1차 블로그 주소')?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? '' : 'checked="checked"')?> title="<?=_t('기본 도메인')?>" /> http://<input type="text" id="primaryDomain" class="text-input" name="primaryDomain" value="<?=escapeJSInAttribute($blog['name'])?>" /><?=$urlRule[1]?>
											</dd>
										</dl>
										<dl id="second-blog-address-line" class="line">
											<dt><label for="secondaryDomain"><?=_t('2차 블로그 주소')?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?=($blog['defaultDomain'] ? 'checked="checked"' : '')?>title="<?=_t('기본 도메인')?>" /> http://<input type="text" id="secondaryDomain" class="text-input" name="secondaryDomain" value="<?=escapeJSInAttribute($blog['secondaryDomain'])?>" /><?=$blogURL?>
											</dd>
										</dl>
<?
	} else {
?>
										<dl id="blog-address-line" class="line">
											<dt><label for="pathDomain"><?=_t('블로그 주소')?></label></dt>
											<dd><?=$urlRule[0]?><input type="text" id="pathDomain" class="text-input" name="pathDomain" value="<?=escapeJSInAttribute($blog['name'])?>" /></dd>
										</dl>
<?
	}
?>
										<div class="button-box">
											<a class="save-button button" href="#void" onclick="setDomains()"><span class="text"><?=_t('저장하기')?></span></a>
										</div>
									</div>
								</form>
<?
}
?>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-profile" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('프로필을 설정합니다')?></span></h2>
							
							<div class="data-inbox">
								<div id="profile-section" class="section">
									<dl id="blog-picture-line" class="line">
										<dt><span class="label"><?=_t('사진')?></span></dt>
										<dd>
											<img id="logo" src="<?=(empty($blog['logo']) ? "{$service['path']}{$service['adminSkin']}/image/spacer.gif" : "{$service['path']}/attach/$owner/{$blog['logo']}")?>" width="92" height="93" alt="<?=_t('블로그 메인 이미지')?>" />
											<iframe src="<?=$blogURL?>/owner/setting/blog/logo" frameborder="0" scrolling="no" width="450" height="40"></iframe>
										</dd>
									</dl>
									<dl id="blog-icon-line" class="line">
										<dt><span class="label"><?=_t('블로그 아이콘')?></span></dt>
										<dd>
<?
if (file_exists(ROOT.'/index.gif')) {
?>
											<img id="blogIcon" src="<?=$service['path']?>/index.gif" width="16" height="16" alt="<?=_t('블로그 아이콘')?>" />
<?
} else {
?>
											<img id="blogIcon" src="<?=$service['path']?>/image/Tattertools.gif" width="16" height="16" alt="<?=_t('블로그 아이콘')?>" />
<?
}
?>
											<iframe src="<?=$blogURL?>/owner/setting/blog/blogIcon" frameborder="0" scrolling="no" width="400" height="23"></iframe>
										</dd>
									</dl>
									<dl id="farvicon-line" class="line">
										<dt><span class="label"><?= _t('Favicon')?></span></dt>
										<dd>
											<script type="text/javascript">
												//<![CDATA[
													if(!isIE) {
														document.write('<img id="favicon" src="<?=$blogURL?>/favicon.ico" alt="<?=_t('파비콘')?>" />');
													} else {
														document.write('<a href="<?=$blogURL?>/favicon.ico" onclick="window.open(this.href); return false;"><span class="text"><?=_t('미리보기')?></span></a>');
													}
												//]]>
											</script>
											<iframe src="<?=$blogURL?>/owner/setting/blog/favicon" frameborder="0" scrolling="no" height="30"></iframe>
										</dd>
									</dl>
								</div>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-rss" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('블로그 공개 정책을 설정합니다')?></span></h2>
							
							<form id="rss-form" class="data-inbox" method="post" action="<?=$service['path']?>/owner/setting/blog">
								<div id="rss-section" class="section">
									<input type="hidden" name="javascript" value="disabled" />
									
									<dl id="post-address-line" class="line">
										<dt><span class="label"><?=_t('글 주소')?></span></dt>
										<dd>
											<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="useSlogan1"><span class="text"><?=_t('문자를 사용합니다.')?> <samp><?=_f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다)', getBlogURL())?></samp></span></label><br />
											<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? '' : ' checked="checked"')?> /> <label for="useSlogan0"><span class="text"><?=_t('숫자를 사용합니다.')?> <samp><?=_f('(예: %1/123)', getBlogURL())?></samp></span></label>
										</dd>
									</dl>
									<dl id="open-type-line" class="line">
										<dt><span class="label"><?=_t('공개 정도')?></span></dt>
										<dd>
											<input type="radio" id="publishEolinSyncOnRSS1" class="radio" name="publishEolinSyncOnRSS"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="publishEolinSyncOnRSS1"><span class="text"><?=_t('공개된 모든 글을 <acronym title="Rich Site Summary">RSS</acronym>로 내보냅니다.')?></span></label><br />
											<input type="radio" id="publishEolinSyncOnRSS0" class="radio" name="publishEolinSyncOnRSS"<?=($blog['publishEolinSyncOnRSS'] ? '' : ' checked="checked"')?> /> <label for="publishEolinSyncOnRSS0"><span class="text"><?=_t('이올린에 발행된 글만을 RSS로 내보냅니다.')?></span></label>
										</dd>
									</dl>
									<dl id="post-count-line" class="line">
										<dt><label for="entriesOnRSS"><?=_t('글 개수')?></label></dt>
										<dd>
											<?=getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 0)?>
											<select id="entriesOnRSS" name="entriesOnRSS">
<?
for ($i = 5; $i <= 30; $i += 5) {
?>
												<option value="<?=$i?>"<?=($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '')?>><?=$i?></option>
<?
}
?>
											</select><?=getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 1)?>
										</dd>
									</dl>
									<dl id="open-range-line" class="line">
										<dt><label for="publishWholeOnRSS"><?=_t('공개 범위')?></label></dt>
										<dd><?=getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 0)?>
											<select id="publishWholeOnRSS" name="publishWholeOnRSS">
												<option value="1"<?=($blog['publishWholeOnRSS'] ? ' selected="selected"' : '')?>><?=_t('전체공개')?></option>
												<option value="0"<?=($blog['publishWholeOnRSS'] ? '' : ' selected="selected"')?>><?=_t('부분공개')?></option>
											</select><?=getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 1)?>
										</dd>
									</dl>
									<dl id="guestbook-authority-line" class="line">
										<dt><label for="allowCommentGuestbook"><?=_t('방명록 사용권한')?></label></dt>
										<dd>
											<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value=""<?=$blog['allowWriteOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowWriteGuestbook"><?=_t('손님이 글쓰기 허용')?></label-->
											<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?=$blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowCommentGuestbook"><?=_t('손님이 댓글쓰기 허용.')?></label>
										</dd>
									</dl>
									
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setRSS()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-language" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('언어, 시간대를 설정합니다')?></span></h2>
							
							<form id="language-form" class="data-inbox" method="post" action="<?=$service['path']?>/owner/setting/blog">
								<div id="language-section" class="section">
									<input type="hidden" name="javascript" value="disabled" />
									
									<dl id="language-line" class="line">
										<dt><label for="language"><?=_t('언어')?></label></dt>
										<dd>
											<select id="language" name="language">
<?
foreach (Locale::getSupportedLocales() as $locale => $language) {
?>
												<option value="<?=$locale?>"<?=($locale == $blog['language'] ? ' selected="selected"' : '')?>><?=$language?></option>
<?
}
?>
											</select>
										</dd>
									</dl>
									<dl id="timezone-line" class="line">
										<dt><label for="timezone"><?=_t('시간대')?></label></dt>
										<dd>
											<select id="timezone" name="timezone">
<?
foreach (Timezone::getList() as $timezone) {
?>
												<option value="<?=$timezone?>"<?=($timezone == $blog['timezone'] ? ' selected="selected"' : '')?>><?php echo _t($timezone)?></option>
<?
}
?>
											</select>
										</dd>
									</dl>
									
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setLocale()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
