<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

$categories = getCategories($blogid);
$selected = 0;
?>
						<script type="text/javascript">
							//<![CDATA[
								function getValueById(id) {
									return document.getElementById(id).value;
								}
								
								function setSkin() {
									if(document.getElementById('showListOnCategoryTitles').checked) 
										showListOnCategory = 2;
									else if(document.getElementById('showListOnCategoryContents').checked) 
										showListOnCategory = 0;
									else if(document.getElementById('showListOnCategorySome').checked) 
										showListOnCategory = 3;
									else 
										showListOnCategory = 1;
									
									if(document.getElementById('showListOnArchiveTitles').checked) 
										showListOnArchive = 2;
									else if(document.getElementById('showListOnArchiveContents').checked) 
										showListOnArchive = 0;
									else if(document.getElementById('showListOnArchiveSome').checked) 
										showListOnArchive = 3;
									else 
										showListOnArchive = 1;

									if(document.getElementById('showListOnTagTitles').checked) 
										showListOnTag = 2;
									else if(document.getElementById('showListOnTagContents').checked) 
										showListOnTag = 0;
									else if(document.getElementById('showListOnTagSome').checked) 
										showListOnTag = 3;
									else 
										showListOnTag = 1;
										
									if(document.getElementById('showListOnAuthorTitles').checked) 
										showListOnAuthor = 2;
									else if(document.getElementById('showListOnAuthorContents').checked) 
										showListOnAuthor = 0;
									else if(document.getElementById('showListOnAuthorSome').checked) 
										showListOnAuthor = 3;
									else 
										showListOnAuthor = 1;
										
									if(document.getElementById('showListOnSearchTitles').checked) 
										showListOnSearch = 2;
									else 
										showListOnSearch = 1;
									
									if(document.getElementById('expandComment').checked) 
										expandComment = 1;
									else 
										expandComment = 0;
									
									if(document.getElementById('expandTrackback').checked) 
										expandTrackback = 1;
									else 
										expandTrackback = 0;
																		
									if(document.getElementById('useFOAF').checked)
										useFOAF = 1;
									else 
										useFOAF = 0;
									
									var tagboxAlign = 1;
									if (document.getElementById('tagboxAlignUsed')	.checked) {
										tagboxAlign = 1;
									} else if(document.getElementById('tagboxAlignName').checked) {
										tagboxAlign = 2;
									} else {
										tagboxAlign = 3;
									}
									if (document.getElementById('pagingComment').checked) {
										useAjaxComment = 1;
									} else {
										useAjaxComment = 0;									
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
									param += 'entriesOnRecent='+getValueById('entriesOnRecent') +'&';
									param += 'noticesOnRecent='+getValueById('noticesOnRecent') +'&';
									param += 'commentsOnRecent='+getValueById('commentsOnRecent') +'&';
									param += 'commentsOnGuestbook='+getValueById('commentsOnGuestbook') +'&';
									param += 'archivesOnPage='+getValueById('archivesOnPage') +'&';
									param += 'tagboxAlign='+tagboxAlign +'&';
									param += 'tagsOnTagbox='+getValueById('tagsOnTagbox') +'&';
									param += 'trackbacksOnRecent='+getValueById('trackbacksOnRecent') +'&';
									param += 'showListOnCategory='+showListOnCategory +'&';
									param += 'showListOnArchive='+showListOnArchive +'&';
									param += 'showListOnTag='+showListOnTag +'&';
									param += 'showListOnAuthor='+showListOnAuthor +'&';
									param += 'showListOnSearch='+showListOnSearch +'&';
									param += 'expandComment='+expandComment +'&';				
									param += 'expandTrackback='+expandTrackback +'&';
									param += 'recentNoticeLength='+getValueById('recentNoticeLength') +'&';
									param += 'recentEntryLength='+getValueById('recentEntryLength') +'&';
									param += 'recentCommentLength='+getValueById('recentCommentLength') +'&';
									param += 'recentTrackbackLength='+getValueById('recentTrackbackLength') +'&';				
									param += 'linkLength='+getValueById('linkLength') +'&';
									param += 'useAjaxComment='+ useAjaxComment +'&';
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
									param += 'url=<?php echo $service['path'];?>/skin/tree/'+document.getElementById('tree').value+'&';
									param += 'showValue='+(document.getElementById('showValue').checked ? 1:0)+'&';
									param += 'itemColor='+document.getElementById('colorOnTree').value+'&';
									param += 'itemBgColor='+document.getElementById('bgColorOnTree').value+'&';
									param += 'activeItemColor='+document.getElementById('activeColorOnTree').value+'&';
									param += 'activeItemBgColor='+document.getElementById('activeBgColorOnTree').value+'&';
									param += 'labelLength='+document.getElementById('labelLengthOnTree').value+'&';
									
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

												<select id="entriesOnRecent" name="entriesOnRecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['entriesOnRecent'])
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

												<select id="noticesOnRecent" name="noticesOnRecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['noticesOnRecent'])
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
										<dl id="recent-notice-line" class="line">
											<dt><span class="label"><?php echo _t('출력될 최근 공지수');?></span></dt>
											<dd><?php echo _f('최근에 쓴 공지를 %1개 보여줍니다.', $arg);?></dd>
										</dl>

<?php
ob_start();
?>

												<select id="commentsOnRecent" name="commentsOnRecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['commentsOnRecent'])
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

												<select id="trackbacksOnRecent" name="trackbacksOnRecent">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['trackbacksOnRecent'])
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

												<select id="archivesOnPage" name="archivesOnPage">
<?php
for ($i = 1; $i < 36; $i++) {
	if ($i == $skinSetting['archivesOnPage'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 36; $i < 120; $i = $i + 12) {
	if ($i == $skinSetting['archivesOnPage']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['archivesOnPage']) && (($i + 12) > $skinSetting['archivesOnPage'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['archivesOnPage'];?>" <?php echo $checked;?>><?php echo $i;?></option>
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
												<input type="radio" id="showListOnCategoryTitles" class="radio" name="showListOnCategory" value="titles"<?php echo ($skinSetting['showListOnCategory'] == 2) ? ' checked="checked"' : '';?> /> <label for="showListOnCategoryTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnCategoryContents" class="radio" name="showListOnCategory" value="contents"<?php echo ($skinSetting['showListOnCategory'] == 0) ? ' checked="checked"' : '';?> /> <label for="showListOnCategoryContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnCategorySome" class="radio" name="showListOnCategory" value="some"<?php echo ($skinSetting['showListOnCategory'] == 3) ? ' checked="checked"' : '';?> /> <label for="showListOnCategorySome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnCategoryAll" class="radio" name="showListOnCategory" value="all"<?php echo ($skinSetting['showListOnCategory'] == 1) ? ' checked="checked"' : '';?> /> <label for="showListOnCategoryAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="archive-click-line" class="line">
											<dt><span class="label"><?php echo _t('저장소 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showListOnArchiveTitles" class="radio" name="showListOnArchive" value="titles"<?php echo ($skinSetting['showListOnArchive'] == 2) ? ' checked="checked"' : '';?> /> <label for="showListOnArchiveTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnArchiveContents" class="radio" name="showListOnArchive" value="contents"<?php echo ($skinSetting['showListOnArchive'] == 0) ? ' checked="checked"' : '';?> /> <label for="showListOnArchiveContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnArchiveSome" class="radio" name="showListOnArchive" value="some"<?php echo ($skinSetting['showListOnArchive'] == 3) ? ' checked="checked"' : '';?> /> <label for="showListOnArchiveSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnArchiveAll" class="radio" name="showListOnArchive" value="all"<?php echo ($skinSetting['showListOnArchive'] == 1) ? ' checked="checked"' : '';?> /> <label for="showListOnArchiveAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="tag-click-line" class="line">
											<dt><span class="label"><?php echo _t('태그 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showListOnTagTitles" class="radio" name="showListOnTag" value="titles"<?php echo ($skinSetting['showListOnTag'] == 2) ? ' checked="checked"' : '';?> /> <label for="showListOnTagTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnTagContents" class="radio" name="showListOnTag" value="contents"<?php echo ($skinSetting['showListOnTag'] == 0) ? ' checked="checked"' : '';?> /> <label for="showListOnTagContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnTagSome" class="radio" name="showListOnTag" value="some"<?php echo ($skinSetting['showListOnTag'] == 3) ? ' checked="checked"' : '';?> /> <label for="showListOnTagSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnTagAll" class="radio" name="showListOnTag" value="all"<?php echo ($skinSetting['showListOnTag'] == 1) ? ' checked="checked"' : '';?> /> <label for="showListOnTagAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="author-click-line" class="line">
											<dt><span class="label"><?php echo _t('저자 선택 시');?></span></dt>
											<dd>
												<input type="radio" id="showListOnAuthorTitles" class="radio" name="showListOnAuthor" value="titles"<?php echo ($skinSetting['showListOnAuthor'] == 2) ? ' checked="checked"' : '';?> /> <label for="showListOnAuthorTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnAuthorContents" class="radio" name="showListOnAuthor" value="contents"<?php echo ($skinSetting['showListOnAuthor'] == 0) ? ' checked="checked"' : '';?> /> <label for="showListOnAuthorContents"><?php echo _t('글 내용을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnAuthorSome" class="radio" name="showListOnAuthor" value="some"<?php echo ($skinSetting['showListOnAuthor'] == 3) ? ' checked="checked"' : '';?> /> <label for="showListOnAuthorSome"><?php echo _t('목록과 한 쪽당 글 수 만큼 글을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnAuthorAll" class="radio" name="showListOnAuthor" value="all"<?php echo ($skinSetting['showListOnAuthor'] == 1) ? ' checked="checked"' : '';?> /> <label for="showListOnAuthorAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="search-click-line" class="line">
											<dt><span class="label"><?php echo _t('검색 시');?></span></dt>
											<dd>
												<input type="radio" id="showListOnSearchTitles" class="radio" name="showListOnSearch" value="titles"<?php echo ($skinSetting['showListOnSearch'] == 2) ? ' checked="checked"' : '';?> /> <label for="showListOnSearchTitles"><?php echo _t('글 목록을 표시합니다.');?></label><br />
												<input type="radio" id="showListOnSearchAll" class="radio" name="showListOnSearch" value="all"<?php echo ($skinSetting['showListOnSearch'] == 1) ? ' checked="checked"' : '';?> /> <label for="showListOnSearchAll"><?php echo _t('목록과 함께 해당되는 모든 글을 표시합니다.');?></label>
											</dd>
										</dl>
										<dl id="post-click-line" class="line">
											<dt><span class="label"><?php echo _t('글을 표시할 때');?></span></dt>
											<dd>
												<input type="checkbox" id="expandComment" class="checkbox" name="expandComment"<?php echo $skinSetting['expandComment'] ? ' checked="checked"' : '';?> /><label for="expandComment"><?php echo _t('댓글을 기본으로 펼칩니다.');?></label><br />
												<input type="checkbox" id="expandTrackback" class="checkbox" name="expandTrackback"<?php echo $skinSetting['expandTrackback'] ? ' checked="checked"' : '';?> /><label for="expandTrackback"><?php echo _t('걸린글을 기본으로 펼칩니다.');?></label>
											</dd>
										</dl>
										<dl id="comment-show-line" class="line">
											<dt><span class="label"><?php echo _t('댓글을 표시할 때');?></span></dt>
											<dd>
												<input type="checkbox" id="pagingComment" class="checkbox" name="pagingComment"<?php echo (Setting::getBlogSettingGlobal('useAjaxComment',1) == 1 ? 'checked = "checked"' : '');?> /><label for="pagingComment"><?php echo _t('댓글 페이징을 사용합니다.');?> <br /><?php echo _t('댓글이 많은 블로그에서 댓글 보기를 누를 경우에만 댓글을 AJAX로 불러와 최근 댓글의 일부부터 보여줍니다.');?> <?php echo _t('댓글 페이징을 사용하면 댓글은 기본적으로 닫힌 채로 출력됩니다.');?></label><br />
											</dd>
										</dl>
									</fieldset>
									
									<fieldset id="length-container" class="container">
										<legend><?php echo _t('문자열 길이 조절');?></legend>
<?php
ob_start();
?>

												<select id="recentNoticeLength" name="recentNoticeLength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentNoticeLength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentNoticeLength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentNoticeLength']) && (($i + 50) > $skinSetting['recentNoticeLength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recentNoticeLength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentNoticeLength'];?></option>
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

												<select id="recentEntryLength" name="recentEntryLength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentEntryLength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentEntryLength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentEntryLength']) && (($i + 50) > $skinSetting['recentEntryLength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recentEntryLength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentEntryLength'];?></option>
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

												<select id="recentCommentLength" name="recentCommentLength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentCommentLength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
													<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentCommentLength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentCommentLength']) && (($i + 50) > $skinSetting['recentCommentLength'])) {
		$checked = ' selected="selected"';
?>
													<option value="<?php echo $skinSetting['recentCommentLength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentCommentLength'];?></option>
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

												<select id="recentTrackbackLength" name="recentTrackbackLength">
<?php
for ($i = 3; $i < 50; $i++) {
	if ($i == $skinSetting['recentTrackbackLength'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
														<option value="<?php echo $i;?>" <?php echo $checked;?>><?php echo $i;?></option>
<?php
}
for ($i = 50; $i < 1000; $i = $i + 50) {
	if ($i == $skinSetting['recentTrackbackLength']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['recentTrackbackLength']) && (($i + 50) > $skinSetting['recentTrackbackLength'])) {
		$checked = ' selected="selected"';
?>
														<option value="<?php echo $skinSetting['recentTrackbackLength'];?>" <?php echo $checked;?>><?php echo $skinSetting['recentTrackbackLength'];?></option>
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

												<select id="linkLength" name="linkLength">
<?php
for ($i = 3; $i <= 80; $i++) {
	if ($i == $skinSetting['linkLength'])
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
												<input type="radio" id="microformatNone" class="radio" name="useMicroformat" value="1" <?php echo (Setting::getBlogSettingGlobal('useMicroformat',3) == 1 ? 'checked = "checked"' : '');?> /><label for="microformatNone"><?php echo _t('Microformat을 사용하지 않습니다.');?></label><br />
												<input type="radio" id="microformatSome" class="radio" name="useMicroformat" value="2" <?php echo (Setting::getBlogSettingGlobal('useMicroformat',3) == 2 ? 'checked = "checked"' : '');?> /><label for="microformatSome"><?php echo _t('웹표준 권고안과 충돌할 수도 있는 규약을 제외한 Microformat을 사용합니다.');?></label><br />
												<input type="radio" id="microformatFull" class="radio" name="useMicroformat" value="3" <?php echo (Setting::getBlogSettingGlobal('useMicroformat',3) == 3 ? 'checked = "checked"' : '');?> /><label for="microformatFull"><?php echo _t('가능한 모든 Microformat을 지원합니다.');?></label>
											</dd>
										</dl>

										<dl id="advanced-foaf-line" class="line">
											<dt><span class="label"><?php echo _t('FOAF 지원');?></span></dt>
											<dd>
												<input type="checkbox" id="useFOAF" class="checkbox" name="useFOAF"<?php echo Setting::getBlogSettingGlobal('useFOAF',1) ? ' checked="checked"' : '';?> /><label for="useFOAF"><?php echo _t('검색엔진이 링크 관계를 인식할 수 있도록 링크에 FOAF를 추가합니다.');?></label>
											</dd>
										</dl>
									</fieldset>
									
									<fieldset id="tag-setting-container" class="container">
										<legend><?php echo _t('태그 조절');?></legend>
										
										<dl id="tag-align-line" class="line">
											<dt><span class="label"><?php echo _t('태그의 정렬방법을');?></span></dt>
											<dd>
												<input type="radio" id="tagboxAlignUsed" class="radio" name="tagboxAlign" value="1" <?php echo ($skinSetting['tagboxAlign'] == 1 ? 'checked = "checked"' : '');?> /><label for="tagboxAlignUsed"><?php echo _t('인기도순으로 표시합니다.');?></label><br />
												<input type="radio" id="tagboxAlignName" class="radio" name="tagboxAlign" value="2" <?php echo ($skinSetting['tagboxAlign'] == 2 ? 'checked = "checked"' : '');?> /><label for="tagboxAlignName"><?php echo _t('이름순으로 표시합니다.');?></label><br />
												<input type="radio" id="tagboxAlignRandom" class="radio" name="tagboxAlign" value="3" <?php echo ($skinSetting['tagboxAlign'] == 3 ? 'checked = "checked"' : '');?> /><label for="tagboxAlignRandom"><?php echo _t('임의로 표시합니다.');?></label>
											</dd>
										</dl>
<?php
ob_start();
?>

												<select id="tagsOnTagbox" name="tagsOnTagbox">
<?php
for ($i = 10; $i <= 200; $i += 10) {
	if ($i == $skinSetting['tagsOnTagbox']) {
		$checked = ' selected="selected"';
	} else if (($i < $skinSetting['tagsOnTagbox']) && (($i + 10) > $skinSetting['tagsOnTagbox'])) {
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
													<option value="-1" <?php echo $skinSetting['tagsOnTagbox'] == - 1 ? 'selected = "selected"' : '';?>><?php echo _t('전체');?></option>
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

												<select id="commentsOnGuestbook" name="commentsOnGuestbook">
<?php
for ($i = 1; $i <= 30; $i++) {
	if ($i == $skinSetting['commentsOnGuestbook'])
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
$skinPath = ROOT . '/skin/tree';
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
												<dt><label for="activeColorOnTree"><?php echo _t('선택된 글자색');?></label></dt>
												<dd><input type="text" id="activeColorOnTree" class="input-text" name="activeColorOnTree" value="<?php echo $skinSetting['activeColorOnTree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="selected-bgcolor-line" class="line">
												<dt><label for="activeBgColorOnTree"><?php echo _t('선택된 배경색');?></label></dt>
												<dd><input type="text" id="activeBgColorOnTree" class="input-text" name="activeBgColorOnTree" value="<?php echo $skinSetting['activeBgColorOnTree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="unselected-color-line" class="line">
												<dt><label for="colorOnTree"><?php echo _t('선택되지 않은 글자색');?></label></dt>
												<dd><input type="text" id="colorOnTree" class="input-text" name="colorOnTree" value="<?php echo $skinSetting['colorOnTree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="unselected-bgcolor-line" class="line">
												<dt><label for="bgColorOnTree"><?php echo _t('선택되지 않은 배경색');?></label></dt>
												<dd><input type="text" id="bgColorOnTree" class="input-text" name="bgColorOnTree" value="<?php echo $skinSetting['bgColorOnTree'];?>" size="7" maxlength="6" onchange="changeTreeStyle()" /></dd>
											</dl>
											<dl id="label-length-line" class="line">
												<dt><label for="labelLengthOnTree"><?php echo _t('분류 길이');?></label></dt>
												<dd><?php echo _f('분류를 %1 글자로 표시합니다.', '<input type="text" id="labelLengthOnTree" class="input-text" name="labelLengthOnTree" value="' . $skinSetting['labelLengthOnTree'] . '" size="3" maxlength="6" onchange="changeTreeStyle()" />');?></dd>
											</dl>
											<dl id="count-display-line" class="line">
												<dt><label for="showValue"><?php echo _t('글 수 출력');?></label></dt>
												<dd><input type="checkbox" class="checkbox" id="showValue" name="showValueOnTree" onclick="changeTreeStyle()" <?php echo $skinSetting['showValueOnTree'] ? 'checked="checked"' : '';?> /><label for="showValue"><?php echo _t('각 분류의 글 수를 표시합니다.');?></label></dd>
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
require ROOT . '/interface/common/owner/footer.php';
?>
