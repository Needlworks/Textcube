<?php
class Pages {
	/*@constructor@*/
	function Pages() {
		$this->reset();
	}
	
	function reset() {
		$this->message =
		$this->mode =
		null;
	}

	function view($message = null) {
		if(!empty($message)) $this->message = $message;
		echo $this->message;	// Will implement later~
		return true;
	}
}
?>
