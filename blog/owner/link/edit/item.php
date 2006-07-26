<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!$link = getLink($owner, $suri['id']))
	respondErrorPage(_t('링크 정보가 존재하지 않습니다.'));
$method = empty($link['rss']) ? 1 : 0;
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu20.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function getSiteInfo() {
									if(document.getElementById('editForm').rss.value == '') {
										alert("<?php echo _t('RSS 주소를 입력해 주십시오.')?>\t");
										return false;		
									}
							
									if(document.getElementById('editForm').rss.value.indexOf('http://')==-1) {
										uri = 'http://'+document.getElementById('editForm').rss.value;
									} else {
										uri = document.getElementById('editForm').rss.value;
									}
									var request = new HTTPRequest("GET", "<?php echo $blogURL?>/owner/link/site/?rss=" + uri);
									request.onVerify = function() {
										return (this.getText("/response/url") != "")
									}
									request.onSuccess = function () {
										document.getElementById('editForm').name.value = this.getText("/response/name");
										document.getElementById('editForm').url.value = this.getText("/response/url");
										return true;
									}
									request.onError = function () {
										return false;
									}
									request.send();
								}
								
								function updateLink() {
									var oForm = document.getElementById('editForm');
									trimAll(oForm);
									if (!checkValue(oForm.name, "<?php echo _t('이름을 입력해 주십시오.')?>\t")) return false;
									if (!checkValue(oForm.url, "<?php echo _t('주소를 입력해 주십시오.')?>\t")) return false;
										var request = new HTTPRequest("POST", blogURL + "/owner/link/edit/exec/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										window.location = blogURL + "/owner/link";
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.')?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크를 수정하고 있습니다.')?>");
									request.send("id=<?php echo $suri['value']?>&name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) + "&rss=" + encodeURIComponent(oForm.rss.value));  
								}
							//]]>
						</script>
						
						<input type="hidden" name="id" value="<?php echo $suri['value']?>" />
						
						<div id="part-link-edit" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('링크 정보를 수정합니다')?></span></h2>
							
							<form id="editForm" method="post" action="<?php echo  $blogURL?>/owner/link/edit/">
								<input type="hidden" name="id" value="<?php echo $suri['value']?>" />
								
								<div class="data-inbox">
									<dl id="rss-address-line" class="line">
										<dt><label for="rss"><?php echo _t('<acronym title="Rich Site Summary">RSS</acronym> 주소')?></label></dt>
										<dd><input type="text" class="text-input" id="rss" name="rss" value="<?php echo $link['rss']?>" /> <a class="get-info-button button" href="#void" onclick="getSiteInfo();"><span class="text"><?php echo _t('정보가져오기')?></span></a></dd>
									</dl>
									<dl id="homepage-title-line" class="line">
										<dt><label for="name"><?php echo _t('홈페이지 제목')?></label></dt>
										<dd><input type="text" class="text-input" id="name" name="name" value="<?php echo htmlspecialchars($link['name'])?>" /></dd>
									</dl>
									<dl id="homepage-address-line" class="line">
										<dt><label for="url"><?php echo _t('홈페이지 주소')?></label></dt>
										<dd><input type="text" class="text-input" id="url" name="url" value="<?php echo htmlspecialchars($link['url'])?>" /></dd>
									</dl>
								</div>
								
								<div class="button-box">
									<a class="edit-button button" href="#void" onclick="updateLink()"><span class="text"><?php echo _t('저장하기')?></span></a>
									<span class="hidden">|</span>
									<a class="cancel-button button" href="<?php echo $blogURL?>/owner/link"><span class="text"><?php echo _t('취소하기')?></span></a>
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>