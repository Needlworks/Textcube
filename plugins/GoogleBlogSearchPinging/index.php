<?php
/*
 * Google Blog Search Ping Service plug-in.
 *
 * Copyright 2010 Google Inc. All rights reserved.
 *
 * This software is subject to, and may be distributed under, the
 * GNU General Public License, either Version 2 of the license,
 * or (at your option) any later version. The license should have
 * accompanied the software or you may obtain a copy of the license
 * from the Free Software Foundation at http://www.fsf.org .
 */

function GoogleBlogSearchPinging_ping($target) {
  /* TODO : do not send ping when private entry/post is added / modified */
  static $lastPing = null;
  $ctx = Model_Context::getInstance();
  $pingUrl = 'http://blogsearch.google.com/ping';

  $ping = $pingUrl
      . '?name=' . rawurlencode($ctx->getProperty('blog.title'))
      . '&url=' . rawurlencode($ctx->getProperty('uri.default')."/");

  if (!isset($lastPing) || $ping != $lastPing) {
    if (ini_get('allow_url_fopen')) {
      file_get_contents($ping);
    } else {
      $request = new HTTPRequest($ping);
      $request->send();
    }
    $lastPing = $ping;
  }
  return $target;
}
?>
