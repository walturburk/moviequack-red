<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/buffet.html");
$layout = new Template("templates/layout.html");


$moviearr = getBuffet();
$user = $_SESSION["user"];

foreach ($moviearr AS $movie) {
  $sq = new Template("templates/moviesquare.html");

  $movietitle = $movie["originaltitle"];
  $movieid = $movie["id"];
  $backdrop = basebackdropurl.$movie["backdrop"];
  $posterurl = basethumburl.$movie["poster"];

  $rating = getMovieRating($movieid);
  $urate = getUsersMovieRating($movieid, $user);
  $movierating = printMovieRating($movieid, $rating, $urate);
  $bookmarkactive = printTagActive(getSpecificTag("bookmark", $user, $movieid));
  $favouriteactive = printTagActive(getSpecificTag("favourite", $user, $movieid));

  $squares .= $sq->output();
}

$content = $t->output();

echo $layout->output();

?>
