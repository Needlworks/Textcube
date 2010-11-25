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
  global $blog, $defaultURL;
  static $lastPing = null;
  $pingUrl = 'http://blogsearch.google.com/ping';

  $ping = $pingUrl
      . '?name=' . rawurlencode($blog['title'])
      . '&url=' . rawurlencode("$defaultURL/");

  if (!isset($lastPing) || $ping != $lastPing) {
    if (ini_get('allow_url_fopen')) {
      file_get_contents($ping);
    } else {
      if (!class_exists('HTTPRequest')) {
        requireComponent('Eolin.PHP.HTTPRequest');
      }
      $request = new HTTPRequest($ping);
      $request->send();
    }
    $lastPing = $ping;
  }
  return $target;
}
?>
