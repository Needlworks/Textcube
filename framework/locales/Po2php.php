<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Po2php
{
	var $msgs;
	var $nomsgs;
	var $comments;

	function open( $source_file )
	{
		$fsource = fopen( $source_file, "r" );
		if( !$fsource )
		{
			return 0;
		}

		$state = 0;
		$this->msgs = array();
		$this->nomsgs = array();
		$this->comments = array();
		$comment = '';
		while( !feof( $fsource ) )
		{
			$line = fgets( $fsource, 4096 );
			$line = rtrim($line);
			if( substr($line,0,1) == "#" )
			{
				$comment .= "$line\r\n";
				continue;
			}

			if( $state == 0 )
			{
				if( preg_match( '/^msgid\s+"(.*)"/', $line, $container ) )
				{
					$state = 1;
					$msgid = $container[1];
					continue;
				}
			}
			else if( $state == 1 )
			{
				if( preg_match( '/^msgstr\s+"(.*)"/', $line, $container ) )
				{
					$msgstr = $container[1];
					$state = 2;
				}
				else
				{
					$line = preg_replace( '/^"|"$/', "", $line );
					$msgid .= $line;
				}
				continue;
			}
			else if( $state == 2 )
			{
				if( preg_match( '/^\s*$/', $line ) )
				{
					if( $msgid != "" )
					{
						if( $msgstr == "" )
						{
							$this->nomsgs[$msgid] = "";
						}
						else
						{
							$this->msgs[$msgid] = $msgstr;
						}
						$this->comments[$msgid] = $comment;
					}
					$comment = "";
					$state = 0;
				}
				else
				{
					$line = preg_replace( '/^"|"$/', "", $line );
					$msgstr .= $line;
				}
			}

		}

		fclose( $fsource );

		return 1;
	}

	function save( $target_file )
	{
		if( !is_writable( $target_file ) ) {
			return 0;
		}
		$ftarget = fopen( $target_file, "w+" );
		if( !$ftarget )
		{
			return 0;
		}

		$msgs = array_merge( $this->msgs, $this->nomsgs );
		ksort( $msgs );
		fwrite( $ftarget, "<?php\r\n" );
		foreach( $msgs as $msgid => $msgstr )
		{
			$comment = $this->comments[$msgid];

			if( $msgstr == "" )
			{
				$pass = "//";
			}
			else
			{
				$pass = "";
			}

			$msgid = str_replace( '\"', '"', $msgid );
			$msgid = str_replace( '\\\\', '\\', $msgid );
			$msgstr = str_replace( '\"', '"', $msgstr );
			$msgstr = str_replace( '\\\\', '\\', $msgstr );

			fwrite( $ftarget, $comment );
			fwrite( $ftarget, $pass . '$' . "__text['$msgid'] = '$msgstr';\r\n" );
		}

		fwrite( $ftarget, "?>\r\n" );

		fclose( $ftarget );

		return 1;
	}

	function saveMsgstrAsMsgid( $target_file )
	{
		$ftarget = fopen( $target_file, "w+" );
		if( !$ftarget )
		{
			return 0;
		}

		$msgs = array_merge( $this->msgs, $this->nomsgs );
		ksort( $msgs );
		fwrite( $ftarget, "#,\r\n" );
		fwrite( $ftarget, "msgid \"\"\r\n" );
		fwrite( $ftarget, "msgstr \"\"\r\n" );
		fwrite( $ftarget, "\"Project-Id-Version: PACKAGE VERSION\\n\"\r\n" );
		fwrite( $ftarget, "\"Report-Msgid-Bugs-To: \\n\"\r\n" );
		fwrite( $ftarget, "\"POT-Creation-Date: " .  strftime( "%Y-%m-%d %H:%M+0000" ) . "\\n\"\r\n" );
		fwrite( $ftarget, "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\r\n" );
		fwrite( $ftarget, "\"Last-Translator: TEXTCUBE\\n\"\r\n" );
		fwrite( $ftarget, "\"Language-Team: TEXTCUBE\\n\"\r\n" );
		fwrite( $ftarget, "\"MIME-Version: 1.0\\n\"\r\n" );
		fwrite( $ftarget, "\"Content-Type: text/plain; charset=UTF-8\\n\"\r\n" );
		fwrite( $ftarget, "\"Content-Transfer-Encoding: 8bit\\n\"\r\n" );
		fwrite( $ftarget, "\r\n" );


		foreach( $msgs as $msgid => $msgstr )
		{
			$comment = $this->comments[$msgid];

			fwrite( $ftarget, $comment );
			fwrite( $ftarget, "msgid \"$msgstr\"\r\n" );
			fwrite( $ftarget, "msgstr \"\"\r\n" );
			fwrite( $ftarget, "\r\n" );
		}

		fclose( $ftarget );

		return 1;
	}

	function Convert($source_file, $target_file)
	{
		$this->open($source_file);
		$this->save($target_file);
	}
}
