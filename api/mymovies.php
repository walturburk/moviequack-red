<?php

include("../db_functions.php");

include("../functions.php");


$user = $_SESSION["user"];

$allusers = getFollowing($user);
$ischeckedu = $_GET["user"];

if (is_array($allusers)) {
	foreach ($allusers AS $u) {

			if(in_array($u, $ischeckedu)) {
				$checked = "checked='checked'";
			} else {
				$checked = "";
			}

		$users .= "<input ".$checked." name='user[]' class='userfilter' id='".$u."' value='".$u."' type='checkbox'><label for='".$u."' class='tabbtn filterbtn'>".$u."</label>";
	}
}

$ischeckedu[] = $user;
$allmovies = getFilteredItems($ischeckedu, "bookmark");


foreach ($allmovies AS $mov) {
  $moviesarray[] = $mov["item"];
  $filteredmovies[$mov["item"]] = $mov;
}


if ($_REQUEST["updateinfo"] == 1) {
  massUpdateStreams($moviesarray);
}

$movies = getStreamableMovies($moviesarray, "bookmark");



foreach ($movies AS $movie) {
  echo "<div style='white-space:nowrap'>";
  print_r($movie);
  echo "</div><br>";
  $streamsites[$movie["type"]][$movie["provider"]]["clear"] = $movie["clear"];
  $streamsites[$movie["type"]][$movie["provider"]]["count"] = 0+$streamsites[$movie["type"]][$movie["provider"]]["count"]+1;
  $streamsites[$movie["type"]][$movie["provider"]]["movie"][] = $movie;

}

$ss["Free"] = $streamsites["free"];
$ss["Subscription"] = $streamsites["flatrate"];
$ss["Rent"] = $streamsites["rent"];
$ss["Buy"] = $streamsites["buy"];

usort($ss["Free"], function($a, $b) {
  return $b['count'] <=> $a['count'];
});

usort($ss["Subscription"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

usort($ss["Rent"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

usort($ss["Buy"], function($a, $b) {
    return $b['count'] <=> $a['count'];
});

echo json_encode($ss);

?>
