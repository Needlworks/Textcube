			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 공지관리')?></span></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li class="list selected"><a href="<?php echo $blogURL?>/owner/notice"><span class="text"><?php echo _t('공지를 봅니다')?></span></a></li>
						<li class="add"><a href="<?php echo $blogURL?>/owner/notice/post"><span class="text"><?php echo _t('공지를 추가합니다')?></span></a></li>
						<li class="helper"><a href="http://www.tattertools.com/doc/9" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?php echo $blogURL?>/owner/notice">
							<input type="hidden" name="page" value="<?php echo $suri['page']?>" />
							
							<div id="data-outbox">
