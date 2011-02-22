<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class DebuggerError extends Exception {};

class Debug {
	private $backend = 'logfile';
	private $file = NULL;

	function __construct($backend_name, $options = NULL) {
		// We don't check validity of $options strictly here.
		switch ($backend_name) {
		case 'logfile':
			// always open in append mode
			$this->file = fopen(ROOT . '/cache/debug.log', 'a');
			if ($this->file === FALSE)
				throw new DebuggerError("Creation failed because cache/debug.log couldn't be opened.");
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
				throw new DebuggerError("Insufficient options.");
			$errno = 0;
			if ($options['host'] == 'localhost')
				$options['host'] = '127.0.0.1';
			$this->file = @fsockopen($options['host'], $options['port'], $errno, $errstr, 10);
			if ($this->file === FALSE || $errno != 0)
				throw new DebuggerError("Creation failed due to socket error ($errno, $errstr)");
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
			// truncate is only supported by normal files. We just append a separator.
			$this->write('--------------------------');
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
			fwrite(STDERR, $msg . "\n");
			break;
		}
	}

	function writef() {
		if (func_num_args() == 1)
			$this->write(func_get_arg(0));
		else if (func_num_args() >= 2) {
			$format = func_get_arg(0);
			$args = array_slice(func_get_args(), 1);
			$this->write(vsprintf($format, $args));
		}
	}

	function dump($var) {
		ob_start();
		var_dump($var);
		$this->write(ob_get_flush());
	}
}
?>
