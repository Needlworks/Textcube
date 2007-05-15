<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');

require ROOT . '/lib/includeForBlogOwner.php';
trashVan();
publishEntries();

// 컨텐츠 목록 생성.
list($categorizedEntries[0], $paging) = getEntriesWithPagingForOwner($owner,-5, null, 1, 5, 0);
list($categorizedEntries[1], $paging) = getEntriesWithPagingForOwner($owner,-5, null, 1, 5, '>= 1');
$entriesClass[0]['name'] = _t('비공개 글');
$entriesClass[1]['name'] = _t('공개된 글');
$entriesClass[0]['url'] = $blogURL.'/owner/entry?visibility=private';
$entriesClass[1]['url'] = $blogURL.'/owner/entry?visibility=public';

require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
<?php
if (!file_exists(ROOT . '/cache/CHECKUP')) {
?>
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
} else if (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION) {
?>
								window.addEventListener("load", checkTextcubeVersion, false);
								function checkTextcubeVersion() {
									if (confirm("<?php echo _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');?>"))
										window.location.href = "<?php echo $blogURL;?>/checkup";
								}
<?php
}
?>
								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
									document.getElementById('allChecked').disabled = false;
									removeItselfById('category-move-button');
								}
								
								function toggleThisTr(obj) {
									objTR = getParentByTagName("TR", obj);
									
									if (objTR.className.match('inactive')) {
										objTR.className = objTR.className.replace('inactive', 'active');
									} else {
										objTR.className = objTR.className.replace('active', 'inactive');
									}
								}
							//]]>
						</script>
						
						<div id="part-post-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('글들을 편집합니다');?></span></h2>

<?php
$i = 0;
foreach($categorizedEntries as $entryClass){
?>
								<h3><?php echo $entriesClass[$i]['name'];?></h3>
								<table>
								<thead>
								<tr>
								<td colspan="2"><?php echo $entriesClass[$i]['name'];?></td>
								</tr>
								<tbody>
<?php
	foreach($entryClass as $entry){
?>
										<tr>
											<td class="date"><?php echo Timestamp::formatDate($entry['published']);?></td>
											<td class="title">
												<?php echo ($entry['draft'] ? ('<span class="temp-icon bullet" title="' . _t('임시 저장본이 있습니다.') . '"><span>' . _t('[임시]') . '</span></span> ') : '');?>
<?php
	$editmode = 'entry';
?>
												<a href="<?php echo $blogURL;?>/owner/<?php echo $editmode;?>/edit/<?php echo $entry['id'];?>" onclick="document.getElementById('list-form').action='<?php echo $blogURL;?>/owner/<?php echo $editmode;?>/edit/<?php echo $entry['id'];?>'<?php echo ($entry['draft'] ? ("+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')") : '');?>; document.getElementById('list-form').submit(); return false;"><?php echo htmlspecialchars($entry['title']);?></a>
											</td>
										</tr>
<?php
	}
?>
									</tbody>
								</table>
							<a href="<?php echo $entriesClass[$i]['url'];?>">GO</a>
							
<?php
	$i++;
}
?>
							
							<form id="category-form" class="category-box" method="post" action="<?php echo $blogURL;?>/owner/entry">
								<div class="section">
									<select id="category" name="category" onchange="document.getElementById('category-form').page.value=1; document.getElementById('category-form').submit()">
										<option value="-5"><?php echo _t('모든 글');?></option>
										<optgroup class="category" label="<?php echo _t('글 종류');?>">
											<option value="-2"><?php echo _t('공지');?></option>
											<option value="-1"><?php echo _t('키워드');?></option>
										</optgroup>
										<optgroup class="category" label="<?php echo _t('분류');?>">
											<option value="0"><?php echo htmlspecialchars(getCategoryNameById($owner,0) ? getCategoryNameById($owner,0) : _t('전체'));?></option>
<?php
foreach (getCategories($owner) as $category) {
	if ($category['id'] != 0) {
?>
											<option value="<?php echo $category['id'];?>"><?php echo ($category['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($category['name']);?></option>
<?php
	}
	foreach ($category['children'] as $child) {
		if ($category['id'] != 0) {
?>
											<option value="<?php echo $child['id'];?>">&nbsp;― <?php echo ($category['visibility'] > 1 && $child['visibility'] > 1 ? '' : _t('(비공개)')).htmlspecialchars($child['name']);?></option>
<?php
		}
	}
}
?>
											<option value="-3"><?php echo _t('(분류 없음)');?></option>
										</optgroup>
									</select>
									<input type="submit" id="category-move-button" class="move-button button" value="<?php echo _t('이동');?>" />
								</div>
							</form>


							<hr class="hidden" />

						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
