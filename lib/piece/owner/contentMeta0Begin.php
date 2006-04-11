            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" /></td>
                      <td style="padding:3px 0px 0px 4px">
                        <table cellspacing="0">
                          <tr>
                            <td class="row"><?=_t('분류')?>: </td>
                            <td>
                              <select name="category" onchange="document.forms[0].page.value=1; document.forms[0].submit()">
                                <option value="0"><?=_t('전체')?></option>
<?
foreach (getCategories($owner) as $category) {
?>
                                <option value="<?=$category['id']?>"<?=($category['id'] == $categoryId ? ' selected="selected"' : '')?>><?=htmlspecialchars($category['name'])?></option>
<?
	foreach ($category['children'] as $child) {
?>
                                <option value="<?=$child['id']?>"<?=($child['id'] == $categoryId ? ' selected="selected"' : '')?>>&nbsp;► <?=htmlspecialchars($child['name'])?></option>
<?
	}
}
?>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
