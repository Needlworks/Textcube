			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 통계보기')?></span></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-visitor" class="selected"><a href="<?php echo $blogURL?>/owner/statistics/visitor"><span class="text"><?php echo _t('방문자 통계를 봅니다')?></span></a></li>
						<li id="sub-menu-referer"><a href="<?php echo $blogURL?>/owner/statistics/referer"><span class="text"><?php echo _t('리퍼러 통계를 봅니다')?></span></a></li>
						<!--li class="storage"><a href="<?php echo $blogURL?>/owner/statistics/storage"><span class="text"><?php echo _t('저장공간 통계를 봅니다')?></span></a></li-->
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/16" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<form method="post" action="<?=$blogURL?>/owner/statistics/visitor/set/0">
						<div id="data-outbox">
