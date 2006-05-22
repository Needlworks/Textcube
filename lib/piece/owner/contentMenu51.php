			<div id="layout-body">
				<h2><span>환경설정 &gt; 서브메뉴</span></h2>
				
				<div id="sub-menu-outbox">
					<ul id="sub-menu">
						<li class="blog"><a href="<?php echo $blogURL?>/owner/setting/blog"><span><?php echo _t('블로그 환경을 관리합니다')?></span></a></li>
						<li class="account selected"><a href="<?php echo $blogURL?>/owner/setting/account"><span><?php echo _t('계정정보를 관리합니다')?></span></a></li>
						<li class="filter"><a href="<?php echo $blogURL?>/owner/setting/filter"><span><?php echo _t('필터를 관리합니다')?></span></a></li>
						<li class="plugin"><a href="<?php echo $blogURL?>/owner/setting/plugins"><span><?php echo _t('플러그인을 관리합니다')?></span></a></li>
						<li class="data"><a href="<?php echo $blogURL?>/owner/data"><span><?php echo _t('데이터를 관리합니다')?></span></a></li>
						<li class="helper"><a href="#void" onclick="<?php echo 'window.open(\'', _t('http://www.tattertools.com/doc/20'), '\')'; ?>"><span><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?php echo $blogURL?>/owner/setting/account">
							<input type="hidden" name="page" value="<?php echo $suri['page']?>" />
							
							<div id="data-outbox">
