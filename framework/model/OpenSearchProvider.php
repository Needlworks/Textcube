<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


class Model_OpenSearchProvider extends XMLTree {

    /// Set the descriptors to DOM tree
    public function setDescriptor($name, $content, $type = null) {
        return $this->setValue('/OpenSearchDescription/' . $name, $content, $type);
    }
}

?>
