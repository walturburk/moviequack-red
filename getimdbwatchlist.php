<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$classname = "pageId";
$dom = new DOMDocument;
$dom->loadHTML($html);
var_dump($dom);


?>