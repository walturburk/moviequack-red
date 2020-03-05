<?php 

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
    "action" => "parse",
    "pageid" => $result["query"]["search"][0]["pageid"],
    "prop" => "text",
    "formatversion" => "2",
    "format" => "json"
];

$url = $endPoint . "?" . http_build_query( $params );

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$output = curl_exec( $ch );
curl_close( $ch );

$result = json_decode( $output, true );

print_r($result);

?>