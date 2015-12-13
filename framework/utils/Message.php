<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/**
 * This class implements message queue.
 */
class Utils_Message extends Singleton {
    private static $__storageTemplate = array(
        'id' => 0,
        'status' => 'OK',
        'visibility' => 'public',
        'message' => 'Default message',
        'detail' => '');
    private static $IV = array(
        'category' => array('string', 'default' => 'common'),
        'status' => array('string', 'default' => 'OK'),
        'visibility' => array('string', 'default' => 'public'),
        'message' => array('string', 'default' => 'Default message'),
        'detail' => array('string', 'default' => '', 'mandatory' => false)
    );
    private static $__maxid = 0;
    private static $__storage = array();

    public static function getInstance() {
        return self::_getInstance(__CLASS__);
    }

    /*@constructor@*/
    function __construct() {
    }

    public function add($message) {
        Validator::validateiArray($message, self::$IV);
        $message['id'] = self::$__maxid++;
        array_push($this->__storage, $message);
        return $message['id'];
    }

    public function remove($condition) {
    }

    public function dump($mode) {
    }

    public function get($condition = null) {
        $result = array();
        if (is_null($condition)) {
            return $this->__storage;
        }
        if (isset($condition['category'])) {
            if (is_array($condition['category'])) {
                foreach ($condition['category'] as $cat) {
                    array_push($result, MMCache::queryAll($this->__storage, 'category', $cat));
                }
            } else {
                array_push($result, MMCache::queryAll($this->__storage, 'category', $condition['category']));
            }
        }
        if (isset($condition['status'])) {
            switch ($condition['status']) {
                case 'OK':
                    array_push($result, MMCache::queryAll($this->__storage, 'status', 'OK'));
                case 'WARNING':
                    array_push($result, MMCache::queryAll($this->__storage, 'status', 'WARNING'));
                case 'ERROR':
                default:
                    array_push($result, MMCache::queryAll($this->__storage, 'status', 'OK'));
            }
        }
        return $result;
    }
}

?>
