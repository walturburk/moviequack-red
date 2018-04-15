<?php


include("db_functions.php");
session_start();
$username = $_SESSION["user"];
$_SESSION["loggedin"] = false;
unset($_SESSION["user"]);




setcookie ("user", null, -1, '/');
setcookie ("logintime", null, -1, '/');
setcookie ("loginhash", null, -1, '/');
setcookie ("skipmovies", null, -1, '/');


include("functions.php");

$movieinfo = db_select("SELECT backdrop FROM  `movie` WHERE backdrop != '' ORDER BY RAND() LIMIT 1");
$backdropurl = $basebigbackdropurl.$movieinfo[0]["backdrop"];
$output .= '<div style="background-image:url('.$backdropurl.')" class="fullheight centeralign backgroundimage">';
$output .= "<div class='fullheight darkwindow white paddingtop'>";

$output .= "<div class='content narrow large'>";
	$output .= "<h2>Goodbye for now <span class='red'>".$username."</span></h2>";

	$output .= "<br><a class='button redbtn' href='/'>Return</a>";
$output .= "</div>";
$output .= "</div>";
$output .= "</div>";

$content = $output;
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");
$body = $layout->output();
echo $foundation->output();


session_destroy();
?>
