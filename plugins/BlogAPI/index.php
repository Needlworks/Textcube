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
		include_once ( $blogapi_dir . "/$name.php");
	}
	else
	{
		include_once ( $blogapi_dir . "/../../components/$name.php");
	}
}
includeOnce( "Eolin.PHP.Core" );
includeOnce( "Eolin.PHP.XMLStruct" );
includeOnce( "Eolin.PHP.XMLTree" );
includeOnce( "Eolin.PHP.XMLRPC" );
includeOnce( "Tattertools.Control.RSS" );
includeOnce( "Tattertools.Core" );
includeOnce( "Tattertools.Control.Auth" );
includeOnce( "Tattertools.Data.Post" );
includeOnce( "Tattertools.Data.Category" );

/*--------- Tatter tools Core component load   -----------*/
DEBUG( "\nTRANSACTION ---------- start   ----------- [" . date("r") . "]\n");
DEBUG( "Agent: " . $_SERVER["HTTP_USER_AGENT"] );
DEBUG( "\nTRANSACTION ---------- request -----------\n" );
DEBUG( $GLOBALS['HTTP_RAW_POST_DATA'] );
DEBUG( "\nTRANSACTION ---------- api -----------\n");


/*--------- API Callbacks -----------*/


/*--------- Basic functions -----------*/

function _get_canonical_id( $id )
{
	global $blogapi_dir;
	$alias_file = $blogapi_dir . "/.htaliases";
	if( !file_exists( $alias_file ) )
	{
		return $id;
	}
	$fd = fopen( $alias_file, "r" );
	$canon = $id;
	while( !feof($fd) )
	{
		$line = fgets( $fd, 1024 );
		if( substr($line,0,1) == "#" )
		{
			continue;
		}
		$match = preg_split( '/( |\t|\r|\n)+/', $line );
		if( $id == $match[0] )
		{
			$canon = $match[1];
			break;
		}
	}
	fclose( $fd );
	return $canon;
}

function _login( $id, $password )
{
	DEBUG( "\n_login: ID: $id, PASSWORD: $password\n" );

	$auth = new Auth;
	if( !$auth->login( $id, $password ) )
	{
		$canon_id = _get_canonical_id($id);
		if( !$auth->login( $canon_id, $password ) )
		{
			DEBUG( "_login: Authentication failed.\n" );
			return new XMLRPCFault( 1, "Authentication failed: $id($canon_id)" );
		}
	}
	DEBUG( "_login: Authenticated.\n" );
	return false;
}

function _utf8_substr($str,$start) 
{ 
	preg_match_all("/./u", $str, $ar); 

	if(func_num_args() >= 3) { 
		$end = func_get_arg(2); 
		return join("",array_slice($ar[0],$start,$end)); 
	} else { 
		return join("",array_slice($ar[0],$start)); 
	} 
} 

function _get_title( $content )
{
	if( preg_match( "{<title>(.+)?</title>}", $content, $match ) )
	{
		return $match[1];
	}
	$title = preg_replace( "{<.*?>}", "", $content);
	$title = _utf8_substr( $title, 0, 40 );
	return $title;
}

function _escape_content( $content )
{
	$content = str_replace( "\r", '', $content );
	return htmlspecialchars($content);
}

function _timestamp( $date8601 )
{
	if( substr( $date8601, 8,1 ) != "T" )
	{
		return $date8601;
	}
	return Timezone::getOffset() + 
		mktime( 
			substr( $date8601, 9, 2 ),
			substr( $date8601, 12, 2 ),
			substr( $date8601, 15, 2 ),
			substr( $date8601, 4, 2 ),
			substr( $date8601, 6, 2 ),
			substr( $date8601, 0, 4 ) );
}

function _dateiso8601( $timestamp )
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	return gmstrftime( "%Y%m%dT%H:%M:%S", $timestamp );
}


function send_failure( $msg )
{
	print(  "<methodResponse>\n" .
			"<fault><value><struct>\n" .
			"<member>\n" .
			"<name>faultCode</name>\n" .
			"<value><int>1</int></value>\n" .
			"</member>\n" .
			"<member>\n" .
			"<name>faultString</name>\n" .
			"<value><string>" . _escape_content($msg) . "</string></value>\n" .
			"</member>\n" .
			"</struct></value></fault>\n" .
			"</methodResponse>\n" );
}

function _getCategoryIdByName( $name_array )
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	DEBUG( "Finding: " . $name . "\n" );

	$category = new Category();
	$category->open();

	$name = $name_array[0];
	$id = $name;

	while(1)
	{
		DEBUG( " Category: " . $category->name . "\n" );
		if( $category->name == $name )
		{
			$id = $category->id;
			break;
		}
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	return $id;

}

function _getCategoryNameById( $id )
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	DEBUG( "Finding: " . $name . "\n" );

	$category = new Category();
	$category->open();

	$name = $id;

	while(1)
	{
		DEBUG( " Category: " . $category->name . "\n" );
		if( $category->id == $id )
		{
			$name = $category->name;
			break;
		}
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	return $name;

}

/* Copied from blog/owner/entry/attach/index.php:getMIMEType,addAttachment */

function getMIMEType($ext,$filename=null){
	if($filename){
		return '';
	}else{
		switch(strtolower($ext)){
			case 'gif':
				return 'image/gif';
			case 'jpeg':
			case 'jpg':
			case 'jpe':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'tiff':
			case 'tif':
				return 'image/tiff';
			case 'bmp':
				return 'image/bmp';
			case 'wav':
				return 'audio/x-wav';
			case 'mpga':
			case 'mp2':
			case 'mp3':
				return 'audio/mpeg';
			case 'm3u':
				return 'audio/x-mpegurl';
			case 'wma':
				return 'audio/x-msaudio';
			case 'ra':
				return 'audio/x-realaudio';
			case 'css':
				return 'text/css';
			case 'html':
			case 'htm':
			case 'xhtml':
				return 'text/html';
			case 'rtf':
				return 'text/rtf';
			case 'sgml':
			case 'sgm':
				return 'text/sgml';
			case 'xml':
			case 'xsl':
				return 'text/xml';
			case 'mpeg':
			case 'mpg':
			case 'mpe':
				return 'video/mpeg';
			case 'qt':
			case 'mov':
				return 'video/quicktime';
			case 'avi':
			case 'wmv':
				return 'video/x-msvideo';
			case 'pdf':
				return 'application/pdf';
			case 'bz2':
				return 'application/x-bzip2';
			case 'gz':
			case 'tgz':
				return 'application/x-gzip';
			case 'tar':
				return 'application/x-tar';
			case 'zip':
				return 'application/zip';
		}
	}
	return '';
}

function addAttachment($owner,$parent,$file){
	global $database;
	/*
	if(empty($file['name'])||($file['error']!=0))
		return false;
	if(fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Attachments WHERE owner=$owner AND parent=$parent AND label='{$file['name']}'")>0){
		return false;
	}
	*/
	$attachment=array();
	$attachment['parent']=$parent?$parent:0;
	$attachment['label']=Path::getBaseName($file['name']);
	$label=mysql_escape_string($attachment['label']);
	$attachment['size']=$file['size'];
	$extension=Path::getExtension($attachment['label']);
	$extension = substr( $extension, 1 );
	switch(strtolower($extension)){
		case 'exe':
		case 'php':
		case 'sh':
		case 'com':
		case 'bat':
			$extension='xxx';
			break;
	}
	$path="../../attach/$owner";
	if(!is_dir($path)){
		mkdir($path);
		if(!is_dir($path))
			return false;
		@chmod($path,0777);
	}
	do{
		$attachment['name']=rand(1000000000,9999999999).".$extension";
		$attachment['path']="$path/{$attachment['name']}";
	}while(file_exists($attachment['path']));

	/* Fixed by coolengineer */
	if( $file['content'] )
	{
		$f = fopen( $attachment['path'], "w" );
		if( !$f )
		{
			return false;
		}
		DEBUG( "Length 1: " . $attachment['size'] . "\n");
		$attachment['size'] = fwrite( $f, $file['content'] );
		DEBUG( "Length 2: " . $attachment['size'] . "\n");
		fclose( $f );
		$file['tmp_name'] = $attachment['path'];
	}

	if($imageAttributes=@getimagesize($file['tmp_name'])){
		$attachment['mime']=$imageAttributes['mime'];
		$attachment['width']=$imageAttributes[0];
		$attachment['height']=$imageAttributes[1];
	}else{
		$attachment['mime']=getMIMEType($extension);
		$attachment['width']=0;
		$attachment['height']=0;
	}
/*
	if(!move_uploaded_file($file['tmp_name'],$attachment['path']))
		return false;
*/
	@chmod($attachment['path'],0666);
	$result=mysql_query("insert into {$database['prefix']}Attachments values ($owner, {$attachment['parent']}, '{$attachment['name']}', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if(!$result){
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}

/* Up to here, copied from blog/owner/entry/attach/index.php */

/* Work around , copied from blog/owner/entry/delete/item.php -r594 */
function getAttachments($owner,$parent){
	global $database;
	$attachments=array();
	if($result=mysql_query("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent")){
		while($attachment=mysql_fetch_array($result))
			array_push($attachments,$attachment);
	}
	return $attachments;
}
function deleteAttachment($owner,$parent,$name){
	global $database, $blogapi_dir;
	@unlink("$blogapi_dir/../../attach/$owner/$name");
	if($parent===false){
		return true;
	}
	$name=mysql_escape_string($name);
	if(mysql_query("delete from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'")&&(mysql_affected_rows()==1)){
		return true;
	}
	return false;
}
function deleteAttachments($owner,$parent){
	$attachments=getAttachments($owner,$parent);
	foreach($attachments as $attachment)
		deleteAttachment($owner,$parent,$attachment['name']);
}

function deleteGarbageTags(){
	global $database,$owner;
	$gc=fetchQueryColumn("SELECT t.id FROM {$database['prefix']}Tags t LEFT JOIN {$database['prefix']}TagRelations r ON t.id = r.tag WHERE r.owner = $owner AND r.tag IS NULL");
	foreach($gc as $g)
		mysql_query("DELETE FROM {$database['prefix']}Tags WHERE id = $g");
}
/* Work around end */

function preview_encode( $owner, $name )
{
	return "?__preview__{" . $owner . "," . $name . "}";
}

function preview_decode_core( $content )
{
	if( preg_match( "/\?__preview__{([^,]+),([^}]+)}/", $content, $matches ) )
	{
		return array( $matches[1], $matches[2] );
	}
	return false;
}

function preview_decode( $content, $parent )
{
	global $owner;
	$attaches = array();
	while( list($o,$n) = preview_decode_core($content) )
	{
DEBUG("Owner: $o, Name $n\n" );
		array_push( $attaches, array( $o, $n ) );
		$content = preg_replace( "/\?__preview__[^}]+}/", "", $content, 1 );
	}
	DEBUG( $attaches, true );
	return array( $content, $attaches );
}

function fixAttachments( $attaches, $parent )
{
	global $database, $owner;
	deleteAttachments( $owner, $parent );
	foreach( $attaches as $att )
	{
		if( $att[0] == $owner )
		{
			DEBUG( "update {$database['prefix']}Attachments set parent=$parent where owner=$owner and parent=0 and name='" . $att[1] . "'\n");
			mysql_query( "update {$database['prefix']}Attachments set parent=$parent where owner=$owner and parent=0 and name='" . $att[1] . "'");
		}
	}
}

/*--------- API main ---------------*/
function BlogAPI()
{
	include "blogger.php";
	include "metaweblog.php";

	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	if( $debug == 11 )
	{
		$f = fopen( "test.xml", "r" );
		$xml = fread( $f, 32768 );
		fclose( $f );
	}

	$functions = array(
		"blogger.getUsersBlogs",
		"blogger.newPost",
		"blogger.editPost",
		"blogger.getTemplate",
		"blogger.getRecentPosts",
		"blogger.deletePost", 
		"blogger.getPost", 
		"metaWeblog.newPost",
		"metaWeblog.getPost",
		"metaWeblog.getCategories",
		"metaWeblog.getRecentPosts",
		"metaWeblog.editPost",
		"metaWeblog.newMediaObject",
		"mt.getPostCategories",
		"mt.setPostCategories",
		"mt.getCategoryList" );

	$xmlrpc = new XMLRPC;

	foreach( $functions as $func )
	{
		$callback = str_replace( ".", "_", $func );
		$xmlrpc->registerMethod( $func, $callback );
	}

	$xmlrpc->receive( $xml );

	if( $debug == 11 )
	{
		print($xml);
	}

	if( $debug )
	{
		fclose( $debugfd );
	}

	DEBUG( "\nTRANSACTION ---------- end  -----------\n");

	if(!headers_sent())
	{
		send_failure( $xml );
	}
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
	$homeurl = $hostURL.$blogURL;
	$apiurl = $homeurl . "/plugin/BlogAPI";

	print( '<?xml version="1.0" ?> 
<rsd version="1.0">
    <service>
        <engineName>Tattertools</engineName> 
        <engineLink>http://www.tattertools.com/</engineLink>
        <homePageLink>' . $homeurl . '</homePageLink>
        <apis>
                <api name="MetaWeblog" preferred="true" apiLink="' . $apiurl . '" blogID="" />
                <api name="Blogger" preferred="false" apiLink="' . $apiurl . '" blogID="" />
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
