<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu50.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								var title = "<?php echo escapeJSInCData($blog['title']);?>";
								var description = "<?php echo escapeJSInCData(trim($blog['description']));?>";
								
								function setBlog() {
									if (document.getElementById('common-form').title.value != title) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/title?title=" + encodeURIComponent(document.getElementById('common-form').title.value));
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
											title = document.getElementById('common-form').title.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 제목을 변경하지 못했습니다.');?>");
										}
										request.send();
									}
									if (document.getElementById('common-form').description.value != description) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/description/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
											description = document.getElementById('common-form').description.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 설명을 변경하지 못했습니다.');?>");
										}
										request.send("description=" + encodeURIComponent(document.getElementById('common-form').description.value));
									}
								}
<?php
if ($service['type'] != 'single') {
	if ($service['type'] == 'domain') {
?>
								var primaryDomain = "<?php echo escapeJSInCData($blog['name']);?>";
								var secondaryDomain = "<?php echo escapeJSInCData($blog['secondaryDomain']);?>";
								var defaultDomain = <?php echo escapeJSInCData($blog['defaultDomain']);?>;

								var newPrimaryDomain = primaryDomain;
								var newSecondaryDomain = secondaryDomain;
								var newDefaultDomain = defaultDomain;
<?php
	} else if ($service['type'] == 'path') {
?>
								var pathDomain = "<?php echo escapeJSInCData($blog['name']);?>";
								var newPathDomain = pathDomain;
<?php
	}
?>
								function setDomains() {
<?php
	if ($service['type'] == 'domain') {
?>
									if ((document.getElementById('multi-form').primaryDomain.value != primaryDomain) && (!checkBlogName(document.getElementById('multi-form').primaryDomain.value))) {
										alert("<?php echo _t('1차 블로그 주소가 올바르지 않습니다.');?>");
										document.getElementById('multi-form').primaryDomain.focus();
										return;
									}
									if ((document.getElementById('multi-form').secondaryDomain.value != secondaryDomain) && (document.getElementById('multi-form').secondaryDomain.value.trim() != "") &&  (!checkDomainName(document.getElementById('multi-form').secondaryDomain.value))) {
										alert("<?php echo _t('2차 블로그 주소가 올바르지 않습니다.');?>");
										document.forms[0].secondaryDomain.focus();
										return;
									}
<?php
	} else if ($service['type'] == 'path') {
?>
									if ((document.getElementById('multi-form').pathDomain.value != pathDomain) && (!checkBlogName(document.getElementById('multi-form').pathDomain.value))) {
										alert("<?php echo _t('블로그 주소가 올바르지 않습니다.');?>");
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
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/domain/set/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											if(newDefaultDomain == 0 && newPrimaryDomain != primaryDomain) {
												alert("<?php echo _t('변경된 1차 블로그 주소로 이동합니다');?>");
												window.location.href = "http://" + newPrimaryDomain + ".<?php echo $service['domain'];?><?php echo $blogURL;?>/owner/setting/blog";
											}
											else if(newDefaultDomain == 1 && newSecondaryDomain != secondaryDomain) {
												alert("<?php echo _t('변경된 2차 블로그 주소로 이동합니다');?>");
												window.location.href = "http://" + newSecondaryDomain + "<?php echo $blogURL;?>/owner/setting/blog";
											}
											primaryDomain = newPrimaryDomain;
											secondaryDomain = newSecondaryDomain;
											defaultDomain = newDefaultDomain;
										}
										request.onError = function() {
											var msg = '';
											switch(parseInt(this.getText("/response/error"))) {
												case 1:
													msg = "<?php echo _t('기본 블로그 도메인을 변경하지 못했습니다');?>";
													break;
												case 2:
													msg = "<?php echo _t('1차 블로그 도메인을 변경하지 못했습니다');?>";						
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?php echo _t('올바르지 않은 블로그 주소입니다');?>";
															break;
														case 2:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
														case 3:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
													}						
													break;
												case 3:
													msg = "<?php echo _t('2차 블로그 주소를 변경하지 못했습니다');?>";
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
														case 2:
															msg += "\n\n<?php echo _t('올바르지 않은 블로그 주소입니다');?>";
															break;
													}						
													break;
												case 4:
													msg = "<?php echo _t('2차 블로그 주소가 비어있어서 기본 블로그 주소로 설정할 수 없습니다');?>";
													document.forms[0].defaultDomain[0].checked = true;
													break;
												default:
													msg = "<?php echo _t('알 수 없는 에러가 발생했습니다');?>";
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
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/domain/set/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											alert("<?php echo _t('변경된 블로그 주소로 이동합니다');?>");
											window.location.href = "http://<?php echo $service['domain'];?><?php echo $service['path'];?>/" + newPathDomain + "/owner/setting/blog";
											pathDomain = newPathDomain;
										}
										request.onError = function() {
											var msg = '';
											switch(parseInt(this.getText("/response/error"))) {
												case 1:
													msg = "<?php echo _t('블로그 주소를 변경하지 못했습니다');?>";
													break;
												case 2:
													msg = "<?php echo _t('1차 블로그 도메인을 변경하지 못했습니다');?>";						
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?php echo _t('올바르지 않은 블로그 주소입니다');?>";
															break;
														case 2:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
														case 3:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
													}						
													break;
												case 3:
													msg = "<?php echo _t('2차 블로그 주소를 변경하지 못했습니다');?>";
													switch(parseInt(this.getText("/response/msg"))) {
														case 1:
															msg += "\n\n<?php echo _t('이미 사용중인 블로그 주소입니다');?>";
															break;
														case 2:
															msg += "\n\n<?php echo _t('올바르지 않은 블로그 주소입니다');?>";
															break;
													}						
													break;
												default:
													msg = "<?php echo _t('알 수 없는 에러가 발생했습니다');?>";
											}
											alert(msg);
										}
										request.send("defaultDomain=0&primaryDomain=" + encodeURIComponent(newPathDomain) + "&secondaryDomain=");
									}

<?php
	}
?>
									if (location) {
										alert("<?php echo _t('변경된 기본 블로그 도메인으로 이동합니다.');?>");
										window.location.href = location;
									}
								}
<?php
}
?>
								
								function setIcons() {
									document.getElementById('icons-form').submit();
								}
								
								var useSlogan = "<?php echo $blog['useSlogan'];?>";
								var publishEolinSyncOnRSS = "<?php echo $blog['publishEolinSyncOnRSS'];?>";
								var entriesOnRSS = "<?php echo $blog['entriesOnRSS'];?>";
								var publishWholeOnRSS = "<?php echo $blog['publishWholeOnRSS'];?>";
								var allowCommentGuestbook = <?php echo $blog['allowWriteDoubleCommentOnGuestbook'];?>;
								//var allowWriteGuestbook = <?php echo $blog['allowWriteOnGuestbook'];?>;
								function setRSS() {
									if (document.getElementById('rss-form').useSlogan[useSlogan].checked == true) {
										if (document.getElementById('rss-form').useSlogan.value != useSlogan) {
											var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/slogan/" + (document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0));
											request.onSuccess = function() {
												useSlogan = document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0;
												PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
											}
											request.onError = function() {
												alert("<?php echo _t('글 주소 표기법을 변경할 수 없습니다.');?>");
											}
											request.send();
										}
									}
									
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/publishRSS/" + (document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0));
									request.onSuccess = function() {
										publishEolinSyncOnRSS = document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0;
										PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('RSS 공개 정도를 변경할 수 없습니다.');?>");
									}
									request.send();
									
									if (document.getElementById('rss-form').entriesOnRSS.value != entriesOnRSS) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/rss/entries/" + document.getElementById('rss-form').entriesOnRSS.value);
										request.onSuccess = function() {
											entriesOnRSS = document.getElementById('rss-form').entriesOnRSS.value;
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('RSS 글 개수를 변경하지 못했습니다.');?>");
										}
										request.send();
									}
									
									if (document.getElementById('rss-form').publishWholeOnRSS.value != publishWholeOnRSS) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/rss/whole/" + document.getElementById('rss-form').publishWholeOnRSS.value);
										request.onSuccess = function() {
											publishWholeOnRSS = document.getElementById('rss-form').publishWholeOnRSS.value;
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('RSS 공개 범위를 변경하지 못했습니다.');?>");
										}
										request.send();
									}
									
									isAllowCommentGuestbook = document.getElementById('allowCommentGuestbook').checked ? 1 : 0;
									//isAllowWriteGuestbook = document.getElementById('allowWriteGuestbook').checked ? 1 : 0;
									if ( isAllowCommentGuestbook != allowCommentGuestbook) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/guestbook/?comment="+isAllowCommentGuestbook+"&write=1");
										request.onSuccess = function() {
											allowCommentGuestbook = isAllowCommentGuestbook;
											PM.showMessage("<?php echo _t('저장되었습니다.');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('실패했습니다.');?>");
										}
										request.send();
									}
								}
								
							//]]>
						</script>
						
						<div id="part-setting-basic" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 기본 정보를 설정합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="common-form" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/blog/title">
									<fieldset class="container">
										<legend><?php echo _t('블로그 정보');?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="blog-title-line" class="line">
											<dt><label for="title"><?php echo _t('블로그 제목');?></label></dt>
											<dd><input type="text" id="title" class="input-text" name="title" value="<?php echo htmlspecialchars($blog['title']);?>" /></dd>
										</dl>
										<dl id="blog-description-line" class="line">
											<dt><label for="description"><?php echo _t('블로그 설명');?></label></dt>
											<dd><textarea id="description" name="description" cols="15" rows="5"><?php echo htmlspecialchars($blog['description']);?></textarea></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setBlog(); return false;" />
									</div>
								</form>
<?php
$urlRule = getBlogURLRule();
if ($service['type'] != 'single') {
?>
								<form id="multi-form" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/blog/domain">
									<fieldset class="container">
										<legend><?php echo _t('블로그 주소');?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
									
<?php
	if ($service['type'] == 'domain') {
?>
										<dl id="first-blog-address-line" class="line">
											<dt><label for="primaryDomain"><?php echo _t('1차 블로그 주소');?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? '' : 'checked="checked"');?> title="<?php echo _t('기본 도메인');?>" /> http://<input type="text" id="primaryDomain" class="input-text" name="primaryDomain" value="<?php echo escapeJSInAttribute($blog['name']);?>" /><?php echo $urlRule[1];?>
											</dd>
										</dl>
										<dl id="second-blog-address-line" class="line">
											<dt><label for="secondaryDomain"><?php echo _t('2차 블로그 주소');?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? 'checked="checked"' : '');?>title="<?php echo _t('기본 도메인');?>" /> http://<input type="text" id="secondaryDomain" class="input-text" name="secondaryDomain" value="<?php echo escapeJSInAttribute($blog['secondaryDomain']);?>" /><?php echo $blogURL;?>
											</dd>
										</dl>
<?php
	} else {
?>
										<dl id="blog-address-line" class="line">
											<dt><label for="pathDomain"><?php echo _t('블로그 주소');?></label></dt>
											<dd><?php echo $urlRule[0];?><input type="text" id="pathDomain" class="input-text" name="pathDomain" value="<?php echo escapeJSInAttribute($blog['name']);?>" /></dd>
										</dl>
<?php
	}
?>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setDomains(); return false;" />
									</div>
								</form>
<?php
}
?>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-profile" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('프로필을 설정합니다');?></span></h2>
							
							<form id="icons-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/setting/blog/icons" enctype="multipart/form-data">
								<div id="icons-upload-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('블로그 로고 및 파비콘');?></legend>
										
										<dl id="blog-picture-line" class="line">
											<dt><span class="label"><?php echo _t('로고 이미지');?></span></dt>
											<dd>
<?php
if (!empty($blog['logo']) && file_exists(ROOT."/attach/$owner/{$blog['logo']}")) {
	$logoInfo = @getimagesize(ROOT."/attach/$owner/{$blog['logo']}");
	if ($logoInfo[0] > 150) {
?>
												<a href="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/<?php echo $blog['logo'];?>" onclick="window.open(this.href); return false;"><img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/<?php echo $blog['logo'];?>" width="150" border="1" alt="<?php echo _t('사용자 로고');?>" /></a>
<?php
	} else {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/<?php echo $blog['logo'];?>" border="1" alt="<?php echo _t('사용자 로고');?>" />
<?php
	}
}
?>

												<input type="file" class="input-file" name="logo" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteLogo" name="deleteLogo" value="yes"<?php echo empty($blog['logo']) ? ' disabled="disabled"' : '';?> /> <label for="deleteLogo"><?php echo _t('로고를 초기화합니다.');?></label></div>
											</dd>
										</dl>
										<dl id="favicon-line" class="line">
											<dt><span class="label"><?php echo _t('Favicon');?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/favicon.ico") && !eregi(' MSIE', $_SERVER['HTTP_USER_AGENT'], $temp)) {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/favicon.ico" border="1" alt="<?php echo _t('파비콘');?>" />
<?php
} else if (file_exists(ROOT."/attach/$owner/favicon.ico") && eregi(' MSIE', $_SERVER['HTTP_USER_AGENT'], $temp)) {
?>
												<a id="favicon-preview" href="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/favicon.ico"><?php echo _t('미리 보기');?></a>
<?php
}
?>
												<input type="file" class="input-file" name="favicon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteFavicon" name="deleteFavicon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/favicon.ico") ? '' : ' disabled="disabled"';?> /> <label for="deleteFavicon"><?php echo _t('파비콘을 초기화합니다.');?></label></div>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div id="blogicon-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('블로그 아이콘');?></legend>
										
										<dl id="blogicon-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 아이콘');?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$owner/index.gif")) {
	$blogIconInfo = getimagesize(ROOT."/attach/$owner/index.gif");
	if ($blogIconInfo != false) {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/index.gif"<?php echo ($blogIconInfo[0] > 48) ? ' width="48"' : '';?> border="1" alt="<?php echo _t('블로그 아이콘');?>" />
<?php
	}
}
?>
												<input type="file" class="input-file" name="blogIcon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteBlogIcon" name="deleteBlogIcon" value="yes"<?php echo file_exists(ROOT."/attach/$owner/index.gif") ? '' : ' disabled="disabled"';?> /> <label for="deleteBlogIcon"><?php echo _t('블로그 아이콘을 초기화합니다.');?></label></div>
												<p><?php echo _t('블로그 아이콘은 댓글과 방명록에서 사용됩니다. 크기는 16×16 이상, 48×48 이하까지 지원합니다.');?></p>
											</dd>
										</dl>
									</fieldset>
								</div>
								
								<div class="button-box">
									<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setIcons(); return false;" />
								</div>
							</form>
						</div>
						
						<hr class="hidden" />
						
<?php
if ($blog['publishWholeOnRSS'] == false && $blog['publishEolinSyncOnRSS'] == false) {
	DBQuery::execute("UPDATE `{$database['prefix']}BlogSetting` SET `publishWholeOnRSS` = 1 WHERE `owner` = $owner");
} else if ($blog['publishWholeOnRSS'] == true && $blog['publishEolinSyncOnRSS'] == true) {
	DBQuery::execute("UPDATE `{$database['prefix']}BlogSetting` SET `publishWholeOnRSS` = 0 WHERE `owner` = $owner");
}
?>
						<div id="part-setting-rss" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 공개 정책을 설정합니다');?></span></h2>
							
							<form id="rss-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/setting/blog">
								<div id="rss-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('RSS 설정');?></legend>
										
										<input type="hidden" name="javascript" value="disabled" />
										
										<dl id="open-type-line" class="line">
											<dt><span class="label"><?php echo _t('공개 정도');?></span></dt>
											<dd>
												<input type="radio" id="publishEolinSyncOnRSS1" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['publishEolinSyncOnRSS'] ? ' checked="checked"' : '');?> /> <label for="publishEolinSyncOnRSS1"><span class="text"><?php echo _t('공개된 모든 글을 <acronym title="Rich Site Summary">RSS</acronym>로 내보냅니다.');?></span></label><br />
												<input type="radio" id="publishEolinSyncOnRSS0" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['publishEolinSyncOnRSS'] ?   '' : 'checked="checked"');?> /> <label for="publishEolinSyncOnRSS0"><span class="text"><?php echo _t('이올린에 발행된 글만을 RSS로 내보냅니다.');?></span></label>
											</dd>

										</dl>
										<dl id="post-count-line" class="line">
											<dt><label for="entriesOnRSS"><?php echo _t('글 개수');?></label></dt>
											<dd>
												<?php echo getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 0);?>
												<select id="entriesOnRSS" name="entriesOnRSS">
<?php
for ($i = 5; $i <= 30; $i += 5) {
?>
													<option value="<?php echo $i;?>"<?php echo ($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '');?>><?php echo $i;?></option>
<?php
}
?>
												</select><?php echo getArrayValue(explode('%1', _t('RSS 파일의 블로그 글은 최신 %1개로 갱신됩니다.')), 1);?>
											</dd>
										</dl>
										<dl id="open-range-line" class="line">
											<dt><label for="publishWholeOnRSS"><?php echo _t('공개 범위');?></label></dt>
											<dd><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 0);?>
												<select id="publishWholeOnRSS" name="publishWholeOnRSS">
													<option value="1"<?php echo ($blog['publishWholeOnRSS'] ? ' selected="selected"' : '');?>><?php echo _t('전체공개');?></option>
													<option value="0"<?php echo ($blog['publishWholeOnRSS'] ? '' : ' selected="selected"');?>><?php echo _t('부분공개');?></option>
												</select><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 1);?>
											</dd>

										</dl>
									</fieldset>
								</div>
								<div id="etc-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('기타 설정');?></legend>
										
										<dl id="post-address-line" class="line">
											<dt><span class="label"><?php echo _t('글 주소');?></span></dt>
											<dd>
												<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?php echo ($blog['useSlogan'] ? ' checked="checked"' : '');?> /> <label for="useSlogan1"><span class="text"><?php echo _t('문자를 사용합니다.');?> <samp><?php echo _f('(예: %1/entry/태터툴즈로-오신-것을-환영합니다)',link_cut(getBlogURL()));?></samp></span></label><br />
												<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?php echo ($blog['useSlogan'] ? '' : ' checked="checked"');?> /> <label for="useSlogan0"><span class="text"><?php echo _t('숫자를 사용합니다.');?> <samp><?php echo _f('(예: %1/123)', link_cut(getBlogURL()));?></samp></span></label>
											</dd>
										</dl>
										<dl id="guestbook-authority-line" class="line">
											<dt><label for="allowCommentGuestbook"><?php echo _t('방명록 사용권한');?></label></dt>
											<dd>
												<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteOnGuestbook'] == '1' ? ' checked="checked"' : "";?> /> <label for="allowWriteGuestbook"><?php echo _t('손님이 글쓰기 허용');?></label-->
												<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteDoubleCommentOnGuestbook'] == '1' ? ' checked="checked"' : "";?> /> <label for="allowCommentGuestbook"><?php echo _t('손님이 댓글쓰기 허용.');?></label>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setRSS(); return false;" />
									</div>
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
