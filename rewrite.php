<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('ROOT',dirname(__FILE__));
require_once(ROOT.'/framework/Dispatcher.php');
/** Dispatching Interface request via URI */
$dispatcher = Dispatcher::getInstance();
/** Interface Loading */
if (empty($service['debugmode'])) {	@include_once $dispatcher->interfacePath;}
else {include_once $dispatcher->interfacePath;}
?>
