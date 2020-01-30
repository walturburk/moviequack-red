<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$exploded = explode('meta property="pageId" content="', $html);
$array = explode('"', $exploded[1]);

$url2 = "https://www.imdb.com/list/".$array[0]."/export";

$csv = file_get_contents($url2);

print_r($csv);

?>