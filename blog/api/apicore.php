<?php
requireComponent( "Eolin.PHP.Core" );
requireComponent( "Eolin.PHP.XMLStruct" );
requireComponent( "Eolin.PHP.XMLTree" );
requireComponent( "Eolin.PHP.XMLRPC" );
requireComponent( "Tattertools.Control.RSS" );
requireComponent( "Tattertools.Core" );
requireComponent( "Tattertools.Control.Auth" );
requireComponent( "Tattertools.Data.Post" );
requireComponent( "Tattertools.Data.Category" );

/*--------- Tatter tools Core component load   -----------*/
DEBUG( "\nTRANSACTION ---------- start   ----------- [" . date("r") . "]\n");
DEBUG( "Agent: " . $_SERVER["HTTP_USER_AGENT"] );
DEBUG( "\nTRANSACTION ---------- request -----------\n" );
DEBUG( $GLOBALS['HTTP_RAW_POST_DATA'] );
DEBUG( "\nTRANSACTION ---------- api -----------\n");

/*--------- Basic functions -----------*/

function api_get_request_id( $id )
{
	if( $_GET["id"] )
	{
		DEBUG( "\nUse url request id: ". $_GET["id"] . "\n");
		return $_GET["id"];
	}
	return $id;
}

function api_get_canonical_id( $id )
{
	$alias_file = ROOT . "/.htaliases";
	$canon = api_get_request_id( $id );

	if( !file_exists( $alias_file ) )
	{
		return $canon;
	}

	$fd = fopen( $alias_file, "r" );
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

function api_login( $id, $password )
{
	DEBUG( "\n_login: ID: $id, PASSWORD: $password\n" );

	$auth = new Auth;
	if( !$auth->login( $id, $password ) )
	{
		$canon_id = api_get_canonical_id($id);
		if( !$auth->login( $canon_id, $password ) )
		{
			DEBUG( "api_login: Authentication failed.\n" );
			return new XMLRPCFault( 1, "Authentication failed: $id($canon_id)" );
		}
	}
	DEBUG( "api_login: Authenticated.\n" );
	return false;
}

function api_utf8_substr($str,$start) 
{ 
	preg_match_all("/./u", $str, $ar); 

	if(func_num_args() >= 3) { 
		$end = func_get_arg(2); 
		return join("",array_slice($ar[0],$start,$end)); 
	} else { 
		return join("",array_slice($ar[0],$start)); 
	} 
} 

function api_get_title( $content )
{
	if( preg_match( "{<title>(.+)?</title>}", $content, $match ) )
	{
		return $match[1];
	}
	$title = preg_replace( "{<.*?>}", "", $content);
	$title = api_utf8_substr( $title, 0, 40 );
	return $title;
}

function api_escape_content( $content )
{
	$content = str_replace( "\r", '', $content );
	return htmlspecialchars($content);
}

function api_timestamp( $date8601 )
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

function api_dateiso8601( $timestamp )
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
			"<value><string>" . api_escape_content($msg) . "</string></value>\n" .
			"</member>\n" .
			"</struct></value></fault>\n" .
			"</methodResponse>\n" );
}

function api_getCategoryIdByName( $name_array )
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

function api_getCategoryNameById( $id )
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

function api_make_post( $param, $ispublic, $postid = -1 )
{
	$post = new Post();
	if( $postid != -1 )
	{
		if( !$post->open( $postid ) )
		{
			return false;
		}
	}

	$post->content = $param['description'];
	$post->title = $param['title'];
	$post->tags = array_merge( split(",", $param['mt_excerpt']) , $param['tagwords'] );

	$post->created = api_timestamp( $param['dateCreated'] );
	$post->modified = api_timestamp( $param['dateCreated'] );

	$post->category = api_getCategoryIdByName( $param['categories'] );
	$post->acceptComment = $param['mt_allow_comments'] !== 0 ? true : false;
	$post->acceptTrackback = $param['mt_allow_pings'] !== 0 ? true : false;

	if( $ispublic )
	{
		$post->visibility = "public";
		$post->published = api_timestamp( $param['dateCreated'] );
	}
	else
	{
		$post->visibility = "private";
	}

	return $post;
}

function api_get_post( $post, $type = "bl" )
{ 
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$post->loadTags();
	DEBUG( "Post " );
	DEBUG( $post, true );
	$params = func_get_args();
	global $service, $hostURL, $blogURL;
	return array( 
				"userid" => "",
				"dateCreated" => api_dateiso8601( $post->created ),
				"datePosted" => api_dateiso8601( $post->published ),
				"dateModified" => api_dateiso8601( $post->modified ),
				"title" =>  api_escape_content($post->title),
				"postid" => $post->id,
				"categories" => array( api_getCategoryNameById($post->category) ),
				"link" => $hostURL . $blogURL . "/" . $post->id ,
				"permaLink" => $hostURL . $blogURL . "/" . $post->id ,
				"description" => ($type == "mt" ? $post->content : "" ),
				"content" => $post->content,
				"mt_allow_comments" => $post->acceptComment ? 1 : 0,
				"mt_allow_pings" => $post->acceptTrackback ? 1 : 0,
				"mt_excerpt" => join( ",", $post->tags )
				);
}

/* Copied from blog/owner/entry/attach/index.php:getMIMEType,addAttachment */

function api_getMIMEType($ext,$filename=null){
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


function api_file_hash( $content )
{
	$md5sum = md5( $content );
	return sprintf( "ta%stt%ser%s", substr( $md5sum, 0, 7 ), substr( $md5sum, 7, 7 ), substr( $md5sum, 14, 7 ) );
}


function api_addAttachment($owner,$parent,$file){
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

	/* Create directory for owner */
	$path="../../attach/$owner";
	if(!is_dir($path)){
		mkdir($path);
		if(!is_dir($path))
			return false;
		@chmod($path,0777);
	}

	/* Select unique file name from md5sum of content */
	$attachment['name'] = api_file_hash( $file['content'] )  . ".$extension";
	$attachment['path'] = "$path/{$attachment['name']}";

	api_deleteAttachment($owner,-1,$attachment['name']);

	if( $file['content'] )
	{
		$f = fopen( $attachment['path'], "w" );
		if( !$f )
		{
			return false;
		}
		$attachment['size'] = fwrite( $f, $file['content'] );
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
DEBUG("\nCHECK " . __LINE__ );
	@chmod($attachment['path'],0666);
	DEBUG( "\ninsert into {$database['prefix']}Attachments values ($owner, {$attachment['parent']}, '{$attachment['name']}', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	$result=mysql_query("insert into {$database['prefix']}Attachments values ($owner, {$attachment['parent']}, '{$attachment['name']}', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if(!$result){
DEBUG("\nCHECK " . __LINE__ );
		@unlink($attachment['path']);
		return false;
	}
DEBUG("\nCHECK " . __LINE__ );
	return $attachment;
}


/* Up to here, copied from blog/owner/entry/attach/index.php */

/* Work around , copied from blog/owner/entry/delete/item.php -r594 */
function api_getAttachments($owner,$parent){
	global $database;
	$attachments=array();
	if($result=mysql_query("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent")){
		while($attachment=mysql_fetch_array($result))
			array_push($attachments,$attachment);
	}
	return $attachments;
}
function api_deleteAttachment($owner,$parent,$name){
	global $database, $blogapi_dir;
	@unlink(ROOT . "/attach/$owner/$name");
	$name=mysql_escape_string($name);
	$parent_clause = "";
	if( $parent >= 0 )
	{
		$parent_clause = "parent = $parent and ";
	}
	DEBUG("\ndelete from {$database['prefix']}Attachments where owner = $owner and $parent_clause name = '$name'");
	if(mysql_query("delete from {$database['prefix']}Attachments where owner = $owner and $parent_clause name = '$name'")&&(mysql_affected_rows()==1)){
		return true;
	}
	DEBUG("\nDelete failure: " . mysql_error() );
	return false;
}
function api_deleteAttachments($owner,$parent){
	$attachments=api_getAttachments($owner,$parent);
	foreach($attachments as $attachment)
		api_deleteAttachment($owner,$parent,$attachment['name']);
}

function api_deleteGarbageTags(){
	global $database,$owner;
	$gc=fetchQueryColumn("SELECT t.id FROM {$database['prefix']}Tags t LEFT JOIN {$database['prefix']}TagRelations r ON t.id = r.tag WHERE r.owner = $owner AND r.tag IS NULL");
	foreach($gc as $g)
		mysql_query("DELETE FROM {$database['prefix']}Tags WHERE id = $g");
}
/* Work around end */

function api_get_attaches( $content, $parent )
{
	global $owner;
	preg_match_all( "/attach\/$owner\/(ta.{7}tt.{7}er.{7}\.[a-z]{2,5})/", $content, $matches );
	DEBUG( $matches[1], true );
	return $matches[1];
}

function api_update_attaches( $attaches, $parent )
{
	global $database, $owner;
	foreach( $attaches as $att )
	{
		DEBUG( "update {$database['prefix']}Attachments set parent=$parent where owner=$owner and parent=0 and name='" . $att . "'\n");
		mysql_query( "update {$database['prefix']}Attachments set parent=$parent where owner=$owner and parent=0 and name='" . $att . "'");
	}
}

/*--------- API main ---------------*/
function api_BlogAPI()
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

?>
