<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

	if(!defined('__TEXTCUBE_SETUP__')) {
		$context = Model_Context::getInstance();
		$dbms = 'MySQL';
		if(!is_null($context->getProperty('database.dbms'))) $dbms = $context->getProperty('database.dbms');
	} else {
		global $dbms;
	}
	require_once(ROOT."/framework/data/IAdapter.php");	
	require_once(ROOT."/framework/data/".$dbms."/Adapter.php");
?>