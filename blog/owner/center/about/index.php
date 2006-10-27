<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/headerA.php';
require ROOT . '/lib/piece/owner/contentMenuA2.php';
?>
						<div id="part-center-about" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('태터툴즈 개발진');?></span></h2>
						
							<h3>Brand yourself! : <?php echo TATTERTOOLS_NAME;?> <?php echo TATTERTOOLS_VERSION;?></h3>
							<div class="main-explain-box">
								<!--<p class="explain"><?php echo _t('이 판을 JH님께 헌정합니다.');?></p>-->
								<p class="explain"><?php echo _t('&copy; 2004 - 2006. 모든 저작권은 개발자 및 공헌자에게 있습니다.<br />태터툴즈는 태터앤컴퍼니와 태터앤프렌즈에서 개발합니다.<br />태터툴즈와 태터툴즈 로고는 태터앤컴퍼니의 상표입니다.');?></p>
							</div>
							
							<div id="developer-description" class="section">
								<h3><?php echo _t('개발자');?></h3>
								
								<div id="maintainer-container" class="container">
									<h4><?php echo _t('Maintainer');?></h4>
									
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
											<tr><td class="id">inureyes</td><td class="name"></td></tr>
											<tr><td class="id">papacha</td><td class="name"></td></tr>
										</tbody>
									</table>
								</div>
								
								<div id="developer-container" class="container">
									<h4><?php echo _t('Developer');?></h4>
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
												<td class="id">coolengineer</td>
												<td class="name"><a href="mailto:hojin.choi@gmail.com">Hojin Choi</a></td>
												<td class="role"><?php echo _t('XML-RPC API interface');?></td>
											</tr>
											<tr>
												<td class="id">crizin</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('reader / editor');?></td>
											</tr>
											<tr>
												<td class="id">daybreaker</td>
												<td class="name"><a href="http://daybreaker.info">Kim Joongi</a></td>
												<td class="role"><?php echo _t('xhtml specification / quality assurance');?></td>
											</tr>
											<tr>
												<td class="id">egoing</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('imazing / owner function');?></td>
											</tr>
											<tr>
												<td class="id"><a href="http://gendoh.tistory.com">gendoh</a></td>
												<td class="name">Sang-il, Lee</td>
												<td class="role"><?php echo _t('security / EAS / quality assurance');?></td>
											</tr>
											<tr>
												<td class="id">ghost_ghost</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('plugin setting / xml schema');?></td>
											</tr>
											<tr>
												<td class="id">graphittie</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('UI / sidebar / xhtml specification / documentation');?></td>
											</tr>
											<tr>
												<td class="id">inureyes</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('function addition / bug tracking / DB management');?></td>
											</tr>
											<tr>
												<td class="id">leezche</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('skin');?></td>
											</tr>
											<tr>
												<td class="id">nani</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('skin');?></td>
											</tr>
											<tr>
												<td class="id">papacha</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('library / component / quality assurance');?></td>
											</tr>
											<tr>
												<td class="id">peris</td>
												<td class="name"><a href="mailto:cshlacid@gmail.com">Sang-hoon Choi</a></td>
												<td class="role"><?php echo _t('plugin architecture');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="internationalization-container" class="container">
									<h4><?php echo _t('Internationalization');?></h4>
									
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
												<td class="id">Louice P.</td>
												<td class="name"><a href="mailto:seikanet@gmail.com">Sangjib Choi</a></td>
												<td class="role"><?php echo _t('Japanese');?></td>
											</tr>
											<tr>
												<td class="id">Ange</td>
												<td class="name"><a href="http://nekoto.poporu.net">Ha-neul Seo</a></td>
												<td class="role"><?php echo _t('Japanese');?></td>
											</tr>
											<tr>
												<td class="id">KIM</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('Chinese');?></td>
											</tr>
											<tr>
												<td class="id">건더기</td>
												<td class="name"><a href="http://blog.kangjang.net">John.K</a></td>
												<td class="role"><?php echo _t('English');?></td>
											</tr>
										</tbody>
									</table>
								</div>
								
								<div id="support-container" class="container">
									<h4><?php echo _t('Support manager');?></h4>
									
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
												<td class="id">J.Parker</td>
												<td class="name"><a href="http://www.create74.com">Yong-ju, Park</a></td>
												<td class="role"><?php echo _t('plugin manager');?></td>
											</tr>
											<tr>
												<td class="id">nani</td>
												<td class="name"><a href="http://sangsangbox.net">Jonggil Ko</a></td>
												<td class="role"><?php echo _t('skin manager');?></td>
											</tr>
											<tr>
												<td class="id">LonnieNa</td>
												<td class="name"></td>
												<td class="role"><?php echo _t('manual manager');?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							
							<div id="supporter-description" class="section">
								<h3><?php echo _t('공헌자');?></h3>
								
								<div id="contributor-container" class="container">
									<h4><?php echo _t('Code Contributor');?></h4>
									
									<p>랜덤여신, McFuture</p>
								</div>
								
								<div id="reporter-container" class="container">
									<h4><?php echo _t('Reporter');?></h4>
									
									<p>마모루, 건더기, 유마, 섭이, JCrew, cirrus, 작은인장, 김종찬, 김정훈, BLue, 소필, webthink, 일모리, lunamoth, 빌리디안, iamtiz, rooine, baragi74, soonJin, Juno, 딘제, iarchitect, Rukxer, gofeel, Ever_K, BlueOcean, thessando, advck1123, danew, 엉뚱이, 마잇, 하노아, Naive, mintstate, 바둥이, expansor, 싸이친구, rhapsody, 제주시티, funny4u, 안용열, lacovnk</p>
								</div>
							</div>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>
