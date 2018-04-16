<?php
include("db_functions.php");
include("functions.php");


$t = new Template("templates/searchpage.html");

$q = $_REQUEST["q"];
$searchterm = $q;

if (strlen($q) > 1) {

$movies = getMovies($q);

$users = getUsers($q);;
if (empty($users)) {
	$userhits = "No matching users found";
} else {
	foreach ($users AS $user) {
		$userhits .= '<div class=""><a class="large" href="/profile/'.$user["username"].'">'.$user["username"].'</a></div>';
	}
}



$searchhit = new Template("templates/searchhit.html");
if (empty($movies)) {
	$searchhits = "";
} else {
	$searchhits = "";
	foreach($movies AS $movie) {
		if ($movie["title"] == $movie["originaltitle"]) {
			$originaltitle = $movie["title"];
			$movietitle = "";
		} else {
			$originaltitle = $movie["originaltitle"];
			$movietitle = $movie["title"];
		}
		$movieid = $movie["id"];

		$movieyear = $movie["year"];
		$posterurl = "https://image.tmdb.org/t/p/w92".$movie["poster"];
		$searchhits .= $searchhit->output();
	}
}

$xsearchhits = "";

$content = "";


$content = $t->output();

} else {

$content = "<div class='content narrow'><h2>pls enter a longer searchterm</h2></div>";

}

$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");
$body = $layout->output();
echo $foundation->output();

?>
