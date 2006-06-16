			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 스킨관리')?></span></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-list"><a href="<?php echo $blogURL?>/owner/skin"><span class="text"><?php echo _t('스킨을 선택합니다')?></span></a></li>
						<li id="sub-menu-edit" class="selected"><a href="<?php echo $blogURL?>/owner/skin/edit"><span class="text"><?php echo _t('선택한 스킨을 편집합니다')?></span></a></li>
						<li id="sub-menu-option"><a href="<?php echo $blogURL?>/owner/skin/setting"><span class="text"><?php echo _t('스킨에 맞춘 출력을 설정합니다')?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/14" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<div id="data-outbox">
