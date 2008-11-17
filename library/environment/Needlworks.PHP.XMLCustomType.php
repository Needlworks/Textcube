<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class XMLCustomType {
	var $value, $name;
	
	function XMLCustomType($varString, $varName) {
		$this->name = $varName;
		$this->value = $varString;
	}
}
?>