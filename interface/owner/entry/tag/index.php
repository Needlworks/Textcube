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
							function updateTagPanel(id) {
								var request = new HTTPRequest("POST","<?php echo $blogURL;?>/owner/entry/tag/panel/");
								request.onSuccess = function () {
									PM.removeRequest(this);
									entryView = this.getText("/response/entryView");
									updatePanel(entryView);
									PM.showMessage("<?php echo _t('태그에 해당되는 글 목록을 불러왔습니다.');?>", "center", "bottom");
								}
								request.onError = function () {
									PM.removeRequest(this);
									PM.showErrorMessage("<?php echo _t('글 목록을 불러올 수 없었습니다.');?>","center","bottom");
								}
								PM.addRequest(request, "<?php echo _t('글 목록을 불러오고 있습니다.');?>");
								request.send("tagId="+id);
							}
							function updatePanel(contentView) {
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
		echo '<a href="#" class="tag" onclick="updateTagPanel('.$t['id'].');return false;">'.$t['name'].'</a> ';
	endforeach;
?>
							</div>
							<div id="tag-panel">
								<dl>
									<dt><?php echo _t('현재 태그');?></dt>
									<dd id="tag-name"><?php echo _t('선택되지 않았습니다.');?></dd>

									<dd id="tag-number-of-posts"><?php echo _t('해당 태그가 포함된 글 수');?> :  <span id="tag-number-of-posts-value" class="text">0</span></dd>
								</dl>
								<dl>
									<dt><?php echo _t('글목록');?></dt>
									<dd>
										<span class="text"><?php echo _t('이 태그를 갖는 모든 글의 목록을 봅니다');?></span>
									</dd>
								</dl>
								<dl>
									<dt><?php echo _t('태그 이름 변경');?></dt>
									<dd>
										<input type="text" id="tag-newname" class="input-text" name="tag-newname" value="" />
										<br />
										<label for="tagnewname"><?php echo _t('현재의 태그를 새로운 이름으로 변경합니다. 같은 태그를 가지고 있는 모든 글의 태그 이름이 함께 바뀌게 됩니다.');?></label>
									</dd>
								</dl>
								<dl>
									<dt><?php echo _t('태그 삭제');?></dt>
									<dd>
										<input type="button" id="tagDelete" class="input-button" name="tagDelete" value="<?php echo _t('삭제');?>" />
										<br />
										<label for="tagName"><?php echo _t('이 태그를 삭제합니다. 같은 태그를 가지고 있는 모든 글에서 이 태그가 삭제됩니다.');?></label>
									</dd>
								</dl>								
						</div>

						<hr class="hidden clear" />
						
<?php
require ROOT . '/interface/common/owner/footer.php';
?>