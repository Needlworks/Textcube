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
									if (document.getElementById('common-form').title.value != title) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/blog/title?title=" + encodeURIComponent(document.getElementById('common-form').title.value));
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											title = document.getElementById('common-form').title.value;
										}
										request.onError = function() {
											alert("<?=_t('블로그 제목을 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									if (document.getElementById('common-form').description.value != description) {
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/blog/description/");
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											description = document.getElementById('common-form').description.value;
										}
										request.onError = function() {
											alert("<?=_t('블로그 설명을 변경하지 못했습니다.')?>");
										}
										request.send("description=" + encodeURIComponent(document.getElementById('common-form').description.value));
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
									if ((document.getElementById('multi-form').primaryDomain.value != primaryDomain) && (!checkBlogName(document.getElementById('multi-form').primaryDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-form').primaryDomain.focus();
										return;
									}
									if ((document.getElementById('multi-form').secondaryDomain.value != secondaryDomain) && (!checkDomainName(document.getElementById('multi-form').secondaryDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.forms[0].secondaryDomain.focus();
										return;
									}
<?
	} else if ($service['type'] == 'path') {
?>
									if ((document.getElementById('multi-form').pathDomain.value != pathDomain) && (!checkBlogName(document.getElementById('multi-form').pathDomain.value))) {
										alert("<?=_t('블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-form').pathDomain.focus();
										return;
									}
<?
	}
?>
									var location = null;
<?
	if ($service['type'] == 'domain') {
?>
									if (document.getElementById('multi-form').defaultDomain[defaultDomain].checked == false) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/default/" + (document.getElementById('multi-form').defaultDomain[1].checked ? 1 : 0));
										request.onSuccess = function() {
											defaultDomain = document.getElementById('multi-form').defaultDomain[1].checked ? 1 : 0;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?=_t('기본 블로그 도메인을 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									if (document.getElementById('multi-form').primaryDomain.value != primaryDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary/?name=" + encodeURIComponent(document.getElementById('multi-form').primaryDomain.value));
										request.onSuccess = function() {
											primaryDomain = document.getElementById('multi-form').primaryDomain.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											if (document.getElementById('multi-form').defaultDomain[0].checked)
												location = "http://" + primaryDomain + ".<?=$service['domain']?><?=$blogURL?>/owner/setting/blog";
										}
										request.onError = function() {
											alert("<?=_t('1차 블로그 도메인을 변경하지 못했습니다')?>");
										}
										request.send();
									}
									if (document.getElementById('multi-form').secondaryDomain.value != secondaryDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/secondary?domain=" + encodeURIComponent(document.getElementById('multi-form').secondaryDomain.value));
										request.onSuccess = function() {
											secondaryDomain = document.getElementById('multi-form').secondaryDomain.value;
											PM.showMessage("<?=_t('저장되었습니다.')?>", "center", "bottom");
											if (document.getElementById('multi-form').defaultDomain[1].checked)
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
									if (document.getElementById('multi-form').pathDomain.value != pathDomain) {
										var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/setting/domain/primary?name=" + encodeURIComponent(document.getElementById('multi-form').pathDomain.value));
										request.onSuccess = function() {
											pathDomain = document.getElementById('multi-form').pathDomain.value;
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
								
								function setIcons() {
									document.getElementById('icons-form').submit();
								}
								
								function setThumbnail() {
									document.getElementById('thumbnail-form').submit();
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
								
								function checkManualInput(obj) {
									if (obj.options[obj.options.selectedIndex].value == "direct") {
										switch (obj.id) {
											case "topPadding":
												document.getElementById('topPaddingManual').removeAttribute('disabled');
												break;
											case "bottomPadding":
												document.getElementById('bottomPaddingManual').removeAttribute('disabled');
												break;
											case "leftPadding":
												document.getElementById('leftPaddingManual').removeAttribute('disabled');
												break;
											case "rightPadding":
												document.getElementById('rightPaddingManual').removeAttribute('disabled');
												break;
										}
									} else {
										switch (obj.id) {
											case "topPadding":
												document.getElementById('topPaddingManual').setAttribute('disabled', 'disabled');
												break;
											case "bottomPadding":
												document.getElementById('bottomPaddingManual').setAttribute('disabled', 'disabled');
												break;
											case "leftPadding":
												document.getElementById('leftPaddingManual').setAttribute('disabled', 'disabled');
												break;
											case "rightPadding":
												document.getElementById('rightPaddingManual').setAttribute('disabled', 'disabled');
												break;
										}
									}
								}
								
								window.addEventListener("load", loadBlogSetting, false);
								
								function loadBlogSetting() {
									document.getElementById('topPadding').removeAttribute('disabled');
									document.getElementById('topPaddingManual').setAttribute('disabled', 'disabled');
									document.getElementById('bottomPadding').removeAttribute('disabled');
									document.getElementById('bottomPaddingManual').setAttribute('disabled', 'disabled');
									document.getElementById('leftPadding').removeAttribute('disabled');
									document.getElementById('leftPaddingManual').setAttribute('disabled', 'disabled');
									document.getElementById('rightPadding').removeAttribute('disabled');
									document.getElementById('rightPaddingManual').setAttribute('disabled', 'disabled');
								}
							//]]>
						</script>
						
						<div id="part-setting-basic" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('블로그 기본 정보를 설정합니다')?></span></h2>
							
							<div class="data-inbox">
								<form id="common-form" class="section" method="post" action="<?=$blogURL?>/owner/setting/blog/title">
									<fieldset class="container">
										<legend><?=_t('블로그 정보')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="blog-title-line" class="line">
											<dt><label for="title"><?=_t('블로그 제목')?></label></dt>
											<dd><input type="text" id="title" class="text-input" name="title" value="<?=htmlspecialchars($blog['title'])?>" /></dd>
										</dl>
										<dl id="blog-description-line" class="line">
											<dt><label for="description"><?=_t('블로그 설명')?></label></dt>
											<dd><textarea id="description" name="description" cols="15" rows="5"><?=htmlspecialchars($blog['description'])?></textarea></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setBlog()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</form>
<?
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
?>
								<form id="multi-form" class="section" method="post" action="<?=$blogURL?>/owner/setting/blog/domain">
									<fieldset class="container">
										<legend><?=_t('블로그 주소')?></legend>
										
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
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setDomains()"><span class="text"><?=_t('저장하기')?></span></a>
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
							
							<form id="icons-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/setting/blog/icons" enctype="multipart/form-data">
								<div id="icons-upload-section" class="section">
									<fieldset class="container">
										<legend><?=_t('블로그 로고 및 파비콘')?></legend>
										
										<dl id="blog-picture-line" class="line">
											<dt><span class="label"><?=_t('로고 이미지')?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/{$blog['logo']}")) {
	$logoInfo = getimagesize(ROOT."/attach/$owner/{$blog['logo']}");
	if ($logoInfo[0] > 150) {
?>
												<a href="<?php echo $service['path']?>/attach/<?php echo $owner?>/<?php echo $blog['logo']?>" onclick="window.open(this.href); return false;"><img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/<?php echo $blog['logo']?>" width="150" border="1" alt="<?php echo _t('사용자 로고')?>" /></a>
<?php
	} else {
?>
												<img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/<?php echo $blog['logo']?>" border="1" alt="<?php echo _t('사용자 로고')?>" />
<?php
	}
}
?>

												<input type="file" class="file-input" name="logo" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteLogo" name="deleteLogo" value="yes"<?php echo empty($blog['logo']) ? ' disabled="disabled"' : '';?> /> <label for="deleteLogo"><?=_t('로고를 초기화합니다.')?></label></div>
											</dd>
										</dl>
										<dl id="favicon-line" class="line">
											<dt><span class="label"><?=_t('Favicon')?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/favicon.ico") && !eregi(' MSIE', $_SERVER['HTTP_USER_AGENT'], $temp)) {
?>
												<img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/favicon.ico" border="1" alt="<?php echo _t('파비콘')?>" />
<?php
} else {
?>
												<a id="favicon-preview" href="<?php echo $service['path']?>/attach/<?php echo $owner?>/favicon.ico"><?php echo _t('미리 보기')?></a>
<?php
}
?>
												<input type="file" class="file-input" name="favicon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteFavicon" name="deleteFavicon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/favicon.ico") ? '' : ' disabled="disabled"';?> /> <label for="deleteFavicon"><?=_t('파비콘을 초기화합니다.')?></label></div>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div id="blogicon-section" class="section">
									<fieldset class="container">
										<legend><?=_t('블로그 아이콘')?></legend>
										
										<dl id="blogicon-line" class="line">
											<dt><span class="label"><?=_t('블로그 아이콘')?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/index.gif")) {
	$blogIconInfo = getimagesize(ROOT."/attach/$owner/index.gif");
	if ($blogIconInfo != false) {
?>
												<img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/index.gif"<?php echo ($blogIconInfo[0] > 48) ? ' width="48"' : ''?> border="1" alt="<?php echo _t('블로그 아이콘')?>" />
<?php
	}
}
?>
												<input type="file" class="file-input" name="blogIcon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteBlogIcon" name="deleteBlogIcon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/index.gif") ? '' : ' disabled="disabled"';?> /> <label for="deleteBlogIcon"><?=_t('블로그 아이콘을 초기화합니다.')?></label></div>
												<p><?php echo _t('블로그 아이콘은 댓글과 방명록에서 사용됩니다. 크기는 16×16 이상, 48×48 이하까지 지원합니다.')?></p>
											</dd>
										</dl>
										<dl id="blogicon-size-line" class="line">
											<dt><span class="label"><?=_t('블로그 아이콘을')?></span></dt>
											<dd>
												<select name="blogIconSize">
													<option value="16"><?php echo _t('16×16 크기로 출력')?></option>
													<option value="32" selected="selected"><?php echo _t('32×32 크기로 출력')?></option>
													<option value="48"><?php echo _t('48×48 크기로 출력')?></option>
													<option value="max"><?php echo _t('사용할 수 있는 최대 크기로 출력.')?></option>
												</select>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div class="button-box">
									<a class="save-button button" href="#void" onclick="setIcons()"><span class="text"><?=_t('저장하기')?></span></a>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-thumbnail" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('썸네일을 설정합니다')?></span></h2>
							
							<div class="data-inbox">
								<form id="thumbnail-form" class="section" method="post" action="<?=$blogURL?>/owner/setting/blog/thumbnail" enctype="multipart/form-data">
									<fieldset class="container">
										<legend><?=_t('썸네일 정보')?></legend>
										
										<dl id="watermark-line" class="line">
											<dt><span class="label"><?=_t('워터 마크')?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/watermark.gif")) {			
	$waterMarkInfo = getimagesize(ROOT."/attach/$owner/watermark.gif");
	if ($waterMarkInfo[0] > 150) {
?>
												<a href="<?php echo $service['path']?>/attach/<?php echo $owner?>/watermark.gif" onclick="window.open(this.href); return false;"><img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/watermark.gif" width="150" border="1" alt="<?php echo _t('워터마크 이미지')?>" /></a>
<?php
	} else {
?>
												<img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/watermark.gif" border="1" alt="<?php echo _t('워터마크 이미지')?>" />
<?php
	}
}
?>
												<input type="file" class="file-input" name="waterMark" /><br />
												<input type="checkbox" class="checkbox" id="deleteWaterMark" name="deleteWaterMark" value="yes"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> /> <label for="deleteWaterMark"><?=_t('워터 마크를 초기화합니다.')?></label>
											</dd>
										</dl>
										<dl id="watermark-position-line" class="line">
											<dt><span class="label"><?=_t('워터마크 위치')?></span></dt>
											<dd>
												<div id="vertical-position">
													<select name="verticalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="top">상단</option>
														<option value="bottom">하단</option>
													</select>
													<input type="text" class="text-input" name="verticalPosition"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
												<div id="horizontal-position">
													<select name="horizontalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="left">좌측</option>
														<option value="right">우측</option>
													</select>
													<input type="text" class="text-input" name="horizontalPosition"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
											</dd>
										</dl>
										<dl id="padding-line" class="line">
											<dt><span class="label"><?=_t('여백')?></span></dt>
											<dd>
												<div id="top-padding">
													<span class="label"><?=_t('상단 여백')?></span>
													<select id="topPadding" name="topPadding" disabled="disabled" onchange="checkManualInput(this)">
														<option value="0">0px</option>
														<option value="5">5px</option>
														<option value="10">10px</option>
														<option value="15">15px</option>
														<option value="20">20px</option>
														<option value="25">25px</option>
														<option value="direct"><?=_t('직접입력.')?></option>
													</select>
													<input type="text" class="text-input" id="topPaddingManual" name="topPaddingManual" />px
												</div>
												<div id="bottom-padding">
													<span class="label"><?=_t('하단 여백')?></span>
													<select id="bottomPadding" name="bottomPadding" disabled="disabled" onchange="checkManualInput(this)">
														<option value="0">0px</option>
														<option value="5">5px</option>
														<option value="10">10px</option>
														<option value="15">15px</option>
														<option value="20">20px</option>
														<option value="25">25px</option>
														<option value="direct"><?=_t('직접입력.')?></option>
													</select>
													<input type="text" class="text-input" id="bottomPaddingManual" name="bottomPaddingManual" />px
												</div>
												<div id="left-padding">
													<span class="label"><?=_t('좌측 여백')?></span>
													<select id="leftPadding" name="leftPadding" disabled="disabled" onchange="checkManualInput(this)">
														<option value="0">0px</option>
														<option value="5">5px</option>
														<option value="10">10px</option>
														<option value="15">15px</option>
														<option value="20">20px</option>
														<option value="25">25px</option>
														<option value="direct"><?=_t('직접입력.')?></option>
													</select>
													<input type="text" class="text-input" id="leftPaddingManual" name="leftPaddingManual" />px
												</div>
												<div id="right-padding">
													<span class="label"><?=_t('우측 여백')?></span>
													<select id="rightPadding" name="rightPadding" disabled="disabled" onchange="checkManualInput(this)">
														<option value="0">0px</option>
														<option value="5">5px</option>
														<option value="10">10px</option>
														<option value="15">15px</option>
														<option value="20">20px</option>
														<option value="25">25px</option>
														<option value="direct"><?=_t('직접입력.')?></option>
													</select>
													<input type="text" class="text-input" id="rightPaddingManual" name="rightPaddingManual" />px
												</div>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setThumbnail()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</form>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-rss" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('블로그 공개 정책을 설정합니다')?></span></h2>
							
							<form id="rss-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/setting/blog">
								<div id="rss-section" class="section">
									<fieldset class="container">
										<legend><?=_t('RSS 설정')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
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
									</fieldset>
								</div>
								<div id="etc-section" class="section">
									<fieldset class="container">
										<legend><?=_t('기타 설정')?></legend>
										
										<dl id="post-address-line" class="line">
											<dt><span class="label"><?=_t('글 주소')?></span></dt>
											<dd>
												<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="useSlogan1"><span class="text"><?=_t('문자를 사용합니다.')?> <samp><?=_f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다)', getBlogURL())?></samp></span></label><br />
												<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?=($blog['useSlogan'] ? '' : ' checked="checked"')?> /> <label for="useSlogan0"><span class="text"><?=_t('숫자를 사용합니다.')?> <samp><?=_f('(예: %1/123)', getBlogURL())?></samp></span></label>
											</dd>
										</dl>
										<dl id="guestbook-authority-line" class="line">
											<dt><label for="allowCommentGuestbook"><?=_t('방명록 사용권한')?></label></dt>
											<dd>
												<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value=""<?=$blog['allowWriteOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowWriteGuestbook"><?=_t('손님이 글쓰기 허용')?></label-->
												<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?=$blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowCommentGuestbook"><?=_t('손님이 댓글쓰기 허용.')?></label>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setRSS()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-language" class="part">
							<h2 class="caption"><span class="main-text"><?=_t('언어, 시간대를 설정합니다')?></span></h2>
							
							<form id="language-form" class="data-inbox" method="post" action="<?=$blogURL?>/owner/setting/blog">
								<div id="language-section" class="section">
									<fieldset class="container">
										<legend><?=_t('언어 및 시간대')?></legend>
										
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
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setLocale()"><span class="text"><?=_t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>
