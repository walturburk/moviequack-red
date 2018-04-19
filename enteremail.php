<?php

include("db_functions.php");
include("functions.php");

$email = db_escape($_REQUEST["email"]);
$rawusername = $_REQUEST["username"];
$username = db_escape($rawusername);

$password = db_escape($_REQUEST["password"]);

$return = db_select("SELECT * FROM `user` WHERE `username` = '$username' LIMIT 1");

$output .= '<div class=""></div><div class="fullheight centeralign content">';
$output .= "<div class='fullheight darkwindow white paddingtop'>";


if (!isset($_REQUEST["username"])) {

} else if (strlen($password)<4) {
	$output .= "Your password needs to be four characters or longer";
} else if (strlen($rawusername)<3) {
	$output .= "Your username needs to be at least three characters long";
} else if (strlen($rawusername)>22) {
	$output .= "Your username may be no longer than 22 characters";
} else if ($return[0]["username"] == $username) {
	$output .= "This username is taken!";
} else if ($return[0]["email"] == $email) {
	$output .= "This email is already registered!";
} else if (!(preg_match('/[^a-zA-Z0-9_]/', $rawusername) == 0)) {
	$output .= "Invalid characters in username!";

} else {
	$success = true;
	$password = createHash($password);
	$ip = getUserIp();
	$query = "INSERT INTO `user` (`username`, `password`, `email`, `ip`)
	VALUES ('$username', '$password', '$email', '$ip');
	";
	db_query($query);


	$_SESSION["loggedin"] = true;
	$_SESSION["user"] = $username;


	saveAutoLogin();

	$output .= "<h2>Welcome to moviequack, <span class='red'>$username</span>!</h2>";

	$newURL = baseurl."/buffet.php";
	header('Location: '.$newURL);

}





if ($success != true) {
	$registerpage = new Template("templates/registerpage.html");
	$output .= '<h2 class="darkred padding2">join us</h2>';
	$output .= $registerpage->output();

	$feed = getFeed($_SESSION["user"]);
	if ($feed) {
		$output .= "<div class='centercontent narrow padding window white'><p class='padding2 margintop large'>This activity will be brought to your new account</p>";
		$output .= "".printFeed($feed)."</div>";
	}

}


$output .= "</div>";
$output .= "</div>";
$content = $output;
$foundation = new Template("templates/foundation.html");
$body = $content;
echo $foundation->output();

?>
