<?php
/// Copyright (c) 2004-2012, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu('','guestbook');
if(isset($suri['id'])) $page = $suri['id'];
else $page = 1;
?>
<div id="guestbook_<?php echo time();?>" title="<?php echo _text('방명록');?>" selected="false">
<?php
	printMobileGuestbookView($page);
?>
	<fieldset class="navi margin-top10">
<?php
	printMobileNavigation(0, false, false, $paging, 'guestbook');
?>
	</fieldset>
</div>
<?php
printMobileHTMLFooter();
?>
