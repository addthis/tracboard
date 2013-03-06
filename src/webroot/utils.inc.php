<?php

require_once('lib/json.inc.php');

function urlParam($name, $dfltVal = null, $check_cookie = false, $put_into_cookie = false) {
    $json = new Services_JSON();

    $cookie_settings = (array_key_exists("tracboard_settings", $_COOKIE)) ? (array)$json->decode($_COOKIE['tracboard_settings']) : array();
    if ($cookie_settings === NULL) $cookie_settings = array();

    if (isset($_GET[$name])) {
        $val = $_GET[$name];
    }
    elseif ($check_cookie) {
      if (array_key_exists($name, $cookie_settings)) $val = $cookie_settings[$name];
      else $val = $dfltVal;
    }

    if (isset($val) && sizeof($val) > 0) {
      // Make a 30-day cookie if asked:
      if ($put_into_cookie) {
        $cookie_settings[$name] = $val;
        $_COOKIE['tracboard_settings'] = $json->encode($cookie_settings);

        // use header() instead of set-cookie, because header allows us to overwrite:
        // simplifies the logic of when to set the cookies, and doesn't send extra junk to
        // the browser:
        header("Set-Cookie: tracboard_settings=" . $_COOKIE['tracboard_settings'] . ";expires=" . date("D, d-M-Y H:i:s T", time() + (30 * 86400)));
      }

      return $val;
    }
    else return $dfltVal;
}
?>
