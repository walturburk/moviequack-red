<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$classname = "pageId";
$dom = new DOMDocument;
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$results = $xpath->query("//*[@property='" . $classname . "']");

if ($results->length > 0) {
    echo $review = $results->item(0)->nodeValue;
}


?>