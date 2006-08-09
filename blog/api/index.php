<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';

requireComponent( "Eolin.PHP.XMLStruct" );
requireComponent( "Eolin.PHP.XMLTree" );
requireComponent( "Eolin.PHP.XMLRPC" );
requireComponent( "Tattertools.Control.RSS" );
requireComponent( "Tattertools.Control.Auth" );
requireComponent( "Tattertools.Data.Post" );
requireComponent( "Tattertools.Data.Category" );

/*--------- API main ---------------*/

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
	api_BlogAPI();
}
?>
