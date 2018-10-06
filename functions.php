<?php


$searchfieldholderclass = "";

autoLogin();
//getDontTag();
if ($_SESSION["loggedin"]) {
} else if (strpos($_SERVER['REQUEST_URI'], "welcome") > 0) {
} else if (strpos($_SERVER['REQUEST_URI'], "join") > 0) {
} else if (strpos($_SERVER['REQUEST_URI'], "login") > 0) {
} else {
	header("Location: /welcome");
}

$apikey = "0a9b195ddb48019271ac2de755730dd4";
$userid = $_SESSION["user"];
$basethumburl = "https://image.tmdb.org/t/p/w92/";
$baseposterurl = "https://image.tmdb.org/t/p/w342/";
$basebackdropurl = "https://image.tmdb.org/t/p/w780/";
$basebigbackdropurl = "https://image.tmdb.org/t/p/w1280/";

define("basethumburl", "https://image.tmdb.org/t/p/w92/");
define("basepostermurl", "https://image.tmdb.org/t/p/w185/");
define("baseposterurl", "https://image.tmdb.org/t/p/w342/");
define("basebackdropurl", "https://image.tmdb.org/t/p/w780/");

$basicfilemtime = filemtime(__DIR__."/css/basic.css");
$moviequackfilemtime = filemtime(__DIR__."/css/moviequack.css");


function addGenreNames() {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.themoviedb.org/3/genre/movie/list?api_key=0a9b195ddb48019271ac2de755730dd4",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_POSTFIELDS => "{}",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  $print = "cURL Error #:" . $err;
	} else {
	  $genrenames = json_decode($response);

		foreach ($genrenames->genres AS $g) {
			$query = "INSERT INTO `genrenames` (`id`, `name`)
			VALUES ('".$g->id."', '".$g->name."');
			";
			db_query($query);
			echo $query;
		}
	}
}


function getDontTag() {
$array[] = "and";
$array[] = "very";
$array[] = "but";
$array[] = "plus";
$array[] = "is";
$array[] = "and";
$array[] = "i";
$array[] = "in";
$array[] = "it";
$array[] = "at";
$array[] = "really";
$array[] = "them";
$array[] = "to";
$array[] = "make";
$array[] = "a";
$array[] = "about";
$array[] = "this";
$array[] = "we";
$array[] = "does";
$array[] = "doesnt";
$array[] = "are";
$array[] = "arent";

$string = implode($array, ", ");
file_put_contents('donttag.bin', $string);
return $array;
}

include("template.php");

if (!isset($_SESSION["user"])) {
	makeDummyUser();
}

function newId($prefix) {

	do {
		$newid = uniqid();
		$id = $prefix.$newid;
		if ($prefix == "u") {
			$idfromdb = db_select("SELECT id FROM `user` WHERE username = '$id'");
		} else if ($prefix == "p") {
			$idfromdb = db_select("SELECT id FROM `post` WHERE username = '$id'");
		}
	} while ($id == $idfromdb[0]["id"]);

	return $id;
}

function getSession($user, $time) {

	$session = db_select("SELECT id FROM `session` WHERE user = '$user' AND time < $time ORDER BY time DESC LIMIT 1");

	$hash = $user.$session[0]["id"];

	return $hash;
}

function saveAutoLogin() {
	$user = $_SESSION["user"];
	$time = time();
	$hash = getSession($user, $time);

	$loginhash = createHash($hash);

	setcookie("user", $user, $time + (60*60*24*365), "/");
	setcookie("logintime", $time, $time + (60*60*24*365), "/");
	setcookie("loginhash", $loginhash, $time + (60*60*24*365), "/");

	return $loginhash;
}

function autoLogin() {

	if (isset($_COOKIE["user"]) && isset($_COOKIE["logintime"]) && isset($_COOKIE["loginhash"])) {

		$user = $_COOKIE["user"];
		$logintime = $_COOKIE["logintime"];
		$loginhash = $_COOKIE["loginhash"];

		$session = getSession($user, $logintime);

		if (hashOk($session, $loginhash)) {
			$_SESSION["loggedin"] = true;
			$_SESSION["user"] = $user;
		}

	}

}

function postMessage($id, $emoji, $movie, $msg, $user) {
	$time = time();
	$user = $_SESSION["user"];

	$proptags = explode(" ", $msg);
	$file = file_get_contents('donttag.bin');
	$notallowed = explode($file, ", ");
	foreach ($proptags AS $tag) {
		$tag = strtolower($tag);
		if (is_array($notallowed)) {
			if (!in_array($tag, $notallowed)) {
				addTag("", $tag);
			}
		}
	}

	$message = db_escape($msg);
	$query = "INSERT INTO `post` (`id`, `item`, `emoji`, `message`, `userid`, `timestamp`)
	VALUES ('$id', '$movie', '$emoji', '$message', '$user',  '$time');
	";
	db_query($query);
}

function postAsAnswer($thismsg, $opmsg) {
	$query = "INSERT INTO `reply` (`reply`, `original`)
	VALUES ('$thismsg', '$opmsg');
	";
	db_query($query);
}

function vote($post, $upvote = 0, $downvote = 0) {
	$timestamp = time();
	$user = $_SESSION["user"];
	$votes = db_select("SELECT * FROM `vote` WHERE post = '$post' AND user = '$user' AND `upvote` = $upvote AND `downvote` = $downvote");
	$votediff = 0;
	if ($votes) {
		$query = "DELETE FROM `vote` WHERE user = '$user' AND post = '$post';";
		$votediff = 0;
	} else {
		$query = "INSERT INTO `vote` (`post`, `user`, `timestamp`, `upvote`, `downvote`)
		VALUES ('$post', '$user', $timestamp, $upvote, $downvote)
		ON DUPLICATE KEY UPDATE
		  timestamp=$timestamp, upvote=$upvote, downvote=$downvote;
		";
		$votediff = $upvote-$downvote;
	}
	db_query($query);
	return $votediff;
}

function getVotes($post) {
	$votes = db_select("SELECT dv.downvote AS downvotes, uv.upvote AS upvotes, (uv.upvote - dv.downvote) AS diff
	FROM
	(SELECT count(downvote) AS downvote FROM `vote` WHERE downvote = true AND upvote = false AND post = '$post') AS dv,
	(SELECT count(upvote) AS upvote FROM `vote` WHERE downvote = false AND upvote = true AND post = '$post') AS uv");
	return $votes[0];
}

function getVotebtnActive($post, $upvote) {
	$user = $_SESSION["user"];
	if ($upvote == true) {
		$col = "upvote";
	} else {
		$col = "downvote";
	}
	$votes = db_select("SELECT * FROM `vote` WHERE `user` = '$user' AND `post` = '$post' AND `$col` = 1");
	if ($votes) {
		$active = "activebtn";
	} else {
		$active = "";
	}
	//$active = $votes[0]["timestamp"];
	return $active;
}

function getMessages($movie) {
	$posts = db_select("SELECT
	reply.reply,
	user.username AS user1, user.username AS user1id,
	post.timestamp AS timestamp, post.emoji,
	post.id, post.message, post.userid, post.item, movie.year AS movieyear, movie.poster AS poster,
(SUM((10+od.upvote-od.downvote)*1000/(UNIX_TIMESTAMP()-post.timestamp)))
	AS votes
	FROM user
	LEFT JOIN post
	ON user.username = post.userid
	LEFT JOIN vote od
	ON post.id = od.post
	LEFT JOIN reply
	ON reply.reply = post.id
	LEFT JOIN movie
	ON post.item = movie.id
	WHERE post.item = '$movie' AND reply.reply IS NULL
	GROUP BY post.id
	ORDER BY `votes` DESC
");
	return $posts;
}

function getReplies($original) {
	$posts = db_select("SELECT user.username, user.username AS userid, post.id AS postid, post.message, post.timestamp FROM `post`
LEFT JOIN reply
ON reply.reply = post.id
LEFT JOIN user
ON user.username = post.userid
WHERE reply.original = '$original'");
	return $posts;
}

function getLatestMessage() {
	$posts = db_select("SELECT
	movie.imdbid, movie.poster, reply.reply,
	user.username AS username, user.username AS userid,
	post.timestamp AS timestamp, post.emoji,
	post.id, post.message, post.userid, post.item,
(SUM((10+od.upvote-od.downvote)*1000/(UNIX_TIMESTAMP()-post.timestamp)))
	AS votes
	FROM user
	LEFT JOIN post
	ON user.username = post.userid
	LEFT JOIN vote od
	ON post.id = od.post
	LEFT JOIN reply
	ON reply.reply = post.id
	LEFT JOIN movie
	ON movie.imdbid = post.item
	WHERE reply.reply IS NULL
	GROUP BY post.id
	ORDER BY `post`.timestamp DESC
LIMIT 1");
	return $posts;
}

function getTrendingMessage() {
	$posts = db_select("SELECT user.username AS username, user.username AS userid, post.timestamp AS timestamp, post.emoji, post.id, post.message, post.userid, post.item, (SUM((10+od.upvote-od.downvote)*1000/(UNIX_TIMESTAMP()-post.timestamp))) AS votes
FROM user
LEFT JOIN post
ON user.username = post.userid
LEFT JOIN vote od
ON post.id = od.post
GROUP BY post.id
ORDER BY `votes`  DESC
LIMIT 1");
	return $posts;
}

function getTopMessage() {
	$posts = db_select("SELECT user.username AS username, user.username AS userid, post.timestamp AS timestamp, post.emoji, post.id, post.message, post.userid, post.item, (SUM((od.upvote-od.downvote))) AS votes
FROM user
LEFT JOIN post
ON user.username = post.userid
LEFT JOIN vote od
ON post.id = od.post
GROUP BY post.id
ORDER BY `votes`  DESC
LIMIT 1");
	return $posts;
}

function getControversialMessage() {
	$posts = db_select("SELECT user.username AS username, user.username AS userid, post.timestamp AS timestamp, post.emoji, post.id, post.message, post.userid, post.item, (SUM((od.upvote))) AS upvotes, (SUM((od.downvote))) AS downvotes, (SUM(od.upvote)*SUM(od.downvote)) as multi
FROM user
LEFT JOIN post
ON user.username = post.userid
LEFT JOIN vote od
ON post.id = od.post
GROUP BY post.id
ORDER BY `multi`  DESC, downvotes DESC
LIMIT 1");
	return $posts;
}

function getMessage($id) {
	$sql = "SELECT movie.title AS movietitle, movie.id AS movieid, movie.year AS movieyear, movie.poster AS poster, reply.original AS origmsg,
	user.username AS user1, user.username AS user1id, post.message, post.emoji, post.timestamp AS timestamp, post.id AS id
	FROM `post`
	LEFT JOIN movie
	ON post.item = movie.id
	LEFT JOIN user
	ON post.userid = user.username
	LEFT JOIN reply
	ON post.id = reply.reply
	WHERE (post.id = '$id')
	ORDER BY `post`.`timestamp` DESC
	LIMIT 30";
	$feed = db_select($sql);

	return $feed;
}

function printMessage($postsarray) {
	if (!is_array($postsarray)) {
		$postitem = $postsarray;
		$postsarray = array();
		$postsarray[] = $postitem;
	}
	$user = $_SESSION["user"];
	$q = new Template("templates/quack.html");
	$posts = "";

	foreach ($postsarray AS $post) {

		$followbtn = "";
		if ($post["user1id"] == $_SESSION["user"]) {
		  $isownprofile = true;
		} else {
		  $userid = $post["user1id"];
		  $alreadyfollows = checkiffollows($_SESSION["user"], $userid);
		  if ($alreadyfollows) {
		    $activebtn = "activebtn";
		  } else {
		    $activebtn = "";
		  }
		  $followbtn = '<span class="smallbtn followbtn '.$activebtn.'" data-followedid="'.$post["user1id"].'"><i style="font-size:17px; vertical-align:top" class="material-icons">person_add</i></span>';
		}

		if (isVisitor($post["user1"])) {
			$post["user1"] = "visitor";
		}


		$qfontsize = round(34-(strlen($post["message"])/10));
		if ($qfontsize < 20 ) {
			$qfontsize = 20;
		}

		$upact = "";
		$downact = "";
		$replies = printReplies(getReplies($post["id"]));
		$upact = getVotebtnActive($post["id"], true);
		$downact = getVotebtnActive($post["id"], false);
		$q->set("followbtn", $followbtn);
		$q->set("tinyposter", basethumburl.$post["poster"]);
		$q->set("replies", $replies);
		$q->set("username", $post["user1"]);
		$q->set("userid", $post["user1id"]);
		$q->set("upvoteactive", $upact);
		$q->set("downvoteactive", $downact);
		$q->set("message", $post["message"]);
		$q->set("movieid", $post["movieid"]);
		$q->set("movietitle", $post["movietitle"]);
		$q->set("movieyear", $post["movieyear"]);
		$q->set("postid", $post["id"]);
		$votes = getVotes($post["id"]);
		$q->set("votes", $votes["diff"]);
		$q->set("upvotes", $votes["upvotes"]);
		$q->set("downvotes", $votes["downvotes"]);
		$q->set("quacksize", $qfontsize);
		$q->set("rawtimestamp", $post["timestamp"]);
		$q->set("timestamp", formatTimestamp($post["timestamp"]));
		$q->set("shorttime", formatTimestampSmart($post["timestamp"]));
		if ($user == $post["user1id"]) {
			$removepostbtn = '<div data-post="'.$post["id"].'" class="simplebtn removepost">Remove post</div>';
		} else {
			$removepostbtn = "";
		}
		$q->set("removepostbtn", $removepostbtn);
		$posts .= $q->output();
	}

	return $posts;
}

function printReplies($postsarray) {

	$posts = "";
	foreach ($postsarray AS $post) {
		$posts .= "<div class='replymsg'><a class='small' href='/user/".$post["userid"]."'>".$post["username"]."</a>";
		$posts .= "<div class='padding0'>".$post["message"]."</div></div>";
	}

	return $posts;
}

function printSpecMessage($sort) {
	if ($sort == 1) {
		$postarray = getLatestMessage();
	} elseif ($sort == 2) {
		$postarray = getTrendingMessage();
	} else {
		$postarray = getTopMessage();
	}
	$message = "<a href='/movie/".$postarray[0]["movieid"]."'>";
	$message .= "<img src='".$postarray[0]["poster"]."'>";
	$message .= "</a>";

	$message .= printMessage($postarray);
	return $message;
}

function printMessages($movie) {

	$postsarray = getMessages($movie);
	$messages = printMessage($postsarray);

	return $messages;
}

function skipMovie($movie) {

	$movies = unserialize($_COOKIE['skipmovies']);
	$movies[] = $movie;
	setcookie('skipmovies', serialize($movies), time()+60*60*24);

	return $movies;
}


function rateMovie($movie, $rating) {

	$user = $_SESSION["user"];
	$time = time();

	if ($rating == "null") {
		$query = "DELETE FROM ratemovie WHERE user = '$user' AND movie = '$movie'";
	} else {
	$query = "INSERT INTO `ratemovie` (`movie`, `user`, `rating`, `timestamp`)
	VALUES ('$movie', '$user', '$rating', '$time')
ON DUPLICATE KEY UPDATE
		  timestamp=".$time.", rating=".$rating."
	";
	}

	return db_query($query);

}

function getMovieRating($movie) {

	$sql = "SELECT SUM(rating) / COUNT(rating) AS overall
FROM `ratemovie`
WHERE movie = '".$movie."'";

	$movie = db_select($sql);
	return $movie[0]["overall"];
}

function getUsersMovieRating($movie, $user) {

	$sql = "SELECT rating
FROM `ratemovie`
WHERE movie = '".$movie."' AND user = '".$user."'
LIMIT 1";

	$movie = db_select($sql);
	return $movie[0]["rating"];
}

function printMovieRating($movie, $rating, $urate) {


	$starrating = round($rating)/2-0.5;

$starclass[] = "star1";
$starclass[] = "star2";
$starclass[] = "star3";
$starclass[] = "star4";
$starclass[] = "star5";



	for ($i = 0; $i < 5; $i++) {
		if ($i > $starrating) {
			$starico = "star_border";
		} else if ($i == $starrating) {
			$starico = "star_half";
		} else {
			$starico = "star";
		}

		if (isset($urate)) {
		if ($i > $urate/2-1) {
			$myrating = "myratingdark";
		} else if ($i == $urate/2-1) {
			$myrating = ($urate/2-1)." myrating actualvote ".$i;
		} else {
			$myrating = "myrating";
		}
	} else {
		$myrating = "notrated";
	}



		$starnr = "";
		foreach ($starclass AS $class) {
			$starnr .= " ".$class;
		}
		unset($starclass[$i]);


		$temp = '<div data-movie="'.$movie.'" data-starnr="'.($i+1).'" class="'.$myrating.' votestar '.$starnr.'"><i class="material-icons">'.$starico.'</i></div>';
		$print .= $temp;
	}

	//$print .= "<div class='red absolutecenter'>".(round($rating*10)/10)."</div>";
	return $print;
}

function getMovies($searchterm) {
	$sqlstart = "SELECT originaltitle AS originaltitle, title AS title, id, year, poster, tmdbid FROM  `movie` WHERE  ";
	$searchterm = mysqli_real_escape_string(db_connect(), $searchterm);
	$like = " `searchstring` LIKE '%".$searchterm."%' ";

	$length = strlen($searchterm);

	for ($i=0; $i<$length; $i++) {
		if ($i == 0 || $i == $length || $i == $length-1) {} else {

			$tsearchterm = substr_replace($searchterm, "_", $i, 1);
			$like .= " OR `searchstring` LIKE '%".$tsearchterm."%' ";

		}
	}
	$sql = $sqlstart.$like;
	$movies = db_select($sql);

	return $movies;
}

function getUsers($searchterm) {
	$searchterm = mysqli_real_escape_string(db_connect(), $searchterm);
	$users = db_select("SELECT username FROM  `user` WHERE  `username` LIKE  '%".$searchterm."%'");
	return $users;
}

function getUserByName($username) {
	$searchterm = mysqli_real_escape_string(db_connect(), $searchterm);
	$users = db_select("SELECT * FROM  `user` WHERE  `username` = '".$username."'");
	return $users;
}

function getExternalMovies($q) {
	global $apikey;

	$apiq = urlencode($q);

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.themoviedb.org/3/search/movie?include_adult=false&page=1&query=".$apiq."&api_key=".$apikey,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_POSTFIELDS => "{}",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  $print = "cURL Error #:" . $err;
	} else {
	  $print = $response;
	}

$movies = getMovies($q);

	$html = json_decode($print, true);

		if (is_array($html["results"])) {
			foreach ($html["results"] AS $key => $val) {
				//$val = array_change_key_case($val, CASE_LOWER);
				$isinarray = false;
				foreach ($movies AS $mia) {
					if ($mia["tmdbid"] == $val["id"]) {
							$isinarray = true;
					}
				}
				if (!$isinarray) {
					$xmovies[] = $val;
					//addMovie($movie["imdbID"]);
				}
			}
		}
		//$return = sortByGenre($xmovies);
		return $xmovies;
}

function getExternalMoviesOldBroken($q) {
	global $apikey;
	$movies = getMovies($q);
	$xmovies = [];
		$apiq = urlencode($q);
	    $url = "https://api.themoviedb.org/3/search/movie?api_key=0a9b195ddb48019271ac2de755730dd4&query=casablanca&page=1&include_adult=false";
		$json = file_get_contents($url);
		$headers = var_dump($http_response_header);
		$html = json_decode($json, true);
		if (is_array($html["results"])) {
			foreach ($html["results"] AS $key => $val) {
				$val = array_change_key_case($val, CASE_LOWER);
				$isinarray = false;
				foreach ($movies AS $mia) {
					if ($mia["id"] == $val["id"]) {
						$isinarray = true;
					}
				}
				if (!$isinarray) {
					$xmovies[] = $val;
					//addMovie($movie["imdbID"]);
				}
			}
		}
		$return = sortByGenre($xmovies);
		return $return;
}

function sortByGenre($movies) {
	$shorts = [];
	$documentaries = [];
	$all = [];
	foreach ($movies AS $movie) {
		if (strpos($movie["genre"], "short")) {
			$shorts[] = $movie;
		} else if (strpos($movie["genre"], "documentary")) {
			$documentaries[] = $movie;
		} else {
			$all[] = $movie;
		}
		$return = array_merge($all, $documentaries, $shorts);
	}
	return $return;
}

function reAddMovie($id) {

	$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$id."'");
	$movie = $movieinfo[0];

	if ($movie["id"]) {
		db_query("DELETE FROM movie WHERE id = '".$id."'");
		addMovie($movie["id"]);
	}

}

function addMovie($id) {

	global $apikey;

	if (strpos($id, 'tt') !== false) {
		$sql = "SELECT * FROM  `movie` WHERE  `imdbid` =  '".$id."'";
	} else {
		$sql = "SELECT * FROM  `movie` WHERE  `id` =  '".$id."'";
	}

	$movieinfo = db_select($sql);
	$movie = $movieinfo[0];
	$mqid = $movie["id"];
	if (!$movie["id"]) {
		$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.themoviedb.org/3/movie/".$id."?api_key=".$apikey,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_POSTFIELDS => "{}",
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  $json = "cURL Error #:" . $err;
} else {
  $json = $response;
}
		$movie = json_decode($json, true);
		$movie = array_change_key_case($movie, CASE_LOWER);
		$printablemovie = $movie;
		//addPoster($movie["poster"]);
		//$movie = array_map('mysql_escape_string', $movie);

		foreach ($movie AS $key => $value) {
			if (!is_array($value)) {
				$movie[$key] = mysqli_real_escape_string(db_connect(), $value);
			}
		}

		$expdate = explode("-", $movie["release_date"]);
		$year = $expdate[0];
		$searcht = $movie["title"]." ".$movie["original_title"];
		$searchstring = iconv('UTF-8', 'ASCII//TRANSLIT', $searcht);//strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $searcht));

		if ($movie["id"] > 0) {
		$mqid = "m".$movie["id"];



		$query = "INSERT INTO `".dbname."`.`movie`
		(`id`, `title`, `originaltitle`, `year`, `releasedate`, `backdrop`, `budget`, `homepage`,
			`imdbid`, `language`, `overview`, `poster`, `revenue`, `runtime`, `status`, `tagline`, `tmdbid`, `searchstring`)
			VALUES
			('".$mqid."', '".$movie["title"]."', '".$movie["original_title"]."', '".$year."', '".$movie["release_date"]."',
			'".$movie["backdrop_path"]."', '".$movie["budget"]."', '".$movie["homepage"]."', '".$movie["imdb_id"]."',
			'".$movie["original_language"]."', '".$movie["overview"]."', '".$movie["poster_path"]."',
			'".$movie["revenue"]."', '".$movie["runtime"]."', '".$movie["status"]."', '".$movie["tagline"]."', '".$movie["id"]."', '".$searchstring."');";

		db_query($query);

		addGenresForMovie($movie["genres"], $mqid);
		addCompanies($movie["production_companies"], $mqid);
		addProdCountries($movie["production_countries"], $mqid);
		addLanguages($movie["spoken_languages"], $mqid);
		addCollections($movie["belongs_to_collection"], $mqid);
	}
		//$movie = $printablemovie;
	}
//$movie["mqid"] = $mqid;
	return $mqid;
}

function addGenresForMovie($genres, $movie) {
	foreach ($genres AS $g) {
		$query = "INSERT INTO `genre` (`movie`, `genre`)
		VALUES ('".$movie."', '".$g["id"]."');
		";
		db_query($query);
		//echo $query;
	}
}

function addCompanies($comp, $movie) {
	foreach ($comp AS $c) {
		$query = "INSERT INTO `productioncompany` (`id`, `name`)
		VALUES ('".$c["id"]."', '".$c["name"]."');
		";
		db_query($query);
		$query = "INSERT INTO `producedby` (`movie`, `company`)
		VALUES ('".$movie."', '".$c["id"]."');
		";
		db_query($query);
	}
}

function addProdCountries($countries, $movie) {
	foreach ($countries AS $c) {
		$query = "INSERT INTO `producedin` (`movie`, `country`)
		VALUES ('".$movie."', '".$c["iso_3166_1"]."');
		";
		db_query($query);
	}
}

function addLanguages($langs, $movie) {
	foreach ($langs AS $l) {
		$query = "INSERT INTO `language` (`movie`, `lang`)
		VALUES ('".$movie."', '".$l["iso_639_1"]."');
		";
		db_query($query);
	}
}

function addCollections($col, $movie) {
	foreach ($col AS $l) {
		$query = "INSERT INTO `collection` (`id`, `name`, `poster`, `backdrop`)
		VALUES ('".$l["id"]."', '".$l["name"]."', '".$l["poster_path"]."', '".$l["backdrop_path"]."');
		";
		db_query($query);
		$query = "INSERT INTO `incollection` (`collection`, `movie`)
		VALUES ('".$l["id"]."', '".$movie."');
		";
		db_query($query);
	}
}

function addTag($movie, $tag) {

	$user = $_SESSION["user"];
	$tag = trim($tag);
	$tag = strtolower(preg_replace('/[^a-zA-Z0-9-_ @]/','', $tag));
	if (strlen($tag) > 0) {
	$time = time();

	$query = "INSERT INTO `".dbname."`.`tag` (`movie`, `user`, `tag`, `timestamp`) VALUES ('".$movie."', '".$user."', '".$tag."', '".$time."');";

	db_query($query);
	return $query;
} else {
	return false;
}
}

function removeTag($movie, $tag) {
	$user = $_SESSION["user"];
	$sql = "DELETE FROM  `".dbname."`.`tag` WHERE  `tag`.`user` =  '".$user."' AND  `tag`.`movie` =  '".$movie."' AND  `tag`.`tag` = '".$tag."' LIMIT 1";
	return db_query($sql);
}

function getAllTags() {
	$tags = db_select("SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` GROUP BY tag ORDER BY tag ASC");
	return $tags;
}

function printAllTags($movie = null) {
	$tags = getAllTags();
	foreach ($tags AS $tag) {
		$print .= "<div class='tag' data-tag='".$tag["tag"]."' data-movie='".$movie."'>";
		$print .= $tag["tag"];
		$print .= "</div>";
	}
	return $print;
}

function printFriendsTags($movie = null) {
	$user = $_SESSION["user"];
	$tags1 = getFollowing($user);

	$print .= "";
	foreach ($tags1 AS $tag) {
		$print .= "<div class='tag' data-tag='@".$tag."' data-movie='".$movie."'>@";
		$print .= $tag;
		$print .= "</div>";
	}

	return $print;
}

function printAllFriendsTags($movie = null) {
	$user = $_SESSION["user"];
	$tags1 = getFollowing($user);
	$tags2 = getTagNamesByUser($movie, $user);
	$print .= "";
	foreach ($tags1 AS $tag) {
		if (in_array("@".$tag, $tags2)) {
			$active = "activebtn";
		} else {
			$active = "";
		}
		$print .= "<div class='tag $active' data-tag='@".$tag."' data-movie='".$movie."'>";
		$print .= $tag;
		$print .= "</div>";
	}
	return $print;



}

function printFriendsAndTags($movie = null) {
	$user = $_SESSION["user"];
	$tags1 = getFollowing($user);
	$tags2 = getAllTags();
	//$tags = array_merge($tags1, $tags2);
	$print .= "";
	foreach ($tags1 AS $tag) {
		$print .= "<div class='tag' data-tag='".$tag."' data-movie='".$movie."'>@";
		$print .= $tag;
		$print .= "</div>";
	}
	foreach ($tags2 AS $tag) {
		$print .= "<div class='tag' data-tag='".$tag["tag"]."' data-movie='".$movie."'>";
		$print .= $tag["tag"];
		$print .= "</div>";
	}
	return $print;
}

function getTagsByLetter($q) {
	$tags1 = db_select("SELECT tag FROM tag WHERE tag LIKE '$q%' GROUP BY tag ORDER BY tag DESC LIMIT 5");
	return $tags1;
}

function getUsersByLetter($q) {
	$tags1 = db_select("SELECT username FROM user WHERE username LIKE '$q%' ORDER BY username DESC LIMIT 5");
	return $tags1;
}

function checkIfUserExist($q) {
	$tags1 = db_select("SELECT username FROM user WHERE username = '$q'");
	if ($tags1[0]["username"] == $q && $q != "") {
		$ret = true;
	} else {
		$ret = false;
	}
	return $ret;
}

function getTagsByUser($movie, $user) {
	$tags = db_select("SELECT movie, user, tag, timestamp, COUNT(user) AS c, 'activebtn' AS active FROM  `tag` WHERE  `movie` =  '".$movie."' AND user = '".$user."' GROUP BY tag ORDER BY c DESC");
	return $tags;
}

function getTagNamesByUser($movie, $user) {
	$tags = db_select("SELECT tag FROM  `tag` WHERE  `movie` =  '".$movie."' AND user = '".$user."' GROUP BY tag ");
	$ret = array();
	foreach ($tags AS $tag) {
		$ret[] = $tag["tag"];
	}
	return $ret;
}

function getAllTagsByUser($user = NULL) {

	if ($user == NULL) {
		$where = "";
	} else if (is_array($user)) {
		$usersor = implode("' OR user = '", $user);
		$where = "WHERE user = '".$usersor."' ";

		} else {
		$where = "WHERE user = '".$user."'";
	}
	$sql = "SELECT * FROM `tag` ".$where." GROUP BY tag ORDER BY `tag` DESC";

	$tags = db_select($sql);
	return $tags;

}


function getTags($movie) {
	$user = $_SESSION["user"];
	$sql1 = "SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` WHERE  `movie` =  '".$movie."' AND user = '".$user."' GROUP BY tag ORDER BY c DESC";
	$tags1 = db_select($sql1);
	$sql2 = "SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` WHERE  `movie` =  '".$movie."' GROUP BY tag ORDER BY c DESC";
	$tags2 = db_select($sql2);


	$active = array();
	$nonactive = array();
	$entry = array();

	foreach ($tags1 AS $tag1) {
		$alreadyadded[] = $tag1["tag"];
	}

	foreach ($tags2 AS $tag2) {
			if (is_array($alreadyadded) && in_array($tag2["tag"], $alreadyadded)) {
				$entry["movie"] = $tag2["movie"];
				$entry["user"] = $tag2["user"];
				$entry["tag"] = $tag2["tag"];
				$entry["timestamp"] = $tag2["timestamp"];
				$entry["c"] = $tag2["c"];
				$entry["active"] = "activebtn";
				$active[] = $entry;
			} else {
				$nonactive[] = $tag2;
			}
		}

	if (empty($tags1)) {
		$tags = $tags2;
	} else {
		$tags = array_merge($active, $nonactive);
	}
	return $tags;
}

function printTags($tags, $movie) {
	$user = $_SESSION["user"];
	foreach ($tags AS $tag) {
		$active = $tag["active"];
		$fontsize = 14+$tag["c"];
		$print .= "<span style='font-size:".$fontsize."px' class='tag $active' data-movie='".$movie."' data-tag='".$tag["tag"]."' >";
		$print .= $tag["tag"];
		$print .= "</span> ";
	}

	if (empty($tags)) {
		$print = "";//<span class='white smalltext inblock padding0'>No tags</span>";
	}
	return $print;
}

function getSpecificTag($tag, $user, $movie) {
	$tags1 = db_select("SELECT tag FROM tag WHERE user = '$user' AND tag = '$tag' AND movie = '$movie'");
	return $tags1[0];
}

function printTagActive($tag) {
	if (empty($tag["tag"])) {
		return "";
	} else {
		return "activebtn";
	}
}

function getExternalStreams($title, $year = null)
{

	global $locale;

	$year = (int)$year;
    $data = array("query" => $title, "release_year_from" => $year, "release_year_until" => $year);
	$data_string = json_encode($data);

	$ch = curl_init('https://api.justwatch.com/titles/'.$locale.'/popular');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);

	$result = curl_exec($ch);
	$result = json_decode($result, true);
	//$streams = $result["items"][0]["offers"];
	//echo "Got external streams";
	//print_r($results);
	return $result;
}


function streamsAreOld($movieid) {
	$strms = getStreams($movieid);
	$week = 604800;
	//echo "<h3>streamtime: ".$strms[0]["timestamp"]." < ".time()." - ".$week."</h3>";
	if (timecodeConvert(timecodeHowLongAgo($strms[0]["timestamp"], "w")) > 2) {
		//echo "areold";
		return true;
	} else {
		return false;
	}
}

function timecodeConvert($time, $unit = "h") {
	if ($unit == "h") {
		$div = 3600;
	} else if ($unit == "min") {
		$div = 60;
	} else if ($unit == "s") {
		$div = 1;
	} else if ($unit == "d") {
		$div = 86400;
	} else if ($unit == "w") {
		$div = 604800;
	} else if ($unit == "y") {
		$div = 31536000;
	}
	$timeconvert = $time / $div;
	return $timeconvert;
}

function timecodeHowLongAgo($time, $unit = "h") {
	$diff = time() - $time;
	$timediff = timecodeConvert($diff, $unit);

	return $timediff;
}

function massUpdateStreams($movies) {
	$timeago = time() - (3600 * 48);
	$sqlpart = implode("' OR m.id = '", $movies);


	$sql = "SELECT s.*, m.title, m.year, m.id AS movieid
	FROM ".dbname.".movie AS m
LEFT JOIN ".dbname.".stream AS s
ON s.movieid = m.id
WHERE (m.id = '".$sqlpart."')
AND (s.timestamp < ".$timeago." OR s.timestamp IS NULL)
GROUP BY m.id";
//echo $sql;
	$streams = db_select($sql);

	foreach ($streams AS $stream) {
		//echo $stream["movieid"]." ".$stream["title"]." ".$stream["year"]."<br>";
		saveStreams($stream["movieid"], $stream["title"], $stream["year"]);
	}
}

function saveStreams($movieid, $title, $year) {

	//echo "savestreams<br>";
	$streams = getExternalStreams($title, $year);


	$cleandbtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $title));
	$cleanstreamtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $streams["items"][0]["title"]));
	/*echo "<br>";
	if ($cleandbtitle == $cleanstreamtitle) {
		echo "yes <br>".$cleandbtitle ."<br>". $cleanstreamtitle;
	} else {
		echo "no <br>".$cleandbtitle ."<br>". $cleanstreamtitle;
	}
	echo "<br>";*/

	if ($cleandbtitle == $cleanstreamtitle && is_array($streams["items"][0]["offers"])) {
		//echo "Update with new streams";
		$query = "DELETE FROM stream WHERE movieid = '$movieid'";

		db_query($query);

		$streams = $streams["items"][0]["offers"];

		$timestamp = time();

		foreach($streams AS $stream) {
			$region = "en_SE";
			$type = $stream["monetization_type"];
			$provider = $stream["provider_id"];
			$price = 0+$stream["retail_price"];
			$currency = $stream["currency"];
			$link = $stream["urls"]["standard_web"];
			$def = $stream["presentation_type"];
			$dateproviderid = $stream["date_provider_id"];

			$query = "INSERT INTO `".dbname."`.`stream`
			(`movieid`, `region`, `type`, `provider`, `price`, `currency`, `link`, `def`, `dateproviderid`, `timestamp`)
			VALUES
			('$movieid', '$region', '$type', '$provider', '$price', '$currency', '$link', '$def', '$dateproviderid', '$timestamp')
				";
				//echo $query."<br>";
			db_query($query);
		}
	} else {
		echo "Update empty";
		$query = "DELETE FROM stream WHERE movieid = '$movieid'";

		db_query($query);

		$region = "en_SE";
		$type = "";
		$provider = 0;
		$price = 0;
		$currency = "";
		$link = "";
		$def = "";
		$dateproviderid = "";
		$timestamp = time();

		$query = "INSERT INTO `".dbname."`.`stream`
		(`movieid`, `region`, `type`, `provider`, `price`, `currency`, `link`, `def`, `dateproviderid`, `timestamp`)
		VALUES
		('$movieid', '$region', '$type', '$provider', '$price', '$currency', '$link', '$def', '$dateproviderid', '$timestamp')
			";
			//echo $query."<br>";
		db_query($query);

	}

}


function getStreams($movieid) {

	$streams = db_select("SELECT *
FROM  mqold.`stream`
LEFT JOIN mqold.provider
ON stream.provider = provider.id
WHERE stream.movieid = '$movieid'
GROUP BY short
ORDER BY  `stream`.`price` ASC");

	return $streams;
}

function printStreams($movieid) {
$streams = getStreams($movieid);
	if (!empty($streams) && $streams[0]["link"]) {
		//$print = "<h3 class='marginbottom'>This title is available for streaming</h3>";
		foreach ($streams AS $stream) {
			$print .= "<a href='";
			$print .= $stream["link"];
			if ($stream["price"] > 0) {

			} else {
				$print .= "' class='free";
			}
			$print .= "'>";
			$print .= $stream["clear"];
			$print .= "</a>";
		}
	} else {
		$print = "<div class='padding'>No streams available</div>";
	}

	return $print;
}


function addPoster($link) {
	if ($link != null) {
		$linkparts = explode("/", $link);
		$filename = end($linkparts);
		array_pop($linkparts);
		$dir = implode("/", $linkparts);
		$imageContents = file_get_contents($link);
		$imageContentsEscaped = mysql_real_escape_string($imageContents);
		db_query("INSERT INTO poster (movie, filename, image) VALUES ('movie', '$filename', '$imageContentsEscaped')");
		echo "<h1>".$imageContents."</h1>";
	}
}

function isVisitor($userid) {
	if (strlen($userid) == 14 && substr($userid, 0, 1) == "u") {
		return true;
	} else {
		return false;
	}
}

function makeDummyUser() {
	$ip = getUserIp();
	$user = db_select("SELECT * FROM  `user` WHERE `ip` = '$ip'");
	$user = $user[0];
	//print_r($user);
	if (isVisitor($user["username"])) {
		newSession($user["username"]);

	} else {
		$username = newId("u");


		$query = "INSERT INTO `user` (`username`, `password`, `email`, `ip`)
		VALUES ('$username', '', '', '$ip');
		";
		$return = db_query($query);

		newSession($username);
		$_SESSION["loggedin"] = false;
	}

}


function newSession($username) {

	$_SESSION["user"] = $username;

	$id = session_id();
	$browser = $_SERVER['HTTP_USER_AGENT'];
	$time = time();
	$ip = getClientIp();


	$query = "INSERT INTO `session` (`id`, `time`, `ip`, `browser`, `user`)
	VALUES ('$id', '$time', '$ip', '$browser', '$username');
	";
	$return = db_query($query);
}

function get_browser_name($user_agent)
{
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';

    return 'Other';
}

function checkiffollows($follower, $follows) {
	$user = db_select("SELECT `timestamp` FROM  `follow` WHERE `follower` = '$follower' AND `follows` = '$follows'");
	return $user[0]["timestamp"];
}

function follow($follows = null) {
	$follower = $_SESSION["user"];

		if (checkiffollows($follower, $follows)) {
			$query = "DELETE FROM `follow` WHERE `follower` = '$follower' AND `follows` = '$follows';";
			$ret = false;
		} else {

			if (checkIfUserExist($follows)) {
			$timestamp = time();
			$query = "INSERT INTO `".dbname."`.`follow`
			(`follower`, `follows`, `timestamp`)
			VALUES ('$follower', '$follows', '$timestamp');";
			$ret = true;
		} else {
			$ret = false;
		}
		}
	db_query($query);
	return $ret;

}

function getTagFeed($user = null) {

		if (is_array($user)) {
			foreach ($user AS $u) {
				$postusersql .= " OR tag.user = '$u'";
			}
		} else if (isset($user) && $user != "") {
			$postusersql = " OR tag.user = '$user'";
		} else if ($user == null) {
			$postusersql = " OR tag.user != 'q'";
		}

		$sql = "SELECT 'tag' AS feedtype, tag.user AS user1id, user.username AS user1, tag.movie, tag.timestamp, movie.id AS movieid, movie.poster, GROUP_CONCAT(tag.tag SEPARATOR ', ') AS tags
FROM `tag`
LEFT JOIN user
ON tag.user = user.username
LEFT JOIN movie
ON tag.movie = movie.id
WHERE tag.tag NOT LIKE '@%' AND (user = 'start' $postusersql)
GROUP BY tag.movie
ORDER BY `tag`.`timestamp` DESC
		LIMIT 30";
		$feed = db_select($sql);

		return $feed;

}

function getShareFeed($user = null) {
$postusersql = "";
		if (is_array($user)) {
			foreach ($user AS $u) {
				$postusersql .= " OR tag.user = '$u'";
			}
			$postusersql = " OR tag.tag LIKE '@".$_SESSION["user"]."'";
		} else if (isset($user) && $user != "") {
			$postusersql = " OR tag.user = '$user'";
			$postusersql .= " OR tag.tag LIKE '@".$user."'";
		} else if ($user == null) {
			$postusersql = " OR tag.tag LIKE '@".$_SESSION["user"]."'";
		}

$sql = "SELECT 'tag' AS feedtype, tag.tag AS tag, tag.user AS user1id, user.username AS user1, tag.movie, tag.timestamp, movie.id AS movieid, movie.title AS title, movie.poster, movie.backdrop AS backdrop
				FROM `tag`
				LEFT JOIN user
				ON tag.user = user.username
				LEFT JOIN movie
				ON tag.movie = movie.id
				WHERE (tag.tag LIKE '@%' AND (user = 'start' $postusersql))
				GROUP BY tag.movie
				ORDER BY `tag`.`timestamp` DESC
				LIMIT 30";
		$feed = db_select($sql);

		return $feed;

}

function getPostsFeed($user = null) {
	$order = "ORDER BY `post`.`timestamp` DESC";
		if (is_array($user)) {
			foreach ($user AS $u) {
				$postusersql .= " OR userid = '$u'";
			}
		} else if (isset($user) && $user != "") {
			$postusersql = " OR userid = '$user'";
		} else if ($user == null) {
			$postusersql = " OR userid != 'q') AND (reply.reply IS NULL";
			$order = "ORDER BY `votes` DESC";
		}

		/*
		SELECT
		reply.reply,
		user.username AS user1, user.username AS user1id,
		post.timestamp AS timestamp, post.emoji,
		post.id, post.message, post.userid, post.movieid, movie.year AS movieyear, movie.poster AS poster,
	(SUM((10+od.upvote-od.downvote)*1000/(UNIX_TIMESTAMP()-post.timestamp)))
		AS votes
		FROM user
		LEFT JOIN post
		ON user.username = post.userid
		LEFT JOIN vote od
		ON post.id = od.post
		LEFT JOIN reply
		ON reply.reply = post.id
		LEFT JOIN movie
		ON post.movieid = movie.id
		WHERE post.movieid = '$movie' AND reply.reply IS NULL
		GROUP BY post.id
		ORDER BY `votes` DESC
		*/

		$sql = "SELECT 'post' AS feedtype, movie.title AS movietitle, movie.id AS movieid, movie.year AS movieyear, movie.poster AS poster, movie.backdrop AS backdrop, reply.original AS origmsg,
		user.username AS user1, user.username AS user1id, post.message, post.emoji, post.timestamp AS timestamp, post.id AS id,
		(SUM((10+od.upvote-od.downvote)*1000/(UNIX_TIMESTAMP()-post.timestamp)))
			AS votes
		FROM `post`
		LEFT JOIN movie
		ON post.item = movie.id
		LEFT JOIN user
		ON post.userid = user.username
		LEFT JOIN reply
		ON post.id = reply.reply
		LEFT JOIN vote od
		ON post.id = od.post
		WHERE (userid = 'start' $postusersql)
		GROUP BY post.id
		$order
		LIMIT 30";
		$feed = db_select($sql);

		return $feed;

}

function getVotesFeed($user) {

		if (is_array($user)) {
			$postusersql = implode("' OR vote.user = '", $user);
			$postusersql = " vote.user = '".$postusersql. "' ";

		} else if (isset($user) && $user != "") {
			$postusersql .= " vote.user = '$user'";
		}

$sql = "SELECT 'vote' AS feedtype, movie.title AS movietitle2, movie.id AS movieid2, movie.poster AS poster2,
				m2.title AS movietitle, m2.id AS movieid, m2.poster AS poster,
				usa.username AS user1, usa.username AS user1id,
				op.username AS user2, op.username AS user2id,
				post.message, vote.post AS post, vote.upvote, vote.downvote, vote.timestamp AS timestamp
				FROM vote
				LEFT JOIN post
				ON post.id = vote.post
				LEFT JOIN movie
				ON vote.post = movie.id
				LEFT JOIN movie AS m2
				ON post.item = m2.id
				LEFT JOIN user AS usa
				ON vote.user = usa.username
				LEFT JOIN user AS op
				ON post.userid = op.username
				WHERE post.message != '' AND ($postusersql)
				ORDER BY `vote`.`timestamp`  DESC
				LIMIT 30";
		$feed = db_select($sql);

		return $feed;
}

function getRatingFeed($user) {

		if (is_array($user)) {

			foreach ($user AS $u) {
				$postusersql .= " OR r.user = '$u'";
			}
		} else if (isset($user) && $user != "") {
			$postusersql .= " OR r.user = '$user'";
		}

$sql = "SELECT 'rating' AS feedtype, u.username AS user1, u.username AS user1id, r.rating AS rating, m.id AS movieid, m.title, m.poster AS poster, m.backdrop AS backdrop, r.timestamp
FROM `ratemovie` AS r
LEFT JOIN user AS u
ON r.user = u.username
LEFT JOIN movie AS m
ON r.movie = m.id
WHERE r.user = 'dude'
".$postusersql."
ORDER BY r.timestamp DESC
LIMIT 30";

		$feed = db_select($sql);
//echo $sql;
		return $feed;

}

function getFeed($user = null) {
	if ($user != null) {
	$ratingfeed = getRatingFeed($user);
	$tagfeed = getTagFeed($user);
	$postfeed = getPostsFeed($user);
	$votefeed = getVotesFeed($user);

}
	if (!is_array($ratingfeed)) {
		$ratingfeed = array();
	}
	if (!is_array($tagfeed)) {
		$tagfeed = array();
	}
	if (!is_array($postfeed)) {
		$postfeed = array();
	}
	if (!is_array($votefeed)) {
		$votefeed = array();
	}

	$sharefeed = getShareFeed($user);
	if (!is_array($sharefeed)) {
		$sharefeed = array();
	}

	$feed1 = array_merge($postfeed, $votefeed, $tagfeed, $ratingfeed, $sharefeed);
	//$feed1 = array_merge($feed1, );

	usort($feed1, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
	});

	//$feed = print_r($feed1, true);
	return $feed1;

	return false;

}

function printFeedItem($content) {

}

function printFeed($feed) {
	$print = "";
global $basethumburl;
	//$feed = getFeed($user);
	if (empty($feed)) {
		//$print = "<div class='large padding'>Follow users to see their activity in the feed</div>";
	} else {
		//$print = "<table class='feed'>";
	}


$rdivstart = "<div class='feeditem narrow clear relative recitem' style='background-image:url(".basebackdropurl.")'>";

$fdivstart = "<div class='feeditem narrow clear relative'>";

$ficonstart = "<div class='feeditemcell feedicon'><i class='material-icons'>";
$ficonend = "</i></div>";
$fconstart = "<div class='feeditemcell feedcontent'><div class='floatright hw'></div>";
$fconend = "</div>";
$fdivend = "</div>";


	foreach ($feed AS $row) {

		if (isVisitor($row["user1"])) { //Set visitors username to generic name "visitor"
			$username = "visitor";
		} else {
			$username = $row["user1"];
		}

		$fusername = "<div class='feedusername small'><a href='/user/".$row["user1"]."'>".$username."</a></div>";

		$fposter = '<div class="qmovielinkholder floatright padding">
		<a class="qmovielink" href="/movie/'.$row["movieid"].'"><img alt="'.$row["movietitle"].' ('.$row["movieyear"].')" src="'.basethumburl.$row["poster"].'"></a>
		</div>';


		switch ($row["feedtype"]) { //determine type
			case "post":
				$type = 1;
				break;
			case "vote":
				if ($row["upvote"]) {
					$type = 2;
				} else if ($row["downvote"]) {
					$type = 3;
				}
				break;
			case "rating":
				$type = 4;
				break;
			case "tag":
				if ("@" == substr($row["tag"], 0, 1)) {
					if ($row["tag"] == "@".$_SESSION["user"]) {
						$type = 7;
					} else {
						$type = 5;
					}
				} else {
					$type = 6;
				}
				break;
			default:
				$type = 0;
				break;
		}




		switch ($type) { //print freed item
			case 1:
				$input = array();
				$input[] = $row;
				$print .= printMessage($input);

				break;
			case 2:
				$print .= $fdivstart;
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "thumb_up";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= "<a href='/quack/".$row["post"]."'>";
				$print .= "\"".$row["message"]."\"";
				$print .= "</a>";
				$print .= "<a href='/user/".$row["user2"]."' class='block small paddingtop'>".$row["user2"]."</a>";
				$print .= $fconend;
				$print .= $fposter;
				$print .= $fdivend;
				break;
			case 3:
				$print .= $fdivstart;
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "thumb_down";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= "<a href='/quack/".$row["post"]."'>";
				$print .= "\"".$row["message"]."\"";
				$print .= "</a>";
				$print .= "<a href='/user/".$row["user2"]."' class='block small paddingtop'>".$row["user2"]."</a>";
				$print .= $fconend;
				$print .= $fposter;
				$print .= $fdivend;
				break;
			case 4:
				$print .= $fdivstart;
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "star";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= "<div class='red large'>".($row["rating"]/2)."</div> stars to ";
				$print .= "<div class='red large nowrap'>".$row["title"]."</div>";
				$print .= $fconend;
				$print .= $fposter;
				$print .= $fdivend;
				break;
			case 5:
				$print .= $fdivstart;
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "share";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= $row["tag"];
				$print .= $fconend;
				$print .= $fposter;
				$print .= $fdivend;
				break;
			case 6:
				$print .= $fdivstart;
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "label";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= $row["tags"];
				$print .= $fconend;
				$print .= $fposter;
				$print .= $fdivend;
			 	break;
			case 7:
				$print .= "<div class='feeditem narrow clear relative recitem' style='background-image:url(".basebackdropurl.$row["backdrop"].")'>";
				$print .= "<div class='tintedwindow'>";
				$print .= $fusername;
				$print .= $ficonstart;
				$print .= "share";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= $username;
				$print .= " recommended ";
				$print .= "<a href='/movie/".$row["movieid"]."' class='block red'>".$row["title"]."</a>";
				$print .= $fconend;
				$print .= "<a href='/list/?tag%5B%5D=%40".$_SESSION["user"]."' class='padding block small'>All recommended movies</a>";
				$print .= $fposter;
				$print .= $fdivend;
				$print .= "</div>";
				break;
		}


	}
	//$print .= "</table>";
	return $print;
}


function getFilteredItems($user, $tag) {

	if (empty($user)) {
		$wuser = "user != '1'";
	} else if (is_array($user)) {
		foreach ($user AS $u) {
			if (is_array($u)) {
				$users[] = $u["username"];
			} else {
				$users[] = $u;
			}
		}
		$wuser = implode("' OR user = '", $users);
		$wuser = "user = '$wuser'";
	} else {
		$wuser = $user;
		$wuser = "user = '$wuser'";
	}
	if (is_array($tag)) {
		foreach ($tag AS $t) {
			if (is_array($t)) {

				$tags[] = $t["tag"];
			} else {
				$tags[] = $t;
			}
		}
		$wtag = implode("' OR tag = '", $tags);
	} else {
		$wtag = $tag;
	}

	$sql = "SELECT tag.movie AS item, movie.title, movie.year, movie.poster
	FROM tag
	LEFT JOIN movie ON movie.id = tag.movie
	WHERE ($wuser)
	AND (tag = '$wtag')
	GROUP BY movie";

	$items = db_select($sql);
	return $items;
}


function removePost($post) {
	$user = $_SESSION["user"];
	$sql = "DELETE FROM `".dbname."`.`post` WHERE `post`.`id` = '".$post."' AND userid = '".$user."'";
	return db_query($sql);
}

function checkImage($url) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);

	if ($headers['http_code']) {
		$return = $url;
	} else {
		$return = "https://images-na.ssl-images-amazon.com/images/M/MV5BMTUxMzQyNjA5MF5BMl5BanBnXkFtZTYwOTU2NTY3._V1_SX300.jpg";
	}

    return $return;
}

function checkImage2($posterurl) {

	$hdrs = @get_headers($posterurl);

    //echo @$hdrs[1]."\n";

    //return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;


	/*if (!@GetImageSize($posterurl)) {
		$posterurl = "https://images-na.ssl-images-amazon.com/images/M/MV5BMTUxMzQyNjA5MF5BMl5BanBnXkFtZTYwOTU2NTY3._V1_SX300.jpg";
	}
	return $posterurl;*/
	return $posterurl;
}

function getFollowing($userid) {
	$userinfo = db_select("SELECT follows FROM  `follow` WHERE  `follower` =  '".$userid."'");
	foreach ($userinfo AS $user) {
		$users[] = $user["follows"];
	}
	return $users;
}

function getBuffet() {

	$movies = db_select("SELECT m.*, SUM(r.rating) AS rate
	FROM ".dbname.".movie AS m
	LEFT JOIN ".dbname.".ratemovie AS r
	ON r.movie = m.id
	GROUP BY m.id
	ORDER BY rate DESC");

	return $movies;

}

function getStreamSites($user, $flatrate = true) {

	if ($flatrate) {
		$type = "AND s.type = 'flatrate'";
	} else {
		$type = "AND s.type != 'flatrate'";
	}

	$sql = "SELECT *, COUNT(*) AS quant FROM ".dbname.".stream AS s
	LEFT JOIN ".dbname.".tag AS t
	ON t.movie = s.movieid
	LEFT JOIN ".dbname.".provider AS p
	ON p.id = s.provider
	WHERE t.tag = 'bookmark'
	AND t.user = '".$user."'
	 ".$type."
	GROUP BY provider
	ORDER BY quant DESC";

	$streamsites = db_select($sql);
	return $streamsites;

}



function getStreamableMovies($user, $tag = "bookmark") {

	$sql = "SELECT * FROM ".dbname.".stream AS s
LEFT JOIN ".dbname.".tag AS t
ON t.movie = s.movieid
LEFT JOIN ".dbname.".movie AS m
ON s.movieid = m.id
LEFT JOIN ".dbname.".provider AS p
ON p.id = s.provider
WHERE t.tag = '".$tag."'
AND t.user = '".$user."'
GROUP BY movieid, provider
";

	$movies = db_select($sql);
	return $movies;

}



$timeforpageload = time();


$webpagetitle = "moviequack";
?>
