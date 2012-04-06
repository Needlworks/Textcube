					</div>
				</div>
			</div>
			
			<hr class="hidden" />
			
			<div id="layout-footer">
				<div id="login-information"><?php echo _f('%1 로 로그인','<strong>'.htmlspecialchars($writer).'</strong>');?></div>
				<address><?php echo TEXTCUBE_COPYRIGHT;?></address>
				<div id="version"><?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?></div>
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		//<![CDATA[
			document.onkeydown = function(oEvent) {
				if(isIE) {
					oEvent = event;
				}

				if (oEvent.altKey || oEvent.ctrlKey || oEvent.metaKey)
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
if (!defined('__TEXTCUBE_EDIT__')) { ?>
					case 81: //Q
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/"; } catch(e) { };
						break;
<?php if ($service['reader']) { ?>
					case 82: //R
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/network/reader"; } catch(e) { };
						break;
<?php
	}
}
if (defined('__TEXTCUBE_READER__')) {
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
					case 49: //1
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/center/dashboard"; } catch(e) { };
						break;
					case 50: //2
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/entry"; } catch(e) { };
						break;
					case 51: //3
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/communication/comment"; } catch(e) { };
						break;
					case 52: //4
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/network/link"; } catch(e) { };
						break;
<?php
if (Acl::check('group.owners')) {
?>
					case 53: //5
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/skin"; } catch(e) { };
						break;
					case 54: //6
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/plugin"; } catch(e) { };
						break;
<?php
}
?>
					case 55: //7
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/setting/blog"; } catch(e) { };
						break;
<?php
if (Acl::check('group.creators')) {
?>
					case 56: //8
						try { window.location = "<?php echo $context->getProperty('uri.blog');?>/owner/control/blog"; } catch(e) { };
						break;
<?php
}
?>
					case 191: //?
						MOOdalBox.open("<?php echo $context->getProperty('uri.default')."/owner/help/?subject=".$blogMenu['topMenu'].'_'.$blogMenu['contentMenu']."&lang=".$blog['language'];?>","","500 300");
						break;
					default:
				}
			}
<?php
	echo activateDetailPanelJS();
?>
	//]]>
	</script>
	<?php echo fireEvent('ShowAdminFooter', ''); ?>
<?php if( $service['type'] == 'domain' && empty($_SESSION['sso_done']) && !empty($blog['secondaryDomain']) ){ 
$_SESSION['sso_done'] = true; ?>
<iframe style="width:0;height:0;border:0" src="<?php echo getBlogURL();?>/login?requestURI=about:blank&session=<?php echo $_COOKIE[Session::getName()];?>"></iframe>
<iframe style="width:0;height:0;border:0" src="<?php echo "http://".$context->getProperty('blog.secondaryDomain').$context->getProperty('uri.blog');?>/login?requestURI=about:blank&session=<?php echo $_COOKIE[Session::getName()];?>"></iframe>
<?php } ?>
</body>
</html>
