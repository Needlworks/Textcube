<?php
// POD : PHP Ontology(or Object-Oriented)-based Data model/framework
// Version 0.19a-PHP5
// By Jeongkyu Shin (jkshin@ncsl.postech.ac.kr)
// Created       : 2007.11.30
// Last modified : 2008.7.20

// (C) 2007 Jeongkyu Shin. All rights reserved. 
// Licensed under the GPL.
// See the GNU General Public License for more details. (/LICENSE, /COPYRIGHT)
// For more information, visit http://pod.nubimaru.com

// NOTE : THIS FILE CONTAINS LEGACY ROUTINE OF DBQuery ONLY.
//        FOR USING FULL FUNCTION, INCLUDE POD.Core.php instead.

// Bypass variables are supported. ($_pod_setting);
class POD extends DBQuery {
	/** Pre-definition **/
	/** Initialization **/
	function __construct($domain = null, $type = null, $prefix = '') {
		global $_pod_setting;
		if($domain != null) $this->_domain = $domain;
		else if(isset($this->domain)) $this->_domain = $this->domain;
		if($type != null) {$this->_prototype = $this->_type = $type;}
		else if(isset($this->prototype)) {$this->_prototype = $this->_type = $this->prototype;}
		if(!empty($prefix)) $this->_tablePrefix = $prefix;
		else if(isset($_pod_setting['tablePrefix'])) $this->_tablePrefix = $_pod_setting['tablePrefix'];
		else $this->_tablePrefix = '';
		if(isset($_pod_setting['DBMS'])) $this->_DBMS = $_pod_setting['DBMS'];
		$this->reset();
		// Should check bind state!
	}

	/** Additional features for Textcube **/
	/** NOTICE : PARTS BELOW EXTENDS DBQuery Class WHICH IS THE BASE OF POD
	             AND WORKS ONLY WITH 'PageCache' Component in Textcube **/
	public static function queryWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::query($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryAllWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryAllWithCache($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryRowWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryRow($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
	public static function queryColumnWithDBCache($query, $prefix = null, $type = MYSQL_BOTH, $count = -1) {
		$cache = new queryCache($query, $prefix);
		if(!$cache->load()) {
			$cache->contents = POD::queryColumn($query, $type, $count);
			$cache->update();
		}
		return $cache->contents;
	}
}
?>
