<?php 
//$searchPage = "My Left Foot The Story of Christy Brown 1989 film";
$searchPage = "prometheus 2012 film";

$endPoint = "https://en.wikipedia.org/w/api.php";
$params = [
    "action" => "query",
    "list" => "search",
    "srsearch" => $searchPage,
    "format" => "json"
];

$url = $endPoint . "?" . http_build_query( $params );

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$output = curl_exec( $ch );
curl_close( $ch );

$result = json_decode( $output, true );


if ($result["query"]["search"][0]["pageid"] > 0) {
    echo "Got first hit page id";
} else {
    print_r($result);
    die ("No page id found from search");
}


$params = [
    "action" => "query",
    "format" => "json",
    "titles" => $result["query"]["search"][0]["title"],
    "prop" => "info"
];

/*$params = [
    "action" => "parse",
    "format" => "json",
    "pageid" => $result["query"]["search"][0]["pageid"],
    "section" => "28"
    
];*/

$url = $endPoint . "?" . http_build_query( $params );

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$output = curl_exec( $ch );
curl_close( $ch );

$result2 = json_decode( $output, true );

//echo "https://en.wikipedia.org/wiki/".str_replace(" ", "_", $result["parse"]["title"]);


echo $result2["query"]["pages"][$result["query"]["search"][0]["pageid"]]["fullurl"];
print_r($result2);




?>