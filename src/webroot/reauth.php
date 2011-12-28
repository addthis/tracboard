<?php
if (isset($_GET["to"])) {
	$to = $_GET["to"];
} else {
	$to = "/";
}
setcookie("reauth", "true");
header("Location: " . $to);
