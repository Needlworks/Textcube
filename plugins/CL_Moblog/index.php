<?
requireComponent( "Needlworks.Mail.Pop3" );

class Moblog
{
	function Moblog( $username, $password, $host, $port = 110, $ssl = 0, 
		$userid = 1 )
	{
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
		$this->ssl = $ssl;
		$this->userid = $userid;

		$this->pop3 = new Pop3();
		$this->pop3->setLogger( array(&$this,'log') );
		$this->pop3->setUidFilter( array(&$this,'checkUid') );
		$this->pop3->setSizeFilter( array(&$this,'checkSize') );
		$this->pop3->setRetrCallback( array(&$this,'retrieveCallback') );

		$this->uidl_file = ROOT."/cache/pop3uidl.txt";

		if( file_exists( $this->uidl_file ) ) {
			$this->stored_uidl = file_get_contents( $this->uidl_file );
		} else {
			$this->stored_uidl = '';
		}

		global $service;
		$fallback_charsets = array( 'ko' => 'euc-kr' );
		if( isset( $fallback_charsets[$service['language']] ) ) {
			$this->pop3->setFallbackCharset( $fallback_charsets[$service['language']] );
		}
	}

	function log($msg)
	{
		print $msg."<br/>\n";
	}

	function saveUidl()
	{
		$f = fopen( $this->uidl_file, "w" );
		fwrite( $f, $this->stored_uidl );
		fclose($f);
		return true;
	}

	function check()
	{
		if( !$this->pop3->connect( $this->host, $this->port, $this->ssl ) ) {
			return false;
		}
		if( !$this->pop3->authorize( $this->username, $this->password ) ) {
			return false;
		}

		$this->pop3->run();

		if( !$this->pop3->quit() ) {
			return false;
		}
		return true;
	}

	function appendUid( $uid )
	{
		$today = date( 'Y-m-d H:i:s' );
		$this->stored_uidl .= "[$uid] $today\r\n";
		$this->saveUidl();
	}

	function checkUid( $uid )
	{
		$ret = !!strstr( $this->stored_uidl, "[$uid]" );
//		echo "Check $uid: ".($ret?"Y":"N")."<br/>\n";
		return $ret;
	}

	function checkSize( $size )
	{
		return $size < 10*1024; /* 20KB? */
	}

	function isMms( & $mail )
	{
		if( !empty($mail['mms']) ) {
			return true;
		}
		if( isset($mail['return_path']) && strstr( $mail['return_path'], 'mms' ) ) {
			/* KTF: ktfmms, SKT: vmms */
			return true;
		}
		return false;
	}

	function retrieveCallback( $lines, $uid )
	{
		$this->appendUid( $uid );
		$mail = $this->pop3->parse( $lines );
		if( !$this->isMms($mail) ) {
			$this->log( "Dismissed: this is not an MMS message" );
			return false;
		}
		if( empty($mail['attachments']) ) {
			$this->log( "Dismissed: there is no attachment" );
			return false;
		}
		requireComponent( "Textcube.Data.Post" );

		$post = new Post();
		$post->userid = $this->userid;
		$post->content = '$TEXT<br/>[##_1C|$FILENAME|width="$WIDTH" height="$HEIGHT" alt=""|_##]';
		$post->content = str_replace( '$TEXT', isset($mail['text'])?$mail['text']:'', $post->content );
		$post->contentFormatter = getDefaultFormatter();
		$post->contentEditor = getDefaultEditor();
		$post->title = $mail['subject'];
		if( empty($post->title) ) {
			$post->title = $post->$mail['attachments'][0]['filename'];
		}
		$post->created = time();
		$post->modified = time();
		$post->acceptComment = true;
		$post->acceptTrackback = true;
		$post->visibility = "public";
		$post->published = time();
		$post->add();

		$this->log( "Subject: {$mail['subject']}" );
		$this->log( "Attachment: {$mail['attachments'][0]['filename']}" );
		requireModel( "blog.api" );
		$att = api_addAttachment( getBlogId(), $post->id, 
					array( 
							'name' => $mail['attachments'][0]['filename'], 
							'content' => $mail['attachments'][0]['decoded_content'], 
							'size' => $mail['attachments'][0]['length']
					) 
			);
		$post->content = str_replace( '$FILENAME', $att['name'], $post->content );
		$post->content = str_replace( '$WIDTH', $att['width'], $post->content );
		$post->content = str_replace( '$HEIGHT', $att['height'], $post->content );
		$post->update();
		return true;
	}
}

function moblog_check()
{
	$pop3host = getBlogSetting( 'MmsPop3Host', 'localhost' );
	$pop3port = getBlogSetting( 'MmsPop3Port', 110 );
	$pop3ssl = getBlogSetting( 'MmsPop3Ssl', 0 );
	$pop3username = getBlogSetting( 'MmsPop3Username', '' );
	$pop3password = getBlogSetting( 'MmsPop3Password', '' );
	$pop3fallbackuserid = getBlogSetting( 'MmsPop3Fallbackuserid', 1 );

	$moblog = new Moblog( $pop3username, $pop3password, $pop3host, $pop3port, $pop3ssl, $pop3fallbackuserid );
	$moblog->check();
	return true;
}

function moblog_manage()
{
	requireModel("common.setting");
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		setBlogSetting( 'MmsPop3Host', $_POST['pop3host'] );
		setBlogSetting( 'MmsPop3Port', $_POST['pop3port'] );
		setBlogSetting( 'MmsPop3Ssl', !empty($_POST['pop3ssl'])?1:0 );
		setBlogSetting( 'MmsPop3Username', $_POST['pop3username'] );
		setBlogSetting( 'MmsPop3Password', $_POST['pop3password'] );
		setBlogSetting( 'MmsPop3Fallbackuserid', getUserId() );
	}
	$pop3host = getBlogSetting( 'MmsPop3Host', 'localhost' );
	$pop3port = getBlogSetting( 'MmsPop3Port', 110 );
	$pop3sslchecked = getBlogSetting( 'MmsPop3Ssl', 0 ) ? " checked " : "";
	$pop3username = getBlogSetting( 'MmsPop3Username', '' );
	$pop3password = getBlogSetting( 'MmsPop3Password', '' );
	$pop3fallheadercharset = getBlogSetting( 'MmsPop3Fallbackcharset', 'euc-kr' );
?>
						<hr class="hidden" />
						
						<div id="part-setting-editor" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('MMS 메시지 확인용 POP3 정보');?></span></h2>
							
							<form id="editor-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/setting/entry/editor">
								<div id="editor-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('MMS 환경을 설정합니다');?></legend>
										
										<dl id="formatter-line" class="line">
											<dt><span class="label"><?php echo _t('POP3 호스트');?></span></dt>
											<dd>
												<input type="text" style="width:14em" class="input-text" name="pop3host" value="<?php echo $pop3host;?>" />
											</dd>
										</dl>
										<dl id="editor-line" class="line">
											<dt><span class="label"><?php echo _t('POP3 포트');?></span></dt>
											<dd>
												<input type="text" style="width:14em" class="input-text" name="pop3port" value="<?php echo $pop3port;?>" />
												<input type="checkbox" name="pop3ssl" value="<?php echo $pop3ssl;?>" /> SSL
											</dd>
										</dl>
										<dl id="editor-line" class="line">
											<dt><span class="label"><?php echo _t('POP3 아이디');?></span></dt>
											<dd>
												<input type="text" style="width:14em" class="input-text" name="pop3username" value="<?php echo $pop3username;?>" />
											</dd>
										</dl>
										<dl id="editor-line" class="line">
											<dt><span class="label"><?php echo _t('POP3 비밀번호');?></span></dt>
											<dd>
												<input type="password" style="width:14em" class="input-text" name="pop3password" value="<?php echo $pop3password;?>" />
											</dd>
										</dl>
									<div class="button-box">
										<input type="submit" class="save-button input-button wide-button" value="<?php echo _t('저장하기');?>"  />
									</div>
								</div>
							</form>
						</div>
	</form>
<?php
}
?>
