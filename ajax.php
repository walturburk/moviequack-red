<?php

include("db_functions.php");
include("functions.php");

$mode = $_REQUEST["mode"];
$q = $_REQUEST["q"];

switch ($mode) {
	case "GETUSERNAME":
		$user = getUserByName($q);
		echo $user[0]["username"];
		break;
	case "POSTMESSAGE":
		$id = newId("p");
		$movie = $_REQUEST["movie"];
		$emoji = $_REQUEST["emoji"];
		$isreplyto = $_REQUEST["replyto"];
		$message = $q;
		$user = $_SESSION["user"];
		postMessage($id, $emoji, $movie, $message, $user);
		if ($isreplyto) {
			postAsAnswer($id, $isreplyto);
		}
		//echo $user;
		//echo $_SESSION["user"];
		break;
	case "VOTE":
		$post = $_REQUEST["post"];
		$upvote = $_REQUEST["upvote"];
		$downvote = $_REQUEST["downvote"];
		echo vote($post, $upvote, $downvote);
		break;
	case "GETVOTES":
		$post = $_REQUEST["post"];
		echo getVotes($post);
		break;
	case "GETGENRES":
		$movie = $_REQUEST["movie"];
		$genrearr = getGenreNamesForMovie($movie);
		foreach ($genrearr AS $name) {
			$arry[] = $name["name"];
		}
		echo implode(" ", $arry);
		break;
	case "ADDMOVIESTODB":
		$q = $_REQUEST["searchterm"];
		$omdbq = str_replace(" ", "+", $q);
		$url = "//svr2.omdbapi.com/?s=".$omdbq."";//"//www.omdbapi.com/?s=".$omdbq."";
		$json = file_get_contents($url);
		$searchresults = json_decode($json, true);
		foreach($searchresults["Search"] AS $movie) {
			$html .= addMovie($movie["imdbID"]);
		}
		break;
	case "PRINTMESSAGES":
		echo printMessages($_REQUEST["movie"]);
		break;
	case "FOLLOW":
		follow($_REQUEST["follows"]);
		break;
	case "EXTERNALSEARCH":
		$internalmovies = getMovies($q);
		$xmovies = getExternalMovies($q);
		if (empty($xmovies) && empty($internalmovies)) {
			$xsearchhits = "<tr><td>No matching movies found</td></tr>";
		} else {
			$xsearchhits = "";//<h4 class='red marginbottom'>Deep search returned this</h4><table class='searchresults'>";
			$searchhit = new Template("templates/searchhit.html");

			foreach($xmovies AS $movie) {
				$movieid = "m".$movie["id"];

				if ($movie["original_title"] != $movie["title"]) {
					$originaltitle = $movie["original_title"];
					$movietitle = $movie["title"];
				} else {
					$originaltitle = $movie["original_title"];
					$movietitle = "";
				}
				$posterurl = "https://image.tmdb.org/t/p/w92".$movie["poster_path"];
				$explodeddate = explode("-", $movie["release_date"]);
				$movieyear = $explodeddate[0];
				$xsearchhits .= $searchhit->output();
			}
		}
		echo $xsearchhits;//."</table>";
		//print_r(getallheaders());
		break;
	case "GETEMOJIBOARD":
		$emojiboard = getEmojiBoard();
		echo $emojiboard;
		break;
	case "PRINTREPLIES":
		$formsg = $_REQUEST["formsg"];
		$replies = getReplies($formsg);
		$return = printReplies($replies);
		echo $return;
		break;


	case "REMOVEPOST":
		removePost($q);
		break;

	case "ADDTAG":
		$movie = $_REQUEST["movie"];
		addTag($movie, $q);
		$tags = getTags($movie);
		echo printTags($tags, $movie);
		break;
	case "ADDUSERTAG":
		$movie = $_REQUEST["movie"];
		if ($q[0] != "@") {
			$atq = "@".$q;
		} else {
			$atq = $q;
			$str = substr($q, 1);
		}
		if (checkIfUserExist($q)) {
			addTag($movie, $atq);
		}
		$tags = getTags($movie);
		echo printTags($tags, $movie);
		break;
	case "REMOVETAG":
		$movie = $_REQUEST["movie"];
		removeTag($movie, $q);
		$tags = getTags($movie);
		echo printTags($tags, $movie);
		break;
	case "RATEMOVIE":
		$movie = $_REQUEST["movie"];
		$user = $_SESSION["user"];
		rateMovie($movie, $q);
		$rating = getMovieRating($movie);
		$urate = getUsersMovieRating($movie, $user);
		//echo printMovieRating($movie, $rating, $urate);
		break;
}

?>
