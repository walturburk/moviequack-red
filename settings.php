<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/settings.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$user = $_SESSION["user"];
$invitecode = getInviteCode($user);
if ($invitecode) {
    $inviteurl = "/join/".$invitecode;
    $inviteurltext = "moviequack.com".$inviteurl;
} else {
    $inviteurl = "#";
    $inviteurltext = "Invite link: You must rate a movie to get an invite link";
}

$officialurl = officialurl;

$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>
