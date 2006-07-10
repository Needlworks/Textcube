			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 스킨관리')?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-list" class="selected"><a href="<?php echo $blogURL?>/owner/skin"><span class="text"><?php echo _t('스킨을 선택합니다')?></span></a></li>
						<li id="sub-menu-edit"><a href="<?php echo $blogURL?>/owner/skin/edit"><span class="text"><?php echo _t('선택한 스킨을 편집합니다')?></span></a></li>
						<li id="sub-menu-option"><a href="<?php echo $blogURL?>/owner/skin/setting"><span class="text"><?php echo _t('스킨에 맞춘 출력을 설정합니다')?></span></a></li>
						<li id="sub-menu-helper"><a href="<?php echo _t(TATTERTOOLS_HOMEPAGE.'/doc/14')?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
