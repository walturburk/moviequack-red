<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$exploded = explode('meta property="pageId" content="', $html);
$array = explode('"', $exploded[1]);
echo "<h1>".$array[0]."</h1>";
print_r($exploded);

?>