<?php

include("db_functions.php");
include("functions.php");

/*
$latestmessage = printSpecMessage(1);
$trendingmessage = printSpecMessage(2);
$topmessage = printSpecMessage(3);
*/

$registerbtnclass = "redbtn";
$loginlinkclass = "red";

if ($_SESSION["loggedin"] == true) {
	$toplogin = "<a href='profile.php?id=".$_SESSION["user"]."''>".$_SESSION["username"]."</a>";
	$logoutbutton = '<li><a href="logout.php">logout</a></li>';
} else {
	$logoutbutton = "";
	$l = new Template("templates/loginpage.html");
	$toplogin = '<span class="expandparent">
	<span class="toprowbutton expandbtn">sign in</span>
	<div class="expandchild fixed pop whitecard centeralign" style="display:none" id="register">
	<div id="login">
		<h2 class="darkred marginbottom paddingbottom">sign in</h2>
    ';
	$toplogin .= $l->output();
	$toplogin .= '</div></div></span><a href="register.php" class="toprowbutton expandbtn">register</a>';
}

$movieinfo = db_select("SELECT backdrop FROM  `movie` WHERE backdrop != '' ORDER BY RAND() LIMIT 1");
$backdropurl = $basebigbackdropurl.$movieinfo[0]["backdrop"];
$mscstyle = "background-image:url(".$backdropurl.");";
//$topsearchbarstyle = "display:none";
$mscclass = "white";

$joinuscontent = "";

if (!$_SESSION["loggedin"] && false) {


//$output .= "<div class='relative padding2'>";

  $movieinfo = db_select("SELECT backdrop FROM  `movie` WHERE backdrop != '' AND backdrop != '".$movieinfo[0]["backdrop"]."' ORDER BY RAND() LIMIT 1");
  $backdropurl2 = $basebigbackdropurl.$movieinfo[0]["backdrop"];
  $output .= '<div style="" class="backgroundimage white">';
  $output .= '<div class="padding3 gradientredback">';


$registerpage = new Template("templates/registerpage.html");



$output .= '<div class="half centeralign mobilehide">';
$output .= '<div class=""><img src="/img/moviequack.png"></div>';
$output .= '';
$output .= '</div>';
$output .= '<div class="half white centeralign">';
$output .= '<h2 class="marginbottom paddingbottom">join moviequack</h2>';
$output .= $registerpage->output();
$output .= "</div>";
$output .= "<div class='clear'></div>";


$output .= "</div></div>";
//$output .= '<div class="content padding2">';

//$output .= '</div>';
//$output .= "</div>";
$joinuscontent .= $output;

$toplogin = "";

}

$rawfeed = getPostsFeed();
$printedfeed = printMessage($rawfeed);
//$randommovie = printRandomMovie();

$t = new Template("templates/indexpage.html");
$content .= $t->output();
$layout = new Template("templates/layout.html");
echo $layout->output();

?>
