<?php

include("db_functions.php");
include("functions.php");

$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$allmovies = getFilteredItems(null, "bookmark");

foreach ($allmovies AS $mov) {
    $moviesarray[] = $mov["item"];
  }

?>
<div style="white-space:pre-wrap">
<?php
  print_r($allmovies);
  massUpdateStreams($moviesarray);
?>
</div>
<?php

$body = $layout->output();
echo $foundation->output();




?>
