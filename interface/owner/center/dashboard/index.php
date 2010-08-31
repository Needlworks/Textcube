<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

requireLibrary('blog.skin');

requireModel('reader.common');
requireModel('blog.comment');
requireModel('blog.response.remote');
requireModel('blog.entry');
requireModel('blog.trash');
requireModel('common.setting');

$blogMenu['topMenu'] = 'center';
$blogMenu['contentMenu'] = 'dashboard';

$ctx = Model_Context::getInstance();

// Move spams to trash.
if (!isset($_REQUEST['ajaxcall'])) {
	require ROOT . '/interface/common/owner/header.php';
	
	trashVan();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['pos'])) $_GET['pos'] = $_POST['pos'];
	if (isset($_POST['rel'])) $_GET['rel'] = $_POST['rel'];
}
// Dashboard setting
if (($_SERVER['REQUEST_METHOD'] == 'POST') && 
	(!isset($_REQUEST['edit'])) &&
	(empty($_GET['useTTdashboard']))) {	// Turn off case.
	$textcubeDashboard = Setting::getBlogSettingGlobal("textcubeDashboard",1);
	if ($textcubeDashboard == 0) {
		Setting::setBlogSettingGlobal("textcubeDashboard", 1);
		$textcubeDashboard = 1;
	} else {
		Setting::setBlogSettingGlobal("textcubeDashboard", 0);
		$textcubeDashboard = 0;
	}
} else if (($_SERVER['REQUEST_METHOD'] == 'POST') &&
	!isset($_REQUEST['edit'])) {	// Turn on case.
	Setting::setBlogSettingGlobal("textcubeDashboard", 1);
	$textcubeDashboard = 1;
} else {	// Just read it.
	$textcubeDashboard = Setting::getBlogSettingGlobal("textcubeDashboard",1);
}

// Layout setting
$layout = Setting::getBlogSettingGlobal('centerLayout', '');
$newlayout = array();
$addedlayout = array();
$oldcenterlayout = array();
$defaultPanelShown = false;
//$textcubeDashboard = Setting::getBlogSettingGlobal("textcubeDashboard",1);

if (count($centerMappings) == 0) {		// No center widgets
	$layout = '';
	Setting::setBlogSettingGlobal('centerLayout', '');
	unset($_GET['pos']);
	unset($_GET['rel']);
}

function findPlugin($item, $arrays)
{
	foreach($arrays as $key => $data) {
		if (($item['plugin'] === $data['plugin']) && ($item['handler'] === $data['handler']))
			return $key;
	}
	return false;
}

$modified = false;

if ((!empty($layout)) && (($oldcenterlayout = unserialize($layout)) != false) ) {	
	$seperatorCount = 0;
	foreach($oldcenterlayout as $item) {
		if ($item['plugin'] == 'defaultDashboardWidget') {
			if($textcubeDashboard == 0) {
				$modified = true;
			} else {
				array_push($newlayout, $item);
				$defaultPanelShown = true;
			}
		} else if ($item['plugin'] == 'TextcubeSeparator') {
			array_push($newlayout, $item);
			$seperatorCount++;
		} else if (($pos = findPlugin($item, $centerMappings)) !== false) {
			array_push($newlayout, $centerMappings[$pos]);
			unset($centerMappings[$pos]);
		} else {
			array_push($addedlayout, $item);
		}
	}
	while ($seperatorCount < 2) {
		array_push($centerMappings, array('plugin' => 'TextcubeSeparator'));
		$seperatorCount++;
	}
	
	$newlayout = array_merge($newlayout, $centerMappings);
} else if (count($centerMappings) > 0) { // TODO 1/3
	unset($_GET['pos']);
	unset($_GET['rel']);
	$middlepos = (count($centerMappings) + 2)/3;
	array_splice($centerMappings, $middlepos , 0, array(array('plugin' => 'TextcubeSeparator')));
	array_splice($centerMappings, $middlepos * 2, 0, array(array('plugin' => 'TextcubeSeparator')));
	$newlayout = $addedlayout = $centerMappings;
}

if (count($newlayout) == 0) { // If no widget is activated, add default widget & separators
	array_push($newlayout, array('plugin' => 'TextcubeSeparator'));
	array_push($newlayout, array('plugin' => 'TextcubeSeparator'));
	if($textcubeDashboard == 1) //array_push($newlayout, array('plugin' => 'defaultDashboardWidget'));
		array_splice($newlayout, 0, 0, array(array('plugin' => 'defaultDashboardWidget')));
	$modified = true;
}

if ((isset($_GET['pos'])) && (($_GET['pos'] < 0) || ($_GET['pos']) >= count($newlayout))) {
	unset($_GET['pos']);
	unset($_GET['rel']);
}

if (isset($_GET['pos']) && is_numeric($_GET['pos'])) {
	if (isset($_GET['rel']) && is_numeric($_GET['rel']) && (is_numeric($_GET['rel']))) {
		$newpos = $_GET['pos'] + $_GET['rel'];
		if ($newpos < 0) $newpos = 0;
		if ($newpos >= count($newlayout)) $newpos = count($newlayout) - 1;
		$item = array_splice($newlayout, $_GET['pos'], 1);
		array_splice($newlayout, $newpos, 0, $item);
		$modified = true;
	}
}
// Checking whether the default widget is activated or not.
$defaultWidgetPosition = 0;
//$count = 0;
if(($textcubeDashboard == 1) && ($defaultPanelShown == false)) {	// No default widget is activated during process.
	foreach($newlayout as $widget) {	// Double-check.
		if($widget['plugin'] == 'defaultDashboardWidget') $defaultPanelShown = true;
//		else if($widget['plugin'] == 'TextcubeSeparator') $defaultWidgetPosition = $count + 1;
//		$count++;
	}

	if($defaultPanelShown == false) {
		array_splice($newlayout, 0, 0, array(array('plugin' => 'defaultDashboardWidget')));
/*	if($seperatorCount < 2) {
			for($i = 0; $i < (2-$seperatorCount); $i++) {
				array_push($newlayout, array('plugin' => 'TextcubeSeparator'));
			}
			array_push($newlayout, array('plugin' => 'defaultDashboardWidget'));
		} else {
			array_splice($newlayout, $defaultWidgetPosition, 0, array(array('plugin' => 'defaultDashboardWidget')));
		}*/
		$modified = true;
	}
}

if ((count($centerMappings) > 0) || (count($addedlayout) > 0) || ($modified == true)) {
	$saveLayout = array_values($newlayout);
	for ($i = 0; $i < count($saveLayout); $i++)
	{
		unset($saveLayout[$i]['title']);
	}
	Setting::setBlogSettingGlobal('centerLayout', serialize($saveLayout));
}

unset($addedlayout);
unset($layout);
unset($oldcenterlayout);

if (isset($_REQUEST['ajaxcall'])) {
	Respond::ResultPage(0);
	exit;
}

$editClass = NULL;
if (isset($_REQUEST['edit'])) {
	$editClass = "-edit";
?>
<script src="<?php echo $ctx->getProperty('service.path');?>/resources/script/jquery/jquery.ui.essentials.1.6.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
<?php echo "\tvar editMode = ".isset($_REQUEST['edit']).";\n";?>
//]]>
</script>
<script src="<?php echo $ctx->getProperty('service.path');?>/resources/script/dashboard.js" type="text/javascript"></script>
<?php
}
?>
						<script type="text/javascript">
							//<![CDATA[
								function changeList() {
									document.getElementById("form-quilt").submit();
								}
<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
								
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $ctx->getProperty('uri.blog');?>/checkup";
								}
<?php
} else {
	$current_version = trim(file_get_contents(ROOT . '/cache/CHECKUP'));
	if ($current_version != TEXTCUBE_VERSION) {
?>
								
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $ctx->getProperty('uri.blog');?>/checkup";
								}
<?php
	}
}
if(Acl::check("group.administrators")) {
?>
								function cleanupCache() {
									var request = new HTTPRequest("GET", "<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard/cleanup/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('캐시를 정리하였습니다.');?>", "center", "bottom");
									}
									request.onError= function () {
										PM.removeRequest(this);
									}
									PM.addRequest(request, "<?php echo _t('캐시를 정리하고 있습니다.');?>");
									request.send();
								}
<?php
	if(Acl::check("group.creators")) {
?>
								var dialog = null;
								
								function showDialog($name) {
									if (dialog)
										dialog.style.display = "none";
									dialog = document.getElementById($name + "Dialog");
									PM.showPanel(dialog);
								}
								
								function hideDialog() {
									if (dialog) {
										dialog.style.display = "none";
										dialog = null;
									}
								}
								
								function optimizeData() {
									document.getElementById("optimizingIndicator").style.width = "0%";
									document.getElementById("optimizingDataDialogTitle").innerHTML = '<?php echo _t('데이터베이스를 최적화하고 있습니다. 잠시만 기다려 주십시오.');?>';
									PM.showPanel("optimizingDataDialog");
									document.getElementById("dataOptimizer").submit();
								}								
<?php
	}
}
?>
							//]]>
						</script>
						<form id="form-quilt" method="post" action="<?php echo parseURL($ctx->getProperty('uri.blog').'/owner/center/dashboard');?>">
							<div id="part-center-quilt<?php echo $editClass;?>" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('조각보를 봅니다');?></span></h2>
								
<?php
if (!isset($_REQUEST['edit']) && Acl::check('group.owners')) {
?>
								<dl id="independent-notice-line" class="line">
									<dt><?php echo _t('정보 패널 설정');?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="useTTdashboard" name="useTTdashboard" value="on" onclick="changeList();return false;"<?php echo $textcubeDashboard == 1 ? ' checked="checked"' : NULL;?> />
										<label for="useTTdashboard"><?php echo _t('블로그 정보를 보여주는 패널을 사용합니다');?></label>
									</dd>
								</dl>
<?php
}
$boardbarNumber = 0;
$positionCounter = 0;
$secondposition = array(0, 0);

if(Acl::check('group.owners')) {
	if(!isset($_REQUEST['edit'])) {
?>
								<div id="widget-button-top" class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('편집');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit'; return false;" />
									<input type="button" class="input-button" value="<?php echo _t('위젯 켜고 끄기');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/plugin?visibility=center'; return false;" />
								</div>
<?php
	} else {
?>
								<div class="button-box">
									<input type="button" class="input-button" value="<?php echo _t('돌아가기');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard'; return false;" />
								</div>
<?php
	}
} else {
?>
								<div class="button-box">
								</div>
<?php
}
?>
								<div id="widget-container-0" class="panel widget-container">
<?php
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] == 'TextcubeSeparator') {
?>

								</div>
								<div id="widget-container-<?php echo $boardbarNumber + 1;?>" class="panel widget-container">
<?php
		$secondposition[$boardbarNumber] = $positionCounter;
		$boardbarNumber++;
	} else if ($mapping['plugin'] == 'defaultDashboardWidget') {
		getDefaultCenterPanel($mapping);
	} else {
?>
									<div id="<?php echo $mapping['plugin'];?>" class="section">
										<h3 class="caption">
											<span><?php echo $mapping['title'];?>
<?php
		if (isset($_REQUEST['edit'])) {
?>
				
											<a class="widget-reorder-up" href="<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter; ?>&amp;rel=-1&edit"><?php echo _t("위로");?></a>
											<a class="widget-reorder-down" href="<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter;?>&amp;rel=1&edit"><?php echo _t("아래로");?></a>
<?php
		}
?>
											</span>
										</h3>
										<?php
		if (!isset($_REQUEST['edit'])) echo handleCenters($mapping);
?>
									</div>
<?php
	}
	$positionCounter++;
}
?>
								</div>
<?php
if ($boardbarNumber < 1) {
?>

								<div id="dojo_boardbar1" class="panel widget-container"></div>
<?php
	$secondposition[$boardbarNumber] = $positionCounter;
	$boardbarNumber++;
	$positionCounter++;
}
if ($boardbarNumber < 2) {
?>
								<div id="dojo_boardbar2" class="panel widget-container"></div>
<?php
	$secondposition[$boardbarNumber] = $positionCounter;
	$boardbarNumber++;
	$positionCounter++;
}

if(Acl::check('group.owners')) {
	if(!isset($_REQUEST['edit'])) {
?>
								<div id="widget-button-bottom" class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('편집');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit'; return false;" />
									<input type="button" class="input-button" value="<?php echo _t('위젯 켜고 끄기');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/plugin?visibility=center'; return false;" />
								</div>
<?php
	} else {
?>
								<div class="button-box">
									<input type="button" class="input-button" value="<?php echo _t('돌아가기');?>" onclick="window.location.href='<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard'; return false;" />
								</div>
<?php
	}
} else {
?>
								<div class="button-box">
								</div>
<?php
}
?>
							</div>
						</form>
<?php
if(Acl::check("group.creators") && !isset($_REQUEST['edit'])) {
?>
						<form id="dataOptimizer" method="get" action="<?php echo $ctx->getProperty('uri.blog');?>/owner/data/optimize" target="blackhole"></form>
						<div id="optimizingDialogBox" class="part">
							<div id="optimizingDataDialog" class="system-dialog" style="position: absolute; display: none; z-index: 500;">
								<h4 id="optimizingDataDialogTitle"></h4>
								<div class="message-sub">
									<span id="optimizingText"></span>
									<span id="optimizingTextSub"></span>
								</div>
								<div id="optimizingIndicator" class="progressBar" style="width: 0%; height: 18px; margin-top: 5px; background-color: #66DDFF;"></div>
							</div>
						</div>
						<iframe id="blackhole" name="blackhole" style="display: none;"></iframe>
<?php
}


//************ Default Center Widget module.
function getDefaultCenterPanel($mapping) {
	$ctx = Model_Context::getInstance();
	$blogid = $ctx->getProperty('blog.id');
?>
									<div id="<?php echo $mapping['plugin'];?>" class="section">
									<h3 class="caption<?php echo isset($_REQUEST['edit']) ? ' visible' : ' invisible';?>">
											<span><?php echo _t('알림판');?>
<?php
	if (isset($_REQUEST['edit'])) {
?>
											<a id="<?php echo $mapping['plugin'];?>dojoup" href="<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter; ?>&amp;rel=-1&edit"><?php echo _t("위로");?></a>
											<a id="<?php echo $mapping['plugin'];?>dojodown" href="<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter;?>&amp;rel=1&edit"><?php echo _t("아래로");?></a>
<?php
	}
?>
											</span>
										</h3>
<?php
	if (isset($_REQUEST['edit'])) {
?>
									</div>
<?php
		return true;
	} else {
		// Get default data
		$stats = getStatistics($blogid);
		$latestEntryId = Setting::getBlogSettingGlobal('LatestEditedEntry_user'.getUserId(),0);

		$comments = getRecentComments($blogid,10);
		$guestbooks = getRecentGuestbook($blogid,10);
		list($commentNotifies,$paging) = getCommentsNotifiedWithPagingForOwner($blogid,0,null,null,null,1,10);
		$trackbacks = getRecentTrackbacks($blogid,10);
		$recents = array();	// title, date, link, category
		foreach($comments as $comment) {
			array_push($recents, array(
			'title'   =>$comment['comment'],
			'date'    =>$comment['written'],
			'link'    => $ctx->getProperty('uri.blog')."/".$comment['entry']."#comment".$comment['id'],
			'category'=>'comment'));
		}
		foreach($commentNotifies as $comment) {
			array_push($recents, array(
			'title'   =>$comment['comment'],
			'date'    =>$comment['written'],
			'link'    => $ctx->getProperty('uri.blog')."/owner/communication/notify",
			'category'=>'commentNotify'));
		}
		foreach($guestbooks as $guestbook) {
			array_push($recents, array(
			'title'   =>$guestbook['comment'],
			'date'    =>$guestbook['written'],
			'link'    => $ctx->getProperty('uri.blog')."/guestbook/".$guestbook['id']."#guestbook".$guestbook['id'],
			'category'=>'guestbook'));
		}
		foreach($trackbacks as $trackback) {
			array_push($recents, array(
			'title'   =>$trackback['subject'],
			'date'    =>$trackback['written'],
			'link'    => $ctx->getProperty('uri.blog')."/".$trackback['entry']."#trackback".$trackback['id'],
			'category'=>'trackback'));
		}
		$sort_array = array();
		foreach($recents as $uniqid => $row){	// Sorting.
			foreach($row as $key=>$value){
				if (!array_key_exists($key, $sort_array)) {
					$sort_array[$key] = array();
				}
				$sort_array[$key][$uniqid] = $value;
			}
		}
		if (!empty($sort_array)) {
			array_multisort($sort_array['date'],SORT_DESC,$recents);
		}
		$recents = array_slice($recents, 0, 14);
		
?>
										<div id="shortcut-collection">
											<h4 class="caption"><span><?php echo _t('바로가기');?></span></h4>
											
											<ul>
												<li class="newPost"><a class="newPost" href="<?php echo $ctx->getProperty('uri.blog');?>/owner/entry/post"><span><?php echo _t('새 글 쓰기');?></span></a></li>
<?php
		if($latestEntryId !== 0) {
			$latestEntry = getEntry($blogid,$latestEntryId);
			if(!is_null($latestEntry)) {
?>
												<li class="modifyPost"><a href="<?php echo $ctx->getProperty('uri.blog');?>/owner/entry/edit/<?php echo $latestEntry['id'];?>"><?php echo _f('최근글(%1) 수정', htmlspecialchars(UTF8::lessenAsEm($latestEntry['title'],10)));?></a></li>
<?php
			}
		}
		if ($ctx->getProperty('service.reader') == true) {
?>
												<li class="rssReader"><a href="<?php echo $ctx->getProperty('uri.blog');?>/owner/network/reader"><?php echo _t('RSS로 등록한 이웃 글 보기');?></a></li>
<?php
		}
		if(Acl::check("group.administrators")) {
?>
												<li class="deleteCache"><a href="<?php echo $ctx->getProperty('uri.blog');?>/owner/center/dashboard/cleanup" onclick="cleanupCache();return false;"><?php echo _t('캐시 지우기');?></a></li>
<?php
			if(Acl::check("group.creators")) {
?>
												<li class="optimizeStorage"><a href="<?php echo $ctx->getProperty('uri.blog');?>/owner/data" onclick="optimizeData();return false;"><?php echo _t('저장소 최적화');?></a></li>
<?php
			}
		}
?>
												<li class="clear"></li>
											</ul>
										</div>
											
										<div id="total-information">
											<h4 class="caption"><span><?php echo _t('요약');?></span></h4>
												
											<table class="posts-line">
												<caption><?php echo _t('글');?></caption>
												<thead>
													<th>type</th>
													<th>sum</th>
												</thead>
												<tbody>
													<tr>
														<td class="type"><?php echo _t('글');?></td>
														<td class="sum"><?php echo number_format(getEntriesTotalCount($blogid));?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('댓글');?></td>
														<td class="sum"><?php echo number_format(getCommentCount($blogid));?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('방명록');?></td>
														<td class="sum"><?php echo number_format(getGuestbookCount($blogid));?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('걸린 글');?></td>
														<td class="sum"><?php echo number_format(getTrackbackCount($blogid));?></td>
													</tr>
												</tbody>
											</table>
											<table class="visitors-line">
												<caption><?php echo _t('방문자');?></caption>
												<thead>
													<th>type</th>
													<th>sum</th>
												</thead>
												<tbody>
													<tr>
														<td class="type"><?php echo _t('오늘');?></td>
														<td class="sum"><?php echo number_format($stats['today']);?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('어제');?></td>
														<td class="sum"><?php echo number_format($stats['yesterday']);?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('7일 평균');?></td>
														<td class="sum"><?php 
	$weekly = Statistics::getWeeklyStatistics();
	$weeklycount = 0;
	foreach($weekly as $day) $weeklycount += $day['visits'];
	echo number_format($weeklycount/7);
	unset($weekly);
	unset($weeklycount);
	?></td>
													</tr>
													<tr>
														<td class="type"><?php echo _t('총방문자');?></td>
														<td class="sum"><?php echo number_format($stats['total']);?></td>
													</tr>
												</tbody>
											</table>
										</div>

										<div id="myBlogInfo">
											<h4 class="caption"><span><a href="<?php echo $ctx->getProperty('uri.blog').'/owner/communication/comment';?>"><?php echo _t('알림판');?></a></span></h4>
											<table class="recent">
												<caption>asdasd</caption>
												<thead>
													<tr>
														<th scope="col" class="date"><?=_t('날짜')?></th>
														<th scope="col" class="category"><?=_t('종류')?></th>
														<th scope="col"><?=_t('내용')?></th>
													</tr>
												</thead>
												<tbody>
<?php
		foreach ($recents as $item) {
?>
													<tr class="<?php echo $item['category'];?>">
														<td class="date"><?php echo Timestamp::format('%m/%d',$item['date']);?></td>
														<td class="category">
															<?php
			switch($item['category']) {
				case 'trackback' : 
					echo '<a href="'.$ctx->getProperty('uri.blog').'/owner/communication/trackback?status=received">'._t('걸린글').'</a>';break;
				case 'comment' : 
					echo '<a href="'.$ctx->getProperty('uri.blog').'/owner/communication/comment?status=comment">'._t('댓글').'</a>';break;
				case 'commentNotify' : 
					echo '<a href="'.$ctx->getProperty('uri.blog').'/owner/communication/notify">'._t('알리미').'</a>';break;
				case 'guestbook' : 
					echo '<a href="'.$ctx->getProperty('uri.blog').'/owner/communication/comment?status=guestbook">'._t('방명록').'</a>';break;
			}
?>
														</td>
														<td class="title"><a href="<?php echo $item['link'];?>"><?php echo htmlspecialchars(UTF8::lessenAsEm($item['title'],20));?></a></td>
													</tr>
<?php
		}
?>
												</tbody>
											</table>
										</div>
								
<?php
		$noticeURL = TEXTCUBE_NOTICE_URL;
		$noticeURLRSS = $noticeURL.($ctx->getProperty('blog.language') ? $ctx->getProperty('blog.language') : "ko")."/rss";
		$noticeEntries = array();

		if(!is_null(Setting::getServiceSetting('TextcubeNotice'.$ctx->getProperty('blog.language')))) {
			$noticeEntries = unserialize(Setting::getServiceSetting('TextcubeNotice'.$ctx->getProperty('blog.language')));
		} else {
			list($result, $feed, $xml) = getRemoteFeed($noticeURLRSS);
			if ($result == 0) {
				$xmls = new XMLStruct();
				$xmls->setXPathBaseIndex(1);
				$noticeEntries = array();
				if ($xmls->open($xml, $ctx->getProperty('service.encoding'))) {
					if ($xmls->getAttribute('/rss', 'version')) {
						for ($i = 1; $link = $xmls->getValue("/rss/channel/item[$i]/link"); $i++) {
							$item = array('permalink' => rawurldecode($link));
							$item['title'] = $xmls->getValue("/rss/channel/item[$i]/title");
							if ($xmls->getValue("/rss/channel/item[$i]/pubDate"))
								$item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/pubDate"));
							else if ($xmls->getValue("/rss/channel/item[$i]/dc:date"))
								$item['written'] = parseDate($xmls->getValue("/rss/channel/item[$i]/dc:date"));
							else
								$item['written'] = 0;
								array_push($noticeEntries, $item);
						}
					}
				}
				Setting::setServiceSetting('TextcubeNotice'.$ctx->getProperty('blog.language'),serialize($noticeEntries));
			}
		}
?>
										<div id="textcube-notice">
											<h4 class="caption"><span><a href="<?php echo $noticeURL.($ctx->getProperty('blog.language') ? $ctx->getProperty('blog.language') : "ko");?>"><?php echo _t('공지사항');?></a></span></h4>
<?php
		if (count($noticeEntries) > 0) {
			array_splice($noticeEntries, 3, count($noticeEntries) - 3);
?>
											<table>
												<tbody>
<?php
			foreach($noticeEntries as $item) {
?>
													<tr>
														<td class="date"><?php echo Timestamp::format2($item['written']);?></td>
														<td class="title"><a href="<?php echo $item['permalink'];?>" onclick="return openLinkInNewWindow(this);" ><?php echo htmlspecialchars(UTF8::lessenAsEm($item['title'],35));?></a></td>
													</tr>
<?php
			}
?>
												</tbody>
											</table>
									
<?php
		} else {
?>
											<div id="fail-notice">
												<?php echo _t('공지사항을 가져올 수 없습니다. 잠시 후 다시 시도해 주십시오.');?>
											</div>
<?php
		}
?>
										</div>
<?php

	}
?>
									</div>
<?php
}
require ROOT . '/interface/common/owner/footer.php';
?>
