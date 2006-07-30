<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu21.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function getSiteInfo() {
									if(document.getElementById('addForm').rss.value == '') {
										alert("<?php echo  _t('RSS 주소를 입력해 주십시오.')?>\t");
										return false;		
									}
									
									if(document.getElementById('addForm').rss.value.indexOf("http://")==-1) {
										uri = 'http://'+document.getElementById('addForm').rss.value;
									} else {
										uri = document.getElementById('addForm').rss.value;
									}
									var request = new HTTPRequest("GET", "<?php echo  $blogURL?>/owner/link/site/?rss=" + uri);
									request.onVerify = function() {
										return (this.getText("/response/url") != "")
									}
									request.onSuccess = function () {
										PM.removeRequest(this);
										document.getElementById('addForm').name.value = this.getText("/response/name");
										document.getElementById('addForm').url.value = this.getText("/response/url");
										return true;
									}
									request.onError = function () {
										PM.removeRequest(this);
										alert("<?php echo  _t('RSS를 읽어올 수 없습니다.')?>");
										return false;
									}
									PM.addRequest(request, "<?php echo  _t('RSS를 읽어오고 있습니다.')?>");
									request.send();
								}
								
								function addLink() {
									var oForm = document.getElementById('addForm');
									trimAll(oForm);
									if (!checkValue(oForm.name, "<?php echo  _t('이름을 입력해 주십시오.')?>\t")) return false;
									if (!checkValue(oForm.url, "<?php echo  _t('주소를 입력해 주십시오.')?>\t")) return false;
									
									var request = new HTTPRequest("POST", blogURL + "/owner/link/add/exec/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										window.location = blogURL + "/owner/link";
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											case 1:
												alert("<?php echo  _t('이미 존재하는 주소입니다.')?>");
												break;
											default:
												alert("<?php echo  _t('알 수 없는 에러가 발생했습니다.')?>");
										}
									}
									PM.addRequest(request, "<?php echo  _t('링크를 추가하고 있습니다.')?>");
									request.send("name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) + "&rss=" + encodeURIComponent(oForm.rss.value));
								}	
							//]]>
						</script>
						
						<div id="part-link-add" class="part">
							<h2 class="caption"><span class="main-text"><?php echo  _t('링크 정보를 추가합니다')?></span></h2>
								
							<form id="addForm" method="post" action="<?php echo  $blogURL?>/owner/link/add/">
								<div class="data-inbox">
									<dl id="rss-address-line" class="line">
										<dt><label for="rss"><?php echo  _t('<acronym title="Rich Site Summary">RSS</acronym> 주소')?></label></dt>
										<dd><input type="text" id="rss" class="text-input rss" name="rss" /> <a class="get-info-button button" href="#void" onclick="getSiteInfo();"><span class="text"><?php echo  _t('정보 가져오기')?></span></a></dd>
									</dl>
									<dl id="homepage-title-line" class="line">
										<dt><label for="name"><?php echo  _t('홈페이지 제목')?></label></dt>
										<dd><input type="text" id="name" class="text-input name" name="name" /></dd>
									</dl>
									<dl id="homepage-address-line" class="line">
										<dt><label for="url"><?php echo  _t('홈페이지 주소')?></label></dt>
										<dd><input type="text" id="url" class="text-input url" name="url" /></dd>
									</dl>
								</div>
								
								<div class="button-box">
									<a class="add-button button" href="#void" onclick="addLink()"><span class="text"><?php echo  _t('추가하기')?></span></a>
									<span class="hidden">|</span>
									<a class="cancel-button button" href="<?php echo  $blogURL?>/owner/link"><span class="text"><?php echo  _t('취소하기')?></span></a>
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?> 