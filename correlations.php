<?php
include("db_functions.php");
include("functions.php");

$t = new Template("templates/correlations.html");
$layout = new Template("templates/layout.html");
$foundation = new Template("templates/foundation.html");

function getCorrelations($user, $everyone = false) {
    if (!$everyone) {
        $rightjoin = 'RIGHT JOIN mqold.follow as f 
                    ON (t.user = f.follower AND s.user = f.follows)';
    } else {
        $rightjoin = "";
    }
    

	$sql = 'SELECT corr, ((10+count)/10)*corr AS weight, user2 
    FROM
    (
    SELECT user1, user2, count(*) as count,  
    (avg(x * y) - avg(x) * avg(y)) / 
    (sqrt(avg(x * x) - avg(x) * avg(x)) * sqrt(avg(y * y) - avg(y) * avg(y) ) ) 
    AS corr2, 
    ( SUM( x * y ) - SUM( x ) * SUM( y ) / COUNT( x ) ) / COUNT( x ) as corr 
    FROM
    (
    SELECT t.rating as x, s.rating as y, t.user AS user1, s.user AS user2 
    FROM mqold.ratemovie t
    INNER JOIN mqold.ratemovie s
     ON (t.movie = s.movie)
      '.$rightjoin.' 
     WHERE (t.user = "'.$user.'") AND t.user != s.user) AS xandy
     GROUP BY user2
     ORDER BY corr DESC
     ) 
     as tab
     WHERE corr IS NOT NULL
     ORDER BY corr DESC';
	$correlations = db_select($sql);
    //echo $sql;
	return $correlations;
}

if (isset($_GET["everyone"])) {
    $everyone = true;
    $everyonebtn = " active ";
    $friendsbtn = "";
} else {
    $everyone = false;
    $everyonebtn = "";
    $friendsbtn = " active ";
}
$correlations = getCorrelations($_SESSION["user"], $everyone);
$corrtable = "";
foreach ($correlations as $line) {
    $corrtable .= "<div><a href='/user/".$line["user2"]."'>".$line["user2"]."</a> : <span>".$line["corr"]."</span></div>";
}

$content = $t->output();
$body = $layout->output();
echo $foundation->output();

?>