			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 휴지통')?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-comment"><a href="<?=$blogURL?>/owner/trash/comment"><span class="text"><?php echo _t('삭제된 댓글')?></span></a></li>
						<li id="sub-menu-trackback"><a href="<?=$blogURL?>/owner/trash/trackback"><span class="text"><?php echo _t('삭제된 트랙백')?></span></a></li>
						<li id="sub-menu-filter" class="selected"><a href="<?=$blogURL?>/owner/trash/filter"><span class="text"><?php echo _t('필터')?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/6" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<div id="data-outbox">
