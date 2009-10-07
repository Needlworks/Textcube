<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Cache_Memcache implements ICache extends Singleton
{
	private static $memcache, $__namespace;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
	public function __construct() {		
	}
	public function __destruct() {		
	}	
	public function reset() {
	}
	/// Default methods
	public function set($key, $value, $expirationDue = 0) {
		if(strpos($key,'.') === false) {	// If key contains namespace, use it.
			if (!empty($this->__namespace)) {
				$key = $this->getNamespaceHash().'.'.$key;
			} else {
				$key = $this->getNamespaceHash('global').$key;
			}
		}
		$this->memcache->set($key,serialize($value),$expirationDue);
	}
	public function get($key, $clear = false) {
		if(strpos($key,'.') === false) {	// If key doesn't contain namespace,
			if (!empty($this->__namespace)) $key = $this->getNamespaceHash().'.'.$key;
			else $key = $this->getNamespaceHash('global').$key;
		}
		return $this->memcache->get($key);
	}
	public function purge($key) {
		if(strpos($key,'.') === false) {	// If key doesn't contain namespace,
			if (!empty($this->__namespace)) $key = $this->__namespace.'.'.$key;
			else $key = $this->getNamespaceHash('global').'.'.$key;
		}
		return $this->memcache->delete($key);
	}
	public function flush() {
		
	}
	/// Namespaces
	public function useNamespace($ns = null) {
		if(is_null($ns)) $this->__namespace = null;
		else $this->__namespace = $ns;
	}
	public function getNamespace() {
		return $this->__namespace;
	}
	
	/// Private methods
	private function getNamespaceHash($ns = null) {
		$context = Model_Context::getInstance();
		if(is_null($ns)) $ns = $this->__namespace;
		$prefix = $context->getProperty('service.domain').'-'.$context->getProperty('blog.id').'-';
		$namehash = $this->memcache->get($prefix);
		if($namehash == false) {
			$seed = dechex(rand(0x10000000, 0x7FFFFFFF)).dechex(rand(0x10000000, 0x7FFFFFFF));
			$this->memcache->set($prefix,$seed);
			return $seed;
		} else {
			return $namehash;
		}
	}
}
?>