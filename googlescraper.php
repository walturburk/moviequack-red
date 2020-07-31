<?php

include("db_functions.php");
include("functions.php");

$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$movieid = $_REQUEST["movieid"];
$movieinfo = db_select("SELECT * FROM  `movie` WHERE  `id` =  '".$movieid."' LIMIT 1");
$movie = $movieinfo[0];

$searchstring = $movie["title"]." ".$movie["year"];
$searchstring = str_replace(" ", "+", $searchstring);

$url = "https://www.google.com/search?hl=sv&q=netflix+".$searchstring;
$googlepage = file_get_contents($url);

$sarray = explode("ZINbbc xpd O9g5cc uUPGi", $googlepage);
foreach ($sarray as $hit) {
    if (strpos($hit, "netflix.com/se/title") || strpos($hit, "netflix.com/title")) {
        echo $hit;
    }
}

$body = $layout->output();
//echo $foundation->output();

?>