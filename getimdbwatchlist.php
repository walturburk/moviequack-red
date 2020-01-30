<?php

$input = "https://www.imdb.com/user/ur4517943/watchlist?ref_=uspf_ci";
$pos = strpos($input, "ur");
$substr = substr($input, $pos+2);
$arr = str_split($substr);
print_r($arr);
$usernumbers = array("u", "r");
foreach ($arr AS $char) {
    if (is_int($char)) {
        $usernumbers[] = $char;
    } else {
        break;
    }
}

$userid = implode("", $usernumbers);
//$userid = "ur4517943";

$url = "https://www.imdb.com/user/".$userid."/watchlist";
echo "url:".$url;

$html = file_get_contents($url);

$exploded = explode('meta property="pageId" content="', $html);
$array = explode('"', $exploded[1]);

$url2 = "https://www.imdb.com/list/".$array[0]."/export";

$csv = file_get_contents($url2);

print_r($csv);

?>