<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$tabsClass['cover'] = true;
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireModel("blog.coverpage");
requireModel("blog.entry");
requireModel("blog.archive");
requireModel("blog.tag");
requireModel("blog.notice");
requireModel("blog.comment");
requireModel("blog.response.remote");
requireModel("blog.link");

require ROOT . '/interface/common/owner/header.php';


$service['pagecache'] = false; // For plugin setting update.

$stats = Statistics::getStatistics($blogid);

function correctCoverpageImage( $subject ) {
	$pattern_with_src = '/(?:\ssrc\s*=\s*["\']?)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_background = '/(?:\sbackground\s*=\s*["\']??)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_url_func = '/(?:url\s*\(\s*\'?)([^)]+)(?:\'?\s*\))/i';
	$return_val = preg_replace_callback($pattern_with_src , 'correctImagePath', $subject);
	$return_val = preg_replace_callback($pattern_with_background , 'correctImagePath', $return_val);
	$return_val = preg_replace_callback($pattern_with_url_func , 'correctImagePath', $return_val );
	return $return_val;
} 

function correctImagePath($match ) {
	global $skinSetting, $serviceURL;
	$pathArr = explode( "/" , $match[1]);
	if( false === $pathArr  ) 
		return $match[0];
	$c = count( $pathArr);
	if( $c <= 1 ) 
		return $match[0];
	if( $pathArr[$c-1] == "" ) 
		return $match[0];// ./s/b/c/ 이런식으로 경로만 들어있는 경우 스킵
	if( false !== array_search( "http:" , $pathArr) ) 
		return $match[0] ; // full url의 경우 스킵
	if( $pathArr[0] != '.'  && $pathArr[0] != '..' ) 
		return $match[0] ; //첫 디렉토리가 현재 디렉토리가 아닌경우 스킵
	return str_replace( $match[1],  $serviceURL . "/skin/{$skinSetting['skin']}/" . $match[1], $match[0]);
}

function getBlogContentForCoverPage()
{
	global $blogid, $blog, $blogURL, $database, $service, $stats, $skinSetting;

	global $pd_category, $pd_categoryXhtml, $pd_archive, $pd_calendar, $pd_tags, $pd_notices, $pd_recentEntry;
	global $pd_recentComment, $pd_recentTrackback, $pd_link, $pd_authorList;
	
	$categories = getCategories($blogid);
	$totalPosts = getEntriesTotalCount($blogid);
	
	$pd_category = getCategoriesView($totalPosts, $categories, isset($category) ? $category : true);
	$pd_categoryXhtml = getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true);
	$pd_archive = getArchives($blogid);
	$pd_calendar = getCalendarView(getCalendar($blogid, true));
	$pd_tags = getRandomTags($blogid);
	$pd_notices = getNotices($blogid);
	$pd_recentEntry = getRecentEntries($blogid);
	$pd_recentComment = getRecentComments($blogid);
	$pd_recentTrackback = getRecentTrackbacks($blogid);
	$pd_link = getLinks($blogid);
	$pd_authorList = User::getUserNamesOfBlog($blogid);
}


function pretty_dress($view)
{
	global $blogid, $blog, $blogURL, $database, $service, $stats, $skinSetting;
	
	/* local static */
	global $pd_category, $pd_categoryXhtml, $pd_archive, $pd_calendar, $pd_tags, $pd_notices, $pd_recentEntry;
	global $pd_recentComment, $pd_recentTrackback, $pd_link, $pd_authorList;
	
	if (isset($_REQUEST['safe'])) {
		// safe mode
		return '<div class="coverpage-element-safebox">&hellip;</div>';
	}
	if (isset($_REQUEST['tag'])) {
		// safe mode
		return '<div class="coverpage-element-safebox"><p>' . nl2br(htmlspecialchars($view, ENT_QUOTES)) . '</p></div>';
	}
	
	$writer = User::getBlogOwnerName($blogid);
	$pageTitle = _t('페이지 제목');
	
	dress('page_title', htmlspecialchars($pageTitle), $view);
	dress('blogger', htmlspecialchars($writer), $view);
	dress('title', htmlspecialchars($blog['title']), $view);
	dress('desc', htmlspecialchars($blog['description']), $view);
	if (!empty($blog['logo']))
		dress('image', "{$service['path']}/attach/$blogid/{$blog['logo']}", $view);
	else
		dress('image', "{$service['path']}/resources/image/spacer.gif", $view);
	dress('blog_link', "$blogURL/", $view);
	dress('keylog_link', "$blogURL/keylog", $view);
	dress('localog_link', "$blogURL/location", $view);
	dress('taglog_link', "$blogURL/tag", $view);
	dress('guestbook_link', "$blogURL/guestbook", $view);
	
	list($view, $searchView) = Skin::cutSkinTag($view, 'search');
	dress('search_name', 'search', $searchView);
	dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
	dress('search_onclick_submit', 'searchBlog()', $searchView);
	dress('search', '<form id="TTSearchForm" action="'.parseURL($blogURL.'/search/').'" method="get" onsubmit="return searchBlog()">'.$searchView.'</form>', $view);
	
	dress('category', $pd_category, $view);
	dress('category_list', $pd_categoryXhtml, $view);
	dress('count_total', $stats['total'], $view);
	dress('count_today', $stats['today'], $view);
	dress('count_yesterday', $stats['yesterday'], $view);
	
	list($view, $archiveView) = Skin::cutSkinTag($view, 'archive_rep');
	dress('archive_rep', getArchivesView($pd_archive, $archiveView), $view);
	dress('calendar', $pd_calendar, $view);
	list($view, $randomView) = Skin::cutSkinTag($view, 'random_tags');
	dress('random_tags', getRandomTagsView($pd_tags, $randomView), $view);
	
	list($view, $recentNoticeItem) = Skin::cutSkinTag($view, 'rct_notice_rep');	
	list($view, $noticeView) = Skin::cutSkinTag($view, 'rct_notice');
	$notices = $pd_notices;
	if (sizeof($notices) == 0) {
		$notices = array( array('title' => _t('공지 제목'), 'id' => -1));
	}
	if (sizeof($notices) > 0) {
		$itemsView = '';
		foreach ($notices as $notice) {
			$itemView = $recentNoticeItem;
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
			dress('notice_rep_link', "$blogURL/notice/{$notice['id']}", $itemView);
			$itemsView .= $itemView;
		}
		dress('rct_notice_rep', $itemsView, $noticeView);
		dress('rct_notice', $noticeView, $view);
	}

	list($view, $authorList) = Skin::cutSkinTag($view, 'author_rep');
	dress('author_rep', getAuthorListView($pd_authorList, $authorList), $view);
	list($view, $recentEntry) = Skin::cutSkinTag($view, 'rctps_rep');	
	dress('rctps_rep', getRecentEntriesView($pd_recentEntry, $recentEntry), $view);
	list($view, $recentComments) = Skin::cutSkinTag($view, 'rctrp_rep');	
	dress('rctrp_rep', getRecentCommentsView($pd_recentComment, $recentComments), $view);
	list($view, $recentTrackback) = Skin::cutSkinTag($view, 'rcttb_rep');	
	dress('rcttb_rep', getRecentTrackbacksView($pd_recentTrackback, $recentTrackback), $view);
	list($view, $s_link_rep) = Skin::cutSkinTag($view, 'link_rep');	
	dress('link_rep', getLinksView($pd_link, $s_link_rep), $view);
	dress('rss_url', "$blogURL/rss", $view);
	dress('owner_url', "$blogURL/owner", $view);
	dress('textcube_name', TEXTCUBE_NAME, $view);
	dress('textcube_version', TEXTCUBE_VERSION, $view);
	
	$tagSearches = array ( '@<a @i' , '@</a *>@i', '@ id *= *".*"@isU', '@ onkey(down|up|press) *="@i', '@ on(click|load|unload) *="@i', '@<input +@i'      , '@<script.*</script *>@siU', '@<form @siU', '@</form>@siU' );
	$tagReplaces = array ( '<span ' , '</span>'  , ''                , ' onnothing="'                , ' onnothing="'                 , '<input disabled="disabled" ' , ''                        , '<div ' , '</div>');
			
	$view = preg_replace($tagSearches, $tagReplaces, $view);
	
	return correctCoverpageImage($view);
}

$defaultModeSelected = false;
$safeModeSelected = false;
$tagModeSelected = false;
$initModeSelected = false;

$viewMode = '';
$viewMode2 = '';
$viewMode3 = '';
$previewMode = '';

if ((!isset($_REQUEST['safe'])) && (!isset($_REQUEST['tag']))) {
	$defaultModeSelected = true;
} else if ((isset($_REQUEST['safe'])) && (!isset($_REQUEST['tag']))) {
	$safeModeSelected = true;
	$viewMode = '&amp;viewMode=safe';
	$viewMode2 = '?viewMode=safe';
	$viewMode3 = '&viewMode=safe';
	$previewMode = '&safe';
} else if ((!isset($_REQUEST['safe'])) && (isset($_REQUEST['tag']))) {
	$tagModeSelected = true;
	$viewMode = '&amp;viewMode=tag';
	$viewMode2 = '?viewMode=tag';
	$viewMode3 = '&viewMode=tag';
	$previewMode = '&tag';
} else if ((isset($_REQUEST['safe'])) && (isset($_REQUEST['tag']))) {
		$initModeSelected = true;
}

$coverpagePluginArray = array();
for ($i=0; $i<count($coverpageMappings); $i++) {
	$coverpagePluginArray[$coverpageMappings[$i]['plugin'] . '/' . $coverpageMappings[$i]['handler']]=
		array( 
			'type' => 3, 'id' => $coverpageMappings[$i]['handler'],
			'plugin' => $coverpageMappings[$i]['plugin'], 'title' =>$coverpageMappings[$i]['title'], 
			'display' => $coverpageMappings[$i]['display'],
			'identifier' => implode(':', array(3,$coverpageMappings[$i]['plugin'],$coverpageMappings[$i]['handler'])),
			'parameters' => $coverpageMappings[$i]['parameters']
		);
}

$skin = new Skin($skinSetting['skin']);
$usedCoverpageBasicModule = array();
$coverpageCount = count($skin->coverpageBasicModules);

getBlogContentForCoverPage();
?>
						<form id="part-coverpage-order" class="part" method="post" action="<?php echo parseURL($blogURL.'/owner/skin/coverpage');?>">
							<h2 class="caption"><span class="main-text"><?php echo _t('블로그 표지를 관리합니다');?></span></h2>
<?php
require ROOT . '/interface/common/owner/skinTab.php';
?>

							<div class="main-explain-box">
								<p class="explain"><?php echo _t('블로그의 표지 구성을 변경할 수 있습니다.');?> <?php echo _f('표지는 블로그 첫 화면 또는 %1/cover 에 표시되는 부분입니다.',$defaultURL);?> <?php echo _t('표지에 새로운 요소를 추가/삭제할 수 있으며 패널들을 자유롭게 배치 할 수 있습니다.');?>
								<?php echo ($service['type'] == 'path' || $service['type'] == 'domain') ?  _t('다중 사용자 모드로 설치된 경우 블로그 관리자는 표지 기능을 이용하여 대표 블로그를 다른 블로그들에 대한 센터 기능을 하도록 구성할 수 있습니다.') : '';?></p>
								<p class="explain"><?php echo _t('블로그-설정 메뉴에서 블로그 첫 화면을 표지로 사용하도록 변경할 수 있습니다.');?></p>
							</div>
							
							<dl id="direct-link-line" class="line">
								<dt><?php echo _t('플러그인 설정');?></dt>
								<dd><a class="button" href="<?php echo $blogURL;?>/owner/plugin?visibility=coverpage"><?php echo _t('위젯 켜고 끄기');?></a></dd>
							</dl>	
<?php
if (is_null($skin->cover) || count($coverpageMappings) == 0) {
	if (is_null($skin->cover))
		$errmsg = _t('사용중인 스킨이 블로그 표지를 지원하지 않습니다.');
	else
		$errmsg = _t('사용중인 표지 플러그인이 없습니다.');
?>
							<ul id="coverpage-tabs-box" class="tabs-box">
								<li class="selected"><a id="default-mode-button" class="button" href="<?php echo $blogURL;?>/owner/skin/coverpage" title="<?php echo _t('실제 출력되는 내용을 직접 볼 수 있는 기본 모드입니다.');?>"><?php echo _t('기본모드');?></a></li>
							</ul>
							
							<div id="coverpage-box-disabled" class="data-inbox">
								<p><?php echo $errmsg;?></p>
							</div>
						</form>
<?php
	require ROOT . '/interface/common/owner/footer.php';
	exit;
}

// 사용중인 표지 모듈 리스트 출력.
$bFirstRadio = true;
$coverpageConfig = getCoverpageModuleOrderData();
if (is_null($coverpageConfig)) {
	for ($i=0; $i<$coverpageCount; $i++) {
		$coverpageConfig[$i] = array();
	}
}
?>
							<ul id="coverpage-tabs-box" class="tabs-box">
								<li<?php echo $defaultModeSelected ? ' class="selected"' : NULL;?>><a id="default-mode-button" class="button" href="<?php echo $blogURL;?>/owner/skin/coverpage" title="<?php echo _t('실제 출력되는 내용을 직접 볼 수 있는 기본 모드입니다.');?>"><?php echo _t('기본모드');?></a></li>
								<li<?php echo $safeModeSelected ? ' class="selected"' : NULL;?>><a id="safe-mode-button" class="button" href="<?php echo $blogURL;?>/owner/skin/coverpage?safe" title="<?php echo _t('태그를 사용하지 않아 레이아웃이 깨질 위험이 없는 모드입니다.');?>"><?php echo _t('안전모드');?></a></li>
								<li<?php echo $tagModeSelected ? ' class="selected"' : NULL;?>><a id="tag-mode-button" class="button" href="<?php echo $blogURL;?>/owner/skin/coverpage?tag" title="<?php echo _t('실제 블로그 표지에 사용되는 태그를 직접사용하는 모드입니다.');?>"><?php echo _t('태그모드');?></a></li>
								<li<?php echo $initModeSelected ? ' class="selected"' : NULL;?>><a id="init-button" class="button" href="coverpage/initialize<?php echo $viewMode2;?>" onclick="if (!confirm('<?php echo _t('정말 표지의 기능을 초기화하시겠습니까?');?>')) return false;" title="<?php echo _t('표지의 기능을 스킨 설정 상태로 초기화합니다.');?>"><span class="text"><?php echo _t('초기화');?></span></a></li>
							</ul>
							
							<div id="coverpage-box" class="data-inbox">
								<table border="0">
									<tr>
<?php

for ($i=0; $i<$coverpageCount; $i++) {
	if (array_key_exists($i, $coverpageConfig))
		$orderConfig = $coverpageConfig[$i];
	else
		$orderConfig = array();
?>
										<td class="section">
											<h3><input type="radio" id="coverpage-<?php echo $i + 1;?>" class="radio" name="coverpageNumber" value="<?php echo $i;?>"<?php echo $bFirstRadio ? ' checked="checked"' : NULL;?> /><label for="coverpage-<?php echo $i + 1;?>"><?php echo $skin->coverpageName[$i];?></label></h3>
											<ul id="coverpage-ul-<?php echo $i;?>" class="coverpage">
<?php
	for ($j=0; $j<count($orderConfig); $j++) {
		if ($orderConfig[$j]['type'] == 3) { // plugin
			$plugin = $orderConfig[$j]['id']['plugin'];
			$handler = $orderConfig[$j]['id']['handler'];
			$sidbarPluginIndex = $plugin . '/' . $handler;
			
			$invalidPlugin = false;
			if (!array_key_exists($sidbarPluginIndex,  $coverpagePluginArray)) {
				// invalid or missed plug-in
				$coverpagePluginArray[$sidbarPluginIndex] = array();
				$coverpagePluginArray[$sidbarPluginIndex]['title'] = $plugin;
				$coverpagePluginArray[$sidbarPluginIndex]['handler'] = $handler;
				$coverpagePluginArray[$sidbarPluginIndex]['display'] = $plugin;
				$coverpagePluginArray[$sidbarPluginIndex]['parameters'] = array();
				$invalidPlugin = true;
			} else {
				include_once (ROOT . "/plugins/{$plugin}/index.php");
			}
			if (function_exists($handler))
			{
				if( !empty( $configMappings[$plugin]['config'] ) ) 				
					$configVal = getCurrentSetting($plugin);
				else
					$configVal ='';
?>
												<li class="coverpage-module coverpage-plugin-module" id="coverpage-element-<?php echo "{$i}-{$j}";?>">
													<h4 class="module-title"><?php echo $coverpagePluginArray[$sidbarPluginIndex]['display'], '::', $coverpagePluginArray[$sidbarPluginIndex]['title'];?></h4>
													<div class="button-box">
<?php
				if ($j == 0) {
?>
														<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
				} else {
?>
														<a href="<?php echo parseURL($blogURL.'/owner/skin/coverpage/order/?coverpageNumber='.$i.'&amp;targetcoverpageNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j - 1).$viewMode);?>" title="<?php echo _t('이 표지 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
				}
				
				if ($j == count($orderConfig) - 1) {
?>
														<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
				} else {
?>
														<a href="<?php echo parseURL($blogURL.'/owner/skin/coverpage/order/?coverpageNumber='.$i.'&amp;targetcoverpageNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j + 2).$viewMode);?>" title="<?php echo _t('이 표지 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
				}
?>
														<a href="<?php echo parseURL($blogURL.'/owner/skin/coverpage/delete/?coverpageNumber='.$i.'&amp;modulePos='.$j.$viewMode);?>" title="<?php echo _t('이 표지 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.gif" border="0" alt="<?php echo _t('삭제');?>" /></a>
														<!-- TODO : coverpage plugin settting -->									
													</div>
<?php 
				$pluginparameters = $coverpagePluginArray[$sidbarPluginIndex]['parameters'];
				if (count($pluginparameters) > 0) {
?>
													<div class="edit-button-box">
														<a href="<?php echo parseURL($blogURL.'/owner/skin/coverpage/edit?coverpageNumber='.$i.'&amp;modulePos='.$j.$viewMode);?>"><?php echo _t('편집');?></a>
													</div>
<?php 
				}
?>
													<div class="module-content">
<?php
				if (($invalidPlugin == false) && function_exists($handler)) {
					$pluginURL = "{$service['path']}/plugins/{$orderConfig[$j]['id']['plugin']}";
					echo pretty_dress(call_user_func($handler, $orderConfig[$j]['parameters']));
				}
?>
													</div>
												</li>
<?php
			}
			if ($invalidPlugin == true) 
				 unset($coverpagePluginArray[$sidbarPluginIndex]);
				
		} else {
			// other type
		}
	}
?>
											</ul>
										</td>
<?php
	$bFirstRadio = false;
}
?>
									</tr>
								</table>
							</div>
							
							<div id="coverpage-element-box" class="data-inbox">
								<h3><?php echo _t('추가 가능한 모듈');?></h3>

								<fieldset id="coverpage-plugin-module-box" class="section">
									<legend><?php echo _t('추가 가능한 플러그인');?></legend>
									
									<ul class="module-list">
<?php
// 표지 플러그인 모듈을 리스트에 포함시킨다.
foreach ($coverpagePluginArray as $nowKey) {
?>
										<li class="coverpage-module" id="<?php echo "add-coverpage-module-{$nowKey['identifier']}";?>">
											<h4 class="module-title"><input type="radio" id="module<?php echo $nowKey['identifier'];?>" class="radio" name="moduleId" value="<?php echo $nowKey['identifier'];?>" /><label for="module<?php echo $nowKey['identifier'];?>"><?php echo $nowKey['display'], '::' , $nowKey['title'];?></label></h4>
																			
											<div class="edit-button-box">
											</div>
											<div class="module-content">
<?php
	$pluginURL = "{$service['path']}/plugins/{$nowKey['plugin']}";
	include_once (ROOT . "/plugins/{$nowKey['plugin']}/index.php");
	echo pretty_dress(call_user_func($nowKey['id'], array('preview' => '')));
?>
											</div>
										</li>
<?php
}
?>	
									</ul>
								</fieldset>
								
								<div class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('모듈 추가');?>" title="<?php echo _t('선택된 모듈을 표지에 추가합니다.');?>"/>									
								</div>
							</div>
							
							<p id="coverpage-description" class="pseudo-clear">
								<em>* <?php echo _t('표지의 위치는 스킨의 구조에 따라 달라집니다.');?></em>
							</p>
						</form>
						<script src="<?php echo $service['path'];?>/resources/script/dojo/dojo.js" type="text/javascript"></script>
						<script src="<?php echo $service['path'];?>/resources/script/coverpage.js" type="text/javascript"></script>
						<script type="text/javascript">
							//<![CDATA[
								var decorateDragPanelString_deleteTitle = "<?php echo _t('이 표지 모듈을 삭제합니다.');?>";
								var commonString_delete = "<?php echo _t('삭제');?>";
								var commonString_cancel = "<?php echo _t('취소');?>";
								var commonString_close = "<?php echo _t('닫기');?>";
								var commonString_saving = "<?php echo _t('저장하고 있습니다.');?>";
								var commonString_error = "<?php echo _t('저장하지 못했습니다');?>";
								
								var viewMode = "<?php echo $viewMode3;?>";
								var previewMode = "<?php echo $previewMode;?>";
								
								function reordering() {
									var pos = 0;
									var pNode = null;
									
<?php
		for ($i=0; $i<$coverpageCount; $i++) {
			echo "pNode = document.getElementById('coverpage-ul-{$i}').firstChild;";
?>
									pos = 0;
									while (pNode != null) {
										if ((pNode.nodeType != 3/* TEXT_NODE */) && (pNode.className.indexOf("coverpage-module") != -1)) { 
											pNode.modulePos = pos++;
											
											var p2Node = pNode.firstChild;
											while (p2Node != null) {
												if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'a')) break;
												p2Node = p2Node.nextSibling;
											}
											if (p2Node != null) {
												p2Node.href = blogURL + "/owner/skin/coverpage/delete/?coverpageNumber=" + pNode.coverpageNumber + "&modulePos=" + pNode.modulePos + viewMode;
											}
											
											if ((pNode.moduleCategory == 'plugin') && (pNode.hasPropertyEdit == true)) {
												p2Node = pNode.firstChild;
												while (p2Node != null) {
													if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'div')) {
														break;
													}
													p2Node = p2Node.nextSibling;
												}
												if (p2Node != null) {
													p2Node.innerHTML = '<a onclick="editCoverpagePlugin('+ pNode.coverpageNumber + ',' + pNode.modulePos + '); return false" ><?php echo _t('편집');?><\/a>';
												}
											}
										}
										
										pNode = pNode.nextSibling;
									}
<?php
		}
?>
								}
								
								function initPages()
								{
									dlg = dojo.widget.createWidget("popupWindow", {}, document.getElementById('body-skin').firstChild, 'after');
									dlg.domNode.className = 'ajax-popup-window';
									var inputs = document.getElementsByTagName("input");
									for (i=0; i < inputs.length;) {
										if (inputs[i].className == 'radio') {
											inputs[i].parentNode.removeChild(inputs[i]);
										} else {
											i++
							 			}
									}
									inputs = document.getElementsByTagName("div");
									for (i=0; i < inputs.length;) {
										if (inputs[i].className == 'button-box') {
											inputs[i].parentNode.removeChild(inputs[i]);
										} else {
											i++
							 			}
									}
									
<?php
for ($i=0; $i<$coverpageCount; $i++) {
	echo "document.getElementById('coverpage-ul-{$i}').coverpage = {$i};";
	echo "new DropPanel(document.getElementById('coverpage-ul-{$i}'), [\"coverpage\"]);";
	
	$orderConfig = array_key_exists($i, $coverpageConfig) ? $coverpageConfig[$i] :  array();
	for ($j=0; $j<count($orderConfig); $j++) {
		echo "document.getElementById('coverpage-element-{$i}-{$j}').coverpageNumber = {$i};";
		echo "document.getElementById('coverpage-element-{$i}-{$j}').modulePos = {$j};";
		echo "document.getElementById('coverpage-element-{$i}-{$j}').ajaxtype = 'reorder';";
		echo "document.getElementById('coverpage-element-{$i}-{$j}').hasPropertyEdit = false;";
		
		if ($orderConfig[$j]['type'] == 3) {
			echo "document.getElementById('coverpage-element-{$i}-{$j}').moduleCategory = 'plugin';";
			echo "document.getElementById('coverpage-element-{$i}-{$j}').hasPropertyEdit = ";
			$plugin = $orderConfig[$j]['id']['plugin'];
			$handler = $orderConfig[$j]['id']['handler'];
			$sidbarPluginIndex = $plugin . '/' . $handler;
			
			echo (array_key_exists($sidbarPluginIndex, $coverpagePluginArray) 
				&& (count($coverpagePluginArray[$sidbarPluginIndex]['parameters']) > 0)) ? 'true' : 'false';
			echo ";";
		}
		
		echo "new DragPanel(document.getElementById('coverpage-element-{$i}-{$j}'), [\"coverpage\"]);";
	}
}

foreach ($coverpagePluginArray as $nowKey) {
	echo "document.getElementById('add-coverpage-module-{$nowKey['identifier']}').identifier = '{$nowKey['identifier']}';";
	echo "document.getElementById('add-coverpage-module-{$nowKey['identifier']}').ajaxtype = 'register';";
	echo "document.getElementById('add-coverpage-module-{$nowKey['identifier']}').moduleCategory = 'plugin';";
	echo "document.getElementById('add-coverpage-module-{$nowKey['identifier']}').hasPropertyEdit = ";
	echo count($nowKey['parameters']) > 0 ? 'true' : 'false';
	echo ";";
	echo "new DragPanelAdd(document.getElementById('add-coverpage-module-{$nowKey['identifier']}'), [\"coverpage\"]);";
}
?>
									reordering();
								}
								dojo.addOnLoad(initPages);

								function changeList() {
									document.getElementById("part-coverpage-order").submit();
								}
							//]]>							
						</script>						
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
