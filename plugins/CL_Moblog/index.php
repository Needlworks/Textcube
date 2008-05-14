<?
requireComponent( "Needlworks.Mail.Pop3" );

class Moblog
{
	function Moblog( $username, $password, $host, $port = 110, $ssl = 0, 
		$userid = 1, $minsize = 10240 )
	{
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
		$this->ssl = $ssl;
		$this->userid = $userid;
		$this->minsize = $minsize;
		$this->recentCount = 100;

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
		static $blocked = false;
		if( Acl::check( 'group.administrators' ) ) {
			print $msg."<br/>\n";
		} else {
			if( !$blocked ) {
				$blocked = true;
				print "Log message prohibited. Please login with administrator's account";
			}
		}
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

	function checkSize( $size, $number, $total )
	{
		if( $number > $total - $this->recentCount ) {
			return false;
		}
		return $size < $this->minsize;
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

	function _getDecoratedContent( & $mail ) {
			$alt = htmlentities($mail['attachments'][0]['filename'],ENT_QUOTES,'utf-8');
			$content = '<p>$TEXT</p><p>[##_1C|$FILENAME|width="$WIDTH" height="$HEIGHT" alt="'.$alt.'"|_##]</p>';
			$text = "<h3>".$mail['subject']."</h3>\r\n".(isset($mail['text'])?$mail['text']:'');
			return str_replace( '$TEXT', $text , $content );
	}

	function retrieveCallback( $lines, $uid )
	{
		$this->appendUid( $uid );
		$mail = $this->pop3->parse( $lines );
		if( in_array( $mail['subject'], array( '제목없음' ) ) ) {
			$mail['subject'] = '';
		}
		$this->log( "Subject: " . $mail['subject'] );
		if( !$this->isMms($mail) ) {
			$this->log( "Dismissed: this is not an MMS message" );
			return false;
		}
		if( empty($mail['attachments']) ) {
			$this->log( "Dismissed: there is no attachment" );
			return false;
		}
		$this->log( "Accepted!" );
		requireComponent( "Textcube.Data.Post" );

		$post = new Post();
		$slogan = date( "Y-m-d" );

		if( $post->open( "slogan = '$slogan'" ) ) {
			$post->content .= $this->_getDecoratedContent( $mail );
			$post->modified = time();
		} else {
			$post->title = $mail['subject'];
			$post->userid = $this->userid;
			$post->content = $this->_getDecoratedContent( $mail );
			$post->contentFormatter = getDefaultFormatter();
			$post->contentEditor = getDefaultEditor();
			$post->created = time();
			$post->acceptComment = true;
			$post->acceptTrackback = true;
			$post->visibility = "public";
			$post->published = time();
			$post->modified = time();
			$post->slogan = $slogan;
			if( !$post->add() ) {
				$this->log( "Failed: there is a problem in adding post" );
				return false;
			}
		}

		$this->log( "Attachment: {$mail['attachments'][0]['filename']}" );
		requireModel( "blog.api" );
		$att = api_addAttachment( getBlogId(), $post->id, 
					array( 
							'name' => $mail['attachments'][0]['filename'], 
							'content' => $mail['attachments'][0]['decoded_content'], 
							'size' => $mail['attachments'][0]['length']
					) 
			);
		if( !$att ) {
			$this->log( "Failed: there is a problem in attaching file" );
			return false;
		}
		$post->content = str_replace( '$FILENAME', $att['name'], $post->content );
		$post->content = str_replace( '$WIDTH', $att['width'], $post->content );
		$post->content = str_replace( '$HEIGHT', $att['height'], $post->content );
		if( !$post->update() ) {
			$this->log( "Failed: there is a problem in adding post." );
			return false;
		}
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
	$pop3minsize = getBlogSetting( 'MmsPop3MinSize', 0 );
	$pop3minsize *= 1024;
	$pop3fallbackuserid = getBlogSetting( 'MmsPop3Fallbackuserid', 1 );

	$moblog = new Moblog( $pop3username, $pop3password, $pop3host, $pop3port, $pop3ssl, $pop3fallbackuserid, $pop3minsize );
	$moblog->check();
	return true;
}

function moblog_manage()
{
	global $blogURL;
	requireModel("common.setting");
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		setBlogSetting( 'MmsPop3Email', $_POST['pop3email'] );
		setBlogSetting( 'MmsPop3Host', $_POST['pop3host'] );
		setBlogSetting( 'MmsPop3Port', $_POST['pop3port'] );
		setBlogSetting( 'MmsPop3Ssl', !empty($_POST['pop3ssl'])?1:0 );
		setBlogSetting( 'MmsPop3Username', $_POST['pop3username'] );
		setBlogSetting( 'MmsPop3Password', $_POST['pop3password'] );
		setBlogSetting( 'MmsPop3Fallbackuserid', getUserId() );
		setBlogSetting( 'MmsPop3MinSize', 0 );
	}
	$pop3email = getBlogSetting( 'MmsPop3Email', '' );
	$pop3host = getBlogSetting( 'MmsPop3Host', 'localhost' );
	$pop3port = getBlogSetting( 'MmsPop3Port', 110 );
	$pop3ssl = getBlogSetting( 'MmsPop3Ssl', 0 ) ? " checked=1 " : "";
	$pop3username = getBlogSetting( 'MmsPop3Username', '' );
	$pop3password = getBlogSetting( 'MmsPop3Password', '' );
	$pop3minsize = getBlogSetting( 'MmsPop3MinSize', 0 );
	$pop3fallheadercharset = getBlogSetting( 'MmsPop3Fallbackcharset', 'euc-kr' );
?>
						<hr class="hidden" />
						
						<div id="part-setting-editor" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('MMS 메시지 확인용 메일 환경 설정');?></span></h2>
<?php if( !Acl::check( "group.administrators" ) ): ?>
								<div id="editor-section" class="section">
										<dl id="formatter-line" class="line">
											<dt><span class="label"><?php echo _t('MMS 수신 이메일');?></span></dt>
											<dd>
											<?php if( empty($pop3email) ): ?>
												<?php echo _t('비공개') ?>
											<?php else: ?>
												<?php echo $pop3email;?>
											<?php endif ?>
											</dd>
											<dd>
											<?php if( empty($pop3email) ): ?>
												<?php echo _('MMS 메시지를 보내어 연동할 이메일이 공개되지 않았습니다'); ?>
											<?php else: ?>
												<?php echo _('이동전화를 이용하여 위 메일로 MMS 메시지를 보내면 블로그에 게시됩니다'); ?>
											<?php endif ?>
											</dd>
										</dl>
								</div>
<?php else: ?>
							<form id="editor-form" class="data-inbox" method="post" action="<?php echo $blogURL;?>/owner/plugin/adminMenu?name=CL_Moblog/moblog_manage">
								<div id="editor-section" class="section">
									<fieldset class="container">
										<legend><?php echo _t('MMS 환경을 설정합니다');?></legend>
										
										<dl id="formatter-line" class="line">
											<dt><span class="label"><?php echo _t('MMS용 이메일');?></span></dt>
											<dd>
												<input type="text" style="width:14em" class="input-text" name="pop3email" value="<?php echo $pop3email;?>" /> 
												<?php echo _t('(필진에 공개 됩니다)'); ?>
											</dd>
										</dl>
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
												<input type="checkbox" name="pop3ssl" value="1" <?php echo $pop3ssl;?> /> SSL
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
<?php endif ?>
						</div>
<?php
}
?>
