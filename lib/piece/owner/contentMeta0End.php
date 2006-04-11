                <td align="right">
                  <table cellspacing="0">
                    <tr>
                      <td class="row"><?=getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0)?></td>
                      <td>
					  
                        <select name="perPage" onchange="document.forms[0].page.value=1; document.forms[0].submit()">					
<?
for ($i = 10; $i <= 30; $i += 5) {
	if ($i == $perPage) {
?>
                           <option value="<?=$i?>" selected="selected"><?=$i?></option>
<?
	} else {
?>
                           <option value="<?=$i?>"><?=$i?></option>
<?
	}
}
?>                        </select>
                      </td>
                      <td class="row"><?=getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1)?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
