<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/settings.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$user = $_SESSION["user"];

$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>
