<?php


include("db_functions.php");
include("functions.php");

$searchfieldholderclass = "moviepagesearchfieldholder";

$t = new Template("templates/moviepage.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$user = $_SESSION["user"];
$id = $_REQUEST["id"];

if (isset($_REQUEST["updateinfo"])) {
	removeMovie($id);
	removeWikiTagsByMovie($id);
}

/*$starttime = time();
	echo "<br>line:";
	echo __LINE__;
	echo "<br>starttime:";
	echo time()-$starttime;
	echo "<br>";*/

$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$id."' LIMIT 1");
$movie = $movieinfo[0];



if (!$movie["id"]) {
	$mid = substr($id, 1);
	$movie = addMovie($mid);
	$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$id."' LIMIT 1");
	$movie = $movieinfo[0];
	$newmovie=true;
}



$movieid = $movie["id"];
$movietitle = $movie["title"];
$originaltitle = $movie["originaltitle"];
$year = $movie["year"];
$posterurl = getPoster($movieid, 3);
$imdbid = $movie["imdbid"];
if (!$posterurl) {
	$thumb = $movie["poster"]."_thumb";
	$poster = $movie["poster"]."_poster";
	$backdrop = $movie["backdrop"]."_backdrop";
	addPoster($mqid, $thumb, 1);
	downloadPosterToDir(basethumburl.$movie["poster_path"], $thumb);
	addPoster($mqid, $poster, 3);
	downloadPosterToDir(baseposterurl.$movie["poster_path"], $poster);
	addPoster($mqid, $backdrop, 5);
	downloadPosterToDir(basebackdropurl.$movie["backdrop_path"], $backdrop);
	$posterurl = getPoster($movieid, 3);
}


//$posterurl = checkImage($poster);
$backdrop = getPoster($movieid, 5);
if (!$backdrop) {
	$backdrop = $basebigbackdropurl.$movie["backdrop"];
}

$genrearr = getGenreNamesForMovie($movieid);
foreach ($genrearr AS $name) {
	$arry[] = $name["name"];
}
$genre = implode(", ", $arry);

$runtime = $movie["runtime"];

$plot = $movie["overview"];

?>
<div style="display:none;white-space:pre-wrap">
<?php

print_r(getViaplayStreams($originaltitle, $imdbid));

if (isset($_REQUEST["updateinfo"]) || $newmovie==true) {
	
	$page = getWikipediaPage($movie);
	$link = getWikipediaLink($page);
	addLinks($link, $movieid, "Wikipedia");
	addPlotTextToTags($page, $movieid);

	
}



//print_r($sections);
//print_r( $splittedtext );



if (streamsAreOld($movieid) || isset($_REQUEST["updateinfo"])) {
	?>
	<div style="display:none;white-space:pre-wrap">
	<?php
	echo "STREAMS ARE OLD";

	$moviearr = $movie;
	$moviearr["movieid"] = $movieid;
	//getCineasternaStreams($movietitle, $year);
	saveStreams($moviearr);

	?>
	</div>
	<?php
}

?>
</div>
<?php


$upvoteactive = getVotebtnActive($movieid, true);
$downvoteactive = getVotebtnActive($movieid, false);
//$postsarray = getMessages($movieid);
//printMessages($movieid);


$posts = printMessages($movieid);


$bookmarkactive = printTagActive(getSpecificTag("bookmark", $user, $movieid));
$favouriteactive = printTagActive(getSpecificTag("favourite", $user, $movieid));

if ($posts == "") {
	$posts = "";
}

$webpagetitle = $movietitle;

$tagsarr = getTags($movieid);

if ($_SESSION["user"] == "walturburk") {
	$tags = '<button class="engage-filter-mode">Filter mode</button><br>';
} else {
	$tags = "";
}

$tags .= printTags($tagsarr, $movieid);
//$taglist = printAllTags($movieid);
$friendstaglist = printAllFriendsTags($movieid);
$streams = printStreams(getStreams($movieid));
$links = printLinks(getLinks($movieid));

$rating = getMovieRating($movieid);
$urate = getUsersMovieRating($movieid, $user);
$movierating = printMovieRating($movieid, $rating, $urate);

if ($rating > 0) {
	$ratingpercent = $rating*10;
} else {
	$ratingpercent = "not yet rated";
}


$content = $t->output();
$body = $layout->output();
echo $foundation->output();



?>
