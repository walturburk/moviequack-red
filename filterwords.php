<?php

include("db_functions.php");
include("functions.php");

$t = new Template("templates/moviepage.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");


if (isset($_REQUEST["removetags"])) {
    
    filterWords(getFilteredWords());
}

$alltags = getAllTags();
$content = "<a href='?removetags'>Remove tags</a><br>".printTagsToFilter($alltags);

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


?>
