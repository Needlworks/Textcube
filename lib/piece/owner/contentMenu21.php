			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 링크관리')?></span></h2>
				
				<div id="sub-menu-outbox">
					<ul id="sub-menu">
						<li class="list"><a href="<?php echo $blogURL?>/owner/link"><span><?php echo _t('목록을 봅니다')?></span></a></li>
						<li class="add selected"><a href="<?php echo $blogURL?>/owner/link/add"><span><?php echo _t('새로운 링크를 추가합니다')?></span></a></li>
						<li class="helper"><a href="#void" onclick="<?php echo 'window.open(\'', _t('http://www.tattertools.com/doc/12'), '\')'; ?>"><span><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?php echo $blogURL?>/owner/link">
							<input type="hidden" name="page" value="<?php echo $suri['page']?>" />
							
							<div id="data-outbox">
