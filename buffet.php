<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/buffet.html");

$layout = new Template("templates/layout.html");





$content = $t->output();

echo $layout->output();

?>
