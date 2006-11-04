			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 링크관리');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-add" class="selected"><a href="<?php echo $blogURL;?>/owner/link/add"><span class="text"><?php echo _t('링크 추가');?></span></a></li>
						<li id="sub-menu-list"><a href="<?php echo $blogURL;?>/owner/link"><span class="text"><?php echo _t('링크 목록');?></span></a></li>
						<li id="sub-menu-helper"><a href="<?php echo getHelpURL('link/add');?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
