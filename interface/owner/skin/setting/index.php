<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
require ROOT . '/library/piece/owner/header.php';

$categories = getCategories($blogid);
$selected = 0;
?>
						<script type="text/javascript">
							//<![CDATA[
								function getValueById(id) {
									return document.getElementById(id).value;
								}
								
								function setSkin() {
									if(document.getElementById('showlistoncategoryTitles').checked) 
										showlistoncategory = 2;
									else if(document.getElementById('showlistoncategoryContents').checked) 
										showlistoncategory = 0;
									else if(document.getElementById('showlistoncategorySome').checked) 
										showlistoncategory = 3;
									else 
										showlistoncategory = 1;
									
									if(document.getElementById('showlistonarchiveTitles').checked) 
										showlistonarchive = 2;
									else if(document.getElementById('showlistonarchiveContents').checked) 
										showlistonarchive = 0;
									else if(document.getElementById('showlistonarchiveSome').checked) 
										showlistonarchive = 3;
									else 
										showlistonarchive = 1;

									if(document.getElementById('showlistontagTitles').checked) 
										showlistontag = 2;
									else if(document.getElementById('showlistontagContents').checked) 
										showlistontag = 0;
									else if(document.getElementById('showlistontagSome').checked) 
										showlistontag = 3;
									else 
										showlistontag = 1;
										
									if(document.getElementById('showlistonauthorTitles').checked) 
										showlistonauthor = 2;
									else if(document.getElementById('showlistonauthorContents').checked) 
										showlistonauthor = 0;
									else if(document.getElementById('showlistonauthorSome').checked) 
										showlistonauthor = 3;
									else 
										showlistonauthor = 1;
										
									if(document.getElementById('showlistonsearchTitles').checked) 
										showlistonsearch = 2;
									else 
										showlistonsearch = 1;
									
									if(document.getElementById('expandcomment').checked) 
										expandcomment = 1;
									else 
										expandcomment = 0;
									
									if(document.getElementById('expandtrackback').checked) 
										expandtrackback = 1;
									else 
										expandtrackback = 0;
																		
									if(document.getElementById('useFOAF').checked)
										useFOAF = 1;
									else 
										useFOAF = 0;
									
									var tagboxalign = 1;
									if (document.getElementById('tagboxalignUsed')	.checked) {
										tagboxalign = 1;
									} else if(document.getElementById('tagboxalignName').checked) {
										tagboxalign = 2;
									} else {
										tagboxalign = 3;
									}
									
									if (document.getElementById('microformatNone').checked) {
										useMicroformat = 1;
									} else if(document.getElementById('microformatSome').checked) {
										useMicroformat = 2;
									} else {
										useMicroformat = 3;
									}

									param  = '';
									param += 'entriesOnPage='+getValueById('entriesOnPage') +'&';
									param += 'entriesOnList='+getValueById('entriesOnList') +'&';
									param += 'entriesonrecent='+getValueById('entriesonrecent') +'&';
									param += 'commentsonrecent='+getValueById('commentsonrecent') +'&';
									param += 'commentsonguestbook='+getValueById('commentsonguestbook') +'&';
									param += 'archivesonpage='+getValueById('archivesonpage') +'&';
									param += 'tagboxalign='+tagboxalign +'&';
									param += 'tagsontagbox='+getValueById('tagsontagbox') +'&';
									param += 'trackbacksonrecent='+getValueById('trackbacksonrecent') +'&';
									param += 'showlistoncategory='+showlistoncategory +'&';
									param += 'showlistonarchive='+showlistonarchive +'&';
									param += 'showlistontag='+showlistontag +'&';
									param += 'showlistonauthor='+showlistonauthor +'&';
									param += 'showlistonsearch='+showlistonsearch +'&';
									param += 'expandcomment='+expandcomment +'&';				
									param += 'expandtrackback='+expandtrackback +'&';
									param += 'recentnoticelength='+getValueById('recentnoticelength') +'&';
									param += 'recententrylength='+getValueById('recententrylength') +'&';
									param += 'recentcommentlength='+getValueById('recentcommentlength') +'&';
									param += 'recenttrackbacklength='+getValueById('recenttrackbacklength') +'&';				
									param += 'linklength='+getValueById('linklength') +'&';
									param += 'useMicroformat='+ useMicroformat +'&';
									param += 'useFOAF='+ useFOAF +'&';

									var request = new HTTPRequest("POST", '<?php echo $blogURL;?>/owner/skin/setting/skin/');
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
									}
									request.onError = function() {
										PM.showErrorMessage("<?php echo _t('저장하지 못했습니다');?>", "center", "bottom");
									}
									request.send(param);
								}
										
								function changeTreeStyle() {	
									var param = '';
									param += 'name='+document.getElementById('tree').value+'&';
									param += 'url=<?php echo $service['path'];?>/image/tree/'+document.getElementById('tree').value+'&';
									param += 'showValue='+(document.getElementById('showValue').checked ? 1:0)+'&';
									param += 'itemColor='+document.getElementById('colorontree').value+'&';
									param += 'itemBgColor='+document.getElementById('bgcolorontree').value+'&';
									param += 'activeItemColor='+document.getElementById('activecolorontree').value+'&';
									param += 'activeItemBgColor='+document.getElementById('activebgcolorontree').value+'&';
									param += 'labelLength='+document.getElementById('labellengthontree').value+'&';
									
									document.getElementById('treePreview').src="<?php echo $blogURL;?>/owner/skin/setting/tree/preview/?"+param;
								}
							//]]>
						</script>
						
						<div id="part-skin-setting" class="part">
							<h2 class="caption"><span class="main-text"><?php echo setDetailPanel('panel_skin_setting','link',_t('스킨에 따라 표시되는 여러 값들을 세세하게 변경합니다'));?></span></h2>
							
							<div id="panel_skin_setting" class="data-inbox folding">
								<form id="skinSetting" class="section" method="post" action="<?php echo $blogURL;?>/owner/skin/setting/skin" enctype="application/x-www-form-urlencoded">
									<fieldset id="per-page-container" class="container">
										<legend><?php echo _t('출력 숫자 조절');?></legend>
<?php
ob_start();
?>

												<select id="entriesOnPage" name="entriesOnPage">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $blog['entriesOnPage'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="post-per-count-line" class="line">
											<dt><span class="label"><?php echo _t('한 쪽당 글 수');?></span></dt>
											<dd><?php echo _f('블로그 글을 한 쪽당 %1개 보여줍니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="entriesOnList" name="entriesOnList">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $blog['entriesOnList'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="list-per-count-line" class="line">
											<dt><span class="label"><?php echo _t('목록 한 쪽당 글 수');?></span></dt>
											<dd><?php echo _f('글목록을 한 쪽당 %1개 보여줍니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="entriesonrecent" name="entriesonrecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['entriesonrecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-post-line" class="line">
											<dt><span class="label"><?php echo _t('출력될 최근 글 수');?></span></dt>
											<dd><?php echo _f('최근에 쓴 글을 %1개 보여줍니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="commentsonrecent" name="commentsonrecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['commentsonrecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-comment-line" class="line">
											<dt><span class="label"><?php echo _t('출력될 최근 댓글 수');?></span></dt>
											<dd><?php echo _f('최근에 달린 댓글을 %1개 보여줍니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="trackbacksonrecent" name="trackbacksonrecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['trackbacksonrecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-trackback-line" class="line">
											<dt><span class="label"><?php echo _t('출력될 최근 걸린글 수');?></span></dt>
											<dd><?php echo _f('최근 걸린글을 %1개 보여줍니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="archivesonpage" name="archivesonpage">
<?php
for ($i = 1; $i < 36; $i++) {
	if ($i == $skinSetting['archivesonpage'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 36; $i < 120; $i = $i + 12) {
	if ($i == $skinSetting['archivesonpage']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['archivesonpage']) && (($i + 12) > $skinSetting['archivesonpage'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['archivesonpage'];?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-archive-line" class="line">
											<dt><span class="label"><?php echo _t('출력될 저장소 수');?></span></dt>
											<dd><?php echo _f('저장소에 글 목록을 %1달치만큼 보여줍니다.', $arg);?></dd>

										</dl>
									</fieldset>
									
									<fieldset id="click-container" class="container">
										<legend><?php echo _t('클릭 설정');?></legend>
										<dl id="category-click-line" class="line">
											<dt><span class="label"><?php echo _t('분류 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showlistoncategoryTitles" class="radio" name="showlistoncategory" value="titles"<?php echo ($skinSetting['showlistoncategory'] == 2) ? ' checked="checked"' : '';?> /> <label for="showlistoncategoryTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showlistoncategoryContents" class="radio" name="showlistoncategory" value="contents"<?php echo ($skinSetting['showlistoncategory'] == 0) ? ' checked="checked"' : '';?> /> <label for="showlistoncategoryContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showlistoncategorySome" class="radio" name="showlistoncategory" value="some"<?php echo ($skinSetting['showlistoncategory'] == 3) ? ' checked="checked"' : '';?> /> <label for="showlistoncategorySome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showlistoncategoryAll" class="radio" name="showlistoncategory" value="all"<?php echo ($skinSetting['showlistoncategory'] == 1) ? ' checked="checked"' : '';?> /> <label for="showlistoncategoryAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="archive-click-line" class="line">
											<dt><span class="label"><?php echo _t('저장소 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showlistonarchiveTitles" class="radio" name="showlistonarchive" value="titles"<?php echo ($skinSetting['showlistonarchive'] == 2) ? ' checked="checked"' : '';?> /> <label for="showlistonarchiveTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonarchiveContents" class="radio" name="showlistonarchive" value="contents"<?php echo ($skinSetting['showlistonarchive'] == 0) ? ' checked="checked"' : '';?> /> <label for="showlistonarchiveContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonarchiveSome" class="radio" name="showlistonarchive" value="some"<?php echo ($skinSetting['showlistonarchive'] == 3) ? ' checked="checked"' : '';?> /> <label for="showlistonarchiveSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonarchiveAll" class="radio" name="showlistonarchive" value="all"<?php echo ($skinSetting['showlistonarchive'] == 1) ? ' checked="checked"' : '';?> /> <label for="showlistonarchiveAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="tag-click-line" class="line">
											<dt><span class="label"><?php echo _t('태그 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showlistontagTitles" class="radio" name="showlistontag" value="titles"<?php echo ($skinSetting['showlistontag'] == 2) ? ' checked="checked"' : '';?> /> <label for="showlistontagTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showlistontagContents" class="radio" name="showlistontag" value="contents"<?php echo ($skinSetting['showlistontag'] == 0) ? ' checked="checked"' : '';?> /> <label for="showlistontagContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showlistontagSome" class="radio" name="showlistontag" value="some"<?php echo ($skinSetting['showlistontag'] == 3) ? ' checked="checked"' : '';?> /> <label for="showlistontagSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showlistontagAll" class="radio" name="showlistontag" value="all"<?php echo ($skinSetting['showlistontag'] == 1) ? ' checked="checked"' : '';?> /> <label for="showlistontagAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="author-click-line" class="line">
											<dt><span class="label"><?php echo _t('저자 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showlistonauthorTitles" class="radio" name="showlistonauthor" value="titles"<?php echo ($skinSetting['showlistonauthor'] == 2) ? ' checked="checked"' : '';?> /> <label for="showlistonauthorTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonauthorContents" class="radio" name="showlistonauthor" value="contents"<?php echo ($skinSetting['showlistonauthor'] == 0) ? ' checked="checked"' : '';?> /> <label for="showlistonauthorContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonauthorSome" class="radio" name="showlistonauthor" value="some"<?php echo ($skinSetting['showlistonauthor'] == 3) ? ' checked="checked"' : '';?> /> <label for="showlistonauthorSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonauthorAll" class="radio" name="showlistonauthor" value="all"<?php echo ($skinSetting['showlistonauthor'] == 1) ? ' checked="checked"' : '';?> /> <label for="showlistonauthorAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="search-click-line" class="line">
											<dt><span class="label"><?php echo _t('검색 시');?></span></dt>
											<dd>
												<input type="radio" id="showlistonsearchTitles" class="radio" name="showlistonsearch" value="titles"<?php echo ($skinSetting['showlistonsearch'] == 2) ? ' checked="checked"' : '';?> /> <label for="showlistonsearchTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showlistonsearchAll" class="radio" name="showlistonsearch" value="all"<?php echo ($skinSetting['showlistonsearch'] == 1) ? ' checked="checked"' : '';?> /> <label for="showlistonsearchAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="post-click-line" class="line">
											<dt><span class="label"><?php echo _t('글을 표시할 때');?></span></dt>
											<dd>
												<input type="checkbox" id="expandcomment" class="checkbox" name="expandcomment"<?php echo $skinSetting['expandcomment'] ? ' checked="checked"' : '';?> /><label for="expandcomment"><?php echo _t('댓글을 기본으로 펼칩니다.');?></label><br />
												<input type="checkbox" id="expandtrackback" class="checkbox" name="expandtrackback"<?php echo $skinSetting['expandtrackback'] ? ' checked="checked"' : '';?> /><label for="expandtrackback"><?php echo _t('걸린글을 기본으로 펼칩니다.');?></label>
											</dd>
										</dl>
									</fieldset>
									
									<fieldset id="length-container" class="container">
										<legend><?php echo _t('문자열 길이 조절');?></legend>
<?php
ob_start();
?>

												<select id="recentnoticelength" name="recentnoticelength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentnoticelength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentnoticelength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentnoticelength']) && (($i + 50) > $skinSetting['recentnoticelength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recentnoticelength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentnoticelength'];?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>


												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-notice-length-line" class="line">
											<dt><span class="label"><?php echo _t('최근 공지 길이');?></span></dt>
											<dd><?php echo _f('최근 공지를 %1 글자로 표시합니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="recententrylength" name="recententrylength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recententrylength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recententrylength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recententrylength']) && (($i + 50) > $skinSetting['recententrylength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recententrylength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recententrylength'];?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>

													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-post-length-line" class="line">
											<dt><span class="label"><?php echo _t('최근 글 길이');?></span></dt>
											<dd><?php echo _f('최근 글을 %1 글자로 표시합니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="recentcommentlength" name="recentcommentlength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentcommentlength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentcommentlength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentcommentlength']) && (($i + 50) > $skinSetting['recentcommentlength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recentcommentlength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentcommentlength'];?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-comment-length-line" class="line">
											<dt><span class="label"><?php echo _t('최근 댓글 길이');?></span></dt>
											<dd><?php echo _f('최근 댓글을 %1 글자로 표시합니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="recenttrackbacklength" name="recenttrackbacklength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recenttrackbacklength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
														<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recenttrackbacklength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recenttrackbacklength']) && (($i + 50) > $skinSetting['recenttrackbacklength'])) {
		$checked = ' selected="selected"';
?>
														<option value="<?php echo $skinSetting['recenttrackbacklength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recenttrackbacklength'];?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>
														<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-trackback-length-line" class="line">
											<dt><span class="label"><?php echo _t('최근 걸린글 길이');?></span></dt>
											<dd><?php echo _f('최근 걸린글을 %1 글자로 표시합니다.', $arg);?></dd>
										</dl>
<?php
ob_start();
?>

												<select id="linklength" name="linklength">
<?php
for ($i = 3; $i <= 80; $i++) {
	if ($i == $skinSetting['linklength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="recent-link-length-line" class="line">
											<dt><span class="label"><?php echo _t('링크 길이');?></span></dt>
											<dd><?php echo _f('링크를 %1 글자로 표시합니다.', $arg);?></dd>
										</dl>
									</fieldset>

									<fieldset id="advanced-setting-container" class="container">
										<legend><?php echo _t('확장 지원');?></legend>
										<dl id="advanced-microformat-line" class="line">
											<dt><span class="label"><?php echo _t('Microformat 지원');?></span></dt>
											<dd>
												<input type="radio" id="microformatNone" class="radio" name="useMicroformat" value="1" <?php echo (getBlogSetting('useMicroformat',3) == 1 ? 'checked = "checked"' : '');?> /><label for="microformatNone"><?php echo _t('Microformat을 사용하지 않습니다.');?></label><br />
												<input type="radio" id="microformatSome" class="radio" name="useMicroformat" value="2" <?php echo (getBlogSetting('useMicroformat',3) == 2 ? 'checked = "checked"' : '');?> /><label for="microformatSome"><?php echo _t('웹표준 권고안과 충돌할 수도 있는 규약을 제외한 Microformat을 사용합니다.');?></label><br />
												<input type="radio" id="microformatFull" class="radio" name="useMicroformat" value="3" <?php echo (getBlogSetting('useMicroformat',3) == 3 ? 'checked = "checked"' : '');?> /><label for="microformatFull"><?php echo _t('가능한 모든 Microformat을 지원합니다.');?></label>
											</dd>
										</dl>

										<dl id="advanced-foaf-line" class="line">
											<dt><span class="label"><?php echo _t('FOAF 지원');?></span></dt>
											<dd>
												<input type="checkbox" id="useFOAF" class="checkbox" name="useFOAF"<?php echo getBlogSetting('useFOAF',1) ? ' checked="checked"' : '';?> /><label for="useFOAF"><?php echo _t('검색엔진이 링크 관계를 인식할 수 있도록 링크에 FOAF를 추가합니다.');?></label>
											</dd>
										</dl>
									</fieldset>
									
									<fieldset id="tag-setting-container" class="container">
										<legend><?php echo _t('태그 조절');?></legend>
										
										<dl id="tag-align-line" class="line">
											<dt><span class="label"><?php echo _t('태그의 정렬방법을');?></span></dt>
											<dd>
												<input type="radio" id="tagboxalignUsed" class="radio" name="tagboxalign" value="1" <?php echo ($skinSetting['tagboxalign'] == 1 ? 'checked = "checked"' : '');?> /><label for="tagboxalignUsed"><?php echo _t('인기도순으로 표시합니다.');?></label><br />
												<input type="radio" id="tagboxalignName" class="radio" name="tagboxalign" value="2" <?php echo ($skinSetting['tagboxalign'] == 2 ? 'checked = "checked"' : '');?> /><label for="tagboxalignName"><?php echo _t('이름순으로 표시합니다.');?></label><br />
												<input type="radio" id="tagboxalignRandom" class="radio" name="tagboxalign" value="3" <?php echo ($skinSetting['tagboxalign'] == 3 ? 'checked = "checked"' : '');?> /><label for="tagboxalignRandom"><?php echo _t('임의로 표시합니다.');?></label>
											</dd>
										</dl>
<?php
ob_start();
?>

												<select id="tagsontagbox" name="tagsontagbox">
<?php
for ($i = 10; $i <= 200; $i += 10) {
	if ($i == $skinSetting['tagsontagbox']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['tagsontagbox']) && (($i + 10) > $skinSetting['tagsontagbox'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
		$checked = '';
	} else {
		$checked = '';
	}
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
													<option value="-1" <?php echo $skinSetting['tagsontagbox'] == - 1 ? 'selected = "selected"' : '';?>><?php echo _t('전체');?></option>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl id="tag-count-line" class="line">
											<dt><span class="label"><?php echo _t('태그상자의 태그 수');?></span></dt>
											<dd><?php echo _f('태그상자의 태그를 %1개 표시합니다.', $arg);?></dd>
										</dl>
									</fieldset>
									
									<fieldset id="guestbook-setting-container" class="container">
										<legend><?php echo _t('방명록 관련 조절');?></legend>
<?php
ob_start();
?>

												<select id="commentsonguestbook" name="commentsonguestbook">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['commentsonguestbook'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>						
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
?>
												</select>
<?php
$arg = ob_get_contents();
ob_end_clean();
?>
										<dl class="line">
											<dt><span class="label"><?php echo _t('쪽 당 방명록 수');?></span></dt>
											<dd><?php echo _f('방명록 한 쪽 당 %1개 글을 표시합니다.', $arg);?></dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setSkin(); return false;" />
									</div>
								</form>
							</div>
						</div>
						
						<hr class="hidden" />
						
						<div id="part-skin-tree" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('분류 디자인을 변경합니다');?></span></h2>
							
							<form id="setSkinForm" method="post" action="<?php echo $blogURL;?>/owner/skin/setting/tree" enctype="application/x-www-form-urlencoded">
								<div class="data-inbox">
									<div id="tree-preview-box">
										<div class="title"><?php echo _t('미리보기');?></div>
										<iframe id="treePreview" src="<?php echo $blogURL;?>/owner/skin/setting/tree/preview" width="300" height="300" frameborder="0" style="overflow: visible;"></iframe>
									</div>
									
									<div class="section">
										<fieldset id="property-box" class="container">
											<legend><?php echo _t('트리 속성');?></legend>
											
											<dl id="tree-skin-line" class="line">
												<dt><span class="label"><?php echo _t('분류 스킨 선택');?></span></dt>
												<dd>
													<select name="tree" id="tree" onchange="changeTreeStyle()">
<?php
$skinPath = ROOT . '/image/tree';
if ($dh = opendir($skinPath)) {
	while (($file = readdir($dh)) !== false) {
		if ($file == '.' || $file == '..')
			continue;
		if ((!file_exists($skinPath . '/' . $file . '/tab_top.gif')))
			continue;
		if ($skinSetting['tree'] == $file)
			echo "<option value=\"$file\" selected=\"selected\">$file</option>";
		else
			echo "<option value=\"$file\">$file</option>";
	}
	closedir($dh);
}
?>
													</select>
												</dd>
											</dl>
											<dl id="selected-color-line" class="line">
												<dt><label for="activecolorontree"><?php echo _t('선택된 글자색');?></label></dt>
												<dd><input type="text" id="activecolorontree" class="input-text" name="activecolorontree" value="<?php echo $skinSetting['activecolorontree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="selected-bgcolor-line" class="line">
												<dt><label for="activebgcolorontree"><?php echo _t('선택된 배경색');?></label></dt>
												<dd><input type="text" id="activebgcolorontree" class="input-text" name="activebgcolorontree" value="<?php echo $skinSetting['activebgcolorontree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="unselected-color-line" class="line">
												<dt><label for="colorontree"><?php echo _t('선택되지 않은 글자색');?></label></dt>
												<dd><input type="text" id="colorontree" class="input-text" name="colorontree" value="<?php echo $skinSetting['colorontree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="unselected-bgcolor-line" class="line">
												<dt><label for="bgcolorontree"><?php echo _t('선택되지 않은 배경색');?></label></dt>
												<dd><input type="text" id="bgcolorontree" class="input-text" name="bgcolorontree" value="<?php echo $skinSetting['bgcolorontree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="label-length-line" class="line">
												<dt><label for="labellengthontree"><?php echo _t('분류 길이');?></label></dt>
												<dd><?php echo _f('분류를 %1 글자로 표시합니다.', '<input type="text" id="labellengthontree" class="input-text" name="labellengthontree" value="' . $skinSetting['labellengthontree'] . '" size="3" maxlength="6" onchange="changeTreeStyle()" />');?></dd>
											</dl>
											<dl id="count-display-line" class="line">
												<dt><label for="showValue"><?php echo _t('글 수 출력');?></label></dt>
												<dd><input type="checkbox" class="checkbox" id="showValue" name="showvalueontree" onclick="changeTreeStyle()" <?php echo $skinSetting['showvalueontree'] ? 'checked="checked"' : '';?> /><label for="showValue"><?php echo _t('각 분류의 글 수를 표시합니다.');?></label></dd>
											</dl>
										</fieldset>
									
										<div class="button-box">
											<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="document.getElementById('setSkinForm').submit()" />
										</div>
									</div>
								</div>
							</form>
						</div>
<?php
require ROOT . '/library/piece/owner/footer.php';
?>
