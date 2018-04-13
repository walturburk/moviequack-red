<?php

include("db_functions.php");
include("functions.php");

$raw = file_get_contents("https://www.rottentomatoes.com/search/?search=the%20walk");

$t = new Template("https://www.rottentomatoes.com/search/?search=the%20walk");

echo $t->output();

 ?>
