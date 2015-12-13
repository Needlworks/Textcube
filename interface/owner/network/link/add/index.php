<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
importlib("model.blog.link");

require ROOT . '/interface/common/owner/header.php';


$tabsClass['add'] = true;
?>
						<script type="text/javascript">
							//<![CDATA[
								var RSSread = false;
								function getSiteInfo() {
									if(document.getElementById('addForm').rss.value == '') {
										alert("<?php echo _t('RSS 주소를 입력해 주십시오.');?>\t");
										return false;		
									}

									if(document.getElementById('addForm').rss.value.indexOf("http://")==-1) {
										uri = 'http://'+document.getElementById('addForm').rss.value;
									} else {
										uri = document.getElementById('addForm').rss.value;
									}
									var request = new HTTPRequest("GET", "<?php echo $context->getProperty('uri.blog');?>/owner/network/link/site/?rss=" + uri);
									request.onVerify = function() {
										return (this.getText("/response/url") != "");
									}
									request.onSuccess = function () {
										PM.removeRequest(this);
										document.getElementById('addForm').name.value = unescape(this.getText("/response/name"));
										document.getElementById('addForm').url.value = this.getText("/response/url");
										RSSread = true;
										return true;
									}
									request.onError = function () {
										PM.removeRequest(this);
										PM.showErrorMessage("<?php echo _t('RSS를 읽어올 수 없습니다.');?>","center", "bottom");
										return false;
									}
									PM.addRequest(request, "<?php echo _t('RSS를 읽어오고 있습니다.');?>");
									request.send();
								}
								
								function addLink() {
									var oForm = document.getElementById('addForm');
									var addRSS = false;
									trimAll(oForm);
									if (!checkValue(oForm.name, "<?php echo _t('이름을 입력해 주십시오.');?>\t")) return false;
									if (!checkValue(oForm.url, "<?php echo _t('주소를 입력해 주십시오.');?>\t")) return false;
<?php
if($service['reader'] != false) {
?>
									if (oForm.rss != '' && RSSread == true) {
										if(confirm('<?php echo _t('이 링크의 RSS를 바깥 글 읽기에 추가하시겠습니까?\n추가하면 바깥 글 읽기 메뉴에서 해당 링크의 새 글을 읽을 수 있습니다.');?>'))
											addRSS = true;
									}
<?php
}
?>
									if(addRSS == true) {
										var request = new HTTPRequest("POST", blogURL + "/owner/reader/action/feed/add/");
										request.onSuccess = function () {
										}
										request.onError= function () {
										}
										request.send("group=0&url=" + encodeURIComponent(oForm.rss.value));
									}
									
									var request = new HTTPRequest("POST", blogURL + "/owner/network/link/add/exec/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										window.location = blogURL + "/owner/network/link";
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											case 1:
												alert("<?php echo _t('이미 존재하는 주소입니다.');?>");
												break;
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크를 추가하고 있습니다.');?>");

									request.send("name=" + encodeURIComponent(oForm.name.value) + "&url=" + encodeURIComponent(oForm.url.value) +
									"&rss=" + encodeURIComponent(oForm.rss.value) +
									"&category=" + encodeURIComponent(oForm.category.value) +
									"&newCategory=" + encodeURIComponent(oForm.newCategory.value)
									);
								}	
							//]]>
						</script>
						
						<div id="part-link-add" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('새로운 링크를 추가합니다');?></span></h2>
<?php
require ROOT . '/interface/common/owner/linkTab.php';
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('RSS 주소를 입력해서 링크할 홈페이지의 정보를 읽어올 수 있습니다. 수동으로 제목과 주소를 입력하셔도 됩니다. RSS 주소를 입력해서 홈페이지의 정보를 읽어온 경우 링크를 추가할 때 바깥 글 읽기에 RSS 주소를 추가할지를 물어봅니다.');?></p>
							</div>
								
							<form id="addForm" method="post" action="<?php echo $context->getProperty('uri.blog');?>/owner/network/link/add/">
								<div class="data-inbox">
									<dl id="rss-address-line" class="line">
										<dt><label for="rss"><?php echo _t('<acronym title="Rich Site Summary">RSS</acronym> 주소');?></label></dt>
										<dd><input type="text" id="rss" class="input-text rss" name="rss" /> <input type="button" class="get-info-button input-button" value="<?php echo _t('정보 가져오기');?>" onclick="getSiteInfo();" /></dd>
									</dl>
									<dl id="homepage-title-line" class="line">
										<dt><label for="name"><?php echo _t('홈페이지 제목');?></label></dt>
										<dd><input type="text" id="name" class="input-text name" name="name" /></dd>
									</dl>
									<dl id="homepage-address-line" class="line">
										<dt><label for="url"><?php echo _t('홈페이지 주소');?></label></dt>
										<dd><input type="text" id="url" class="input-text url" name="url" /></dd>
									</dl>
									<dl id="category-line" class="line">
										<dt><label for="url"><?php echo _t('분류');?></label></dt>
										<dd>
											<select id="category" name="category">
											<option value="0"><?php echo _t('분류 없음');?></option>
<?php
$categories = array();
$categories = getLinkCategories(getBlogId());
foreach ($categories as $category) {
?>
											<option value="<?php echo $category['id'];?>"><?php echo htmlspecialchars($category['name']);?></option>
<?php
}
?>
											</select>
										</dd>
										<dd><?php echo _t('또는 새로운 분류를 추가합니다.').' :';?><input type="text" id="newCategory" class="input-text input-category" name="newCategory" /></dd>
									</dl>
								</div>
								
								<div class="button-box">
									<input type="submit" class="add-button input-button" value="<?php echo _t('추가하기');?>" onclick="addLink(); return false" />
									<span class="hidden">|</span>
									<input type="button" class="cancel-button input-button" value="<?php echo _t('취소하기');?>" onclick="window.location.href='<?php echo $context->getProperty('uri.blog');?>/owner/network/link'" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?> 
