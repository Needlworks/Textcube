<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_MOBILE__', true);
require ROOT . '/library/preprocessor.php';

// Redirect for ipod touch / iPhone
if(strpos($_SERVER['HTTP_USER_AGENT'],'iPod') || strpos($_SERVER['HTTP_USER_AGENT'],'iPhone')){
	header("Location: ".$pathURL.getFancyURLpostfix()."/i");	exit;
}
requireView('mobileView');

if(empty($suri['id'])) {
	list($entry, $paging) = getEntriesWithPaging($blogid, 1, 1);
	if(empty($entry))
		printMobileErrorPage(_text('페이지 오류'), _text('글이 하나도 없습니다.'), $blogURL);
	else
		header("Location: $blogURL/{$entry[0]['id']}");
} else {
	list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
	$entry = $entries ? $entries[0] : null;
	printMobileHtmlHeader();
	?>
	<div id="content">
		<h2><?php echo htmlspecialchars($entry['title']);?></h2>	
		<hr />
		<?php printMobileEntryContentView($blogid, $entry, getKeywordNames($blogid)); ?>
	</div>
<?php
	if(doesHaveOwnership() || ($entry['visibility'] >= 2) || (isset($_COOKIE['GUEST_PASSWORD']) && (trim($_COOKIE['GUEST_PASSWORD']) == trim($entry['password'])))) {
		printMobileNavigation($entry, true, true, $paging);
	} else {
		printMobileNavigation($entry, false, false, $paging);
	}
	printMobileHtmlFooter();
}
?>
