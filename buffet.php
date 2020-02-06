<?php

include("db_functions.php");

include("functions.php");

$t = new Template("templates/buffet.html");
$layout = new Template("templates/foundation.html");


$user = $_SESSION["user"];
$following = getFollowing($user);
$moviearr = getFilteredItems($following, "bookmark");

$followingstring = implode(", ", $following);

foreach ($moviearr AS $movie) {
  $sq = new Template("templates/moviesquare.html");
  $dump = print_r($movie, true);
  $movietitle = $movie["originaltitle"];
  $movieid = $movie["id"];
  $backdrop = basebackdropurl.$movie["backdrop"];
  $posterurl = baseposterurl.$movie["poster"];
  $description = $movie["overview"];
  $movieyear = $movie["year"];

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