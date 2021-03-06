<?php


include("db_functions.php");
include("functions.php");


$t = new Template("templates/quackpage.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$user = $_SESSION["user"];
$id = $_REQUEST["id"];

$quacki = getMessage($id);
$quack = $quacki[0];

$postid = $quack["id"];
$username = $quack["user1"];
$message = $quack["message"];
$userid = $quack["user1"];

$movieyear = $quack["movieyear"];
$movieid = $quack["movieid"];

$movieid = $quack["movieid"];

$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$movieid."' LIMIT 1");
$movie = $movieinfo[0];

saveStreams($movie);

//print_r(getExternalStreams($movie["title"], $movie["year"]));

$movieid = $movie["id"];
$movietitle = $movie["title"];
$year = $movie["year"];
$poster = basepostermurl.$movie["poster"];
$posterurl = checkImage($poster);
$backdrop = $basebackdropurl.$movie["backdrop"];

$runtime = $movie["runtime"];
$genre = $movie["genre"];
$plot = $movie["overview"];

$upvoteactive = getVotebtnActive($movieid, true);
$downvoteactive = getVotebtnActive($movieid, false);

$replies = printReplies(getReplies($postid));



if ($_SESSION["loggedin"] != true) {
	$isinwl = " gotologin ";
	$isinrl = " gotologin ";
}


if ($posts == "") {
	$posts = "";
}

$tagsarr = getTags($movieid);
$tags = printTags($tagsarr, $movieid);
$taglist = printAllTags($movieid);
$streams = printStreams(getStreams($movieid));

$rating = getMovieRating($movieid);
$urate = getUsersMovieRating($movieid, $user);
$movierating = printMovieRating($movieid, $rating, $urate);

$searchfieldholderclass = "moviepagesearchfieldholder";
$webpagetitle = $message;
$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>
