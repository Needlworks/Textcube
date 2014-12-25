<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_AlternateLogins extends DBModel {
	public function __construct() {
		$this->context = Model_Context::getInstance();
		$this->reset();
	}

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
		
	public function reset($param = null) {
		$this->userid = null;
		$this->provider = '';
		$this->remoteid = '';
		$this->data = '';
	}


	public function get() {
		parent::reset('AlternateLogins');
		if (!is_null($this->userid)) { $this->setQualifier('userid','eq',$this->userid);}
		if (!empty($this->provider)) { $this->setQualifier('provider','eq',$this->provider,true);}
		if (!empty($this->remoteid)) { $this->setQualifier('remoteid','eq',$this->remoteid,true);}
		$result = $this->getRow();
		$this->userid = $result['userid'];
		$this->provider = $result['provider'];
		$this->remoteid = $result['remoteid'];
		$this->data = $result['data'] ? unserialize($result['data']) : array();
	}

	public function set() {
		parent::reset('AlternateLogins');
		if (!is_null($this->userid)) { $this->setAttribute('userid',$this->userid);} else { return false;}
		if (!empty($this->provider)) { $this->setAttribute('provider',$this->provider,true);} else { return false;}
		if (!empty($this->remoteid)) { $this->setAttribute('remoteid',$this->remoteid,true);} else { return false;}
		if (!empty($this->data)) { 
			$this->setAttribute('data',serialize($this->data),true);
		} else { 
			$this->setAttribute('data','',true);
		}
		$result = $this->replace();
	}

	public function remove() {
		parent::reset('AlternateLogins');
		if (!is_null($this->userid)) { $this->setQualifier('userid','eq',$this->userid);}
		if (!is_null($this->provider)) { $this->setQualifier('provider','eq',$this->provider,true);}
		if (!is_null($this->remoteid)) { $this->setQualifier('remoteid','eq',$this->remoteid,true);}
		return $this->delete();
	}

	private function error($state) {
		$this->_error['message'] = $state;
		return false;
	}
	// UNIQUE KEY (userid, provider, remoteid)
	// KEY : userid / remoteid / provider
	protected $structure = array(
		"userid"	=> array(
			"type"	=> "integer",
			"isNull"	=> false
			),
		"provider"	=> array(
			"type"	 => "varchar",
			"length" => 16,
			"isNull"	 => false
			),
		"remoteid"	=> array(
			"type" 	=> "varchar",
			"length" => 255,
			"isNull"	=> false
		),
		"data"	=>	array(
			"type"	=> "text",
			"isNull"	=> true
			)
	);
}
?>
