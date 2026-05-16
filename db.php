<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user= getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'finance_db';

$conn= new mysqli($host,$user,$pass,$name);
?>