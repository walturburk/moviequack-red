<?php
//ini_set('display_errors', 1);
include("db_functions.php");
include("functions.php");
$success = false;

if (isset($_REQUEST["username"]) && isset($_REQUEST["password"])) {
	$username = mysqli_escape_string(db_connect(), $_REQUEST["username"]);
	$password = mysqli_escape_string(db_connect(), $_REQUEST["password"]);
}

$output .= "<div class='content narrow white centeralign'>";
$output .= "<div class='white'>";
if (isset($_REQUEST["username"]) && $_REQUEST["username"] != "") {

$return = db_select("SELECT * FROM `user` WHERE username = '$username';");



if ($return && hashOk($password, $return[0]["password"])) {

	newSession($return[0]["username"]);
	$_SESSION["loggedin"] = true;
	$output .= "<h2>Welcome <span class='red'>".$return[0]["username"]."</span>!</h2>";
	$success = true;
	saveAutoLogin();
	header('Location: /');
} else if ($return) {
	$output .= "Wrong password!";
} else {
	$output .= "User doesn't exist!";
}


$output .= "</div>";


}
if ($success != true) {

	$loginpage = new Template("templates/loginpage.html");



	$output .= "<h1 class='padding2'>sign in</h1>";
	$output .= $loginpage->output();


}

$output .= "</div>";
$output .= "</div>";

unset($_REQUEST["username"]);
unset($_REQUEST["password"]);

$content = $output;
$foundation = new Template("templates/foundation.html");
$body = $content;
echo $foundation->output();
?>
