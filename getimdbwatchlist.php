<?php

include("db_functions.php");
include("functions.php");

//$input = "https://www.imdb.com/user/ur4517943/watchlist?ref_=uspf_ci";
$input = $_REQUEST["imdbusr"];
$pos = strpos($input, "ur"); //get pos of imdb username prefix
$substr = substr($input, $pos+2); //get all chars after username prefix "ur"
$arr = str_split($substr); //split those into and array of characters

$usernumbers = array("u", "r"); //declare array that starts with imdbs standard username prefix
foreach ($arr AS $char) { //loop through those characters
    if ($char >= 0 && $char <= 9) { //if it is a number
        $usernumbers[] = $char; //put it in the array
    } else {
        $arr = array();
    }
}

$userid = implode("", $usernumbers); //glue together array to get userid as a string
//$userid = "ur4517943";

$url = "https://www.imdb.com/user/".$userid."/watchlist"; //insert userid into imdb watchlist array

$html = file_get_contents($url); //get all html from watchlist url

$exploded = explode('meta property="pageId" content="', $html); //find meta-tag with property="pageId" which holds attribute "content" with the actual listid of the watchlist
$array = explode('"', $exploded[1]); //split again by end of attr content closing citation " to get the containing value
$listid = $array[0];

$url2 = "https://www.imdb.com/list/".$listid."/export"; //this is the url for csv export of imdb lists by listid


if ($listid) {

    $csv = file_get_contents($url2); //get file contents of the csv

    $lines = explode( "\n", $csv ); //split csv by linebreaks
    $headers = str_getcsv( array_shift( $lines ) ); //remove and store first line that contains header names for the data in new header-array
    $data = array();
    foreach ( $lines as $line ) {

        $row = array();

        foreach ( str_getcsv( $line ) as $key => $field ) 
            $row[ $headers[ $key ] ] = $field; //use header-array values as keys for the rest of the data in csv

        $row = array_filter( $row );

        $data[] = $row;

    }

    if (!empty($data)) {

        foreach ($data as $key => $val) {
            echo $val["Const"];
            addMovie($val["Const"]);
        }

    } else {
        echo "List is empty";
    }

} else {
    echo "Error<br>";
    echo "user: ".$userid;
    echo "listid: ".$url2;
}



?>