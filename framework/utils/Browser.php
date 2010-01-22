<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Utils_Browser extends Singleton
{
	private static $browserName;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	function __construct() {
		$this->browserName = null;
	}
	
	public function getBrowserName() {
		/// Blocking (is in development)
		$ctx = Model_Context::getInstance();
		if($ctx->getProperty('service.useMobileAdmin') != true) {
			return 'unknown';
		}
		if(!is_null($this->browserName)) return $this->browserName;
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			if(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'iPod')) {
				$this->browserName = 'mSafari';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'iceweasel') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'Minefield')) {
				$this->browserName = 'firefox';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')) {
				$this->browserName = 'Safari';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')) {
				$this->browserName = 'Chrome';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Webkit')) {
				$this->browserName = 'Webkit';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
				$this->browserName = 'IE';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera')) {
				$this->browserName = 'Opera';
			} else {
				$this->browserName = 'unknown';
			}
		}
		return $this->browserName;
	}
	public function getVersion() {
	}
	
	public function isMobile() {
	}
	public function isSafari() {
	}
	public function isIE() {
	}
	public function isOpera() {
	}

	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}
?>
