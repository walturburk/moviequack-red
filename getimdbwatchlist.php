<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$metatags = get_meta_tags($url);
var_dump($metatags);


?>