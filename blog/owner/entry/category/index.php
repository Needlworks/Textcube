<?
define('ROOT', '../../../..');
if(count($_POST) > 0) {
	$IV = array(
		'POST' => array(
			'deleteCategory' => array('id', 'mandatory' => false),
			'direction' => array(array('up', 'down'), 'mandatory' => false),
			'id' => array('int', 'mandatory' => false),
			'newCategory' => array('string', 'mandatory' => false),
			'modifyCategoryName' => array('string', 'mandatory' => false)
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
else if(!empty($_POST['id']))
	$depth = getParentCategoryId($owner, $_POST['id']) ? 2 : 1;
else
	$depth = 0;
if (empty($_GET['entries']) || $_GET['entries'] == 0)
	$entries = 0;
else
	$entries = $_GET['entries'];
if (!empty($_POST['newCategory'])) {
	$history = addCategory($owner, ($selected == 0) ? null : $_POST['id'], trim($_POST['newCategory'])) ? 'document.getElementById("newCategory").select();' : '';
} else if (!empty($_POST['modifyCategoryName']) && ($selected > 0)) {
	$history = modifyCategory($owner, $_POST['id'], trim($_POST['modifyCategoryName'])) ? 'document.getElementById("modifyCategoryName").select();' : '';
} else {
	$history = '';
}
$categories = getCategories($owner);
$name = getCategoryNameById($owner, $selected);
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

			<input type="hidden" name="deleteCategory"/>
			<input type="hidden" name="direction"/>
			<input type="hidden" name="id"/>

            <table cellspacing="0" style="background-color:#EBF2F8; width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">				
              <tr valign="top">
			  	<td width="300" valign="top" style="padding:5px">
				<div style="height: 100%; border: 1px solid #ccc; background-color: #fff; padding: 10px; min-height: 200px;">
				<?=getCategoriesViewInOwner(getEntriesTotalCount($owner), $categories, $selected)?>
				</div>
				</td>
                <td style="padding:10px 5px 10px 5px">
                  <table cellspacing="0">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('생성')?> |</td>
<?
if ($depth <= 1) {
?>
                      <td>
                        <input name="newCategory" type="text" class="text1" id="newCategory" style="width:140px;" onkeyup="if (event.keyCode == 13 && validateText(this.value)){addCategory()}" />
                      </td>
                      <td style="padding-left:5px;">
                        <table class="buttonTop" cellspacing="0" onclick="addCategory()">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('추가하기')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <div style="padding:5px 0px 0px 135px">
					<?=_f('"%1"의 하위에 새 분류를 생성합니다', htmlspecialchars("$name"))?>
				  </div>
<?
} else {
	echo '					<td>' . _t('분류는 2단까지 허용됩니다') . '</td>';
}
?>
                  <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                      <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                  </table>

                  <table cellspacing="0" style="margin-top:7px">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('레이블 변경')?> |</td>
                      <td>
				<input name="modifyCategoryName" type="text" class="text1" id="modifyCategoryName" style="width:140px;" onkeyup="if (event.keyCode == '13' && validateText(this.value)) modifyCategory();" value="<?=htmlspecialchars($name)?>" />
                      </td>
                      <td style="padding-left:5px;" onclick="modifyCategory(); return false;">
                        <table class="buttonTop" cellspacing="0">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장하기')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                 </table> 
                  <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                      <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                  </table>
				 
                  <table cellspacing="0" style="margin-top:7px">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('정렬순서 변경')?> |</td>
<?
if ($selected > 0) {
?>						  
                      <td>
                        <table class="buttonTop" cellspacing="0" onclick="moveCategory('up');" border="0">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><img src="<?=$service['path']?>/image/owner/upArrow.gif" alt="" align="absmiddle"/>
                            <?=_t('위로 올리기')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table>
                      </td>
                      <td>
                        <table class="buttonTop" cellspacing="0" onclick="moveCategory('down');">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><img src="<?=$service['path']?>/image/owner/downArrow.gif" alt="" align="absmiddle"/>
                            <?=_t('아래로 내리기')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table> 
                      </td>
<?
} else {
	echo '					<td>' . _t('최상단 분류는 이동할 수 없습니다') . '</td>';
}
?>					  
                    </tr>
                  </table>
                  <!--
                  <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                      <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                  </table>
				
                  <table cellspacing="0" style="margin-top:7px">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('글 이동')?> |</td>
                      <td>
                        <table class="buttonTop" cellspacing="0" onclick="window.open('post_pop.php?view=3&amp;node=1','post_pop','width=500, height=450, scrollbars=1');; return false;">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('글 이동창 띄우기')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
				  -->
                  <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                      <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt="" src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                  </table>
                  <table cellspacing="0" style="margin-top:7px">
                    <tr>
                      <td class="entryEditTableLeftCell"><?=_t('분류 삭제')?> |</td>
                      <td>
<?
if ($selected == 0) {
	echo _t('최상단 분류는 삭제할 수 없습니다');
} else if (getNumberEntryInCategories($selected) > 0) {
	echo _t('분류에 등록된 글이 있으므로 삭제할 수 없습니다');
} else if (getNumberChildCategory($selected) > 0) {
	echo _t('하위 분류가 있으므로 삭제할 수 없습니다');
} else {
?>
						<table class="buttonTop" cellspacing="0" onclick="removeCategory();">
                          <tr>
                            <td><img alt="" width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                            <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('삭제')?></td>
                            <td><img alt="" width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                          </tr>
                        </table>
<?
}
?>
				      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
	
<?
require ROOT . '/lib/piece/owner/footer.php';
?>