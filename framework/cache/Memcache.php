<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/**
 * @requires Model_Context
 */
class Cache_Memcache extends Singleton {
    private static $memcache, $__namespace, $__value, $__qualifiers;

    public static function getInstance() {
        return self::_getInstance(__CLASS__);
    }

    public function __construct() {
        $context = Model_Context::getInstance();
        $this->__qualifiers = array();
        if ($context->getProperty('service.memcached') == true):
            $this->memcache = new Memcache;
            $this->memcache->connect((!is_null($context->getProperty('memcached.server')) ? $context->getProperty('memcached.server') : 'localhost'));
        endif;
    }

    public function __destruct() {
    }

    /// Default methods
    public function set($key, $value, $expirationDue = 0) {
        if (strpos($key, '.') === false) {    // If key contains namespace, use it.
            if (!empty($this->__namespace)) {
                $key = $this->getNamespaceHash() . '.' . $key;
            } else {
                $key = $this->getNamespaceHash('global') . '.' .$key;
            }
        }
        $this->memcache->set($key, $value, 0, $expirationDue);
    }

    public function get($key, $clear = false) {
        if (strpos($key, '.') === false) {    // If key doesn't contain namespace,
            if (!empty($this->__namespace)) {
                $key = $this->getNamespaceHash() . '.' . $key;
            } else {
                $key = $this->getNamespaceHash('global') . '.' .$key;
            }
        }
        return $this->memcache->get($key);
    }

    public function purge($key) {
        if (strpos($key, '.') === false) {    // If key doesn't contain namespace,
            if (!empty($this->__namespace)) {
                $key = $this->getNamespaceHash() . '.' . $key;
            } else {
                $key = $this->getNamespaceHash('global') . '.' . $key;
            }
        }
        return $this->memcache->delete($key);
    }

    public function flush() {
        if (is_null($this->__namespace)) {
            return $this->memcache->flush();
        } else {
            $this->renewNamespaceHash($this->__namespace);
        }
    }

    /// Namespaces
    public function useNamespace($ns = null) {
        if (is_null($ns)) {
            $this->__namespace = null;
        } else {
            $this->__namespace = $ns;
        }
    }

    public function getNamespace() {
        return $this->__namespace;
    }

    /// Compatibility layer via Data_IModel
    public function reset($ns, $param = '') {
        $this->__qualifiers = array();
        $this->useNamespace($ns . $param);
    }

    public function setQualifier($key, $condition, $value, $param = null) {
        array_push($this->__qualifiers, $key . $condition . $value);
    }

    public function setAttribute($key, $value = null, $param = null) {
        $this->__value = $value;    // Last attribute set will be used as key-value pair index.
        return true;
    }

    public function getAll() {
        return $this->getCell();
    }

    public function getCell() {
        return $this->get($this->getKeyFromQualifiers());
    }

    public function insert() {
        return $this->set($this->getKeyFromQualifiers(), $this->__value);
    }

    public function delete() {
        return $this->purge($this->getKeyFromQualifiers());
    }

    public function replace() {
        return $this->insert();
    }

    /// Private methods
    private function getNamespaceHash($ns = null, $renew = false) {
        $context = Model_Context::getInstance();
        if (is_null($ns)) {
            $ns = $this->__namespace;
        }
        $prefix = $context->getProperty('service.domain') . '-' . $context->getProperty('blog.id') . '-' . $ns . '-';
        if ($renew !== false) {
            $namehash = false;
        } else {
            $namehash = $this->memcache->get($prefix);
        }
        if ($namehash == false) {
            $seed = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
            $this->memcache->set($prefix, $seed);
            return $seed;
        } else {
            return $namehash;
        }
    }

    private function renewNamespaceHash($ns = null) {
        return $this->getNamespaceHash($ns, true);
    }

    private function getKeyFromQualifiers() {
        asort($this->__qualifiers);
        return abs(crc32(implode('-', $this->__qualifiers)));
    }
}

?>
