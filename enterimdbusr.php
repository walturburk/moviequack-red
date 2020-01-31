<?php

include("db_functions.php");
include("functions.php");





$t = new Template("templates/enterimdbusr.html");
$foundation = new Template("templates/foundation.html");
$body = $t->output();
echo $foundation->output();

?>
