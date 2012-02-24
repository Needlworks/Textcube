<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
logout();
printMobileHTMLHeader();
printMobileHTMLMenu();
$context = Model_Context::getInstance();
?>

<div id="Logout" title="Logout" class="panel" selected="false">
	<div class="content">
		<h2><?php echo _text('성공적으로 로그아웃 하였습니다.');?></h2>
	</div>
	<a data-role="button" href="<?php echo $context->getProperty('uri.blog');?>"><?php echo _text('첫 화면으로 돌아가기');?></a>
</div>
<?php
printMobileHTMLFooter();
?>
