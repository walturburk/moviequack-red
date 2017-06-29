<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/profile.html");
$layout = new Template("templates/layout.html");

$id = $_REQUEST["id"];
if (!isset($id)) {
  $id = $_SESSION["user"];
}

if ($_SESSION["loggedin"] != true && $_SESSION["user"] == $id) {
	$notloggedin = "<div class='greyback large padding2 centeralign'>You're not signed in! Do you already <a class='red' href='login.php'>have an account</a> or do you want to <a class='red' href='register.php'>make this profile yours</a>?</div>";
} else {
	$notloggedin = "";
}

$profileoptions = "";
if ($id == $_SESSION["user"]) {
  $isownprofile = true;
  if ($_SESSION["loggedin"]) {
    $profileoptions = '<div class="floatright margin0"><a class="button whitebtn" href="logout.php">Log out</a></div>';
  }
} else {
  $userid = $id;
  $alreadyfollows = checkiffollows($_SESSION["user"], $userid);
  if ($alreadyfollows) {
    $activebtn = "activebtn";
  } else {
    $activebtn = "";
  }
  $profileoptions = '<div class="floatright margin0 button whitebtn followbtn '.$activebtn.'" data-followedid="'.$id.'">Follow</div>';
}





$userinfo = db_select("SELECT * FROM  `user` WHERE  `id` =  '".$id."'");
$user = $userinfo[0];

if ($user) {
$username = $user["username"];
} else {
  $username = "";
}
$rawfeed = getFeed($id);
$feed = printFeed($rawfeed);

$content = $t->output();
echo $layout->output();

?>
