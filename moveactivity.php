<?php

include("db_functions.php");
include("functions.php");


if ($_POST["submit"]) {

  $username = $_SESSION["user"];

  $sql = "UPDATE `post` SET `post`.`userid` = '".$username."' WHERE `post`.`userid` = '".$_SESSION['prevuser']."' ";
  db_query($sql);
  $sql = "UPDATE `vote` SET `vote`.`user` = '".$username."' WHERE `vote`.`user` = '".$_SESSION['prevuser']."' ";
  db_query($sql);
  $sql = "UPDATE `ratemovie` SET `ratemovie`.`user` = '".$username."' WHERE `ratemovie`.`user` = '".$_SESSION['prevuser']."' ";
  db_query($sql);
  $sql = "UPDATE `tag` SET `tag`.`user` = '".$username."' WHERE `tag`.`user` = '".$_SESSION['prevuser']."' ";
  db_query($sql);

}

$feed = getFeed($_SESSION["prevuser"]);

if ($feed) {
  $output = "<div class='centercontent narrow padding centeralign'>";
  $output .= "<h3>Seems like you have used this site before</h3><br><p>Would you like to bring the activity below to you new account?</p><br>";
  $output .= "<form action='' method='post'>";
  $output .= "<input type='hidden' name='submit' value='submit'/>";
  $output .= "<input class='button' value='Yes, bring activity' type='submit' /><br><br>";
  $output .= "</form>";
  $output .= "<a href='/buffet' class='link'>No, start fresh</a>";
  $output .= "</div>";
	$output .= "<div class='centercontent narrow padding white'>";
	$output .= printFeed($feed);
  $output .= "</div>";
} else {

  $url = "/buffet.php";
  header("Location:".$url);

}

$content = $output;

$foundation = new Template("templates/foundation.html");
$body = $output;
echo $foundation->output();

?>
