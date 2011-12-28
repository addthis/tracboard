<?php
/**
 * Copyright 2011 Clearspring Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Makes sure the page has these vars defined:
 *   - tracUsername
 *   - tracPassword
 *
 * If they aren't, this will issue an auth request to the user and attempt to get them.
 */

$currPage = $_SERVER['REQUEST_URI'];

function mustAuth() {
  echo '<p>Sorry, you need to authenticate with Trac to use this.</p>';
  echo '<p><a href="' . $currPage . '">try again</a></p>';
  exit;
}

// If we don't have any credentials, or if there's a cookie that tells us they are bad, re-prompt 
if (isset($_COOKIE['reauth']) || (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))) {
  setcookie("reauth", "", 1);
  header('WWW-Authenticate: Basic realm="Your Trac credentials?"');
  header('HTTP/1.0 401 Unauthorized');
  mustAuth();
} else {
  $tracUsername = $_SERVER['PHP_AUTH_USER'];
  $tracPassword = $_SERVER['PHP_AUTH_PW'];
}