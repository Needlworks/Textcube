<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

if (!$link = getLinkCategory($blogid, $suri['id']))
	Respond::ErrorPage(_t('링크 정보가 존재하지 않습니다.'));
require ROOT . '/interface/common/owner/header.php';


$tabsClass['categoryEdit'] = true;

?>
						<script type="text/javascript">
							//<![CDATA[
								function updateLinkCategory() {
									var oForm = document.getElementById('editForm');
									trimAll(oForm);
									if (!checkValue(oForm.name, "<?php echo _t('제목을 입력해 주십시오.');?>\t")) return false;
									var request = new HTTPRequest("POST", blogURL + "/owner/network/link/categoryEdit/exec/");
									request.onSuccess = function () {
										PM.removeRequest(this);
										window.location = blogURL + "/owner/network/link";
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크 카테고리를 수정하고 있습니다.');?>");
									request.send("id=<?php echo $suri['id'];?>&name=" + encodeURIComponent(oForm.name.value));  
								}
							//]]>
						</script>
						
						<div id="part-link-category-edit" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('링크 카테고리 정보를 수정합니다');?></span></h2>
<?php
require ROOT . '/interface/common/owner/linkTab.php';
?>
							<form id="editForm" method="post" action="<?php echo $blogURL;?>/owner/network/link/categoryEdit/">
								<input type="hidden" name="id" value="<?php echo $suri['value'];?>" />
								
								<div class="data-inbox">
									<dl id="categoty-title-line" class="line">
										<dt><label for="name"><?php echo _t('제목');?></label></dt>
										<dd><input type="text" class="input-text" id="name" name="name" value="<?php echo htmlspecialchars($link['name']);?>" /></dd>
									</dl>
								</div>
								
								<div class="button-box">
									<input type="submit" class="edit-button input-button" value="<?php echo _t('저장하기');?>" onclick="updateLinkCategory(); return false;" />
									<span class="hidden">|</span>
									<input type="button" class="cancel-button input-button" value="<?php echo _t('취소하기');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/network/link'" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
