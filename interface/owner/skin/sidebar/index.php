<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$tabsClass['sidebar'] = true;
importlib('blogskin');
importlib("model.blog.sidebar");
importlib("model.blog.entry");
importlib("model.blog.archive");
importlib("model.blog.tag");
importlib("model.blog.notice");
importlib("model.blog.comment");
importlib("model.blog.remoteresponse");
importlib("model.blog.link");

require ROOT . '/interface/common/owner/header.php';


$stats = Statistics::getStatistics($blogid);

function correctSidebarImage( $subject ) {
	$pattern_with_src = '/(?:\ssrc\s*=\s*["\']?)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_background = '/(?:\sbackground\s*=\s*["\']??)([^\s^"^>^\']+)(?:[\s">\'])/i';
	$pattern_with_url_func = '/(?:url\s*\(\s*\'?)([^)]+)(?:\'?\s*\))/i';
	$return_val = preg_replace_callback($pattern_with_src , 'correctImagePath', $subject);
	$return_val = preg_replace_callback($pattern_with_background , 'correctImagePath', $return_val);
	$return_val = preg_replace_callback($pattern_with_url_func , 'correctImagePath', $return_val );
	return $return_val;
} 

function correctImagePath($match ) {
	$context = Model_Context::getInstance();
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
	return str_replace( $match[1],  $context->getProperty('uri.service') . "/skin/blog/".$context->getProperty('skin.skin')."/" . $match[1], $match[0]);
}

function getBlogContentForSideBar()
{
	global $blogid, $blog, $database, $service, $stats, $skinSetting;

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
	global $blogid, $blog, $database, $service, $stats, $skinSetting;
	$context = Model_Context::getInstance();
	// local static 
	global $pd_category, $pd_categoryXhtml, $pd_archive, $pd_calendar, $pd_tags, $pd_notices, $pd_recentEntry;
	global $pd_recentComment, $pd_recentTrackback, $pd_link, $pd_authorList;
	
	if (isset($_REQUEST['safe'])) {
		// safe mode
		return '<div class="sidebar-element-safebox">&hellip;</div>';
	}
	if (isset($_REQUEST['tag'])) {
		// safe mode
		return '<div class="sidebar-element-safebox"><p>' . nl2br(htmlspecialchars($view, ENT_QUOTES)) . '</p></div>';
	}
	
	$writer = User::getBlogOwnerName($blogid);
	$pageTitle = _t('페이지 제목');
	
	dress('page_title', htmlspecialchars($pageTitle), $view);
	dress('blogger', htmlspecialchars($writer), $view);
	dress('title', htmlspecialchars($context->getProperty('blog.title')), $view);
	dress('desc', htmlspecialchars($context->getProperty('blog.description')), $view);
	if ($context->getProperty('blog.logo') == null)
		dress('image', $context->getProperty('service.path')."/attach/$blogid/".$context->getProperty('blog.logo'), $view);
	else
		dress('image', $context->getProperty('service.path')."/resources/image/spacer.gif", $view);
	dress('blog_link', $context->getProperty('uri.blog')."/", $view);
	dress('keylog_link', $context->getProperty('uri.blog')."/keylog", $view);
	dress('localog_link', $context->getProperty('uri.blog')."/location", $view);
	dress('taglog_link', $context->getProperty('uri.blog')."/tag", $view);
	dress('guestbook_link', $context->getProperty('uri.blog')."/guestbook", $view);
	
	list($view, $searchView) = Skin::cutSkinTag($view, 'search');
	dress('search_name', 'search', $searchView);
	dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
	dress('search_onclick_submit', 'searchBlog()', $searchView);
	dress('search', '<form id="TTSearchForm" action="'.$context->getProperty('uri.blog').'/search/" method="get" onsubmit="return searchBlog()">'.$searchView.'</form>', $view);
	
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
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', Utils_Unicode::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
			dress('notice_rep_link', $context->getProperty('uri.blog')."/notice/{$notice['id']}", $itemView);
			$itemsView .= $itemView;
		}
		dress('rct_notice_rep', $itemsView, $noticeView);
		dress('rct_notice', $noticeView, $view);
	}

	list($view, $authorList) = Skin::cutSkinTag($view, 'author_rep');
	dress('author_rep', getAuthorListView($pd_authorList, $authorList), $view);	
	list($view, $recentEntry) = Skin::cutSkinTag($view, 'rctps_rep');	
	dress('rctps_rep', revertTempTags(getRecentEntriesView($pd_recentEntry, $recentEntry)), $view);
	list($view, $recentComments) = Skin::cutSkinTag($view, 'rctrp_rep');	
	dress('rctrp_rep', revertTempTags(getRecentCommentsView($pd_recentComment, $recentComments)), $view);
	list($view, $recentTrackback) = Skin::cutSkinTag($view, 'rcttb_rep');	
	dress('rcttb_rep', getRecentTrackbacksView($pd_recentTrackback, $recentTrackback), $view);
	list($view, $s_link_rep) = Skin::cutSkinTag($view, 'link_rep');	
	dress('link_rep', getLinksView($pd_link, $s_link_rep), $view);
	dress('rss_url', $context->getProperty('uri.blog')."/rss", $view);
	dress('owner_url', $context->getProperty('uri.blog')."/owner", $view);
	dress('textcube_name', TEXTCUBE_NAME, $view);
	dress('textcube_version', TEXTCUBE_VERSION, $view);
	
	$tagSearches = array ( '@<a @i' , '@</a *>@i', '@ id *= *".*"@isU', '@ onkey(down|up|press) *="@i', '@ on(click|load|unload) *="@i', '@<input +@i'      , '@<script.*</script *>@siU', '@<form @siU', '@</form>@siU' );
	$tagReplaces = array ( '<span ' , '</span>'  , ''                , ' onnothing="'                , ' onnothing="'                 , '<input disabled="disabled" ' , ''                        , '<div ' , '</div>');
	
	$view = preg_replace($tagSearches, $tagReplaces, $view);
	
	return correctSidebarImage($view);
}

?>

<?php
$defaultModeSelected = false;
$safeModeSelected = false;
$tagModeSelected = false;
$initModeSelected = false;

$viewMode = '';
$viewMode2 = '';
$viewMode3 = '';
$previewMode = '';

if ((!isset($_REQUEST['safe'])) && (!isset($_REQUEST['tag']))) {
	$safeModeSelected = true;
	$viewMode = '&amp;viewMode=safe';
	$viewMode2 = '?viewMode=safe';
	$viewMode3 = '&viewMode=safe';
	$previewMode = '&safe';	
	$_REQUEST['safe'] = true;
	//$defaultModeSelected = true;
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

$sidebarPluginArray = array();
for ($i=0; $i<count($sidebarMappings); $i++) {
	$sidebarPluginArray[$sidebarMappings[$i]['plugin'] . '/' . $sidebarMappings[$i]['handler']]=
		array( 
			'type' => 3, 'id' => $sidebarMappings[$i]['handler'],
			'plugin' => $sidebarMappings[$i]['plugin'], 'title' =>$sidebarMappings[$i]['title'], 
			'display' => $sidebarMappings[$i]['display'],
			'identifier' => implode(':', array(3,$sidebarMappings[$i]['plugin'],$sidebarMappings[$i]['handler'])),
			'parameters' => $sidebarMappings[$i]['parameters']
		);
}

$skin = new Skin($skinSetting['skin']);
$usedSidebarBasicModule = array();
$sidebarCount = count($skin->sidebarBasicModules);

getBlogContentForSideBar();
?>
						<form id="part-sidebar-order" class="part" method="post" action="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/register'.$viewMode2);?>">
							<h2 class="caption"><span class="main-text"><?php echo _t('사이드바 기능을 관리합니다');?></span></h2>
<?php
require ROOT . '/interface/common/owner/skinTab.php';
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('블로그의 사이드바 구성을 변경할 수 있습니다. 사이드바는 블로그 화면에서 양 옆이나 위아래에 표시되는 메뉴가 있는 부분을 말합니다. 사이드바에 새로운 요소를 추가/삭제할 수 있으며 자유로운 위치 이동을 할 수 있습니다.');?></p>
							</div>
							
							<dl id="direct-link-line" class="line">
								<dt><?php echo _t('플러그인 설정');?></dt>
								<dd><a class="button" href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/plugin');?>"><?php echo _t('위젯 켜고 끄기');?></a></dd>
							</dl>
							
<?php
if ($sidebarCount == 0) {
?>
							<ul id="sidebar-tabs-box" class="tabs-box">
								<li class="selected"><a id="default-mode-button" class="button" href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar');?>" title="<?php echo _t('실제 출력되는 내용을 직접 볼 수 있는 기본 모드입니다.');?>"><?php echo _t('기본모드');?></a></li>
							</ul>
							
							<div id="sidebar-box-disabled" class="data-inbox">
								<p><?php echo _t('사용중인 스킨이 사이드바 기능을 지원하지 않습니다.');?></p>
							</div>
						</form>
<?php
	require ROOT . '/interface/common/owner/footer.php';
	exit;
}

// 사용중인 사이드바 모듈 리스트 출력.
$bFirstRadio = true;
$sidebarConfig = getSidebarModuleOrderData($sidebarCount);

if (is_null($sidebarConfig)) {
	for ($i=0; $i<$sidebarCount; $i++) {
		$sidebarConfig[$i] = array();
	}
}
?>
							<ul id="sidebar-tabs-box" class="tabs-box second-tab">
								<li class="default-mode-button<?php echo $defaultModeSelected ? ' selected' : NULL;?>"><a href="<?php echo $context->getProperty('uri.blog');?>/owner/skin/sidebar" title="<?php echo _t('실제 출력되는 내용을 직접 볼 수 있는 기본 모드입니다.');?>"><?php echo _t('기본모드');?></a></li>
								<li class="safe-mode-button<?php echo $safeModeSelected ? ' selected' : NULL;?>"><a  href="<?php echo $context->getProperty('uri.blog');?>/owner/skin/sidebar?safe" title="<?php echo _t('태그를 사용하지 않아 레이아웃이 깨질 위험이 없는 모드입니다.');?>"><?php echo _t('안전모드');?></a></li>
								<li class="tag-mode-button<?php echo $tagModeSelected ? ' selected' : NULL;?>"><a href="<?php echo $context->getProperty('uri.blog');?>/owner/skin/sidebar?tag" title="<?php echo _t('실제 블로그 표지에 사용되는 태그를 직접사용하는 모드입니다.');?>"><?php echo _t('태그모드');?></a></li>
								<li class="init-button<?php echo $initModeSelected ? ' selected' : NULL;?>"><a href="sidebar/initialize<?php echo $viewMode2;?>" onclick="if (!confirm('<?php echo _t('정말 사이드바 기능을 초기화하시겠습니까?');?>')) return false;" title="<?php echo _t('사이드바의 기능을 스킨 설정 상태로 초기화합니다.');?>"><span class="text"><?php echo _t('초기화');?></span></a></li>
							</ul>
							
							<div id="sidebar-box" class="data-inbox">
								<table border="0">
									<tr>
<?php

for ($i=0; $i<$sidebarCount; $i++) {
	if (array_key_exists($i, $sidebarConfig))
		$orderConfig = $sidebarConfig[$i];
	else
		$orderConfig = array();
?>
										<td class="section">
											<h3><input type="radio" id="sidebar-<?php echo $i + 1;?>" class="radio" name="sidebarNumber" value="<?php echo $i;?>"<?php echo $bFirstRadio ? ' checked="checked"' : NULL;?> /><label for="sidebar-<?php echo $i + 1;?>"><?php echo $skin->sidebarName[$i];?></label></h3>
											
											<ul id="sidebar-ul-<?php echo $i;?>" class="sidebar">
<?php
	for ($j=0; $j<count($orderConfig); $j++) {
		if ($orderConfig[$j]['type'] == 1) { // skin text
			$skini = $orderConfig[$j]['id'];
			$skinj = $orderConfig[$j]['parameters'];
?>
												<li class="sidebar-module sidebar-basic-module" id="sidebar-element-<?php echo "{$i}-{$j}";?>">
													<h4 class="module-title"><?php echo $skin->sidebarBasicModules[$skini][$skinj]['title'];?></h4>
													<div class="button-box">
<?php
			if ($j == 0) {
?>
														<img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
			} else {
?>
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/order/?sidebarNumber='.$i.'&amp;targetSidebarNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j - 1).$viewMode);?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
			}
				
			if ($j == count($orderConfig) - 1) {
?>
														<img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
			} else {
?>
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/order/?sidebarNumber='.$i.'&amp;targetSidebarNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j + 2).$viewMode);?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
			}
?>
												
														<a href="<?php echo $context->getProperty('uri.blog'); ?>/owner/skin/sidebar/delete/?sidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?><?php echo $viewMode;?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_delete_module.gif" border="0" alt="<?php echo _t('삭제');?>" /></a>
													</div>
													<div class="module-content"><?php echo pretty_dress($skin->sidebarBasicModules[$skini][$skinj]['body']);?></div>
												</li>
<?php
			array_push($usedSidebarBasicModule, $orderConfig[$j]['id']);
		} else if ($orderConfig[$j]['type'] == 2) { // default handler
			// TODO : implement it!
		} else if ($orderConfig[$j]['type'] == 3) { // plugin
		
			$plugin = $orderConfig[$j]['id']['plugin'];
			$handler = $orderConfig[$j]['id']['handler'];
			$sidbarPluginIndex = $plugin . '/' . $handler;
			
			$invalidPlugin = false;
			if (!array_key_exists($sidbarPluginIndex,  $sidebarPluginArray)) {
				// invalid or missed plug-in
				$sidebarPluginArray[$sidbarPluginIndex] = array();
				$sidebarPluginArray[$sidbarPluginIndex]['title'] = $plugin;
				$sidebarPluginArray[$sidbarPluginIndex]['handler'] = $handler;
				$sidebarPluginArray[$sidbarPluginIndex]['display'] = $plugin;
				$sidebarPluginArray[$sidbarPluginIndex]['parameters'] = array();
				$invalidPlugin = true;
			} else {
				include_once (ROOT . "/plugins/{$plugin}/index.php");
			}
			//if (function_exists($handler))
			{
				if( !empty( $configMappings[$plugin]['config'] ) ) {	
					$configVal = getCurrentSetting($plugin);
					$context->setProperty('plugin.config',Setting::fetchConfigVal($configVal));
				} else {
					$configVal ='';
					$context->setProperty('plugin.config',array());
				}
?>
												<li class="sidebar-module sidebar-plugin-module" id="sidebar-element-<?php echo "{$i}-{$j}";?>">
													<h4 class="module-title"><?php echo $sidebarPluginArray[$sidbarPluginIndex]['display'], '::', $sidebarPluginArray[$sidbarPluginIndex]['title'];?></h4>
													<div class="button-box">
<?php
				if ($j == 0) {
?>
														<img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
				} else {
?>
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/order/?sidebarNumber='.$i.'&amp;targetSidebarNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j - 1).$viewMode);?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
				}
				
				if ($j == count($orderConfig) - 1) {
?>
														<img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
				} else {
?>
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/order/?sidebarNumber='.$i.'&amp;targetSidebarNumber='.$i.'&amp;modulePos='.$j.'&amp;targetPos='.($j + 2).$viewMode);?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
				}
?>
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/delete/?sidebarNumber='.$i.'&amp;modulePos='.$j.$viewMode);?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/image/img_delete_module.gif" border="0" alt="<?php echo _t('삭제');?>" /></a>
														<!-- TODO : sidebar plugin settting -->									
													</div>
<?php 
				$pluginparameters = $sidebarPluginArray[$sidbarPluginIndex]['parameters'];
				if (count($pluginparameters) > 0) {
?>
													<div class="edit-button-box">
														<a href="<?php echo parseURL($context->getProperty('uri.blog').'/owner/skin/sidebar/edit?sidebarNumber='.$i.'&amp;modulePos='.$j.$viewMode);?>"><?php echo _t('편집');?></a>
													</div>
<?php 
				}
?>
													<div class="module-content">
<?php
				if (($invalidPlugin == false) && function_exists($handler)) {
					$pluginURL = "{$context->getProperty('service.path')}/plugins/{$orderConfig[$j]['id']['plugin']}";
					echo pretty_dress(call_user_func($handler, $orderConfig[$j]['parameters']));
				}
?>
													</div>
												</li>
<?php
			}
			if ($invalidPlugin == true) 
				 unset($sidebarPluginArray[$sidbarPluginIndex]);
				
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
							
							<div id="sidebar-element-box" class="data-inbox">
								<h3><?php echo _t('추가 가능한 모듈');?></h3>
								
								<fieldset id="sidebar-basic-module-box" class="section">
									<legend><?php echo _t('추가 가능한 모듈(스킨 기본)');?></legend>
									
									<ul class="module-list" id="sidebar-basic-module-box-ul">
<?php
// 사용중이지 않은 스킨 내장형 사이드바 모듈 리스트 출력.
$sortedArray = array();
for ($i=0; $i<$sidebarCount; $i++) {
	$moduleCountInSidebar = count($skin->sidebarBasicModules[$i]);
	for ($j=0; $j<$moduleCountInSidebar; $j++) {
		array_push($sortedArray, 
			array('title' => $skin->sidebarBasicModules[$i][$j]['title'], 
				'body' => $skin->sidebarBasicModules[$i][$j]['body'],
				'identifier' => implode(':', array(1, $i, $j))
			)
		);
	}
}

for ($i=0;$i<$sidebarCount; $i++) {
	$orderConfig = array_key_exists($i,$sidebarConfig) ? $sidebarConfig[$i] : array();
	for ($j=0; $j<count($orderConfig) ; $j++) {
		if ($orderConfig[$j]['type'] == 1) {
			$identifier = implode(':', array(1, $orderConfig[$j]['id'], $orderConfig[$j]['parameters']));
			$pos = 0;
			while ($pos<count($sortedArray)) {
				if ($sortedArray[$pos]['identifier'] == $identifier) break;
				$pos++;
			}
			if ($pos<count($sortedArray)) {
				array_splice($sortedArray, $pos, 1);
			}
		}
	}
}

foreach ($sortedArray as $nowKey) {
?>
										<li class="sidebar-module" id="<?php echo "add-sidebar-element-{$nowKey['identifier']}";?>">
											<h4 class="module-title"><input type="radio" id="module<?php echo $nowKey['identifier'];?>" class="radio" name="moduleId" value="<?php echo $nowKey['identifier'];?>" /><label for="module<?php echo $nowKey['identifier'];?>"><?php echo $nowKey['title'];?></label></h4>
											
											<div class="module-content"><?php echo pretty_dress($nowKey['body']);?></div>
										</li>
<?php
}
?>
									</ul>
								</fieldset>
								
								<fieldset id="sidebar-plugin-module-box" class="section">
									<legend><?php echo _t('추가 가능한 플러그인');?></legend>
									
									<ul class="module-list">
<?php
// 사이드바 플러그인 모듈을 리스트에 포함시킨다.
foreach ($sidebarPluginArray as $nowKey) {
?>
										<li class="sidebar-module" id="<?php echo "add-sidebar-module-{$nowKey['identifier']}";?>">
											<h4 class="module-title"><input type="radio" id="module<?php echo $nowKey['identifier'];?>" class="radio" name="moduleId" value="<?php echo $nowKey['identifier'];?>" /><label for="module<?php echo $nowKey['identifier'];?>"><?php echo $nowKey['display'], '::' , $nowKey['title'];?></label></h4>
																			
											<div class="edit-button-box">
											</div>
											<div class="module-content">
<?php
	$pluginURL = "{$context->getProperty('service.path')}/plugins/{$nowKey['plugin']}";
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
									<input type="submit" class="input-button" value="<?php echo _t('모듈 추가');?>" title="<?php echo _t('사이드바에 선택된 모듈의 기능을 추가합니다.');?>"/>									
								</div>
							</div>
							
							<p id="sidebar-description" class="pseudo-clear">
								<em>* <?php echo _t('여러개의 사이드바 사용시 사이드바의 위치는 스킨의 구조에 따라 달라집니다.');?></em>
							</p>
						</form>
						
						<script src="<?php echo $context->getProperty('service.path');?>/resources/script/dojo/dojo.js" type="text/javascript"></script>
						<script src="<?php echo $context->getProperty('service.path');?>/resources/script/sidebar.min.js" type="text/javascript"></script>
						<script type="text/javascript">
							//<![CDATA[
								var decorateDragPanelString_deleteTitle = "<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>";
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
		for ($i=0; $i<$sidebarCount; $i++) {
			echo "pNode = document.getElementById('sidebar-ul-{$i}').firstChild;";
?>
									pos = 0;
									while (pNode != null) {
										if ((pNode.nodeType != 3/* TEXT_NODE */) && (pNode.className.indexOf("sidebar-module") != -1)) { 
											pNode.modulePos = pos++;
											
											var p2Node = pNode.firstChild;
											while (p2Node != null) {
												if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'a')) break;
												p2Node = p2Node.nextSibling;
											}
											if (p2Node != null) {
												p2Node.href = blogURL + "/owner/skin/sidebar/delete/?sidebarNumber=" + pNode.sidebarNumber + "&modulePos=" + pNode.modulePos + viewMode;
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
													p2Node.innerHTML = '<a onclick="editSidebarPlugin('+ pNode.sidebarNumber + ',' + pNode.modulePos + '); return false" ><?php echo _t('편집');?><\/a>';
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
									
									new DropDeletePanel(document.getElementById('sidebar-basic-module-box-ul'), ["sidebar"]);

<?php
for ($i=0; $i<$sidebarCount; $i++) {
	echo "document.getElementById('sidebar-ul-{$i}').sidebar = {$i};".CRLF;
	echo "new DropPanel(document.getElementById('sidebar-ul-{$i}'), [\"sidebar\"]);".CRLF;
	
	$orderConfig = array_key_exists($i, $sidebarConfig) ? $sidebarConfig[$i] :  array();
	for ($j=0; $j<count($orderConfig); $j++) {
		echo "document.getElementById('sidebar-element-{$i}-{$j}').sidebarNumber = {$i};".CRLF;
		echo "document.getElementById('sidebar-element-{$i}-{$j}').modulePos = {$j};".CRLF;
		echo "document.getElementById('sidebar-element-{$i}-{$j}').ajaxtype = 'reorder';".CRLF;
		echo "document.getElementById('sidebar-element-{$i}-{$j}').hasPropertyEdit = false;".CRLF;
		
		if ($orderConfig[$j]['type'] == 3) {
			echo "document.getElementById('sidebar-element-{$i}-{$j}').moduleCategory = 'plugin';".CRLF;
			echo "document.getElementById('sidebar-element-{$i}-{$j}').hasPropertyEdit = ";
			$plugin = $orderConfig[$j]['id']['plugin'];
			$handler = $orderConfig[$j]['id']['handler'];
			$sidbarPluginIndex = $plugin . '/' . $handler;
			
			echo (array_key_exists($sidbarPluginIndex, $sidebarPluginArray) 
				&& (count($sidebarPluginArray[$sidbarPluginIndex]['parameters']) > 0)) ? 'true' : 'false';
			echo ";".CRLF;
		}
		
		echo "new DragPanel(document.getElementById('sidebar-element-{$i}-{$j}'), [\"sidebar\"]);".CRLF;
	}
}

foreach ($sortedArray as $nowKey) {
	echo "document.getElementById('add-sidebar-element-{$nowKey['identifier']}').identifier = '{$nowKey['identifier']}';".CRLF;
	echo "document.getElementById('add-sidebar-element-{$nowKey['identifier']}').ajaxtype = 'register';".CRLF;
	echo "document.getElementById('add-sidebar-element-{$nowKey['identifier']}').moduleCategory = 'sidebar_element';".CRLF;
	echo "document.getElementById('add-sidebar-element-{$nowKey['identifier']}').hasPropertyEdit = false;".CRLF;
	echo "new DragPanelAdd(document.getElementById('add-sidebar-element-{$nowKey['identifier']}'), [\"sidebar\"]);".CRLF;
}
foreach ($sidebarPluginArray as $nowKey) {
	echo "document.getElementById('add-sidebar-module-{$nowKey['identifier']}').identifier = '{$nowKey['identifier']}';".CRLF;
	echo "document.getElementById('add-sidebar-module-{$nowKey['identifier']}').ajaxtype = 'register';".CRLF;
	echo "document.getElementById('add-sidebar-module-{$nowKey['identifier']}').moduleCategory = 'plugin';".CRLF;
	echo "document.getElementById('add-sidebar-module-{$nowKey['identifier']}').hasPropertyEdit = ";
	echo count($nowKey['parameters']) > 0 ? 'true' : 'false';
	echo ";".CRLF;
	echo "new DragPanelAdd(document.getElementById('add-sidebar-module-{$nowKey['identifier']}'), [\"sidebar\"]);".CRLF;
}
?>
									reordering();
								}
								dojo.addOnLoad(initPages);
							//]]>							
						</script>						
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
