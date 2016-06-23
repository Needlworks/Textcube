<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('ROOT',dirname(__FILE__));
if (file_exists(ROOT . '/framework/id/load')) {
	$app_id = trim(file_get_contents(ROOT . '/framework/id/load'));
} else {
	$app_id = 'textcube';
}
require_once(ROOT.'/framework/id/'.$app_id.'/Dispatcher.php');
/** Dispatching Interface request via URI */
$dispatcher = Dispatcher::getInstance();
/** Interface Loading */
if (empty($dispatcher->service['debugmode'])) {@include_once $dispatcher->interfacePath;}
else {include_once $dispatcher->interfacePath;}
?>
