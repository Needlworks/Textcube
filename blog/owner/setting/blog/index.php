<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu50.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								var title = "<?php echo escapeJSInCData($blog['title'])?>";
								var description = "<?php echo escapeJSInCData(trim($blog['description']))?>";
								
								function setBlog() {
									if (document.getElementById('common-form').title.value != title) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/title?title=" + encodeURIComponent(document.getElementById('common-form').title.value));
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
											title = document.getElementById('common-form').title.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 제목을 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									if (document.getElementById('common-form').description.value != description) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL?>/owner/setting/blog/description/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
											description = document.getElementById('common-form').description.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 설명을 변경하지 못했습니다.')?>");
										}
										request.send("description=" + encodeURIComponent(document.getElementById('common-form').description.value));
									}
								}
<?php
if ($service['type'] != 'single') {
	if ($service['type'] == 'domain') {
?>
								var primaryDomain = "<?php echo escapeJSInCData($blog['name'])?>";
								var secondaryDomain = "<?php echo escapeJSInCData($blog['secondaryDomain'])?>";
								var defaultDomain = <?=escapeJSInCData($blog['defaultDomain'])?>;

								var newPrimaryDomain = primaryDomain;
								var newSecondaryDomain = secondaryDomain;
								var newDefaultDomain = defaultDomain;
<?php
	} else if ($service['type'] == 'path') {
?>
								var pathDomain = "<?php echo escapeJSInCData($blog['name'])?>";
								var newPathDomain = pathDomain;
<?php
	}
?>
								function setDomains() {
<?php
	if ($service['type'] == 'domain') {
?>
									if ((document.getElementById('multi-form').primaryDomain.value != primaryDomain) && (!checkBlogName(document.getElementById('multi-form').primaryDomain.value))) {
										alert("<?php echo _t('1차 블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-form').primaryDomain.focus();
										return;
									}
									if ((document.getElementById('multi-form').secondaryDomain.value != secondaryDomain) && (document.getElementById('multi-form').secondaryDomain.value.trim() != "") &&  (!checkDomainName(document.getElementById('multi-form').secondaryDomain.value))) {
										alert("<?php echo _t('2차 블로그 주소가 올바르지 않습니다.')?>");
										document.forms[0].secondaryDomain.focus();
										return;
									}
<?php
	} else if ($service['type'] == 'path') {
?>
									if ((document.getElementById('multi-form').pathDomain.value != pathDomain) && (!checkBlogName(document.getElementById('multi-form').pathDomain.value))) {
										alert("<?php echo _t('블로그 주소가 올바르지 않습니다.')?>");
										document.getElementById('multi-form').pathDomain.focus();
										return;
									}
<?php
	}
?>
									var location = null;
<?php
	if ($service['type'] == 'domain') {
?>
									newPrimaryDomain = document.getElementById('multi-form').primaryDomain.value;
									newSecondaryDomain = document.getElementById('multi-form').secondaryDomain.value;
									newDefaultDomain = document.getElementById('multi-form').defaultDomain[defaultDomain].checked ? 0 : 1;
								
									if(primaryDomain != newPrimaryDomain || secondaryDomain != newSecondaryDomain || defaultDomain != newDefaultDomain) {
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/domain/set/");
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
											if(newDefaultDomain == 0 && newPrimaryDomain != primaryDomain) {
												alert("<?=_t('변경된 1차 블로그 주소로 이동합니다')?>");
												window.location.href = "http://" + newPrimaryDomain + ".<?=$service['domain']?><?=$blogURL?>/owner/setting/blog";
											}
											else if(newDefaultDomain == 1 && newSecondaryDomain != secondaryDomain) {
												alert("<?=_t('변경된 2차 블로그 주소로 이동합니다')?>");
												window.location.href = "http://" + newSecondaryDomain + "<?=$blogURL?>/owner/setting/blog";
											}
											primaryDomain = newPrimaryDomain;
											secondaryDomain = newSecondaryDomain;
											defaultDomain = newDefaultDomain;
										}
										request.onError = function() {
											var msg = '';
											switch(parseInt(this.getText("/response/error"))) {
												case 1:
													msg = "<?=_t('기본 블로그 도메인을 변경하지 못했습니다')?>";
													break;
												case 2:
													msg = "<?=_t('1차 블로그 도메인을 변경하지 못했습니다')?>";						
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?=_t('올바르지 않은 블로그 주소입니다')?>";
															break;
														case 2:
															msg += "\n\n<?=_t('이미 사용중인 블로그 주소입니다')?>";
															break;
														case 3:
															msg += "\n\n<?=_t('이미 사용중인 블로그 주소입니다')?>";
															break;
													}						
													break;
												case 3:
													msg = "<?=_t('2차 블로그 주소를 변경하지 못했습니다')?>";
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?=_t('이미 사용중인 블로그 주소입니다')?>";
															break;
														case 2:
															msg += "\n\n<?=_t('올바르지 않은 블로그 주소입니다')?>";
															break;
													}						
													break;
												case 4:
													msg = "<?=_t('2차 블로그 주소가 비어있어서 기본 블로그 주소로 설정할 수 없습니다')?>";
													document.forms[0].defaultDomain[0].checked = true;
													break;
												default:
													msg = "<?=_t('알 수 없는 에러가 발생했습니다')?>";
											}
											alert(msg);
										}
										request.send("defaultDomain=" + newDefaultDomain + "&primaryDomain=" + encodeURIComponent(newPrimaryDomain) + "&secondaryDomain=" + encodeURIComponent(newSecondaryDomain));
									}
<?php
	} else if ($service['type'] == 'path') {
?>
									newPathDomain = document.getElementById('multi-form').pathDomain.value;
									if(pathDomain != newPathDomain) {
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/setting/domain/set/");
										request.onSuccess = function() {
											PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
											alert("<?=_t('변경된 블로그 주소로 이동합니다')?>");
											window.location.href = "http://<?=$service['domain']?><?=$service['path']?>/" + newPathDomain + "/owner/setting/blog";
											pathDomain = newPathDomain;
										}
										request.onError = function() {
											switch(parseInt(this.getText("/response/error"))) {
												case 1:
													alert("<?=_t('블로그 주소를 변경하지 못했습니다')?>");
													break;
												default:
													alert("<?=_t('알 수 없는 에러가 발생했습니다')?>");
											}
										}
										request.send("defaultDomain=0&primaryDomain=" + encodeURIComponent(newPathDomain) + "&secondaryDomain=");
									}

<?php
	}
?>
									if (location) {
										alert("<?php echo _t('변경된 기본 블로그 도메인으로 이동합니다.')?>");
										window.location.href = location;
									}
								}
<?php
}
?>
								
								function setIcons() {
									document.getElementById('icons-form').submit();
								}
								
								function setThumbnail() {
									document.getElementById('thumbnail-form').submit();
								}
								
								var useSlogan = "<?php echo $blog['useSlogan']?>";
								var publishEolinSyncOnRSS = "<?php echo $blog['publishEolinSyncOnRSS']?>";
								var entriesOnRSS = "<?php echo $blog['entriesOnRSS']?>";
								var publishWholeOnRSS = "<?php echo $blog['publishWholeOnRSS']?>";
								var allowCommentGuestbook = <?php echo $blog['allowWriteDoubleCommentOnGuestbook']?>;
								//var allowWriteGuestbook = <?php echo $blog['allowWriteOnGuestbook']?>;
								function setRSS() {
									if (document.getElementById('rss-form').useSlogan[useSlogan].checked == true) {
										if (document.getElementById('rss-form').useSlogan.value != useSlogan) {
											var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/slogan/" + (document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0));
											request.onSuccess = function() {
												useSlogan = document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0;
												PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
											}
											request.onError = function() {
												alert("<?php echo _t('글 주소 표기법을 변경할 수 없습니다.')?>");
											}
											request.send();
										}
									}
									
									var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/publishRSS/" + (document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0));
									request.onSuccess = function() {
										publishEolinSyncOnRSS = document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0;
										PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('RSS 공개 정도를 변경할 수 없습니다.')?>");
									}
									request.send();
									
									if (document.getElementById('rss-form').entriesOnRSS.value != entriesOnRSS) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/rss/entries/" + document.getElementById('rss-form').entriesOnRSS.value);
										request.onSuccess = function() {
											entriesOnRSS = document.getElementById('rss-form').entriesOnRSS.value;
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('RSS 글 개수를 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									
									if (document.getElementById('rss-form').publishWholeOnRSS.value != publishWholeOnRSS) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/rss/whole/" + document.getElementById('rss-form').publishWholeOnRSS.value);
										request.onSuccess = function() {
											publishWholeOnRSS = document.getElementById('rss-form').publishWholeOnRSS.value;
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('RSS 공개 범위를 변경하지 못했습니다.')?>");
										}
										request.send();
									}
									
									isAllowCommentGuestbook = document.getElementById('allowCommentGuestbook').checked ? 1 : 0;
									//isAllowWriteGuestbook = document.getElementById('allowWriteGuestbook').checked ? 1 : 0;
									if ( isAllowCommentGuestbook != allowCommentGuestbook) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/guestbook/?comment="+isAllowCommentGuestbook+"&write=1");
										request.onSuccess = function() {
											allowCommentGuestbook = isAllowCommentGuestbook;
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('실패했습니다.')?>");
										}
										request.send();
									}
								}
								
								var language = "<?php echo $blog['language']?>";
								var skinLanguage = "<?php echo $blog['blogLanguage']?>";
								var timezone = "<?php echo $blog['timezone']?>";
								
								function setLocale() {
									if ((document.getElementById('language-form').adminLanguage.value != language) || (document.getElementById('language-form').blogLanguage.value != skinLanguage)) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/language?language=" + encodeURIComponent(document.getElementById('language-form').adminLanguage.value) + "&blogLanguage=" + encodeURIComponent(document.getElementById('language-form').blogLanguage.value));
										request.onSuccess = function() {
											language = document.getElementById('language-form').adminLanguage.value;
											skinLanguage = document.getElementById('language-form').blogLanguage.value;
											if (document.getElementById('language-form').timezone.value != timezone) {
												PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
												var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.getElementById('language-form').timezone.value));
												request.onSuccess = function() {
													timezone = document.getElementById('language-form').timezone.value;
													window.location.href = "<?php echo $blogURL?>/owner/setting/blog";
												}
												request.onError = function() {
													alert("<?php echo _t('블로그 시간대를 변경할 수 없습니다.')?>");
													window.location.href = "<?php echo $blogURL?>/owner/setting/blog";
												}
												request.send();
											} else {
												window.location.href = "<?php echo $blogURL?>/owner/setting/blog";
											}
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 언어를 변경할 수 없습니다.')?>");
										}
										request.send();
									}
									else if (document.getElementById('language-form').timezone.value != timezone) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.getElementById('language-form').timezone.value));
										request.onSuccess = function() {
											timezone = document.getElementById('language-form').timezone.value;
											PM.showMessage("<?php echo _t('저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 시간대를 변경할 수 없습니다.')?>");
										}
										request.send();
									}
								}

								var editorMode = "<?php echo getUserSetting('editorMode', 1)?>";
								var strictXHTML = "<?php echo getUserSetting('strictXHTML', 0)?>";
								function setEditor() {
									if (document.getElementById('editor-form').editorMode.value != editorMode || document.getElementById('editor-form').strictXHTML.value != strictXHTML) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/setting/blog/editor/?editorMode=" + document.getElementById('editor-form').editorMode.value + "&strictXHTML=" + document.getElementById('editor-form').strictXHTML.value);
										request.onSuccess = function() {
											editorMode = document.getElementById('editor-form').editorMode.value;
											strictXHTML = document.getElementById('editor-form').strictXHTML.value;
											PM.showMessage("<?php echo _t('저장되었습니다')?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('에디터 설정을 변경할 수 없습니다')?>");
										}
									}
									request.send();
								}
								
								function setAdminSkin() {
									document.getElementById('admin-skin-form').submit();
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
									if (document.getElementById('topPadding').value != "direct") {
										document.getElementById('topPadding').removeAttribute('disabled');
										document.getElementById('topPaddingManual').setAttribute('disabled', 'disabled');
									} else {
										document.getElementById('topPadding').removeAttribute('disabled');
									}
									if (document.getElementById('rightPadding').value != "direct") {
										document.getElementById('rightPadding').removeAttribute('disabled');
										document.getElementById('rightPaddingManual').setAttribute('disabled', 'disabled');
									} else {
										document.getElementById('rightPadding').removeAttribute('disabled');
									}
									if (document.getElementById('bottomPadding').value != "direct") {
										document.getElementById('bottomPadding').removeAttribute('disabled');
										document.getElementById('bottomPaddingManual').setAttribute('disabled', 'disabled');
									} else {
										document.getElementById('bottomPadding').removeAttribute('disabled');
									}
									if (document.getElementById('leftPadding').value != "direct") {
										document.getElementById('leftPadding').removeAttribute('disabled');
										document.getElementById('leftPaddingManual').setAttribute('disabled', 'disabled');
									} else {
										document.getElementById('leftPadding').removeAttribute('disabled');
									}
								}
							//]]>
						</script>
						
						<div id="part-setting-basic" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 기본 정보를 설정합니다')?></span></h2>
							
							<div class="data-inbox">
								<form id="common-form" class="section" method="post" action="<?php echo $blogURL?>/owner/setting/blog/title">
									<fieldset class="container">
										<legend><?php echo _t('블로그 정보')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="blog-title-line" class="line">
											<dt><label for="title"><?php echo _t('블로그 제목')?></label></dt>
											<dd><input type="text" id="title" class="text-input" name="title" value="<?php echo htmlspecialchars($blog['title'])?>" /></dd>
										</dl>
										<dl id="blog-description-line" class="line">
											<dt><label for="description"><?php echo _t('블로그 설명')?></label></dt>
											<dd><textarea id="description" name="description" cols="15" rows="5"><?php echo htmlspecialchars($blog['description'])?></textarea></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setBlog()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</form>
<?php
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
?>
								<form id="multi-form" class="section" method="post" action="<?php echo $blogURL?>/owner/setting/blog/domain">
									<fieldset class="container">
										<legend><?php echo _t('블로그 주소')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
									
<?php
	if ($service['type'] == 'domain') {
?>
										<dl id="first-blog-address-line" class="line">
											<dt><label for="primaryDomain"><?php echo _t('1차 블로그 주소')?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? '' : 'checked="checked"')?> title="<?php echo _t('기본 도메인')?>" /> http://<input type="text" id="primaryDomain" class="text-input" name="primaryDomain" value="<?php echo escapeJSInAttribute($blog['name'])?>" /><?php echo $urlRule[1]?>
											</dd>
										</dl>
										<dl id="second-blog-address-line" class="line">
											<dt><label for="secondaryDomain"><?php echo _t('2차 블로그 주소')?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? 'checked="checked"' : '')?>title="<?php echo _t('기본 도메인')?>" /> http://<input type="text" id="secondaryDomain" class="text-input" name="secondaryDomain" value="<?php echo escapeJSInAttribute($blog['secondaryDomain'])?>" /><?php echo $blogURL?>
											</dd>
										</dl>
<?php
	} else {
?>
										<dl id="blog-address-line" class="line">
											<dt><label for="pathDomain"><?php echo _t('블로그 주소')?></label></dt>
											<dd><?php echo $urlRule[0]?><input type="text" id="pathDomain" class="text-input" name="pathDomain" value="<?php echo escapeJSInAttribute($blog['name'])?>" /></dd>
										</dl>
<?php
	}
?>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setDomains()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</form>
<?php
}
?>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-profile" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('프로필을 설정합니다')?></span></h2>
							
							<form id="icons-form" class="data-inbox" method="post" action="<?php echo $blogURL?>/owner/setting/blog/icons" enctype="multipart/form-data">
								<div id="icons-upload-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('블로그 로고 및 파비콘')?></legend>
										
										<dl id="blog-picture-line" class="line">
											<dt><span class="label"><?php echo _t('로고 이미지')?></span></dt>
											<dd>
<?php
if (!empty($blog['logo']) && file_exists(ROOT."/attach/$owner/{$blog['logo']}")) {
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
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteLogo" name="deleteLogo" value="yes"<?php echo empty($blog['logo']) ? ' disabled="disabled"' : '';?> /> <label for="deleteLogo"><?php echo _t('로고를 초기화합니다.')?></label></div>
											</dd>
										</dl>
										<dl id="favicon-line" class="line">
											<dt><span class="label"><?php echo _t('Favicon')?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/favicon.ico") && !eregi(' MSIE', $_SERVER['HTTP_USER_AGENT'], $temp)) {
?>
												<img src="<?php echo $service['path']?>/attach/<?php echo $owner?>/favicon.ico" border="1" alt="<?php echo _t('파비콘')?>" />
<?php
} else if (file_exists(ROOT."/attach/$owner/favicon.ico") && eregi(' MSIE', $_SERVER['HTTP_USER_AGENT'], $temp)) {
?>
												<a id="favicon-preview" href="<?php echo $service['path']?>/attach/<?php echo $owner?>/favicon.ico"><?php echo _t('미리 보기')?></a>
<?php
}
?>
												<input type="file" class="file-input" name="favicon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteFavicon" name="deleteFavicon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/favicon.ico") ? '' : ' disabled="disabled"';?> /> <label for="deleteFavicon"><?php echo _t('파비콘을 초기화합니다.')?></label></div>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div id="blogicon-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('블로그 아이콘')?></legend>
										
										<dl id="blogicon-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 아이콘')?></span></dt>
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
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteBlogIcon" name="deleteBlogIcon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/index.gif") ? '' : ' disabled="disabled"';?> /> <label for="deleteBlogIcon"><?php echo _t('블로그 아이콘을 초기화합니다.')?></label></div>
												<p><?php echo _t('블로그 아이콘은 댓글과 방명록에서 사용됩니다. 크기는 16×16 이상, 48×48 이하까지 지원합니다.')?></p>
											</dd>
										</dl>
<?php
$blogIconSize = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'blogIconSize'");
if ($blogIconSize == false) {
	$blogIconSize = "16";
}
?>
	
										<dl id="blogicon-size-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 아이콘을')?></span></dt>
											<dd>
												<select name="blogIconSize">
													<option value="16"<?php echo $blogIconSize == '16' ? ' selected="selected"' : ''?>><?php echo _t('16×16 크기로 출력')?></option>
													<option value="32"<?php echo $blogIconSize == '32' ? ' selected="selected"' : ''?>><?php echo _t('32×32 크기로 출력')?></option>
													<option value="48"<?php echo $blogIconSize == '48' ? ' selected="selected"' : ''?>><?php echo _t('48×48 크기로 출력')?></option>
													<!--option value="max"><?php echo _t('사용할 수 있는 최대 크기로 출력.')?></option-->
												</select>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div class="button-box">
									<a class="save-button button" href="#void" onclick="setIcons()"><span class="text"><?php echo _t('저장하기')?></span></a>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
<?php
if (extension_loaded('gd')) {
?>
						<div id="part-setting-thumbnail" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('썸네일을 설정합니다')?></span></h2>
							
							<div class="data-inbox">
								<form id="thumbnail-form" class="section" method="post" action="<?php echo $blogURL?>/owner/setting/blog/thumbnail" enctype="multipart/form-data">
									<fieldset class="container">
										<legend><?php echo _t('썸네일 정보')?></legend>
										
										<dl id="watermark-line" class="line">
											<dt><span class="label"><?php echo _t('워터 마크')?></span></dt>
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
												<input type="checkbox" class="checkbox" id="deleteWaterMark" name="deleteWaterMark" value="yes"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> /> <label for="deleteWaterMark"><?php echo _t('워터 마크를 초기화합니다.')?></label>
											</dd>
										</dl>
										<dl id="watermark-gamma-line" class="line">
											<dt><span class="label"><?php echo _t('워터마크 투명도')?></span></dt>
											<dd>
												<select name="gammaForWaterMark"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
<?php
	$gammaForWaterMark = getWaterMarkGamma();
	for ($i=100; $i>=0; $i--) {
?>
													<option value="<?php echo $i?>"<?php echo $i == $gammaForWaterMark ? ' selected="selected"' : ''?><?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>><?php echo $i?></option>
<?php
	}
?>
												</select>
												<p><?php echo _t('0은 완전투명(안 보임), 100은 완전 불투명.')?></p>
											</dd>
										</dl>
<?php
	$waterMarkPosition = getUserSetting("waterMarkPosition");
	if ($waterMarkPosition == false) {
		$bottom = 0;
		$center = 0;
		$horizontalValue = 0;
		$verticalValue = 0;
	} else {
		$waterMarkPosition = explode("|", $waterMarkPosition);
		parse_str($waterMarkPosition[0]);
		parse_str($waterMarkPosition[1]);
		
		if (isset($left))
			$horizontalValue = $left;
		if (isset($right))
			$horizontalValue = $right;
		if (isset($center))
			$horizontalValue = 0;
		if (isset($top))
			$verticalValue = $top;
		if (isset($bottom))
			$verticalValue = $bottom;
		if (isset($middle))
			$verticalValue = 0;
	}
?>
										<dl id="watermark-position-line" class="line">
											<dt><span class="label"><?php echo _t('워터마크 위치')?></span></dt>
											<dd>
												<div id="vertical-position">
													<select name="verticalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="top"<?php echo isset($top) ? ' selected="selected"' : ''?>>상단</option>
														<option value="middle"<?php echo isset($middle) ? ' selected="selected"' : ''?>>중앙</option>
														<option value="bottom"<?php echo isset($bottom) ? ' selected="selected"' : ''?>>하단</option>
													</select>
													<input type="text" class="text-input" name="verticalPosition" value="<?php echo $verticalValue?>"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
												<div id="horizontal-position">
													<select name="horizontalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="left"<?php echo isset($left) ? ' selected="selected"' : ''?>>좌측</option>
														<option value="center"<?php echo isset($center) ? ' selected="selected"' : ''?>>중앙</option>
														<option value="right"<?php echo isset($right) ? ' selected="selected"' : ''?>>우측</option>
													</select>
													<input type="text" class="text-input" name="horizontalPosition" value="<?php echo $horizontalValue?>" <?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
											</dd>
										</dl>
										<dl id="watermark-resize-line" class="line">
											<dt><span class="label"><?php echo _t('워터마크 자동 조절')?></span></dt>
											<dd>
												<input type="radio" id="waterMarkAutoResizeYes" class="radio" name="waterMarkAutoResize"<?php echo ($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="waterMarkAutoResizeYes"><span class="text"><?php echo _t('이미지 크기가 워터마크보다 작은 경우, 워터마크의 크기를 이미지에 맞춥니다.')?></span></label><br />
												<input type="radio" id="waterMarkAutoResizeNo" class="radio" name="waterMarkAutoResize"<?php echo ($blog['useSlogan'] ? '' : ' checked="checked"')?> /> <label for="waterMarkAutoResizeNo"><span class="text"><?php echo _t('이미지 크기가 워터마크보다 작아도 워터마크의 크기를 조절하지 않습니다.')?></span></label>
											</dd>
										</dl>
<?php
	$thumbnailPadding = getThumbnailPadding();
	$colorOfPadding = DBQuery::queryCell("SELECT `value` FROM `{$database['prefix']}UserSettings` WHERE `user` = $owner AND `name` = 'thumbnailPaddingColor'");
	if ($colorOfPadding == false) {
		$colorOfPadding = "FFFFFF";
	}
?>
										<dl id="padding-line" class="line">
											<dt><span class="label"><?php echo _t('썸네일 여백')?></span></dt>
											<dd>
<?php
	$paddingOrder = array("top", "bottom", "left", "right");
	$paddingText = array(_t('상단 여백'), _t('하단 여백'), _t('좌측 여백'), _t('우측 여백'));
	
	for ($i=0; $i<count($paddingOrder); $i++) {
		$tempIndex = $paddingOrder[$i];
?>
												<div id="<?php echo $paddingOrder[$i]?>-padding">
													<span class="label"><?php echo $paddingText[$i]?></span>
													<select id="<?php echo $paddingOrder[$i]?>Padding" name="<?php echo $paddingOrder[$i]?>Padding" onchange="checkManualInput(this)" disabled="disabled">
														<option value="0"<?php echo $thumbnailPadding[$tempIndex] == 0 ? ' selected="selected"' : ''?>>0px</option>
														<option value="5"<?php echo $thumbnailPadding[$tempIndex] == 5 ? ' selected="selected"' : ''?>>5px</option>
														<option value="10"<?php echo $thumbnailPadding[$tempIndex] == 10 ? ' selected="selected"' : ''?>>10px</option>
														<option value="15"<?php echo $thumbnailPadding[$tempIndex] == 15 ? ' selected="selected"' : ''?>>15px</option>
														<option value="20"<?php echo $thumbnailPadding[$tempIndex] == 20 ? ' selected="selected"' : ''?>>20px</option>
														<option value="25"<?php echo $thumbnailPadding[$tempIndex] == 25 ? ' selected="selected"' : ''?>>25px</option>
														<option value="direct"<?php echo !in_array($thumbnailPadding[$tempIndex], array(0, 5, 10, 15, 20, 25)) ? ' selected="selected"' : ''?>><?php echo _t('직접입력')?></option>
													</select>
													<input type="text" class="text-input" id="<?php echo $paddingOrder[$i]?>PaddingManual" name="<?php echo $paddingOrder[$i]?>PaddingManual" value="<?php echo ${'thumbnail'.ucfirst($paddingOrder[$i]).'Padding'}?>" />px
												</div>
<?php
	}
?>
												<div id="padding-color">
													<span class="label"><?php echo _t('여백 색상')?></span>
													<input type="text" class="text-input" name="paddingColor" value="<?php echo $colorOfPadding?>" />
												</div>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setThumbnail()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</form>
							</div>
						</div>
						
						<hr class="hidden" />
						
<?php
}
?>
						<div id="part-setting-rss" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 공개 정책을 설정합니다')?></span></h2>
							
							<form id="rss-form" class="data-inbox" method="post" action="<?php echo $blogURL?>/owner/setting/blog">
								<div id="rss-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('RSS 설정')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="open-type-line" class="line">
											<dt><span class="label"><?php echo _t('공개 정도')?></span></dt>
											<dd>
												<input type="radio" id="publishEolinSyncOnRSS1" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="publishEolinSyncOnRSS1"><span class="text"><?php echo _t('공개된 모든 글을 <acronym title="Rich Site Summary">RSS</acronym>로 내보냅니다.')?></span></label><br />
												<input type="radio" id="publishEolinSyncOnRSS0" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['publishEolinSyncOnRSS'] ? '' : ' checked="checked"')?> /> <label for="publishEolinSyncOnRSS0"><span class="text"><?php echo _t('이올린에 발행된 글만을 RSS로 내보냅니다.')?></span></label>
											</dd>
										</dl>
										<dl id="post-count-line" class="line">
											<dt><label for="entriesOnRSS"><?php echo _t('글 개수')?></label></dt>
											<dd>
												<?php echo getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 0)?>
												<select id="entriesOnRSS" name="entriesOnRSS">
<?php
for ($i = 5; $i <= 30; $i += 5) {
?>
													<option value="<?php echo $i?>"<?php echo ($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '')?>><?php echo $i?></option>
<?php
}
?>
												</select><?php echo getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 1)?>
											</dd>
										</dl>
										<dl id="open-range-line" class="line">
											<dt><label for="publishWholeOnRSS"><?php echo _t('공개 범위')?></label></dt>
											<dd><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 0)?>
												<select id="publishWholeOnRSS" name="publishWholeOnRSS">
													<option value="1"<?php echo ($blog['publishWholeOnRSS'] ? ' selected="selected"' : '')?>><?php echo _t('전체공개')?></option>
													<option value="0"<?php echo ($blog['publishWholeOnRSS'] ? '' : ' selected="selected"')?>><?php echo _t('부분공개')?></option>
												</select><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 1)?>
											</dd>
										</dl>
									</fieldset>
								</div>
								<div id="etc-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('기타 설정')?></legend>
										
										<dl id="post-address-line" class="line">
											<dt><span class="label"><?php echo _t('글 주소')?></span></dt>
											<dd>
												<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?php echo ($blog['useSlogan'] ? ' checked="checked"' : '')?> /> <label for="useSlogan1"><span class="text"><?php echo _t('문자를 사용합니다.')?> <samp><?php echo _f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다)', getBlogURL())?></samp></span></label><br />
												<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?php echo ($blog['useSlogan'] ? '' : ' checked="checked"')?> /> <label for="useSlogan0"><span class="text"><?php echo _t('숫자를 사용합니다.')?> <samp><?php echo _f('(예: %1/123)', getBlogURL())?></samp></span></label>
											</dd>
										</dl>
										<dl id="guestbook-authority-line" class="line">
											<dt><label for="allowCommentGuestbook"><?php echo _t('방명록 사용권한')?></label></dt>
											<dd>
												<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowWriteGuestbook"><?php echo _t('손님이 글쓰기 허용')?></label-->
												<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? ' checked="checked"' : ""?> /> <label for="allowCommentGuestbook"><?php echo _t('손님이 댓글쓰기 허용.')?></label>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setRSS()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-language" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('언어, 시간대를 설정합니다')?></span></h2>
							
							<form id="language-form" class="data-inbox" method="post" action="<?php echo $blogURL?>/owner/setting/blog">
								<div id="language-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('언어 및 시간대')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="admin-language-line" class="line">
											<dt><label for="admin-language"><?php echo _t('관리자 화면 언어')?></label></dt>
											<dd>
												<select id="admin-language" name="adminLanguage">
<?php
$supportedLanguages = Locale::getSupportedLocales();
foreach ($supportedLanguages as $locale => $language) {
?>
													<option value="<?php echo $locale?>"<?php echo ($locale == $blog['language'] ? ' selected="selected"' : '')?>><?php echo $language?></option>
<?php
}
?>
												</select>
											</dd>
										</dl>
										<dl id="blog-language-line" class="line">
											<dt><label for="blog-language"><?php echo _t('블로그 언어')?></label></dt>
											<dd>
												<select id="blog-language" name="blogLanguage">
<?php
foreach ($supportedLanguages as $locale => $language) {
?>
													<option value="<?php echo $locale?>"<?php echo ($locale == $blog['blogLanguage'] ? ' selected="selected"' : '')?>><?php echo $language?></option>
<?php
}
?>
												</select>
												<p><?php echo _t('이 언어설정은 외부 블로그에 표시되는 메세지의 언어를 설정합니다.<br />한국어 블로그를 운영하고 계신다면 한국어를 선택해 주십시오.')?></p>
											</dd>
										</dl>
										<dl id="timezone-line" class="line">
											<dt><label for="timezone"><?php echo _t('시간대')?></label></dt>
											<dd>
												<select id="timezone" name="timezone">
<?php
foreach (Timezone::getList() as $timezone) {
?>
													<option value="<?php echo $timezone?>"<?php echo ($timezone == $blog['timezone'] ? ' selected="selected"' : '')?>><?php echo _t($timezone)?></option>
<?php
}
?>
												</select>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setLocale()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>

						<hr class="hidden" />
						
						<div id="part-setting-editor" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('글 작성 환경을 설정합니다')?></span></h2>
							
							<form id="editor-form" class="data-inbox" method="post" action="<?php echo $blogURL?>/owner/setting/blog">
								<div id="editor-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('글 작성 환경을 설정합니다')?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="editor-line" class="line">
											<dt><label for="editorMode"><?php echo _t('기본 작성 모드')?></label></dt>
											<dd>
<?php
$editorMode = getUserSetting('editorMode', 1);
?>
												<select id="editorMode" name="editorMode">
													<option value="1"<?php echo $editorMode==1?' selected="selected"':''?>><?php echo _t('위지윅 모드')?></option>
													<option value="2"<?php echo $editorMode==2?' selected="selected"':''?>><?php echo _t('HTML 직접 편집')?></option>
												</select>
											</dd>
										</dl>
										<dl id="strictXHTML-line" class="line">
											<dt><label for="strictXHTML"><?php echo _t('<abbr title="eXtensible HyperText Markup Language">XHTML</abbr> 준수')?></label></dt>
											<dd>
<?php
$strictXHTML = getUserSetting('strictXHTML', 0);
?>
												<select id="strictXHTML" name="strictXHTML">
													<option value="0"<?php echo $strictXHTML==0?' selected="selected"':''?>><?php echo _t('처리하지 않음')?></option>
													<option value="1"<?php echo $strictXHTML==1?' selected="selected"':''?>><?php echo _t('올바른 XHTML 코드로 다듬어 출력')?></option>
												</select>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setEditor()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-admin" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('관리자 화면 스킨을 설정합니다')?></span></h2>
							
							<form id="admin-skin-form" class="data-inbox" method="post" action="<?php echo $blogURL?>/owner/setting/blog/skin">
								<div id="admin-skin-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('관리자 스킨을 설정합니다')?></legend>
										
										<dl id="admin-skin--line" class="line">
											<dt><label for="adminSkin"><?php echo _t('관리자 화면용 스킨')?></label></dt>
											<dd>
												<select id="adminSkin" name="adminSkin">
<?php
$currentAdminSkin = getUserSetting("adminSkin", "default");
$dir = dir(ROOT . '/style/admin/');
while ($tempAdminSkin = $dir->read()) {
	if (!ereg('^[[:alnum:] _-]+$', $tempAdminSkin))
		continue;
	if (!is_dir(ROOT . '/style/admin/' . $tempAdminSkin))
		continue;
	if (!file_exists(ROOT . "/style/admin/$tempAdminSkin/index.xml"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/style/admin/$tempAdminSkin/index.xml"))) {
		continue;
	} else {
		$skinDir = trim($tempAdminSkin);
		$skinName = htmlspecialchars($xmls->getValue('/adminSkin/information/name[lang()]'));
?>
													<option value="<?php echo $skinDir?>"<?php echo $currentAdminSkin==$skinDir ?' selected="selected"':''?>><?php echo $skinName?></option>
<?php
	}
}
?>
												</select>
											</dd>
										</dl>
										<dl id="editor-template-line" class="line">
											<dt><label for="editorTemplate"><?php echo _t('위지윅 에디터 템플릿')?></label></dt>
											<dd>
												<select id="editorTemplate" name="editorTemplate">
<?php
$editorTemplate = getUserSetting('visualEditorTemplate');
?>
													<option value=""<?php echo empty($editorTemplate)?' selected="selected"':''?>><?php echo _t('기본 템플릿')?></option>
<?php
$dirHandler = dir(ROOT . "/skin");
while ($dir = $dirHandler->read()) {
	if (!ereg('^[[:alnum:] _-]+$', $dir))
		continue;
	if (!is_dir(ROOT . '/skin/' . $dir))
		continue;
	if (!file_exists(ROOT . "/skin/$dir/index.xml") || !file_exists(ROOT . "/skin/$dir/wysiwyg.css"))
		continue;
	$xmls = new XMLStruct();
	if (!$xmls->open(file_get_contents(ROOT . "/skin/$dir/index.xml"))) {
		continue;
	} else {
		$skinName = $xmls->getValue('/skin/information/name');
?>
													<option value="<?php echo trim($dir)?>"<?php echo $editorTemplate==$dir?' selected="selected"':''?>><?php echo _f('%1 스킨의 템플릿', $skinName)?></option>
<?php
	}
}
?>
												</select>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<a class="save-button button" href="#void" onclick="setAdminSkin()"><span class="text"><?php echo _t('저장하기')?></span></a>
									</div>
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
