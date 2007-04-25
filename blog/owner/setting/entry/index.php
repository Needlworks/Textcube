<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu55.php';
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
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/entry/editor/?defaultEditor=" + document.getElementById('editor-form').defaultEditor.value + "&defaultFormatter=" + document.getElementById('editor-form').defaultFormatter.value + "&useBlogAPI=" + document.getElementById('editor-form').useBlogAPI.checked);
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
								
								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
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
												<input type="checkbox" class="checkbox" id="useBlogAPI" name="useBlogAPI" value="yes" <?php echo (getUserSetting("useBlogAPI", 0) == "1") ? ' checked="checked"' : '';?> /><label for="useBlogAPI"><?php echo _t('metaWeblogAPI나 Blogger API를 이용하여 글을 작성할 수 있도록 합니다.');?></label>
												<p><?php echo _t('API 사용시 주소는 블로그 주소/api 입니다.');?></p>
											</dd>
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
												<input type="checkbox" class="checkbox" id="useResamplingAsDefault" name="useResamplingAsDefault" value="yes" onclick="if (this.checked == false && document.getElementById('useWatermarkAsDefault').checked == true) document.getElementById('useWatermarkAsDefault').checked = false;"<?php echo (getUserSetting("resamplingDefault") == "yes") ? ' checked="checked"' : NULL;?> /><label for="useResamplingAsDefault"><?php echo _t('이미지 리샘플링 기능을 기본으로 사용합니다. <em>이 기능 사용시 서버에 많은 부하가 걸릴 수 있으니 주의하시기 바랍니다.</em>');?></label><br />
<?php
	if (file_exists(ROOT."/attach/$owner/watermark.gif")) {
?>
												<input type="checkbox" class="checkbox" id="useWatermarkAsDefault" name="useWatermarkAsDefault" value="yes" onclick="if (this.checked == true && document.getElementById('useResamplingAsDefault').checked == false) document.getElementById('useResamplingAsDefault').checked = true;"<?php echo (getUserSetting("waterMarkDefault") == "yes") ? ' checked="checked"' : NULL;?> /><label for="useWatermarkAsDefault"><?php echo _t('워터마크 기능을 기본으로 사용합니다.');?></label>
<?php
	}
?>
											</dd>
										</dl>
										<dl id="watermark-line" class="line">
											<dt><span class="label"><?php echo _t('워터 마크');?></span></dt>
											<dd>
												<p><?php echo _t('워터 마크는 그림이나 사진의 소유권을 표시하거나 위조를 방지하기 위하여 넣는 무늬입니다.');?></p>
<?php
	if (file_exists(ROOT."/attach/$owner/watermark.gif")) {			
		$waterMarkInfo = getimagesize(ROOT."/attach/$owner/watermark.gif");
		if ($waterMarkInfo[0] > 150) {
?>
												<a href="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/watermark.gif?randomseed=<?php echo rand();?>" onclick="window.open(this.href); return false;"><img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/watermark.gif?randomseed=<?php echo rand();?>" width="150" border="1" alt="<?php echo _t('워터마크 이미지');?>" /></a>
<?php
		} else {
?>
												<img src="<?php echo $service['path'];?>/attach/<?php echo $owner;?>/watermark.gif?randomseed=<?php echo rand();?>" border="1" alt="<?php echo _t('워터마크 이미지');?>" />
<?php
		}
	}
?>
												<br /><input type="file" class="input-file" name="waterMark" /><br />
												<input type="checkbox" class="checkbox" id="deleteWaterMark" name="deleteWaterMark" value="yes"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> /><label for="deleteWaterMark"><?php echo _t('워터 마크를 초기화합니다.');?></label>
											</dd>
										</dl>
<?php
	$waterMarkPosition = getUserSetting("waterMarkPosition", "left=10|bottom=10");
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
?>
										<dl id="watermark-position-line" class="line">
											<dt><span class="label"><?php echo _t('워터마크 위치');?></span></dt>
											<dd>
												<div id="vertical-position">
													<select name="verticalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="top"<?php echo isset($top) ? ' selected="selected"' : '';?>><?php echo _t('상단');?></option>
														<option value="middle"<?php echo isset($middle) ? ' selected="selected"' : '';?>><?php echo _t('중앙');?></option>
														<option value="bottom"<?php echo isset($bottom) ? ' selected="selected"' : '';?>><?php echo _t('하단');?></option>
													</select>
													<input type="text" class="input-text" name="verticalPosition" value="<?php echo $verticalValue;?>"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
												<div id="horizontal-position">
													<select name="horizontalType"<?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?>>
														<option value="left"<?php echo isset($left) ? ' selected="selected"' : '';?>><?php echo _t('좌측');?></option>
														<option value="center"<?php echo isset($center) ? ' selected="selected"' : '';?>><?php echo _t('중앙');?></option>
														<option value="right"<?php echo isset($right) ? ' selected="selected"' : '';?>><?php echo _t('우측');?></option>
													</select>
													<input type="text" class="input-text" name="horizontalPosition" value="<?php echo $horizontalValue;?>" <?php echo file_exists(ROOT."/attach/$owner/watermark.gif") ? '' : ' disabled="disabled"';?> />px
												</div>
											</dd>
										</dl>

<?php	
	$thumbnailPadding = getThumbnailPadding();
	$colorOfPadding = getThumbnailPaddingColor();
?>
										<dl id="padding-line" class="line">
											<dt><span class="label"><?php echo _t('이미지 여백');?></span></dt>
											<dd>
<?php
	$paddingOrder = array("top", "bottom", "left", "right");
	$paddingText = array(_t('상단 여백'), _t('하단 여백'), _t('좌측 여백'), _t('우측 여백'));
	
	for ($i=0; $i<count($paddingOrder); $i++) {
		$tempIndex = $paddingOrder[$i];
?>
												<div id="<?php echo $paddingOrder[$i];?>-padding">
													<span class="label"><?php echo $paddingText[$i];?></span>
													<select id="<?php echo $paddingOrder[$i];?>Padding" name="<?php echo $paddingOrder[$i];?>Padding" onchange="checkManualInput(this)" disabled="disabled">
														<option value="0"<?php echo $thumbnailPadding[$tempIndex] == 0 ? ' selected="selected"' : '';?>>0px</option>
														<option value="5"<?php echo $thumbnailPadding[$tempIndex] == 5 ? ' selected="selected"' : '';?>>5px</option>
														<option value="10"<?php echo $thumbnailPadding[$tempIndex] == 10 ? ' selected="selected"' : '';?>>10px</option>
														<option value="15"<?php echo $thumbnailPadding[$tempIndex] == 15 ? ' selected="selected"' : '';?>>15px</option>
														<option value="20"<?php echo $thumbnailPadding[$tempIndex] == 20 ? ' selected="selected"' : '';?>>20px</option>
														<option value="25"<?php echo $thumbnailPadding[$tempIndex] == 25 ? ' selected="selected"' : '';?>>25px</option>
														<option value="direct"<?php echo !in_array($thumbnailPadding[$tempIndex], array(0, 5, 10, 15, 20, 25)) ? ' selected="selected"' : '';?>><?php echo _t('직접입력');?></option>
													</select>
<?php
	$temp = $thumbnailPadding[$paddingOrder[$i]];
?>
													<input type="text" class="input-text" id="<?php echo $paddingOrder[$i];?>PaddingManual" name="<?php echo $paddingOrder[$i];?>PaddingManual" value="<?php echo $temp;?>" />px
												</div>
<?php
	}
?>
												<div id="padding-color">
													<span class="label"><?php echo _t('여백 색상');?></span>
													<input type="text" class="input-text" name="paddingColor" value="<?php echo $colorOfPadding;?>" />
												</div>
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
require ROOT . '/lib/piece/owner/footer1.php';
?>
