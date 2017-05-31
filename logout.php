<?php


include("db_functions.php");
session_start();
$username = $_SESSION["username"];
$_SESSION["loggedin"] = false;
unset($_SESSION["user"]);
unset($_SESSION["username"]);

setcookie ("user", "", time() - 3600);
setcookie ("logintime", "", time() - 3600);
setcookie ("loginhash", "", time() - 3600);
setcookie ("skipmovies", "", time() - 3600);

include("functions.php");

$movieinfo = db_select("SELECT backdrop FROM  `movie` WHERE backdrop != '' ORDER BY RAND() LIMIT 1");
$backdropurl = $basebigbackdropurl.$movieinfo[0]["backdrop"];
$output .= '<div style="background-image:url('.$backdropurl.')" class="fullheight centeralign backgroundimage">';
$output .= "<div class='fullheight darkwindow white paddingtop'>";

$output .= "<div class='content narrow large'>";
	$output .= "<h2>Goodbye for now <span class='red'>".$username."</span></h2>";

	$output .= "<br><a class='button redbtn' href=''>Return</a>";
$output .= "</div>";
$output .= "</div>";
$output .= "</div>";

$content = $output;
$layout = new Template("templates/layout.html");
echo $layout->output();


session_destroy();
?>
