<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class Debug {
	private $backend = 'logfile';
	private $file = NULL;

	function __construct($backend_name, $options = NULL) {
		// We don't check validity of $options strictly here.
		switch ($backend_name) {
		case 'logfile':
			// always open in append mode
			$this->file = fopen('./debug.log', 'a');
			if ($this->file === FALSE)
				return FALSE;
			if ($options != NULL && $options['truncate'] == TRUE)
				$this->truncate();
			break;
		case 'syslog':
			// NOTE: syslog state is shared by all other php codes run through the execution.
			if ($options == NULL)
				openlog('TEXTCUBE', LOG_ODELAY);
			else
				openlog('TEXTCUBE', $options['option'], $options['facility']);
			break;
		case 'socket':
			if ($options == NULL)
				return FALSE;
			$this->file = fsockopen($options['host'], $options['port']);
			if ($this->file === FALSE)
				return FALSE;
			break;
		default:
			$backend_name = 'console';
			break;
		}
		$this->backend = $backend_name;
		return TRUE;
	}

	function __destruct() {
		switch ($this->backend) {
		case 'logfile':
		case 'socket':
			if ($this->file != NULL && $this->file !== FALSE)
				fclose($this->file);
			break;
		case 'syslog':
			closelog();
			break;
		default:
			break;
		}
	}

	function truncate() {
		switch ($this->backend) {
		case 'logfile':
			ftruncate($this->file);
			break;
		default:
			// truncate is only supported by normal files.
			break;
		}
	}

	function write($msg) {
		switch ($this->backend) {
		case 'logfile':
		case 'socket':
			if ($this->file != NULL && $this->file !== FALSE)
				fwrite($this->file, $msg . "\n");
			break;
		case 'syslog':
			syslog(LOG_DEBUG, $msg);
			break;
		default:
			fwrite(STDERR, $msg);
			break;
		}
	}

	function writef($format, $vars) {
	}
}
?>
