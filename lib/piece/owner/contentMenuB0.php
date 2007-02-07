			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 플러그인');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-plugin"<?php echo ((!isset($_GET['name'])) ? ' class="selected"' : '');?>><a href="<?php echo $blogURL;?>/owner/plugin"><span class="text"><?php echo _t('플러그인 목록');?></span></a></li>
<?php

foreach($adminMenuMappings as $path => $adminMenuitem)
{
	$selected = (isset($_GET['name']) && ($_GET['name'] == $path)) ? ' class="selected"' : '';
?>
						<li <?php echo $selected;?>><a href="<?php echo $blogURL;?>/owner/plugin/adminMenu?name=<?php echo $path;?>"><span class="text"><?php echo $adminMenuitem['title'];?></span></a></li>
	<?php
}
?>
						<li id="sub-menu-plugin-tablesetting"><a href="<?php echo $blogURL;?>/owner/plugin/tableSetting"><span class="text"><?php echo _t('플러그인 데이터 관리');?></span></a></li>
						<li id="sub-menu-helper"><a href="<?php echo getHelpURL('plugin');?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
