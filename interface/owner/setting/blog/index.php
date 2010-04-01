<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';
?>
						<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/generaltag.js"></script>
						<script type="text/javascript">
							//<![CDATA[
								var title = "<?php echo escapeJSInCData($blog['title']);?>";
								var description = "<?php echo escapeJSInCData(trim($blog['description']));?>";
								
								function setBlog() {
									if (document.getElementById('common-form').title.value != title) {
										
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/title");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											title = document.getElementById('common-form').title.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 제목을 변경하지 못했습니다.');?>");
										}
										request.send("title=" + encodeURIComponent(document.getElementById('common-form').title.value));
									}
									if (document.getElementById('common-form').description.value != description) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/description/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											description = document.getElementById('common-form').description.value;
										}
										request.onError = function() {
											alert("<?php echo _t('블로그 설명을 변경하지 못했습니다.');?>");
										}
										request.send("description=" + encodeURIComponent(document.getElementById('common-form').description.value));
									}
								}
								
								function setBlogTag() {
									var oForm = document.forms[2];
									var tagValue = "";
									try {
										tagValue = oTag.getValues().join(",");
									} catch (e) {
										tagValue = oForm.blog-tag.value;
									}
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/tag/");
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('태그를 변경하지 못했습니다.');?>");
									}
									request.send("tags=" + encodeURIComponent(tagValue));
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
									newDefaultDomain = document.getElementById('multi-form').defaultDomain[0].checked ? 0 : 1;
								
									if(primaryDomain != newPrimaryDomain || secondaryDomain != newSecondaryDomain || defaultDomain != newDefaultDomain) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/domain/set/");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
//alert(newDefaultDomain);
//alert(newSecondaryDomain);
//alert(secondaryDomain);
											if(newDefaultDomain == 0) {
												alert("<?php echo _t('변경된 1차 블로그 주소로 이동합니다');?>");
												window.location.href = "http://" + newPrimaryDomain + ".<?php echo $service['domain'];?><?php echo $blogURL;?>/owner/setting/blog";
											}
											else if(newDefaultDomain == 1) {
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
								
								var useSlogan             = "<?php echo $blog['useSloganOnPost'];?>";
								var useCSlogan            = "<?php echo $blog['useSloganOnCategory'];?>";
								var useTSlogan            = "<?php echo $blog['useSloganOnTag'];?>";
								var publishEolinSyncOnRSS = "<?php echo $blog['publishEolinSyncOnRSS'];?>";
								var useFeedViewOnCategory = "<?php echo $blog['useFeedViewOnCategory'];?>";
								var entriesOnRSS          = "<?php echo $blog['entriesOnRSS'];?>";
								var publishWholeOnRSS     = "<?php echo $blog['publishWholeOnRSS'];?>";
								var allowCommentGuestbook = <?php echo $blog['allowWriteDblCommentOnGuestbook'];?>;
								var blogVisibility        = <?php echo $blog['visibility'];?>;

								function setPolicy() {
									if (document.getElementById('rss-form').useSlogan[useSlogan].checked == true
										|| document.getElementById('rss-form').useCSlogan[useCSlogan].checked == true
										|| document.getElementById('rss-form').useTSlogan[useTSlogan].checked == true) {
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/slogan/");
										
										request.onSuccess = function() {
											useSlogan = document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0;
											useCSlogan = document.getElementById('rss-form').useCSlogan[0].checked ? 1 : 0;
											useTSlogan = document.getElementById('rss-form').useTSlogan[0].checked ? 1 : 0;
												PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											}
										request.onError = function() {
											alert("<?php echo _t('글 주소 표기법을 변경할 수 없습니다.');?>");
										}
										request.send("useSloganOnPost="+(document.getElementById('rss-form').useSlogan[0].checked ? 1 : 0)
											+"&useSloganOnCategory="+(document.getElementById('rss-form').useCSlogan[0].checked ? 1 : 0)
											+"&useSloganOnTag="+(document.getElementById('rss-form').useTSlogan[0].checked ? 1 : 0)
										);
									} 
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/feed/");
									request.onSuccess = function() {
										publishEolinSyncOnRSS = document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0;
										useFeedViewOnCategory = document.getElementById('rss-form').useFeedViewOnCategory[0].checked ? 1 : 0;
										entriesOnRSS = document.getElementById('rss-form').entriesOnRSS.value;
										commentsOnRSS = document.getElementById('rss-form').commentsOnRSS.value;
										publishWholeOnRSS = document.getElementById('rss-form').publishWholeOnRSS.value;
										PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
									}
									request.onError = function() {
										PM.showErrorMessage("<?php echo _t('피드 관련 설정을 변경할 수 없습니다.');?>", "center", "bottom");
									}
									
									request.send("publishWholeOnRSS="+document.getElementById('rss-form').publishWholeOnRSS.value
										+"&publishEolinSyncOnRSS="+(document.getElementById('rss-form').publishEolinSyncOnRSS[0].checked ? 1 : 0)
										+"&entriesOnRSS="+document.getElementById('rss-form').entriesOnRSS.value
										+"&commentsOnRSS="+document.getElementById('rss-form').commentsOnRSS.value
										+"&useFeedViewOnCategory="+(document.getElementById('rss-form').useFeedViewOnCategory[0].checked ? 1 : 0));

									isAllowCommentGuestbook = document.getElementById('allowCommentGuestbook').checked ? 1 : 0;
									if ( isAllowCommentGuestbook != allowCommentGuestbook) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/guestbook/?comment="+isAllowCommentGuestbook+"&write=1");
										request.onSuccess = function() {
											allowCommentGuestbook = isAllowCommentGuestbook;
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
										}
										request.onError = function() {
											alert("<?php echo _t('실패했습니다.');?>");
										}
										request.send();
									}

									var visibilityPostParams = {};
<?php
if($service['allowBlogVisibilitySetting']){
?>									
									if(document.getElementById('visibilityPrivate').checked) newVisibility = 0;
									else if(document.getElementById('visibilityMember').checked) newVisibility = 1;
									else newVisibility = 2;

									if (blogVisibility != newVisibility)
										visibilityPostParams['visibility'] = newVisibility;
<?php
}
?>

									if(document.getElementById('useiPhoneUI').checked) newUseiPhoneUI = 1;
									else newUseiPhoneUI = 0;
									if(document.getElementById('acceptComments').checked) acceptComments = 0;
									else acceptComments = 1;
									if(document.getElementById('acceptTrackbacks').checked) acceptTrackbacks = 0;
									else acceptTrackbacks = 1;
									
									var request2 = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/blog/visibility/");
									request2.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
									}
									request2.onError = function() {
										PM.showErrorMessage("<?php echo _t('실패했습니다.');?>", "center", "bottom");
									}
									visibilityPostParams = jQuery.extend({}, visibilityPostParams, {
										'useiPhoneUI': newUseiPhoneUI,
										'acceptComments': acceptComments,
										'acceptTrackbacks': acceptTrackbacks
									});
									request2.send(jQuery.param(visibilityPostParams));

									try {
										var oonly = document.getElementById( 'openidonlycomment' );
										oonly = oonly.checked ? "1" : "0";
										var ologo = document.getElementById( 'openidlogodisplay' );
										ologo = ologo.checked ? "1" : "0";
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/openid/change");
										request.onSuccess = function() {
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
										}
										request.onError = function() {
											PM.showErrorMessage("<?php echo _t('저장하지 못했습니다');?>","center", "bottom");
										}
										request.send("openidonlycomment="+oonly+"&openidlogodisplay="+ologo);
									} catch(e) {
									}
								}
								var language = "<?php echo $blog['language'];?>";
								var skinLanguage = "<?php echo $blog['blogLanguage'];?>";
								var timezone = "<?php echo $blog['timezone'];?>";
								var errorType = "";
								
								function setLocale() {
									errorType = "";

									if (document.getElementById('language-form').timezone.value != timezone) {
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/timezone?timezone=" + encodeURIComponent(document.getElementById('language-form').timezone.value));
										request.onSuccess = function() {
											timezone = document.getElementById('language-form').timezone.value;
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
										}
										request.onError = function() {
											if (errorType == "language")
												errorType = "both";
											else
												errorType = "timezone";
										}
										request.send();
									}

									if (document.getElementById('language-form').adminLanguage.value != language || document.getElementById('language-form').blogLanguage.value != skinLanguage) {
										var needRefresh = false;
										if (document.getElementById('language-form').adminLanguage.value != language) needRefresh = true;
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/blog/language?language=" + encodeURIComponent(document.getElementById('language-form').adminLanguage.value) + "&blogLanguage=" + encodeURIComponent(document.getElementById('language-form').blogLanguage.value));
										request.onSuccess = function() {
											language = document.getElementById('language-form').adminLanguage.value;
											skinLanguage = document.getElementById('language-form').blogLanguage.value;
											PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
											if (needRefresh == true) {
												window.location.href = "<?php echo $blogURL;?>/owner/setting/blog";
											}
										}
										request.onError = function() {
											errorType = "language";
										}
										request.send();
									}
									
									if (errorType == "language")
										alert("<?php echo _t('블로그 언어를 변경할 수 없습니다.');?>");
									else if (errorType == "timezone")
										alert("<?php echo _t('블로그 시간대를 변경할 수 없습니다.');?>");
									else if (errorType == "both")
										alert("<?php echo _t('블로그 언어와 시간대를 변경할 수 없습니다.');?>");
								}
							//]]>
						</script>
						<div id="part-setting-basic" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그의 기본 정보를 설정합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="common-form" class="section" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog/title');?>">
									<fieldset class="container">
										<legend><?php echo _t('블로그 정보');?></legend>
										
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
								<form id="multi-form" class="section" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog');?>">
									<fieldset class="container">
										<legend><?php echo _t('블로그 주소');?></legend>
									
<?php
	if ($service['type'] == 'domain') {
?>
										<dl id="first-blog-address-line" class="line">
											<dt><label for="primaryDomain"><?php echo _t('1차 블로그 주소');?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? '' : 'checked="checked"');?> title="<?php echo _t('기본 도메인');?>" /> http://<input type="text" id="primaryDomain" class="input-text" name="primaryDomain" value="<?php echo htmlspecialchars($blog['name']);?>" /><?php echo $urlRule[1];?>
											</dd>
										</dl>
										<dl id="second-blog-address-line" class="line">
											<dt><label for="secondaryDomain"><?php echo _t('2차 블로그 주소');?></label></dt>
											<dd>
												<input type="radio" class="radio" name="defaultDomain" <?php echo ($blog['defaultDomain'] ? 'checked="checked"' : '');?>title="<?php echo _t('기본 도메인');?>" /> http://<input type="text" id="secondaryDomain" class="input-text" name="secondaryDomain" value="<?php echo htmlspecialchars($blog['secondaryDomain']);?>" /><?php echo $blogURL;?>
											</dd>
										</dl>
<?php
	} else {
?>
										<dl id="blog-address-line" class="line">
											<dt><label for="pathDomain"><?php echo _t('블로그 주소');?></label></dt>
											<dd><?php echo $urlRule[0];?><input type="text" id="pathDomain" class="input-text" name="pathDomain" value="<?php echo htmlspecialchars($blog['name']);?>" /></dd>
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
								<form id="tag-form" class="section" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog/tag');?>">
									<fieldset class="container">
										<legend><?php echo _t('블로그 태그');?></legend>
										
										<dl id="blog-tag-line" class="line">
											<dt><label for="title"><?php echo _t('전문가 태그');?></label></dt>
											<dd id="blog-tag"></dd>
											<dd><?php echo _t('전문가 태그는 이 블로그가 어떠한 주제로 운영되는지를 표시합니다. 전문가태그 정보는 블로그의 코드에 메타 태그 형태로 명시되어 검색 엔진등이 참조할 수 있게 됩니다.');?></dd>
										</dl>
									</fieldset>
									<script type="text/javascript">
										//<![CDATA[
											try {
												var oTag = new Tag(document.getElementById("blog-tag"), "<?php echo $blog['language'];?>", <?php echo isset($service['disableEolinSuggestion']) && $service['disableEolinSuggestion'] ? 'true' : 'false';?>);
												oTag.setInputClassName("input-tag");
<?php
		$tags = array();
		$blogTags = getBlogTags($blogid);
		if(!empty($blogTags)) {
			$blogTags = explode(',',$blogTags);
			foreach ($blogTags as $tag) {
				array_push($tags, $tag);
				echo 'oTag.setValue("' . addslashes($tag) . '");';
			}
		}
?>
											} catch(e) {
												document.getElementById("tag").innerHTML = '<input type="text" class="input-text" name="blog-tag" value="<?php echo addslashes(str_replace('"', '&quot;', implode(', ', $tags)));?>" /><br /><?php echo _t('태그 입력 스크립트를 사용할 수 없습니다. 콤마(,)로 구분된 태그를 직접 입력해 주십시오.(예: 텍스트큐브, BLOG, 테스트)');?>';
											}
										//]]>
									</script> 									
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setBlogTag(); return false;" />
									</div>
								</form>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-setting-profile" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 이미지 및 아이콘을 설정합니다');?></span></h2>
							
							<form id="icons-form" class="data-inbox" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog/icons');?>" enctype="multipart/form-data">
								<div id="icons-upload-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('블로그 로고 및 파비콘');?></legend>
										
										<dl id="blog-picture-line" class="line">
											<dt><span class="label"><?php echo _t('로고 그림');?></span></dt>
											<dd>
<?php
if (!empty($blog['logo']) && file_exists(ROOT."/attach/$blogid/{$blog['logo']}")) {
	$logoInfo = @getimagesize(ROOT."/attach/$blogid/{$blog['logo']}");
	if ($logoInfo[0] > 150) {
?>
												<a href="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/<?php echo $blog['logo'];?>" onclick="window.open(this.href); return false;"><img src="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/<?php echo $blog['logo'];?>" width="150" border="1" alt="<?php echo _t('사용자 로고');?>" /></a>
<?php
	} else {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/<?php echo $blog['logo'];?>" border="1" alt="<?php echo _t('사용자 로고');?>" />
<?php
	}
}
?>

												<input type="file" class="input-file" name="logo" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteLogo" name="deleteLogo" value="yes"<?php echo empty($blog['logo']) ? ' disabled="disabled"' : '';?> /><label for="deleteLogo"><?php echo _t('로고를 초기화합니다.');?></label></div>
											</dd>
										</dl>
										<dl id="favicon-line" class="line">
											<dt><span class="label"><?php echo _t('파비콘');?></span></dt>
											<dd>
<?php
if (file_exists(ROOT."/attach/$blogid/favicon.ico") && !stristr($_SERVER['HTTP_USER_AGENT'], ' MSIE')) {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/favicon.ico" border="1" alt="<?php echo _t('파비콘');?>" />
<?php
} else if (file_exists(ROOT."/attach/$blogid/favicon.ico") && stristr($_SERVER['HTTP_USER_AGENT'], ' MSIE')) {
?>
												<a id="favicon-preview" href="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/favicon.ico"><?php echo _t('미리 보기');?></a>
<?php
}
?>
												<input type="file" class="input-file" name="favicon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteFavicon" name="deleteFavicon" value="yes"<?php echo file_exists(ROOT."/attach/$blogid/favicon.ico") ? '' : ' disabled="disabled"';?> /><label for="deleteFavicon"><?php echo _t('파비콘을 초기화합니다.');?></label></div>
												<p><?php echo _t('파비콘은 웹 브라우저의 즐겨찾기나 주소 입력줄에 보이는 그림입니다.');?></p>
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
if (file_exists(ROOT."/attach/$blogid/index.gif")) {
	$blogIconInfo = getimagesize(ROOT."/attach/$blogid/index.gif");
	if ($blogIconInfo != false) {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/index.gif"<?php echo ($blogIconInfo[0] > 48) ? ' width="48"' : '';?> border="1" alt="<?php echo _t('블로그 아이콘');?>" />
<?php
	}
}
?>
												<input type="file" class="input-file" name="blogIcon" />
												<div class="init-box"><input type="checkbox" class="checkbox" id="deleteBlogIcon" name="deleteBlogIcon" value="yes"<?php echo file_exists(ROOT."/attach/$blogid/index.gif") ? '' : ' disabled="disabled"';?> /><label for="deleteBlogIcon"><?php echo _t('블로그 아이콘을 초기화합니다.');?></label></div>
												<div class="init-box"><input type="checkbox" class="checkbox" id="useBlogIconAsIphoneShortcut" name="useBlogIconAsIphoneShortcut" <?php echo (Setting::getBlogSettingGlobal('useBlogIconAsIphoneShortcut',true) ? ' checked="checked"' : '');?> /><label for="useBlogIconAsIphoneShortcut"><?php echo _t('iPhone / iPod Touch에서 블로그 아이콘을 바로가기 아이콘으로 사용합니다.');?></label></div>
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
						<div id="part-setting-rss" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 공개 정책을 설정합니다');?></span></h2>
							
							<form id="rss-form" class="data-inbox" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog');?>">
								<div id="rss-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('피드 설정');?></legend>
										
										<dl id="open-type-line" class="line">
											<dt><span class="label"><?php echo _t('피드 공개 정도');?></span></dt>
											<dd>
												<input type="radio" id="publishEolinSyncOnRSS1" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['publishEolinSyncOnRSS'] ? ' checked="checked"' : '');?> /><label for="publishEolinSyncOnRSS1"><span class="text"><?php echo _t('공개된 모든 글을 <acronym title="Rich Site Summary">RSS</acronym> 및 ATOM 피드로 내보냅니다.');?></span></label><br />
												<input type="radio" id="publishEolinSyncOnRSS0" class="radio" name="publishEolinSyncOnRSS"<?php echo ($blog['publishEolinSyncOnRSS'] ?   '' : ' checked="checked"');?> /><label for="publishEolinSyncOnRSS0"><span class="text"><?php echo _t('이올린에 발행된 글만을 RSS 및 ATOM 피드로 내보냅니다.');?></span></label>
											</dd>
										</dl>
										
										<dl id="category-feed-line" class="line">
											<dt><span class="label"><?php echo _t('카테고리 피드 사용');?></span></dt>
											<dd>
												<input type="radio" id="useFeedViewOnCategory1" class="radio" name="useFeedViewOnCategory"<?php echo ($blog['useFeedViewOnCategory'] ? ' checked="checked"' : '');?> /><label for="useFeedViewOnCategory1"><span class="text"><?php echo _t('카테고리 목록에 ATOM 피드를 출력합니다.');?></span></label><br />
												<input type="radio" id="useFeedViewOnCategory0" class="radio" name="useFeedViewOnCategory"<?php echo ($blog['useFeedViewOnCategory'] ?   '' : ' checked="checked"');?> /><label for="useFeedViewOnCategory0"><span class="text"><?php echo _t('카테고리 목록에서 ATOM 피드를 사용하지 않습니다.');?></span></label>
											</dd>
										</dl>

										<dl id="post-count-line" class="line">
											<dt><span class="label"><?php echo _t('글 개수');?></span></dt>
											<dd>
												<?php echo getArrayValue(explode('%1', _t('최근 %1 개의 블로그 글을 피드로 내보냅니다.')), 0);?>
												<select id="entriesOnRSS" name="entriesOnRSS">
<?php
for ($i = 5; $i <= 50; $i += 5) {
?>
													<option value="<?php echo $i;?>"<?php echo ($i == $blog['entriesOnRSS'] ? ' selected="selected"' : '');?>><?php echo $i;?></option>
<?php
}
?>
												</select><?php echo getArrayValue(explode('%1', _t('최근 %1 개의 글을 피드로 내보냅니다.')), 1);?>
											</dd>
											<dd>
												<?php echo getArrayValue(explode('%1', _t('최근 %1 개의 댓글, 걸린글(트랙백) 및 답변을 피드로 내보냅니다.')), 0);?>
												<select id="commentsOnRSS" name="commentsOnRSS">
<?php
for ($i = 5; $i <= 30; $i += 5) {
?>
													<option value="<?php echo $i;?>"<?php echo ($i == $blog['commentsOnRSS'] ? ' selected="selected"' : '');?>><?php echo $i;?></option>
<?php
}
?>
												</select><?php echo getArrayValue(explode('%1', _t('최근 %1 개의 댓글, 걸린글(트랙백) 및 답변을 피드로 내보냅니다.')), 1);?>
											</dd>

										</dl>
										<dl id="open-range-line" class="line">
											<dt><span class="label"><?php echo _t('공개 범위');?></span></dt>
											<dd><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 0);?>
												<select id="publishWholeOnRSS" name="publishWholeOnRSS">
													<option value="1"<?php echo ($blog['publishWholeOnRSS'] ? ' selected="selected"' : '');?>><?php echo _t('전체공개');?></option>
													<option value="0"<?php echo ($blog['publishWholeOnRSS'] ? '' : ' selected="selected"');?>><?php echo _t('부분공개');?></option>
												</select><?php echo getArrayValue(explode('%1', _t('RSS 파일의 글 본문은 %1를 원칙으로 합니다.')), 1);?>
											</dd>

										</dl>
									</fieldset>
								</div>
<?php echo setDetailPanel('panelEtcSetting','button');?>								
								<div id="panelEtcSetting" class="section folding">
									<fieldset class="container">
										<legend><?php echo _t('기타');?></legend>
										
										<dl id="post-address-line" class="line">
											<dt><span class="label"><?php echo _t('글 주소');?></span></dt>
											<dd>
												<input type="radio" id="useSlogan1" class="radio" name="useSlogan"<?php echo ($blog['useSloganOnPost'] ? ' checked="checked"' : '');?> /><label for="useSlogan1"><span class="text"><?php echo _t('문자를 사용합니다.');?> <samp><?php echo _f('(예: %1/entry/텍스트큐브로-오신-것을-환영합니다)',link_cut(getBlogURL()));?></samp></span></label><br />
												<input type="radio" id="useSlogan0" class="radio" name="useSlogan"<?php echo ($blog['useSloganOnPost'] ? '' : ' checked="checked"');?> /><label for="useSlogan0"><span class="text"><?php echo _t('숫자를 사용합니다.');?> <samp><?php echo _f('(예: %1/123)', link_cut(getBlogURL()));?></samp></span></label>
											</dd>
										</dl>
										<dl id="category-address-line" class="line">
											<dt><span class="label"><?php echo _t('카테고리 주소');?></span></dt>
											<dd>
												<input type="radio" id="useCSlogan1" class="radio" name="useCSlogan"<?php echo ($blog['useSloganOnCategory'] ? ' checked="checked"' : '');?> /><label for="useCSlogan1"><span class="text"><?php echo _t('문자를 사용합니다.');?> <samp><?php echo _f('(예: %1/category/텍스트큐브)',link_cut(getBlogURL()));?></samp></span></label><br />
												<input type="radio" id="useCSlogan0" class="radio" name="useCSlogan"<?php echo ($blog['useSloganOnCategory'] ? '' : ' checked="checked"');?> /><label for="useCSlogan0"><span class="text"><?php echo _t('숫자를 사용합니다.');?> <samp><?php echo _f('(예: %1/category/1)', link_cut(getBlogURL()));?></samp></span></label>
											</dd>
										</dl>
										<dl id="tag-address-line" class="line">
											<dt><span class="label"><?php echo _t('태그 주소');?></span></dt>
											<dd>
												<input type="radio" id="useTSlogan1" class="radio" name="useTSlogan"<?php echo ($blog['useSloganOnTag'] ? ' checked="checked"' : '');?> /><label for="useTSlogan1"><span class="text"><?php echo _t('문자를 사용합니다.');?> <samp><?php echo _f('(예: %1/tag/텍스트큐브)',link_cut(getBlogURL()));?></samp></span></label><br />
												<input type="radio" id="useTSlogan0" class="radio" name="useTSlogan"<?php echo ($blog['useSloganOnTag'] ? '' : ' checked="checked"');?> /><label for="useTSlogan0"><span class="text"><?php echo _t('숫자를 사용합니다.');?> <samp><?php echo _f('(예: %1/tag/101)', link_cut(getBlogURL()));?></samp></span></label>
											</dd>
										</dl>
<?php
if($service['allowBlogVisibilitySetting']){
?>
										<dl id="blog-open-type-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 공개 정도');?></span></dt>
											<dd>
												<input type="radio" id="visibilityPrivate" class="radio" name="visibility"<?php echo ($blog['visibility']==0 ? ' checked="checked"' : '');?> /><label for="visibilityPrivate"><span class="text"><?php echo _t('이 블로그의 구성원만 접근할 수 있도록 합니다.');?></span></label><br />
												<input type="radio" id="visibilityMember" class="radio" name="visibility"<?php echo ($blog['visibility']==1 ? ' checked="checked"' : '');?> /><label for="visibilityMember"><span class="text"><?php echo _t('블로그에 아이디가 있는 경우만 접근할 수 있도록 합니다.');?></span></label><br />
												<input type="radio" id="visibilityPublic" class="radio" name="visibility"<?php echo ($blog['visibility']==2 ? ' checked="checked"' : '');?> /><label for="visibilityPublic"><span class="text"><?php echo _t('누구나 접근할 수 있도록 합니다.');?></span></label>
											</dd>
											<dd>
												<p><label for="visibility"><?php echo _t('블로그 공개 정도를 설정합니다.').' '._t('접근 권한이 제한된 경우에는 로그인 이후에 블로그를 열람할 수 있습니다.').'<br />'._t('접근 권한이 제한된 경우 RSS로 내용을 열람할 수 없으며, RSS로 새 글이 올라오는 시간만 전달됩니다.');?></label></p>
											</dd>
										</dl>
<?php
}
?>										
										<dl id="guestbook-authority-line" class="line">
											<dt><label for="allowCommentGuestbook"><?php echo _t('방명록에 대한 답글 권한');?></label></dt>
											<dd>
												<!--input type="checkbox" id="allowWriteGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteOnGuestbook'] == '1' ? ' checked="checked"' : "";?> /><label for="allowWriteGuestbook"><?php echo _t('손님이 글쓰기 허용');?></label-->
												<input type="checkbox" id="allowCommentGuestbook" class="checkbox" value=""<?php echo $blog['allowWriteDblCommentOnGuestbook'] == '1' ? ' checked="checked"' : "";?> /><label for="allowCommentGuestbook"><?php echo _t('방명록에 남긴 글에 대하여 손님이 답글을 쓰는 것을 허용합니다.');?></label>
											</dd>
										</dl>
<?php
$openidonlycomment = Setting::getBlogSettingGlobal( "AddCommentMode", "" );
if( $openidonlycomment == 'openid' ) {
	$openidonlycomment = "checked='checked'";
} else {
	$openidonlycomment = "";
}

$openidlogodisplay = Setting::getBlogSettingGlobal( "OpenIDLogoDisplay", 0 );
if( $openidlogodisplay ) {
	$openidlogodisplay = "checked='checked'";
} else {
	$openidlogodisplay = "";
}

$acceptComments = Setting::getBlogSettingGlobal( "acceptComments", 1 );
if( $acceptComments ) {
	$acceptComments = "";
} else {
	$acceptComments = "checked='checked'";
}
$acceptTrackbacks = Setting::getBlogSettingGlobal( "acceptTrackbacks", 1 );
if( $acceptTrackbacks ) {
	$acceptTrackbacks = "";
} else {
	$acceptTrackbacks = "checked='checked'";
}

?>
										<dl id="comment-authority-line" class="line">
											<dt><label for="allowCommentGuestbook"><?php echo _t('방명록 및 댓글 쓰기 권한');?></label></dt>										
											<dd>
												<input id="openidonlycomment" type="checkbox" name="openidonlycomment" <?php echo $openidonlycomment?> />
												<label for="openidonlycomment"><?php echo _t('오픈아이디로 로그인을 해야만 댓글 및 방명록을 쓸 수 있습니다.').' '._t('필명을 사칭하는 경우를 방지할 수 있습니다.'); ?></label>
												<br />
												<input id="openidlogodisplay" type="checkbox" name="openidlogodisplay" <?php echo $openidlogodisplay?> />
												<label for="openidlogodisplay"><?php echo _t('오픈아이디로 로그인하여 쓴 댓글/방명록에 오픈아이디 아이콘을 표시합니다.') ?></label>
											</dd>
										</dl>
																			
										<dl id="accept-response-line" class="line">
											<dt><label for="acceptResponses"><?php echo _t('댓글 및 글걸기 차단');?></label></dt>										
											<dd>
												<input id="acceptComments" type="checkbox" name="acceptComment" <?php echo $acceptComments;?> />
												<label for="acceptComments"><?php echo _t('블로그의 모든 글에 댓글및 방명록을 달 수 없도록 합니다.'); ?></label>
												<br />
												<input id="acceptTrackbacks" type="checkbox" name="acceptTrackback" <?php echo $acceptTrackbacks;?> />
												<label for="acceptTrackbacks"><?php echo _t('블로그의 모든 글에 트랙백 및 핑백을 보낼 수 없도록 합니다.') ?></label>
											</dd>
										</dl>
																				
										<dl id="blog-iphone-ui-line" class="line">
											<dt><span class="label"><?php echo _t('모바일 인터페이스');?></span></dt>
											<dd>
												<input type="checkbox" id="useiPhoneUI" class="checkbox" name="useiPhoneUI"<?php echo (Setting::getBlogSettingGlobal('useiPhoneUI',true) ? ' checked="checked"' : '');?> /><label for="useiPhoneUI"><?php echo _t('iPhone / iPod Touch로 블로그에 접속할 경우 자동으로 iPhone용 인터페이스로 이동합니다.');?></label>
											</dd>
										</dl>
									</fieldset>
								</div>
								<div class="button-box">
									<input type="submit" class="save-button input-button wide-button" value="<?php echo _t('저장하기');?>" onclick="setPolicy(); return false;" />
								</div>
							</form>
						</div>

						<hr class="hidden" />
						
						<div id="part-setting-language" class="part">
							<h2 class="caption"><span class="main-text"><?php echo setDetailPanel('language_setting','link',_t('언어, 시간대를 설정합니다'));?></span></h2>
							<form id="language-form" class="data-inbox" method="post" action="<?php echo parseURL($blogURL.'/owner/setting/blog/language');?>">
								<div id="language_setting" class="section folding">
									<fieldset class="container">
										<legend><?php echo _t('언어 및 시간대');?></legend>
										
										<dl id="admin-language-line" class="line">
											<dt><span class="label"><?php echo _t('관리자 화면 언어');?></span></dt>
											<dd>
												<select id="admin-language" name="adminLanguage">
<?php
$locale = Locales::getInstance();
$locale->setDirectory(ROOT.'/resources/locale/owner');
$supportedLanguages = $locale->getSupportedLocales();
foreach ($supportedLanguages as $locale => $language) {
?>
													<option value="<?php echo $locale;?>"<?php echo ($locale == $blog['language'] ? ' selected="selected"' : '');?>><?php echo $language;?></option>
<?php
}
?>
												</select>
												<p><?php echo _t('관리 패널 화면에서 사용할 언어를 설정합니다.');?></p>
											</dd>
										</dl>
										<dl id="blog-language-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 언어');?></span></dt>
											<dd>
												<select id="blog-language" name="blogLanguage">
<?php
$locale = Locales::getInstance();
$locale->setDirectory(ROOT.'/resources/locale/blog');
$supportedLanguages = $locale->getSupportedLocales();
foreach ($supportedLanguages as $locale => $language) {
?>
													<option value="<?php echo $locale;?>"<?php echo ($locale == $blog['blogLanguage'] ? ' selected="selected"' : '');?>><?php echo $language;?></option>
<?php
}
?>
												</select>
												<p><?php echo _t('블로그에 표시되는 메세지의 언어를 설정합니다. 달력이나 댓글 입력창에 적용됩니다.');?></p>
											</dd>
										</dl>
										<dl id="timezone-line" class="line">
											<dt><span class="label"><?php echo _t('시간대');?></span></dt>
											<dd>
												<select id="timezone" name="timezone">
<?php
$tz = new Timezone;
foreach ($tz->getList() as $timezone) {
?>
													<option value="<?php echo $timezone;?>"<?php echo ($timezone == $blog['timezone'] ? ' selected="selected"' : '');?>><?php echo _t($timezone);?></option>
<?php
}
?>
												</select>
												<p><?php echo _t('블로그에서 사용할 시간대를 설정합니다.');?></p>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button wide-button" value="<?php echo _t('저장하기');?>" onclick="setLocale(); return false;" />
									</div>
								</div>
							</form>
						</div>
<?php 
if (isset($_GET['message'])) {
	$msg = escapeJSInCData($_GET['message']);
?>
	<script type="text/javascript">
		//<![CDATA[
			window.onload = function() { PM.showMessage("<?php echo $msg;?>", "center", "bottom"); }
		//]]>
	</script>
<?php
}

require ROOT . '/interface/common/owner/footer.php';
?>
