<?php
/* BlogAPI RSD automarker for Textcube 2.0
   ----------------------------------
   Version 2.0
   Needlworks development team.

   Creator          : coolengineer
   Maintainer       : coolengineer

   Created at       : 2006.8.6
   Last modified at : 2011.2.3
 
 This plugin adds RSD link into blog skin.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function AddRSD($target)
{
	$ctx = Model_Context::getInstance();
	$target .= '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.$ctx->getProperty('uri.host').$ctx->getProperty('uri.blog').'/api?rsd" />'.CRLF;
	return $target;
}
?>
