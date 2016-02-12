<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class RSS {
    function refresh() {
        if (file_exists(__TEXTCUBE_CACHE_DIR__ . "/rss/" . getBlogId() . ".xml")) {
            @unlink(__TEXTCUBE_CACHE_DIR__ . "/rss/" . getBlogId() . ".xml");
        }
    }
}

class ATOM {
    function refresh() {
        if (file_exists(__TEXTCUBE_CACHE_DIR__ . "/atom/" . getBlogId() . ".xml")) {
            @unlink(__TEXTCUBE_CACHE_DIR__ . "/atom/" . getBlogId() . ".xml");
        }
    }
}
?>
