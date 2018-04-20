<?php

include("db_functions.php");
include("functions.php");

$welcome = new Template("templates/welcome.html");

$foundation = new Template("templates/foundation.html");

$registerpage = new Template("templates/registerpage.html");

$register = '<h1 class="padding2">join us</h1>';
$register .= $registerpage->output();

$body = $welcome->output();

echo $foundation->output();
?>
