<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function setSmtp() {
									var useCustomSMTP = document.getElementById('useCustomSMTP').checked?1:0;
									var smtpHost = document.getElementById('smtpHost').value;
									var smtpPort = document.getElementById('smtpPort').value;
									
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/control/server/mailhost/");
									request.onVerify = function() {
										return this.getText("/response/error") == 0;
									}
									request.onSuccess = function() {
										PM.showMessage("<?php echo _t('저장했습니다');?>", "center", "bottom");
									}
									request.onError = function() {
											alert('<?php echo _t('저장하지 못했습니다');?>');
									}
									request.send("&useCustomSMTP="+useCustomSMTP+"&smtpHost="+encodeURIComponent(smtpHost)+"&smtpPort="+smtpPort);
								}


							//]]>
						</script>

						<div id="part-setting-mailhost" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('메일 보낼 서버를 지정합니다');?></span></h2>
							
							<div class="data-inbox">
								<form class="section" method="post" action="<?php	echo $blogURL;?>/owner/setting/blog/mailhost">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('메일 서버 설정');?></span></dt>
										<dd>

											<div class="line">
												<input id="useCustomSMTP" type="checkbox" class="checkbox" name="useCustomSMTP" value="1" <?php if( getServiceSetting( 'useCustomSMTP', 0 ) ) { echo "checked='checked'"; } ?> />
												<label for="useCustomSMTP"><?php echo _t('메일서버 지정'); ?></label>
											</div>
											<div class="line">
												<label for="smtpHost"><?php echo _t('메일서버 IP 주소:포트'); ?></label>
												<input id="smtpHost" type="text" class="input-text" name="smtpHost" value="<?php echo getServiceSetting( 'smtpHost', '127.0.0.1' ); ?>" /> :
												<input id="smtpPort" type="text" class="input-text" name="smtpPort" value="<?php echo getServiceSetting( 'smtpPort', 25 );?>" />
											</div>
										</dd>
									</dl>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php	echo _t('저장하기');?>" onclick="setSmtp(); return false;" />
									</div>
								</form>
							</div>
						</div>
<?php 
if (isset($_GET['message'])) {
	$msg = escapeJSInCData($_GET['message']);
?>
	<script type="text/javascript">
		//<![CDATA[
			window.onload = function() { PM.showMessage("<?php echo $msg;?>", "center", "bottom"); }
		//]]>
	</script>
<?php
}

require ROOT . '/lib/piece/owner/footer.php';
?>
