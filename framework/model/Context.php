<?php

/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_Context extends Singleton {
    private $__property, $__namespace;

    function __construct() {
    }

    public function setProperty($key, $value, $namespace = null) {
        $key = $this->__getKey($key, $namespace);
        $this->__property[$key] = $value;
    }

    public function setPropertyItem($key, $value, $namespace = null) {
        $key = $this->__getKey($key, $namespace);
        if (empty($this->__property[$key])) {
            $this->__property[$key] = array();
        }
        if (is_array($this->__property[$key])) {
            array_push($this->__property[$key], $value);
            return true;
        }
        return false;
    }

    public function unsetPropertyItem($key, $value, $namespace = null) {
        $key = $this->__getKey($key, $namespace);
        if (empty($this->__property[$key])) {
            return true;
        }
        if (is_array($this->__property[$key]) && $removeKey = array_search($value,$this->__property[$key])) {
            unset($this->__property[$key][$removeKey]);
            return true;
        }
        return false;
    }

    public function unsetProperty($key, $namespace = null) {
        $key = $this->__getKey($key, $namespace);
        unset($this->__property[$key]);
    }

    private function __getKey($key, $namespace) {
        global $pluginName;
        if (strpos($key, '.') === false) {    // If key contains namespace, use it.
            if (!is_null($namespace)) {
                $key = $namespace . '.' . $key;
            } else {
                if (!empty($this->__namespace)) {
                    $key = $this->__namespace . '.' . $key;
                } else {
                    if (!empty($pluginName)) {
                        $key = $pluginName . '.' . $key;
                    } else {
                        $key = 'global.' . $key;
                    }
                }
            }
        }
        return $key;
    }

    public function getProperty($key, $defaultValue = null) {
        $key = $this->__getKey($key, null);
        if (isset($this->__property[$key])) {
            return $this->__property[$key];
        } else {
            return $defaultValue;
        }
    }

    public function saveProperty($key, $namespace = null) {
    }

    public function loadProperty($key, $namespace = null) {
    }

    public function saveAllFromNamespace($ns) {
    }

    public function loadAllFromNamespace($ns) {
    }

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

    public function getAllFromNamespace($ns) {
        $result = array();
        $len = strlen($ns) + 1;
        foreach ($this->__property as $k => $v) {
            if (strpos($k, $ns) === 0) {
                $result[substr($k, $len)] = $v;
            }
        }
        return $result;
    }

    function __destruct() {
        // Nothing to do: destruction of this class means the end of execution
    }
}

?>
