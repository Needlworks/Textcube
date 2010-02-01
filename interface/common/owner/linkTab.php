							<ul id="link-tabs-box" class="tabs-box">
								<li<?php echo isset($tabsClass['add']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/network/link/add"><?php echo _t('링크 추가');?></a></li>
								<li<?php echo isset($tabsClass['list']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/network/link"><?php echo _t('링크 목록');?></a></li>
								<li<?php echo isset($tabsClass['xfn']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/network/xfn"><?php echo _t('링크 관계 관리');?></a></li>
<?php
if(isset($tabsClass['edit'])) {
?>
								<li<?php echo isset($tabsClass['edit']) ? ' class="selected"' : NULL;?>><a href="#"><?php echo _t('링크 수정');?></a></li>
<?php
}
if(isset($tabsClass['categoryEdit'])) {
?>
								<li<?php echo isset($tabsClass['categoryEdit']) ? ' class="selected"' : NULL;?>><a href="#"><?php echo _t('링크 카테고리 수정');?></a></li>
<?php
}
?>
							</ul>
