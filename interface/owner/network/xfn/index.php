<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.link");

if( isset($_POST['usexfn']) ) {
	updateXfn( $blogid, $_POST );
	header( "Location: ${_SERVER['REQUEST_URI']}" );
}

$page=1;
if( isset( $_GET['page'] ) ) {
	$page=$_GET['page'];
}

$tabsClass['xfn'] = true;
list( $links, $paging ) = getLinksWithPagingForOwner($blogid, $page, 30);
$service['admin_script'] = array( 'xfn.js' );
require ROOT . '/interface/common/owner/header.php';

?>
						<script type="text/javascript">
							//<![CDATA[
							//]]>
						</script>
						
						<div id="part-link-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('친구 관계를 설정합니다');?></span></h2>
<?php
require ROOT . '/interface/common/owner/linkTab.php';
?>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('각 링크의 관계를 설정합니다.').' '._t('여기서 지정한 링크들의 관계는 XFN (XHTML Friends Network) 규격에 맞추어 블로그의 링크 출력시 추가 데이터로 함께 출력됩니다.');?></p>
							</div>
							<form method="post">
							<input type="hidden" name="usexfn" id="usexfn" value="0" />
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead class="xfn">
									<tr>
										<th class="xfn-homepage"><span class="text"><?php echo _t('홈페이지 이름');?></span></th>
										<th class="xfn-me"><span class="text"><?php echo _t('또다른나');?></span></th>
										<th class="xfn-friend"><span class="text"><?php echo _t('친밀도');?></span></th>
										<th class="xfn-met"><span class="text"><?php echo _t('만남');?></span></th>
										<th class="xfn-professional"><span class="text"><?php echo _t('전문분야');?></span></th>
										<th class="xfn-coresident"><span class="text"><?php echo _t('생활반경');?></span></th>
										<th class="xfn-family"><span class="text"><?php echo _t('가족관계');?></span></th>
										<th class="xfn-romantic"><span class="text"><?php echo _t('애정');?></span></th>
									</tr>
								</thead>
<?php
if (sizeof($links) > 0) {
	echo "									<tbody>";
}
for ($i=0; $i<sizeof($links); $i++) {
	$link = $links[$i];
	$xfn = $link['xfn'];
	$xfn_items = split( ' ', $xfn );

	$check_me         =
	$check_met        =
	$check_coworker   =
	$check_colleague  =
	$check_muse       =
	$check_crush      =
	$check_date       =
	$check_sweetheart =
	$check_contact    =
	$check_friend     =
	$check_acquaintance =
	$check_child      =
	$check_sibling    =
	$check_parent     =
	$check_kin        =
	$check_spouse     =
	$check_coresident =
	$check_neighbor   =
	'';

	foreach( $xfn_items as $item ) {
		$item = str_replace( '-', '', $item );
		${'check_'.$item} = 'checked';
	}

	$check_none_friendship = 
		( $check_contact || $check_friend || $check_acquaintance ) ? '' : 'checked';
	$check_none_family = 
		( $check_child || $check_sibling || $check_parent || $check_kin || $check_spouse ) ? '':'checked';
	$check_none_geographical = 
		( $check_coresident || $check_neighbor ) ? '' : 'checked';

	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($links) - 1) ? ' last-line' : '';
?>
									<tr id="link_id_<?php echo $link['id'];?>" class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="xfn-homepage"><a href="<?php echo $blogURL;?>/owner/network/link/edit/<?php echo $link['id'];?>" title="<?php echo htmlspecialchars($link['url']);?>"><?php echo htmlspecialchars(UTF8::lessen($link['name'],12));?></a>
										<input type="hidden" name="xfn<?php echo $link['id'];?>" id="xfn_id_<?php echo $link['id'];?>" value="<?php echo $xfn; ?>"/>
										</td>
										<td class="xfn-edit">
										<input type="checkbox" name="me<?php echo $link['id'];?>" id="me_id_<?php echo $link['id'];?>" <?php echo $check_me?> />
										</td>
										<td class="xfn-edit">
										<label for="friendship-contact_id_<?php echo $link['id'];?>"><input name="friendship<?php echo $link['id'];?>" value="contact" id="friendship-contact_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_contact; ?> /> <?php echo _t('연락처를 아는')?></label>
										<label for="friendship-aquaintance_id_<?php echo $link['id'];?>"><input name="friendship<?php echo $link['id'];?>" value="acquaintance" id="friendship-aquaintance_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_acquaintance; ?> /> <?php echo _t('일로서 아는');?></label> 
										<label for="friendship-friend_id_<?php echo $link['id'];?>"><input name="friendship<?php echo $link['id'];?>" value="friend" id="friendship-friend_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_friend; ?> /> <?php echo _t('잘 아는'); ?> </label> 
										<label for="friendship-none_id_<?php echo $link['id'];?>"><input name="friendship<?php echo $link['id'];?>" value="" id="friendship-none_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_none_friendship; ?> /> <?php echo _t('모르는'); ?></label>
										</td>
										<td class="xfn-edit">
										<label for="met_id_<?php echo $link['id'];?>"><input type="checkbox" name="met<?php echo $link['id'];?>" id="met_id_<?php echo $link['id'];?>" value="met" <?php echo $check_met;?> /> <?php echo _t('만나본 적있는'); ?></label>
										</td>
										<td class="xfn-edit">
										<label for="coworker_id_<?php echo $link['id'];?>">
										<input type="checkbox" name="coworker<?php echo $link['id'];?>" id="coworker_id_<?php echo $link['id'];?>" value="co-worker" <?php echo $check_coworker ?> />
										<?php echo _t('같이 일하는'); ?>
										</label>
										<label for="colleague_id_<?php echo $link['id'];?>">
										<input type="checkbox" name="colleague<?php echo $link['id'];?>" id="colleague_id_<?php echo $link['id'];?>" value="colleague" <?php echo $check_colleague ?>/>
										<?php echo _t('분야가 같은'); ?>
										</label>
										</td>
										<td class="xfn-edit">
										<label for="co-resident_id_<?php echo $link['id'];?>"><input name="geographical<?php echo $link['id'];?>" value="co-resident" id="co-resident_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_coresident; ?> /> <?php echo _t('같이 사는'); ?></label> 
										<label for="neighbor_id_<?php echo $link['id'];?>"><input name="geographical<?php echo $link['id'];?>" value="neighbor" id="neighbor_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_neighbor; ?> /> <?php echo _t('이웃에 사는'); ?></label> 
										<label for="geographical-none_id_<?php echo $link['id'];?>"><input name="geographical<?php echo $link['id'];?>" value="" id="geographical-none_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_none_geographical; ?> /> <?php echo _t('비공개');?></label>
										</td>
										<td class="xfn-edit">
										<label for="family-child_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="child" id="family-child_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_child; ?> /> <?php echo _t('자녀'); ?></label> 
										<label for="family-parent_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="parent" id="family-parent_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_parent; ?> /> <?php echo _t('부모'); ?></label> 
										<label for="family-sibling_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="sibling" id="family-sibling_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_sibling; ?> /> <?php echo _t('형제,자매'); ?></label> 
										<label for="family-spouse_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="spouse" id="family-spouse_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_spouse;?> /> <?php echo _t('배우자'); ?></label> 
										<label for="family-kin_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="kin" id="family-kin_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_kin; ?> /> <?php echo _t('친척'); ?></label>

										<label for="family-none_id_<?php echo $link['id'];?>"><input name="family<?php echo $link['id'];?>" value="" id="family-none_id_<?php echo $link['id'];?>" type="radio" <?php echo $check_none_family; ?> /> <?php echo _t('관계 없음'); ?></label>
										</td>
										<td class="xfn-edit">
										<label for="muse_id_<?php echo $link['id'];?>"><input name="romantic<?php echo $link['id'];?>" value="muse" id="muse_id_<?php echo $link['id'];?>" type="checkbox" <?php echo $check_muse; ?> /> <?php echo _t('기분좋은') ?></label> 
										<label for="crush_id_<?php echo $link['id'];?>"><input name="romantic<?php echo $link['id'];?>" value="crush" id="crush_id_<?php echo $link['id'];?>" type="checkbox" <?php echo $check_crush; ?> /> <?php echo _t('매력적인');?></label> 
										<label for="date_id_<?php echo $link['id'];?>"><input name="romantic<?php echo $link['id'];?>" value="date" id="date_id_<?php echo $link['id'];?>" type="checkbox" <?php echo $check_date; ?> /> <?php echo _t('만나는'); ?></label> 
										<label for="sweetheart_id_<?php echo $link['id'];?>"><input name="romantic<?php echo $link['id'];?>" value="sweetheart" id="sweetheart_id_<?php echo $link['id'];?>" type="checkbox" <?php echo $check_sweetheart; ?> /> <?php echo _t('오직하나뿐인') ?></label>
										</td>
										          
									</tr>
<?php
}
if (sizeof($links) > 0) echo "									</tbody>";
?>
							</table>
							<div class="button-box">
								<input type="submit" class="edit-button input-button" value="<?php echo _t('저장하기');?>" />
								<span class="hidden">|</span>
								<input type="button" class="cancel-button input-button" value="<?php echo _t('취소하기');?>" onclick="window.location.href='<?php echo $blogURL;?>/owner/network/link/xfn'" />
							</div>
							</form>

							<div id="page-section" class="section">
								<div id="page-navigation">
									<span id="page-list">
<?php
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
									</span>
									<span id="total-count"><?php echo sprintf(_t('총 %d건'), empty($paging['total']) ? "0" : $paging['total']);?></span>
								</div>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
