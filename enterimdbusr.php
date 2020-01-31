<?php

include("db_functions.php");
include("functions.php");





$t = new Template("templates/enterimdbusr.html");
$content = $t->output();
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");
$body = $layout->output();
echo $foundation->output();

?>
