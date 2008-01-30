<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'page' => array('number','min'=>1,'default'=>1)
	) 
);

$page=(isset($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1 );



$service['admin_script']='control.js';

require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';

global $blogURL;
$page = $_GET['page'];

?>
<h2 class="caption"><span class="main-text"><?php echo _t('PHP Info'); ?></span></h2>
<div class="phpinfo">
<?php phpinfo();?>
</div>
<?php require ROOT . '/lib/piece/owner/footer.php';?>
