<?
define('ROOT', '../../..');
define('__TATTERTOOLS_READER__', true);
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header6.php';
$setting = getReaderSetting($owner);
?>
			<script type="text/javascript">
				//<![CDATA[
					var s_unknownError = "<?=_t('알 수 없는 에러입니다.')?>";
					var s_notFoundPrevPost = "<?=_t('이전 포스트가 없습니다.')?>";
					var s_notFoundNextPost = "<?=_t('다음 포스트가 없습니다.')?>";
					var s_groupAdded = "<?=_t('그룹이 추가됐습니다.')?>";
					var s_enterFeedName = "<?=_t('피드 이름을 입력하세요.')?>";
					var s_groupExists = "<?=_t('이미 존재하는 그룹 이름입니다.')?>";
					var s_addingGroup = "<?=_t('그룹을 추가하고 있습니다.')?>";
					var s_groupModified = "<?=_t('그룹이 수정됐습니다.')?>";
					var s_enterGroupName = "<?=_t('그룹 이름을 입력하세요.')?>";
					var s_editingGroup = "<?=_t('그룹을 수정하고 있습니다.')?>";
					var s_confirmDelete = "<?=_t('삭제하시겠습니까?')?>";
					var s_groupRemoved = "<?=_t('그룹이 삭제됐습니다.')?>";
					var s_groupNotFound = "<?=_t('없는 그룹입니다.')?>";
					var s_removingGroup = "<?=_t('그룹을 삭제하고 있습니다.')?>";
					var s_feedAdded = "<?=_t('피드가 추가됐습니다.')?>";
					var s_feedExists = "<?=_t('이미 존재하는 피드 주소입니다.')?>";
					var s_conNotConnect = "<?=_t('입력하신 URL에 접속할 수 없습니다.')?>";
					var s_feedBroken = "<?=_t('올바른 피드가 아닙니다.')?>";
					var s_requestFeed = "<?=_t('피드를 가져오고 있습니다.')?>";
					var s_feedModified = "<?=_t('피드가 수정됐습니다.')?>";
					var s_editingFeed = "<?=_t('피드를 수정하고 있습니다.')?>";
					var s_feedRemoved = "<?=_t('피드가 삭제됐습니다.')?>";
					var s_removingFeed = "<?=_t('피드를 삭제하고 있습니다.')?>";
					var s_saved = "<?=_t('저장되었습니다.')?>";
					var s_markedAsUnread = "<?=_t('읽지 않은 상태로 변경됐습니다.')?>";
					var s_loadingList = "<?=_t('포스트 목록을 불러오고 있습니다.')?>";
					var s_opmlImportComplete = "<?=_t('OPML 파일을 가져왔습니다.')?>";
					var s_opmlUploadComplete = "<?=_t('개의 피드를 가져왔습니다.\n피드를 업데이트 해주세요.')?>";
					var s_xmlBroken = "<?=_t('올바른 XML 파일이 아닙니다.')?>";
					var s_opmlBroken = "<?=_t('올바른 OPML 파일이 아닙니다.')?>";
					var s_loadingOPML = "<?=_t('OPML 파일을 가져오고 있습니다.')?>";
				//]]>
			</script>
			<script type="text/javascript" src="<?=$service['path']?>/script/reader.js"></script>
			<script type="text/javascript">
				//<![CDATA[
					var Reader = new TTReader();
					TTReader.feedUpdating = "<?=_t('피드 업데이트 중')?>";
					TTReader.feedFailure = "<?=_t('잘못된 피드')?>";
					TTReader.feedUpdate = "<?=_t('피드 업데이트')?>";
					Reader.isPannelCollapsed = <?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'false' : 'true'?>;
					STD.addEventListener(document);
					document.addEventListener("mouseup", Reader.finishResizing, false);
					STD.addEventListener(window);
					window.addEventListener("scroll", function() { Reader.setListPosition(); }, false);
<?
if ($setting['loadImage'] == 2) {
?>
					Reader.optionForceLoadImage = true;
<?
}
if ($setting['newWindow'] == 2) {
?>
					Reader.optionForceNewWindow = true;
<?
}
?>
					var pannelLabel = "<?=_t('피드 리스트')?>";
					var configureLabel = "<?=_t('설정')?>";
				//]]>
			</script>
			
			<iframe id="hiddenFrame" name="hiddenFrame" src="about:blank" width="1" height="1" style="display: none;"></iframe>
			
			<div id="layout-body">
				<h2><?=_t('리더 서브메뉴')?></h2>
				
				<div id="reader-menu-box">
					<ul id="reader-menu">
						<li id="all-read"><a href="<?=$blogURL . '/owner/reader'?>"><span class="text"><?=_t('전체보기')?></span></a></li>
						<li id="scrap"><span id="starredOnlyIndicator" class="scrap-off-icon bullet"><span class="text"></span></span><a href="#void" onclick="Reader.showStarredOnly(); return false;"><span class="text"><?=_t('스크랩된 글 보기')?></span></a></li>
						<li id="setting"><a id="settingLabel" href="#void" onclick="Reader.toggleConfigure(); return false;"><span class="text"><?=_t('설정')?></span></a></li>
						<li id="feed-update"><a href="#void" onclick="Reader.updateAllFeeds(); return false;"><span class="text"><?=_t('모든 피드 업데이트')?><span id="progress"></span></span></a></li>
						<li id="search">
							<input type="text" id="keyword" class="text-input" onkeydown="if(event.keyCode==13) Reader.showSearch()" />
							<a class="search-button button" href="#void" onclick="Reader.showSearch()"><span class="text"><?=_t('검색')?></span></a>
						</li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<form method="post" action="<?=$blogURL?>/owner/reader/opml/import/file/" target="hiddenFrame" enctype="multipart/form-data">
						
						<div id="data-outbox">
							<div id="pannel" style="display: <?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'block' : 'none'?>;">
								<div id="groupsAndFeeds" class="part">
									<h2 class="caption"><span class="main-text"><?=_t('피드 리스트')?></span></h2>
									
									<div class="data-inbox">
										<div id="group-section" class="section">
											<h3><?=_t('피드 그룹')?></h3>
											
											<div id="groupBox" class="container" style="height: <?=getPersonalization($owner, 'readerPannelHeight')?>px;">
<?
printFeedGroups($owner);
?>
											</div>
										</div>
										
										<hr class="hidden" />
										
										<div id="feed-section" class="section">
											<h3><?=_t('현재 그룹 내의 피드 리스트')?></h3>
										
											<div id="feedBox" class="section" style="height: <?=getPersonalization($owner, 'readerPannelHeight')?>px;">
<?
printFeeds($owner);
?>
											</div>
										</div>
									</div>
								</div>
								
								<hr class="hidden" />
								
								<div id="configure" class="part" style="display: none;">
									<h2 class="caption"><span class="main-text"><?=_t('설정')?></span></h2>
									
									<div class="data-inbox">
										<div id="reader-section" class="section">
											<h3 class="title"><?=_t('리더 환경을 설정합니다')?></h3>
<?
if (getUserId() == 1) {
?>
											<div class="container">
												<dl id="update-line" class="line">
													<dt><label for="updateCycle"><?=_t('업데이트 주기')?></label></dt>
													<dd>
														<select id="updateCycle" name="updateCycle">
															<option value="0"<?=$setting['updateCycle'] == 0 ? ' selected="selected"' : ''?>><?=_t('수집하지 않음')?></option>
															<option value="60"<?=$setting['updateCycle'] == 60 ? ' selected="selected"' : ''?>>1<?=_t('시간')?></option>
															<option value="120"<?=$setting['updateCycle'] == 120 ? ' selected="selected"' : ''?>>2<?=_t('시간')?></option>
															<option value="240"<?=$setting['updateCycle'] == 240 ? ' selected="selected"' : ''?>>4<?=_t('시간')?></option>
															<option value="480"<?=$setting['updateCycle'] == 480 ? ' selected="selected"' : ''?>>8<?=_t('시간')?></option>
															<option value="960"<?=$setting['updateCycle'] == 960 ? ' selected="selected"' : ''?>>16<?=_t('시간')?></option>
														</select>
													</dd>
												</dl>
												<dl id="period-line" class="line">
													<dt><label for="feedLife"><?=_t('수집한 글의 보존기간')?></label></dt>
													<dd>
														<select id="feedLife" name="feedLife">
															<option value="10"<?=$setting['feedLife'] == 10 ? ' selected="selected"' : ''?>>10<?=_t('일')?></option>
															<option value="20"<?=$setting['feedLife'] == 20 ? ' selected="selected"' : ''?>>20<?=_t('일')?></option>
															<option value="30"<?=$setting['feedLife'] == 30 ? ' selected="selected"' : ''?>>30<?=_t('일')?></option>
															<option value="45"<?=$setting['feedLife'] == 45 ? ' selected="selected"' : ''?>>45<?=_t('일')?></option>
															<option value="60"<?=$setting['feedLife'] == 60 ? ' selected="selected"' : ''?>>60<?=_t('일')?></option>
															<option value="90"<?=$setting['feedLife'] == 90 ? ' selected="selected"' : ''?>>90<?=_t('일')?></option>
															<option value="0"<?=$setting['feedLife'] == 0 ? ' selected="selected"' : ''?>><?=_t('계속보관')?></option>
														</select>
													</dd>
												</dl>
<?
}
?>
												<dl id="image-line" class="line">
													<dt><span class="label"><?=_t('링크가 차단된 이미지')?></span></dt>
													<dd>
														<div class="image-get-yes"><input type="radio" id="loadImage2" class="radio" name="loadImage" value="2"<?=$setting['loadImage'] == 2 ? ' checked="checked"' : ''?> /> <label for="loadImage2"><?=_t('강제로 읽어오기')?></label></div>
														<div class="image-get-no"><input type="radio" id="loadImage1" class="radio" name="loadImage" value="1"<?=$setting['loadImage'] == 1 ? ' checked="checked"' : ''?> /> <label for="loadImage1"><?=_t('그대로 두기')?></label></div>
													</dd>
												</dl>
												<dl id="javascript-line" class="line">
													<dt><span class="label"><?=_t('자바스크립트 허용')?></span></dt>
													<dd>
														<div class="javascript-yes"><input type="radio" id="allowScript1" class="radio" name="allowScript" value="1"<?=$setting['allowScript'] == 1 ? ' checked="checked"' : ''?> /> <label for="allowScript1"><?=_t('허용')?></label></div>
														<div class="javascript-no"><input type="radio" id="allowScript2" class="radio" name="allowScript" value="2"<?=$setting['allowScript'] == 2 ? ' checked="checked"' : ''?> /> <label for="allowScript2"><?=_t('거부')?></label></div>
														<em><?php echo _t('허용 시 문제가 발생할 수 있습니다.')?></em>
													</dd>
												</dl>
												<dl id="link-line" class="line">
													<dt><span class="label"><?=_t('링크')?></span></dt>
													<dd>
														<div class="window-self"><input type="radio" id="newWindow1" class="radio" name="newWindow" value="1"<?=$setting['newWindow'] == 1 ? ' checked="checked"' : ''?> /> <label for="newWindow1"><?=_t('기본값')?></label></div>
														<div class="window-blank"><input type="radio" id="newWindow2" class="radio" name="newWindow" value="2"<?=$setting['newWindow'] == 2 ? ' checked="checked"' : ''?> /> <label for="newWindow2"><?=_t('새창으로')?></label></div>
													</dd>
												</dl>
											
												<div class="button-box">
													<a class="save-button button" href="#void" onclick="Reader.saveSetting()"><span class="text"><?=_t('저장하기')?></span></a>
												</div>
											</div>
										</div>
										
										<div id="opml-section" class="section">
											<h3 class="title"><?=_t('OPML 관리')?></h3>
											
											<div class="container">
												<dl id="get-line" class="line">
													<dt><span class="label"><?=_t('가져오기')?></span></dt>
													<dd>
														<div id="get-upload"><input type="radio" id="opmlMethod1" class="radio" name="opmlMethod" value="1" checked="checked" onclick="document.getElementById('opmlUpload').style.display='block';document.getElementById('opmlRequest').style.display='none';" /> <label for="opmlMethod1"><span class="text"><?=_t('파일 업로드')?></span></label></div>
														<div id="get-url"><input type="radio" id="opmlMethod2" class="radio" name="opmlMethod" value="2" onclick="document.getElementById('opmlUpload').style.display='none';document.getElementById('opmlRequest').style.display='block';" /> <label for="opmlMethod2"><span class="text"><?=_t('<acronym title="Uniform Resource Locator">URL</acronym> 입력')?></span></label></div>
													</dd>
												</dl>
												<dl id="opmlUpload" class="line">
													<dt><label for="opmlUploadValue"><?=_t('<acronym title="Outline Processor Markup Language">OPML</acronym> 업로드')?></label></dt>
													<dd><input type="file" id="opmlUploadValue" class="file-input" name="opmlFile" /></dd>
												</dl>
												<dl id="opmlRequest" class="line" style="display: none;">
													<dt><label for="opmlRequestValue"><?=_t('URL로 읽어오기')?></label></dt>
													<dd><input type="text" id="opmlRequestValue" class="text-input" /></dd>
												</dl>
												
												<div class="button-box two-button-box">
													<a class="import-button button" href="#void" onclick="if(document.forms[0].opmlMethod[0].checked) Reader.importOPMLUpload(); else Reader.importOPMLURL();"><span class="text"><?=_t('가져오기')?></span></a>
													<span class="hidden">|</span>
													<a class="export-button button" href="#void" onclick="Reader.exportOPML()"><span class="text"><?=_t('내보내기')?></span></a>												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<div id="toggleBar" onmousedown="Reader.startResizing(event)">
								<script type="text/javascript">
									//<![CDATA[
										var show_str = '<?=_t('패널 보기')?>';
										var hide_str = '<?=_t('패널 가리기')?>';
										
										document.write('<a id="toggleButton" class="pannel-<?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'show' : 'hide'?>" href="#void" onclick="Reader.togglePannel(event)">');
										document.write('<span class="text"><?= getPersonalization($owner, 'readerPannelVisibility') == 1 ? _t('패널 가리기') : _t('패널 보기')?></span>');
										document.write('</a>');
									//]]>
								</script>
							</div>
							
							<hr class="hidden" />
							
							<div id="scrollPoint">
								<div id="post-information" class="part">
									<h2 class="caption"><span class="main-text"><?=_t('포스트 리스트')?></span></h2>
									
									<div id="post-list" class="data-inbox">
										<div class="title">
											<a id="totalList" href="<?=$blogURL?>/owner/reader" title="<?=_t('포스트 리스트를 전부 출력합니다.')?>"><span class="text"><?=_t('전체 목록')?></span></a><span class="count">(<span id="entriesShown">0</span>/<span id="entriesTotal">0</span>)</span>
											<span class="hidden">|</span>
											<a id="iconMoreEntries" href="#void" onclick="Reader.listScroll(1); return false;" title="<?=_t('지나간 포스팅 정보를 더 읽어옵니다.')?>"><span class="text"><?=_t('더 읽어오기')?></span></a>
										</div>
										
										<div id="listup" class="section" onscroll="Reader.listScroll(0)">
<?
printFeedEntries($owner);
?>
										</div>
										
										<div class="button-box">
											<a class="hide-button button" href="#void" onclick="Reader.showUnreadOnly(); return false;"><span class="text"><?=_t('읽은 글 감추기')?></span></a>
											<span class="divider">-</span>
											<a class="shortcut-button button" href="#void" onclick="document.getElementById('shortcuts').style.display = document.getElementById('shortcuts').style.display=='none' ? 'block' : 'none'"><span class="text"><?=_t('단축키 보기')?></span></a>
										</div>
										
										<div id="shortcuts" style="display: none;">
											<h3 class="title"><?=_t('단축키')?></h3>
											
											<ul>
												<li><kbd>A</kbd>, <kbd>H</kbd> - <?=_t('이전 글')?></li>
												<li><kbd>S</kbd>, <kbd>L</kbd> - <?=_t('다음 글')?></li>
												<li><kbd>D</kbd> - <?=_t('새창으로')?></li>
												<li><kbd>F</kbd> - <?=_t('안 읽은 글만 보기')?></li>
												<li><kbd>G</kbd> - <?=_t('스크랩한 글만 보기')?></li>
												<li><kbd>Q</kbd> - <?=_t('블로그 화면으로')?></li>
												<li><kbd>W</kbd> - <?=_t('현재글 스크랩')?></li>
												<li><kbd>R</kbd> - <?=_t('리더 첫화면으로')?></li>
												<li><kbd>T</kbd> - <?=_t('글 수집하기')?></li>
												<li><kbd>J</kbd> - <?=_t('위로 스크롤')?></li>
												<li class="last-shortcut"><kbd>K</kbd> - <?=_t('아래로 스크롤')?></li>
											</ul>
										</div>
									</div>
								</div>
								
								<hr class="hidden" />
								
								<div id="content-information" class="part">
									<h2 class="caption"><span class="main-text"><?=_t('포스트 내용')?></span></h2>
									
									<div id="post-content" class="data-inbox">
										<div class="title">
											<span id="blogTitle"></span>
											<span class="divider"> | </span>
											<span class="move"><a class="prev-button button" href="#void" onclick="Reader.prevEntry()"><span class="text"><?=_t('이전')?></span></a><span class="divider"> : </span><a class="next-button button" href="#void" onclick="Reader.nextEntry()"><span class="text"><?=_t('다음')?></span></a></span>
										</div>
										
										<div id="floatingList" class="section">
											<div id="entry">
<?
printFeedEntry($owner);
?>
											</div>
										</div>
									</div>
								</div>
							</div>
<?
if (isset($_GET['forceRefresh'])) {
?>
							<script type="text/javascript">
								//<![CDATA[
									Reader.updateAllFeeds();
								//]]>
							</script>
<?
}
require ROOT . '/lib/piece/owner/footer0.php';
?>