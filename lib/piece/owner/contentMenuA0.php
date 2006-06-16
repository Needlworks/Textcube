			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 센터')?></span></h2>
				
				<div id="sub-menu-outbox">
					<ul id="sub-menu">
						<li id="sub-menu-dashboard" class="selected"><a href="<?=$blogURL?>/owner/center/dashboard"><span class="text"><?php echo _t('알림판')?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/6" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<form method="post" action="<?=$blogURL?>/owner/center/dashboard">
						<div id="data-outbox">
