			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 센터');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-dashboard"><a href="<?php echo $blogURL;?>/owner/center/dashboard"><span class="text"><?php echo _t('조각보');?></span></a></li>
						<li id="sub-menu-dashboard-setting"><a href="<?php echo $blogURL;?>/owner/center/setting"><span class="text"><?php echo _t('자투리');?></span></a></li>
						<li id="sub-menu-about" class="selected"><a href="<?php echo $blogURL;?>/owner/center/about"><span class="text"><?php echo _t('텍스트큐브는');?></span></a></li>
						<li id="sub-menu-helper"><a href="<?php echo getHelpURL('center/about');?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
