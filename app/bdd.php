<?php
// retrieve database credentials from env vars
$mysql_server = getenv("MYSQL_SERVER");
$mysql_user = getenv("MYSQL_USER");
$mysql_password = getenv("MYSQL_PASSWORD");

// Create connection
$conn = new mysqli($mysql_server, $mysql_user, $mysql_password);

// Check connection
if ($conn->connect_error) {
    echo "serveur : ".$mysql_server."\n";
    echo "user : ".$mysql_user."\n";
    echo "password : ".$mysql_password."\n";
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>

