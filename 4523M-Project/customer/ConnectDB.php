<?php
session_start();
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb'; 
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Database connection failed:" . mysqli_connect_error());
}
?>