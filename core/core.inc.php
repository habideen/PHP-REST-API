<?php  
error_reporting(0);
ob_start();
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DB_NAME"; //replace this with your db name

date_default_timezone_set('Africa/Lagos'); //set default timezone

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed");
} 

function getRow( $string){
	global $conn;
	$result = $conn->query($string);
	$result = $result->fetch_assoc();
	return $result;
}

function getRows( $string){
	global $conn;
	return $conn->query($string);
}

function countRows( $string){
	global $conn;
	$result = $conn->query($string);
	$result = mysqli_num_rows($result);
	return $result;
}

function query( $string ){
	global $conn;
	return ( $conn->query($string) ) ? true : false;
}


?>