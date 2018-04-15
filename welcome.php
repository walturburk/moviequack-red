<?php

include("db_functions.php");
include("functions.php");

$welcome = new Template("templates/welcome.html");

$foundation = new Template("templates/foundation.html");

$body = $welcome->output();

echo $foundation->output();
?>
