<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Cache_code {
	function __construct() {
		$this->reset();
	}
	
	private function reset() {
		$this->code =
		$this->name =
		$this->fileName =
		null;
	}
	
	private function initialize() {
		 if (!is_dir(ROOT."/cache/code")){
			 @mkdir(ROOT."/cache/code");
			 @chmod(ROOT."/cache/code",0777);
		 }
	}
	
	public function save() {
		if(!empty($this->name)) $this->__getCodes();	// Get source codes.
		if(empty($this->code)) return $this->_error(2);
		$this->initialize();
		$this->fileName = ROOT."/cache/code/".$this->name;
		$fileHandle = fopen($this->fileName,'w');
		if(fwrite($fileHandle, $this->code)){
			fclose($fileHandle);
			@chmod($this->fileName, 0666);
			return true;
		}
		fclose($fileHandle);
		return $this->_error(3);
	}
	
	/*@ private @*/
	private function __getCodes() {
		global $__requireComponent, $__requireView, $__requireLibrary, $__requireBasics, $__requireInit, $__requireModel;
		$code = '';
/*		foreach($__requireComponent as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/components/'.$lib.'.php');
		}*/
		foreach((array_merge($__requireBasics,$__requireLibrary)) as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/'.$lib.'.php');
		}
		foreach($__requireModel as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/model/'.$lib.'.php');
		}
		
		foreach($__requireView as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/view/'.$lib.'.php');
		}
		
		foreach($__requireInit as $lib) {
			if(strpos($lib,'DEBUG') === false) $code .= file_get_contents(ROOT .'/library/'.$lib.'.php');
		}
		$this->code = $code;
	}
	
	private function _error($err = 0) {
		var_dump($err);
		return false;
		//return $err;
	}
}
?>
