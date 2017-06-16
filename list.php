<?php

include("db_functions.php");
include("functions.php");

$userid = $_SESSION["user"];
$username = $_SESSION["username"];
$selectedlist = $_REQUEST["id"];

if ($_SESSION["loggedin"] != true) {
	header("Location: register.php");
}

setcookie("selectedlist", $selectedlist, time() + (86400 * 30), "/");

$lists = getLists($userid);

if (!isset($selectedlist)) {
  if (isset($_COOKIE["selectedlist"])) {
    $selectedlist = $_COOKIE["selectedlist"];
  } else {
    $selectedlist = $lists[0]["listid"];
  }
}

$listselect = printListSelect($selectedlist);

$listname = "You have no lists";





$items = getItemsFromList($selectedlist);

$listname = $items[0]["name"];

if ($listname == "") {
	foreach($lists AS $list) {
	  if ($list["listid"] == $selectedlist) {
	    $listname = $list["name"];
	  }
	}
}

$userid = $lists[0]["user"];
if (empty($items) && $selectedlist != "") {
  $listitems = "<div style='' class='content narrow centeralign large'>This list is empty!</div>";
} else if ($_REQUEST["listmode"] == "compact") {
	$listitems .= "<ul class='clear floatleft sortablelist'>";
	 foreach ($items AS $item) {
		 $listitems .= "<li class='sortableli' style=''><a href='movie.php?id=".$item["item"]."' class='handle absolutecenter'><img alt='".$item["title"]."' class='handle' src='".basethumburl.$item["poster"]."'>";
		 $listitems .= "<div class='xinfo'>".$item["title"]."</div></a></li>";
	 }
	 $listitems .= "</ul><div class='clear'></div>";
 } else {
  $listitems .= '<div class="relative"><div class="responsive_table"><table class="titlelist" id="listitems">';
  foreach($items AS $item) {
    $listitems .= "<tr class='titleitem addremparent' id='".$item["item"]."'>";

		$listitems .= "<td class='fixedcell'>";
	$listitems .= "<a href='movie.php?id=".$item["item"]."' class=''><img alt='".$item["title"]."' class='handle' src='".basethumburl.$item["poster"]."'></a>";
$listitems .= "</td>";
$listitems .= "<td style='height:105px;min-width:70px;'> </td>";

$listitems .= "<td class='large padding0'>";
$listitems .= "<span data-list='".$selectedlist."' data-item='".$item["item"]."' class='floatright btn activebtn removefromlist'><i style='font-size:30px' class='material-icons'>delete</i></span>";
$listitems .= "</td>";

$listitems .= "<td class='padding0'>";
$listitems .= "<a href='movie.php?id=".$item["item"]."'>".$item["title"]."</a>";
$listitems .= "</td>";

	$listitems .= "<td style='' id='movierating' class='ratemovie darkrating'>";
	$rating = getMovieRating($item["item"]);
	$urate = getUsersMovieRating($item["item"], $userid);
	$listitems .= printMovieRating($item["item"], $rating, $urate);
	$listitems .= "</td>";



		//$listitems .= "</div>";
		$listitems .= "<td class='userquack'>";
			if ($item["message"] != "") {
	    $listitems .= "<span class='floatleft middle'>".getEmoji($item["emoji"])."</span>";
			$listitems .= "<a class='bubble padding smalltext' href='movie.php?id=".$item["item"]."'>";
			//$listitems .= "<div class='bubble padding'>";
			$listitems .= $item["message"];
			//$listitems .= "</div>";
			$listitems .= "</a>";
			} else {
				$listitems .= "";
			}
			$listitems .= "<br></td>";

			$listitems .= "<td>";
			$listitems .= '<div id="streams" class="streams darkstreams">';
			$listitems .= printStreams($item["item"]);
			$listitems .= "</div>";
			$listitems .= "</td>";



			$listitems .= "<td class=''><div class=''>";
			$tags = getTagsByUser($item["item"], $userid);
			if ($tags) {
				$listitems .= printTags($tags);
			} else {
				$listitems .= "";
			}
			$listitems .= "</div></td>";

		//$listitems .= "</div>";
    $listitems .= "</tr>";
  }
  $listitems .= "</table></div></div>";
}

//if ($_SESSION["loggedin"]) {
$loggedinlistoptions = '<div class="expandparent inblock marginright">
  '.$listselect.'
<div class="expandbtn button redbtn removelist"><i class="material-icons">delete</i></div>
<div class="expandchild fixed">
<div class="whitecard">Are you sure you want to delete '.$listname.'?<br>
  <div class="margintop button redbtn confirmdeletelist">Yes</div>
</div>
</div>
</div>

<div class="inblock">
<input id="newlistname" class="input" type="text" placeholder="Create new list">
<div class="button redbtn createnewlist"><i class="material-icons">add</i></div>
</div>';
//} else {
//  $loggedinlistoptions = "<p class='large'>You must be logged in to manage lists</p>";
//}


$t = new Template("templates/list.html");
$content = $t->output();
$layout = new Template("templates/layout.html");
echo $layout->output();

?>
