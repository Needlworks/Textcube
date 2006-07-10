<?
/*--------- Blogger API functions -----------*/

function blogger_getUsersBlogs()
{
	global $service, $blog, $hostURL, $blogURL;
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	DEBUG( "\nhostURL: $hostURL\nblogURL: $blogURL\n\$service = " );
	DEBUG( $service, true );
	DEBUG( "\n\$blog = " );
	DEBUG( $blog, true );
	DEBUG( "\n" );

	$params = func_get_args();
	$result = _login( $params[1], $params[2] );
	if( $result )
	{
		return $result;
	}

	$blogs = array( 
		array( 
				"url" => $hostURL . $blogURL,
				"blogid" => $service['domain'] . $blogURL,
				"blogName" => $blog['title'],
		) 
	);
	return $blogs;
}

function blogger_newPost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$post = new Post();
	$post->content = $params[4];
	$post->title = htmlspecialchars(_get_title($params[4]));

	if( $params[5] )
	{
		$post->visibility = "public";
	}
	else
	{
		$post->visibility = "private";
	}

	$result = _login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	if( !$post->add() )
	{
		$post->close();
		return XMLRPCFault( 1, "Posting error" );
	}

	RSS::refresh();

	$id = "{$post->id}";
	$post->close();
	DEBUG( "Result: $id\n" );
	return $id;
}

function blogger_editPost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();

	$result = _login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->title = htmlspecialchars(_get_title( $params[4] ));
	$post->id = intval($params[1]);
	$post->content = htmlspecialchars($params[4]);

	if( $params[5] )
	{
		$post->visibility = "public";
	}
	else
	{
		$post->visibility = "private";
	}

	$ret = $post->update();
	$post->close();

	RSS::refresh();

	return $ret ? 1 : 0;
}

function blogger_deletePost()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

/*
	$post = new Post();
	$post->count = 1;
	$ret = $post->remove( intval( $params['blog_post_id'] ) );
	$post->close();
*/

// Workaround: TT must fix "DELETE FROM FROM" typo error
	{
		global $database, $owner;
		$id = intval( $params[1] );
		$filter = 'AND id = ' . $id;
		$ret = mysql_query("DELETE FROM {$database['prefix']}Entries " .
			"WHERE owner = $owner AND category >= 0 $filter");
		if(mysql_affected_rows()>0){
			$result=mysql_query("DELETE FROM {$database['prefix']}Comments WHERE owner = $owner AND entry = $id");
			$result=mysql_query("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND entry = $id");
			$result=mysql_query("DELETE FROM {$database['prefix']}TrackbackLogs WHERE owner = $owner AND entry = $id");
			$result=mysql_query("DELETE FROM {$database['prefix']}TagRelations WHERE owner = $owner AND entry = $id");
			deleteAttachments($owner,$id);
			RSS::refresh();
		}
	}
// To here.

	DEBUG( $ret ? "Deleted.\n" : "Error-occurred.\n" );
	return $ret;

}

function _get_post( $post, $type = "bl" )
{ 
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	global $service, $hostURL, $blogURL;
	return array( 
				"userid" => "",
				"dateCreated" => _dateiso8601( $post->created ),
				"datePosted" => _dateiso8601( $post->published ),
				"dateModified" => _dateiso8601( $post->modified ),
				"title" =>  _escape_content($post->title),
				"postid" => $post->id,
				"categories" => array( _getCategoryNameById($post->category) ),
				"link" => $hostURL . $blogURL . "/" . $post->id ,
				"permaLink" => $hostURL . $blogURL . "/" . $post->id ,
				"description" => ($type == "mt" ? $post->content : "" ),
				"content" => $post->content
				);
}

function blogger_getRecentPosts()
{
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open();
	$out = array();

	for($i=0; $i<$params[4]; $i++ )
	{
		array_push( $out, _get_post( $post, "bl" ) );
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
	DEBUG( "Enter: " . __FUNCTION__ . "\n" );
	$params = func_get_args();
	$result = _login( $params[2], $params[3] );
	if( $result )
	{
		return $result;
	}

	$post = new Post();
	$post->open( intval( $params[1] ) );

	$ret = _get_post( $post );
	$post->close();

	return $ret;

}

function blogger_getTemplate()
{
	$params = func_get_args();
	$result = _login( $params[2], $params[3] );
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
			$template .= $fgets( $fd, 4096 );
		}
		fclose( $fd );
	}
	return htmlspecialchars($template);
}

?>
