<?php

include("db_functions.php");
include("functions.php");

$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$allmovies = getFilteredItems(null, "bookmark");

foreach ($allmovies AS $mov) {
    $moviesarray[] = $mov["item"];
  }


  print_r($allmovies);
echo massUpdateStreams($moviesarray);

$body = $layout->output();
echo $foundation->output();




?>
