			<div id="layout-body">
				<h2><span class="text"><?php echo _t('팝업 윈도우 : 글수정')?></span></h2>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?php echo $blogURL?>/owner/entry">
							<input type="hidden" name="page" value="<?php echo $suri['page']?>" />
							
							<div id="data-outbox">
