<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu('','trackback');

if(isset($_GET['page'])) $page = $_GET['page'];
else $page = 1;

if(!empty($suri['id'])) { 
	list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
	$entry = $entries ? $entries[0] : null;
?>
<div id="trackback_<?php echo $entry['id']."_".time();?>" title="<?php echo _text('트랙백');?> : <?php echo htmlspecialchars($entry['title']);?>" selected="false">
<?php
	printMobileTrackbackView($entry['id']);
	printMobileNavigation($entry, true, false);
?>
</div>
<?php
} else {
?>	
<div id="trackback_<?php echo "_".time();?>" title="<?php echo _text('최근 트랙백');?>" selected="false">
<?php
	printMobileRecentTrackbackView($page);
	printMobileNavigation($entry, false, false, $paging, 'trackback');
?>
</div>
<?php
}
?>
