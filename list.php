<?php

include("db_functions.php");
include("functions.php");

$user = $_SESSION["user"];

$friends = getFollowing($user);
$tagsbuttons = "";

if (is_array($friends)) {
	$allusers = $friends;
	array_unshift($allusers, $user);
} else {
	$allusers[] = $user;
}

$ischeckedu = $_GET["user"];
$ischeckedt = $_GET["tag"];

if (!isset($_GET["user"])) {

}

$alltags = getAllTagsByUser($ischeckedu);



/*if(in_array($user, $ischeckedu)) {
	$checked = "checked='checked'";
} else {
	$checked = "";
}
$users = "<label class='button filterbtn userfilter'><input ".$checked." name='user[]' class='userfilter' value='".$user."' type='checkbox'>".$user."</label>";
*/
//$users .= "<label class='button filterbtn userfilter'><input name='user[]' class='userfilter' value='friends' type='checkbox'>Friends</label>";
//$users .= "<label class='button filterbtn userfilter'><input name='user[]' class='userfilter' value='everyone' type='checkbox'>Everyone</label>";

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



foreach ($alltags AS $tag) {
	if(in_array($tag["tag"], $ischeckedt)) {
		$checked = "checked='checked'";
	} else {
		$checked = "";
	}
	$tagsbuttons .= "<input ".$checked." name='tag[]' class='tagfilter' id='".$tag["tag"]."' value='".$tag["tag"]."' type='checkbox'><label for='".$tag["tag"]."' class='tabbtn filterbtn'>".$tag["tag"]."</label>";
}


$items = getFilteredItems($ischeckedu, $ischeckedt);






if (empty($items) && $selectedlist != "") {
  $listitems = "<div style='' class='content narrow centeralign large'>This list is empty!</div>";
} else if ($_REQUEST["listmode"] == "compact") {
	$listitems .= "<ul class='clear floatleft sortablelist'>";
	 foreach ($items AS $item) {
		 $listitems .= "<li class='sortableli' style=''><a href='/movie/".$item["item"]."' class='handle absolutecenter'><img alt='".$item["title"]."' class='handle' src='".basethumburl.$item["poster"]."'>";
		 $listitems .= "<div class='xinfo'>".$item["title"]."</div></a></li>";
	 }
	 $listitems .= "</ul><div class='clear'></div>";
 } else {
  $listitems .= '<div class="relative"><div class="responsive_table"><table class="titlelist" id="listitems">';
  foreach($items AS $item) {
    $listitems .= "<tr class='titleitem addremparent' id='".$item["item"]."'>";

		$listitems .= "<td class='fixedcell'>";
	$listitems .= "<a href='/movie/".$item["item"]."' class=''><img alt='".$item["title"]."' class='handle' src='".basethumburl.$item["poster"]."'></a>";
$listitems .= "</td>";
$listitems .= "<td style='height:105px;min-width:70px;'> </td>";

/*$listitems .= "<td class='large padding0'>";
$listitems .= "<span data-list='".$selectedlist."' data-item='".$item["item"]."' class='floatright btn activebtn removefromlist'><i style='font-size:30px' class='material-icons'>delete</i></span>";
$listitems .= "</td>";*/

$listitems .= "<td class='padding0'>";
$listitems .= "<a href='/movie/".$item["item"]."'>".$item["title"]."</a>";
$listitems .= "</td>";

	$listitems .= "<td style='' id='movierating' class='ratemovie darkrating'>";
	$rating = getMovieRating($item["item"]);
	$urate = getUsersMovieRating($item["item"], $userid);
	$listitems .= printMovieRating($item["item"], $rating, $urate);
	$listitems .= "</td>";



		//$listitems .= "</div>";
		/*$listitems .= "<td class='userquack'>";
			if ($item["message"] != "") {
	    $listitems .= "<span class='floatleft middle'>".getEmoji($item["emoji"])."</span>";
			$listitems .= "<a class='bubble padding smalltext' href='/movie/".$item["item"]."'>";

			$listitems .= $item["message"];

			$listitems .= "</a>";
			} else {
				$listitems .= "";
			}
			$listitems .= "<br></td>";*/

			$listitems .= "<td>";
			$listitems .= '<div id="streams" class="streams darkstreams">';
			$listitems .= printStreams($item["item"]);
			$listitems .= "</div>";
			$listitems .= "</td>";



			$listitems .= "<td class=''><div class=''>";
			$tags = getTagsByUser($item["item"], $userid);
			if ($tags) {
				$listitems .= printTags($tags, $item["item"]);
			} else {
				$listitems .= "";
			}
			$listitems .= "</div></td>";

		//$listitems .= "</div>";
    $listitems .= "</tr>";
  }
  $listitems .= "</table></div></div>";
}








$t = new Template("templates/list.html");
$content = $t->output();
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");
$body = $layout->output();
echo $foundation->output();

?>
