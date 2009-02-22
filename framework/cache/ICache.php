<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

interface ICache
{
	public function __construct($type);
	public function setEntry($key, $expirationDue);
	public function getEntry($key, $clear = false);

	public static function generateKey();
}
?>
