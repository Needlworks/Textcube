<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';

/*--------- Debugging environment -----------*/
global $debug, $debug_file, $blogapi_dir;
$debug = 0; /* DEBUGLOG */
$debug_file = ROOT . "/.htdebug.log";
$blogapi_dir = dirname( __FILE__ );

if( $debug )
{
	global $debugfd, $debug_file;
	$debugfd = fopen( $debug_file, "a" );
}
else
{
	if( file_exists( $debug_file ) )
	{
		unlink( $debug_file );
	}
}

function DEBUG( $str, $internal = false)
{
	global $debug, $debugfd;
	if( !$debug )
	{
		return;
	}
	if( $internal )
	{
		$str = var_export( $str, true );
	}
	fputs( $debugfd, $str );
}

/*--------- API main ---------------*/
function BlogAPI()
{
	include_once "apicore.php";

	_BlogAPI();
	return "";
}

function AddRSD($target)
{
	global $hostURL, $blogURL;
	$target .= '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.$hostURL.$blogURL.'/api/rsd" />'.CRLF;
	return $target;
}

function SendRSD()
{
	global $hostURL, $blogURL;
	global $owner;
	$homeurl = $hostURL.$blogURL;
	$apiurl = $homeurl . "/api";
	$blogid = $owner;

	print( '<?xml version="1.0" encoding="utf-8" ?> 
<rsd xmlns="http://archipelago.phrasewise.com/rsd" version="1.0">
    <service xmlns="">
        <engineName>Tattertools</engineName> 
        <engineLink>http://www.tattertools.com/</engineLink>
        <homePageLink>' . $homeurl . '/</homePageLink>
        <apis>
                <api name="MetaWeblog" preferred="true" apiLink="' . $apiurl . '" blogID="' . $blogid . '" />
                <api name="Blogger" preferred="false" apiLink="' . $apiurl . '" blogID="' . $blogid . '" />
        </apis>
    </service>
</rsd>' );
}

if( substr( $_SERVER["REQUEST_URI"], -8 ) == "/api?rsd" )
{
	SendRSD();
}
else
{
	BlogAPI();
}
?>
