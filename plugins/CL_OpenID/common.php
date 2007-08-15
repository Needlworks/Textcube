<?php

if (!defined('ROOT')) {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}

define( 'Auth_OpenID_NO_MATH_SUPPORT', 1 );
$path_extra = dirname(__FILE__);
$path = ini_get('include_path');
if( !isset( $_ENV['OS'] ) || strstr( $_ENV['OS'], 'Windows' ) === false ) {
	$path .= ':' . $path_extra;
} else {
	$path .= ';' . $path_extra;
}

if( file_exists( "/dev/urandom" ) ) {
	define('Auth_OpenID_RAND_SOURCE', '/dev/urandom');
} else if( file_exists( "/dev/random" ) ) {
	define('Auth_OpenID_RAND_SOURCE', '/dev/random');
} else {
	define('Auth_OpenID_RAND_SOURCE', null);
}

ini_set('include_path', $path);

/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store OpenID
 * information.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * This is where the example will store its OpenID information.  You
 * should change this path if you want the example store to be created
 * elsewhere.  After you're done playing with the example script,
 * you'll have to remove this directory manually.
 */
$store_path = ROOT . "/cache/_php_consumer";

if (!file_exists($store_path) &&
    !mkdir($store_path)) {
    print "Could not create the FileStore directory '$store_path'. ".
        " Please check the effective permissions.";
    exit(0);
}

$store = new Auth_OpenID_FileStore($store_path);

/**
 * Create a consumer object using the store object created earlier.
 */
$consumer = new Auth_OpenID_Consumer($store);

?>
