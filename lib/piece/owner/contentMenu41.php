			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 통계보기')?></span></h2>
				
				<div id="sub-menu-outbox">
					<ul id="sub-menu">
						<li class="visitor"><a href="<?php echo $blogURL?>/owner/statistics/visitor"><span><?php echo _t('방문자 통계를 봅니다')?></span></a></li>
						<li class="referer selected"><a href="<?php echo $blogURL?>/owner/statistics/referer"><span><?php echo _t('리퍼러 통계를 봅니다')?></span></a></li>
						<!--li class="storage"><a href="<?php echo $blogURL?>/owner/statistics/storage"><span><?php echo _t('저장공간 통계를 봅니다')?></span></a></li-->
						<li class="helper"><a href="#void" onclick="<?php echo 'window.open(\'', _t('http://www.tattertools.com/doc/17'), '\')'; ?>"><span><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?php echo $blogURL?>/owner/statistics">
						
							<div id="data-outbox">
