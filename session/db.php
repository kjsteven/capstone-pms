<?php
$servername = "localhost"; 
$username = "u167471319_root";       
$password = "pQ|8?Rsz";            
$dbname = "u167471319_propertywise";   

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


