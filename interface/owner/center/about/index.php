<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

?>
						<div id="part-center-about" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('텍스트큐브 개발자');?></span></h2>
						
							<h3>Brand yourself! : <?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?></h3>
							
							<div class="main-explain-box">
								<p class="explain">
									<q xml:lang="la" title="<?php echo _t('모든 만물은 책이요 그림이요 또한 거울이니');?>">Omnis mundi creatura quasi liber et pictura nobis est, et speculum</q><br />
									<cite><?php echo _t('움베르트 에코 -장미의 이름- 중');?></cite>
								</p>
								<div id="copyright"><?php echo _t('&copy; 2004-2016. 모든 저작권은 개발자 및 공헌자에게 있습니다.<br />텍스트큐브는 니들웍스/TNF에서 개발합니다.<br />텍스트큐브와 텍스트큐브 로고는 니들웍스의 상표입니다.');?></div>
								<div id="XHTML-ValidIcon">
									<img src="http://www.w3.org/Icons/valid-xhtml11-blue" alt="Valid XHTML 1.1!" />
								</div>
								<div id="Needlworks-Icon">
									<img src="<?php echo $context->getProperty('uri.service');?>/resources/image/needlworks.png" alt="Made by Needlworks / TNF" />
								</div>
								<div class="clear"></div>
							</div>
							
							<div id="developer-description" class="section">
								<h3><span class="text"><?php echo _t('개발자');?></span></h3>
								
								<div id="maintainer-container" class="container">
									<h4><span class="text"><?php echo _t('Maintainer');?></span></h4>
									
									<table>
										<colgroup>
											<col class="name"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('이름');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://forest.nubimaru.com">Jeongkyu Shin</a></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://gendoh.com">Sang-il Lee</a></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="developer-container" class="container">
									<h4><span class="text"><?php echo _t('Developer');?></span></h4>
									<table>
										<colgroup>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('분야');?></th>
											</tr>
										</thead>
										<tbody>
										<tr>
												<td class="name vcard"><a class="fn url" href="http://coolengineer.com/">Hojin Choi</a></td>
												<td class="role"><?php echo _t('ACL / i18n / XML-RPC API interface / OpenID / Microformat');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://daybreaker.info">Kim Joongi</a></td>
												<td class="role"><?php echo _t('XHTML / IIS Support / Textcube server / Quality Assurance');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://www.graphittie.org/blog">graphittie™</a></td>
												<td class="role"><?php echo _t('UI / Sidebar / XHTML / Bug tracking');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://forest.nubimaru.com">Jeongkyu Shin</a></td>
												<td class="role"><?php echo _t('Core / DB management / Editor / Documentation');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://www.create74.com">Yong-ju, Park</a></td>
												<td class="role"><?php echo _t('Mobile / Teamblog / Coverpage / Plugin');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://tokigun.net">Seong-Hoon Kang</a></td>
												<td class="role"><?php echo _t('Editor / Formatter / Module');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://crizin.net">Jaeyong Lee</a></td>
												<td class="role"><?php echo _t('RSS Reader');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://bringbring.com">Jaepil Koh</a></td>
												<td class="role"><?php echo _t('Service administration panel');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://junwhan.com">Jun-whan Ahn</a></td>
												<td class="role"><?php echo _t('Optimization / Bug tracking / Editor / Formatter');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://yupmin.com">Young-jin Yoon</a></td>
												<td class="role"><?php echo _t('Maintenance / Backporting');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="internationalization-container" class="container">
									<h4><span class="text"><?php echo _t('Internationalization Maintainer');?></span></h4>
									
									<table>
										<colgroup>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('언어');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://spirited.tistory.com">Youyoung Song</a></td>
												<td class="role">한국어</td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn email" href="mailto:seikanet@gmail.com">Sangjib Choi</a></td>
												<td class="role">日本語</td>
											</tr>
											<tr>
												<td class="name vcard"><span class="fn nickname">DX.KIM</span></td>
												<td class="role">简体中文</td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://blog.chieh.tw">Chieh</a></td>
												<td class="role">正體中文</td>
											</tr>
											<tr>
												<td class="name vcard"><span class="fn">Steve Yum</span></td>
												<td class="role">English</td>
                                            </tr>
											<tr>
												<td class="name vcard"><span class="fn">Ducduy Dao</span></td>
												<td class="role">Vietnamese</td>
                                            </tr>
										</tbody>
									</table>
								</div>

								<div id="painter-container" class="container">
								<h4><span class="text"><?php echo _t('Painter');?></span></h4>
									
									<table>
										<colgroup>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('분야');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://design.funny4u.com">Guihwan Yu</a></td>
												<td class="role"><?php echo _t('Icon design');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://1upz.com">Won-eob Cho</a></td>
												<td class="role"><?php echo _t('Default skin');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://cyworld.com/madskillz">Hyunsang Hwang</a></td>
												<td class="role"><?php echo _t('Logo design');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://themaum.com">Sungho Kim</a></td>
												<td class="role"><?php echo _t('Plugin icon design');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="support-container" class="container">
									<h4><span class="text"><?php echo _t('Supports');?></span></h4>
									
									<table>
										<colgroup>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('역할');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://blog.2pink.net">Shik Yoon</a></td>
												<td class="role"><?php echo _t('Site design / Manual');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://ilmol.com">Sem Kim</a></td>
												<td class="role"><?php echo _t('Notice moderator (English)');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://lunamoth.biz">lunamoth</a></td>
												<td class="role"><?php echo _t('Online manual');?></td>
											</tr>
											<tr>
												<td class="name vcard"><a class="fn url" href="http://bringbring.com">Jaepil Koh</a></td>
												<td class="role"><?php echo _t('TatterCamp moderator');?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							
							<div id="supporter-description" class="section">
								<h3><span class="text"><?php echo _t('공헌자');?></span></h3>
								<div id="creators-container" class="container">
									<h4><?php echo _t('Hall of Fame');?></h4>
									<p>
									<span class="vcard"><a class="fn url" href="http://interlude.pe.kr">Jaehoon Jeong</a> (0.9x),</span>
									<span class="vcard"><a class="fn url" href="http://papacha.net">Sunju Jahng</a> (1.0x)</span>
									</p>
								</div>	
								<div id="contributor-container" class="container">
									<h4><?php echo _t('Code Contributor');?></h4>
									
									<p>
									<span class="vcard"><a class="fn nickname url" href="http://blog.creorix.com">Creorix</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://crizin.net">Crizin</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://egoing.net">egoing</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://skk97.tistory.com">Hanyoung</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://blog.laco.pe.kr">lacovnk</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://laziel.com">laziel</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://story.isloco.com">linus</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://mcfuture.net">McFuture</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://nya.pe.kr">NYA</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://tokigun.net">tokigun</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://rsef.net/">Peris</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://sangsangbox.net">나니</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://offree.net">도아</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://barosl.com/blog">랜덤여신</a>,	
									<span class="vcard"><a class="fn nickname url" href="http://yupmin.tistory.com/">엽기민원</a></span>,
									<span class="vcard"><a class="fn nickname url" href="#">우수한</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://chakani.net">차칸아이</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://www.yangkun.pe.kr">희망이아빠</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://reznoa.nayana.com/tt">레즈</a></span>,
									<span class="vcard"><a class="fn nickname url" href="http://hina.ushiisland.net">Hina</a></span>
									</p>
									
									<h4><?php echo _t('Internationalization Contributor');?></h4>
									<p>
									<span class="vcard"><a class="fn url" href="http://www.fsun.cn/blog">AIR</a></span>(简体中文),
									<span class="vcard"><a class="fn url" href="http://ddokbaro.com">Baro</a></span>(简体中文),
									<span class="vcard"><a class="fn url" href="http://hina.ushiisland.net/blog">Hina</a></span>(正體中文),
									M. Satoh(日本語),
									Nazu NT(日本語),
									Shungchul Kim(日本語),
									Sid (English),
									Taku S.(日本語),
									Terry Lee (English),
									건더기 (English)
									</p>
								</div>
								<div id="tester-container" class="container">
									<h4><?php echo _t('Tester');?></h4>
									<p>adeurian, chester, DARKLiCH, FeelSoGood, glradios, laotzu, McFuture, Tartarus, Silvester, xizhu, 독도2005, 심민규, 엽기민원, 유마, 헤이</p>
								</div>
								
								<div id="reporter-container" class="container">
									<h4><?php echo _t('Reporter');?></h4>
									
									<p>마모루, 건더기, 유마, 섭이, JCrew, cirrus, 작은인장, 김종찬, 김정훈, BLue, 소필, webthink, 일모리, lunamoth, 빌리디안, 티즈, rooine, baragi74, soonJin, Juno, 딘제, iarchitect, Rukxer, gofeel, Ever_K, BlueOcean, thessando, advck1123, danew, 엉뚱이, 마잇, 하노아, Naive, mintstate, 바둥이, expansor, 싸이친구, rhapsody, 제주시티, funny4u, 안용열, lacovnk, laziel, 랜덤여신, McFuture, subyis, leokim, diasozo, Ikaris Cyrus Faust, DARKLiCH, 주성애비, dikafryo, 이일환, Chiri, htna, Milfy, filmstyle, citta, 날개달기, vampelf, 비나무, FeelSoGood, 헤이, 밤하늘, raziel, U클래스, pom., bellblog, ataiger, onesound, mountaineer, jkm0114, 컴Ting, okto79, 보물섬, daydreamer, 너른호수, envia, Enistia, 정두원, 엘다, Capricorn, Kimatg, 47GPS, jellicle93</p>
								</div>
							</div>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
