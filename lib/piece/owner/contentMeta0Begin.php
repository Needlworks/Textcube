            <table cellspacing="0" width="100%">
              <tr>
                <td>
                  <table cellspacing="0" style="width:100%; height:28px">
                    <tr>
                      <td style="width:18px"><img alt="" src="<?php echo $service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18" /></td>
                      <td style="padding:3px 0px 0px 4px">
                        <table cellspacing="0">
                          <tr>
                            <td class="row"><?php echo _t('분류')?>: </td>
                            <td>
                              <select name="category" onchange="document.forms[0].page.value=1; document.forms[0].submit()">
                                <option value="0"><?php echo _t('전체')?></option>
<?php 
foreach (getCategories($owner) as $category) {
?>
                                <option value="<?php echo $category['id']?>"<?php echo ($category['id'] == $categoryId ? ' selected="selected"' : '')?>><?php echo htmlspecialchars($category['name'])?></option>
<?php 
	foreach ($category['children'] as $child) {
?>
                                <option value="<?php echo $child['id']?>"<?php echo ($child['id'] == $categoryId ? ' selected="selected"' : '')?>>&nbsp;► <?php echo htmlspecialchars($child['name'])?></option>
<?php 
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
