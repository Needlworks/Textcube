<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';

requireLibrary('blog.skin');


requireModel('reader.common');
requireModel('blog.comment');
requireModel('blog.trackback');
requireModel('blog.entry');
requireModel('blog.trash');
requireModel('common.setting');


$blogMenu['topMenu'] = 'center';
$blogMenu['contentMenu'] = 'dashboard';

if (!isset($_REQUEST['ajaxcall'])) {
	require ROOT . '/lib/piece/owner/header.php';
	
	trashVan();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['pos'])) $_GET['pos'] = $_POST['pos'];
	if (isset($_POST['rel'])) $_GET['rel'] = $_POST['rel'];
}

$layout = getBlogSetting('centerLayout', '');
$newlayout = array();
$addedlayout = array();
$oldcenterlayout = array();

if (count($centerMappings) == 0) {
	$layout = '';
	setBlogSetting('centerLayout', '');
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

if ((!empty($layout)) && (($oldcenterlayout = unserialize($layout)) != false) ) {
	
	$seperatorCount = 0;
	
	foreach($oldcenterlayout as $item) {
		if ($item['plugin'] == 'TatterToolsSeperator') {
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
		array_push($centerMappings, array('plugin' => 'TatterToolsSeperator'));
		$seperatorCount++;
	}
	
	$newlayout = array_merge($newlayout, $centerMappings);
} else if (count($centerMappings) > 0) { // TODO 1/3
	unset($_GET['pos']);
	unset($_GET['rel']);
	$middlepos = (count($centerMappings) + 2)/3;
	array_splice($centerMappings, $middlepos , 0, array(array('plugin' => 'TatterToolsSeperator')));
	array_splice($centerMappings, $middlepos * 2, 0, array(array('plugin' => 'TatterToolsSeperator')));
	$newlayout = $addedlayout = $centerMappings;
}

if ((isset($_GET['pos'])) && (($_GET['pos'] < 0) || ($_GET['pos']) >= count($newlayout))) {
	unset($_GET['pos']);
	unset($_GET['rel']);
}

$modified = false;
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

if ((count($centerMappings) > 0) || (count($addedlayout) > 0) || ($modified == true)) {
	$saveLayout = array_values($newlayout);
	for ($i = 0; $i < count($saveLayout); $i++)
	{
		unset($saveLayout[$i]['title']);
	}
	setBlogSetting('centerLayout', serialize($saveLayout));
}

unset($addedlayout);
unset($layout);
unset($oldcenterlayout);

if (isset($_REQUEST['ajaxcall'])) {
	respond::ResultPage(0);
	exit;
}

$editClass = NULL;
if (isset($_REQUEST['edit'])) {
	$editClass = "-edit";
?>
<script src="<?php echo $service['path'];?>/script/dojo/dojo.js" type="text/javascript"></script>
<script src="<?php echo $service['path'];?>/script/dashboard.js" type="text/javascript"></script>
<?php
}
?>
						<script type="text/javascript">
							//<![CDATA[
								function changeList() {
									document.getElementById("tempForm").submit();
								}
<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
								
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION) {
?>
								
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
}
if(Acl::check("group.administrators")) {
?>
								function cleanupCache() {
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/center/dashboard/cleanup/");
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
<?php
if (false) {
	fetchConfigVal();
}
?>	
<?php
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (empty($_POST['useTTdashboard']))) {
	$textcubeDashboard = getBlogSetting("textcubeDashboard",1);
	if (is_null($textcubeDashboard)) {
		setBlogSetting("textcubeDashboard", 1);
		$textcubeDashboard = 1;
	} else {
		setBlogSetting("textcubeDashboard", 0);
		$textcubeDashboard = 0;
	}
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	setBlogSetting("textcubeDashboard", 1);
	$textcubeDashboard = 1;
} else {
	$textcubeDashboard = getBlogSetting("textcubeDashboard",1);
}

//$textcubeDashboard = getBlogSetting("textcubeDashboard");
//if (is_null($textcubeDashboard)) {
//	setBlogSetting("textcubeDashboard", 1);
//	$textcubeDashboard = 1;
//}

if($textcubeDashboard) {
	if (!isset($_REQUEST['edit'])) {
		$stats = getStatistics($blogid);
?>
						<div id="part-center-dashboard" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('센터');?></span></h2>
								
							<div id="shortcut-collection" class="section">
								<h3><?php echo _t('바로가기');?></h3>
								
								<ul>
									<li><a href="<?php echo $blogURL;?>/owner/entry/post"><?php echo _t('새 글 쓰기');?></a></li>
<?php
		$latestEntryId = getBlogSetting('LatestEditedEntry_user'.getUserId(),0);
		if($latestEntryId !== 0) {
			$latestEntry = getEntry($blogid,$latestEntryId);
			if(!is_null($latestEntry)) {
?>
									<li><a href="<?php echo $blogURL;?>/owner/entry/edit/<?php echo $latestEntry['id'];?>"><?php echo _f('최근글(%1) 수정', htmlspecialchars(UTF8::lessenAsEm($latestEntry['title'],10)));?></a></li>
<?php
			}
		}
		if(Acl::check("group.owners")) {
?>
									<li><a href="<?php echo $blogURL;?>/owner/entry/category"><?php echo _t('카테고리 추가 또는 변경');?></a></li>
									<li><a href="<?php echo $blogURL;?>/owner/skin"><?php echo _t('스킨 변경');?></a> / <a href="<?php echo $blogURL;?>/owner/skin/setting"><?php echo _t('스킨 상세 설정');?></a></li>
									<li><a href="<?php echo $blogURL;?>/owner/skin/coverpage"><?php echo _t('표지 변경');?></a> / <a href="<?php echo $blogURL;?>/owner/skin/sidebar"><?php echo _t('사이드바 변경');?></a></li>									
									<li><a href="<?php echo $blogURL;?>/owner/plugin"><?php echo _t('플러그인 켜고 끄기');?></a></li>
<?php
		}
		if ($service['reader'] == true) {
?>
									<li><a href="<?php echo $blogURL;?>/owner/network/reader"><?php echo _t('RSS로 등록한 이웃 글 보기');?></a></li>
<?php
		}
?>
								</ul>
							</div>
								
							<div id="total-information" class="section">
								<h3><?php echo _t('요약');?></h3>
									
								<table>
									<tbody>
										<tr>
											<th><?php echo _t('오늘/어제방문자');?></th>
											<td><?php echo number_format($stats['today']) . '/' . number_format($stats['yesterday']);?></td>
										</tr>
										<tr>
											<th><?php echo _t('총 방문자');?></th>
											<td><?php echo number_format($stats['total']);?></td>
										</tr>
										<tr>
											<th><?php echo _t('글 개수');?></th>
											<td><?php echo number_format(getEntriesTotalCount($blogid));?></td>
										</tr>
										<tr>
											<th><?php echo _t('댓글/걸린글 개수');?></th>
											<td><?php echo number_format(getCommentCount($blogid)) . '/' . number_format(getTrackbackCount($blogid));?></td>
										</tr>
									</tbody>
								</table>
<?php
		if(Acl::check("group.administrators")) {
?>
								<ul>
									<li><a href="<?php echo $blogURL;?>/owner/center/dashboard/cleanup" onclick="cleanupCache();return false;"><?php echo _t('캐시 지우기');?></a></li>
<?php
			if(Acl::check("group.creators")) {
?>
									<li><a href="<?php echo $blogURL;?>/owner/data" onclick="optimizeData();return false;"><?php echo _t('저장소 최적화');?></a></li>
								</ul>
<?php
			} else {
?>
								</ul>
<?php
			}
		}
?>
							</div>
							
							<div id="textcube-notice" class="section">
								<h3><?php echo _t('공지사항');?></h3>
									
<?php
		$noticeURL = "http://notice.textcube.org/";
		$noticeURLRSS = $noticeURL.(isset($blog['language']) ? $blog['language'] : "ko")."/rss";

		if(!is_null(getServiceSetting('Textcube_Notice_'.$blog['language']))) {
			$noticeEntries = unserialize(getServiceSetting('Textcube_Notice_'.$blog['language']));
		} else {
			list($result, $feed, $xml) = getRemoteFeed($noticeURLRSS);
			if ($result == 0) {
				$xmls = new XMLStruct();
				$xmls->setXPathBaseIndex(1);
				$noticeEntries = array();
				if ($xmls->open($xml, $service['encoding'])) {
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
				setServiceSetting('Textcube_Notice_'.$blog['language'],serialize($noticeEntries));
			}
		}

		if (count($noticeEntries) > 0) {
			array_splice($noticeEntries, 5, count($noticeEntries) - 5);
?>
								<table>
									<tbody>
<?php
			foreach($noticeEntries as $item) {
?>
										<tr>
											<td class="title"><a href="<?php echo $item['permalink'];?>" onclick="return openLinkInNewWindow(this);" ><?php echo htmlspecialchars(UTF8::lessenAsEm($item['title'],60));?></a></td>
											<td class="date"><?php echo Timestamp::format2($item['written']);?></td>
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

							<div id="myBlogInfo" class="section">
								<h3><?php echo _t('알림판');?></h3>
								<div id="infoPanel">
									<table class="comment">
										<caption><a href="<?php echo $blogURL."/owner/communication/comment";?>"><?php echo _t('최근 댓글');?></a></caption>
										<thead>
											<tr>
												<th scope="col"><?=_t('내용')?></th>
												<th scope="col" class="date"><?=_t('날짜')?></th>
											</tr>
										</thead>
										<tbody>
<?php
		$comments = getRecentComments($blogid,5);
		foreach ($comments as $comment) {
?>
											<tr>
												<td class="title"><a href="<?php echo $blogURL."/".$comment['entry']."#comment".$comment['id'];?>"><?php echo htmlspecialchars(UTF8::lessenAsEm($comment['comment'], 25));?></a></td>
												<td class="date"><?php echo Timestamp::format('%m/%d',$comment['written']);?></td>
											</tr>
<?php
		}
?>
										</tbody>
									</table>
									<table class="commentNotifier">
										<caption><a href="<?php echo $blogURL."/owner/communication/notify";?>"><?php echo _t('최근 댓글 알리미');?></a></caption>
										<thead>
											<tr>
												<th scope="col"><?=_t('내용')?></th>
												<th scope="col" class="date"><?=_t('날짜')?></th>
											</tr>
										</thead>
										<tbody>
<?php
		list($commentNotifies,$paging) = getCommentsNotifiedWithPagingForOwner($blogid,0,null,null,null,1,5);
		foreach ($commentNotifies as $comment) {
?>
											<tr>
												<td class="title"><?php echo htmlspecialchars(UTF8::lessenAsEm($comment['comment'], 25));?></td>
												<td class="date"><?php echo Timestamp::format('%m/%d',$comment['written']);?></td>
											</tr>
<?php
		}
?>
										</tbody>
									</table>
									<table class="guestbook">
										<caption><a href="<?php echo $blogURL."/owner/communication/comment?status=guestbook";?>"><?php echo _t('최근 방명록');?></a></caption>
										<thead>
											<tr>
												<th scope="col"><?=_t('내용')?></th>
												<th scope="col" class="date"><?=_t('날짜')?></th>
											</tr>
										</thead>
										<tbody>
<?php
		$guestbooks = getRecentGuestbook($blogid,5);
		foreach ($guestbooks as $guestbook) {
?>
											<tr>
												<td class="title"><a href="<?php echo $blogURL."/guestbook/".$guestbook['id']."#guestbook".$guestbook['id'];?>"><?php echo htmlspecialchars(UTF8::lessenAsEm($guestbook['comment'], 25));?></a></td>
												<td class="date"><?php echo Timestamp::format('%m/%d',$guestbook['written']);?></td>
											</tr>
<?php
		}
?>
										</tbody>
									</table>
									<table class="trackback">
										<caption><a href="<?php echo $blogURL."/owner/communication/trackback";?>"><?php echo _t('최근 트랙백');?></a></caption>
										<thead>
											<tr>
												<th scope="col"><?=_t('내용')?></th>
												<th scope="col" class="date"><?=_t('날짜')?></th>
											</tr>
										</thead>
										<tbody>
<?php
		$trackbacks = getRecentTrackbacks($blogid,5);
		foreach ($trackbacks as $trackback) {
?>
											<tr>
												<td class="title"><a href="<?php echo $blogURL."/".$trackback['entry']."#trackback".$trackback['id'];?>"><?php echo htmlspecialchars(UTF8::lessenAsEm($trackback['subject'], 25));?></a></td>
												<td class="date"><?php echo Timestamp::format('%m/%d',$trackback['written']);?></td>
											</tr>
<?php
		}
?>
										</tbody>
									</table>
								</div>

							</div>
						</div>
<?php
		unset($feed);
		unset($xmls);
		unset($noticeEntries);
	}
}
?>
						<form id="tempForm" method="post" action="<?php echo parseURL($blogURL.'/owner/center/dashboard');?>">
							<div id="part-center-quilt<?php echo $editClass;?>" class="part">
								<h2 class="caption"><span class="main-text"><?php echo _t('조각보를 봅니다');?></span></h2>
								
<?php
if (!isset($_REQUEST['edit']) && Acl::check('group.owners')) {
?>
								<dl id="independent-notice-line" class="line">
									<dt><?php echo _t('정보 패널 설정');?></dt>
									<dd>
										<input type="checkbox" class="checkbox" id="useTTdashboard" name="useTTdashboard" value="on" onclick="changeList();return false;"<?php echo $textcubeDashboard == 1 ? " checked" : NULL;?> />
										<label for="useTTdashboard"><?php echo _t('블로그 정보를 보여주는 패널을 사용합니다');?></label>
									</dd>
								</dl>
<?php
}
if(Acl::check('group.owners')) {
?>
								<dl id="direct-link-line" class="line">
									<dt><?php echo _t('플러그인 설정');?></dt>
									<dd>
										<a class="button" href="<?php echo $blogURL;?>/owner/plugin?visibility=center"><?php echo _t('위젯 켜고 끄기');?></a>
									</dd>
								</dl>
<?php
}
$boardbarNumber = 0;
$positionCounter = 0;
$secondposition = array(0, 0);
?>
								<div id="dojo_boardbar0" class="panel">
<?php
foreach ($newlayout as $mapping) {
	if ($mapping['plugin'] == 'TatterToolsSeperator') {
?>

								</div>
								<div id="dojo_boardbar<?php echo $boardbarNumber + 1;?>" class="panel">
<?php
		$secondposition[$boardbarNumber] = $positionCounter;
		$boardbarNumber++;
	} else {
?>
									<div id="<?php echo $mapping['plugin'];?>" class="section">
										<h3>
											<?php echo $mapping['title'];?> 
<?php
		if (isset($_REQUEST['edit'])) {
?>
				
											<a id="<?php echo $mapping['plugin'];?>dojoup" href="<?php echo $blogURL;?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter; ?>&amp;rel=-1&edit"><?php echo _t("위로");?></a>
											<a id="<?php echo $mapping['plugin'];?>dojodown" href="<?php echo $blogURL;?>/owner/center/dashboard?edit&pos=<?php echo $positionCounter;?>&amp;rel=1&edit"><?php echo _t("아래로");?></a>
<?php
		}
?>
										</h3>
										<?php echo handleCenters($mapping);?>
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

								<div id="dojo_boardbar1" class="panel"></div>
<?php
	$secondposition[$boardbarNumber] = $positionCounter;
	$boardbarNumber++;
	$positionCounter++;
}
if ($boardbarNumber < 2) {
?>
								<div id="dojo_boardbar2" class="panel"></div>
<?php
	$secondposition[$boardbarNumber] = $positionCounter;
	$boardbarNumber++;
	$positionCounter++;
}

// 팀블로그 :: 관리자 권한이 없으면 센터를 편집할수없다.

if(Acl::check('group.owners')) {
	if(!isset($_REQUEST['edit'])) {
?>
								<div class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('편집');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/center/dashboard?edit'; return false;" />
								</div>
<?php
	} else {
?>
								<div class="button-box">
									<input type="button" class="input-button" value="<?php echo _t('돌아가기');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/center/dashboard'; return false;" />
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
						<form id="dataOptimizer" method="get" action="<?php echo $blogURL;?>/owner/data/optimize" target="blackhole"></form>
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
if (isset($_REQUEST['edit'])) {
?>
						<script type="text/javascript">
							//<![CDATA[
								var pan0 = new DropPanel(document.getElementById('dojo_boardbar0'), ["dashboard"]);
								document.getElementById('dojo_boardbar0').plusposition = -1;
								var pan1 = new DropPanel(document.getElementById('dojo_boardbar1'), ["dashboard"]);
								document.getElementById('dojo_boardbar1').plusposition = <?php echo $secondposition[0];?>;
								var pan1 = new DropPanel(document.getElementById('dojo_boardbar2'), ["dashboard"]);
								document.getElementById('dojo_boardbar2').plusposition = <?php echo $secondposition[1];?>;
									
<?php
	$positionCounter = 0;
	foreach ($newlayout as $mapping) {
		if ($mapping['plugin'] != 'TatterToolsSeperator') {
?>
		document.getElementById('<?php echo $mapping['plugin'];?>').pos = <?php echo $positionCounter;?>;
		new DragPanel(document.getElementById('<?php echo $mapping['plugin'];?>'), ["dashboard"]);
		
		
		document.getElementById('<?php echo $mapping['plugin'];?>dojoup').parentNode.removeChild(document.getElementById('<?php echo $mapping['plugin'];?>dojoup'));
		document.getElementById('<?php echo $mapping['plugin'];?>dojodown').parentNode.removeChild(document.getElementById('<?php echo $mapping['plugin'];?>dojodown'));
<?php
		}
		$positionCounter++;
	}
?>
							//]]!>
						</script>
<?php
}

require ROOT . '/lib/piece/owner/footer.php';
?>
