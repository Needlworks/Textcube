<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/**
 * Blog View
 * ---------
 * - Modularize every blogview output.
 **/
class BlogView {
    private $buf, $skin, $view;

	function __construct() {
		$context = Model_Context::getInstance();
        $this->buf = new Utils_OutputWriter;
        $this->skin = new Skin($context->getProperty('skin.skin'));
        $this->view = $this->skin->outter;
    }
}
