						</form>
					</td>
				</tr>
			</table>
			<table cellspacing="0" style="width:100%">
				<tr>
					<td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftBottom.gif" /></td>
					<td  width="100%" style="background-color:#FFFFFF"><img alt=""height="1" src="<?=$service['path']?>/image/owner/spacer.gif" /></td>
					<td style="width:7px; height:7px"><img alt="" width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightBottom.gif" /></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div style="height:23px; padding-top:10px; background-image:url('<?=$service['path']?>/image/owner/footerLine.gif')">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
  <tr><td style="font:10px Tahoma; color:#fff; padding-left:30px;">
<?=TATTERTOOLS_COPYRIGHT?>. All rights reserved.  </td>
<td align="right" style="font:11px dotum; color:#c2e9ff; padding-right:30px; padding-top:3px;"><?=TATTERTOOLS_NAME?> <?=TATTERTOOLS_VERSION?></td></tr></table></div>
<script type="text/javascript">
//<![CDATA[
	
	document.onkeydown = function(oEvent) {
		if(isIE) {
			oEvent = event;
		}

		if (oEvent.altKey || oEvent.ctrlKey)
			return;
		if(isIE) {
			var nodeName = oEvent.srcElement.nodeName
		} else {
			var nodeName = oEvent.target.nodeName
		}
		switch (nodeName) {
			case "INPUT":
			case "SELECT":
			case "TEXTAREA":
				return;
		}
		switch (oEvent.keyCode) {
<? if (!defined('__TATTERTOOLS_EDIT__')) { ?>
			case 81: //Q
				try { window.location = "<?=$blogURL?>/"; } catch(e) { }
				break;
			case 82: //R
				try { window.location = "<?=$blogURL?>/owner/reader"; } catch(e) { };
				break;
<? } if (defined('__TATTERTOOLS_READER__')) { ?>
			case 65: //A
			case 72: //H
				Reader.prevEntry();
				break;
			case 83: //S
			case 76: //L
				Reader.nextEntry();
				break;
			case 68: //D
				Reader.openEntryInNewWindow();
				break;
			case 70: //F
				Reader.showUnreadOnly();
				break;
			case 71: //G
				Reader.showStarredOnly();
				break;
			case 84: //T
				Reader.updateAllFeeds();
				break;
			case 87: //W
				Reader.toggleStarred();
				break;
			case 74: //J
				window.scrollBy(0, 100);
				break;
			case 75: //K
				window.scrollBy(0, -100);
				break;				
<?
}
if (isset($paging['prev'])) {
?>
			case 65: //A
				window.location = "<?="{$paging['url']}{$paging['prefix']}{$paging['prev']}{$paging['postfix']}"?>";
				break;
<?
}
if (isset($paging['next'])) {
?>
			case 83: //S
				window.location = "<?="{$paging['url']}{$paging['prefix']}{$paging['next']}{$paging['postfix']}"?>";
				break;
<?
}
?>
			default:
		}	
	}
	
//]]>
</script>
</body>
</html>
