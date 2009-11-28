<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
?>
<div id="guestbook_<?php echo time();?>" title="Guestbook" selected="false">
<?php
	printIphoneGuestbookView(0);
?>
	<fieldset class="navi margin-top10">
<?php
	printIphoneNavigation($entry, false, false, $paging);
?>
	</fieldset>
</div>
