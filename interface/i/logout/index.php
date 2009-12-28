<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
if (false) {
	fetchConfigVal();
}
logout();
?>
<div id="Logout" title="Logout" class="panel" selected="false">
	<div class="content">
		Logout Successfully.
	</div>
	<a href="#" onclick="self.location.reload();" class="whiteButton margin-top10"><?php echo _text('Go to front page');?></a>
</div>
