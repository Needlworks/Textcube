<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/**
	Blog View
	---------
	- Modularize every blogview output.
**/

class BlogView {
	private $buf, $skin, $view;
	
	function __construct() {
		global $skinSetting;
		$this->buf  = new OutputWriter;
		$this->skin = new Skin($skinSetting['skin']);
		$this->view = $this->skin->outter;
	}
}
