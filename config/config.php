<?php
$host = "sql200.byethost9.com"; 
$user = "b9_41443133"; 
$pass = "SpendSync123"; 
$dbname = "b9_41443133_budget_db"; 

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>