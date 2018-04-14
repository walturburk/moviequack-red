<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/mymovies.html");
$layout = new Template("templates/layout.html");

$user = $_SESSION["user"];


$movies = getStreamableMovies($user, "bookmark");

foreach ($movies AS $movie) {
  $streamsites[$movie["provider"]]["clear"] = $movie["clear"];
  $streamsites[$movie["provider"]]["count"] = 0+$streamsites[$movie["provider"]]["count"]+1;
  $streamsites[$movie["provider"]]["movie"][] = $movie;

}

usort($streamsites, function($a, $b) {
    return $b['count'] <=> $a['count'];
});

foreach ($streamsites AS $streamsite) {
  $print .= "<h2>".$streamsite["clear"]."</h2>";
  foreach ($streamsite["movie"] AS $movie) {
    $print .= "<a href='movie.php?id=".$movie["movieid"]."'><img src='".basethumburl.$movie["poster"]."'/></a>";
  }
}

//$print = print_r($streamsites, true);

$content = $t->output();

echo $layout->output();

?>
