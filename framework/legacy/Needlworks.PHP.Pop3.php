<?php
/// Copyright (c) 2006-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

class Pop3 {
	function Pop3()
	{
		$this->ctx = null;
		$this->logger = null;
		$this->uidl_filter = null;
		$this->size_filter = null;
		$this->stat_callback = null;
		$this->retr_callback = null;
		$this->mails = array();
		$this->uids = array();
		$this->filterred = array();
		$this->clearStatus();
	}

	function log($msg)
	{
		if( !$this->logger ) {
			return;
		}
		call_user_func( $this->logger, $msg );
	}

	function connect( $server, $port = 110, $bSSL = false )
	{
		$this->clearStatus();
		$this->ctx = fsockopen( $bSSL ? "ssl://$server" : $server, $port); 
		if( !$this->ctx ) {
			return false;
		}
		$line = fgets( $this->ctx, 1024 );
		return $this->checkStatus( $line );
	}

	function authorize( $username, $password )
	{
		$this->clearStatus();
		$this->log( "Send: USER $username" );
		if( !fputs($this->ctx, "USER $username\r\n") ) {
			return false;
		}
		if( !$this->receiveResult() ) {
			return false;
		}
		$this->log( "Send: PASS [......]" );
		if( !fputs($this->ctx, "PASS $password\r\n") ) {
			return false;
		}
		return $this->receiveResult();
	}

	function retr($nr=false)
	{
		$this->clearStatus();
		if( $nr != false ) {
			$numbers = array( $nr );
		} else {
			$numbers = array_values( $this->mails );
		}

		foreach( $numbers as $nr ) {
			$this->log( "Send: RETR $nr" );
			if( !fputs($this->ctx, "RETR $nr\r\n") ) {
				return false;
			}
			if( !$this->receiveResult(true) ) {
				return false;
			}
			if( $this->retr_callback ) {
				call_user_func( $this->retr_callback, $this->results, $this->uids[$nr] );
			}
		}

		return true;
	}

	function dele($nr=false)
	{
		$this->clearStatus();
		if( $nr != false ) {
			$numbers = array( $nr );
		} else {
			$numbers = array_values( $this->mails );
		}
		foreach( $numbers as $nr ) {
			$this->log( "Send: DELE $nr" );
			if( !fputs($this->ctx, "DELE $nr\r\n") ) {
				return false;
			}
			if( !$this->receiveResult(false) ) {
				return false;
			}
		}
		return true;
	}

	function quit()
	{
		$this->clearStatus();
		$this->log( "Send: QUIT" );
		if( !fputs($this->ctx, "QUIT\r\n") ) {
			return false;
		}
		return true;
	}

	function setLogger( $func )
	{
		$this->logger =& $func;
	}

	function setUidFilter( $func )
	{
		$this->uidl_filter =& $func;
	}

	function setSizeFilter( $func )
	{
		$this->size_filter =& $func;
	}

	function setStatCallback( $func )
	{
		$this->stat_callback =& $func;
	}

	function setRetrCallback( $func )
	{
		$this->retr_callback =& $func;
	}

	function setFallbackCharset( $charset )
	{
		$this->fallback_charset = $charset;
	}

	function uidl()
	{
		$this->clearStatus();
		$this->log( "Send: UIDL" );
		if( !fputs($this->ctx, "UIDL\r\n") ) {
			return false;
		}
		if( !$this->receiveResult(true) ) {
			return false;
		}
		$this->uids = array();
		$this->mails = array();
		foreach( $this->results as $line ) {
			list( $number, $uid ) = split( " ", $line );
			if( !empty($this->filterred[$number]) ) {
				continue;
			}
			if( $this->uidl_filter && call_user_func($this->uidl_filter, $uid, $number) ) {
				$this->filterred[$number] = true;
				continue;
			}
			$this->uids[$number] = $uid;
			$this->mails[$uid] = $number;
		}
		return true;
	}

	function list_size()
	{
		$this->clearStatus();
		$this->log( "Send: STAT" );
		if( !fputs($this->ctx, "STAT\r\n") ) {
			return false;
		}
		if( !$this->receiveResult(false) ) {
			return false;
		}
		list( $total, $totalsize ) = split( " ", $this->status );
		if( $this->stat_callback ) {
			if( !call_user_func( $this->stat_callback, $total, $totalsize ) ) {
				return false;
			}
		}

		if( $total == 0 ) {
			return false;
		}

		$this->log( "Send: LIST" );
		if( !fputs($this->ctx, "LIST\r\n") ) {
			return false;
		}
		if( !$this->receiveResult(true) ) {
			return false;
		}
		foreach( $this->results as $line ) {
			list( $number, $size ) = split( " ", $line );
			if( !empty($this->filterred[$number]) ) {
				continue;
			}
			if( $this->size_filter && call_user_func($this->size_filter, $size, $number, $total) ) {
				if( isset( $this->uids[$number] ) ) {
					unset( $this->mails[$this->uids[$number]] );
					unset( $this->uids[$number] );
				}
				$this->filterred[$number] = true;
			}
		}
		return true;
	}

	function run()
	{
		if( !$this->list_size() ) { return; }
		if( !$this->uidl() ) { return; }
		if( !$this->retr() ) { return; }
	}

	function clearStatus()
	{
		$this->status = '';
		$this->error = '';
		$this->results = array();
	}

	function receiveResult($checkPeriod=false)
	{
		$count = 1000000; /* Maximum 1000000 lines! it's enough to handle 6MB bytes */
		$line = fgets($this->ctx, 1024); 
		$this->log( "Recv: ".trim($line) );
		if( !$line ) {
			return false;
		}
		if( !$this->checkStatus( $line ) ) {
			return false;
		}
		if( !$checkPeriod ) {
			return true;
		}
		$this->results = array();
		while($count--) {
			$line = fgets($this->ctx, 1024); 
			$line = trim($line);
			if( $line == '.' ) { break; }
			array_push($this->results, $line);
			//echo $line."<br/>";
		}
		return true;
	}

	function checkStatus( $line )
	{
		$this->status = substr( $line, 4 );
		if( substr($line,0,3) == '+OK' ) {
			return true;
		}
		$this->error = $this->status;
		return false;
	}

	function getLastError()
	{
		return $this->error;
	}

	function _decode_header_core( $matches )
	{
		static $rewrite_charset = array(
			'ks_c_5601-1987' => 'euc-kr'
		);
		switch( strtolower($matches[2]) ) {
		case 'b':
			$content = base64_decode( $matches[3] );
			break;
		case 'q':
			$content = quoted_printable_decode( $matches[3] );
			break;
		default:
			return '';
		}
		$charset = strtolower($matches[1]);

		if( $charset != 'utf-8' && $charset != 'utf8' ) {
			if( isset($rewrite_charset[$charset]) ) {
				$charset = $rewrite_charset[$charset];
			}
			$content = @iconv( $charset, 'utf-8', $content );
		}
		return $content;
	}

	function decode_header($hdr)
	{
		if( strstr( $hdr, "=?" ) ) {
			$hdr = preg_replace_callback( "/=\?([^?]+)\?([BbQq])\?([^?]+)\?=/",  array( &$this, '_decode_header_core' ), $hdr );
		} else {
			if( !empty($this->fallback_charset) ) {
				$hdr = iconv( $this->fallback_charset, 'utf-8', $hdr );
			}
		}
		return $hdr;
	}

	function parse( & $data, $begin = 0, $end = 0 )
	{
		if( $end == 0 ) { $end = count($data); }

		$mail = array();

		/* Header explorer */
		for( $i=$begin; isset($data[$i]); $i++ ) {
			$line =& $data[$i];
			if( empty($line) ) {
				$i++; /* Skip the blank line */
				break;
			}
			/* treat multilined header, with keeping the $data indices */
			if( $line == "__SKIP__\n" ) {
				continue;
			}
			for( $j=$i+1; isset($data[$j]); $j++ ) {
				if( empty($data[$j]) || $data[$j][0] != ' ' ) { break; }
				$line = $line . ltrim( $data[$j] );
				$data[$j] = "__SKIP__\n";
			}
			
			if( preg_match( '/.*boundary=["\']?([^"\']+)["\']?/i', $line, $match ) ) {
				$mail['boundary'] = "--{$match[1]}";
			}
			if( !isset( $mail['filename'] ) && preg_match( '/.*(file)?name=["\']?([^"\']+)["\']?/i', $line, $match ) ) {
				$mail['filename'] = $this->decode_header($match[2]);
			}
			if( preg_match( '/^(X-MMS(?:[^:]+)):\s*([a-zA-Z\/.-]+);?.*/i', $line, $match ) ) {
				if( !isset($mail['mms']) ) {
					$mail['mms'] = array();
				}
				$mail['mms'][$match[1]] = $match[2];
			}
			if( preg_match( '/^Content-Type:\s*([a-zA-Z\/.-]+);?.*/i', $line, $match ) ) {
				$mail['content_type'] = strtolower($match[1]);
				if( preg_match( '/^charset=["\']?([^"\']+)["\']?/i', $line, $match ) ) {
					$mail['content_charset'] = $match[1];
				}
			}
			if( !isset( $mail['return_path'] ) && preg_match( '/^Return-Path:\s*(.*)/i', $line, $match ) ) {
				$mail['return_path'] = $match[1];
			}
			if( !isset( $mail['subject'] ) && preg_match( '/^Subject:\s*(.*)/i', $line, $match ) ) {
				$mail['subject'] = $this->decode_header($match[1]);
			}
			if( !isset( $mail['date'] ) && preg_match( '/^Date:\s*(.*)/i', $line, $match ) ) {
				$match[1] = str_replace( "Wen", "Wed", $match[1] ); /* SKT date header bug, #1036 */
				$mail['date'] = strtotime( $match[1] );
				$mail['date_string'] = strftime( "%Y-%m-%d", $mail['date'] );
				$mail['time_string'] = strftime( "%H:%M:%S", $mail['date'] );
				$mail['date_year'] =   strftime( "%Y", $mail['date'] );
				$mail['date_month'] =  strftime( "%m", $mail['date'] );
				$mail['date_day'] =    strftime( "%d", $mail['date'] );
			}
			if( !isset( $mail['from'] ) && preg_match( '/^From:([^<]*)<(.*)>/i', $line, $match ) ) {
				$mail['sender'] = trim($this->decode_header($match[1]));
				$mail['from'] = $match[2];
			}
			if( !isset( $mail['content_transfer_encoding'] ) && preg_match( '/^Content-Transfer-Encoding:\s*(\S+)/i', $line, $match ) ) {
				$mail['content_transfer_encoding'] = strtolower($match[1]);
			}
		}

		/* Body explorer */
		if( empty($mail['boundary']) ) {
			$mail['content'] = join( "\r\n", array_slice( $data, $i, $end-$i ) );
			if( isset( $mail['content_transfer_encoding'] ) && $mail['content_transfer_encoding'] ==  'base64' ) {
				$mail['decoded_content'] = base64_decode( $mail['content'] );
			} else {
				$mail['decoded_content'] = $mail['content'];
			}
			$mail['length'] = strlen($mail['decoded_content']);
			if( isset($mail['content_type']) && substr($mail['content_type'],0,4) == 'text' ) {
				$mail['text_type'] = $mail['content_type'];
				$mail['text'] = $mail['decoded_content'];
				if( !empty($mail['content_charset']) ) {
					if(strtolower($mail['content_charset']) != 'utf-8') {
						$mail['text'] = iconv( $mail['content_charset'], 'utf-8', $mail['text'] );
					}
				} elseif( isset($this->fallback_charset) ) {
					if(strtolower($mail['content_charset']) != 'utf-8') {
						$mail['text'] = iconv( $this->fallback_charset, 'utf-8//IGNORE', $mail['text'] );
					}
				}
			}
		} else {
			$parts = array();
			$part_begin = $i;
			for( ; $i<$end; $i++ )
			{
				if( $data[$i] == $mail['boundary'] || $data[$i] == "{$mail['boundary']}--" ) {
					array_push( $parts, array( $part_begin, $i ) );
					$part_begin = $i+1;
				}
			}
			array_shift( $parts ); // Skip the preamble before body
			$mail['parts'] = array();
			$mail['attachments'] = array();
			foreach( $parts as $slice ) {
				$part = $this->parse( $data, $slice[0], $slice[1] );
				if( strstr( $part['content_type'], 'image' ) ) {
					array_push( $mail['attachments'], $part );
				}
				if( isset($mail['text']) && strstr($mail['text_type'],'plain') &&
					isset( $part['text_type'] ) && strstr($part['text_type'],'html') ) {
					$mail['text'] = $part['text'];
					$mail['text_type'] = $part['text_type'];
				}
				if( !isset($mail['text']) && isset( $part['text'] ) ) {
					$mail['text'] = $part['text'];
					$mail['text_type'] = $part['text_type'];
				}
				array_push( $mail['parts'], $part );
			}
		}
		return $mail;
	}
}
