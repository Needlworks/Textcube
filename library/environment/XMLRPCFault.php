<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class XMLRPCFault {
	var $code, $string;

	function XMLRPCFault($code = 0, $string = 'Error') {
		$this->code = $code;
		$this->string = $string;
	}
}
?>