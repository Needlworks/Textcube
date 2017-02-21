<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

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
								
								function addLineSearch() {
									window.external.AddSearchProvider('<?php echo $defaultURL.'/owner/setting/entry/addline';?>');
									return false;
								}

								function refreshLineSearch(mode) {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/setting/entry/refreshLine");
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('갱신되었습니다');?>", "center", "bottom");
									}
									request.onError = function() {
										alert("<?php echo _t('갱신할 수 없었습니다');?>");
									}
									request.send('mode='+mode);
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
											<dd>
												<p><label for="defaultFormatter"><?php echo _t('포매터는 글이 작성되는 형식과 이후 보여줄 때의 형식을 결정합니다.').'<br />'._t('기본 포매터를 지정한 경우 새 글을 작성할 때 지정한 포매터를 기본값으로 사용합니다.').' '._t('기본 포매터가 지정되더라도 편집기 화면에서 언제든지 다른 포매터를 글마다 지정할 수 있습니다');?></label></p>
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
											<dd>
												<p><label for="defaultEditor"><?php echo _t('글을 작성할 때 사용할 편집기를 지정합니다.');?></label></p>
											</dd>											
										</dl>
<?php echo setDetailPanel('panelBlogAPISetting','button');?>
										<div id="panelBlogAPISetting" class="folding">
										<script type="text/javascript">//<![CDATA[
											setFormatter(document.getElementById('defaultFormatter').value, document.getElementById('defaultEditor'), false);
										//]]></script>
										<dl id="blogapi-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 API 사용 여부');?></span></dt>
											<dd>
												<input type="checkbox" class="checkbox" id="useBlogAPI" name="useBlogAPI" value="yes" <?php echo (Setting::getBlogSettingGlobal("useBlogAPI", 0) == "1") ? ' checked="checked"' : '';?> /><label for="useBlogAPI"><?php echo _t('MetaWeblog API나 Blogger API를 이용하여 글을 작성할 수 있도록 합니다.');?></label>
											</dd>
											<dd><?php echo _t('이 기능을 사용할 경우 BlogAPI를 지원하는 다양한 외부 프로그램을 사용하여 블로그에 글을 작성할 수 있습니다.').'<br />'._t('외부 프로그램에 텍스트큐브를 등록할 경우, BlogAPI 종류로는 MovableType 혹은 MetaWeblog API를 선택하시면 됩니다.'); ?></dd>
										</dl>
										<dl id="blogapi-password-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 API 용 비밀번호');?></span></dt>
											<dd>
												<input type="text" style="width:14em" class="input-text" id="blogApiPassword" name="blogApiPassword" value="<?php echo Setting::getBlogSettingGlobal("blogApiPassword", "");?>" />
												<input type="button" class="input-button" value="<?php echo _t('임의로 생성')?>" onclick="chooseBlogPassword()" />
												<input type="button" class="input-button" value="<?php echo _t('관리자 비밀번호를 그대로 사용')?>" onclick="clearBlogPassword()" />
											</dd>
											<dd>
												<p><label for="blogApiPassword"><?php echo _t('BlogAPI에 사용할 비밀번호입니다.').'<br />'._t('외부 프로그램 또는 BlogAPI를 지원하는 서비스들을 사용할 때, 보안상의 이유로 블로그 관리자 비밀번호를 알려주고 싶지 않은 경우 사용하시기 바랍니다.').' '._t('관리자 로그인 비밀번호와 동일하게 사용하실 경우 비워두시기 바랍니다.');?></label></p>
											</dd>
										</dl>
										<dl id="blogapi-helper-line" class="line">
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
													<optgroup class="category" label="">
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
											<dd>
												<p><label for="apientry"><?php echo _t('외부 편집기가 사용할 수 있는 주소를 만들어 주는 <strong>도우미</strong>입니다.').'<br />'.
											_t('BlogAPI를 사용하는 편집기들은 이 블로그로 글을 보내기 위하여 API 주소를 사용합니다.').' '.
											_t('편집기에 미리 정한 분류로 글을 보내는 기능이 없거나, 글들을 일괄적으로 하나의 분류로 작성하고자 할 때 이 도우미를 사용해서 주소를 만든 후 복사해서 사용하시기 바랍니다.');?></label></p>
											</dd>
										</dl>
										</div>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button wide-button" value="<?php echo _t('저장하기');?>" onclick="setEditorConfig(); return false;" />
									</div>
								</div>
							</form>
						</div>

						<hr class="hidden" />
						
						<div id="part-setting-line" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('라인 작성 환경을 설정합니다');?></span></h2>
							
							<form id="line-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/setting/entry/line">
								<div id="line-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('라인 작성 환경을 설정합니다');?></legend>
										


<?php echo setDetailPanel('panelLineSetting','button');?>
										<div id="panelLineSetting" class="folding">
										
										<dl id="line-password-line" class="line">
											<dt><span class="label"><?php echo _t('Line 글쓰기를 검색 공급자에 추가하기');?></span></dt>
											<dd>
												<input type="button" class="input-button" value="<?php echo _t('검색 표시줄에 Line 글쓰기 추가하기')?> (<?php echo _t('공개');?>)" onclick="addLineSearch('public');" />
												<input type="button" class="input-button" value="<?php echo _t('검색 표시줄에 Line 글쓰기 추가하기')?> (<?php echo _t('비공개');?>)" onclick="addLineSearch('private');" />
												<input type="button" class="input-button" value="<?php echo _t('새로 고침')?>" onclick="refreshLineSearch();" />
											</dd>
											<dd>
												<p><label for="LinePassword"><?php echo _t('Line은 짧은 글로 생각이나 느낌을 정리하기 위한 기능입니다.').'<br />'._t('옆의 링크를 누르면 지금 사용하시는 브라우저의 검색 입력줄에 이 블로그의 Line이 추가됩니다.').' '._t('추가한 후에는 브라우저의 검색 입력줄에서 추가된 Line을 선택하고 글을 입력하여 바로 Line을 쓸 수 있습니다.').' '._t('공공 장소의 컴퓨터에서는 추가하지 마시기 바랍니다. 만약 실수로 추가하신 경우, 옆의 새로 고침 버튼을 눌러 Line의 비밀 번호를 변경하면, 기존에 등록한 검색 표시줄에서 글을 쓸 수 없게 됩니다.');?></label></p>
											</dd>
										</dl>
										</div>
									</fieldset>
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
												<input type="checkbox" class="checkbox" id="useResamplingAsDefault" name="useResamplingAsDefault" value="yes"<?php echo (Setting::getBlogSettingGlobal("resamplingDefault") == "yes") ? ' checked="checked"' : NULL;?> onchange="document.getElementById('useResamplingResponsive').disabled = !this.checked;" /><label for="useResamplingAsDefault"><?php echo _t('이미지 리샘플링 기능을 기본으로 사용합니다.');?> </label>
											</dd>
											<dd>
												<p><?php echo _t('이미지 리샘플링을 통하여 올린 이미지의 크기를 줄여 저장한 후 출력하여 블로그의 트래픽을 줄여줍니다.');?> <strong><?php echo _t('이 기능을 사용하면 올린 이미지의 크기를 줄여 저장하는 과정에서 서버에 무리를 줄 수 있으니 주의하시기 바랍니다.');?></strong></p>
											</dd>
										</dl>
										<dl id="resampling-responsive-line" class="line">
											<dt><span class="label"><?php echo _t('고급 설정');?></span></dt>
											<dd>
												<input type="checkbox" class="checkbox" id="useResamplingResponsive" name="useResamplingResponsive" value="yes"<?php echo (Setting::getBlogSettingGlobal("resamplingResponsive") == "yes") ? ' checked="checked"' : NULL;?> /><label for="useResamplingResponsive"><?php echo _t('srcset을 이용한 반응형 이미지 기능을 사용합니다.');?> </label>
											</dd>
											<dd>
												<p><?php echo _t('이미지의 작은 사이즈 사본을 추가적으로 생성하고, 접속한 브라우저의 화면 크기에 따라 가장 알맞은 사이즈의 이미지만 서빙하여 트래픽을 절약할 수 있습니다. IE를 제외한 모든 모던한 브라우저에서 해당 기능을 지원하며, srcset을 지원하지 않는 브라우저에서는 기본 크기의 이미지 주소를 참조합니다.');?><strong><?php echo _t('이 기능을 사용하려면, 위의 이미지 리샘플링 기능이 켜져있어야 합니다.');?></strong>
												</p>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button wide-button" value="<?php echo _t('저장하기');?>" onclick="setResample(); return false;" />
									</div>
								</form>
							</div>
						</div>
<?php
}
require ROOT . '/interface/common/owner/footer.php';
?>
