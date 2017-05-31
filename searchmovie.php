<?php

include("db_functions.php");
include("functions.php");

$q = $_REQUEST["q"];
$movie = getMovies($q);


	$output = json_encode($movie);

	echo $output;

?> 
