<?
/*--------- Debugging environment -----------*/
global $debug, $debug_file, $blogapi_dir;
$debug = 0; /* DEBUGLOG */
$debug_file = "../../plugins/BlogAPI/.htdebug.log";
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

/*--------- Tatter tools Core component load   -----------*/

function includeOnce($name){
	global $blogapi_dir;
	if(!ereg('^[[:alnum:]]+[[:alnum:].]+$',$name))
		return ;
	if( TATTERTOOLS_VERSION < "1.0.6" && file_exists( $blogapi_dir . "/$name.php" ) )
	{
		DEBUG ( $blogapi_dir . "/$name.php\n");
		include_once( $blogapi_dir . "/$name.php");
	}
	else
	{
		$componet_file = $blogapi_dir . "/../../components/$name.php";
		if( file_exists( $componet_file ) )
		{
			include_once( $componet_file );
		}
		else
		{
			print( "File($componet_file) doesn't exist\n" );
		}
	}
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
	$target .= '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.$hostURL.$blogURL.'/plugin/BlogAPI/rsd" />'.CRLF;
	return $target;
}

function SendRSD()
{
	global $hostURL, $blogURL;
	global $service;
	global $owner;
	$homeurl = $hostURL.$blogURL;
	$apiurl = $homeurl . "/plugin/BlogAPI";
	$blogid = $service['domain'] . $blogURL;
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

function BlogAPITest()
{
	global $debug,$service, $blog;
	if( !$debug )
	{
		print( "<b>Set \"\$debug = 1;\" in " . __FILE__ );
		return;
	}
	print( "<pre>" );
	print( dirname(__FILE__) . "\n" );
	print( "Test page for checking.\n" );
	print( "Tatter tools version: " . TATTERTOOLS_VERSION . "\n");
	print( "Tatter tools root: " . ROOT . "\n");
	print( "Included " );
	print_r( get_included_files() );
	print( "</pre>" );
}

function BlogAPIAtom()
{
	includeOnce( "atom" );
	DoAtom();
}
?>
