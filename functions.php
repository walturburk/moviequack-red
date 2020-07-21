<?php


$searchfieldholderclass = "";
$q = "";

autoLogin();
//getDontTag();
if ($_SESSION["loggedin"]) {
} else if (strpos($_SERVER['REQUEST_URI'], "welcome") > 0) {
} else if (strpos($_SERVER['REQUEST_URI'], "login") > 0) {
} else if (strpos($_SERVER['REQUEST_URI'], "join") > 0 && isset($_REQUEST["inv"])) {
} else if (strpos($_SERVER['REQUEST_URI'], "massupdatestreams") > 0) {
} else {
	header("Location: /welcome");
}

$apikey = "0a9b195ddb48019271ac2de755730dd4";
$userid = $_SESSION["user"];
$basethumburl = "https://image.tmdb.org/t/p/w92/";
$baseposterurl = "https://image.tmdb.org/t/p/w342/";
$basebackdropurl = "https://image.tmdb.org/t/p/w780/";
$basebigbackdropurl = "https://image.tmdb.org/t/p/w1280/";

define("apikey", "0a9b195ddb48019271ac2de755730dd4");
define("basethumburl", "https://image.tmdb.org/t/p/w92/");
define("basepostermurl", "https://image.tmdb.org/t/p/w185/");
define("baseposterurl", "https://image.tmdb.org/t/p/w342/");
define("basebackdropurl", "https://image.tmdb.org/t/p/w780/");

$basicfilemtime = filemtime(__DIR__."/css/basic.css");
$moviequackfilemtime = filemtime(__DIR__."/css/moviequack.css");
$functionsfilemtime = filemtime(__DIR__."/js/functions.js");


function addGenreNames() {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.themoviedb.org/3/genre/movie/list?api_key=0a9b195ddb48019271ac2de755730dd4",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_CUSTOMREQUEST => "GET"
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

function getGenreNamesForMovie($movie) {
	return db_select("SELECT genrenames.name FROM `genrenames` LEFT JOIN `genre` ON `genre`.`genre` = `genrenames`.`id` WHERE movie = '".$movie."'");
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
		} else if ($prefix == "i") {
			$idfromdb = db_select("SELECT id FROM `invite` WHERE id = '$id'");
		}
	} while ($id == $idfromdb[0]["id"]);

	return $id;
}


function createInviteCode($owner) {
	$id = newId("i");
	$query = "INSERT INTO `invite` (`id`, `owner`, `consumedby`, `consumedat`) VALUES ('".$id."', '".$owner."', '', 0);
	";
	return db_query($query);
}

function getInviteCode($user) {
	$inv = db_select("SELECT id FROM `invite` WHERE `invite`.`owner` = '".$user."' AND consumedby = '' ");
	return $inv[0]["id"];
}

function useInviteCode($id, $consumer) {
	$inv = db_select("SELECT id, owner FROM `invite` WHERE `invite`.`id` = '".$id."' AND consumedby = '' ");
	if ($inv) {
		follow($inv[0]["owner"], $consumer);
		$query = "UPDATE `invite` SET `consumedby` = '".$consumer."', `consumedat` = '".time()."' WHERE `invite`.`id` = '".$id."';";
		$ret = db_query($query);
		return $ret;
	} else {
		return false;
	}
	
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
				addTag("", $tag, $_SESSION["user"]);
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
	createInviteCode($_SESSION["user"]);
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

function getMovieRatings() {
	$sql = "SELECT movie, 
	SUM(rating) / COUNT(rating) AS overall 
	FROM ".dbname.".ratemovie
	GROUP BY movie";

	$movies = db_select($sql);
	return $movies;
}

function getWeightedMovieRatings($user) {
	$sql = "SELECT movie, SUM((tb.corr*rm.rating)) / COUNT(rm.rating) AS overall 
	FROM ".dbname.".ratemovie as rm 
	LEFT JOIN 
	(SELECT corr, ((10+count)/10)*corr AS weight, user2 
	FROM
	(
	SELECT user1, user2, count(*) as count,  
	(avg(x * y) - avg(x) * avg(y)) / 
	(sqrt(avg(x * x) - avg(x) * avg(x)) * sqrt(avg(y * y) - avg(y) * avg(y))
	) 
	AS corr
	FROM
	(
	SELECT t.rating as x, s.rating as y, t.user AS user1, s.user AS user2 
	FROM ".dbname.".ratemovie t
	INNER JOIN ".dbname.".ratemovie s
	ON (t.movie = s.movie)
	WHERE (t.user = '".$user."') AND t.user != s.user) AS xandy
	GROUP BY user2
	ORDER BY corr DESC
	) 
	as tab
	WHERE corr IS NOT NULL
	ORDER BY weight DESC) as tb 
	ON tb.user2 = rm.user
	GROUP BY movie";

	$movies = db_select($sql);
	return $movies;
}

function getTopSuggestedMovies($user) {
	$sql = "SELECT movieinfo.id AS movieid, po.filename as poster, overall, (overall+COALESCE(weighted_overall, 0)) as total_overall 
	FROM 
	(SELECT movie AS movieid, rm.rating, SUM(rm.rating)/COUNT(rm.rating) as overall, SUM((tb.corr*(rm.rating-6))) / COUNT(rm.rating) AS weighted_overall
	FROM ".dbname.".ratemovie as rm 
	LEFT JOIN 
	(SELECT corr, user2 
	FROM
	(
	SELECT user1, user2, count(*) as count,  
	(avg(x * y) - avg(x) * avg(y)) / 
	(sqrt(avg(x * x) - avg(x) * avg(x)) * sqrt(avg(y * y) - avg(y) * avg(y))
	) 
	AS corr
	FROM
	(
	SELECT t.rating as x, s.rating as y, t.user AS user1, s.user AS user2 
	FROM ".dbname.".ratemovie t
	INNER JOIN ".dbname.".ratemovie s
	ON (t.movie = s.movie)
	WHERE (t.user = '".$user."') AND t.user != s.user
	) 
	AS xandy
	GROUP BY user2
	ORDER BY corr DESC
	) 
	as tab
	WHERE corr IS NOT NULL
	ORDER BY corr DESC) as tb 
	ON tb.user2 = rm.user
	GROUP BY movie) AS mega_table
	LEFT JOIN (
	SELECT * FROM ".dbname.".ratemovie WHERE user = '".$user."' 
	) AS user1rate 
	ON movieid = user1rate.movie 
	LEFT JOIN ".dbname.".movie as movieinfo
	ON movieinfo.id = movieid 
	LEFT JOIN ".dbname.".poster as po
	ON po.movieid = mega_table.movieid  AND po.size = 1 
	LEFT JOIN tag 
	ON tag.movie = mega_table.movieid AND tag.tag = 'bookmark' AND tag.user = '".$user."'
	WHERE user1rate.user IS NULL 
	AND tag.user IS NULL
	ORDER BY total_overall DESC";
	$movies = db_select($sql);
	return $movies;
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

	$url = "https://api.themoviedb.org/3/search/movie?include_adult=false&page=1&query=".$apiq."&api_key=".$apikey;

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
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


	$print = file_get_contents($url);
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

function removeMovie($id) {

	return db_query("DELETE FROM movie WHERE id = '".$id."'");

}

function reAddMovie($id) {
	$sql = "SELECT * FROM  `movie` WHERE  `id` =  '".$id."'";
	$movieinfo = db_select($sql);
	$movie = $movieinfo[0];
	if ($movie["id"]) {
		db_query("DELETE FROM movie WHERE id = '".$id."'");
	}

	$mid = substr($id, 1); //remove the "m"
	addMovie($mid); 

}

function addMovie($id) {

	global $apikey;
	$isimdbid = false;
	if (strpos($id, 'tt') !== false ) {
		$isimdbid = true;
		$sql = "SELECT * FROM  `movie` WHERE  `imdbid` =  '".$id."'";
	} else {
		$sql = "SELECT * FROM  `movie` WHERE  `id` =  '".$id."'";
	}

	$movieinfo = db_select($sql);
	$movie = $movieinfo[0];
	$mqid = $movie["id"];

	

	if (!$movie["id"]) {

		if ($isimdbid) {
			$url = "https://api.themoviedb.org/3/find/".$id."?api_key=".$apikey."&language=en-US&external_source=imdb_id";
			$json = file_get_contents($url);
			$arr = json_decode($json, true);
			$id = $arr["movie_results"][0]["id"];
			$mqid = "m".$id;
			addTag($mqid, "bookmark", $_SESSION["user"]);
		}

		/*$curl = curl_init();

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
}*/


		$url = "https://api.themoviedb.org/3/movie/".$id."?api_key=".$apikey;
		$json = file_get_contents($url);
		//echo "<br>urlwithid:".$url."<br>";
		
		$movie = json_decode($json, true);
		$movie = array_change_key_case($movie, CASE_LOWER);
		$printablemovie = $movie;
		//addPoster($movie["poster"]);
		//$movie = array_map('mysql_escape_string', $movie);
		/*echo "<h4>".$url;
		print_r($response);
		
		echo "</h4>";*/
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

			$thumb = $movie["poster_path"]."_thumb";
			$poster = $movie["poster_path"]."_poster";
			$backdrop = $movie["backdrop_path"]."_backdrop";
			addPoster($mqid, $thumb, 1);
			downloadPosterToDir(basethumburl.$movie["poster_path"], $thumb);
			addPoster($mqid, $poster, 3);
			downloadPosterToDir(baseposterurl.$movie["poster_path"], $poster);
			addPoster($mqid, $backdrop, 5);
			downloadPosterToDir(basebackdropurl.$movie["backdrop_path"], $backdrop);
			

			addGenresForMovie($movie["genres"], $mqid);
			addCompanies($movie["production_companies"], $mqid);
			addProdCountries($movie["production_countries"], $mqid);
			addLanguages($movie["spoken_languages"], $mqid);
			addCollections($movie["belongs_to_collection"], $mqid);
		}
		//$movie = $printablemovie;
		
	} else if ($isimdbid) {
		addTag($movie["id"], "bookmark", $_SESSION["user"]);
	}
	
//$movie["mqid"] = $mqid;
	return $mqid;
}

function downloadPosterToDir($url, $filename = null) {
	//$url = "https://image.tmdb.org/t/p/w342/6UTZmeQcxbtW32MyR5nKIx7ID4f.jpg";
	
	//if source url is set, proceed, otherwise return false
	if ($url) { 

		//get headers and check filesize before attempting download
		$headers = get_headers($url, true);
		if ($headers['Content-Length'] > 0) {
			$dir = "/var/www/html/moviequack/img/posters/";
	
		//if filename is set, save as specific file
		if ($filename) {
			$cmd = 'wget -O '.$dir.$filename.' '.$url;
		} else { //otherwise just save with sourcename
			$cmd = 'wget -P '.$dir.' '.$url;
		}
	
		return shell_exec($cmd);
		}
		

	} else {

		return false;

	}

}

function addPoster($movieid, $filename, $size) {

	$query = "INSERT INTO `poster` (`movieid`, `filename`, `size`, `timestamp`) 
	VALUES ('".$movieid."', '".$filename."', ".$size.", ".time().");";

	return db_query($query);
}

function getPoster($movieid, $size) {
	$dir = "/img/posters/";
	$posters = db_select("SELECT filename FROM `poster` WHERE movieid = '".$movieid."' AND size = ".$size);
	if ($posters) {
		return $dir.$posters[0]["filename"];
	} else {
		return false;
	}
	
}

//enter movietitle and movieyear as params
function getWikipediaPage($movietitle, $movieyear) {
	$searchPage = "prometheus 2012 film";
	$searchPage = $movietitle." ".$movieyear." film";

	$endPoint = "https://en.wikipedia.org/w/api.php";
	$params = [
		"action" => "query",
		"list" => "search",
		"srsearch" => $searchPage,
		"format" => "json"
	];

	$url = $endPoint . "?" . http_build_query( $params );

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec( $ch );
	curl_close( $ch );

	$result = json_decode( $output, true );


	if ($result["query"]["search"][0]["pageid"] > 0) {
		foreach ($result["query"]["search"] as $hit) {
			$hit["title"] = explode("(", $hit["title"]);
			echo $hit["title"]."<br>".$movietitle;
			if (strpos($hit["title"], $movietitle) !== false || strpos($movietitle, $hit["title"]) !== false) {
				return $hit;
			}
		}
	} else {
		//print_r($result);
		//die ("No page id found from search");
		return false;
	}
}

//use the result from getWikipediaPage() as parameter for this function
function getWikipediaLink($page) {

	$endPoint = "https://en.wikipedia.org/w/api.php";
	$params = [
		"action" => "query",
		"format" => "json",
		"titles" => $page["title"],
		"prop" => "info",
		"inprop" => "url|talkid"
	];

	$url = $endPoint . "?" . http_build_query( $params );

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec( $ch );
	curl_close( $ch );

	$result2 = json_decode( $output, true );

	//echo "https://en.wikipedia.org/wiki/".str_replace(" ", "_", $result["parse"]["title"]);


	return $result2["query"]["pages"][$page["pageid"]]["fullurl"];

}

//enter result from getWikipediaPage()
function getWikipediaSections($page) {

	$endPoint = "https://en.wikipedia.org/w/api.php";
	$params = [
		"action" => "parse",
		"format" => "json",
		"page" => $page["title"], 
		"prop" => "sections"
	]; 
	//?action=parse&page=Manual:Extensions&prop=sections

	$url = $endPoint . "?" . http_build_query( $params );

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec( $ch );
	curl_close( $ch );

	$result = json_decode( $output, true );

	//echo "https://en.wikipedia.org/wiki/".str_replace(" ", "_", $result["parse"]["title"]);


	return $result["parse"]["sections"];

}

//enter result from getWikipediaPage()
function getWikipediaTextFromSection($page, $section) {

	$endPoint = "https://en.wikipedia.org/w/api.php";

	/*$params = [
		"action" => "query",
		"format" => "json",
		"titles" => $page["title"], 
		"section" => $section, 
		'prop' => 'extracts',
		'explaintext' => "true"
	]; */
	
	$params = [
		"action" => "parse",
		"format" => "json",
		"page" => $page["title"], 
		"prop" => "wikitext",
		"section" => $section,
		"disabletoc" => "1"
	]; 
	//api.php?action=parse&format=json&page=house&prop=text&section=3&disabletoc=1

	$url = $endPoint . "?" . http_build_query( $params );

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$output = curl_exec( $ch );
	curl_close( $ch );

	$result = json_decode( $output, true );

	//echo "https://en.wikipedia.org/wiki/".str_replace(" ", "_", $result["parse"]["title"]);


	return $result["parse"]["wikitext"]["*"];

}

function splitWikitext($text) {

	$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
$text = strtr( $text, $unwanted_array );

	$text = str_replace("'s", "", $text);
	$text = preg_replace('/[^a-zA-Z0-9]/', ' ', $text);
	$text = preg_replace('/\s+/', ' ',$text);

	$array = explode(" ", $text);
	return $array;
}

function getFilteredWords() {
	$words = db_select("SELECT word FROM `filteredwords` ");
	return array_column($words, 'word');
}

function filterWords($array) {
	$sql = "DELETE FROM  `".dbname."`.`tag` WHERE `tag`.`tag` = '";
	$sql .= implode("' OR `tag`.`tag` = '", $array);
	$sql .= "'";
	return db_query($sql);
}

function addFilterWord($word, $user = "") {
	$query = "INSERT INTO `filteredwords` (`word`, `user`, `timestamp`) 
		VALUES ('".$word."', '".$user."', ".time().");";
	db_query($query);
	return $query;
}

function removeFilterWord($word) {
	$sql = "DELETE FROM  `".dbname."`.`filteredwords` WHERE `word` = '".$word."'";
	return db_query($sql);
}

function matchMovieName($movie, $checknameo) {
	
	$movieid = $movie["id"];
	$checkname = cleanTitle($checknameo);
	echo "checkname:".$checkname;

	if (cleanTitle($movie["title"]) == $checkname) {
		return true;
	}
	if (cleanTitle($movie["originaltitle"]) == $checkname) {
		return true;
	}
	$titles = db_select("SELECT * FROM `akas` WHERE `movieid` LIKE '".$movieid."' AND `title` LIKE '".$checknameo."'");
	print_r($titles);
	foreach ($titles AS $title) {
		if (cleanTitle($title["title"]) == $checkname) {
			return true;
		}
	}
	return false;
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

function addAkas($akas, $movieid) {
	if (!is_array($akas)) {
		$akas = array($akas);
	}
	foreach ($akas AS $a) {
		$query = "INSERT INTO `akas` (`movieid`, `title`) VALUES ('".$movieid."', '".$a."');";
		db_query($query);
	}
}

function addLinks($links, $movieid, $linkname = "") {
	if (!is_array($links)) {
		$links = array($links);
	}
	foreach ($links AS $l) {
		$query = "INSERT INTO `link` (`movieid`, `linkname`, `url`, `timestamp`) VALUES ('".$movieid."', $linkname'', '".$l."', CURRENT_TIMESTAMP);";
		db_query($query);
	}
}


function addTag($movie, $tags, $user) {

	if (!is_array($tags)) {
		$tags = array($tags);
	}
	$queryp = array();
	$time = time();
	foreach ($tags AS $tag) {
		$tag = trim($tag);
		$tag = strtolower(preg_replace('/[^a-zA-Z0-9-_ @]/','', $tag));
		if (strlen($tag) > 1) {
			
			$queryp[] = "('".$movie."', '".$user."', '".$tag."', '".$time."')";

		}
	}

	$query = "INSERT IGNORE INTO `".dbname."`.`tag` (`movie`, `user`, `tag`, `timestamp`) VALUES ".implode(", ", $queryp).";";
	//echo $query;
	$ret = db_query($query);
	//echo $query;
	return $ret;
}

function removeTag($movie, $tag) {
	$user = $_SESSION["user"];
	$sql = "DELETE FROM  `".dbname."`.`tag` WHERE  `tag`.`user` =  '".$user."' AND  `tag`.`movie` =  '".$movie."' AND  `tag`.`tag` = '".$tag."' LIMIT 1";
	return db_query($sql);
}

function removeWikiTagsByMovieovie ($movie) {
	$user = $_SESSION["user"];
	$sql = "DELETE FROM  `".dbname."`.`tag` WHERE  `tag`.`user` =  'wikiplot' AND  `tag`.`movie` =  '".$movie."'";
	return db_query($sql);
}

function getAllTags() {
	$tags = db_select("SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` GROUP BY tag ORDER BY c DESC");
	return $tags;
}

function getLinks($movieid) {
	$links = db_select("SELECT movieid, linkname, url, timestamp FROM link WHERE movieid = '".$movieid."'");
	return $links;
}

function printLinks($links) {
	$print = "";
	foreach ($links AS $link) {
		$print .= "<a style='display:block' class='link' href='".$link["url"]."' data-movie='".$link["movieid"]."'>";
		if ($link["linkname"] != "") {
			$print .= $link["linkname"];
		} else {
			$print .= $link["url"];
		}
		$print .= "</a>";
	}
	return $print;
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
	$sql = "SELECT username FROM `".dbname."`.user WHERE username = '$q'";
	$tags1 = db_select($sql);
	if (strtolower($tags1[0]["username"]) == strtolower($q) && $q != "") {
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
		$where = "WHERE tag LIKE 'a%' ";
	} else if (is_array($user)) {
		$usersor = implode("' OR user = '", $user);
		$where = "WHERE user = '".$usersor."' ";

		} else {
		$where = "WHERE user = '".$user."'";
	}
	$sql = "SELECT * FROM `tag` ".$where." GROUP BY tag ORDER BY `tag` ASC";

	$tags = db_select($sql);
	
	return $tags;

}


function getTags($movie) {
	$user = $_SESSION["user"];
	$sql1 = "SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` WHERE  `movie` =  '".$movie."' AND user = '".$user."' GROUP BY tag ORDER BY c DESC";
	$tags1 = db_select($sql1);
	$sql2 = "SELECT movie, user, tag, timestamp, COUNT(user) AS c FROM  `tag` WHERE  `movie` =  '".$movie."' GROUP BY tag ORDER BY c DESC, RAND()";
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

function printTagsToFilter($tags) {
	foreach ($tags AS $tag) {
		$active = "activebtn";
		$fontsize = 18+$tag["c"];
		if ($fontsize > 36) {
			$fontsize = 36;
		}
		$print .= "<div style=';font-size:".$fontsize."px' class='filter-word $active' data-tag='".$tag["tag"]."' >";
		
		$print .= $tag["tag"];
		$print .= "<span style='font-size:14; color:rgba(255,255,255,0.3)'>".$tag["c"]."</span>";
		$print .= "</div> ";
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

function getCineasternaStreams($title, $year = null)
{

	global $locale;

	$year = (int)$year;
    $data = array("variables" => array("queryString" => $title));//"query" => $title, "release_year_from" => $year, "release_year_until" => $year);
	$data_string = json_encode($data);

	$library_id = 6;

	//get a list of titles https://backend.cineasterna.com/library/title/get_selected_titles?library_id=6&num_titles=10
	$url = 'https://backend.cineasterna.com/library/title/get_titles?library_id='.$library_id.'&page=1&locale=sv&sort=asc&search='.urlencode($title).'&genres=%5B%5D&languages=%5B%5D&years=%5B'.$year.','.$year.'%5D&ratings=%5B%5D';
	
	// create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	//problem with user agent
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
	curl_setopt($ch, CURLOPT_REFERER, 'https://www.moviequack.com/');

	// $output contains the output string
	$resultc = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);      



	
	$result = json_decode($resultc, true);
	
	$streams = $result["titles"][0];

	$cleanstreamtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $streams["name_en"]));
	$cleanstreamtitleswe = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $streams["name"]));
	
	$cleandbtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $title));

	if (strpos($streams["release_date"], "".$year) > -1 && ($cleandbtitle == $cleanstreamtitle || $cleandbtitle == $cleanstreamtitleswe)) {
		
		$stream = array();
		$stream["monetization_type"] = "free";
		$stream["provider_id"] = 902;
		$stream["retail_price"] = 0;
		$stream["currency"] = "SEK";
		$stream["urls"]["standard_web"] = "https://www.cineasterna.com/sv/library/".$library_id."/title/".$streams["id"];
		$stream["presentation_type"] = "HD";
		$stream["date_provider_id"] = date("Y-m-d", strtotime(time()))."_timestamp";
		
		return $stream;
	} else {
		return false;
	}
}

function cleanTitle($title) {
	return strtolower(preg_replace('/[^A-Za-z0-9\- ]/','', $title));
}
function getSvtPlayStreams($title, $year = null)
{

	global $locale;

	$year = (int)$year;
    $data = array("variables" => array("queryString" => $title));//"query" => $title, "release_year_from" => $year, "release_year_until" => $year);
	$data_string = json_encode($data);

	$url = 'https://api.svt.se/contento/graphql?ua=svtplaywebb-play-render-prod-client&operationName=SearchPage&variables=%7B%22querystring%22%3A%22'.urlencode($title).'%22%7D&extensions=%7B%22persistedQuery%22%3A%7B%22version%22%3A1%2C%22sha256Hash%22%3A%22a57cbf0cb04919ebe71ed93abb5f96a35af02d4f4e22acf9e475c5bf59806607%22%7D%7D';
	echo "svtplayurl:".$url;
	
	// create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	//problem with user agent
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
	curl_setopt($ch, CURLOPT_REFERER, 'https://www.moviequack.com/');

	// $output contains the output string
	$result = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);      



	//print_r($result);
	$result = json_decode($result, true);
	
	foreach ($result["data"]["search"] AS $streams) {
		//$streams = $result["data"]["search"][0];



		$cleanstreamtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $streams["item"]["name"]));

		
		$cleandbtitle = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','', $title));

		$longdescarr = explode("(", $streams["item"]["longDescription"]); //get original title from end of description
		$originaltitle = str_replace(")", "", str_replace("(", "", end($longdescarr)));

		if ($cleandbtitle != $cleanstreamtitle) {
			$streams["item"]["name"] = $originaltitle;
		}

		if (strpos($streams["item"]["shortDescription"], "".$year) > 0) {

			$stream = array();
			$stream = $streams["item"];
			$stream["monetization_type"] = "free";
			$stream["provider_id"] = 901;
			$stream["retail_price"] = 0;
			$stream["currency"] = "SEK";
			$stream["urls"]["standard_web"] = "https://www.svtplay.se".$streams["item"]["urls"]["svtplay"];
			$stream["presentation_type"] = "HD";
			$stream["date_provider_id"] = $streams["item"]["image"]["changed"]."_timestamp";

			$streamsarr[] = $stream;
		}
	}

	return $streamsarr;

}

function getExternalStreams($title, $year = null)
{

	global $locale;

	$year = (int)$year;
    $data = array("query" => $title, "release_year_from" => $year, "release_year_until" => $year);
	$data_string = json_encode($data);

	$url = 'https://apis.justwatch.com/content/titles/'.$locale.'/popular';
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);

	$result = curl_exec($ch);

	
	//$json = file_get_contents($url);

	$result = json_decode($result, true);
	$streams = $result["items"][0]["offers"];
	//echo "Got external streams";
	//print_r($result);
	return $result;
}


function streamsAreOld($movieid) {
	$timeago = time() - (60 * 60 * 24 * 3);

	$sql = "SELECT *
	FROM  ".dbname.".`stream`
	LEFT JOIN ".dbname.".provider
	ON stream.provider = provider.id
	WHERE stream.movieid = '$movieid' AND timestamp > $timeago 
	GROUP BY short
	ORDER BY  `stream`.`timestamp` DESC";

	$streams = db_select($sql);
	return empty($streams);
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
	$timeago = time() - (60 * 60 * 24 * 14);
	$sqlpart = implode("' OR m.id = '", $movies);


	$sql = "SELECT s.*, m.title, m.year, m.id AS movieid
	FROM ".dbname.".movie AS m
	LEFT JOIN ".dbname.".stream AS s
	ON s.movieid = m.id
	WHERE (m.id = '".$sqlpart."')
	AND (s.timestamp < ".$timeago." OR s.timestamp IS NULL)
	GROUP BY m.id";
	echo "<br>".$sql."<br>";
	$streams = db_select($sql);

	$starttime = time();
	echo "<br>line:";
	echo __LINE__;
	echo "<br>starttime:";
	echo time()-$starttime;
	echo "<br>";

	foreach ($streams AS $stream) {
		echo $stream["movieid"]." ".$stream["title"]." ".$stream["year"]."<br>";
		saveStreams($stream);
		echo "<br>line:";
		echo __LINE__;
		echo "<br>starttime:";
		echo time()-$starttime;
		echo "<br>";
	}
	return $sql;
}

function saveStreams($movie) {
	echo "saveStreams";
	$movieid = $movie["movieid"];
	$title = $movie["title"];
	$originaltitle = $movie["originaltitle"];
	$year = $movie["year"];
	
	$streams = getExternalStreams($title, $year);

	$svtplaystream = getSvtPlayStreams($title, $year); //fetch streams by default movie title
	
	if ($svtplaystream) {
		foreach ($svtplaystream AS $s) {
			//print_r($s);
			if (matchMovieName($movie, $s["name"])) {
				$streams["items"][0]["offers"][] = $s;
			}
		}
	}

	$svtplaystreamoriginal = getSvtPlayStreams($originaltitle, $year); //fetch streams by original movie title
	
	if ($svtplaystreamoriginal) {
		foreach ($svtplaystreamoriginal AS $s) {
			//print_r($s);
			if (matchMovieName($movie, $s["name"])) {
				$streams["items"][0]["offers"][] = $s;
			}
		}
	}
	
	$cineasternastream = getCineasternaStreams($title, $year);
	if ($cineasternastream) {
		$streams["items"][0]["offers"][] = $cineasternastream;
	}


	if (matchMovieName($movie, $streams["items"][0]["title"]) && is_array($streams["items"][0]["offers"])) {
		echo "Update with new streams";

		//check if there was an old stream
		$sql = "SELECT provider, type FROM stream WHERE movieid = '$movieid' ";
		$exstrms = db_select($sql);
		$existing_streams = array();
		$existing_providers = array();
		foreach ($exstrms AS $strm) {
			$existing_streams[$strm["provider"]."_".$strm["type"]] = $strm;
			$existing_providers[$strm["provider"]] = $strm["type"];
		}
		/*$query = "DELETE FROM stream WHERE movieid = '$movieid'";
		db_query($query);*/

		print_r($existing_streams);

		$streams = $streams["items"][0]["offers"];

		$timestamp = time();

		$new_streams = array();

		foreach($streams AS $stream) {

			$region = "en_SE";
			$type = $stream["monetization_type"];
			$provider = $stream["provider_id"];
			$price = 0+$stream["retail_price"];
			$currency = $stream["currency"];
			$link = $stream["urls"]["standard_web"];
			$def = $stream["presentation_type"];
			$cutproviderdate = explode("_", $stream["date_provider_id"]);
			$dateproviderid = date("Y-m-d", strtotime($cutproviderdate[0]));

			if (!empty($existing_streams[$provider."_".$type])) { //if stream with same provider already exists and is same type (rent, buy etc) 

				echo "<br>stream already exists so just update $type";
				//DONT SEND NOTIFICATIONS but update timestamp to indicate it has been checked 
				$query = "UPDATE `".dbname."`.`stream` SET `timestamp` = ".$timestamp." WHERE provider = '".$provider."' AND type = '".$type."' AND movieid = '".$movieid."';";
				$ret = db_query($query);

			} else if (empty($existing_providers[$provider]) && ($type == "flatrate" || $type == "free")) { //if stream provider didnt exist before, and new stream type is flatrate or free
				
				echo "<br>provider stream didnt exist, and type is $type";
				$new_streams[$provider] = $type; //make notification for this
				
			} else if (in_array($type, $existing_providers) || !empty($existing_providers[$provider])) { //if this type (buy, rent etc) already exists or provider exists

				//dont do shit
				echo "<br>a stream with $type already exist, so dont notify ".in_array($type, $existing_streams);

			} else { //if stream link does not exist before
				
				echo "<br>maked a notify of p:$provider t:$type";
				$new_streams[$provider] = $type; //make notification for this

			}

			if (strlen($link) > 254) { //if its a long assed glitched url, 
				$link = substr($link, 0, 254); //just cut the fucker by 254 (db column is varchar capped at 255)
			}

			$query = "INSERT INTO `".dbname."`.`stream`
				(`movieid`, `region`, `type`, `provider`, `price`, `currency`, `link`, `def`, `dateproviderid`, `timestamp`)
				VALUES
				('$movieid', '$region', '$type', '$provider', '$price', '$currency', '$link', '$def', '$dateproviderid', '$timestamp')
					";
				echo $query."<br>";
				$ret = db_query($query);
			
		}

		if (!empty($new_streams)) {
			newStreamNotification($movieid, $new_streams);
		}

		$query = "DELETE FROM stream WHERE movieid = '$movieid' AND timestamp < $timestamp";

		db_query($query);
		

	} else {
		//echo "Update empty";
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
		echo $query."<br>";
		db_query($query);

	}

}

function newStreamNotification($movieid, $new_streams) {

	$sql = "SELECT user.email as email
	FROM `tag` 
	LEFT JOIN `user`
	ON `tag`.`user` = `user`.`username`
	WHERE movie = '$movieid' AND tag = 'bookmark'";
	$users = db_select_val($sql, "email");

	if (!empty($users)) {
		

		$sql = "SELECT title FROM movie WHERE id = '".$movieid."'";
		$movie = db_select($sql)[0];

		$sql = "SELECT id, clear FROM provider";
		$provider = db_select_key_val($sql, "id", "clear");

		$arra = array_intersect_key($provider, $new_streams);

		$availableon = implode(", ", $arra);

			// the subject 
			$subject = $movie["title"]." is now available on ".$availableon;
			// the message
			$msg = $movie["title"]." is now available on \n";
			foreach ($new_streams AS $provider_id => $type) {
				$msg .= $provider[$provider_id]." ";
				if ($type == "free") {
					$msg .= "for free";
				} else if ($type == "flatrate") {
					$msg .= "through subscription";
				} else {
					$msg .= "to ".$type;
				}
				$msg .= "\n";
			}
			$msg .= "\nhttps://moviequack.com/movie/".$movieid;
			

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);
			//echo "newstreamnot ";
			//echo $msg;
			$headers = "From: moviequack.com <streams@moviequack.com>";



			foreach ($users as $user) {
				// send email
				mail($user, $subject, $msg, $headers);
			}
		

		return true;
	} else {
		return false;
	}
	

}

function getStreams($movieid) {

	$streams = db_select("SELECT *
FROM  ".dbname.".`stream`
LEFT JOIN ".dbname.".provider
ON stream.provider = provider.id
WHERE stream.movieid = '$movieid'
GROUP BY IF(type='flatrate' || type='free', 1, 0), provider
ORDER BY  `stream`.`price` ASC");

	return $streams;
}

function printStreams($streams) {

	if (!empty($streams) && $streams[0]["link"]) {
		$print = "";//<h3 class='marginbottom'>This title is available for streaming</h3>";
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


/*function addPoster($link) {
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
}*/

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
	$sql = "SELECT `timestamp` FROM  `".dbname."`.`follow` WHERE `follower` = '$follower' AND `follows` = '$follows'";
	$user = db_select($sql);

	return $user[0]["timestamp"];
}

function follow($follows = null, $follower = null) {
	if ($follower == null) {
		$follower = $_SESSION["user"];
	}
	

		if (checkiffollows($follower, $follows)) {
			$query = "DELETE FROM `".dbname."`.`follow` WHERE `follower` = '$follower' AND `follows` = '$follows';";
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

$sql = "SELECT 'tag' AS feedtype, tag.tag AS tag, tag.user AS user1id, user.username AS user1, tag.movie, tag.timestamp, movie.id AS movieid, movie.title AS title, p.filename as poster, movie.backdrop AS backdrop
				FROM `tag`
				LEFT JOIN user
				ON tag.user = user.username
				LEFT JOIN movie
				ON tag.movie = movie.id
				LEFT JOIN poster as p
				ON p.movieid = movie.id AND p.size = 1 
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
				$print .= "";
				$print .= $ficonend;
				$print .= $fconstart;
				$print .= "<div class='red large'>";
				for ($i=0; $i<5; $i++) {
					if ($i<($row["rating"]/2)) {
						$print .= '<i class="material-icons">star</i>';
					} else {
						$print .= '<i style="opacity:0.2" class="material-icons">star</i>';
					}
					
				}
				$print .= "</div> ";
				$print .= "<div class='red large nowrap'>".$row["title"]."</div>";
				$print .= "<div style='display:none'>".date("d-M-Y G:i", $row["timestamp"])."</div>";
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


function getFilteredItems($user, $tag, $options = array()) {

	if (empty($user) || $user == null) {
		$wuser = "user != '1'";
	} else if (is_array($user)) {
		foreach ($user AS $u) {
			if (is_array($u)) {
				$users[] = $u["username"];
			} else {
				$users[] = $u;
			}
		}
		$wuser = implode("' OR tag.user = '", $users);
		$wuser = "tag.user = '$wuser'";
	} else {
		$wuser = $user;
		$wuser = "tag.user = '$wuser'";
	}
	if (is_array($tag)) {
		foreach ($tag AS $t) {
			if (is_array($t)) {

				$tags[] = $t["tag"];
			} else {
				$tags[] = $t;
			}
		}
		$wtag = implode("' OR tag.tag = '", $tags);
	} else {
		$wtag = $tag;
	}


	

	/*$sql = "SELECT * 
	FROM 
	(SELECT tag.movie AS item, movie.*, count(*) as num_users 
	FROM tag 
	LEFT JOIN movie ON movie.id = tag.movie 
	WHERE ($wuser) 
	AND (tag.tag = '$wtag') 
	GROUP BY item ) 
	as customtable
	WHERE num_users >= ".count($users);*/

	$sql = "SELECT * 
	FROM 
	(SELECT tag.movie AS item, movie.title, poster.filename as poster, count(*) as num_users 
	FROM tag 
	LEFT JOIN movie ON movie.id = tag.movie 
	LEFT JOIN poster ON poster.movieid = movie.id AND poster.size = 1
	WHERE ($wuser) 
	AND (tag.tag = '$wtag') 
	GROUP BY item ) 
	as customtable
	ORDER BY num_users DESC";

	//echo $sql;
	
	$items = db_select($sql);
	return $items;

	
}


function removePost($post) {
	$user = $_SESSION["user"];
	$sql = "DELETE FROM `".dbname."`.`post` WHERE `post`.`id` = '".$post."' AND userid = '".$user."'";
	return db_query($sql);
}

function getMimeType($filename)
{
    $mimetype = false;
    if(function_exists('mime_content_type')) {
       $mimetype = mime_content_type($filename);
    }
    return $mimetype;
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


function getFilteredStreamableMovies($user, $tag) {

	

	if (is_array($tag)) {
		foreach ($tag AS $t) {
			if (is_array($t)) {
				$tags[] = $t["tag"];
			} else {
				$tags[] = $t;
			}
		}
		$wtag = implode("', '", $tags);
	} else {
		$wtag = $tag;
	}

	if (empty($user) || $user == null) {
		$wuser = "tag.user != '1'";
	} else if (is_array($user)) {
		foreach ($user AS $u) {
			if (is_array($u)) {
				$users[] = $u["username"];
			} else {
				$users[] = $u;
			}
		}
		$wuser = $users;
	} else {
		$wuser = $user;
		$wuser = "tag.user ='$wuser'";
	}

	if (is_array($wuser)) {
		foreach ($wuser as $wu) {
			$where_sql[] = "SELECT tag.movie AS item 
		FROM tag 
		WHERE (tag.user = '$wu') 
		AND tag.tag IN ('$wtag') 
		GROUP BY item ";
		}
		
	} else {
		$where_sql[] = "SELECT tag.movie AS item 
		FROM tag 
		WHERE (tag.user = '$wuser') 
		AND tag.tag IN ('$wtag') 
		GROUP BY item ";
	}
	

$sql = "SELECT s.movieid, s.type, s.provider, po.filename as poster, p.clear 
FROM ".dbname.".stream AS s 
LEFT JOIN ".dbname.".movie AS m
ON s.movieid = m.id
LEFT JOIN ".dbname.".provider AS p
ON p.id = s.provider
LEFT JOIN ".dbname.".poster AS po
ON po.movieid = m.id AND po.size = 1
WHERE s.movieid IN (".implode(" ) AND s.movieid IN (", $where_sql).") 
GROUP BY s.movieid, provider
";

	//echo $sql;
	
	$items = db_select($sql);
	return $items;

	
}



function getStreamableMovies($moviesarray) {

	$where_sql = implode("', '", $moviesarray);		

	$sql = "SELECT s.movieid, s.type, s.provider, po.filename as poster, p.clear 
	FROM ".dbname.".stream AS s 
	LEFT JOIN ".dbname.".movie AS m
	ON s.movieid = m.id
	LEFT JOIN ".dbname.".provider AS p
	ON p.id = s.provider
	LEFT JOIN ".dbname.".poster AS po
	ON po.movieid = m.id AND po.size = 1
	WHERE s.movieid IN ('".$where_sql."') 
	GROUP BY movieid, provider
	";
	echo $sql;

	$movies = db_select($sql);
	
	return $movies;

}

/*
old 
function getStreamableMovies($moviesarray, $tag = "bookmark") {

	$where_sql = implode("', '", $moviesarray);		

	$sql = "SELECT s.movieid, s.type, s.provider, po.filename as poster, p.clear 
	FROM ".dbname.".stream AS s 
	LEFT JOIN ".dbname.".tag AS t
	ON t.movie = s.movieid AND t.tag = '".$tag."' AND user = '".$_SESSION["user"]."'
	LEFT JOIN ".dbname.".movie AS m
	ON s.movieid = m.id
	LEFT JOIN ".dbname.".provider AS p
	ON p.id = s.provider
	LEFT JOIN ".dbname.".poster AS po
	ON po.movieid = m.id AND po.size = 1
	WHERE s.movieid IN ('".$where_sql."') AND t.tag = '".$tag."'
	GROUP BY movieid, provider
	";
	echo $sql;

	$movies = db_select($sql);
	
	return $movies;

}*/

function getStreamableMoviesComplete($moviesarray, $tag = "bookmark") {

	$where_sql = implode("' OR s.movieid = '", $moviesarray);
	
		$sql = "SELECT * FROM ".dbname.".stream AS s
	LEFT JOIN ".dbname.".tag AS t
	ON t.movie = s.movieid
	LEFT JOIN ".dbname.".movie AS m
	ON s.movieid = m.id
	LEFT JOIN ".dbname.".provider AS p
	ON p.id = s.provider
	WHERE s.movieid = '".$where_sql."' 
	GROUP BY movieid, provider
	";
	//echo $sql;
		$movies = db_select($sql);
		
		return $movies;
	
	}



$timeforpageload = time();


$webpagetitle = "moviequack";
?>
