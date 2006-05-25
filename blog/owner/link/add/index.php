<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu21.php';
?>
									<script type="text/javascript">
										//<![CDATA[
											function getSiteInfo() {
												if(document.forms[0].rss.value == '') {
													alert("<?=_t('RSS 주소를 입력해 주세요.')?>\t");
													return false;		
												}

												if(document.forms[0].rss.value.indexOf("http://")==-1) {
													uri = 'http://'+document.forms[0].rss.value;
												} else {
													uri = document.forms[0].rss.value;
												}
												var request = new HTTPRequest("GET", "<?=$blogURL?>/owner/link/site/?rss=" + uri);
												request.onVerify = function() {
													return (this.getText("/response/url") != "")
												}
												request.onSuccess = function () {
													PM.removeRequest(this);
													document.forms[0].name.value = this.getText("/response/name");
													document.forms[0].url.value = this.getText("/response/url");
													return true;
												}
												request.onError = function () {
													PM.removeRequest(this);
													alert("<?=_t('RSS를 읽어올 수 없습니다.')?>");
													return false;
												}
												PM.addRequest(request, "<?=_t('RSS를 읽어오고 있습니다.')?>");
												request.send();
											}

											function addLink() {
												var oForm = document.forms[0];
												trimAll(oForm);
												if (!checkValue(oForm.name, "<?=_t('이름을 입력해 주십시오.')?>\t")) return false;
												if (!checkValue(oForm.url, "<?=_t('주소를 입력해 주세요.')?>\t")) return false;

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
															alert("<?=_t('이미 존재하는 주소입니다.')?>");
															break;
														default:
															alert("<?=_t('알 수 없는 에러가 발생했습니다.')?>");
													}
												}
												PM.addRequest(request, "<?=_t('링크를 추가하고 있습니다.')?>");
												request.send("name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) + "&rss=" + encodeURIComponent(oForm.rss.value));
											}	
										//]]>
									</script>
									
									<div id="part-link-add" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('링크 정보를 설정합니다')?></span></h2>
										
										<div class="data-inbox">
											<dl class="line">
												<dt><label for="rss"><span class="text"><?=_t('RSS 주소')?></span></label><span class="divider"> | </span></dt>
												<dd><input type="text" id="rss" class="text-input rss" name="rss" /> <a class="get-info-button button" href="#void" onclick="getSiteInfo();"><span class="text"><?=_t('정보가져오기')?></span></a></dd>
												<dd class="clear"></dd>
											</dl>
											<dl class="line">
												<dt><label for="name"><span class="text"><?=_t('홈페이지 제목')?></span></label><span class="divider"> | </span></dt>
												<dd><input type="text" id="name" class="text-input name" name="name" /></dd>
												<dd class="clear"></dd>
											</dl>
											<dl class="line">
												<dt><label for="url"><span class="text"><?=_t('홈페이지 주소')?></span></label><span class="divider"> | </span></dt>
												<dd><input type="text" id="url" class="text-input url" name="url" /></dd>
												<dd class="clear"></dd>
											</dl>
											<div class="clear"></div>
										</div>
									</div>
									
									<hr class="hidden" />
									
									<div class="button-box">
										<a class="add-button button" href="#void" onclick="addLink()"><span class="text"><?=_t('추가하기')?></span></a>
										<span class="hidden">|</span>
										<a class="cancel-button button" href="<?=$blogURL?>/owner/link"><span class="text"><?=_t('취소하기')?></span></a>
										<div class="clear"></div>
									</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?> 