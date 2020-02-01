<?php

include("db_functions.php");
include("functions.php");

$email = db_escape($_REQUEST["email"]);

$return = db_select("SELECT * FROM `user` WHERE `email` = '$email' LIMIT 1");

$output .= '<div class="padding"></div><div class="fullheight centeralign">';


$output .= "<div class='fullheight white paddingtop'>";

if ($return[0]["email"] == $email && isset($_REQUEST["email"])) {
	$output .= "This email is already registered! Try to log in or reset your password.";
} else if (isset($_REQUEST["email"]) && isset($_POST["submit"])) {
	$success = true;

  $sql = "UPDATE `user` SET `user`.`email` = '".$email."' WHERE `user`.`username` = '".$_SESSION['user']."' ";
	db_query($sql);


	//header('Location: /moveactivity.php');

}





if ($success != true) {
	$registerpage = new Template("templates/enteremail.html");
	$output .= '<h2 class="padding2">enter your email</h2>';
	$output .= $registerpage->output();
}


$output .= "</div>";
$output .= "</div>";
$content = $output;

$foundation = new Template("templates/foundation.html");
$body = $output;
echo $foundation->output();

?>
