<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<div id="part-center-about" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('텍스트큐브 개발진');?></span></h2>
						
							<h3>Brand yourself! : <?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?></h3>
							<div class="main-explain-box">
								<p class="explain">
									<em><?php echo _t('부 - 버그야 물렀거라 - 적.');?></em><br />
									<?php echo _t('&copy; 2004 - 2007. 모든 저작권은 개발자 및 공헌자에게 있습니다.<br />텍스트큐브는 니들웍스/TNF에서 개발합니다.<br />텍스트큐브와 텍스트큐브 로고는 니들웍스의 상표입니다.').CRLF;?>
								</p>
							</div>
							
							<div id="developer-description" class="section">
								<h3><span class="text"><?php echo _t('개발자');?></span></h3>
								
								<div id="maintainer-container" class="container">
									<h4><span class="text"><?php echo _t('Maintainer');?></span></h4>
									
									<table>
										<colgroup>
											<col class="id"></col>
											<col class="name"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="id"><?php echo _t('id');?></th>
												<th class="name"><?php echo _t('이름');?></th>
											</tr>
										</thead>
										<tbody>
										<tr><td class="id"><a href="http://gendoh.tistory.com">gendoh</a></td><td class="name">Sang-il, Lee</td></tr>
											<tr><td class="id"><a href="http://inureyes.tistory.com">inureyes</a></td><td class="name">Jeongkyu Shin</td></tr>
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
												<th class="id"><?php echo _t('id');?></th>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('분야');?></th>
											</tr>
										</thead>
										<tbody>
										<tr>
												<td class="id"><a href="http://coolengineer.com/">coolengineer</a></td>
												<td class="name">Hojin Choi</td>
												<td class="role"><?php echo _t('ACO / ACL / i18n / XML-RPC API interface / OpenID');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://daybreaker.info">daybreaker</a></td>
												<td class="name">Kim Joongi</td>
												<td class="role"><?php echo _t('xhtml specification / quality assurance');?></td>
											</tr>
											<tr>
												<td class="id"><span class="text">graphittie</span></td>
												<td class="name">&nbsp;</td>
												<td class="role"><?php echo _t('UI / sidebar / xhtml specification');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://forest.nubimaru.com">inureyes</a></td>
												<td class="name">Jeongkyu Shin</td>
												<td class="role"><?php echo _t('core / DB management / bug tracking / documentation');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://www.create74.com">J.Parker</a></td>
												<td class="name">Yong-ju, Park</td>
												<td class="role"><?php echo _t('teamblog / plugin');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="internationalization-container" class="container">
									<h4><span class="text"><?php echo _t('Internationalization');?></span></h4>
									
									<table>
										<colgroup>
											<col class="id"></col>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="id"><?php echo _t('id');?></th>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('언어');?></th>
											</tr>
										</thead>
										<tbody>
										<tr>
												<td class="id"><a href="mailto:seikanet@gmail.com">Louice P.</a></td>
												<td class="name">Sangjib Choi</td>
												<td class="role"><?php echo _t('Japanese');?></td>
											</tr>
											<tr>
												<td class="id"><span class="text">KIM</span></td>
												<td class="name">KIM</td>
												<td class="role"><?php echo _t('Chinese');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://blog.kangjang.net">건더기</a></td>
												<td class="name">John.K</td>
												<td class="role"><?php echo _t('English');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://memoriesreloaded.net">CK</a></td>
												<td class="name">CK</td>
												<td class="role"><?php echo _t('English');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="support-container" class="container">
									<h4><span class="text"><?php echo _t('Support manager');?></span></h4>
									
									<table>
										<colgroup>
											<col class="id"></col>
											<col class="name"></col>
											<col class="role"></col>
										</colgroup>
										<thead>
											<tr>
												<th class="id"><?php echo _t('id');?></th>
												<th class="name"><?php echo _t('이름');?></th>
												<th class="role"><?php echo _t('역할');?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="id"><a href="http://www.create74.com">J.Parker</a></td>
												<td class="name">Yong-ju, Park</td>
												<td class="role"><?php echo _t('plugin');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://sangsangbox.net">nani</a></td>
												<td class="name">Jonggil Ko</td>
												<td class="role"><?php echo _t('skin');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://blog.2pink.net">LonnieNa</a></td>
												<td class="name">Shik Yoon</td>
												<td class="role"><?php echo _t('manual');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://lunamoth.biz">lunamoth</a></td>
												<td class="name"></td>
												<td class="role"><?php echo _t('manual');?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							
							<div id="supporter-description" class="section">
								<h3><span class="text"><?php echo _t('공헌자');?></span></h3>
								
								<div id="contributor-container" class="container">
									<h4><?php echo _t('Code Contributor');?></h4>
									
									<p>
									<a href="http://barosl.com/blog">랜덤여신</a>,
									<a href="http://blog.laco.pe.kr">lacovnk</a>,
									<a href="http://www.mcfuture.net">McFuture</a>,
									<a href="http://laziel.com">laziel</a>,
									<a href="http://tokigun.net">tokigun</a>,
									<a href="http://story.isloco.com">linus</a>,
									<a href="http://nya.pe.kr">NYA</a>,
									<a href="http://www.yangkun.pe.kr">희망이아빠</a>,
									<a href="#">우수한</a>
									</p>
								</div>
								
								<div id="reporter-container" class="container">
									<h4><?php echo _t('Reporter');?></h4>
									
									<p>마모루, 건더기, 유마, 섭이, JCrew, cirrus, 작은인장, 김종찬, 김정훈, BLue, 소필, webthink, 일모리, lunamoth, 빌리디안, 티즈, rooine, baragi74, soonJin, Juno, 딘제, iarchitect, Rukxer, gofeel, Ever_K, BlueOcean, thessando, advck1123, danew, 엉뚱이, 마잇, 하노아, Naive, mintstate, 바둥이, expansor, 싸이친구, rhapsody, 제주시티, funny4u, 안용열, lacovnk, laziel, 랜덤여신, McFuture, subyis, leokim, diasozo, Ikaris C. Faust, DARKLiCH, 주성애비, dikafryo, 이일환, Chiri, htna, Milfy, filmstyle, citta, 날개달기, vampelf</p>
								</div>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
