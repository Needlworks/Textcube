<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class PluginCustomConfig{
	var $usable = false;
 	function PluginCustomConfig($blogid, $pluginName){
		$this->blogid = $blogid;
		$this->pluginName = $pluginName;
		$this->reset();
	}
	function reset(){
		$this->configVal = array();
		$this->usable = false;
	}
	/* public bool */
	function load(){
		global $configMappings;
		if( false == Validator::id( $this->blogid ) ){
			$this->usable = false;
			return false;
		}
		if( !isset($this->pluginName) || empty($this->pluginName) ){
			$this->usable = false;
			return false;
		}
		$plugin = $this->pluginName;
		if( !isset($configMappings[$plugin]) ){
			$this->usable = false;
			return false;
		}
		$this->configVal = $this->__getPluginConfig();
		if( false == is_array( $this->configVal ) ){
			$this->usable = false;
			return false;
		}
		$this->usable = true;
		return true;
	}
	/* private string */
	function __getPluginConfig(){
		global $database;
		if(defined("__TISTORY__") == true){
			global $__globalCache_data;
			if(isset($__globalCache_data['pluginSettings']) && array_key_exists($this->pluginName, $__globalCache_data['pluginSettings'])){
				return $__globalCache_data['pluginSettings'][$this->pluginName];
			}
		}
		$configXml = POD::queryCell("SELECT settings FROM {$database['prefix']}Plugins WHERE blogid = {$this->blogid} AND name = '{$this->pluginName}'");
		$t= Setting::fetchConfigVal($configXml);
		return false==is_array($t)?array():$t;
	}
	
	/* private bool */
	function __commit(){
		global $database;
		if( false == $this->usable )  
			return $this->usable;
		if( false == is_array($this->configVal) )
			return false;
		$element = '';
		foreach($this->configVal as $key => $value) {
			$element.= "<field name=\"$key\" type=\"text\" ><![CDATA[$value]]></field>";
		}
		$xml = '<?xml version="1.0" encoding="utf-8"?><config>'.$element.'</config>';
		$xml = POD::escapeString($xml);

		if (defined('__TISTORY__')) {
			expireGlobalDressing($this->blogid);
			DataCache::expireData('SkinCache', $this->blogid);
			globalCacheExpire($this->blogid);
		}

		return POD::query("REPLACE INTO {$database['prefix']}Plugins (blogid, name, settings) VALUES({$this->blogid},'{$this->pluginName}', '$xml')");
	}
	
	/* public string null*/
	function getValue($name){
		if( false == $this->usable )  
			return null;
		return !isset($this->configVal[$name])==true?null:$this->configVal[$name];
	}
	/* public bool */
	function setValue($name , $value){
		if( false == $this->usable )  
			return $this->usable;
		$this->configVal[$name] = $value;
		return $this->__commit();
	}
	
	/* public array null */
	function getAllValue(){
		return $this->usable==false?null:$this->configVal;
	}
	/* public bool */
	function setMergedValue( /* array */ $configVal ){
		if(false == $this->usable ) 
			return $this->usable;
		if( false == is_array( $configVal ) )
			return false;
		$this->configVal = array_merge( $this->configVal, $configVal);
		return $this->__commit();
	}
	/* public bool */
	function setAllValue(/* array */ $configVal ){
		if(false == $this->usable ) 
			return $this->usable;
		if( false == is_array( $configVal ) )
			return false;
		$this->configVal = $configVal;
		return $this->__commit();
	}
}
?>
