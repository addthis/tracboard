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

require_once "config.inc.php";
require_once "auth.inc.php";
require_once "lib/interfacecache.inc.php";
require_once "lib/traclib.inc.php";

// Init Trac connectivity...
$tracImpl = new TracLib(TRAC_SERVER, "xmlrpc", $tracUsername, $tracPassword);
$trac = $tracImpl;
$methodBlacklist = array("parseTicket", "matrixTicketsByField");

// If you want to operate on a cache of the Trac data, turn this on (see lib/interfacecache)
//$trac = new InterfaceCache($tracImpl, "cache", $methodBlacklist);
