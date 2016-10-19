<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'deleteCategory' => array('id', 'mandatory' => false),
			'direction' => array(array('up', 'down'), 'mandatory' => false),
			'id' => array('int', 'mandatory' => false),
			'newCategory' => array('string', 'mandatory' => false),
			'modifyCategoryName' => array('string', 'mandatory' => false),
			'modifyCategoryBodyId' => array('string', 'default' => 'tt-body-category'),
			'visibility' => array('int', 'mandatory' => false)
		)
	);
}
require ROOT . '/library/preprocessor.php';
$context = Model_Context::getInstance();
importlib('model.blog.category');
importlib('model.blog.entry');
if (!empty($_POST['id']))
	$selected = $_POST['id'];
else if (empty($_GET['id']))
	$selected = 0;
else
	$selected = $_GET['id'];

if (!empty($_POST['visibility'])) {
	$setVisibility = $_POST['visibility'];
	$visibility = setCategoryVisibility($blogid,$selected,$setVisibility);
} else {
	$visibility = getCategoryVisibility($blogid, $selected);
}

if (!empty($_POST['deleteCategory'])) {
	$parent = getParentCategoryId($blogid, $_POST['deleteCategory']);
	$selected = (is_null($parent)) ? 0 : $parent;
	$_POST['modifyCategoryName'] = '';
	$_POST['modifyCategoryBodyId'] = '';
	deleteCategory($blogid, $_POST['deleteCategory']);
}

if (!empty($_POST['direction']))
	moveCategory($blogid, $selected, $_POST['direction']);

if ($selected == 0)
	$depth = 0;
else if (!empty($_GET['name1']) && !empty($_GET['name2']))
	$depth = 2;
else if (!empty($_GET['name1']) && empty($_GET['name2']))
	$depth = 1;
else
	$depth = 0;

if (empty($_GET['entries']) || $_GET['entries'] == 0)
	$entries = 0;
else
	$entries = $_GET['entries'];

if ((!empty($_POST['newCategory']) && Utils_Misc::isSpace($_POST['newCategory'])) ||
			(!empty($_POST['modifyCategoryName']) && Utils_Misc::isSpace($_POST['modifyCategoryName']))) {
	$history = '';
	$errorMessage = _t('공백문자는 카테고리 이름으로 사용할 수 없습니다');
} elseif ((!empty($_POST['newCategory']) && strpos($_POST['newCategory'], '/') !== false) || (!empty($_POST['modifyCategoryName']) && strpos($_POST['modifyCategoryName'], '/') !== false)) {
	$history = '';
	$errorMessage = _t('슬래시가 들어간 카테고리 이름은 사용할 수 없습니다');
/*} elseif ((!empty($_POST['newCategory']) && strpos($_POST['newCategory'], '&') !== false) || (!empty($_POST['modifyCategoryName']) && strpos($_POST['modifyCategoryName'], '&') !== false)) {
	$history = '';
	$errorMessage = _t('앰퍼샌드(&)가 들어간 카테고리 이름은 사용할 수 없습니다');*/
} elseif ((!empty($_POST['newCategory']) && strpos($_POST['newCategory'], '?') !== false) || (!empty($_POST['modifyCategoryName']) && strpos($_POST['modifyCategoryName'], '?') !== false)) {
	$history = '';
	$errorMessage = _t('물음표가 들어간 카테고리 이름은 사용할 수 없습니다');
} elseif (!empty($_POST['newCategory'])) {
	$history = addCategory($blogid, ($selected == 0) ? null : $_POST['id'], trim($_POST['newCategory'])) ? 'document.getElementById("newCategory").select();' : '';
	if(empty($history)) $errorMessage = _t('같은 이름의 카테고리가 이미 존재합니다');
} else if (!empty($_POST['modifyCategoryName']) || !empty($_POST['modifyCategoryBodyId'])) {
	$history = modifyCategory($blogid, $_POST['id'], trim($_POST['modifyCategoryName']),trim($_POST['modifyCategoryBodyId'])) ? 'document.getElementById("modifyCategoryName").select();' : '';
	$tempParentId = POD::queryCell("SELECT `parent` FROM `{$database['prefix']}Categories` WHERE `id` = {$_POST['id']} AND `blogid` = ".getBlogId());
	if (preg_match('/^[0-9]+$/', $tempParentId, $temp)) {
		$depth = 2;
	} else {
		$depth = 1;
	}
} else {
	$history = '';
}
$categories = getCategories($blogid);
$name = getCategoryNameById($blogid, $selected) ? getCategoryNameById($blogid, $selected) : _t('전체');
$bodyid = getCategoryBodyIdById($blogid, $selected);
if ((empty($_POST['search'])) || ($searchColumn === true)) {
	$searchParam = true;
} else {
	$searchParam[0] = $_POST['searchColumn'];
	$searchParam[1] = $_POST['search'];
}
require ROOT . '/interface/common/owner/header.php';

?>
						<script type="text/javascript">
							//<![CDATA[
								function removeCategory() {
									if(confirm('<?php echo _t('삭제 하시겠습니까?');?>')) {
										var oform=document.forms[0];
										oform.deleteCategory.value=<?php echo $selected;?>;

										oform.submit()
									}
								}

								function moveCategory(direction) {
									var oform=document.forms[0];
									oform.direction.value=direction
									oform.id.value=<?php echo $selected;?>;
									oform.submit()
								}

								function addCategory() {
									var oform=document.forms[0];
									oform.id.value=<?php echo $selected;?>;
									oform.submit()
								}

								function modifyCategory() {
									var oform=document.forms[0];
									oform.id.value=<?php echo $selected;?>;
									oform.submit()
								}

								function changeCategoryVisibility() {
									var oform=document.forms[0];
									if (document.getElementById('currentVisibility').checked) {
										oform.visibility.value = 1;
									} else {
										oform.visibility.value = 2;
									}
									oform.id.value=<?php echo $selected;?>;
									oform.submit()
								}

								window.addEventListener("load", expandTreeInit, false);
								function expandTreeInit() {
									try {
										<?php echo $history;?>
										expandTree();
									} catch(e) {
										alert(e.message);
									}
								}

								function validateText(str) {
									return true;
								}
<?php
	if(isset($errorMessage)) {
?>
								alert('<?php echo $errorMessage;?>');
<?php
	}
?>
							//]]>
						</script>

						<div id="part-post-tree" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('분류를 관리합니다');?></span></h2>

							<div class="data-inbox">
								<div id="tree-preview-box">
									<div class="title"><?php echo _t('미리보기');?></div>
									<div id="treePreview">
<?php echo getCategoriesViewInOwner(getEntriesTotalCount(getBlogId()), $categories, $selected);?>
									</div>
								</div>

								<form class="section" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/entry/category">
									<fieldset id="property-box" class="container">
										<legend><?php echo _t('분류 관리 및 설정');?></legend>

										<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
										<input type="hidden" name="deleteCategory" />
										<input type="hidden" name="direction" />
										<input type="hidden" name="id" />
										<input type="hidden" name="visibility" />

										<dl id="label-create-line" class="line">
											<dt><label for="newCategory"><?php echo _t('만들기');?></label></dt>
<?php
if ($depth <= 1) {
?>
											<dd>
												<div class="field-box">
													<input type="text" id="newCategory" class="input-text" name="newCategory" onkeyup="if (event.keyCode == 13 &amp;&amp; validateText(this.value)) {addCategory();return false;}" />
													<input type="button" class="add-button input-button" value="<?php echo _t('추가하기');?>" onclick="addCategory(); return false;" />
												</div>
												<p>
													<?php echo _f('"%1"의 하위에 새 분류를 만듭니다.', htmlspecialchars("$name"));?>
												</p>
											</dd>
<?php
} else {
?>
											<dd><p><?php echo _t('분류는 2단까지 허용됩니다.');?></p></dd>
<?php
}
?>

										</dl>
										<dl id="label-change-line" class="line">
											<dt><label for="modifyCategoryName"><?php echo _t('이름 변경');?></label></dt>
											<dd>
												<div class="field-box">
													<input type="text" id="modifyCategoryName" class="input-text" name="modifyCategoryName" onkeyup="if (event.keyCode == '13' &amp;&amp; validateText(this.value)) modifyCategory();" value="<?php echo htmlspecialchars($name);?>" />
													<input type="button" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="modifyCategory(); return false;" />
												</div>
											</dd>
										</dl>
										<dl id="body-id-line" class="line">
											<dt><label for="modifyCategoryBodyId"><?php echo _t('Body Id 변경');?></label></dt>
											<dd>
												<div class="field-box">
													<input type="text" id="modifyCategoryBodyId" class="input-text" name="modifyCategoryBodyId" onkeyup="if (event.keyCode == '13' &amp;&amp; validateText(this.value)) modifyCategory();" value="<?php echo htmlspecialchars($bodyid);?>" <?php if ($selected == 0) echo 'readonly="readonly"';?> />
													<input type="button" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="modifyCategory(); return false;" />
												</div>
												<p><?php echo _t('Body id는 블로그 스킨의 <acronym title="Cascading Style Sheet">CSS</acronym> 활용을 위해 사용합니다.<br /> 기본값인 "tt-body-category"를 그냥 사용하셔도 사용에 지장은 없습니다.');?></p>
											</dd>
										</dl>
										<dl id="label-move-line" class="line">
											<dt><span class="label"><?php echo _t('정렬순서 변경');?></span></dt>
<?php
if ($selected > 0) {
?>
											<dd>
												<div class="field-box">
													<input type="button" class="up-button input-button" value="<?php echo _t('위로');?>" onclick="moveCategory('up');" /><span class="divider"> | </span><input type="button" class="down-button input-button" value="<?php echo _t('아래로');?>" onclick="moveCategory('down');" />
												</div>
											</dd>
<?php
} else {
?>
											<dd><p><?php echo _t('최상단 분류는 이동할 수 없습니다.');?></p></dd>
<?php
}
?>

										</dl>
										<dl id="label-visibility-line" class="line">
											<dt><span class="label"><?php echo _t('공개 설정');?></span></dt>
											<dd>
<?php
if ($selected > 0) {
?>
												<input type="checkbox" id="currentVisibility" class="checkbox" name="currentVisibility"<?php echo $visibility != 2 ? ' checked="checked"' : '';?> onclick="changeCategoryVisibility();return false;" /><label for="currentVisibility"><?php echo _t('이 카테고리를 비공개로 설정합니다.');?></label>
<?php
} else {
?>
												<p><?php echo _t('최상단 분류는 비공개로 설정할 수 없습니다.');?></p>
<?php
}
?>
											</dd>
										</dl>
										<dl id="label-remove-line" class="line">
											<dt><span class="label"><?php echo _t('분류 삭제');?></span></dt>
											<dd>

<?php
if ($selected == 0) {
	echo '<p>'._t('최상단 분류는 삭제할 수 없습니다.').'</p>';
} else if (getNumberEntryInCategories($selected) > 0) {
	echo '<p>'._t('분류에 등록된 글이 있으므로 삭제할 수 없습니다.').'</p>';
} else if (getNumberChildCategory($selected) > 0) {
	echo '<p>'._t('하위 분류가 있으므로 삭제할 수 없습니다.').'</p>';
} else {
?>
												<div class="field-box">
													<input type="button" class="remove-button input-button" value="<?php echo _t('삭제하기');?>" onclick="removeCategory();return false;" />
												</div>
<?php
}
?>
											</dd>
										</dl>
									</fieldset>
								</form>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
