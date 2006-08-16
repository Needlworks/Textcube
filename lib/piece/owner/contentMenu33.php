			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 스킨관리');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-list"><a href="<?php echo $blogURL;?>/owner/skin"><span class="text"><?php echo _t('스킨 선택');?></span></a></li>
						<li id="sub-menu-edit"><a href="<?php echo $blogURL;?>/owner/skin/edit"><span class="text"><?php echo _t('스킨 편집');?></span></a></li>
						<li id="sub-menu-option"><a href="<?php echo $blogURL;?>/owner/skin/setting"><span class="text"><?php echo _t('출력 설정');?></span></a></li>
						<li id="sub-menu-option" class="selected"><a href="<?php echo $blogURL;?>/owner/skin/sidebar"><span class="text"><?php echo _t('사이드바');?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/15" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
