<?php

include("db_functions.php");
include("functions.php");

$user = $_SESSION["user"];

$friends = getFollowing($user);

if (is_array($friends)) {
	$allusers = array("username" => $user) + $friends;
} else {
	$allusers[] = array("username" => $user);
}

$ischeckedu = $_GET["user"];
$ischeckedt = $_GET["tag"];

if (!isset($_GET["user"])) {
	$ischeckedu[] = $_SESSION["user"];
}

$alltags = getAllTagsByUser($selectedusers);



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
		if(in_array($u["username"], $ischeckedu)) {
			$checked = "checked='checked'";
		} else {
			$checked = "";
		}
		$users .= "<label class='button filterbtn'><input ".$checked." name='user[]' class='userfilter' value='".$u["username"]."' type='checkbox'>".$u["username"]."</label>";
	}
}

foreach ($alltags AS $tag) {
	if(in_array($tag["tag"], $ischeckedt)) {
		$checked = "checked='checked'";
	} else {
		$checked = "";
	}
	$tags .= "<label class='button filterbtn'><input ".$checked." name='tag[]' class='tagfilter' value='".$tag["tag"]."' type='checkbox'>".$tag["tag"]."</label>";
}


$items = getFilteredItems($ischeckedu, $ischeckedt);
print_r($items);
foreach ($items AS $item) {
	$listitems .= "<a href='/movie.php?id=".$item["movie"]."'>".$item["title"]."</a>";
}

$t = new Template("templates/list.html");
$content = $t->output();
$layout = new Template("templates/layout.html");
echo $layout->output();

?>
