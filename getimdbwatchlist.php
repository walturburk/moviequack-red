<?php

//echo phpinfo();

$url = "https://www.imdb.com/user/ur4517943/watchlist";

$html = file_get_contents($url);

$metatags = get_meta_tags($url);

$doc = new DOMDocument();
@$doc->loadHTML($html);

$searchNodes = $doc->getElementsByTagName( "meta" );

foreach( $searchNodes as $searchNode )
{
    $valueID = $searchNode->getAttribute('pageId');
    print_r($searchNode);

}
var_dump($metatags);
var_dump($valueID);


?>