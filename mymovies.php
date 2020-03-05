<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/mymovies.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$user = $_SESSION["user"];


$allmovies = getFilteredItems($user, "bookmark");

foreach ($allmovies AS $mov) {
  $moviesarray[] = $mov["item"];
}


massUpdateStreams($moviesarray);

$movies = getStreamableMovies($user, "bookmark");


foreach ($movies AS $movie) {
  $streamsites[$movie["type"]][$movie["provider"]]["clear"] = $movie["clear"];
  $streamsites[$movie["type"]][$movie["provider"]]["count"] = 0+$streamsites[$movie["type"]][$movie["provider"]]["count"]+1;
  $streamsites[$movie["type"]][$movie["provider"]]["movie"][] = $movie;

}


$ss["Subscription"] = $streamsites["flatrate"];
$ss["Buy"] = $streamsites["buy"];
$ss["Rent"] = $streamsites["rent"];

usort($ss["Subscription"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

usort($ss["Rent"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

usort($ss["Buy"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

foreach ($ss AS $key => $streamsite) {
  $print .= "<div style='position:relative; text-align:center; '><p class='stickyheader'>".$key."</p><div class='content' >";
  foreach ($streamsite AS $s) {
    $print .= "<h3 style='padding-bottom:1rem'>".$s["clear"]." (".$s["count"].")</h3>";
    foreach ($s["movie"] AS $movie) {
      $print .= "<a class='poster postertiny' href='/movie/".$movie["movieid"]."'><img src='".basethumburl.$movie["poster"]."'/></a>";
    }
    $print .= "<div style='padding:2rem 0;'></div>";
  }
  $print .= "</div>";
  $print .= "</div>";
}


//$print = print_r($streamsites, true);

$content = $t->output();

$body = $layout->output();
echo $foundation->output();

?>
