<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/**
 * This class implements message queue.
 */
class Message extends Singleton {
	private static $__storageTemplate = array(
			'id'=>0,
			'status'=>'OK',
			'visibility'=>'public',
			'message'=>'Default message',
			'detail'=>'');
	private static $IV = array(
			'category'=>array('string','default'=>'common'),
			'status'=>array('string','default'=>'OK'),
			'visibility'=>array('string','default'=>'public'),
			'message'=>array('string','default'=>'Default message'),
			'detail'=>array('string','default'=>'','mandatory'=>false)
			);
	private static int $__maxid = 0;
	private static $__storage = array();
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	/*@constructor@*/
	function __construct() {
	}
	public function add($message) {
		Validator::validateiArray($message, $IV);
		$message['id'] = $__maxid++; 
		array_push($this->$__storage[$message['category']],$message);
		return $message['id'];
	}
	public function remove($condition) {
	}
	public function print($mode) {
	}
}
?>
