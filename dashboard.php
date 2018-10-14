<?php

include("db_functions.php");
include("functions.php");

$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

$body = $layout->output();
$layoutpart = explode("{content}", $foundation->output());

$user = $_SESSION["user"];

$shares = getShareFeed();
$bookmarks = getFilteredItems($user, "bookmark");
$suggested = getTopSuggestedMovies($user);

//printout
echo $layoutpart[0];
include("templates/dashboardpage.php");
echo $layoutpart[1];

?>
