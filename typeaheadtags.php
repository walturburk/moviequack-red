<?php

include("db_functions.php");
include("functions.php");

$q = $_REQUEST["q"];
$tags = getTagsByLetter($q);


	$output = json_encode($tags);

	echo $output;

?>
