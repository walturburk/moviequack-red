<?php

include("db_functions.php");
include("functions.php");

$user = $_SESSION["user"];

$friends = getFollowing($user);
$alltags = getAllTagsByUser($selectedusers);

$selecteduser = $_REQUEST[""];


$t = new Template("templates/list.html");
$content = $t->output();
$layout = new Template("templates/layout.html");
echo $layout->output();

?>
