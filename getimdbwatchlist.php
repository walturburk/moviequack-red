<?php

echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$dom = new DOMDocument();
$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$result = '';
foreach($xpath->evaluate('//metaiv[@property="pageId"]/node()') as $childNode) {
  $result .= $dom->saveHtml($childNode);
}
var_dump($result);


?>