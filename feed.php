<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/feed.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");


$id = $_REQUEST["id"];
if (!isset($id)) {
  $id = $_SESSION["user"];
}

$userid = $id;


if ($alreadyfollows) {
  $activebtn = "activebtn";
} else {
  $activebtn = "";
}

$userinfo = db_select("SELECT * FROM  `user` WHERE  `username` =  '".$id."'");
$user = $userinfo[0];

$username = $user["username"];

$follows = getFollowing($id);
$rawfeed = getFeed($follows);
$feed = printFeed($rawfeed);

$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>
