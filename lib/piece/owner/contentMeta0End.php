                <td align="right">
                  <table cellspacing="0">
                    <tr>
                      <td class="row"><?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?></td>
                      <td>
					  
                        <select name="perPage" onchange="document.forms[0].page.value=1; document.forms[0].submit()">					
<?php 
for ($i = 10; $i <= 30; $i += 5) {
	if ($i == $perPage) {
?>
                           <option value="<?php echo $i?>" selected="selected"><?php echo $i?></option>
<?php 
	} else {
?>
                           <option value="<?php echo $i?>"><?php echo $i?></option>
<?php 
	}
}
?>                        </select>
                      </td>
                      <td class="row"><?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1)?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
