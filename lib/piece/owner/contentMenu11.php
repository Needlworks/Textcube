      <table width="100%" cellspacing="0">
        <tr>
          <td valign="bottom" style="padding-left:10px">
            <table cellspacing="0">
              <tr>
               
				
                <td style="padding:0px 0px 0px 3px"><img src="<?php echo  $blogURL?>/image/owner/subTabLeftSide.gif" width="4" height="26" alt="" /></td>
                <td class="pointerCursor" onclick="window.location.href = '<?php echo  $blogURL?>/owner/keyword'" nowrap="nowrap" style="font-size:13px; color:#FFFFFF; padding:3px 4px 0px 4px; font-weight:bold; background-color:#EAEBEC; background-image:url('<?php echo  $blogURL?>/image/owner/subTabCenter.gif')" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?php echo  _t('키워드를 봅니다')?></td>
                <td><img src="<?php echo  $blogURL?>/image/owner/subTabRightSide.gif" alt="" /></td>
				
				 <td style="padding:0px 0px 0px 3px"><img src="<?php echo  $blogURL?>/image/owner/subTabActiveLeftSide.gif" width="4" height="26" alt="" /></td>
                <td class="pointerCursor" onclick="window.location.href = '<?php echo  $blogURL?>/owner/keyword/post'" nowrap="nowrap" style="font-size:13px; color:#00A7DE; padding:3px 4px 0px 4px; font-weight:bold; background-color:#FFFFFF" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?php echo  _t('::키워드를 새로 추가합니다::')?></td>
                <td><img src="<?php echo  $blogURL?>/image/owner/subTabActiveRightSide.gif" width="4" height="26" alt="" /></td>
              </tr>
            </table>
          </td>
          <td align="right" valign="bottom">
            <table class="pointerCursor" cellspacing="0" onclick="return false">
              <tr>
                <td style="color:#FFFFFF; padding:0px 4px 4px 10px" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" nowrap="nowrap"><?php echo  _t('도우미')?></td>
                <td style="vertical-align:top"><img src="<?php echo  $blogURL?>/image/owner/iconHelp.gif" width="14" height="14" alt="" /></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table cellspacing="0" style="width:100%">
        <tr>
          <td style="width:7px; height:7px"><img width="7" height="7" src="<?php echo  $blogURL?>/image/owner/roundEdgeLeftTop.gif" alt="" /></td>
          <td width="100%" bgcolor="#FFFFFF"><img width="1" height="1" src="<?php echo  $blogURL?>/image/owner/spacer.gif" alt="" /></td>
          <td style="width:7px; height:7px"><img width="7" height="7" src="<?php echo  $blogURL?>/image/owner/roundEdgeRightTop.gif" alt="" /></td>
        </tr>
      </table>
      <table cellspacing="0" style="width:100%; background-color:#FFFFFF">
        <tr>
          <td valign="top" style="height:50px; padding:5px 15px 15px 15px">
		  <form method="post" action="<?php echo  $blogURL?>/owner/keyword">
		    <input type="hidden" name="page" value="<?php echo  $suri['page']?>" />
