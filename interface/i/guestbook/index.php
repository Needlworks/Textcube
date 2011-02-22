<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
if(isset($suri['id'])) $page = $suri['id'];
else $page = 1;
?>
<div id="guestbook_<?php echo time();?>" title="<?php echo _text('방명록');?>" selected="false">
<?php
	printIphoneGuestbookView($page);
?>
	<fieldset class="navi margin-top10">
<?php
	printIphoneNavigation(0, false, false, $paging, 'guestbook');
?>
	</fieldset>
</div>
