<?php
/// Copyright (c) 2004-2013, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
$context = Model_Context::getInstance();
printMobileHTMLHeader();

if(empty($suri['id']) && empty($suri['value'])) {
	printMobileHTMLMenu('','list');
	$blog['entriesOnList'] = 8;
	if(!$listWithPaging = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnList']))
		$listWithPaging = array(array(), array('total' => 0));
	$list = array('title' => (empty($suri['value']) ? getCategoryLabelById($blogid, 0) : $suri['value']), 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);
	$paging = $listWithPaging[1];
	print printMobileEntryListView($list['items'],'blog_posts_'.$suri['page'],_text('글목록'),$paging, $list['count']);
	print printMobileListNavigation($paging,'entry');
} else {
	if(!empty($suri['id'])) {
		list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
	} else if(!empty($suri['value'])) {
		$entryPrint = true;
		list($entries, $paging) = getEntryWithPagingBySlogan($blogid, $suri['value']);
	//	printMobileHTMLHeader();
	}
	printMobileHTMLMenu('','list');
	
	$entry = $entries ? $entries[0] : null;
?>
	<div id="post_<?php echo $entry['id'];?>" title="<?php echo htmlspecialchars($entry['title']);?>" class="panel"<?php echo (!empty($entryPrint) ? 'selected="true"' : '');?>>
		<div class="entry_info">
			<h2><?php echo htmlspecialchars($entry['title']);?></h2>
			<h3 class="noBorderLine"><?php echo Timestamp::format5($entry['published']);?></h3>
		</div>
		<div class="content"><?php printMobileEntryContentView($blogid, $entry, null); ?></div>
<?php 
	$entryTags = getTags($entry['blogid'], $entry['id']);
	if (sizeof($entryTags) > 0) {
?>
		<div class="entry_tags" data-role="content" data-theme="c">
		<h3 class="tags_title">Tags</h3>
<?php
		$tags = array();
		$relTag = Setting::getBlogSettingGlobal('useMicroformat', 3)>1 && (count($entries) == 1 || !empty($skin->hentryExisted) );
		foreach ($entryTags as $entryTag) {
			$tags[$entryTag['name']] = '<a href="'.$context->getProperty('uri.blog').'/tag/' . $entryTag['id'] . '">' . htmlspecialchars($entryTag['name']) . '</a>';
		}
		echo implode(",\r\n", array_values($tags));
?>
		</div>
	</div>
<?php
	}
	if(doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && (trim($_COOKIE['GUEST_PASSWORD']) == trim($entry['password'])))) {
		printMobileNavigation($entry, true, true, $paging);
	} else {
		printMobileNavigation($entry, false, false, $paging);
	}
	if(!empty($entryPrint)) {
?>
		</div>
<?php
	}
}

printMobileHTMLFooter();
?>
