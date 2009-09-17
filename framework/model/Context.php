<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Model_Context extends Singleton
{
	private static $__property, $URI;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		/// Loads URI handler information.
	//	$URI = Model_URIHandler::getInstance();
	}

	public function setProperty($key,$value, $options = null) {
		$this->__property[$key] = $value;
	}

	public function getProperty($key) {
		if (isset($this->__property[$key])) return $this->__property[$key];
		else return null;
	}
	
	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}
?>
