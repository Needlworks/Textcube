<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';

if (!isset($_REQUEST['ajaxcall'])) {
	require ROOT . '/lib/piece/owner/headerA.php';
	require ROOT . '/lib/piece/owner/contentMenuA0.php';
	trashVan();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['pos'])) $_GET['pos'] = $_POST['pos'];
	if (isset($_POST['rel'])) $_GET['rel'] = $_POST['rel'];
}

$layout = getUserSetting('centerLayout', '');
$newlayout = array();
$addedlayout = array();
$oldcenterlayout = array();

if (count($centerMappings) == 0) {
	$layout = '';
	setUserSetting('centerLayout', '');
	unset($_GET['pos']);
	unset($_GET['rel']);
}

if ((!empty($layout)) && (($oldcenterlayout = unserialize($layout)) != false) ){
	
	$seperatorCount = 0;
	
	foreach($oldcenterlayout as $item) {
		if ($item['plugin'] == 'TatterToolsSeperator') {
			array_push($newlayout, $item);
			$seperatorCount++;
		} else if (($pos = array_search($item, $centerMappings, true)) !== false) {
			array_push($newlayout, $item);
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
	setUserSetting('centerLayout', serialize($newlayout));
}

unset($addedlayout);
unset($layout);
unset($oldcenterlayout);

if (isset($_REQUEST['ajaxcall'])) {
	respondResultPage(0);
	exit;
}

if (isset($_REQUEST['edit'])) {
?>
<script src="<?php echo $service['path'];?>/script/dojo/dojo.js" type="text/javascript"></script>
<script src="<?php echo $service['path'];?>/script/dashboard.js" type="text/javascript"></script>
<?php
}

if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", checkTattertoolsVersion, false);
								function checkTattertoolsVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
							//]]>
						</script>
<?php
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION) {
?>
						<script type="text/javascript">
							//<![CDATA[
								window.addEventListener("load", checkTattertoolsVersion, false);
								function checkTattertoolsVersion() {
									if (confirm("<?php echo _t('태터툴즈 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
							//]]>
						</script>
<?php
}

if (false) {
	fetchConfigVal();
}
?>	
						<form method="post" action="<?php echo $blogURL;?>/owner/center/dashboard">
							<div id="part-center-dashboard" class="part">
<?php
$tattertoolsDashboard = getUserSetting("tattertoolsDashboard");
if (is_null($tattertoolsDashboard)) {
	setUserSetting("tattertoolsDashboard", 1);
	$tattertoolsDashboard = 1;
}

if($tattertoolsDashboard) {
	if (!isset($_REQUEST['edit'])) {
		$stats = getStatistics($owner);
?>
								<h2 class="caption"><span class="main-text"><?php echo _t('태터툴즈');?></span></h2>
								
								<div id="shortcut-collection" class="section">
									<h3><?php echo _t('바로가기');?></h3>
									
									<ul>
										<li><a href="<?php echo $blogURL;?>/owner/entry/post"><?php echo _t('새글 쓰기');?></a></li>
<?php
		$latestEntryId = getUserSetting('LatestEditedEntry',0);
		if($latestEntryId !== 0){
			$latestEntry = getEntry($owner,$latestEntryId);
?>
										<li><a href="<?php echo $blogURL;?>/owner/entry/edit/<?php echo $latestEntry['id'];?>"><?php echo _f('최근글(%1) 수정', UTF8::lessenAsEm(htmlspecialchars($latestEntry['title']),10));?></a></li>
<?php
		}
?>
										<li><a href="<?php echo $blogURL;?>/owner/skin"><?php echo _t('스킨 변경');?></a></li>
										<li><a href="<?php echo $blogURL;?>/owner/skin/setting"><?php echo _t('블로그 표시설정');?></a></li>
										<li><a href="<?php echo $blogURL;?>/owner/entry/category"><?php echo _t('카테고리 변경');?></a></li>
										<li><a href="<?php echo $blogURL;?>/owner/plugin"><?php echo _t('플러그인 관리');?></a></li>
										<li><a href="<?php echo $blogURL;?>/owner/reader"><?php echo _t('RSS 리더');?></a></li>
									</ul>
								</div>
								
								<div id="total-information" class="section">
									<h3><?php echo _t('종합정보');?></h3>
									
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
												<td><?php echo number_format(getEntriesTotalCount($owner));?></td>
											</tr>
											<tr>
												<th><?php echo _t('댓글/걸린글 개수');?></th>
												<td><?php echo number_format(getCommentCount($owner)) . '/' . number_format(getTrackbackCount($owner));?></td>
											</tr>
										</tbody>
									</table>
								</div>
							
								<div id="tattertools-notice" class="section">
									<h3><?php echo _t('태터툴즈 공지사항');?></h3>
									
<?php
		$noticeURL = "http://blog.tattertools.com";
		$noticeURLRSS = "http://blog.tattertools.com/rss";

		list($result, $feed, $xml) = getRemoteFeed($noticeURLRSS);
		if ($result == 0) {
			$xmls = new XMLStruct();
			$noticeEntries = array();
			if ($xmls->open($xml, $service['encoding'])) {
				if ($xmls->getAttribute('/rss', 'version')) {
					for ($i = 0; $link = $xmls->getValue("/rss/channel/item[$i]/link"); $i++) {
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
				} else if ($xmls->getAttribute('/feed', 'version')) {
					for ($i = 0; $link = $xmls->getValue("/feed/entry[$i]/id"); $i++) {
						for ($j = 0; $rel = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'rel'); $j++) {
							if($rel == 'alternate') {
								$link = $xmls->getAttribute("/feed/entry[$i]/link[$j]", 'href');
								break;
							}
						}
						$item = array('permalink' => rawurldecode($link));
						$item['author'] = $xmls->getValue("/feed/entry[$i]/author/name");
						$item['title'] = $xmls->getValue("/feed/entry[$i]/title");
						$item['written'] = parseDate($xmls->getValue("/feed/entry[$i]/issued"));
						array_push($noticeEntries, $item);
					}
				} else if ($xmls->getAttribute('/rdf:RDF', 'xmlns')) {
					for ($i = 0; $link = $xmls->getValue("/rdf:RDF/item[$i]/link"); $i++) {
						$item = array('permalink' => rawurldecode($link));
						$item['author'] = $xmls->getValue("/rdf:RDF/item[$i]/dc:creator");
						$item['title'] = $xmls->getValue("/rdf:RDF/item[$i]/title");
						$item['written'] = parseDate($xmls->getValue("/rdf:RDF/item[$i]/dc:date"));
						array_push($noticeEntries, $item);
					}
				}
			}	
			
			if (count($noticeEntries) > 0) {
				// customize point. 사이트 공지를 넣고 싶다면 이것을 줄이고 또 출력하면 된다~
				array_splice($noticeEntries, 10, count($noticeEntries) - 10);
?>
									<table>
										<tbody>
<?php
				foreach($noticeEntries as $item) {
?>
											<tr>
												<td class="title"><a href="<?php echo $item['permalink'];?>"><?php echo htmlspecialchars($item['title']);?></a></td>
												<td class="date"><?php echo Timestamp::format5($item['written']);?></td>
											</tr>
<?php
				}
?>
										</tbody>
									</table>
									
									<div id="cite">from <a href="<?php echo $noticeURL;?>"><?php echo $noticeURL;?></a></div>
<?php
			} else {
?>
									<div id="empty-notice">
										<?php echo _t('공지사항이 없습니다.');?>
									</div>
<?php
			}
			
		} else {
?>
									<div id="fail-notice">
										<?php echo _t('공지사항을 가져올 수 없습니다. 잠시 후 다시 시도해 주십시오.');?>
									</div>
<?php
		}

		unset($feed);
		unset($xmls);
		unset($noticeEntries);
	}
?>
								</div>
<?php
}
?>

								<div id="part-center-quilt" class="part">
									<h2 class="caption"><span class="main-text"><?php echo _t('조각보를 봅니다');?></span></h2>
									
<?php
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
									<div id="dojo_boardbar'. ($boardbarNumber + 1).'" class="panel">
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

if (!isset($_REQUEST['edit'])) {
?>
									<div class="button-box">
										<input type="submit" class="input-button" value="<?php echo _t('편집');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/center/dashboard?edit'; return false;" />
									</div>
<?php
}
?>
								</div>
							</form>

<?php
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

require ROOT . '/lib/piece/owner/footer1.php';
?>
