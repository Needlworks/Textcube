<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'deleteTag' => array('id', 'mandatory' => false),
			'id' => array('id', 'mandatory' => false),
			'category' => array('id', 'mandatory' => false),
		)
	);
}

require ROOT . '/library/preprocessor.php';

requireModel('blog.tag');
requireModel('blog.entry');

if (!empty($_POST['deleteTag'])) {
	deleteTagById($blogid, $_POST['deleteTag']);
}

if (!empty($_POST['id']) && !empty($_POST['category'])) {
	$entries = array();
	foreach (getEntriesByTagId($blogid, $_POST['id']) as $entry) {
		$entries[] = $entry['id'];
	}
	changeCategoryOfEntries($blogid, implode(',', $entries), $_POST['category']);
}

$tags = getSiteTags($blogid);
if(isset($suri['value']) && !empty($suri['value']) ) {
	$tag = $suri['value'];
	$list = getEntryListWithPagingByTag($blogid, $tag, $suri['page'], 20);
} else {
	$list = null;	
}
require ROOT . '/interface/common/owner/header.php';
?>
						<script type="text/javascript">
							//<![CDATA[
							function updateTagEntries(id) {
								var request = new HTTPRequest("POST","<?php echo $blogURL;?>/owner/entry/tag/entryList/");
								var page = 1;
								request.onSuccess = function () {
									PM.removeRequest(this);
									entryView = this.getText("/response/entryView");
									updateEntryList(entryView);
									PM.showMessage("<?php echo _t('태그에 해당되는 글 목록을 불러왔습니다.');?>", "center", "bottom");
								}
								request.onError = function () {
									PM.removeRequest(this);
									PM.showErrorMessage("<?php echo _t('글 목록을 불러올 수 없었습니다.');?>","center","bottom");
								}
								PM.addRequest(request, "<?php echo _t('글 목록을 불러오고 있습니다.');?>");
								request.send("tagId="+id+"&page="+page);
							}
							function updateEntryList(contentView) {
								Econtent = document.getElementById("tag-entries");
								Econtent.innerHTML = contentView;
								return true;							
							}							
							//]]>
						</script>
						<div id="part-post-tag" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('태그를 관리합니다');?></span></h2>
						</div>

						<hr class="hidden" />

						<div id="tag-content-box">
							<div id="tag-cloud">
<?php
	foreach($tags as $t):
		echo '<a href="#" class="tag" onclick="updateTagEntries('.$t['id'].');return false;">'.$t['name'].'</a> ';
	endforeach;
?>
							</div>
							<div id="tag-entries">
<?php
	if(is_null($list)) {
		echo '								<span class="text notice">'._t("태그를 선택하면 해당되는 글 목록을 볼 수 있습니다").'</span>';
	} else {
		foreach($list as $l):
			
		endforeach;
	}
?>

							</div>
						</div>

						<hr class="hidden clear" />
						
<?php
require ROOT . '/interface/common/owner/footer.php';
?>