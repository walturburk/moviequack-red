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
	$notloggedin = "<div class='urgentmessage'>Urgent message: You're not signed in! <br><a class='red' href='register.php'>Join us immediately</a> <br>or if you already have an account <a class='red' href='login.php'>sign in</a>.</div>";
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





$userinfo = db_select("SELECT * FROM  `user` WHERE  `username` =  '".$id."'");
$user = $userinfo[0];

if ($user) {
  $username = $user["username"];
  if (isVisitor($username)) {
    $username = "visitor";
  }
} else {
  $username = "";
}
$rawfeed = getFeed($id);
$feed = printFeed($rawfeed);

$content = $t->output();
echo $layout->output();

?>
