<?php

include("db_functions.php");
include("functions.php");

$email = mysql_escape_string($_REQUEST["email"]);
$rawusername = $_REQUEST["username"];
$username = mysql_escape_string($rawusername);
$password = mysql_escape_string($_REQUEST["password"]);
$password2 = mysql_escape_string($_REQUEST["password2"]);

$return = db_select("SELECT * FROM `user` WHERE `username` = '$username' OR `email` = '$email' LIMIT 1");

$movieinfo = db_select("SELECT backdrop FROM  `movie` WHERE backdrop != '' ORDER BY RAND() LIMIT 1");
$backdropurl = $basebigbackdropurl.$movieinfo[0]["backdrop"];
$output .= '<div style="background-image:url('.$backdropurl.')" class="fullheight centeralign backgroundimage">';


$output .= "<div class='fullheight darkwindow white paddingtop'>";

if (!isset($_REQUEST["username"])) {

} else if (strlen($password)<4) {
	$output .= "Your password needs to be four characters or longer";
} else if (strlen($rawusername)<3) {
	$output .= "Your username needs to be at least three characters long";
} else if (strlen($rawusername)>22) {
	$output .= "Your username may be no longer than 22 characters";
} else if ($password != $password2) {
	$output .= "You can only have one password!";
} else if ($return[0]["username"] == $username) {
	$output .= "This username is taken!";
} else if ($return[0]["email"] == $email) {
	$output .= "This email is already registered!";
} else if (!(preg_match('/[^a-zA-Z0-9_]/', $rawusername) == 0)) {
	$output .= "Invalid characters in username!";

} else {
	$success = true;
	$id = newId("u");
	$password = createHash($password);
	$ip = getUserIp();
	$query = "INSERT INTO `user` (`id`, `username`, `password`, `email`, `ip`)
	VALUES ('$id', '$username', '$password', '$email', '$ip');
	";
	db_query($query);
	$recommendlist = newId("l");
	$watchlist = newId("l");
	$query2 = "INSERT INTO `wilnkfzr_moviequack`.`list` (`user`, `listid`, `name`, `permit`) VALUES ('$id', '$recommendlist', 'Recommend', '');
	";
	$query3 = "INSERT INTO `wilnkfzr_moviequack`.`list` (`user`, `listid`, `name`, `permit`) VALUES ('$id', '$watchlist', 'Watchlist', '');
	";

	db_query($query3);
	db_query($query2);

	$sql = "UPDATE `post` SET `post`.`userid` = '".$id."' WHERE `post`.`userid` = '".$_SESSION['user']."' ";
	db_query($sql);
	$sql = "UPDATE `vote` SET `vote`.`user` = '".$id."' WHERE `vote`.`user` = '".$_SESSION['user']."' ";
	db_query($sql);
	$sql = "UPDATE `ratemovie` SET `ratemovie`.`user` = '".$id."' WHERE `ratemovie`.`user` = '".$_SESSION['user']."' ";
	db_query($sql);

	$_SESSION["loggedin"] = true;
	$_SESSION["user"] = $id;
	$_SESSION["username"] = $username;

	saveAutoLogin();

	$output .= "<h2>Welcome to moviequack, $username!</h2>";

}





if ($success != true) {
	$registerpage = new Template("templates/registerpage.html");
	$output .= '<h2 class="darkred padding2">join us</h2>';
	$output .= $registerpage->output();

	$feed = getFeed($_SESSION["user"]);
	if ($feed) {
		$output .= "<p class='padding2 margintop large'>This activity will be brought to your new account</p>";
		$output .= "<div class='centercontent narrow padding window white'>".printFeed($feed)."</div>";
	}

}


$output .= "</div>";
$output .= "</div>";
$content = $output;
$layout = new Template("templates/layout.html");
echo $layout->output();

?>
