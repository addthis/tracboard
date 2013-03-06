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

require_once "header.inc.php";
require_once "uihelpers.inc.php";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>TracBoard</title>
    <script type="text/javascript">
      // Config state...
      var TRAC_SERVER = "<? echo TRAC_SERVER ?>";
    </script>
    <script type="text/javascript" src="js/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript" src="js/jqueryui/1.8.2/jquery-ui.min.js"></script>
    <script type="text/javascript" src="fancybox/jquery.fancybox-1.3.1.pack.js"></script>
        <script type="text/javascript" src="js/jquery/jquery.equalheights.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.qtip-1.0.0-rc3.wdm.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript" src="js/coloring.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <?php
    if (isset($scriptFiles)) {
      foreach($scriptFiles as $scriptFile) {
      echo '<script type="text/javascript" src="' . $scriptFile . '"></script>';
      }
    }
    ?>
    <link rel="stylesheet" href="fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/main.css" />
  </head>
  <body>
    <div id="header">
      <div class="navlinks">
        <a title="Plan the roadmap" href="plan.php"<?php if($nav=='plan') { ?> class="nav-active"<?php }?>>roadmap</a>
        &nbsp;|&nbsp;
        <a title="Manage a sprint" href="sprint.php"<?php if($nav=='sprint') { ?> class="nav-active"<?php }?>>sprint</a>
        &nbsp;|&nbsp;
        <a title="Track swimlanes" href="swim.php"<?php if($nav=='swim') { ?> class="nav-active"<?php }?>>swimlanes</a>
            <div class="auth">You are <a href="reauth.php?to=sprint.php"><?php echo $tracUsername ?></a></div>
      </div>
    </div>
