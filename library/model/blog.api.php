<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/*--------- Basic functions -----------*/

function api_setHint( $hint, $value = true )
{
	global $arr_hint;
	if( !$arr_hint )
	{
		$arr_hint = array();
	}
	$arr_hint[$hint] = $value;
}

function api_getHint( $hint )
{
	global $arr_hint;
	if( $arr_hint && isset( $arr_hint[$hint] ) )
	{
		return $arr_hint[$hint];
	}
	return false;
}

function api_get_request_id( $id )
{
	if(array_key_exists('id', $_GET))
	{
		return $_GET["id"];
	}
	return $id;
}

function api_get_canonical_id( $id )
{
	$alias_file = ROOT . "/.htaliases";
	$canon = api_get_request_id( $id );
	
	if( $id == "" || !file_exists( $alias_file ) )
	{
		return $canon;
	}
	
	$fd = fopen( $alias_file, "r" );
	if( $fd == FALSE )
	{
		return $canon;
	}
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
	$auth = new Auth;
	if( !$auth->login( $id, $password ) )
	{
		$canon_id = api_get_canonical_id($id);
		if( !$auth->login( $canon_id, $password ) )
		{
			return new XMLRPCFault( 1, "Authentication failed: $id($canon_id)" );
		}
	}
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
	if ($name_array === '') return 0;
	if (count($name_array) <= 0) return 0;
	$category = new Category();
	$category->open(false);
	
	$name = strtolower( $name_array[0] );
	$id = 0;
	$numeric_id = null;
	
	while(1)
	{
		if( strtolower($category->label) == $name ) {
			$id = $category->id;
			break;
		}
		if( $category->id == $name ) {
			$numeric_id = $category->id;
		}
		if( !$category->shift() )
		{
			break;
		}
	}

	if( $id == 0 && $numeric_id !== null ) {
		$id = $numeric_id;
	}
	
	$category->close();
	return $id;
	
}

function api_getCategoryNameById( $id )
{
	$category = new Category();
	$category->open();
	
	$name = $id;
	
	while(1)
	{
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

function api_fix_content( $content )
{
	$ctx = Model_Context::getInstance();
	$new_content = "";
	if( strstr( $_SERVER["HTTP_USER_AGENT"], 'Zoundry' ) )
	{
		$new_content = str_replace( "</ul>\n", "</ul>", $content );
		$new_content = str_replace( "</ol>\n", "</ol>", $new_content );
		$new_content = str_replace( "</li>\n", "</li>", $new_content );
		$new_content = str_replace( "</div>\n", "</div>", $new_content );
		$new_content = str_replace( "</p>\n", "</p>", $new_content );
	}
	else
	{
		$new_content = $content;
	}
	// Fix attachment URL into TTML-spcific address.
	
	$new_content = str_replace($ctx->getProperty('uri.host').$ctx->getProperty('service.path')."/attach/".$blogid, '[##_ATTACH_PATH_##]',$new_content);
	return $new_content;
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
	
	$post->content = api_fix_content( $param['description'] );
	$post->contentformatter = getDefaultFormatter();
	$post->contenteditor = getDefaultEditor();
	$post->title = $param['title'];
	
	//$param['mt_excerpt'] = array_key_exists('mt_excerpt', $param) ? $param['mt_excerpt'] : '';
	//$param['tagwords'] = array_key_exists('tagwords', $param) ? $param['tagwords'] : array();
	$param['categories'] = array_key_exists('categories', $param) ? $param['categories'] : '';
	$param['dateCreated'] = array_key_exists('dateCreated', $param) ? $param['dateCreated'] : api_dateiso8601(time());
	$param['mt_allow_comments'] = array_key_exists('mt_allow_comments', $param) ? $param['mt_allow_comments'] : '';
	$param['mt_allow_pings'] = array_key_exists('mt_allow_pings', $param) ? $param['mt_allow_pings'] : '';
	$param['mt_keywords'] = array_key_exists('mt_keywords', $param) ? $param['mt_keywords'] : '';

	if( api_getHint("TagsFromCategories") )
	{
		$post->tags = array_merge( $param['categories'], explode(',', $param['mt_excerpt']) , $param['tagwords'] );
	}
	else
	{
		//$post->tags = array_merge( explode(",", $param['mt_excerpt']) , $param['tagwords'] );
		$post->tags = explode(',', $param['mt_keywords']);
		$post->category = api_getCategoryIdByName( $param['categories'] );
	}

	$extra_category = api_getHint("Category");
	if( !empty($extra_category) ) {
		$post->category = api_getCategoryIdByName( array($extra_category) );
	}
	$post->created = api_timestamp( $param['dateCreated'] );
	$post->modified = api_timestamp( $param['dateCreated'] );
	$post->published = api_timestamp( $param['dateCreated'] );
	
	$post->acceptcomment = $param['mt_allow_comments'] !== 0 ? true : false;
	$post->accepttrackback = $param['mt_allow_pings'] !== 0 ? true : false;
	
	if( $ispublic )
	{
		$post->visibility = "public";
	}
	else
	{
		$post->visibility = "private";
	}
	
	return $post;
}

function api_get_post( $post, $type = "bl" )
{ 
	$post->loadTags();
	//$params = func_get_args();
	global $hostURL, $blogURL;
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
			"mt_allow_comments" => $post->acceptcomment ? 1 : 0,
			"mt_allow_pings" => $post->accepttrackback ? 1 : 0,
			"mt_keywords" => join( ",", $post->tags )
			);
}

/* Copied from blog/owner/entry/attach/index.php:addAttachment */

function api_file_hash( $content )
{
	$md5sum = md5( $content );
	return sprintf( "ta%stt%ser%s", substr( $md5sum, 0, 7 ), substr( $md5sum, 7, 7 ), substr( $md5sum, 14, 7 ) );
}


function api_addAttachment($blogid,$parent,$file) {
	global $database;
	
	$attachment=array();
	$attachment['parent']=$parent?$parent:0;
	$attachment['label']=Path::getBaseName($file['name']);
	$label=POD::escapeString(UTF8::lessenAsEncoding($attachment['label'],64));
	$attachment['size']=$file['size'];
	$extension=Path::getExtension($attachment['label']);
	switch(strtolower($extension)) {
		case '.exe':
		case '.php':
		case '.sh':
		case '.com':
		case '.bat':
			$extension='.xxx';
			break;
	}
	
	/* Create directory for owner */
	$path = ROOT . "/attach/$blogid";
	if(!is_dir($path)) {
		mkdir($path);
		if(!is_dir($path))
			return false;
		@chmod($path,0777);
	}
	
	$oldFile = POD::queryCell("SELECT name FROM {$database['prefix']}Attachments WHERE blogid=$blogid AND parent=$parent AND label = '$label'");
	
	if ($oldFile !== null) {
		$attachment['name'] = $oldFile;
	} else {
		$attachment['name'] = rand(1000000000, 9999999999) . $extension;
		
		while (Attachment::doesExist($attachment['name']))
		$attachment['name'] = rand(1000000000, 9999999999) . $extension;
	}
	
	
	$attachment['path'] = "$path/{$attachment['name']}";
	
	deleteAttachment($blogid,-1,$attachment['name']);
	
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
	
	if($imageAttributes=@getimagesize($file['tmp_name'])) {
		$attachment['mime']=$imageAttributes['mime'];
		$attachment['width']=$imageAttributes[0];
		$attachment['height']=$imageAttributes[1];
	}else{
		$attachment['mime']=Misc::getMIMEType($extension);
		$attachment['width']=0;
		$attachment['height']=0;
	}
	
	$attachment['mime']=UTF8::lessenAsEncoding($attachment['mime'], 32);
	
	@chmod($attachment['path'],0666);
	$result=POD::query("insert into {$database['prefix']}Attachments values ($blogid, {$attachment['parent']}, '{$attachment['name']}', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if(!$result) {
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}


/* Up to here, copied from blog/owner/entry/attach/index.php */

function api_get_attaches( $content)
{
	preg_match_all( "/attach\\/".getBlogId()."\\/(ta.{7}tt.{7}er.{7}\\.[a-z]{2,5})/", $content, $matches );
	return $matches[1];
}

function api_update_attaches( $parent, $attaches = null)
{
	global $database;
	if (is_null($attaches)) {
		POD::query( "update {$database['prefix']}Attachments set parent=$parent where blogid=".getBlogId()." and parent=0");		
	} else {
		foreach( $attaches as $att )
		{
			$att = POD::escapeString($att);
			POD::query( "update {$database['prefix']}Attachments set parent=$parent where blogid=".getBlogId()." and parent=0 and name='" . $att . "'");
		}
	}
}

function api_update_attaches_with_replace($entryId)
{
	global $database;
	
	$newFiles = POD::queryAll("SELECT name, label FROM {$database['prefix']}Attachments WHERE blogid=".getBlogId()." AND parent=0");
	if( $newFiles ) {
		foreach($newFiles as $newfile) {
			$newfile['label'] = POD::escapeString(UTF8::lessenAsEncoding($newfile['label'], 64));
			$oldFile = POD::queryCell("SELECT name FROM {$database['prefix']}Attachments WHERE blogid=".getBlogId()." AND parent=$entryId AND label='{$newfile['label']}'");
		
			if (!is_null($oldFile)) {
				deleteAttachment(getBlogId(), $entryId, $oldFile);
			}
		}
	}	
	api_update_attaches($entryId);
}

/*--------- API main ---------------*/
function api_BlogAPI()
{
	global $blogApiFunctions;
	if (!array_key_exists('HTTP_RAW_POST_DATA', $GLOBALS)) {
		XMLRPC::sendFault(1, 'Invalid Method Call');
		exit;
	}
	
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];

	$blogApiFunctions = array(
		"blogger.getUsersBlogs",
		"blogger.newPost",
		"blogger.editPost",
		"blogger.getTemplate",
		"blogger.getRecentPosts",
		"blogger.deletePost", 
		"blogger.getPost", 
//		"blogger.getTemplate", // doesn't supported
//		"blogger.setTemplate", // doesn't supported
		"metaWeblog.newPost",
		"metaWeblog.getPost",
		"metaWeblog.getCategories",
		"metaWeblog.getRecentPosts",
		"metaWeblog.editPost",
		"metaWeblog.newMediaObject",
		"mt.getPostCategories",
		"mt.setPostCategories",
		"mt.getCategoryList",
//		"mt.supportedTextFilters", //지원안함 // 텍스트 처리하는 플러그인 리스트
		"mt.supportedMethods", // XMLRPC 함수 리스트
		"mt.publishPost", // rebuild post
//		"mt.getTrackbackPings", // 인증을 안하므로 무시 // 받은 트랙백 리스트
		"mt.getRecentPostTitles" // getRecentPosts와 거의 동일, 트래픽 프랜들리 버전
		 );

	$xmlrpc = new XMLRPC;

	foreach( $blogApiFunctions as $func )
	{
		$callback = str_replace( ".", "_", $func );
		$xmlrpc->registerMethod( $func, $callback );
	}

	$otherMethods = fireEvent( 'RegisterRpcMethod', array() );
	foreach( $otherMethods as $func => $callback )
	{
		$xmlrpc->registerMethod( $func, $callback );
	}

	$xmlrpc->receive( $xml );
}

/*--------- Blogger API functions -----------*/

function blogger_getUsersBlogs()
{
	global $blog, $hostURL, $blogURL;

	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$blogs = array( 
		array( 
				"url" => $hostURL . $blogURL,
				"blogid" => (string)getBlogId(),
				"blogName" => $blog['title'],
		) 
	);
	return $blogs;
}

function blogger_newPost()
{
	$params = func_get_args();
	$post = new Post();
	$post->content = $params[4];
	$post->contentformatter = getDefaultFormatter();
	$post->contenteditor = getDefaultEditor();
	$post->title = htmlspecialchars(api_get_title($params[4]));

	if( $params[5] )
	{
		$post->visibility = "public";
	}
	else
	{
		$post->visibility = "private";
	}

	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	if( !$post->add($params[2]) )
	{
		$post->close();
		return new XMLRPCFault( 1, "Posting error" );
	}
	RSS::refresh();

	$id = "{$post->id}";
	fireEvent('AddPostByBlogAPI', $id, $post);
	$post->close();
	return $id;
}

function blogger_editPost()
{
	$params = func_get_args();

	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->title = htmlspecialchars(api_get_title( $params[4] ));
	$post->id = intval($params[1]);
	$post->content = $params[4];
	$post->contentformatter = getDefaultFormatter();
	$post->contenteditor = getDefaultEditor();

	if( $params[5] )
	{
		$post->visibility = "public";
	}
	else
	{
		$post->visibility = "private";
	}

	$ret = $post->update();
	fireEvent('UpdatePostByBlogAPI', $post->id, $post);
	$post->close();

	RSS::refresh();
	if($ret!=false) Setting::setBlogSettingGlobal('LatestEditedEntry',$post->id);
	return $ret ? true : false;
}

function blogger_deletePost()
{
	$params = func_get_args();
	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$id = intval( $params[1] );
	$ret = $post->open( $id );
	$ret = $post->remove();
	deleteAttachments(getBlogId(), $id );
	fireEvent('DeletePostByBlogAPI', $id, $post);

	return $ret ? true : false;

}

function blogger_getRecentPosts()
{
	$params = func_get_args();
	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open();
	$out = array();

	for($i=0; $i<$params[4]; $i++ )
	{
		array_push( $out, api_get_post( $post, "bl" ) );
		if( !$post->shift() )
		{
			break;
		}
	}
	$post->close();
	return $out;

}

function blogger_getPost()
{
	$params = func_get_args();
	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[1] ) );

	$ret = api_get_post( $post );
	$post->close();

	return $ret;

}

function blogger_getTemplate()
{
	$params = func_get_args();
	$result = api_login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$file = ( $params[4] == "main" ? "template.main.tpl" : "template.archindex.tpl" );
	$template = "";
	if( file_exists( $file ) )
	{
		$fd = fopen( $file, "r" );
		while( !feof($fd) )
		{
			$template .= fgets( $fd, 4096 );
		}
		fclose( $fd );
	}
	return htmlspecialchars($template);
}

/*--------- MetaWebLog API functions -----------*/

function metaWeblog_getCategories()
{
	global $hostURL, $blogURL;
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$category = new Category();
	$category->open(false);


	$cat = array();
	while($category->id)
	{
		array_push( $cat, array( 
			'htmlUrl' => "$hostURL$blogURL/category/" . $category->label,
			//'rssUrl' => "",
			'categoryName' => $category->label,
			'description' => $category->label,
			'title' => $category->label,
			'categoryid' => $category->id,
			'isPrimary' => true 
			) );
			
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	return $cat;

}


function mt_getCategoryList()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$category = new Category();
	$category->open(false);

	$cat = array();
	while(1)
	{
		array_push( $cat, array( 
			'categoryName' => $category->label,
			'categoryId' => $category->id,
			'isPrimary' => true ) );
			
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	return $cat;

}


function metaWeblog_getRecentPosts()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open();
	$out = array();

	for($i=0; $post->_count > 0 && $i<$params[3]; $i++ )
	{
		array_push( $out, api_get_post( $post, "mt" ) );
		if( !$post->shift() )
		{
			break;
		}
	}

	$post->close();
	return $out;
}

function metaWeblog_getPost()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[0] ) );

	$ret = api_get_post( $post, "mt" );
	$post->close();

	return $ret;
}

function metaWeblog_newPost()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = api_make_post( $params[3], $params[4] );
	
	if ($post === false) {
		return new XMLRPCFault( 1, "Textcube posting error" );
	}
	if( !$post->add($params[1]) )
	{
		$error = $post->error;
		$post->close();
		return new XMLRPCFault( 1, "Textcube invalid field: $error" );
	}

	api_update_attaches($post->id );
	fireEvent('AddPostByBlogAPI', $post->id, $post);
	RSS::refresh();


	$id = "{$post->id}";
	$post->close();
	if($id) Setting::setBlogSettingGlobal('LatestEditedEntry',$id);
	return $id;
}
 
function mt_setPostCategories()
{
	$params = func_get_args();

	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	if( !$post->open( $params[0] ) )
	{
		return new XMLRPCFault( 1, "Posting error" );
	}

	$category = null;
	if (is_null($params[3])) $params[3] = array();
	foreach( $params[3] as $cat )
	{
		if(array_key_exists('isPrimary', $cat) && $cat['isPrimary'] )
		{
			$category = $cat['categoryId'];
		} else {
			if (is_null($category))
				$category = $cat['categoryId'];
		}
	}
	if( !is_null($category) )
	{
		$post->category = intval($category);
		$post->update();
	}
	$post->close();
	return true;
}

function mt_getPostCategories()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[0] ) );

	$cat = array( array( "categoryId" => $post->category, 
			"categoryName" => api_getCategoryNameById( $post->category ),
			"isPrimary" => true) );
	$post->close();


	return $cat;
}

function metaWeblog_editPost()
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = api_make_post( $params[3], $params[4], $params[0] );
	$post->created = null;
	if( !$post )
	{
		return new XMLRPCFault( 1, "Textcube editing error" );
	}

	$ret = $post->update();

	// 기존 글의 파일들 지우기 (잘 찾아서)
	// 새로 업로드 된 파일들 옮기기
	api_update_attaches_with_replace( $post->id );
	fireEvent('UpdatePostByBlogAPI', $id, $post);
	
	RSS::refresh();

	$post->close();
	if($ret!=false) Setting::setBlogSettingGlobal('LatestEditedEntry',$post->id);
	return $ret ? true : false;
}

function metaWeblog_newMediaObject()
{
	$ctx = Model_Context::getInstance();
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$file = array( 
		'name' => $params[3]['name'],
		'content' => $params[3]['bits'],
		'size' => count($params[3]['bits']) 
		);
		
	$attachment = api_addAttachment( getBlogId(), 0, $file );
	if( !$attachment )
	{
		return new XMLRPCFault( 1, "Can't create file" );
	}
	
	$attachurl = array ( 'url' => $ctx->getProperty('uri.host').$ctx->getProperty('service.path')."/attach/".$blogid."/".$attachment['name']);
	return $attachurl;
}

function mt_supportedMethods()
{
	global $blogApiFunctions;
	return $blogApiFunctions;
}

function mt_publishPost() /* postid, username, password */
{
	// Only check whether the post exists or not
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	if (! $post->open( intval( $params[0] ) ) ) {
		return false;
	}

	return true;
}

function mt_getRecentPostTitles() /* blogid, username, password, count */
{
	$params = func_get_args();
	$result = api_login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open();
	$out = array();

	for($i=0; $post->_count > 0 && $i<$params[3]; $i++ )
	{
		array_push( $out, api_get_post( $post, "mt" ) );
		if( !$post->shift() )
		{
			break;
		}
	}

	$post->close();
	return $out;
}
?>
