<?php

include("db_functions.php");
include("functions.php");





$t = new Template("templates/enterimdbusr.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>
