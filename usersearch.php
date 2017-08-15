<?php

include("db_functions.php");
include("functions.php");

$q = $_REQUEST["q"];
$users = getUsersByLetter($q);


	$output = json_encode($users);

	echo $output;

?>
