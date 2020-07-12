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
	removeWikiTagsByMovieovie($id);
}


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



if (isset($_REQUEST["updateinfo"]) || $newmovie==true) {
	
	$page = getWikipediaPage($movietitle, $year);
	$link = getWikipediaLink($page);
	addLinks($link, $movieid, "Wikipedia");
	$sections = getWikipediaSections($page);

	$sectionid = 1;

	foreach ($sections AS $id => $section) {
		if ($section["line"] == "Plot" || $section["line"] == "Premise") {
			//echo "SECTIONID:".print_r($section);
			$sectionid = $section["index"];
		} else {
			//echo $section["line"];
		}
	}

	$section_text = getWikipediaTextFromSection($page, $sectionid);
//print_r($section_text);
	$splittedtext = splitWikitext($section_text);
//print_r($splittedtext);
	$words = getFilteredWords();

	$tagstoadd = array_udiff($splittedtext, $words, "strcasecmp"); //filters out all $words from the wikipedia wordsc



	addTag($movieid, $tagstoadd, "wikiplot");
}


?>
<div style="display:none;white-space:pre-wrap">
<?php
//print_r($sections);
print_r( $splittedtext );
?>
</div>
<?php

if (streamsAreOld($movieid) || isset($_REQUEST["updateinfo"])) {
	?>
	<div style="display:none;white-space:pre-wrap">
	<?php
	$moviearr = $movie;
	$moviearr["movieid"] = $movieid;
	//getCineasternaStreams($movietitle, $year);
	saveStreams($moviearr);
	?>
	</div>
	<?php
}



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
