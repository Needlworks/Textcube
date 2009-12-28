<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';

$blogid = getBlogId();
$links = getLinks( $blogid );

header( "Content-type: application/xml" );
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?><rdf:RDF
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      xmlns:foaf="http://xmlns.com/foaf/0.1/"
      xmlns:admin="http://webns.net/mvcb/">
<foaf:PersonalProfileDocument rdf:about="">
	<foaf:maker rdf:resource="#me"/>
	<foaf:primaryTopic rdf:resource="#me"/>
	<admin:generatorAgent rdf:resource="http://www.textcube.org/"/>
</foaf:PersonalProfileDocument>
<foaf:Person rdf:ID="me">
<?php
if( trim($blog['name']) != '' ) { echo "<foaf:name>{$blog['name']}</foaf:name>\n"; }
if( !empty($blog['OpenIDDelegate']) ) { echo "<foaf:openid>{$blog['OpenIDDelegate']}</foaf:openid>\n"; }
if( !empty($blog['logo']) ) { 
	echo "<foaf:depiction rdf:resource=\"http://{$service['domain']}{$service['path']}/attach/$blogid/{$blog['logo']}\" />\n";
	echo "<foaf:img rdf:resource=\"http://{$service['domain']}{$service['path']}/attach/$blogid/{$blog['logo']}\" />\n";
}
foreach( $links as $link ) {
	if( $link['visibility'] < 2 || !$link['xfn'] ) { continue; }
	if( $link['xfn'] == 'me' ) {
?><foaf:homepage rdf:resource="<?php echo htmlspecialchars($link['url']); ?>" />
<?php
	}
}
foreach( $links as $link ) {
	if( $link['visibility'] < 2 || !$link['xfn'] ) { continue; }
	if( $link['xfn'] == 'me' ) { continue; }
?>
<foaf:knows>
	<foaf:Person rdf:ID="ID<?php echo htmlspecialchars($link['id']);?>" >
	<foaf:name><?php echo htmlspecialchars($link['name']); ?></foaf:name>
	<foaf:nick><?php echo htmlspecialchars($link['name']); ?></foaf:nick>
	<foaf:homepage rdf:resource="<?php echo htmlspecialchars($link['url']); ?>" />
	</foaf:Person>
</foaf:knows>
<?php } ?>
</foaf:Person>
</rdf:RDF>
