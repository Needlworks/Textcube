					</div>
				</div>
			</div>
			
			<hr class="hidden" />
			
			<div id="layout-footer">
				<div id="copyright"><span class="text"><?php echo TEXTCUBE_COPYRIGHT;?></span></div>
				<div id="version"><span class="text"><?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?></span></div>
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
			}
		//]]>
	</script>

	<?php echo fireEvent('ShowAdminFooter', ''); ?>
</body>
</html>
