<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Model_Transaction {
	function pickle($data) {
		if( !isset( $_SESSION['pickle'] ) ) {
			$_SESSION['pickle'] = array();
		}
		$tid = sprintf("%010dP%s",time(),md5( microtime(true) ));
		while( isset( $_SESSION['pickle'][$tid] ) ) {
			$tid = sprintf("%010dP%s",time(),md5( microtime(true) ));
			usleep(50);
		}
		$_SESSION['pickle'][$tid] = $data;
		return $tid;
	}

	function unpickle( $tid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$tid] ) ) {
			return null;
		}
		$data = $_SESSION['pickle'][$tid];
		unset( $_SESSION['pickle'][$tid] );
		if( empty( $_SESSION['pickle'] ) ) {
			unset( $_SESSION['pickle'] );
		}
		return $data;
	}

	function repickle( $tid, & $data ) {
		if( empty($tid) ) {
			return;
		}
		$_SESSION['pickle'][$tid] = $data;
	}

	function taste( $tid ) {
		if( !isset( $_SESSION['pickle'] ) || !isset( $_SESSION['pickle'][$tid] ) ) {
			return null;
		}
		return $_SESSION['pickle'][$tid];
	}

	function clear() {
		if( isset( $_SESSION['pickle'] ) ) {
			unset( $_SESSION['pickle'] );
		}
	}

	function gc() {
		if( !isset( $_SESSION['pickle'] ) ) {
			return;
		}
		$current = time();
		foreach( array_keys( $_SESSION['pickle'] ) as $k ) {
			$created_time = int($k);
			if( $created_time < $current - 3600 ) {
				unset( $_SESSION['pickle'][$k] );
			}
		}
		if( empty($_SESSION['pickle']) ) {
			unset( $_SESSION['pickle'] );
		}
	}

	function debug( $tid = null ) {
		header( "X-Debug-tid: $tid" );
		foreach( $_SESSION['pickle'][$tid] as $k => $v ) {
			header( "X-Debug-$k: [$v]" );
		}
	}
}

?>
