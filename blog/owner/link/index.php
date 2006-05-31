<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$links = getLinks($owner);
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu20.php';
?>
									<script type="text/javascript">
										//<![CDATA[
											function deleteLink(id) {
												if (!confirm("<?=_t('링크를 삭제하시겠습니까?')?>"))
													return;

												var request = new HTTPRequest("GET", blogURL + "/owner/link/delete/" + id);
												request.onSuccess = function () {
													PM.removeRequest(this);
													PM.showMessage("<?=_t('링크가 삭제되었습니다.')?>", "center", "bottom");
													var node = document.getElementById("link_" + id);
													node1 = node.nextSibling;
													if(node1)
														node1.parentNode.removeChild(node1);
													node.parentNode.removeChild(node);
												}
												request.onError= function () {
													PM.removeRequest(this);
													switch(parseInt(this.getText("/response/error")))
													{
														default:
															alert("<?=_t('알 수 없는 에러가 발생했습니다.')?>");
													}
												}
												PM.addRequest(request, "<?=_t('링크를 삭제하고 있습니다.')?>");
												request.send();
											}
										//]]>
									</script>
									
									<div id="part-link-list" class="part">
										<h2 class="caption"><span class="main-text"><?=_t('링크 목록입니다')?></span></h2>
										
										<table class="data-inbox" cellspacing="0" cellpadding="0">
											<thead>
												<tr>
													<td class="homepage"><span class="text"><?=_t('홈페이지 이름')?></span></td>
													<td class="address"><span class="text"><?=_t('사이트 주소')?></span></td>
													<!--td class="edit"><span class="text"><?=_t('수정')?></span></td-->
													<td class="delete"><span class="text"><?=_t('삭제')?></span></td>
												</tr>
											</thead>
											<tbody>
<?
for ($i=0; $i<sizeof($links); $i++) {
	$link = $links[$i];
	
	if ($i == sizeof($links) - 1) {
?>
												<tr id="link_<?=$link['id']?>" class="tr-last-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
<?
	} else {
?>
												<tr id="link_<?=$link['id']?>" class="tr-body inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
<?
	}
?>
    												<td class="homepage"><a href="<?=$blogURL?>/owner/link/edit/<?=$link['id']?>" title="<?=_t('이 링크 정보를 수정합니다.')?>"><?=htmlspecialchars($link['name'])?></a></td>
													<td class="address"><a href="<?=htmlspecialchars($link['url'])?>" onclick="window.open(this.href); return false;" title="<?=_t('이 링크에 연결합니다.')?>"><?=htmlspecialchars($link['url'])?></a></td>
													<!--td class="edit"><a class="edit-button button" href="<?=$blogURL?>/owner/link/edit/<?=$link['id']?>" title="<?=_t('링크 정보를 수정합니다.')?>"><span><?=_t('수정')?></span></a></td-->
													<td class="delete"><a class="delete-button button" href="#void" onclick="deleteLink(<?=$link['id']?>)" title="<?=_t('링크 정보를 삭제합니다.')?>"><span class="text"><?=_t('삭제')?></span></a></td>
												</tr>
<?
}
?>
											</tbody>
										</table>
									</div>
<?
require ROOT . '/lib/piece/owner/footer0.php';
?>
