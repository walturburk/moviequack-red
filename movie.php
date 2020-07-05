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
$posterurl = baseposterurl.$movie["poster"];
//$posterurl = checkImage($poster);
$backdrop = $basebigbackdropurl.$movie["backdrop"];

$runtime = $movie["runtime"];
$genre = $movie["genre"];
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

	$splittedtext = splitWikitext($section_text);

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
$taglist = printAllTags($movieid);
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
