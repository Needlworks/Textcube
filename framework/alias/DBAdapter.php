<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

	$context = Model_Context::getInstance();
	$dbms = 'MySQL';
	if(!is_null($context->getProperty('database.dbms'))) $dbms = $context->getProperty('database.dbms');
	require_once(ROOT."/framework/data/IAdapter.php");	
	require_once(ROOT."/framework/data/".$dbms."/Adapter.php");
	DBAdapter::cacheLoad();
	register_shutdown_function( array('DBAdapter','cacheSave') );
?>
