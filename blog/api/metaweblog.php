<?php
/*--------- MetaWebLog API functions -----------*/

function metaWeblog_getCategories()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	global $service, $hostURL, $blogURL;
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$category = new Category();
	$category->open();

	DEBUG( $category, true );

	$cat = array();
	while($category->id)
	{
		DEBUG( " Category: " . $category->name . "\n" );
		array_push( $cat, array( 
			'htmlUrl' => "$hostURL$blogURL/category/" . $category->name,
			'rssUrl' => "$hostURL$blogURL/SubRSS.php?ct1=" . $category->id,
			'categoryName' => $category->name,
			'description' => $category->name,
			'categoryId' => $category->id,
			'isPrimary' => true ) );
			
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	DEBUG( $cat, true );
	return $cat;

}

/*
function mt_getCategoryList()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	global $service;
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$category = new Category();
	$category->open();

	$cat = array();
	$base_url = "http://" . $service['domain'] . $service['path'] . "/";
	while(1)
	{
		DEBUG( " Category: " . $category->name . "\n" );
		array_push( $cat, array( 
			'categoryName' => $category->name,
			'categoryId' => $category->id,
			'isPrimary' => true ) );
			
		if( !$category->shift() )
		{
			break;
		}
	}

	$category->close();
	DEBUG( $cat, true );
	return $cat;

}
*/

function metaWeblog_getRecentPosts()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	global $blog;
	DEBUG( $blog, true );

	$post = new Post();
	$post->open();
	$out = array();

	for($i=0; $post->_count > 0 && $i<$params[3]; $i++ )
	{
		array_push( $out, _get_post( $post, "mt" ) );
		if( !$post->shift() )
		{
			break;
		}
	}

	$post->close();
	DEBUG($out, true);
	return $out;
}

function metaWeblog_getPost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[0] ) );

	$ret = _get_post( $post, "mt" );
	$post->close();

	DEBUG( $ret, true );
	return $ret;
}

function metaWeblog_newPost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = _make_post( $params[3], $params[4] );

	$attaches = _get_attaches( $post->content );

	if( !$post->add() )
	{
		$post->close();
		DEBUG( "Adding failure." );
		return XMLRPCFault( 1, "Tattertools posting error" );
	}

	_update_attaches( $attaches, $post->id );
	RSS::refresh();

	DEBUG( $post, true );

	$id = "{$post->id}";
	$post->close();
	DEBUG( "Result: $id\n" );
	return $id;
}
 
function mt_setPostCategories()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();

	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	if( !$post->open( $params[0] ) )
	{
		DEBUG( "Can't open document (id:" . $params[0] . ")\n" );
		return XMLRPCFault( 1, "Posting error" );
	}

	$category = "";
	DEBUG( $params[3], true );
	foreach( $params[3] as $index => $cat )
	{
		DEBUG( " category: " . $cat['categoryName'] . " (" . $cat['isPrimary'] . ")\n" );
		if( $cat['isPrimary'] )
		{
			$category = $cat['categoryId'];
		}
	}
	if( $category )
	{
		$post->category = $category;
		$post->update();
	}
	DEBUG( $post, true );
	$post->close();
	return 1;
}

function mt_getPostCategories()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	global $service;
	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[0] ) );

	$cat = array( $post->category );
	$post->close();

	DEBUG( $cat, true );

	return $cat;
}

function metaWeblog_editPost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	DEBUG( "Params: " );
	DEBUG( $params, true );
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$post = _make_post( $params[3], $params[4], $params[0] );
	if( !$post )
	{
		return XMLRPCFault( 1, "Tattertools editing error" );
	}

	$attaches = _get_attaches( $post->content );

	$ret = $post->update();

	_update_attaches( $attaches, $post->id );
	RSS::refresh();

	DEBUG( $post, true );
	$post->close();

	return $ret ? 1 : 0;
}

function metaWeblog_newMediaObject()
{
	global $owner;
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	DEBUG( "Params: " );
	DEBUG( $params, true );
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}
	$mediaOjbect = $params[3]['bits'];

	$tmp_dir = ROOT. "/attach/temp";
	if( !is_dir( $tmp_dir ) )
	{
		mkdir( $tmp_dir );
		if( !is_dir( $tmp_dir ) )
		{
			return new XMLRPCFault( 1, "Can't Create Directory $tmp_dir" );
		}
		@chmod( $path, 0777 );
	}

	$file = array( 
		'name' => $params[3]['name'],
		'content' => $params[3]['bits'],
		'size' => count($params[3]['bits']) 
		);
		
	$attachment = _addAttachment( $owner, 0, $file );
	if( !$attachment )
	{
		return new XMLRPCFault( 1, "Can't create file" );
	}
	$attachurl = array ( 'url' => getBlogURL() . "/attach/$owner/" . $attachment['name'] );
	DEBUG( "\nAttached url : " );
	DEBUG( $attachurl, true );
	return $attachurl;
}
?>
