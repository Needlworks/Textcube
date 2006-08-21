			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 환경설정');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-blog"><a href="<?php echo $blogURL;?>/owner/setting/blog"><span class="text"><?php echo _t('기본 설정');?></span></a></li>
						<li id="sub-menu-account" class="selected"><a href="<?php echo $blogURL;?>/owner/setting/account"><span class="text"><?php echo _t('계정 정보');?></span></a></li>
						<li id="sub-menu-filter"><a href="<?php echo $blogURL;?>/owner/setting/filter"><span class="text"><?php echo _t('필터');?></span></a></li>
						<li id="sub-menu-data"><a href="<?php echo $blogURL;?>/owner/data"><span class="text"><?php echo _t('데이터 관리');?></span></a></li>
						<li id="sub-menu-etc"><a href="<?php echo $blogURL;?>/owner/setting/etc"><span class="text"><?php echo _t('기타 설정');?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/20" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
