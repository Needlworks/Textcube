<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_READER__', true);
$IV = array(
	'GET' => array(
		'forceRefresh' => array('any', 'mandatory' => false)
	)
);
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

$setting = getReaderSetting($blogid);
?>
			<script type="text/javascript">
				//<![CDATA[
					var s_unknownError = "<?php echo _t('알 수 없는 에러입니다.');?>";
					var s_notFoundPrevPost = "<?php echo _t('이전 포스트가 없습니다.');?>";
					var s_notFoundNextPost = "<?php echo _t('다음 포스트가 없습니다.');?>";
					var s_groupAdded = "<?php echo _t('그룹을 추가하였습니다.');?>";
					var s_enterFeedName = "<?php echo _t('피드 이름을 입력하세요.');?>";
					var s_groupExists = "<?php echo _t('이미 존재하는 그룹 이름입니다.');?>";
					var s_addingGroup = "<?php echo _t('그룹을 추가하고 있습니다.');?>";
					var s_groupModified = "<?php echo _t('그룹을 수정하였습니다.');?>";
					var s_enterGroupName = "<?php echo _t('그룹 이름을 입력하세요.');?>";
					var s_editingGroup = "<?php echo _t('그룹을 수정하고 있습니다.');?>";
					var s_confirmDelete = "<?php echo _t('삭제하시겠습니까?');?>";
					var s_groupRemoved = "<?php echo _t('그룹을 삭제하였습니다.');?>";
					var s_groupNotFound = "<?php echo _t('없는 그룹입니다.');?>";
					var s_removingGroup = "<?php echo _t('그룹을 삭제하고 있습니다.');?>";
					var s_feedAdded = "<?php echo _t('피드를 추가하였습니다.');?>";
					var s_feedExists = "<?php echo _t('이미 존재하는 피드 주소입니다.');?>";
					var s_conNotConnect = "<?php echo _t('입력하신 URL에 접속할 수 없습니다.');?>";
					var s_feedBroken = "<?php echo _t('올바른 피드가 아닙니다.');?>";
					var s_requestFeed = "<?php echo _t('피드를 가져오고 있습니다.');?>";
					var s_feedModified = "<?php echo _t('피드를 수정하였습니다.');?>";
					var s_editingFeed = "<?php echo _t('피드를 수정하고 있습니다.');?>";
					var s_feedRemoved = "<?php echo _t('피드를 삭제했습니다.');?>";
					var s_removingFeed = "<?php echo _t('피드를 삭제하고 있습니다.');?>";
					var s_saved = "<?php echo _t('저장되었습니다');?>";
					var s_markedAsUnread = "<?php echo _t('읽지 않은 상태로 변경하였습니다.');?>";
					var s_markedAsReadAll = "<?php echo _t('모두 읽은 상태로 변경하였습니다.');?>";
					var s_loadingList = "<?php echo _t('글 목록을 불러오고 있습니다.');?>";
					var s_opmlImportComplete = "<?php echo _t('OPML 파일을 가져왔습니다.');?>";
					var s_opmlUploadCompleteMulti = "<?php echo _t('개의 피드를 가져왔습니다.\n피드를 업데이트 해 주십시오.');?>";
					var s_opmlUploadCompleteSingle = "<?php echo _t('하나의 피드를 가져왔습니다.\n피드를 업데이트 해 주십시오.');?>";
					var s_xmlBroken = "<?php echo _t('올바른 XML 파일이 아닙니다.');?>";
					var s_opmlBroken = "<?php echo _t('올바른 OPML 파일이 아닙니다.');?>";
					var s_loadingOPML = "<?php echo _t('OPML 파일을 가져오고 있습니다.');?>";
				//]]>
			</script>
			<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/reader.min.js"></script>
			<script type="text/javascript">
				//<![CDATA[
					var Reader = new TTReader();
					TTReader.feedUpdating = "<?php echo _t('피드 업데이트 중');?>";
					TTReader.feedFailure = "<?php echo _t('잘못된 피드');?>";
					TTReader.feedUpdate = "<?php echo _t('피드 업데이트');?>";
					Reader.isPannelCollapsed = <?php echo Setting::getBlogSettingGlobal('readerPannelVisibility', 1) == 1 ? 'false' : 'true';?>;
					STD.addEventListener(document);
					document.addEventListener("mouseup", Reader.finishResizing, false);
					STD.addEventListener(window);
					window.addEventListener("scroll", function() { Reader.setListPosition(); }, false);
<?php
if ($setting['loadimage'] == 2) {
?>
					Reader.optionForceLoadImage = true;
<?php
}
if ($setting['newwindow'] == 2) {
?>
					Reader.optionForceNewWindow = true;
<?php
}
?>
					var pannelLabel = "<?php echo _t('피드 리스트');?>";
					var configureLabel = "<?php echo _t('설정');?>";
				//]]>
			</script>
			
			<iframe id="hiddenFrame" name="hiddenFrame" src="about:blank" width="1" height="1" style="display: none;"></iframe>
			
			<div id="layout-body">
<?php
if(defined('__TEXTCUBE_READER_SUBMENU__')) 
	
?>
				<h2><?php echo _t('리더 서브메뉴');?></h2>
				
				<div id="reader-menu-box">
					<ul id="reader-menu">
						<li id="all-read"><a href="<?php echo $context->getProperty('uri.blog') . '/owner/network/reader';?>"><span class="text"><?php echo _t('전체 보기');?></span></a></li>
						<li id="scrap"><span id="starredOnlyIndicator" class="scrap-off-icon bullet"><span class="text"></span></span><a href="#void" onclick="Reader.showStarredOnly(); return false;"><span class="text"><?php echo _t('스크랩한 글 보기');?></span></a></li>
						<li id="setting" class="configureText"><a id="settingLabel" href="#void" onclick="Reader.toggleConfigure(); return false;"><span class="text"><?php echo _t('설정');?></span></a></li>
						<li id="feed-update"><a href="#void" onclick="Reader.updateAllFeeds(); return false;"><span class="text"><?php echo _t('모든 피드 새로고침');?><span id="progress"></span></span></a></li>
					</ul>
				</div>
				<div id="search-form">
					<div id="search-box" class="section">
						<input type="text" id="keyword" class="input-text" onkeydown="if(event.keyCode==13) Reader.showSearch()" />
						<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="Reader.showSearch(); return false;" />
					</div>
				</div>
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
						<div id="pannel" style="display: <?php echo Setting::getBlogSettingGlobal('readerPannelVisibility', 1) == 1 ? 'block' : 'none';?>;">
							<div id="groupsAndFeeds" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('피드 목록');?></span></h2>
								
								<div class="data-inbox">
									<div id="group-section" class="section">
										<h3><?php echo _t('피드 그룹');?></h3>
										
										<div id="groupBox" class="container" style="height: <?php echo Setting::getBlogSettingGlobal('readerPannelHeight', 150);?>px;">
<?php
printFeedGroups($blogid);
?>
										</div>
									</div>
									
									<hr class="hidden" />
									
									<div id="feed-section" class="section">
										<h3><?php echo _t('현재 그룹 내의 피드 목록');?></h3>
									
										<div id="feedBox" class="section" style="height: <?php echo Setting::getBlogSettingGlobal('readerPannelHeight', 150);?>px;">
<?php
printFeeds($blogid);
?>
										</div>
									</div>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<div id="configure" class="part" style="display: none;">
								<h2 class="caption"><span class="main-text"><?php echo _t('설정');?></span></h2>
								
								<div class="data-inbox">
									<form id="reader-section" class="section" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/reader/config/save/" target="hiddenFrame">
<?php
if (getUserId() == 1) {
?>
										<fieldset class="container">
											<legend class="title"><?php echo _t('리더 환경을 설정합니다');?></legend>
											
											<dl id="update-line" class="line">
												<dt><label for="updatecycle"><?php echo _t('업데이트 주기');?></label></dt>
												<dd>
													<select id="updatecycle" name="updatecycle">
														<option value="0"<?php echo $setting['updatecycle'] == 0 ? ' selected="selected"' : '';?>><?php echo _t('수집하지 않음');?></option>
														<option value="60"<?php echo $setting['updatecycle'] == 60 ? ' selected="selected"' : '';?>>1<?php echo _t('시간');?></option>
														<option value="120"<?php echo $setting['updatecycle'] == 120 ? ' selected="selected"' : '';?>>2<?php echo _t('시간');?></option>
														<option value="240"<?php echo $setting['updatecycle'] == 240 ? ' selected="selected"' : '';?>>4<?php echo _t('시간');?></option>
														<option value="480"<?php echo $setting['updatecycle'] == 480 ? ' selected="selected"' : '';?>>8<?php echo _t('시간');?></option>
														<option value="960"<?php echo $setting['updatecycle'] == 960 ? ' selected="selected"' : '';?>>16<?php echo _t('시간');?></option>
													</select>
												</dd>
											</dl>
											<dl id="period-line" class="line">
												<dt><label for="feedlife"><?php echo _t('수집한 글의 보존기간');?></label></dt>
												<dd>
													<select id="feedlife" name="feedlife">
														<option value="10"<?php echo $setting['feedlife'] == 10 ? ' selected="selected"' : '';?>>10<?php echo _t('일');?></option>
														<option value="20"<?php echo $setting['feedlife'] == 20 ? ' selected="selected"' : '';?>>20<?php echo _t('일');?></option>
														<option value="30"<?php echo $setting['feedlife'] == 30 ? ' selected="selected"' : '';?>>30<?php echo _t('일');?></option>
														<option value="45"<?php echo $setting['feedlife'] == 45 ? ' selected="selected"' : '';?>>45<?php echo _t('일');?></option>
														<option value="60"<?php echo $setting['feedlife'] == 60 ? ' selected="selected"' : '';?>>60<?php echo _t('일');?></option>
														<option value="90"<?php echo $setting['feedlife'] == 90 ? ' selected="selected"' : '';?>>90<?php echo _t('일');?></option>
														<option value="0"<?php echo $setting['feedlife'] == 0 ? ' selected="selected"' : '';?>><?php echo _t('계속보관');?></option>
													</select>
												</dd>
											</dl>
<?php
}
?>
											<dl id="image-line" class="line">
												<dt><span class="label"><?php echo _t('링크가 차단된 이미지');?></span></dt>
												<dd>
													<div class="image-get-yes"><input type="radio" id="loadimage2" class="radio" name="loadimage" value="2"<?php echo $setting['loadimage'] == 2 ? ' checked="checked"' : '';?> /><label for="loadimage2"><?php echo _t('강제로 읽어오기');?></label></div>
													<div class="image-get-no"><input type="radio" id="loadimage1" class="radio" name="loadimage" value="1"<?php echo $setting['loadimage'] == 1 ? ' checked="checked"' : '';?> /><label for="loadimage1"><?php echo _t('그대로 두기');?></label></div>
												</dd>
											</dl>
											<dl id="javascript-line" class="line">
												<dt><span class="label"><?php echo _t('자바스크립트 허용');?></span></dt>
												<dd>
													<div class="javascript-yes"><input type="radio" id="allowscript1" class="radio" name="allowscript" value="1"<?php echo $setting['allowscript'] == 1 ? ' checked="checked"' : '';?> /><label for="allowscript1"><?php echo _t('허용');?></label></div>
													<div class="javascript-no"><input type="radio" id="allowscript2" class="radio" name="allowscript" value="2"<?php echo $setting['allowscript'] == 2 ? ' checked="checked"' : '';?> /><label for="allowscript2"><?php echo _t('거부');?></label></div>
													<em><?php echo _t('허용 시 문제가 발생할 수 있습니다.');?></em>
												</dd>
											</dl>
											<dl id="link-line" class="line">
												<dt><span class="label"><?php echo _t('링크');?></span></dt>
												<dd>
													<div class="window-self"><input type="radio" id="newwindow1" class="radio" name="newwindow" value="1"<?php echo $setting['newwindow'] == 1 ? ' checked="checked"' : '';?> /><label for="newwindow1"><?php echo _t('기본값');?></label></div>
													<div class="window-blank"><input type="radio" id="newwindow2" class="radio" name="newwindow" value="2"<?php echo $setting['newwindow'] == 2 ? ' checked="checked"' : '';?> /><label for="newwindow2"><?php echo _t('새 창으로');?></label></div>
												</dd>
											</dl>
										</fieldset>
										
										<div class="button-box">
											<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="Reader.saveSetting(); return false;" />
										</div>
									</form>
									
									<form id="opml-section" class="section" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/reader/opml/import/file/" enctype="multipart/form-data" target="hiddenFrame">
										<fieldset class="container">
											<legend class="title"><?php echo _t('OPML 관리');?></legend>
											
											<dl id="get-line" class="line">
												<dt><span class="label"><?php echo _t('가져오기');?></span></dt>
												<dd>
													<div id="get-upload"><input type="radio" id="opmlMethod1" class="radio" name="opmlMethod" value="1" checked="checked" onclick="document.getElementById('opmlUpload').style.display='block';document.getElementById('opmlRequest').style.display='none';" /><label for="opmlMethod1"><span class="text"><?php echo _t('파일 업로드');?></span></label></div>
													<div id="get-url"><input type="radio" id="opmlMethod2" class="radio" name="opmlMethod" value="2" onclick="document.getElementById('opmlUpload').style.display='none';document.getElementById('opmlRequest').style.display='block';" /><label for="opmlMethod2"><span class="text"><?php echo _t('<acronym title="Uniform Resource Locator">URL</acronym> 입력');?></span></label></div>
												</dd>
											</dl>
											<dl id="opmlUpload" class="line">
												<dt><label for="opmlUploadValue"><?php echo _t('<acronym title="Outline Processor Markup Language">OPML</acronym> 업로드');?></label></dt>
												<dd><input type="file" id="opmlUploadValue" class="input-file" name="opmlFile" /></dd>
											</dl>
											<dl id="opmlRequest" class="line" style="display: none;">
												<dt><label for="opmlRequestValue"><?php echo _t('URL로 읽어오기');?></label></dt>
												<dd><input type="text" id="opmlRequestValue" class="input-text" /></dd>
											</dl>
										</fieldset>
										
										<div class="button-box two-button-box">
											<input type="submit" class="import-button input-button" value="<?php echo _t('가져오기');?>" onclick="if(document.getElementById('opml-section').opmlMethod[0].checked) Reader.importOPMLUpload(); else Reader.importOPMLURL(); return false;" />
											<span class="hidden">|</span>
											<input type="submit" class="export-button input-button" value="<?php echo _t('내보내기');?>" onclick="Reader.exportOPML(); return false;" />
										</div>
									</form>
								</div>
							</div>
						</div>
						
						<hr class="hidden" />
							
						<div id="toggleBar" onmousedown="Reader.startResizing(event)">
							<script type="text/javascript">
								//<![CDATA[
									var show_str = '<?php echo _t('패널 보기');?>';
									var hide_str = '<?php echo _t('패널 가리기');?>';
									
									document.write('<a id="toggleButton" class="pannel-<?php echo Setting::getBlogSettingGlobal('readerPannelVisibility', 1) == 1 ? 'show' : 'hide';?>" href="#void" onclick="Reader.togglePannel(event); return false;">');
									document.write('<span class="text"><?php echo Setting::getBlogSettingGlobal('readerPannelVisibility', 1) == 1 ? _t('패널 가리기') : _t('패널 보기');?><\/span>');
									document.write('<\/a>');
								//]]>
							</script>
						</div>
						
						<hr class="hidden" />
						
						<div id="scrollPoint">
							<div id="post-information" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('글 목록');?></span></h2>
								
								<div id="post-list" class="data-inbox">
									<div class="title">
										<a id="totalList" href="<?php echo $context->getProperty('uri.blog');?>/owner/network/reader" title="<?php echo _t('글 목록을 전부 출력합니다.');?>"><span class="text"><?php echo _t('전체 목록');?></span></a><span class="count">(<span id="entriesShown">0</span>/<span id="entriesTotal">0</span>)</span>
										<span class="hidden">|</span>
										<a id="iconMoreEntries" href="#void" onclick="Reader.listScroll(1); return false;" title="<?php echo _t('지나간 글 정보를 더 읽어옵니다.');?>"><span class="text"><?php echo _t('더 읽어오기');?></span></a>
									</div>
									
									<div id="listup" class="section" onscroll="Reader.listScroll(0)">
<?php
printFeedEntries($blogid,0,0,true);
?>
									</div>
									
									<div class="button-box">
										<a class="hide-button button" href="#void" onclick="Reader.showUnreadOnly(); return false;"><span class="text"><?php echo _t('읽은 글 감추기');?></span></a>
										<span class="divider">-</span>
										<a class="markAsReadAll-button button" id="iconMarkAsReadAll" href="#void" onclick="Reader.markAsReadAll(); return false;" title="<?php echo _t('모든 글을 읽었다고 표시합니다.');?>"><span class="text"><?php echo _t('모두 읽은 글로 표시');?></span></a>
										<span class="divider">-</span>
										<a class="shortcut-button button" href="#void" onclick="document.getElementById('shortcuts').style.display = document.getElementById('shortcuts').style.display=='none' ? 'block' : 'none'"><span class="text"><?php echo _t('단축키');?></span></a>
									</div>
									
									<div id="shortcuts" style="display: none;">
										<h3 class="title"><?php echo _t('단축키');?></h3>
										
										<ul>
											<li><kbd>A</kbd>, <kbd>H</kbd> - <?php echo _t('이전 글');?></li>
											<li><kbd>S</kbd>, <kbd>L</kbd> - <?php echo _t('다음 글');?></li>
											<li><kbd>D</kbd> - <?php echo _t('새 창으로');?></li>
											<li><kbd>F</kbd> - <?php echo _t('안 읽은 글만보기');?></li>
											<li><kbd>G</kbd> - <?php echo _t('스크랩된 글 보기');?></li>
											<li><kbd>Q</kbd> - <?php echo _t('블로그 화면으로');?></li>
											<li><kbd>W</kbd> - <?php echo _t('현재글 스크랩');?></li>
											<li><kbd>R</kbd> - <?php echo _t('리더 첫화면으로');?></li>
											<li><kbd>T</kbd> - <?php echo _t('글 수집하기');?></li>
											<li><kbd>J</kbd> - <?php echo _t('위로 스크롤');?></li>
											<li class="last-shortcut"><kbd>K</kbd> - <?php echo _t('아래로 스크롤');?></li>
										</ul>
									</div>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<div id="content-information" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('글 내용');?></span></h2>
								
								<div id="post-content" class="data-inbox">
									<div class="title">
										<span id="blogTitle"></span>
										<span class="divider"> | </span>
										<span class="move"><a class="prev-button button" href="#void" onclick="Reader.prevEntry()"><span class="text"><?php echo _t('이전');?></span></a><span class="divider"> : </span><a class="next-button button" href="#void" onclick="Reader.nextEntry()"><span class="text"><?php echo _t('다음');?></span></a></span>
									</div>
									
									<div id="floatingList" class="section">
										<div id="entry">
<?php
printFeedEntry($blogid);
?>
										</div>
									</div>
								</div>
							</div>
						</div>
<?php
if (isset($_GET['forceRefresh'])) {
?>
						<script type="text/javascript">
							//<![CDATA[
								Reader.updateAllFeeds();
							//]]>
						</script>
<?php
}
require ROOT . '/interface/common/owner/footer.php';
?>
