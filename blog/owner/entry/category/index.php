<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_POST['id']))
	$selected = $_POST['id'];
else if (empty($_GET['id']))
	$selected = 0;
else
	$selected = $_GET['id'];
if (!empty($_POST['deleteCategory'])) {
	$parent = getParentCategoryId($owner, $_POST['deleteCategory']);
	$selected = (is_null($parent)) ? 0 : $parent;
	$_POST['modifyCategoryName'] = '';
	$_POST['modifyCategoryBodyId'] = '';
	deleteCategory($owner, $_POST['deleteCategory']);
}
if (!empty($_POST['direction']))
	moveCategory($owner, $selected, $_POST['direction']);
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
if (!empty($_POST['newCategory'])) {
	$history = addCategory($owner, ($selected == 0) ? null : $_POST['id'], trim($_POST['newCategory'])) ? 'document.getElementById("newCategory").select();' : '';
} else if ((!empty($_POST['modifyCategoryName']) OR !empty($_POST['modifyCategoryBodyId'])) && ($selected > 0)) {
	$history = modifyCategory($owner, $_POST['id'], trim($_POST['modifyCategoryName']),trim($_POST['modifyCategoryBodyId'])) ? 'document.getElementById("modifyCategoryName").select();' : '';
	$tempParentId = fetchQueryCell("SELECT `parent` FROM `{$database['prefix']}Categories` WHERE `id` = {$_POST['id']}");
	if (preg_match('/^[0-9]+$/', $tempParentId, $temp)) {
		$depth = 2;
	} else {
		$depth = 1;
	}
} else {
	$history = '';
}
$categories = getCategories($owner);
$name = getCategoryNameById($owner, $selected);
$bodyid = getCategoryBodyIdById($owner, $selected);
if ((empty($_POST['search'])) || ($searchColumn === true)) {
	$searchParam = true;
} else {
	$searchParam[0] = $_POST['searchColumn'];
	$searchParam[1] = $_POST['search'];
}
require ROOT . '/lib/piece/owner/header0.php';
require ROOT . '/lib/piece/owner/contentMenu03.php';
?>
									<input type="hidden" name="deleteCategory" />
									<input type="hidden" name="direction" />
									<input type="hidden" name="id" />
									
									<script type="text/javascript">
										//<![CDATA[
											function removeCategory() {
												if(confirm('<?=_t('삭제할까요?')?>')) {
													var oform=document.forms[0];  
													oform.deleteCategory.value=<?=$selected?>; 
													oform.submit()
												}
											}
											
											function moveCategory(direction) {
												var oform=document.forms[0];
												oform.direction.value=direction
												oform.id.value=<?=$selected?>;
												oform.submit()
											}
											
											function addCategory() {
												var oform=document.forms[0];
												oform.id.value=<?=$selected?>;
												oform.submit()
											}
											
											function modifyCategory() {
												var oform=document.forms[0];
												oform.id.value=<?=$selected?>;
												oform.submit()
											}
											window.onload = function () {
												try {
													<?=$history?>
													expandTree();								
												} catch(e) {
													alert(e.message);	
												}
											}
											
											function validateText(str) {
												return true;
											}
										//]]>
									</script>
									
									<div id="part-post-tree" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('분류를 관리할 수 있습니다')?></span></h2>
										
										<div class="data-inbox">
											<div id="treePreview">
<?=getCategoriesViewInOwner($categories, $selected, getCategoriesSkin())?>
											</div>
											
											<div id="property-box">
												<dl class="line">
													<dt><label for="newCategory"><span class="text"><?=_t('생성')?></span></label><span class="divider"> | </span></dt>
<?
if ($depth <= 1) {
?>
													<dd>
														<input type="text" id="newCategory" class="text-input" name="newCategory" onkeyup="if (event.keyCode == 13 && validateText(this.value)){addCategory()}" />
														<a class="add-button button" href="#void" onclick="addCategory()"><span class="text"><?=_t('추가하기')?></span></a>
														<p class="explain">
															<?=_f('"%1"의 하위에 새 분류를 생성합니다.', htmlspecialchars("$name"))?>
														</p>
													</dd>
<?
} else {
?>
													<dd><p class="explain"><?=_t('분류는 2단까지 허용됩니다.')?></p></dd>
<?
}
?>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><label for="modifyCategoryName"><span class="text"><?=_t('레이블 변경')?></span></label><span class="divider"> | </span></dt>
													<dd>
														<input type="text" id="modifyCategoryName" class="text-input" name="modifyCategoryName" onkeyup="if (event.keyCode == '13' && validateText(this.value)) modifyCategory();" value="<?=$name?>" />
														<a class="save-button button" href="#void" onclick="modifyCategory(); return false;"><span class="text"><?=_t('저장하기')?></span></a>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><label for="modifyCategoryBodyId"><span class="text"><?=_t('Body Id 변경')?></span></label><span class="divider"> | </span></dt>
													<dd>
														<input type="text" id="modifyCategoryBodyId" class="text-input" name="modifyCategoryBodyId" onkeyup="if (event.keyCode == '13' && validateText(this.value)) modifyCategory();" value="<?=$bodyid?>" />
														<a class="save-button button" href="#void" onclick="modifyCategory(); return false;"><span class="text"><?=_t('저장하기')?></span></a>
													<p class="explain"><?=_t('Body id는 블로그의 CSS 활용을 위해 사용합니다.')?></p>
													</dd>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span class="text"><?=_t('정렬순서 변경')?></span><span class="divider"> | </span></dt>

<?
if ($selected > 0) {
?>						  
													<dd>
														<a class="up-button button" href="#void" onclick="moveCategory('up');"><span class="text"><?=_t('위로')?></span></a><span class="divider"> | </span><a class="down-button button" href="#void" onclick="moveCategory('down');"><span class="text"><?=_t('아래로')?></span></a>
													</dd>
<?
} else {
?>
													<dd><p class="explain"><?=_t('최상단 분류는 이동할 수 없습니다.')?></p></dd>
<?
}
?>
													<dd class="clear"></dd>
												</dl>
												<dl class="line">
													<dt><span class="text"><?=_t('분류 삭제')?></span><span class="divider"> | </span></dt>
													<dd>
<?
if ($selected == 0) {
	echo _t('최상단 분류는 삭제할 수 없습니다.');
} else if (getNumberEntryInCategories($selected) > 0) {
	echo _t('분류에 등록된 글이 있으므로 삭제할 수 없습니다.');
} else if (getNumberChildCategory($selected) > 0) {
	echo _t('하위 분류가 있으므로 삭제할 수 없습니다.');
} else {
?>
														<a class="remove-button button" href="#void" onclick="removeCategory();"><span class="text"><?=_t('삭제하기')?></span></a>
<?
}
?>
													</dd>
													<dd class="clear"></dd>
												</dl>
											</div>
										</div>
									</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>
