<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
requireView('iphoneView');
list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
$entry = $entries ? $entries[0] : null;
?>
<div id="trackback_<?php echo $entry['id']."_".time();?>" title="Trackback <?php echo $entry['id'];?>" selected="false">
	<?php
		printIphoneTrackbackView($entry['id']);
	?>
	<fieldset class="navi margin-top10">
	<?php
		printIphoneNavigation($entry, true, false);
	?>
	</fieldset>
</div>
