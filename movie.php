<?php


include("db_functions.php");
include("functions.php");

$searchfieldholderclass = "moviepagesearchfieldholder";

$t = new Template("templates/moviepage.html");
$layout = new Template("templates/layout.html");

$user = $_SESSION["user"];
$id = $_REQUEST["id"];

if (isset($_REQUEST["updateinfo"])) {
	reAddMovie($id);
}

$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$id."' LIMIT 1");
$movie = $movieinfo[0];



if (!$movie["id"]) {
	$mid = substr($id, 1);
	$movie = addMovie($mid);
	$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$id."' LIMIT 1");
	$movie = $movieinfo[0];
}

saveStreams($movie["id"], $movie["title"], $movie["year"]);

//print_r(getExternalStreams($movie["title"], $movie["year"]));

$movieid = $movie["id"];
$movietitle = $movie["title"];
$year = $movie["year"];
$posterurl = baseposterurl.$movie["poster"];
//$posterurl = checkImage($poster);
$backdrop = $basebackdropurl.$movie["backdrop"];

$runtime = $movie["runtime"];
$genre = $movie["genre"];
$plot = $movie["overview"];

$upvoteactive = getVotebtnActive($movieid, true);
$downvoteactive = getVotebtnActive($movieid, false);
//$postsarray = getMessages($movieid);
//printMessages($movieid);


$posts = printMessages($movieid);


$watchlateractive = printTagActive(getSpecificTag("watch later", $user, $movieid));
$favouriteactive = printTagActive(getSpecificTag("favourite", $user, $movieid));

if ($posts == "") {
	$posts = "";
}

$webpagetitle = $movietitle;

$tagsarr = getTags($movieid);
$tags = printTags($tagsarr, $movieid);
$taglist = printAllTags($movieid);
$friendstaglist = printAllFriendsTags($movieid);
$streams = printStreams($movieid);

$rating = getMovieRating($movieid);
$urate = getUsersMovieRating($movieid, $user);
$movierating = printMovieRating($movieid, $rating, $urate);

if ($rating > 0) {
	$ratingpercent = $rating*10;
} else {
	$ratingpercent = "not yet rated";
}

$content = $t->output();
echo $layout->output();

?>
