<?php

if (isDev()) {
  ini_set('display_errors', 1);
} else {
  ini_set('display_errors', 0);
}

session_start();

$config = getConfig();

define("dbname", $config["dbname"]);
$baseurl = $config["baseurl"];
define("baseurl", $baseurl);
define("officialurl", $config["officialurl"]);
$locale = "en_SE";

date_default_timezone_set('Europe/Stockholm');

error_reporting(E_ALL & ~E_NOTICE);

function getUserIp() {

    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

function getClientIp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function createId() {
	$newid = uniqid();
	return $newid;
}

function isDev() {
	if (strpos($_SERVER['SERVER_NAME'], "localhost") > 0 || strpos($_SERVER['SERVER_NAME'], "moviequack.com") < 1 || isset($_REQUEST["dev"])) {
		return true;
	} else {
	   return false;
   }

}


function getConfig() {

  if (isDev()) {
    $config = parse_ini_file("config-dev.ini");
    $config = parse_ini_file("../../config.ini");
    if (!$config) {
      $config = parse_ini_file("../../config.ini");
    }
  } else {
    $config = parse_ini_file("../../config-live.ini");
    $config["baseurl"] = "http://" . $_SERVER['SERVER_NAME'] ;
  }


  return $config;

}

function db_connect() {

	static $connection;

	if (!isset($connection)) {

		$config = getConfig();
		$connection = mysqli_connect($config["dbpath"], $config["username"], $config["password"], $config["dbname"]);

	}

	if ($connection === false) {
			echo "Failed to connect to MySQL: (" . mysqli_connect_errno . ") " . mysqli_connect_error;
	}
	return $connection;

}

function db_query($query) {
    // Connect to the database
    $connection = db_connect();

    // Query the database
    $result = mysqli_query($connection,$query);

	if($result === false) {
    $error = db_error();
    // Send the error to an administrator, log to a file, etc.
	//echo $error;
	}

    return $result;
}

function db_escape($string) {
  $connection = db_connect();
  $escaped = mysqli_real_escape_string($connection, $string);
  return $escaped;
}

function db_error() {
    $connection = db_connect();
    return mysqli_error($connection);
}

function db_select($query) {
    $rows = array();
    $result = db_query($query);

    // If query failed, return `false`
    if($result === false) {
        return false;
    }

    // If query was successful, retrieve all the rows into an array
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function formatTimestamp($time) {
	$formatted = date("l jS \of F Y H:i:s", $time);
	return $formatted;
}

function formatTimestampSmart($time) {
  $now = time();
  $timeago = $now-$time;
  if ($timeago > 14515200) {
    $form = date("jS F Y", $time);
  } else if ($timeago > 2419200) {
    $form = date("jS F", $time);
  } else if ($timeago > 604800) {
    $form = date("l jS H:i", $time);
  } else if ($timeago > 43200) {
      $form = date("l H:i", $time);
  } else {
    $form = date("H:i", $time);
  }

  return $form;
}

function getCost() { //get appropriate cost for password hashing on this server
	$timeTarget = 0.05; // 50 milliseconds
	$cost = 8;
	do {
		$cost++;
		$start = microtime(true);
		password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
		$end = microtime(true);
	} while (($end - $start) < $timeTarget);

	return "Appropriate Cost Found: " . $cost . "\n";
}

function createHash($source) {
	$hash = password_hash($source, PASSWORD_DEFAULT);
	return $hash;
}

function hashOk($source, $hash) {
	$isok = password_verify($source, $hash);
	return $isok;
}
