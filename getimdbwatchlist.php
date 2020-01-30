<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$exploded = explode('meta property="pageId" content="', $html);
$array = explode('"', $exploded[1]);

$url2 = "https://www.imdb.com/list/".$array[0]."/export";

echo "<h1>".$url2."</h1>";
print_r($exploded);

print_r(file_get_contents($url2));

?>