<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../../../..');
require ROOT . '/lib/include.php';
requireStrictRoute();
list($entryId) = getCommentAttributes($owner, $suri['id'], 'entry');
if (deleteComment($owner, $suri['id'], $entryId, '') === false) {
	printMobileErrorPage(_t('답글을 삭제할 수 없습니다'), _t('관리자가 아닙니다'), "$blogURL/comment/delete/{$suri['id']}");
	exit();
}
list($entries, $paging) = getEntryWithPaging($owner, $entryId);
$entry = $entries ? $entries[0] : null;
printMobileHtmlHeader();
?>
<div id="content">
	<h2><?php echo _t('답글이 삭제됐습니다');?></h2>
</div>
<?php
printMobileNavigation($entry);
printMobileHtmlFooter();
?>