<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/moviepage.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");


if (isset($_REQUEST["removetags"])) {
    
    filterWords(getFilteredWords());
}

if (isset($_REQUEST["getalltags"])) {

$alltags = getAllTags();
$content = "<a href='?removetags'>Remove tags</a><br>".printTagsToFilter($alltags);

}
$body = $layout->output();
echo $foundation->output();




//get all plots

if (isset($_REQUEST["getallplots"])) {

    $movieinfo = db_select("SELECT m.id, m.title, m.year FROM  `movie` as m 
    LEFT JOIN link AS l
    ON m.id = l.movieid
    WHERE url IS NULL");
    
    $words = getFilteredWords();

    foreach ($movieinfo as $movie) {
        $movietitle = $movie["title"];
        $year = $movie["year"];
        $movieid = $movie["id"];

        $page = getWikipediaPage($movietitle, $year);
        $link = getWikipediaLink($page);
        addLinks($link, $movieid);
        $sections = getWikipediaSections($page);

        $sectionid = 1;

        foreach ($sections AS $id => $section) {
            if ($section["line"] == "Plot" || $section["line"] == "Premise") {
                //echo "SECTIONID:".print_r($section);
                $sectionid = $section["index"];
            } else {
                //echo $section["line"];
            }
        }

        $section_text = getWikipediaTextFromSection($page, $sectionid);
        $splittedtext = splitWikitext($section_text);
        
        $tagstoadd = array_diff($splittedtext, $words); //filters out all $words from the wikipedia words $splittedtext
        addTag($movieid, $tagstoadd, "wikiplot");
    }
/*


*/
}

//get all plots

if (isset($_REQUEST["getallposters"])) {

    $movieinfo = db_select("SELECT m.id, m.title, m.year FROM  `movie` as m 
    LEFT JOIN poster AS p
    ON m.id = p.movieid
    WHERE p.filename IS NULL
    LIMIT 250");

print_r($movieinfo);
    foreach ($movieinfo as $m) {
        $movieid = ltrim($m["id"], 'm'); 
        $url = "https://api.themoviedb.org/3/movie/".$movieid."?api_key=".apikey;
		$json = file_get_contents($url);
		//echo "<br>urlwithid:".$url."<br>";
		
		$movie = json_decode($json, true);
        
        $thumb = $m["id"]."_thumb";
        $poster = $m["id"]."_poster";
        $backdrop = $m["id"]."_backdrop";

        /*echo $url;
        echo "<br>thumb: ".$thumb;
        echo "<br>poster: ".$poster;
        echo "<br>backdrop: ".$backdrop;*/
        echo "<br><img src='".basethumburl.$movie["poster_path"]."'><br>";
        addPoster($m["id"], $thumb, 1);
        downloadPosterToDir(basethumburl.$movie["poster_path"], $thumb);
        addPoster($m["id"], $poster, 3);
        downloadPosterToDir(baseposterurl.$movie["poster_path"], $poster);
        addPoster($m["id"], $backdrop, 5);
        downloadPosterToDir(basebackdropurl.$movie["backdrop_path"], $backdrop);
    }
}


?>
