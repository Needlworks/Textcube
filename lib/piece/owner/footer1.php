					</div>
				</div>
			</div>
			
			<hr class="hidden" />
			
			<div id="layout-footer">
				<div id="copyright"><span class="text"><?php echo TATTERTOOLS_COPYRIGHT;?></span></div>
				<div id="version"><span class="text"><?php echo TATTERTOOLS_NAME;?> <?php echo TATTERTOOLS_VERSION;?></span></div>
			</div>
		</div>
	</div>
	
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
<?php
if (!defined('__TATTERTOOLS_EDIT__')) { ?>
					case 81: //Q
						try { window.location = "<?php echo $blogURL;?>/"; } catch(e) { };
						break;
					case 82: //R
						try { window.location = "<?php echo $blogURL;?>/owner/reader"; } catch(e) { };
						break;
<?php 
}
if (defined('__TATTERTOOLS_READER__')) {
?>
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
<?php 
}
if (isset($paging['prev'])) {
?>
					case 65: //A
						window.location = "<?php echo "{$paging['url']}{$paging['prefix']}{$paging['prev']}{$paging['postfix']}";?>";
						break;
<?php 
}
if (isset($paging['next'])) {
?>
					case 83: //S
						window.location = "<?php echo "{$paging['url']}{$paging['prefix']}{$paging['next']}{$paging['postfix']}";?>";
						break;
<?php 
}
?>
					default:
				}
			}
		//]]>
	</script>
</body>
</html>
