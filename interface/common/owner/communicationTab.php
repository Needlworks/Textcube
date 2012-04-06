							<ul id="communication-tabs-box" class="tabs-box">
								<li<?php echo isset($tabsClass['comment']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/comment?page=1<?php echo $tabsClass['postfix'];?>&amp;status=comment"><?php echo _t('댓글');?></a></li>
								<li<?php echo isset($tabsClass['guestbook']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/comment?page=1<?php echo $tabsClass['postfix'];?>&amp;status=guestbook"><?php echo _t('방명록');?></a></li>
								<li<?php echo isset($tabsClass['notify']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/notify"><?php echo _t('댓글 알리미');?></a></li>
								<li<?php echo isset($tabsClass['received']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trackback?page=1<?php echo $tabsClass['postfix'];?>&amp;status=received"><?php echo _t('걸린 글');?></a></li>
								<li<?php echo isset($tabsClass['sent']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trackback?page=1<?php echo $tabsClass['postfix'];?>&amp;status=sent"><?php echo _t('건 글');?></a></li>
								<li<?php echo isset($tabsClass['trash']) ? ' class="selected"' : NULL;?>><a href="<?php echo $context->getProperty('uri.blog');?>/owner/communication/trash<?php echo $tabsClass['postfix'];?>"><?php echo _t('휴지통');?></a></li>
							</ul>
