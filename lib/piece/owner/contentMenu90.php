			<div id="layout-body">
				<h2><span>휴지통관리 &gt; 서브메뉴</span></h2>
				
				<div id="sub-menu-outbox">
					<ul id="sub-menu">
						<li class="trackback selected"><a href="<?=$blogURL?>/owner/trash/trackback"><span><?php echo _t('트랙백을 관리합니다')?></span></a></li>
						<li class="comment"><a href="<?=$blogURL?>/owner/trash/comment"><span><?php echo _t('댓글을 관리합니다')?></span></a></li>
						<li class="filter"><a href="<?=$blogURL?>/owner/trash/filter"><span><?php echo _t('필터를 관리합니다')?></span></a></li>
						<li class="helper"><a href="#void" onclick="<?php echo 'window.open(\'', _t('http://www.tattertools.com/doc/6'), '\')'; ?>"><span><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?=$blogURL?>/owner/entry/notify">
							<input type="hidden" name="page" value="<?=$suri['page']?>" />
							
							<div id="data-outbox">
