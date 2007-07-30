<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
printFormatterSelectScript();
?>
						<script type="text/javascript">
							//<![CDATA[
								var title = "<?php echo escapeJSInCData($blog['title']);?>";
								var description = "<?php echo escapeJSInCData(trim($blog['description']));?>";
								
								function setResample() {
									document.getElementById("resample-form").submit();
								}
		

								
								function setEditorConfig() {
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/entry/editor/?defaultEditor=" + document.getElementById('editor-form').defaultEditor.value + "&defaultFormatter=" + document.getElementById('editor-form').defaultFormatter.value + "&useBlogAPI=" + document.getElementById('editor-form').useBlogAPI.checked + "&blogApiPassword=" + document.getElementById('editor-form').blogApiPassword.value );
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('에디터 설정을 변경할 수 없습니다');?>");
									}
									request.send();
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

								function clearBlogPassword() {
									document.getElementById('blogApiPassword').value = "";
								}
								function chooseBlogPassword() {
									var blogApiPassword = document.getElementById('blogApiPassword');
									var value = "";
									var asciibase = "0123456789abcdef";
									for( i=0;i<20;i++) {
										value += "" + asciibase.charAt(Math.round((Math.random()*15)));
									}
									blogApiPassword.value = value;
								}
							//]]>
						</script>
			
						<hr class="hidden" />
						
						<div id="part-setting-editor" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('글 작성 환경을 설정합니다');?></span></h2>
							
							<form id="editor-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/setting/entry/editor">
								<div id="editor-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('글 작성 환경을 설정합니다');?></legend>
										
										<dl id="formatter-line" class="line">
											<dt><span class="label"><?php echo _t('기본 포매터');?></span></dt>
											<dd>
												<select id="defaultFormatter" name="defaultFormatter" onchange="return setFormatter(this.value, document.getElementById('defaultEditor'), true);">
<?php
$defaultFormatter = getDefaultFormatter();
foreach (getAllFormatters() as $key => $value) {
?>
													<option value="<?php echo htmlspecialchars($key);?>"<?php echo $key==$defaultFormatter?' selected="selected"':'';?>><?php echo htmlspecialchars($value['name']);?></option>
<?php
}
?>
												</select>
											</dd>
										</dl>
										<dl id="editor-line" class="line">
											<dt><span class="label"><?php echo _t('기본 편집기');?></span></dt>
											<dd>
												<select id="defaultEditor" name="defaultEditor" onfocus="return saveEditor(this);" onchange="return setEditor(this);">
<?php
$defaultEditor = getDefaultEditor();
foreach (getAllEditors() as $key => $value) {
?>
													<option value="<?php echo htmlspecialchars($key);?>"<?php echo $key==$defaultEditor?' selected="selected"':'';?>><?php echo htmlspecialchars($value['name']);?></option>
<?php
}
?>
												</select>
											</dd>
										</dl>
										<script type="text/javascript">//<![CDATA[
											setFormatter(document.getElementById('defaultFormatter').value, document.getElementById('defaultEditor'), false);
										//]]></script>
										<dl id="blogapi-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 API 사용 여부');?></span></dt>
											<dd>
												<input type="checkbox" class="input-checkbox" id="useBlogAPI" name="useBlogAPI" value="yes" <?php echo (getBlogSetting("useBlogAPI", 0) == "1") ? ' checked="checked"' : '';?> /><label for="useBlogAPI"><?php echo _t('metaWeblogAPI나 Blogger API를 이용하여 글을 작성할 수 있도록 합니다.');?></label>
											</dd>
											<dd><?php echo _t("블로그 API는 MovableType 혹은 MetaWeblog API를 선택하시면 됩니다."); ?></dd>
										</dl>
										<dl id="blogapi-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 API 용 비밀번호');?></span></dt>
											<dd>
												<p><label for="blogApiPassword"><?php echo _t('BlogAPI에 사용할 비밀번호입니다. 관리자 로그인 비밀번호와 동일하게 사용하실 경우 비워두세요.');?></label></p>
											</dd>
											<dd>
												<input type="text" style="width:14em" class="input-text" id="blogApiPassword" name="blogApiPassword" value="<?php echo getBlogSetting("blogApiPassword", "");?>" />
												<input type="button" class="input-button" value="<?php echo _t('임의로 생성')?>" onclick="chooseBlogPassword()" >
												<input type="button" class="input-button" value="<?php echo _t('관리자 비밀번호를 그대로 사용')?>" onclick="clearBlogPassword()" >
											</dd>
										</dl>
										<dl id="blogapi-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 API 주소 도우미');?></span></dt>
											<dd>
											<div class="section">
												<script type="text/javascript">//<![CDATA[
												function showapi() {
													var selectedIndex = document.getElementById('category').selectedIndex;
													var cid = document.getElementById('category').options[selectedIndex].value;
													document.getElementById('apientry').innerHTML = "<?php echo $defaultURL?>" + "/api";
													if( cid != "" ) {
														document.getElementById('apientry').innerHTML += "?category="+cid;
													}
												}
												setTimeout( "showapi()", 100 );
												//]]></script>
												<select id="category" name="category" onchange="showapi()">
													<optgroup class="category">
													<option value=""><?php echo _t('API 주소');?></option>
													</optgroup>
													<optgroup class="category" label="<?php echo _t('분류');?>">
			<?php
			foreach (getCategories(getBlogId()) as $category) {
				if ($category['id'] != 0) {
			?>
														<option value="<?php echo $category['id'];?>"><?php echo ($category['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($category['name']);?></option>
			<?php
				}
				foreach ($category['children'] as $child) {
					if ($category['id'] != 0) {
			?>
														<option value="<?php echo $child['id'];?>">&nbsp;― <?php echo ($category['visibility'] > 1 && $child['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($child['name']);?></option>
			<?php
					}
				}
			}
			?>
													</optgroup>
												</select>
												<span style="width:400px" id="apientry" ></span>
											</div>
											</dd>
											<dd><?php echo _t("외부 편집기가 사용할 수 있는 주소를 만들어 주는 <b>도우미</b>입니다. 보통 많은 편집기들은 'API 주소'에 해당하는 것을 사용합니다. 그러나,분류설정이 기능이 없거나 혹은 강제로 하나의 분류로 보내고자 할 때 사용할 수 있는 주소도 있습니다. <br /><b>원하시는 주소를 복사해서 사용하시면 됩니다</b>"); ?></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setEditorConfig(); return false;" />
									</div>
								</div>
							</form>
						</div>

						<hr class="hidden" />
						
<?php
if (extension_loaded('gd')) {
?>
						<div id="part-setting-resample" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('이미지 리샘플링을 설정합니다');?></span></h2>
							
							<div class="data-inbox">
								<form id="resample-form" class="section" method="post" action="<?php echo $blogURL;?>/owner/setting/entry/resample" enctype="multipart/form-data">
									<fieldset class="container">
										<legend><?php echo _t('이미지 리샘플링 정보');?></legend>
										
										<dl id="resampling-default-line" class="line">
											<dt><span class="label"><?php echo _t('기본 설정');?></span></dt>
											<dd>
												<input type="checkbox" class="input-checkbox" id="useResamplingAsDefault" name="useResamplingAsDefault" value="yes"<?php echo (getBlogSetting("resamplingDefault") == "yes") ? ' checked="checked"' : NULL;?> /><label for="useResamplingAsDefault"><?php echo _t('이미지 리샘플링 기능을 기본으로 사용합니다.');?> </label><br /><?php echo _t('이미지 리샘플링을 통하여 올린 이미지의 크기를 줄여 저장한 후 출력하여 블로그의 트래픽을 줄여줍니다.');?> <em><?php echo _t('이 기능을 사용하면 올린 이미지의 크기를 줄여 저장하는 과정에서 서버에 무리를 줄 수 있으니 주의하시기 바랍니다.');?></em>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setResample(); return false;" />
									</div>
								</form>
							</div>
						</div>
<?php
}
require ROOT . '/lib/piece/owner/footer.php';
?>
