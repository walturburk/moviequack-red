<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$metatags = get_meta_tags($url);

$doc = new DOMDocument();
@$doc->loadHTML($html);
$nodes = $doc->getElementsByTagName('meta');
var_dump($metatags);
var_dump($nodes);


?>