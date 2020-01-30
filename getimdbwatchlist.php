<?php


$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$dom = new DOMDocument();

$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$divContent = $xpath->query('//div[class="export"]');

echo $divContent;
print_r($divContent);

?>