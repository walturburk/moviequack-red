<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/feed.html");
$layout = new Template("templates/layout.html");

if ($_SESSION["loggedin"] != true) {
	header("Location: /register.php");
}

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

$userinfo = db_select("SELECT * FROM  `user` WHERE  `id` =  '".$id."'");
$user = $userinfo[0];

$username = $user["username"];

$follows = getFollowing($id);
$rawfeed = getFeed($follows);
$feed = printFeed($rawfeed);

$content = $t->output();
echo $layout->output();

?>
