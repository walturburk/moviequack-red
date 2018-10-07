<?php

include("db_functions.php");
include("functions.php");


$user = $_SESSION["user"];

$registerbtnclass = "redbtn";
$loginlinkclass = "red";


	$toplogin = "<a href='/user/".$_SESSION["user"]."''>".$_SESSION["user"]."</a>";
	$logoutbutton = '<li><a href="/logout">logout</a></li>';


//$topsearchbarstyle = "display:none";
$mscclass = "white";

$joinuscontent = "";



$follows = getFollowing($user);
$rawfeed = getFeed($follows);
$printedfeed = printFeed($rawfeed);

$rawfeed = getPostsFeed();
$printedfeed .= printMessage($rawfeed);


$t = new Template("templates/mainfeedpage.html");
$content .= $t->output();
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");
$foundation = new Template("templates/foundation.html");
$body = $layout->output();
echo $foundation->output();

?>