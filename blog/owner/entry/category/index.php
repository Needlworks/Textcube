<?php
define('ROOT', '../../../..');
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'deleteCategory' => array('id', 'mandatory' => false),
			'direction' => array(array('up', 'down'), 'mandatory' => false),
			'id' => array('int', 'mandatory' => false),
			'newCategory' => array('string', 'mandatory' => false),
			'modifyCategoryName' => array('string', 'mandatory' => false),
			'modifyCategoryBodyId' => array('string', 'default' => 'category')
		)
	);
}
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
} else if (!empty($_POST['modifyCategoryName']) OR !empty($_POST['modifyCategoryBodyId'])) {
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
$name = getCategoryNameById($owner, $selected) ? getCategoryNameById($owner, $selected) : _t('전체');
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
						<script type="text/javascript">
							//<![CDATA[
								function removeCategory() {
									if(confirm('<?php echo _t('삭제할까요?')?>')) {
										var oform=document.forms[0];  
										oform.deleteCategory.value=<?php echo $selected?>; 
										 
										oform.submit()
									}
								}
								
								function moveCategory(direction) {
									var oform=document.forms[0];
									oform.direction.value=direction
									oform.id.value=<?php echo $selected?>;
									oform.submit()
								}
								
								function addCategory() {
									var oform=document.forms[0];
									oform.id.value=<?php echo $selected?>;
									oform.submit()
								}
								
								function modifyCategory() {
									var oform=document.forms[0];
									oform.id.value=<?php echo $selected?>;
									oform.submit()
								}
								
								window.addEventListener("load", expandTreeInit, false);
								function expandTreeInit() {
									try {
										<?php echo $history?>
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
							<h2 class="caption"><span class="main-text"><?php echo _t('분류를 관리할 수 있습니다')?></span></h2>
							
							<div class="data-inbox">
								<div id="treePreview">
<?php echo getCategoriesViewInOwner(getEntriesTotalCount($owner), $categories, $selected)?>
								</div>
								
								<form class="section" method="post" action="<?php echo $blogURL?>/owner/entry/category">
									<fieldset id="property-box" class="container">
										<legend><?php echo _t('분류 관리 및 설정')?></legend>
										
										<input type="hidden" name="page" value="<?php echo $suri['page']?>" />
										<input type="hidden" name="deleteCategory" />
										<input type="hidden" name="direction" />
										<input type="hidden" name="id" />
																					
										<dl id="label-create-line" class="line">
											<dt><label for="newCategory"><?php echo _t('생성')?></label></dt>
<?php
if ($depth <= 1) {
?>
											<dd>
												<div class="field-box">
													<input type="text" id="newCategory" class="text-input" name="newCategory" onkeyup="if (event.keyCode == 13 && validateText(this.value)){addCategory()}" />
													<a class="add-button button" href="#void" onclick="addCategory()"><span class="text"><?php echo _t('추가하기')?></span></a>
												</div>
												<p>
													<?php echo _f('"%1"의 하위에 새 분류를 생성합니다.', htmlspecialchars("$name"))?>
												</p>
											</dd>
<?php
} else {
?>
											<dd><p><?php echo _t('분류는 2단까지 허용됩니다.')?></p></dd>
<?php
}
?>											
										</dl>
										<dl id="label-change-line" class="line">
											<dt><label for="modifyCategoryName"><?php echo _t('레이블 변경')?></label></dt>
											<dd>
												<div class="field-box">
													<input type="text" id="modifyCategoryName" class="text-input" name="modifyCategoryName" onkeyup="if (event.keyCode == '13' && validateText(this.value)) modifyCategory();" value="<?php echo $name?>" />
													<a class="save-button button" href="#void" onclick="modifyCategory(); return false;"><span class="text"><?php echo _t('저장하기')?></span></a>
												</div>
											</dd>
										</dl>
										<dl id="body-id-line" class="line">
											<dt><label for="modifyCategoryBodyId"><?php echo _t('Body Id 변경')?></label></dt>
											<dd>
												<div class="field-box">
													<input type="text" id="modifyCategoryBodyId" class="text-input" name="modifyCategoryBodyId" onkeyup="if (event.keyCode == '13' && validateText(this.value)) modifyCategory();" value="<?php echo $bodyid?>" />
													<a class="save-button button" href="#void" onclick="modifyCategory(); return false;"><span class="text"><?php echo _t('저장하기')?></span></a>
												</div>
												<p><?php echo _t('Body id는 블로그의 <acronym title="Cascading Style Sheet">CSS</acronym> 활용을 위해 사용합니다.')?></p>
											</dd>
										</dl>
										<dl id="label-move-line" class="line">
											<dt><span class="label"><?php echo _t('정렬순서 변경')?></span></dt>
<?php
if ($selected > 0) {
?>
											<dd>
												<div class="field-box">
													<a class="up-button button" href="#void" onclick="moveCategory('up');"><span class="text"><?php echo _t('위로')?></span></a><span class="divider"> | </span><a class="down-button button" href="#void" onclick="moveCategory('down');"><span class="text"><?php echo _t('아래로')?></span></a>
												</div>
											</dd>
<?php
} else {
?>
											<dd><p><?php echo _t('최상단 분류는 이동할 수 없습니다.')?></p></dd>
<?php
}
?>												
										</dl>
										<dl id="label-remove-line" class="line">
											<dt><span class="label"><?php echo _t('분류 삭제')?></span></dt>
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
													<a class="remove-button button" href="#void" onclick="removeCategory();"><span class="text"><?php echo _t('삭제하기')?></span></a>
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
require ROOT . '/lib/piece/owner/footer1.php';
?>
