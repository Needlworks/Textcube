<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$links = getLinks($owner);
require ROOT . '/lib/piece/owner/header2.php';
require ROOT . '/lib/piece/owner/contentMenu20.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function deleteLink(id) {
									if (!confirm("<?php echo _t('링크를 삭제하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/link/delete/" + id);
									request.onSuccess = function () {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('링크가 삭제되었습니다.');?>", "center", "bottom");
										var node = document.getElementById("link_" + id);
										node.parentNode.removeChild(node);
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크를 삭제하고 있습니다.');?>");
									request.send();
								}
							//]]>
						</script>
						
						<div id="part-link-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('링크 목록입니다');?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="homepage"><span class="text"><?php echo _t('홈페이지 이름');?></span></th>
										<th class="address"><span class="text"><?php echo _t('사이트 주소');?></span></th>
										<th class="edit"><span class="text"><?php echo _t('수정');?></span></th>
										<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
									</tr>
								</thead>
<?php
if (sizeof($links) > 0) echo "									<tbody>";
for ($i=0; $i<sizeof($links); $i++) {
	$link = $links[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($links) - 1) ? ' last-line' : '';
?>
									<tr id="link_<?php echo $link['id'];?>" class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="homepage"><a href="<?php echo $blogURL;?>/owner/link/edit/<?php echo $link['id'];?>" title="<?php echo _t('이 링크 정보를 수정합니다.');?>"><?php echo htmlspecialchars($link['name']);?></a></td>
										<td class="address"><a href="<?php echo htmlspecialchars($link['url']);?>" onclick="window.open(this.href); return false;" title="<?php echo _t('이 링크에 연결합니다.');?>"><?php echo htmlspecialchars($link['url']);?></a></td>
										<td class="edit"><a class="edit-button button" href="<?php echo $blogURL;?>/owner/link/edit/<?php echo $link['id'];?>" title="<?php echo _t('링크 정보를 수정합니다.');?>"><span><?php echo _t('수정');?></span></a></td>
										<td class="delete"><a class="delete-button button" href="<?php echo $blogURL;?>/owner/link/delete/<?php echo $link['id'];?>" onclick="deleteLink(<?php echo $link['id'];?>); return false;" title="<?php echo _t('링크 정보를 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
									</tr>
<?php
}
if (sizeof($links) > 0) echo "									</tbody>";
?>
							</table>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
