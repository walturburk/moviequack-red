<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/buffet.html");
$layout = new Template("templates/foundation.html");


$moviearr = getBuffet();
$user = $_SESSION["user"];

foreach ($moviearr AS $movie) {
  $sq = new Template("templates/moviesquare.html");

  $movietitle = $movie["originaltitle"];
  $movieid = $movie["id"];
  $backdrop = basebackdropurl.$movie["backdrop"];
  $posterurl = basethumburl.$movie["poster"];
  $description = print_r($movie, true);

  $rating = getMovieRating($movieid);
  $urate = getUsersMovieRating($movieid, $user);
  $movierating = printMovieRating($movieid, $rating, $urate);
  $bookmarkactive = printTagActive(getSpecificTag("bookmark", $user, $movieid));
  $favouriteactive = printTagActive(getSpecificTag("favourite", $user, $movieid));

  $squares .= $sq->output();
}

$body = $t->output();

echo $layout->output();


?>