<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

interface ICache
{
	public function __construct();
	public function __destruct();
	public function reset();
	/// Default methods
	public function set($key, $value, $expirationDue);
	public function get($key, $clear = false);
	public function purge($key);
	public function flush();
	/// Namespaces
	public function useNamespace($ns = null);
	public function getNamespace();
}
?>
