<?php

$config = parse_ini_file("config-dev.ini");

$servername = $config["dbpath"];
$username = $config["username"];
$password = $config["password"];

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// Create database
$sql = file_get_contents("mqolddump.sql");
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();


?>